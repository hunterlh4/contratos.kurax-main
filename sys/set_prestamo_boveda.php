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


if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_listar_prestamos")
{
	$param_local = $_POST["param_local"];
	$incluir_busqueda_por_fecha = $_POST["incluir_busqueda_por_fecha"];

	$param_fecha_inicio = $_POST['param_fecha_inicio'];
	$param_fecha_inicio = date("Y-m-d", strtotime($param_fecha_inicio));

	$param_fecha_fin = $_POST['param_fecha_fin'];
	$param_fecha_fin = date("Y-m-d", strtotime($param_fecha_fin));

	$param_situacion = $_POST["param_situacion"];
	
	$login_usuario_id = $login?$login['id']:null;

	// INCIO: OBTENER RED

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
        
        $where_redes = " AND lo.red_id IN ($ids_data_select_red) ";
    }

    // FIN: OBTENER RED

	$where_local = "";
	$where_situacion = "";
	$where_fechas = "";

	if($param_local != 0)
	{
		$where_local = " AND cpb.local_id = '".$param_local."' ";
	}

	if($param_situacion != 0)
	{
		if($param_situacion == 3)
		{
			$where_situacion = " AND cpb.situacion_tesoreria_etapa_id = '".$param_situacion."' ";
		}
		else if($param_situacion == 4)
		{
			$where_situacion = " AND cpb.situacion_jefe_etapa_id = '".$param_situacion."' OR cpb.situacion_tesoreria_etapa_id = '".$param_situacion."' ";	
		}
		else
		{
			$where_situacion = " AND cpb.situacion_jefe_etapa_id = '".$param_situacion."' ";	
		}
	}

	if($incluir_busqueda_por_fecha == 1)
	{
		$where_fechas = " AND DATE_FORMAT(cpb.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_inicio."' AND '".$param_fecha_fin."' ";
	}

	$query = "
		SELECT
			cpb.id,
			lo.id AS local_id,
		    lo.nombre AS local,
		    etp.situacion AS situacion_tipo_prestamo,
		    lo.cc_id AS centro_costo,
		    cpb.caja_id_receptora,
		    cpb.fecha_recibe_dinero,
		    cpb.monto,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		    ej.situacion AS situacion_jefe,
		    et.situacion AS situacion_tesoreria,
		    cpb.created_at AS fecha_solicitud,
		    pp.fecha_carga_comprobante
		FROM tbl_caja_prestamo_boveda cpb
			INNER JOIN tbl_locales lo
			ON cpb.local_id = lo.id
			INNER JOIN tbl_prestamo_etapa etp
			ON cpb.tipo_prestamo = etp.id
			INNER JOIN tbl_usuarios tu
			ON cpb.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_prestamo_etapa ej
			ON cpb.situacion_jefe_etapa_id = ej.id
			INNER JOIN tbl_prestamo_etapa et
			ON cpb.situacion_tesoreria_etapa_id = et.id
			LEFT JOIN tbl_prestamo_programacion_detalle ppd
		    ON ppd.tbl_caja_prestamo_boveda_id = cpb.id
		    LEFT JOIN tbl_prestamo_programacion pp
		    ON pp.id = ppd.tbl_prestamo_programacion_id
		WHERE cpb.status = 1
			".$where_redes."
			".$where_local."
			".$where_situacion."
			".$where_fechas."
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->situacion_tipo_prestamo,
			"2" => "[".$reg->centro_costo."] - ".$reg->local,
			"3" => "S/ ".number_format($reg->monto, 2, '.', ','),
			"4" => $reg->usuario_solicitante,
			"5" => $reg->fecha_solicitud,
			"6" => $reg->situacion_jefe,
			"7" => $reg->situacion_tesoreria,
			"8" => $reg->fecha_carga_comprobante,
			"9" => $reg->caja_id_receptora,
			"10" => $reg->fecha_recibe_dinero,
			"11" => '<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=prestamo&amp;sub_sec_id=boveda_detalle_solicitud&id='.$reg->id.'&amp;param=1"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
			        '
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"query" => $query,
		"list_query" => $list_query,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_listar_locales") 
{
	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'prestamo' AND sub_sec_id = 'boveda' LIMIT 1")->fetch_assoc();

	$menu_prestamo = $menu_id_consultar["id"];

	$id_local_red = $_POST["id_local_red"];

	$usuario_id = $login?$login['id']:null;
	$usuario_cargo_id = $login?$login['cargo_id']:null;

	$query = "";
	$where_red_id = "";
	
	$where_red_id = " AND l.red_id = '".$id_local_red."' ";

	if($usuario_cargo_id == 16)
    {
        //SI ES JEFE COMERCIAL
        $query = 
        "
            SELECT
                z.id,
                l.id AS local_id,
                l.nombre, l.cc_id AS ceco
            FROM tbl_zonas z
                INNER JOIN tbl_personal_apt p
                ON p.id = z.jop_id
                INNER JOIN tbl_usuarios u
                ON p.id = u.personal_id
                INNER JOIN tbl_locales l
                ON z.id = l.zona_id
            WHERE u.id = ".$usuario_id." 
                AND l.estado = 1 AND l.nombre IS NOT NULL
                 ".$where_red_id."
            ORDER BY l.nombre ASC
        ";
    }
    else if($usuario_cargo_id == 4 || array_key_exists($menu_prestamo,$usuario_permisos) && in_array("add_prestamo_boveda", $usuario_permisos[$menu_prestamo]))
    {
        //SI ES SUPERVISOR U OTRO PERSONAL QUE TIENE PERMISO DE REGISTRAR PRESTAMO BOVEDA
        $query = 
        "
            SELECT
                ul.id, ul.usuario_id, ul.local_id,
                l.nombre, l.cc_id AS ceco
            FROM tbl_usuarios_locales ul
                INNER JOIN tbl_locales l
                ON ul.local_id = l.id
            WHERE ul.usuario_id = ".$usuario_id." 
                AND ul.estado = 1 AND l.estado = 1 AND l.nombre IS NOT NULL
                ".$where_red_id."
            ORDER BY l.nombre ASC
        ";
    }
    else
    {
    	$result["http_code"] = 400;
    	$result["codigo"] = 2;
    	$result["status"] = "Alerta";
		$result["result"] = "No tienes el cargo para solicitar préstamo boveda.";
		echo json_encode($result);
		exit();
    }

	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["consulta_error"] = $mysqli->error;
		exit();
	}
	
	if(count($list) == 0)
	{
		$result["http_code"] = 400;
		$result["codigo"] = 1;
		$result["result"] = "No se encontro resultados.";
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
		$result["codigo"] = 1;
		$result["result"] = "No existen registros.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_prestamo_boveda_modal_nuevo_prestamo")
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_tipo_tienda = $_POST['form_modal_sec_prestamo_boveda_param_tipo_tienda'];
		$param_local = $_POST['form_modal_sec_prestamo_boveda_param_local'];
		$param_tipo_prestamo = $_POST['form_modal_sec_prestamo_boveda_param_tipo_prestamo'];
		$asignar_al_cajero = $_POST['asignar_al_cajero'];

		$param_num_cuenta_asignacion_id = $_POST['form_modal_sec_prestamo_boveda_param_num_cuenta'];
		$modal_param_cajero = $_POST['form_modal_sec_prestamo_boveda_param_cajero'];
		$modal_param_cliente = $_POST['form_modal_sec_prestamo_boveda_param_cliente'];
		$modal_param_cliente_dni = $_POST['form_modal_sec_prestamo_boveda_param_cliente_dni'];
		$modal_param_banco = $_POST['form_modal_sec_prestamo_boveda_param_banco'];
		$modal_param_num_cuenta_cajero = $_POST['form_modal_sec_prestamo_boveda_param_num_cuenta_cajero'];
		$param_monto = str_replace(",","",$_POST["form_modal_sec_prestamo_boveda_param_monto"]);

		$param_validar_prestamo_existente = $_POST['validar_prestamo_existente'];

		$error = '';
		$respuesta_email = "";

		$campos_cabecera = "";
		$campos_valores = "";

		$validar_prestamo_existente = "0";

		$fecha_actual = date("Y-m-d");

		//INICIO: VALIDAR SI TIENE DNI REGISTRADO
		
		$param_verificar_dni = "";

		if($asignar_al_cajero == 1)
		{
			// VERIFICAR AL CAJERO
			$param_verificar_dni = $modal_param_cajero;
		}
		else
		{
			// VERIFICAR AL SUPERVISOR
			$param_verificar_dni = $usuario_id;
		}

		$select_verificar_dni = 
		"
			SELECT
				u.id,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario,
			    IFNULL(p.dni, '') AS dni
			FROM tbl_usuarios u
				INNER JOIN tbl_personal_apt p
				ON u.personal_id = p.id
			WHERE u.id = '".$param_verificar_dni."' 
				AND u.estado = 1
				AND p.estado = 1
			LIMIT 1
		";

		$list_verificar_dni = $mysqli->query($select_verificar_dni);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Error al registrar.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		$row_count = $list_verificar_dni->num_rows;
		
		$verificar_dni = "";
		$verificar_dni_usuario = "";

		if ($row_count > 0) 
		{
			$row = $list_verificar_dni->fetch_assoc();
			$id = $row["id"];
			$verificar_dni = $row["dni"];
			$verificar_dni_usuario = $row["usuario"];
		}

		if($verificar_dni == "")
		{
			$result["http_code"] = 400;
			$result["status"] = "Error al registrar.";
			$result["error"] = "No existe el DNI para la persona a depositar " .$verificar_dni_usuario;

			echo json_encode($result);
			exit();
		}

		//FIN: VALIDAR SI TIENE DNI REGISTRADO

		if($param_validar_prestamo_existente == 1)
		{
			$select_existe_prestamo = 
			"
			    SELECT
					id, local_id, monto
				FROM tbl_caja_prestamo_boveda
				WHERE local_id = '".$param_local."' 
					AND DATE_FORMAT(created_at, '%Y-%m-%d') = '".$fecha_actual."'
			";

			$query_select_existe_prestamo = $mysqli->query($select_existe_prestamo);

			$row_count = mysqli_num_rows($query_select_existe_prestamo);

			if($row_count > 0)
			{
			    $validar_prestamo_existente = "1";
			    $result["http_code"] = 201;
				$result["status"] = "";
				$result["error"] = "";
				echo json_encode($result);
				exit();
			}
			else
			{
				$validar_prestamo_existente = "0";
			}
		}
		
		if($validar_prestamo_existente == 0)
		{
			$select_horarios_boveda = 
			"
			    SELECT
			        id, nombre, descripcion, valor
			    FROM tbl_prestamo_parametro
			    WHERE id IN (1)
			";

			$query_horarios_boveda = $mysqli->query($select_horarios_boveda);

			$row_count = mysqli_num_rows($query_horarios_boveda);

			$valor_solicitar_prestamo = 0;

			if($row_count > 0)
			{
			    while($li = $query_horarios_boveda->fetch_assoc())
			    {
			        $reg_id = $li["id"];
			        $reg_valor = $li["valor"];

			       	$valor_solicitar_prestamo = $reg_valor;
			    }
			}

			$registrar_solicitud_horario = false;
			$hora_actual = date("H:i:s");

			if($hora_actual < $valor_solicitar_prestamo)
			{
			    $registrar_solicitud_horario = true;
			}


			if($registrar_solicitud_horario === true)
			{
				if($param_tipo_tienda == 9)
				{
					//TIENDAS SPORBART

					if($param_tipo_prestamo == 7)
					{
						//TIPO PRESTAMO: BOVEDA

						if($asignar_al_cajero == 1)
						{
							//CAMPOS:
							//CAJERO

							$campos_cabecera = 
							"
								asignacion_id_num_cuenta,
								cajero_usuario_id
							";
							
							$campos_valores = 
							"
								'0',
								'".$modal_param_cajero."'
							";
						}
						else
						{
							$campos_cabecera = 
							"
								asignacion_id_num_cuenta
							";
							
							$campos_valores = 
							"
								'0'
							";
						}
					}
					else if($param_tipo_prestamo == 8)
					{
						//TIPO PRESTAMO: PAGO DE PREMIOS
						
						//CAMPOS:
						//CLIENTE
						//CLIENTE DNI
						//BANCO
						//NUMERO DE CUENTA
						//ARCHIVOS

						if(!isset($_FILES['form_modal_sec_prestamo_boveda_param_archivos']))
						{
							$result["http_code"] = 400;
							$result["status"] = "No se encontro Archivos.";
							$result["error"] = "Seleccionar al menos 1 archivo.";

							echo json_encode($result);
							exit();
						}

						$campos_cabecera = 
						"
							asignacion_id_num_cuenta,
							cliente, 
							cliente_dni,
							banco_cajero_id, 
							numero_cuenta_cajero
						";

						$campos_valores = 
						"
							'0',
							'".$modal_param_cliente."', 
							'".$modal_param_cliente_dni."', 
							'".$modal_param_banco."', 
							'".$modal_param_num_cuenta_cajero."'
						";
					}
				}
				else if($param_tipo_tienda == 1 || $param_tipo_tienda == 16)
				{
					//RED AT

					if($param_tipo_prestamo == 7)
					{
						//TIPO PRESTAMO: BOVEDA

						if($asignar_al_cajero == 1)
						{
							//CAMPOS:
							//CAJERO
							//BANCO
							//NUMERO DE CUENTA

							$campos_cabecera = 
							"
								asignacion_id_num_cuenta,
								cajero_usuario_id,
								banco_cajero_id, 
								numero_cuenta_cajero
							";
							
							$campos_valores = 
							"
								'0',
								'".$modal_param_cajero."',
								'".$modal_param_banco."', 
								'".$modal_param_num_cuenta_cajero."'
							";
						}
						else
						{
							//CAMPOS:
							//NUMERO DE CUENTA

							$campos_cabecera = 
							"
								asignacion_id_num_cuenta
							";
							
							$campos_valores = 
							"
								'".$param_num_cuenta_asignacion_id."'
							";
						}
					}
					else if($param_tipo_prestamo == 8)
					{
						//TIPO PRESTAMO: PAGO DE PREMIOS

						//CAMPOS:
						//CLIENTE
						//CLIENTE DNI
						//BANCO
						//NUMERO DE CUENTA
						//ARCHIVOS

						if(!isset($_FILES['form_modal_sec_prestamo_boveda_param_archivos']))
						{
							$result["http_code"] = 400;
							$result["status"] = "No se encontro Archivos.";
							$result["error"] = "Seleccionar al menos 1 archivo.";

							echo json_encode($result);
							exit();
						}

						$campos_cabecera = 
						"
							asignacion_id_num_cuenta,
							cliente, 
							cliente_dni,
							banco_cajero_id, 
							numero_cuenta_cajero
						";

						$campos_valores = 
						"
							'0',
							'".$modal_param_cliente."', 
							'".$modal_param_cliente_dni."', 
							'".$modal_param_banco."', 
							'".$modal_param_num_cuenta_cajero."'
						";
					}
				}

				$query_insert = 
				"
					INSERT INTO tbl_caja_prestamo_boveda
					(
						tipo_tienda,
						local_id,
						tipo_prestamo,
						monto,
						caja_id_recibe_dinero,
						".$campos_cabecera.",
						situacion_jefe_etapa_id,
						situacion_tesoreria_etapa_id,
						status,
						user_created_id,
						created_at,
						user_updated_id,
						updated_at
					)
					VALUES
					(
						'".$param_tipo_tienda."',
						'".$param_local."',
						'".$param_tipo_prestamo."',
						'".$param_monto."',
						'0',
						".$campos_valores.",
						'1',
						'1',
						1,
						'".$login["id"]."', 
						'".date('Y-m-d H:i:s')."',
						'".$login["id"]."', 
						'".date('Y-m-d H:i:s')."'
					)
				";

				$mysqli->query($query_insert);

				$id_prestamo = mysqli_insert_id($mysqli);

				if($mysqli->error)
				{
					$error = $mysqli->error;

					$result["http_code"] = 400;
					$result["status"] = "Error al registrar.";
					$result["error"] = $error;

					echo json_encode($result);
					exit();
				}

				// INICIO: GUARDAR ARCHIVOS DE PAGO DE PREMIOS
				if($param_tipo_prestamo == 8)
				{
					$path = "/var/www/html/files_bucket/prestamos/boveda/pago_premios/";
					$download = "/files_bucket/prestamos/boveda/pago_premios/";

					if (!is_dir($path)) 
					{
						mkdir($path, 0777, true);
					}

					$cant = 1;

					for ($i=0; $i < count($_FILES['form_modal_sec_prestamo_boveda_param_archivos']['name']); $i++)
					{
						$file_name = $_FILES['form_modal_sec_prestamo_boveda_param_archivos']['name'][$i];
						$file_tmp = $_FILES['form_modal_sec_prestamo_boveda_param_archivos']['tmp_name'][$i];
						$file_size = $_FILES['form_modal_sec_prestamo_boveda_param_archivos']['size'][$i];
						$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

						$nombreFileUpload = "ID_".$id_prestamo."_item_".$cant."_file_".date('YmdHis'). ".".$file_extension;
						$nombreDownload = $download.$nombreFileUpload;
						move_uploaded_file($file_tmp, $path. $nombreFileUpload);

						
						$query_insert_file = 
						"
							INSERT INTO tbl_prestamo_boveda_files
							(
								nombre_tabla_referencia,
								nombre_tabla_referencia_id,
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
								'tbl_caja_prestamo_boveda',
								'".$id_prestamo."',
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
							)
						";

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
				}
				// FIN: GUARDAR ARCHIVOS DE PAGO DE PREMIOS
				
				if($error == '')
				{
					// INICIO ENVIAR CORREO
					
					$respuesta_email = sec_prestamo_boveda_send_email_nuevo_prestamo($id_prestamo);

					// FIN ENVIAR CORREO

					$result["http_code"] = 200;
					$result["status"] = "Datos guardados";
					$result["error"] = "";
					$result["respuesta_email"] = $respuesta_email;

					echo json_encode($result);
					exit();
				}
				else
				{
					$result["http_code"] = 400;
					$result["status"] = "Error al registrar.";
					$result["error"] = $error;

					echo json_encode($result);
					exit();
				}
			}
			else
			{
				$result["http_code"] = 400;
				$result["status"] = "Error al registrar.";
				$result["error"] = "La hora permitida para realizar la solicitud del préstamo bóveda es hasta las " .date( "g:i a", strtotime($valor_solicitar_prestamo));

				echo json_encode($result);
				exit();
			}
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

function sec_prestamo_boveda_send_email_nuevo_prestamo($id_prestamo)
{
	include("db_connect.php");
	include("sys_login.php");

	$respuesta_email = 0;

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	$sel_query = $mysqli->query("
		SELECT
			b.id, b.tipo_tienda, lr.nombre AS nombre_tipo_tienda,
			b.tipo_prestamo, etp.situacion AS nombre_tipo_prestamo,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		    b.created_at AS fecha_solicitud,
		    l.id AS local_id,
		    l.nombre AS local,
		    b.monto,
			ej.situacion AS situacion_jefe,
		    IFNULL(b.cajero_usuario_id, 0) AS cajero_usuario_id,
		    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS cajero_asignado
		FROM tbl_caja_prestamo_boveda b
			INNER JOIN tbl_locales l
			ON b.local_id = l.id
			INNER JOIN tbl_usuarios tu
			ON b.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_prestamo_etapa ej
			ON b.situacion_jefe_etapa_id = ej.id
			LEFT JOIN tbl_usuarios tuc
			ON b.cajero_usuario_id = tuc.id
			LEFT JOIN tbl_personal_apt tpc
			ON tuc.personal_id = tpc.id
			INNER JOIN tbl_locales_redes lr
	    	ON b.tipo_tienda = lr.id
	    	INNER JOIN tbl_prestamo_etapa etp
	    	ON b.tipo_prestamo = etp.id
		WHERE b.id = '".$id_prestamo."'
	");

	$body = "";
	$body .= '<html>';

	while($sel = $sel_query->fetch_assoc())
	{
		$id = $sel["id"];
		$nombre_tipo_tienda = $sel["nombre_tipo_tienda"];
		$nombre_tipo_prestamo = $sel["nombre_tipo_prestamo"];
		$usuario_solicitante = $sel["usuario_solicitante"];
		$fecha_solicitud = $sel["fecha_solicitud"];
		$local_id = $sel["local_id"];
		$local = $sel["local"];
		$monto = $sel["monto"];
		$situacion_jefe = $sel["situacion_jefe"];
		$cajero_usuario_id = $sel["cajero_usuario_id"];
		$cajero_asignado = $sel["cajero_asignado"];
		

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
			$body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tipo Tienda:</b></td>';
			$body .= '<td>'.$nombre_tipo_tienda.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tipo Tienda:</b></td>';
			$body .= '<td>'.$nombre_tipo_prestamo.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Usuario Solicitante:</b></td>';
			$body .= '<td>'.$usuario_solicitante.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$fecha_solicitud.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tienda:</b></td>';
			$body .= '<td>'.$local.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Monto:</b></td>';
			$body .= '<td>S/ '.number_format($monto, 2 , '.', ',').'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Situación:</b></td>';
			$body .= '<td>'.$situacion_jefe.'</td>';
		$body .= '</tr>';

		if($cajero_usuario_id != 0)
		{
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Cajero Asignado:</b></td>';
				$body .= '<td>'.$cajero_asignado.'</td>';
			$body .= '</tr>';
		}

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=prestamo&sub_sec_id=boveda_detalle_solicitud&id='.$id_prestamo.'&amp;param=2" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	$titulo_email = "";
	
	if(env('SEND_EMAIL') == 'test')
	{
		$titulo_email = "TEST SISTEMAS: Préstamo Bóveda: Nueva Solicitud de Préstamo ID: ".$id_prestamo;
	}
	else
	{
		$titulo_email = "Préstamo Bóveda: Nueva Solicitud de Préstamo ID: ".$id_prestamo;
	}

	$cc = [
	];

	$bcc = [
	];

	// INICIO LISTAR USUARIOS DE LA TIENDA DEL PRESTAMO
	// USUARIOS: SUPERVISORES
	// AREA OPERACIONES: 21
	// CARGO SUPERVISOR: 4
	$select_usuarios_enviar_a = 
	"
		SELECT DISTINCT
			p.correo
		FROM tbl_usuarios_locales ul
			LEFT JOIN tbl_usuarios u
			ON ul.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE ul.local_id = '".$local_id."' AND ul.estado = 1 AND p.correo IS NOT NULL
			AND (p.area_id = 15 OR p.area_id = 21) AND p.cargo_id = 4 AND p.estado = 1
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
	// FIN LISTAR USUARIOS DE LA TIENDA DEL PRESTAMO
	
	// INICIO LISTAR JEFE COMERCIALES DE LAS TIENDAS DEL PRESTAMO REALIZADO
	$select_usuarios_jc_enviar_a = 
	"
		SELECT
			p.correo
		FROM tbl_locales l
			INNER JOIN tbl_zonas z
			ON l.zona_id = z.id
			INNER JOIN tbl_personal_apt p
			ON p.id = z.jop_id
		WHERE l.id = '".$local_id."' AND p.correo IS NOT NULL AND p.estado = 1
	";
	
	$sel_query_usuarios_jc_enviar_a = $mysqli->query($select_usuarios_jc_enviar_a);

	$row_count_jc = $sel_query_usuarios_jc_enviar_a->num_rows;

	if ($row_count_jc > 0)
	{
		while($sel = $sel_query_usuarios_jc_enviar_a->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($cc, $sel['correo']);
			}
		}
	}
	// FIN LISTAR JEFE COMERCIALES DE LAS TIENDAS DEL PRESTAMO REALIZADO

	//INICIO: LISTAR USUARIOS DEL GRUPO - COPIA OCULTA
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
	//FIN: LISTAR USUARIOS DEL GRUPO - COPIA OCULTA

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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_btn_export")
{
	$param_local = $_POST["param_local"];
	$incluir_busqueda_por_fecha = $_POST["incluir_busqueda_por_fecha"];

	$param_fecha_inicio = $_POST['param_fecha_inicio'];
	$param_fecha_inicio = date("Y-m-d", strtotime($param_fecha_inicio));

	$param_fecha_fin = $_POST['param_fecha_fin'];
	$param_fecha_fin = date("Y-m-d", strtotime($param_fecha_fin));

	$param_situacion = $_POST["param_situacion"];
	
	$where_local = "";
	$where_situacion = "";
	$where_fechas = "";

	if($param_local != 0)
	{
		$where_local = " AND cpb.local_id = '".$param_local."' ";
	}

	if($param_situacion != 0)
	{
		if($param_situacion == 3)
		{
			$where_situacion = " AND cpb.situacion_tesoreria_etapa_id = '".$param_situacion."' ";
		}
		else
		{
			$where_situacion = " AND cpb.situacion_jefe_etapa_id = '".$param_situacion."' ";	
		}
	}

	if($incluir_busqueda_por_fecha == 1)
	{
		$where_fechas = " AND DATE_FORMAT(cpb.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_inicio."' AND '".$param_fecha_fin."' ";
	}

	$query = "
		SELECT
			cpb.id,
			lo.id AS local_id,
			lo.nombre AS local,
		    lo.cc_id AS ceco,
		    lo.zona_id,
		    z.nombre AS local_zona,
		    cpb.caja_id_recibe_dinero,
		    IF(cpb.caja_id_recibe_dinero = 1, 'SI', 'NO') AS caja_recibe_dinero,
			cpb.caja_id_receptora,
		    cpb.fecha_recibe_dinero,
			cpb.monto,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		    ej.situacion AS situacion_jefe,
		    et.situacion AS situacion_tesoreria,
			cpb.created_at AS fecha_solicitud		    
		FROM tbl_caja_prestamo_boveda cpb
			INNER JOIN tbl_locales lo
			ON cpb.local_id = lo.id
		    INNER JOIN tbl_zonas z
		    ON lo.zona_id = z.id
			INNER JOIN tbl_usuarios tu
			ON cpb.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_prestamo_etapa ej
			ON cpb.situacion_jefe_etapa_id = ej.id
			INNER JOIN tbl_prestamo_etapa et
			ON cpb.situacion_tesoreria_etapa_id = et.id
		WHERE cpb.status = 1
			".$where_local."
			".$where_situacion."
			".$where_fechas."
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/prestamos/boveda/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/prestamos/boveda/*'); //obtenemos todos los nombres de los ficheros
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

	$titulosColumnas = array('Nº', 'Tienda', 'CECO', 'Zona', 'Monto', 'Solicitante', 'Fecha Solicitud', 'Situación Jefe', 'Situación Tesoreria', 'Confirmación Dinero Recibido', 'Caja Receptora', 'Fecha Recepción');

	// Se combinan las celdas A1 hasta K1, para colocar ahí el titulo del reporte
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:L1');
	
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
    ->setCellValue('I2', $titulosColumnas[8])
    ->setCellValue('J2', $titulosColumnas[9])
    ->setCellValue('K2', $titulosColumnas[10])
    ->setCellValue('L2', $titulosColumnas[11]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 3; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['local'])
		->setCellValue('C'.$i, $fila['ceco'])
		->setCellValue('D'.$i, $fila['local_zona'])
		->setCellValue('E'.$i, 'S/ '.$fila['monto'])
		->setCellValue('F'.$i, $fila['usuario_solicitante'])
		->setCellValue('G'.$i, $fila['fecha_solicitud'])
		->setCellValue('H'.$i, $fila['situacion_jefe'])
		->setCellValue('I'.$i, $fila['situacion_tesoreria'])
		->setCellValue('J'.$i, $fila['caja_recibe_dinero'])
		->setCellValue('K'.$i, $fila['caja_id_receptora'])
		->setCellValue('L'.$i, $fila['fecha_recibe_dinero']);
		
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

	// SIRVE PARA PONER ALTURA A LA FILA, EN ESTE CASO A LA FILA 1.
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(63);

	$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:L".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('C1:E'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('G1:L'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('D3:D'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

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
	$excel_path = '/var/www/html/files_bucket/prestamos/boveda/Préstamo Bóveda.xls';
	$excel_path_download = '/files_bucket/prestamos/boveda/Préstamo Bóveda.xls';

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

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_modal_atender_prestamo_listar")
{

	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_situacion = $_POST["param_situacion"];
		
		$usuario_cargo_id = $login?$login['cargo_id']:null;

		$where = "";

		if($usuario_cargo_id == 16)
		{
			//JEFE COMERCIAL
			$where = " AND tuj.id = '".$usuario_id."' ";

		}
		else if($usuario_cargo_id == 29)
		{
			//SUB GERENTE
			if(env('SEND_EMAIL') == 'test')
			{
				//SUB GERENTE
				//DEV
				$sub_gerente_centro = 4357;
				$sub_gerente_diferente_centro = 4358;

				if($usuario_id == $sub_gerente_centro)
				{
					$zonas_sub_gerente_centro = "1, 7, 8";

					$where = " AND lo.zona_id IN (".$zonas_sub_gerente_centro.") ";
				}
				else if($usuario_id == $sub_gerente_diferente_centro)
				{
					$zonas_sub_gerente_diferente_centro = "3, 2, 4, 6, 5";

					$where = " AND lo.zona_id IN (".$zonas_sub_gerente_diferente_centro.") ";
				}
				else
				{
					$result["http_code"] = 400;
			        $result["result"] ="No eres el sub gerente.";

			        echo json_encode($result);
					exit();
				}
			}
			else
			{
				//PRODUCCION
				//GERARDO RUIZ (Sub Gerente Comercial)
				$sub_gerente_centro = 6900;

				//HECTOR ARROYO (Sub Gerente Comercial)
				$sub_gerente_diferente_centro = 135;

				if($usuario_id == $sub_gerente_centro)
				{
					$zonas_sub_gerente_centro = "1, 7, 8, 14";

					$where = " AND lo.zona_id IN (".$zonas_sub_gerente_centro.") ";
				}
				else if($usuario_id == $sub_gerente_diferente_centro)
				{
					$zonas_sub_gerente_diferente_centro = "3, 2, 4, 6, 5";

					$where = " AND lo.zona_id IN (".$zonas_sub_gerente_diferente_centro.") ";
				}
				else
				{
					$result["http_code"] = 400;
			        $result["result"] ="No eres el sub gerente.";

			        echo json_encode($result);
					exit();
				}
			}
			
		}
		else
		{
			$result["http_code"] = 400;
	        $result["result"] ="No tienes el cargo para atender los préstamo bóveda.";

	        echo json_encode($result);
			exit();
		}

		$tbody = '';
		$ids_todos_prestamo = '';
		$total_monto_programado = 0;

		$query = "
			SELECT
				cpb.id,
				lo.id AS local_id,
			    lr.nombre AS situacion_tipo_tienda,
			    etp.situacion AS situacion_tipo_prestamo,
			    lo.nombre AS local,
			    cpb.caja_id_receptora,
			    cpb.fecha_recibe_dinero,
			    cpb.monto,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
			    ej.situacion AS situacion_jefe,
			    et.situacion AS situacion_tesoreria,
			    cpb.created_at AS fecha_solicitud
			FROM tbl_caja_prestamo_boveda cpb
				INNER JOIN tbl_locales lo
				ON cpb.local_id = lo.id
				INNER JOIN tbl_locales_redes lr
				ON cpb.tipo_tienda = lr.id
				INNER JOIN tbl_prestamo_etapa etp
				ON cpb.tipo_prestamo = etp.id
				INNER JOIN tbl_zonas z
			    ON lo.zona_id = z.id
			    INNER JOIN tbl_usuarios tuj
			    ON z.jop_id = tuj.personal_id
				INNER JOIN tbl_usuarios tu
				ON cpb.user_created_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_prestamo_etapa ej
				ON cpb.situacion_jefe_etapa_id = ej.id
				INNER JOIN tbl_prestamo_etapa et
				ON cpb.situacion_tesoreria_etapa_id = et.id
			WHERE cpb.status = 1 
				AND cpb.situacion_jefe_etapa_id = '".$param_situacion."'
				".$where."
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

			while ($row = $list_query->fetch_assoc()) 
			{
				$tbody .= '<tr>';
					$tbody .= '<td>' . $num . '</td>';
					$tbody .= '<td>' . $row["id"] . '</td>';
					$tbody .= '<td>' . $row["situacion_tipo_tienda"] . '</td>';
					$tbody .= '<td>' . $row["situacion_tipo_prestamo"] . '</td>';
					$tbody .= '<td>' . $row["local"] . '</td>';
					$tbody .= '<td class="text-center"> S/ ' . $row["monto"] . '</td>';
					$tbody .= '<td>' . $row["usuario_solicitante"] . '</td>';
					$tbody .= '<td>' . $row["fecha_solicitud"] . '</td>';
					$tbody .= '<td class="text-center">' . $row["situacion_jefe"] . '</td>';
					$tbody .= '<td class="text-center">';
						$tbody .= '<a class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Detalle" href="./?sec_id=prestamo&amp;sub_sec_id=boveda_detalle_solicitud&id='.$row["id"].'&amp;param=2" target="_blank">';
						$tbody .= '<span class="fa fa-eye"></span>';
						$tbody .= '</a>';
					$tbody .= '</td>';

					if($param_situacion == 1)
					{
						$tbody .= '<td class="text-center">';
							$tbody .= '<input type="checkbox" value="'.$row["id"].'" name="sec_prestamo_boveda_modal_check_atencion_jefe_'.$num.'" id="sec_prestamo_boveda_modal_check_atencion_jefe_'.$num.'" style="width: 36%; height: 30px; padding-bottom: 0px; margin-bottom: 0px; vertical-align: middle;">';
						$tbody .= '</td>';	
					}
				$tbody .= '</tr>';

				$total_monto_programado += $row["monto"];
				$num += 1;
			}
		}
		else
		{
			$tbody .= '<tr>';
				$tbody .= '<td colspan="11" style="text-align: center;">No existen registros</td>';
			$tbody .= '</tr>';
		}

		if ($row_count >= 0) 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["result"] = $tbody;
			$result["total_registro"] = $row_count;
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["result"] = "No hay registros de Asignación por pagar.";
		}

		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_modal_atender_prestamo_listar_diferente_pendiente")
{
	$param_situacion = $_POST["param_situacion"];

	$usuario_id = $login?$login['id']:null;

	$usuario_cargo_id = $login?$login['cargo_id']:null;

	$where = "";

	if($usuario_cargo_id == 16)
	{
		//JEFE COMERCIAL
		$where = " AND tuj.id = '".$usuario_id."' ";

	}
	else if($usuario_cargo_id == 29)
	{
		//SUB GERENTE
		if(env('SEND_EMAIL') == 'test')
		{
			//DEV
			$sub_gerente_centro = 4357;
			$sub_gerente_diferente_centro = 4358;

			if($usuario_id == $sub_gerente_centro)
			{
				$zonas_sub_gerente_centro = "1, 7, 8";

				$where = " AND lo.zona_id IN (".$zonas_sub_gerente_centro.") ";
			}
			else if($usuario_id == $sub_gerente_diferente_centro)
			{
				$zonas_sub_gerente_diferente_centro = "3, 2, 4, 6, 5";

				$where = " AND lo.zona_id IN (".$zonas_sub_gerente_diferente_centro.") ";
			}
		}
		else
		{
			//PRODUCCION
			//GERARDO RUIZ (Sub Gerente Comercial)
			$sub_gerente_centro = 6900;

			//HECTOR ARROYO (Sub Gerente Comercial)
			$sub_gerente_diferente_centro = 135;

			if($usuario_id == $sub_gerente_centro)
			{
				$zonas_sub_gerente_centro = "1, 7, 8, 14";

				$where = " AND lo.zona_id IN (".$zonas_sub_gerente_centro.") ";
			}
			else if($usuario_id == $sub_gerente_diferente_centro)
			{
				$zonas_sub_gerente_diferente_centro = "3, 2, 4, 6, 5";

				$where = " AND lo.zona_id IN (".$zonas_sub_gerente_diferente_centro.") ";
			}
		}
	}

	$query_select = "
		SELECT
			cpb.id,
			lo.id AS local_id,
			lr.nombre AS situacion_tipo_tienda,
			etp.situacion AS situacion_tipo_prestamo,
		    lo.nombre AS local,
		    cpb.caja_id_receptora,
		    cpb.fecha_recibe_dinero,
		    cpb.monto,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		    ej.situacion AS situacion_jefe,
		    et.situacion AS situacion_tesoreria,
		    cpb.created_at AS fecha_solicitud
		FROM tbl_caja_prestamo_boveda cpb
			INNER JOIN tbl_locales lo
			ON cpb.local_id = lo.id
			INNER JOIN tbl_locales_redes lr
			ON cpb.tipo_tienda = lr.id
			INNER JOIN tbl_prestamo_etapa etp
			ON cpb.tipo_prestamo = etp.id
			INNER JOIN tbl_zonas z
		    ON lo.zona_id = z.id
		    INNER JOIN tbl_usuarios tuj
		    ON z.jop_id = tuj.personal_id
			INNER JOIN tbl_usuarios tu
			ON cpb.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_prestamo_etapa ej
			ON cpb.situacion_jefe_etapa_id = ej.id
			INNER JOIN tbl_prestamo_etapa et
			ON cpb.situacion_tesoreria_etapa_id = et.id
		WHERE cpb.status = 1
			AND cpb.situacion_jefe_etapa_id = '".$param_situacion."' 
			".$where."
	";

	$list_query = $mysqli->query($query_select);

	//$li = $list_query->fetch_assoc();

	$data =  array();
	$cont = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $cont,
			"1" => $reg->id,
			"2" => $reg->situacion_tipo_tienda,
			"3" => $reg->situacion_tipo_prestamo,
			"4" => $reg->local,
			"5" => "S/ ".number_format($reg->monto, 2, '.', ','),
			"6" => $reg->usuario_solicitante,
			"7" => $reg->fecha_solicitud,
			"8" => $reg->situacion_jefe,
			"9" => '<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=prestamo&amp;sub_sec_id=boveda_detalle_solicitud&id='.$reg->id.'&amp;param=1"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
			        '
		);

		$cont++;
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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_modal_atender_prestamo_datatable_btn_aprobar_prestamo") 
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$select_horarios_boveda = 
		"
		    SELECT
		        id, nombre, descripcion, valor
		    FROM tbl_prestamo_parametro
		    WHERE id IN (2)
		";

		$query_horarios_boveda = $mysqli->query($select_horarios_boveda);

		$row_count = mysqli_num_rows($query_horarios_boveda);

		$valor_atencion = 0;

		if($row_count > 0)
		{
		    while($li = $query_horarios_boveda->fetch_assoc())
		    {
		        $reg_id = $li["id"];
		        $reg_valor = $li["valor"];

		       	$valor_atencion = $reg_valor;
		    }
		}

		$atender_solicitud_horario = false;
		$hora_actual = date("H:i:s");

		if($hora_actual < $valor_atencion)
		{
		    $atender_solicitud_horario = true;
		}


		if($atender_solicitud_horario === true)
		{
			$created_at = date("Y-m-d H:i:s");
			$error = '';

			$arreglo_data = $_POST["array_check_atencion_prestamo_jefe_aprobar"];
			$ids_data = json_decode($arreglo_data);

			foreach($ids_data as $item)
			{

				$query_update = "
							UPDATE tbl_caja_prestamo_boveda 
								SET situacion_jefe_etapa_id = 2,
									usuario_jefe_atencion_id = '".$usuario_id."',
									fecha_atencion_jefe = '".$created_at."'
							WHERE id = '".$item->item_id."' ";

				$mysqli->query($query_update);
			}

			if($mysqli->error)
			{
				$error .= $mysqli->error;
			}


			if ($error == '') 
			{
				$result["http_code"] = 200;
				$result["status"] = "Datos obtenidos de gestión.";
				$result["result"] = $error;
			} 
			else 
			{
				$result["http_code"] = 400;
				$result["status"] = "Datos obtenidos de gestión.";
				$result["result"] = $error;
			}

			echo json_encode($result);
			exit();
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Error al registrar.";
			$result["error"] = "La hora permitida para atender la solicitud del préstamo bóveda es hasta las " .date( "g:i a", strtotime($valor_solicitar_prestamo));

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}
}

?>