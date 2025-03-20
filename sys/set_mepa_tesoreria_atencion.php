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

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_obtener_empresa_numero_cuenta") 
{

	$empresa = $_POST["empresa"];
	
	$query = "
				SELECT
					id,
				    num_cuenta_corriente AS nombre
				FROM cont_num_cuenta
				WHERE status = 1 AND razon_social_id = '".$empresa."' AND moneda_id = 1 AND tipo_pago_id = 2
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

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_asignacion_caja_chica_pendientes_de_pago")
{
	// tipo_consulta 1: CREACION DE PROGRAMACION DE PAGOS
	// tipo_consulta 2: MOSTRAR LA ASIGNACION COMO PROGRAMACION DE PAGOS
	$tipo_consulta = $_POST["tipo_consulta"];

	$param_tipo_caja_chica = $_POST["mepa_tesoreria_atencion_param_tipo_caja_chica"];
	$param_tipo_caja_chica_texto = $_POST["mepa_tesoreria_atencion_param_tipo_caja_chica_texto"];
	$param_tipo_banco = $_POST["mepa_tesoreria_atencion_param_tipo_banco"];
	$param_tipo_empresa = $_POST["mepa_tesoreria_atencion_param_tipo_empresa"];
	$param_ids_asignacion = $_POST["ids_asignacion"];

	$contador_array_ids = 0;
	$data_param_ids_asignacion = json_decode($param_ids_asignacion);
	
	$ids_asignacion = '';

	foreach ($data_param_ids_asignacion as $value) 
	{
		if ($contador_array_ids > 0) 
		{
			$ids_asignacion .= ',';
		}
		
		$ids_asignacion .= $value;			
		$contador_array_ids++;
	}

	if($contador_array_ids == 0)
	{
		$ids_asignacion = 0;
	}


	//LISTAR LOS ID DE LA TABLA: mepa_caja_chica_programacion_detalle el campo: nombre_tabla_id
	$query_id_asignacion_programacion_detalle = "
		SELECT
			nombre_tabla_id
		FROM mepa_caja_chica_programacion_detalle
		WHERE status = 1 AND nombre_tabla = 'mepa_asignacion_caja_chica'
	";

	$data_id_asignacion_en_programacion_detalle = $mysqli->query($query_id_asignacion_programacion_detalle);

	$ids_asignacion_en_programacion_detalle = '';
	$cont_ids_asignacion = 0;

	while($row = $data_id_asignacion_en_programacion_detalle->fetch_assoc())
	{
		if($cont_ids_asignacion > 0)
		{
			$ids_asignacion_en_programacion_detalle .= ',';
		}

		$ids_asignacion_en_programacion_detalle .= $row["nombre_tabla_id"];

		$cont_ids_asignacion++;
	}
	
	$html = '';
	$tbody = '';
	$ids_todos_asignacion = '';
	$total_monto_programado = 0;

	$where_tipo_banco = "";

	if($param_tipo_banco == 1)
	{
		// SOLO BANCOS BBVA
		$where_tipo_banco = " AND ac.banco_id = 12";

	}
	else if($param_tipo_banco == 2)
	{
		// SOLO BANCOS DIFERENTES A BBVA
		$where_tipo_banco = " AND ac.banco_id != 12";
	}

	$query = "
		SELECT
			ma.id, ma.usuario_asignado_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado, tp.dni, ma.situacion_etapa_id, ce.situacion AS estado_solicitud, 
		    ma.fondo_asignado, ac.banco_id, tb.nombre AS banco, ac.num_cuenta
		FROM mepa_asignacion_caja_chica ma
			INNER JOIN tbl_usuarios tu
			ON ma.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_etapa ce
			ON ma.situacion_etapa_id = ce.etapa_id
			INNER JOIN mepa_asignacion_cuenta_bancaria ac
			ON ac.asignacion_id = ma.id
			INNER JOIN tbl_bancos tb
    		ON ac.banco_id = tb.id
		WHERE
			ma.status = 1 AND ma.tipo_solicitud_id = 1 AND ma.situacion_etapa_id = 6 
			AND ma.situacion_etapa_id_tesoreria != 7
				AND ma.empresa_id = '".$param_tipo_empresa."' AND ac.status = 1
			".$where_tipo_banco."
	";

	if ($tipo_consulta == '1')
	{
		if($ids_asignacion_en_programacion_detalle != '')
		{
			$query .= " AND ma.id NOT IN(" . $ids_asignacion_en_programacion_detalle . ")";
		}

		if ($ids_asignacion != '') 
		{
			$query .= " AND ma.id NOT IN(" . $ids_asignacion . ")";
		}
	}
	else 
	{
		$query .= " AND ma.id IN(" . $ids_asignacion . ")";
	}
	
	$list_query = $mysqli->query($query);

	if($mysqli->error)
	{
		enviar_error($mysqli->error . $query);
	}

	$row_count = $list_query->num_rows;

	if ($row_count > 0) 
	{
		$num = 1;

		while ($row = $list_query->fetch_assoc()) 
		{
			$tbody .= '<tr>';
				$tbody .= '<td>' . $num . '</td>';
				$tbody .= '<td>' . $row["usuario_asignado"] . '</td>';
				$tbody .= '<td>' . $row["dni"] . '</td>';
				$tbody .= '<td>' . $row["banco"] . '</td>';
				$tbody .= '<td>' . $row["num_cuenta"] . '</td>';
				$tbody .= '<td>' . $row["fondo_asignado"] . '</td>';
				
				$tbody .= '<td>';

				if ($tipo_consulta == '1') 
				{
					$tbody .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Agregar a la programación de pagos" onclick="mepa_tesoreria_atencion_agregar_asignacion_a_la_programacion_pagos(' . $row["id"] . ')">';
					$tbody .= '<i class="fa fa-plus"></i>';
					$tbody .= '</a>';
				} 
				else 
				{
					$tbody .= '<a class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Quitar de la programación de pagos" onclick="mepa_tesoreria_atencion_quitar_asignacion_de_la_programacion(' . $row["id"] . ')">';
					$tbody .= '<i class="fa fa-minus"></i>';
					$tbody .= '</a>';
				}
				$tbody .= '</td>';
				$tbody .= '<td>';
				if($tipo_consulta == '1')
				{
					$tbody .= '<button type="button" 
                		class="btn btn-danger btn-xs btn-block" onclick="mepa_tesoreria_atencion_rechazar_asignacion('.$row["id"].');">';
						$tbody .= '<i class="icon fa fa-bell"></i>';
						$tbody .= '<span id="demo-button-text">';
							$tbody .= 'Rechazar';
						$tbody .= '</span>';
					$tbody .= '</button>';
				}
				$tbody .= '</td>';
			$tbody .= '</tr>';

			if ($num == 1) 
			{
				$ids_todos_asignacion .= $row["id"];
			} 
			else 
			{
				$ids_todos_asignacion .= "," . $row["id"];
			}

			$total_monto_programado += $row["fondo_asignado"];
			$num += 1;
		}

		$html .= '<table class="table table-bordered" style="font-size: 12px;">';
			$html .= '<thead>';

			if ($tipo_consulta == '1') 
			{
				$html .= '<tr>';
					$html .= '<th colspan="8" style="background-color: #E5E5E5;">';
						$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px; text-align: left;">';
						$html .= 'Usuarios: (' . trim($param_tipo_caja_chica_texto) . ')';
						$html .= '</div>';
						$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
							$html .= '<button type="button" class="btn btn-success btn-xs"  title="Agregar todos" style="width: 100px;" onclick="mepa_tesoreria_atencion_asignacion_agregar_varios_a_la_programacion(' . $ids_todos_asignacion . ')">';
							$html .= '<i class="fa fa-plus"></i>';
							$html .= ' Agregar todos';
							$html .= '</button>';
						$html .= '</div>';
					$html .= '</th>';
				$html .= '</tr>';
			} 
			else 
			{
				$html .= '<tr>';
					$html .= '<th colspan="7" style="background-color: #E5E5E5;">';
						$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px" style="text-align: left;">';
						$html .= 'Usuarios que integran la programación de pago:';
						$html .= '</div>';
						$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
							$html .= '<button type="button" class="btn btn-warning btn-xs" title="Quitar todos" style="width: 100px;" onclick="mepa_tesoreria_atencion_asignacion_quitar_varios_a_la_programacion(' . $ids_todos_asignacion . ')">';
							$html .= '<i class="fa fa-minus"></i>';
							$html .= ' Quitar todos';
							$html .= '</button>';
						$html .= '</div>';
					$html .= '</th>';
				$html .= '</tr>';
			}

				$html .= '<tr>';
					$html .= '<th>#</th>';
					$html .= '<th>Usuario Asignado</th>';
					$html .= '<th>DNI</th>';
					$html .= '<th>Banco</th>';
					$html .= '<th>Nº. Cuenta</th>';
					$html .= '<th>Fondo</th>';

					if ($tipo_consulta == '1') 
					{
						$html .= '<th>Agregar</th>';
					} 
					else 
					{
						$html .= '<th>Quitar</th>';
					}

					if($tipo_consulta == '1')
					{
						$html .= '<th>Rechazar</th>';	
					}
				$html .= '</tr>';

			$html .= '</thead>';
			
			$html .= '<tbody>';
				$html .= $tbody;

			if ($tipo_consulta == '2') 
			{
				$html .= '<tr>';
					$html .= '<th colspan="7" style="text-align: right; background-color: #E5E5E5;">';
					$html .= '</th>';
				$html .= '</tr>';
				$html .= '<tr style="font-size: 13px;">';
					$html .= '<th colspan="6" style="text-align: right;">';
						$html .= 'Total Usuarios:';
					$html .= '</th>';
					$html .= '<th style="text-align: right;">';
						$html .= $row_count;
					$html .= '</th>';
				$html .= '</tr>';
				$html .= '<tr style="font-size: 13px;">';
					$html .= '<th colspan="6" style="text-align: right;">';
						$html .= 'Total monto:';
					$html .= '</th>';
					$html .= '<th style="text-align: right;">';
						$html .= number_format($total_monto_programado, 2, '.', ',');
					$html .= '</th>';
				$html .= '</tr>';
			}

			$html .= '</tbody>';
		$html .= '</table>';
	}
	else if ($row_count == 0)
	{
		// NO EXISTEN DATOS PENDIENTE DE PAGOS DE ASIGNACION CAJA CHICA
		$html .= '<table class="table table-bordered" style="font-size: 12px;">';
			$html .= '<thead>';
				$html .= '<tr>';
					$html .= '<th style="background-color: #E5E5E5;">';

					if ($tipo_consulta == '1') 
					{
						$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px; text-align: left;">';
						$html .= 'Usuarios: (' . trim($param_tipo_caja_chica_texto) . ')';
						$html .= '</div>';
					} 
					else 
					{
						$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px" style="text-align: left;">';
						$html .= 'Usuarios que integran la programación de pago:';
						$html .= '</div>';
					}
					$html .= '</th>';
				$html .= '</tr>';
			$html .= '</thead>';
		
			$html .= '<tbody>';
				$html .= '<tr>';

				if ($tipo_consulta == '1') 
				{
					$html .= '<td style="text-align: center;">No existen registros</td>';
				} 
				else 
				{
					$html .= '<td style="text-align: center;">No existen registros</td>';
				}

				$html .= '</tr>';
			$html .= '</tbody>';
		$html .= '</table>';
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
}


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_guardar_asignacion_programacion_de_pago") 
{

	$user_id = $login?$login['id']:null;
	
	if((int) $user_id>0)
	{
		$created_at = date('Y-m-d H:i:s');

		$int_tipo_caja_chica = $_POST["param_tipo_caja_chica"];
		$int_tipo_banco = $_POST["param_tipo_banco"];
		$int_tipo_empresa = $_POST["param_tipo_empresa"];
		$int_tipo_empresa_num_cuenta = $_POST["param_tipo_empresa_num_cuenta"];
		
		$ids_asignacion = $_POST["ids_asignacion"];
		$data_ids_asignacion = json_decode($ids_asignacion);

		$array_detalle_programacion_id = [];


		// INICIO INSERTAR PROGRAMACIÓN
		$query_insert_programacion = "
			INSERT INTO mepa_caja_chica_programacion
			(
				num_cuenta_id,
				empresa_id,
				tipo_solicitud_id,
				tipo_banco,
				fecha_programacion,			
				status,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'" . $int_tipo_empresa_num_cuenta . "',
				'" . $int_tipo_empresa . "',
				'" . $int_tipo_caja_chica . "',
				'" . $int_tipo_banco . "',
				'" . $created_at . "',
				1,
				" . $user_id . ",
				'" . $created_at . "',
				" . $user_id . ",
				'" . $created_at . "'
				)
			";

		$mysqli->query($query_insert_programacion);
		
		$programacion_id = mysqli_insert_id($mysqli);
		
		if($mysqli->error)
		{
			enviar_error($mysqli->error . $query_insert_programacion);
		}
		// FIN INSERTAR PROGRAMACIÓN

		// INICIO INSERTAR DETALLE PROGRAMACIÓN
		$array_detalle_programacion_id = [];

		foreach ($data_ids_asignacion as $value_asignacion_id) 
		{
			// INICIO OBTENER LA CUENTA BANCARIA VIGENTE

			$query_programacion = "
				SELECT
					ac.id AS asignacion_cuenta_bancaria_id,
				    ac.asignacion_id
				FROM mepa_asignacion_caja_chica a
					INNER JOIN mepa_asignacion_cuenta_bancaria ac
					ON ac.asignacion_id = a.id
				WHERE a.id = '".$value_asignacion_id."' AND ac.status = 1
			";

			$list_query_programacion = $mysqli->query($query_programacion);

			$row_count = $list_query_programacion->num_rows;

			if ($row_count > 0) 
			{
				$row = $list_query_programacion->fetch_assoc();
				$asignacion_cuenta_bancaria_id = $row["asignacion_cuenta_bancaria_id"];
				$asignacion_id = $row["asignacion_id"];
			}
			else
			{
				$result["http_code"] = 400;
				$result["result"] = "No existen número de cuenta para dichos usuarios";

				echo json_encode($result);
				exit();
			}

			// FIN OBTENER LA CUENTA BANCARIA VIGENTE


			$query_insert_detalle_programacion = "
				INSERT INTO mepa_caja_chica_programacion_detalle
				(
					mepa_caja_chica_programacion_id,
					nombre_tabla,
					nombre_tabla_id,
					nombre_tabla_cuenta_bancaria_id,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					" . $programacion_id . ",
					'mepa_asignacion_caja_chica',
					" . $value_asignacion_id . ",
					".$asignacion_cuenta_bancaria_id.",
					1,
					" . $user_id . ",
					'" . $created_at . "',
					" . $user_id . ",
					'" . $created_at . "'
				)";

			$mysqli->query($query_insert_detalle_programacion);
			
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $query_insert_detalle_programacion);
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
        $result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }
}


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_guardar_asignacion_cambios_programacion_de_pago") 
{
	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');
	$array_detalle_programacion_id = [];

	if((int) $user_id>0)
	{
		$programacion_id = $_POST["programacion_id_edit"];
		$id_sispags = $_POST["ids_asignacion"];
		$array_asignacion_detalle_nuevos = json_decode($id_sispags);

		// INICIO OBTENER ASIGNACION DETALLE ACTUALES
		$query_detalle_programacion = "
			SELECT 
			    nombre_tabla_id
			FROM mepa_caja_chica_programacion_detalle
			WHERE status = 1 AND mepa_caja_chica_programacion_id = " . $programacion_id . "
		";

		$list_query = $mysqli->query($query_detalle_programacion);

		if($mysqli->error)
		{
			enviar_error($mysqli->error . $query_detalle_programacion);
		}

		$row_count = $list_query->num_rows;

		$array_asignacion_detalle_actuales = array();

		if ($row_count > 0) 
		{
			while ($row = $list_query->fetch_assoc()) 
			{
				array_push($array_asignacion_detalle_actuales, $row["nombre_tabla_id"]);
			}
		}
		// FIN OBTENER ASIGNACION DETALLE ACTUALES

		// INICIO INSERTAR DETALLE PROGRAMACIÓN NUEVOS
		$array_sispag_nuevos_acreedores = array_diff($array_asignacion_detalle_nuevos, $array_asignacion_detalle_actuales);

		foreach ($array_sispag_nuevos_acreedores as $value_sispag_id) 
		{
			// INICIO OBTENER LA CUENTA BANCARIA VIGENTE

			$query_programacion = "
				SELECT
					ac.id AS asignacion_cuenta_bancaria_id,
				    ac.asignacion_id
				FROM mepa_asignacion_caja_chica a
					INNER JOIN mepa_asignacion_cuenta_bancaria ac
					ON ac.asignacion_id = a.id
				WHERE a.id = '".$value_sispag_id."' AND ac.status = 1
			";

			$list_query_programacion = $mysqli->query($query_programacion);

			$row_count = $list_query_programacion->num_rows;

			if ($row_count > 0) 
			{
				$row = $list_query_programacion->fetch_assoc();
				$asignacion_cuenta_bancaria_id = $row["asignacion_cuenta_bancaria_id"];
				$asignacion_id = $row["asignacion_id"];
			}
			else
			{
				$result["http_code"] = 400;
				$result["result"] = "No existen número de cuenta para dichos usuarios";

				echo json_encode($result);
				exit();
			}

			// FIN OBTENER LA CUENTA BANCARIA VIGENTE

			$query_insert_detalle_programacion = "
												INSERT INTO mepa_caja_chica_programacion_detalle
												(
													mepa_caja_chica_programacion_id,
													nombre_tabla,
													nombre_tabla_id,
													nombre_tabla_cuenta_bancaria_id,
													status,
													user_created_id,
													created_at,
													user_updated_id,
													updated_at
												)
												VALUES
												(
													" . $programacion_id . ",
													'mepa_asignacion_caja_chica',
													" . $value_sispag_id . ",
													".$asignacion_cuenta_bancaria_id.",
													1,
													" . $user_id . ",
													'" . $created_at . "',
													" . $user_id . ",
													'" . $created_at . "'
												)
												";
			
			$mysqli->query($query_insert_detalle_programacion);
			
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $query_insert_detalle_programacion);
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN NUEVOS
		
		// INICIO ELIMINAR DETALLE DE LA PROGRAMACION
		$array_sispag_debaja = array_diff($array_asignacion_detalle_actuales, $array_asignacion_detalle_nuevos);
		foreach ($array_sispag_debaja as $value_sispag_id_de_baja) 
		{
			$sql_update_sispag_de_baja = "
				UPDATE mepa_caja_chica_programacion_detalle
				SET
					status = 0,
					user_updated_id = $user_id,
					updated_at = '$created_at'
				WHERE 
					mepa_caja_chica_programacion_id = $programacion_id 
					AND nombre_tabla_id = $value_sispag_id_de_baja;
			";
			
			$mysqli->query($sql_update_sispag_de_baja);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $sql_update_sispag_de_baja);
			}
		}
		// FIN ELIMINAR DETALLE DE LA PROGRAMACION

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
		$result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
	
}


if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_liquidacion_caja_chica_pendientes_de_pago")
{
	// tipo_consulta 1: CREACION DE PROGRAMACION DE PAGOS
	// tipo_consulta 2: MOSTRAR LA ASIGNACION O LIQUIDACION COMO AGREGADOS A LA PROGRAMACION DE PAGOS
	$tipo_consulta = $_POST["tipo_consulta"];
	$param_tipo_caja_chica = $_POST["mepa_tesoreria_atencion_param_tipo_caja_chica"];
	$param_tipo_caja_chica_texto = $_POST["mepa_tesoreria_atencion_param_tipo_caja_chica_texto"];
	$param_tipo_banco = $_POST["mepa_tesoreria_atencion_param_tipo_banco"];
	$param_tipo_empresa = $_POST["mepa_tesoreria_atencion_param_tipo_empresa"];
	$param_ids_liquidacion = $_POST["ids_liquidacion"];

	$contador_array_ids = 0;
	$data_param_ids_liquidacion = json_decode($param_ids_liquidacion);
	
	$ids_liquidacion = '';

	foreach ($data_param_ids_liquidacion as $value) 
	{
		if ($contador_array_ids > 0) 
		{
			$ids_liquidacion .= ',';
		}
		
		$ids_liquidacion .= $value;			
		$contador_array_ids++;
	}

	if($contador_array_ids == 0)
	{
		$ids_liquidacion = 0;
	}


	//LISTAR LOS ID DE LA TABLA: mepa_caja_chica_programacion_detalle el campo: nombre_tabla_id
	$query_id_asignacion_programacion_detalle = "
		SELECT
			nombre_tabla_id
		FROM mepa_caja_chica_programacion_detalle
		WHERE status = 1 AND nombre_tabla = 'mepa_liquidacion_caja_chica'
	";

	$data_id_asignacion_en_programacion_detalle = $mysqli->query($query_id_asignacion_programacion_detalle);

	$ids_asignacion_en_programacion_detalle = '';
	$cont_ids_asignacion = 0;

	while($row = $data_id_asignacion_en_programacion_detalle->fetch_assoc())
	{
		if($cont_ids_asignacion > 0)
		{
			$ids_asignacion_en_programacion_detalle .= ',';
		}

		$ids_asignacion_en_programacion_detalle .= $row["nombre_tabla_id"];

		$cont_ids_asignacion++;
	}
	
	$html = '';
	$tbody = '';
	$ids_todos_asignacion = '';
	$suma_liquidacion = 0;
	$total_monto_programado = 0;

	$where_tipo_banco = "";

	if($param_tipo_banco == 1)
	{
		// SOLO BANCOS BBVA
		$where_tipo_banco = " AND ac.banco_id = 12";

	}
	else if($param_tipo_banco == 2)
	{
		// SOLO BANCOS DIFERENTES A BBVA
		$where_tipo_banco = " AND ac.banco_id != 12";
	}

	$query = "
		SELECT
			l.id, a.usuario_asignado_id, ac.banco_id, tb.nombre AS banco, ac.num_cuenta,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado, tp.dni,
		    l.etapa_id_se_envio_a_tesoreria,
		    ce.situacion AS estado_solicitud,
		    IFNULL(l.sub_total, 0) AS sub_total,
		    l.total_rendicion,
		    m.monto_cierre
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_etapa ce
			ON l.etapa_id_se_envio_a_tesoreria = ce.etapa_id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN mepa_asignacion_cuenta_bancaria ac
			ON ac.asignacion_id = a.id
			INNER JOIN tbl_bancos tb
    		ON ac.banco_id = tb.id
		WHERE l.status = 1 AND a.situacion_etapa_id != 8 AND l.situacion_etapa_id_superior = 6 AND l.situacion_etapa_id_contabilidad = 6 AND l.etapa_id_se_envio_a_tesoreria = 9 AND ac.status = 1 AND a.empresa_id = '".$param_tipo_empresa."'
			".$where_tipo_banco."
	";

	if ($tipo_consulta == '1')
	{
		if($ids_asignacion_en_programacion_detalle != '')
		{
			$query .= " AND l.id NOT IN(" . $ids_asignacion_en_programacion_detalle . ")";
		}

		if ($ids_liquidacion != '') 
		{
			$query .= " AND l.id NOT IN(" . $ids_liquidacion . ")";
		}
	}
	else 
	{
		$query .= " AND l.id IN(" . $ids_liquidacion . ")";
	}
	
	$list_query = $mysqli->query($query);

	if($mysqli->error)
	{
		enviar_error($mysqli->error . $query);
	}

	$row_count = $list_query->num_rows;

	if ($row_count > 0) 
	{
		$num = 1;

		while ($row = $list_query->fetch_assoc()) 
		{
			$suma_liquidacion = $row["sub_total"];

			$tbody .= '<tr>';
				$tbody .= '<td>' . $num . '</td>';
				$tbody .= '<td>' . $row["usuario_asignado"] . '</td>';
				$tbody .= '<td>' . $row["dni"] . '</td>';
				$tbody .= '<td>' . $row["banco"] . '</td>';
				$tbody .= '<td>' . $row["num_cuenta"] . '</td>';
				$tbody .= '<td>' . $row["estado_solicitud"] . '</td>';
				$tbody .= '<td>' . number_format($suma_liquidacion, 2, '.', ',');
				
				$tbody .= '<td>';

				if ($tipo_consulta == '1') 
				{
					$tbody .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Agregar a la programación de pagos" onclick="mepa_tesoreria_atencion_agregar_liquidacion_a_la_programacion_pagos(' . $row["id"] . ')">';
					$tbody .= '<i class="fa fa-plus"></i>';
					$tbody .= '</a>';
				} 
				else 
				{
					$tbody .= '<a class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Quitar de la programación de pagos" onclick="mepa_tesoreria_atencion_quitar_liquidacion_de_la_programacion(' . $row["id"] . ')">';
					$tbody .= '<i class="fa fa-minus"></i>';
					$tbody .= '</a>';
				}
				$tbody .= '</td>';
			$tbody .= '</tr>';

			if ($num == 1) 
			{
				$ids_todos_asignacion .= $row["id"];
			} 
			else 
			{
				$ids_todos_asignacion .= "," . $row["id"];
			}

			$total_monto_programado += $suma_liquidacion;
			$num += 1;
		}

		$html .= '<table class="table table-bordered" style="font-size: 12px;">';
			$html .= '<thead>';

			if ($tipo_consulta == '1') 
			{
				$html .= '<tr>';
					$html .= '<th colspan="8" style="background-color: #E5E5E5;">';
						$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px; text-align: left;">';
						$html .= 'Usuarios: (' . trim($param_tipo_caja_chica_texto) . ')';
						$html .= '</div>';
						$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
							$html .= '<button type="button" class="btn btn-success btn-xs"  title="Agregar todos" style="width: 100px;" onclick="mepa_tesoreria_atencion_liquidacion_agregar_varios_a_la_programacion(' . $ids_todos_asignacion . ')">';
							$html .= '<i class="fa fa-plus"></i>';
							$html .= ' Agregar todos';
							$html .= '</button>';
						$html .= '</div>';
					$html .= '</th>';
				$html .= '</tr>';
			} 
			else 
			{
				$html .= '<tr>';
					$html .= '<th colspan="8" style="background-color: #E5E5E5;">';
						$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px" style="text-align: left;">';
						$html .= 'Usuarios que integran la programación de pago:';
						$html .= '</div>';
						$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
							$html .= '<button type="button" class="btn btn-warning btn-xs" title="Quitar todos" style="width: 100px;" onclick="mepa_tesoreria_atencion_liquidacion_quitar_varios_a_la_programacion(' . $ids_todos_asignacion . ')">';
							$html .= '<i class="fa fa-minus"></i>';
							$html .= ' Quitar todos';
							$html .= '</button>';
						$html .= '</div>';
					$html .= '</th>';
				$html .= '</tr>';
			}

				$html .= '<tr>';
					$html .= '<th>#</th>';
					$html .= '<th>Usuario Asignado</th>';
					$html .= '<th>DNI</th>';
					$html .= '<th>Banco</th>';
					$html .= '<th>Nº. Cuenta</th>';
					$html .= '<th>Situación</th>';
					$html .= '<th>Monto</th>';

					if ($tipo_consulta == '1') 
					{
						$html .= '<th>Agregar</th>';
					} 
					else 
					{
						$html .= '<th>Quitar</th>';
					}
				$html .= '</tr>';

			$html .= '</thead>';
			
			$html .= '<tbody>';
				$html .= $tbody;

			if ($tipo_consulta == '2') 
			{
				$html .= '<tr>';
					$html .= '<th colspan="8" style="text-align: right; background-color: #E5E5E5;">';
					$html .= '</th>';
				$html .= '</tr>';
				$html .= '<tr style="font-size: 13px;">';
					$html .= '<th colspan="7" style="text-align: right;">';
						$html .= 'Total Usuarios:';
					$html .= '</th>';
					$html .= '<th style="text-align: right;">';
						$html .= $row_count;
					$html .= '</th>';
				$html .= '</tr>';
				$html .= '<tr style="font-size: 13px;">';
					$html .= '<th colspan="7" style="text-align: right;">';
						$html .= 'Total monto:';
					$html .= '</th>';
					$html .= '<th style="text-align: right;">';
						$html .= number_format($total_monto_programado, 2, '.', ',');
					$html .= '</th>';
				$html .= '</tr>';
			}

			$html .= '</tbody>';
		$html .= '</table>';
	}
	else if ($row_count == 0)
	{
		// NO EXISTEN DATOS PENDIENTE DE PAGOS DE ASIGNACION CAJA CHICA
		$html .= '<table class="table table-bordered" style="font-size: 12px;">';
			$html .= '<thead>';
				$html .= '<tr>';
					$html .= '<th style="background-color: #E5E5E5;">';

					if ($tipo_consulta == '1') 
					{
						$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px; text-align: left;">';
						$html .= 'Usuarios: (' . trim($param_tipo_caja_chica_texto) . ')';
						$html .= '</div>';
					} 
					else 
					{
						$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px" style="text-align: left;">';
						$html .= 'Usuarios que integran la programación de pago:';
						$html .= '</div>';
					}
					$html .= '</th>';
				$html .= '</tr>';
			$html .= '</thead>';
		
			$html .= '<tbody>';
				$html .= '<tr>';

				if ($tipo_consulta == '1') 
				{
					$html .= '<td style="text-align: center;">No existen registros</td>';
				} 
				else 
				{
					$html .= '<td style="text-align: center;">No existen registros</td>';
				}

				$html .= '</tr>';
			$html .= '</tbody>';
		$html .= '</table>';
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
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_guardar_liquidacion_programacion_de_pago") 
{

	$user_id = $login?$login['id']:null;
	
	if((int) $user_id>0)
	{
		$created_at = date('Y-m-d H:i:s');

		$int_tipo_caja_chica = $_POST["param_tipo_caja_chica"];
		$int_tipo_banco = $_POST["param_tipo_banco"];
		$int_tipo_empresa = $_POST["param_tipo_empresa"];
		$int_tipo_empresa_num_cuenta = $_POST["param_tipo_empresa_num_cuenta"];
		
		$ids_liquidacion = $_POST["ids_liquidacion"];
		$data_ids_liquidacion = json_decode($ids_liquidacion);

		$array_detalle_programacion_id = [];

		// INICIO OBTENER NUMERO DE CUENTA, PARAM: EMPRESA, BANCO, MONEDA = 1 (SOLES)

		// INICIO INSERTAR PROGRAMACIÓN
		$query_insert_programacion = "
			INSERT INTO mepa_caja_chica_programacion
			(
				num_cuenta_id,
				empresa_id,
				tipo_solicitud_id,
				tipo_banco,
				fecha_programacion,
				status,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'" . $int_tipo_empresa_num_cuenta . "',
				'" . $int_tipo_empresa . "',
				'" . $int_tipo_caja_chica . "',
				'" . $int_tipo_banco . "',
				'" . $created_at . "',
				1,
				" . $user_id . ",
				'" . $created_at . "',
				" . $user_id . ",
				'" . $created_at . "'
				)
			";

		$mysqli->query($query_insert_programacion);
		
		$programacion_id = mysqli_insert_id($mysqli);
		
		if($mysqli->error)
		{
			enviar_error($mysqli->error . $query_insert_programacion);
		}
		// FIN INSERTAR PROGRAMACIÓN

		// INICIO INSERTAR DETALLE PROGRAMACIÓN
		$array_detalle_programacion_id = [];

		foreach ($data_ids_liquidacion as $value_liquidacion_id) 
		{
			// INICIO OBTENER LA CUENTA BANCARIA VIGENTE

			$query_programacion = "
				SELECT
					ac.id AS asignacion_cuenta_bancaria_id,
					ac.asignacion_id
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN mepa_asignacion_caja_chica a
				    ON l.asignacion_id = a.id
					INNER JOIN mepa_asignacion_cuenta_bancaria ac
					ON ac.asignacion_id = a.id
				WHERE l.id = '".$value_liquidacion_id."' AND ac.status = 1
			";

			$list_query_programacion = $mysqli->query($query_programacion);

			$row_count = $list_query_programacion->num_rows;

			if ($row_count > 0) 
			{
				$row = $list_query_programacion->fetch_assoc();
				$asignacion_cuenta_bancaria_id = $row["asignacion_cuenta_bancaria_id"];
				$asignacion_id = $row["asignacion_id"];
			}
			else
			{
				$result["http_code"] = 400;
				$result["result"] = "No existen número de cuenta para dichos usuarios";

				echo json_encode($result);
				exit();
			}

			// FIN OBTENER LA CUENTA BANCARIA VIGENTE

			$query_insert_detalle_programacion = "
												INSERT INTO mepa_caja_chica_programacion_detalle
												(
													mepa_caja_chica_programacion_id,
													nombre_tabla,
													nombre_tabla_id,
													nombre_tabla_cuenta_bancaria_id,
													status,
													user_created_id,
													created_at,
													user_updated_id,
													updated_at
												)
												VALUES
												(
													" . $programacion_id . ",
													'mepa_liquidacion_caja_chica',
													" . $value_liquidacion_id . ",
													".$asignacion_cuenta_bancaria_id.",
													1,
													" . $user_id . ",
													'" . $created_at . "',
													" . $user_id . ",
													'" . $created_at . "'
												)";

			$mysqli->query($query_insert_detalle_programacion);
			
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $query_insert_detalle_programacion);
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
        $result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_guardar_liquidacion_cambios_programacion_de_pago") 
{
	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');
	$array_detalle_programacion_id = [];

	if((int) $user_id>0)
	{
		$programacion_id = $_POST["programacion_id_edit"];
		$ids_liquidacion = $_POST["ids_liquidacion"];
		$array_liquidacion_detalle_nuevos = json_decode($ids_liquidacion);

		// INICIO OBTENER ASIGNACION DETALLE ACTUALES
		$query_detalle_programacion = "
			SELECT 
			    nombre_tabla_id
			FROM mepa_caja_chica_programacion_detalle
			WHERE status = 1 AND mepa_caja_chica_programacion_id = " . $programacion_id . "
		";

		$list_query = $mysqli->query($query_detalle_programacion);

		if($mysqli->error)
		{
			enviar_error($mysqli->error . $query_detalle_programacion);
		}

		$row_count = $list_query->num_rows;

		$array_liquidacion_detalle_actuales = array();

		if ($row_count > 0) 
		{
			while ($row = $list_query->fetch_assoc()) 
			{
				array_push($array_liquidacion_detalle_actuales, $row["nombre_tabla_id"]);
			}
		}
		// FIN OBTENER ASIGNACION DETALLE ACTUALES

		// INICIO INSERTAR DETALLE PROGRAMACIÓN NUEVOS
		$array_liquidacion_nuevos = array_diff($array_liquidacion_detalle_nuevos, $array_liquidacion_detalle_actuales);
		foreach ($array_liquidacion_nuevos as $value_liquidacion_id) 
		{

			// INICIO OBTENER LA CUENTA BANCARIA VIGENTE

			$query_programacion = "
				SELECT
					ac.id AS asignacion_cuenta_bancaria_id,
					ac.asignacion_id
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN mepa_asignacion_caja_chica a
				    ON l.asignacion_id = a.id
					INNER JOIN mepa_asignacion_cuenta_bancaria ac
					ON ac.asignacion_id = a.id
				WHERE l.id = '".$value_liquidacion_id."' AND ac.status = 1
			";

			$list_query_programacion = $mysqli->query($query_programacion);

			$row_count = $list_query_programacion->num_rows;

			if ($row_count > 0) 
			{
				$row = $list_query_programacion->fetch_assoc();
				$asignacion_cuenta_bancaria_id = $row["asignacion_cuenta_bancaria_id"];
				$asignacion_id = $row["asignacion_id"];
			}
			else
			{
				$result["http_code"] = 400;
				$result["result"] = "No existen número de cuenta para dichos usuarios";

				echo json_encode($result);
				exit();
			}

			// FIN OBTENER LA CUENTA BANCARIA VIGENTE

			$query_insert_detalle_programacion = "
												INSERT INTO mepa_caja_chica_programacion_detalle
												(
													mepa_caja_chica_programacion_id,
													nombre_tabla,
													nombre_tabla_id,
													nombre_tabla_cuenta_bancaria_id,
													status,
													user_created_id,
													created_at,
													user_updated_id,
													updated_at
												)
												VALUES
												(
													" . $programacion_id . ",
													'mepa_liquidacion_caja_chica',
													" . $value_liquidacion_id . ",
													".$asignacion_cuenta_bancaria_id.",
													1,
													" . $user_id . ",
													'" . $created_at . "',
													" . $user_id . ",
													'" . $created_at . "'
												)
												";
			
			$mysqli->query($query_insert_detalle_programacion);
			
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $query_insert_detalle_programacion);
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN NUEVOS

		// INICIO ELIMINAR DETALLE DE LA PROGRAMACION
		$array_liquidacion_debaja = array_diff($array_liquidacion_detalle_actuales, $array_liquidacion_detalle_nuevos);
		foreach ($array_liquidacion_debaja as $value_liquidacion_id_de_baja) 
		{
			$sql_update_sispag_de_baja = "
				UPDATE mepa_caja_chica_programacion_detalle
				SET
					status = 0,
					user_updated_id = $user_id,
					updated_at = '$created_at'
				WHERE 
					mepa_caja_chica_programacion_id = $programacion_id 
					AND nombre_tabla_id = $value_liquidacion_id_de_baja;
			";
			
			$mysqli->query($sql_update_sispag_de_baja);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $sql_update_sispag_de_baja);
			}
		}
		// FIN ELIMINAR DETALLE DE LA PROGRAMACION
		
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
        $result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }
}

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_aumento_asignacion_caja_chica_pendientes_de_pago")
{
	// tipo_consulta 1: CREACION DE PROGRAMACION DE PAGOS
	// tipo_consulta 2: MOSTRAR LA ASIGNACION COMO PROGRAMACION DE PAGOS
	$tipo_consulta = $_POST["tipo_consulta"];

	$param_tipo_caja_chica = $_POST["mepa_tesoreria_atencion_param_tipo_caja_chica"];
	$param_tipo_caja_chica_texto = $_POST["mepa_tesoreria_atencion_param_tipo_caja_chica_texto"];
	$param_tipo_banco = $_POST["mepa_tesoreria_atencion_param_tipo_banco"];
	$param_tipo_empresa = $_POST["mepa_tesoreria_atencion_param_tipo_empresa"];
	$param_ids_aumento_asignacion = $_POST["ids_aumento_asignacion"];

	$contador_array_ids = 0;
	$data_param_ids_aumento_asignacion = json_decode($param_ids_aumento_asignacion);
	
	$ids_aumento_asignacion = '';

	foreach ($data_param_ids_aumento_asignacion as $value) 
	{
		if ($contador_array_ids > 0) 
		{
			$ids_aumento_asignacion .= ',';
		}
		
		$ids_aumento_asignacion .= $value;			
		$contador_array_ids++;
	}

	if($contador_array_ids == 0)
	{
		$ids_aumento_asignacion = 0;
	}


	//LISTAR LOS ID DE LA TABLA: mepa_caja_chica_programacion_detalle el campo: nombre_tabla_id
	$query_id_aumento_asignacion_programacion_detalle = "
		SELECT
			nombre_tabla_id
		FROM mepa_caja_chica_programacion_detalle
		WHERE status = 1 AND nombre_tabla = 'mepa_aumento_asignacion_caja_chica'
	";

	$data_id_aumento_asignacion_en_programacion_detalle = $mysqli->query($query_id_aumento_asignacion_programacion_detalle);

	$ids_aumento_asignacion_en_programacion_detalle = '';
	$cont_ids_aumento_asignacion = 0;

	while($row = $data_id_aumento_asignacion_en_programacion_detalle->fetch_assoc())
	{
		if($cont_ids_aumento_asignacion > 0)
		{
			$ids_aumento_asignacion_en_programacion_detalle .= ',';
		}

		$ids_aumento_asignacion_en_programacion_detalle .= $row["nombre_tabla_id"];

		$cont_ids_aumento_asignacion++;
	}
	
	$html = '';
	$tbody = '';
	$ids_todos_aumento_asignacion = '';
	$total_monto_programado = 0;

	$where_tipo_banco = "";

	if($param_tipo_banco == 1)
	{
		// SOLO BANCOS BBVA
		$where_tipo_banco = " AND ac.banco_id = 12";

	}
	else if($param_tipo_banco == 2)
	{
		// SOLO BANCOS DIFERENTES A BBVA
		$where_tipo_banco = " AND ac.banco_id != 12";
	}

	$query = "
		SELECT
			a.id, ma.usuario_asignado_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado, tp.dni, ma.situacion_etapa_id, ce.situacion AS estado_solicitud, 
		    a.monto, ac.banco_id, tb.nombre AS banco, ac.num_cuenta
		FROM mepa_aumento_asignacion a
			INNER JOIN mepa_asignacion_caja_chica ma
    		ON a.asignacion_id = ma.id
			INNER JOIN tbl_usuarios tu
			ON ma.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_etapa ce
			ON a.situacion_etapa_id = ce.etapa_id
			INNER JOIN mepa_asignacion_cuenta_bancaria ac
			ON ac.asignacion_id = ma.id AND ac.status = 1
			INNER JOIN tbl_bancos tb
    		ON ac.banco_id = tb.id
		WHERE ma.status = 1 AND ma.tipo_solicitud_id = 1 
				AND ma.situacion_etapa_id = 6 
				AND a.situacion_etapa_id = 6 
				AND a.situacion_tesoreria_etapa_id = 10
				AND a.status = 1 AND a.tipo_solicitud_id = 9 AND ac.status = 1 
				AND ma.empresa_id = '".$param_tipo_empresa."'
				".$where_tipo_banco."
	";

	if ($tipo_consulta == '1')
	{
		if($ids_aumento_asignacion_en_programacion_detalle != '')
		{
			$query .= " AND a.id NOT IN(" . $ids_aumento_asignacion_en_programacion_detalle . ")";
		}

		if ($ids_aumento_asignacion != '') 
		{
			$query .= " AND a.id NOT IN(" . $ids_aumento_asignacion . ")";
		}
	}
	else 
	{
		$query .= " AND a.id IN(" . $ids_aumento_asignacion . ")";
	}
	
	$list_query = $mysqli->query($query);

	if($mysqli->error)
	{
		enviar_error($mysqli->error . $query);
	}

	$row_count = $list_query->num_rows;

	if ($row_count > 0) 
	{
		$num = 1;

		while ($row = $list_query->fetch_assoc()) 
		{
			$tbody .= '<tr>';
				$tbody .= '<td>' . $num . '</td>';
				$tbody .= '<td>' . $row["usuario_asignado"] . '</td>';
				$tbody .= '<td>' . $row["dni"] . '</td>';
				$tbody .= '<td>' . $row["banco"] . '</td>';
				$tbody .= '<td>' . $row["num_cuenta"] . '</td>';
				$tbody .= '<td>' . $row["monto"] . '</td>';
				
				$tbody .= '<td>';

				if ($tipo_consulta == '1') 
				{
					$tbody .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Agregar a la programación de pagos" onclick="mepa_tesoreria_atencion_agregar_aumento_asignacion_a_la_programacion_pagos(' . $row["id"] . ')">';
					$tbody .= '<i class="fa fa-plus"></i>';
					$tbody .= '</a>';
				} 
				else 
				{
					$tbody .= '<a class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Quitar de la programación de pagos" onclick="mepa_tesoreria_atencion_quitar_aumento_asignacion_de_la_programacion(' . $row["id"] . ')">';
					$tbody .= '<i class="fa fa-minus"></i>';
					$tbody .= '</a>';
				}
				$tbody .= '</td>';
				$tbody .= '<td>';
				if($tipo_consulta == '1')
				{
					$tbody .= '<button type="button" 
                		class="btn btn-danger btn-xs btn-block" onclick="mepa_tesoreria_atencion_rechazar_aumento_asignacion('.$row["id"].');">';
						$tbody .= '<i class="icon fa fa-bell"></i>';
						$tbody .= '<span id="demo-button-text">';
							$tbody .= 'Rechazar';
						$tbody .= '</span>';
					$tbody .= '</button>';
				}
				$tbody .= '</td>';
			$tbody .= '</tr>';

			if ($num == 1) 
			{
				$ids_todos_aumento_asignacion .= $row["id"];
			} 
			else 
			{
				$ids_todos_aumento_asignacion .= "," . $row["id"];
			}

			$total_monto_programado += $row["monto"];
			$num += 1;
		}

		$html .= '<table class="table table-bordered" style="font-size: 12px;">';
			$html .= '<thead>';

			if ($tipo_consulta == '1') 
			{
				$html .= '<tr>';
					$html .= '<th colspan="8" style="background-color: #E5E5E5;">';
						$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px; text-align: left;">';
						$html .= 'Usuarios: (' . trim($param_tipo_caja_chica_texto) . ')';
						$html .= '</div>';
						$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
							$html .= '<button type="button" class="btn btn-success btn-xs"  title="Agregar todos" style="width: 100px;" onclick="mepa_tesoreria_atencion_aumento_asignacion_agregar_varios_a_la_programacion(' . $ids_todos_aumento_asignacion . ')">';
							$html .= '<i class="fa fa-plus"></i>';
							$html .= ' Agregar todos';
							$html .= '</button>';
						$html .= '</div>';
					$html .= '</th>';
				$html .= '</tr>';
			} 
			else 
			{
				$html .= '<tr>';
					$html .= '<th colspan="7" style="background-color: #E5E5E5;">';
						$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px" style="text-align: left;">';
						$html .= 'Usuarios que integran la programación de pago:';
						$html .= '</div>';
						$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
							$html .= '<button type="button" class="btn btn-warning btn-xs" title="Quitar todos" style="width: 100px;" onclick="mepa_tesoreria_atencion_aumento_asignacion_quitar_varios_a_la_programacion(' . $ids_todos_aumento_asignacion . ')">';
							$html .= '<i class="fa fa-minus"></i>';
							$html .= ' Quitar todos';
							$html .= '</button>';
						$html .= '</div>';
					$html .= '</th>';
				$html .= '</tr>';
			}

				$html .= '<tr>';
					$html .= '<th>#</th>';
					$html .= '<th>Usuario Asignado</th>';
					$html .= '<th>DNI</th>';
					$html .= '<th>Banco</th>';
					$html .= '<th>Nº. Cuenta</th>';
					$html .= '<th>Aumento</th>';

					if ($tipo_consulta == '1') 
					{
						$html .= '<th>Agregar</th>';
					} 
					else 
					{
						$html .= '<th>Quitar</th>';
					}
					if($tipo_consulta == '1')
					{
						$html .= '<th>Rechazar</th>';	
					}
				$html .= '</tr>';

			$html .= '</thead>';
			
			$html .= '<tbody>';
				$html .= $tbody;

			if ($tipo_consulta == '2') 
			{
				$html .= '<tr>';
					$html .= '<th colspan="7" style="text-align: right; background-color: #E5E5E5;">';
					$html .= '</th>';
				$html .= '</tr>';
				$html .= '<tr style="font-size: 13px;">';
					$html .= '<th colspan="6" style="text-align: right;">';
						$html .= 'Total Usuarios:';
					$html .= '</th>';
					$html .= '<th style="text-align: right;">';
						$html .= $row_count;
					$html .= '</th>';
				$html .= '</tr>';
				$html .= '<tr style="font-size: 13px;">';
					$html .= '<th colspan="6" style="text-align: right;">';
						$html .= 'Total monto:';
					$html .= '</th>';
					$html .= '<th style="text-align: right;">';
						$html .= number_format($total_monto_programado, 2, '.', ',');
					$html .= '</th>';
				$html .= '</tr>';
			}

			$html .= '</tbody>';
		$html .= '</table>';
	}
	else if ($row_count == 0)
	{
		// NO EXISTEN DATOS PENDIENTE DE PAGOS DE ASIGNACION CAJA CHICA
		$html .= '<table class="table table-bordered" style="font-size: 12px;">';
			$html .= '<thead>';
				$html .= '<tr>';
					$html .= '<th style="background-color: #E5E5E5;">';

					if ($tipo_consulta == '1') 
					{
						$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px; text-align: left;">';
						$html .= 'Usuarios: (' . trim($param_tipo_caja_chica_texto) . ')';
						$html .= '</div>';
					} 
					else 
					{
						$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px" style="text-align: left;">';
						$html .= 'Usuarios que integran la programación de pago:';
						$html .= '</div>';
					}
					$html .= '</th>';
				$html .= '</tr>';
			$html .= '</thead>';
		
			$html .= '<tbody>';
				$html .= '<tr>';

				if ($tipo_consulta == '1') 
				{
					$html .= '<td style="text-align: center;">No existen registros</td>';
				} 
				else 
				{
					$html .= '<td style="text-align: center;">No existen registros</td>';
				}

				$html .= '</tr>';
			$html .= '</tbody>';
		$html .= '</table>';
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
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_guardar_aumento_asignacion_programacion_de_pago") 
{

	$user_id = $login?$login['id']:null;
	
	if((int) $user_id>0)
	{
		$created_at = date('Y-m-d H:i:s');

		$int_tipo_caja_chica = $_POST["param_tipo_caja_chica"];
		$int_tipo_banco = $_POST["param_tipo_banco"];
		$int_tipo_empresa = $_POST["param_tipo_empresa"];
		$int_tipo_empresa_num_cuenta = $_POST["param_tipo_empresa_num_cuenta"];
		
		$ids_aumento_asignacion = $_POST["ids_aumento_asignacion"];
		$data_ids_aumento_asignacion = json_decode($ids_aumento_asignacion);

		$array_detalle_programacion_id = [];


		// INICIO INSERTAR PROGRAMACIÓN
		$query_insert_programacion = "
			INSERT INTO mepa_caja_chica_programacion
			(
				num_cuenta_id,
				empresa_id,
				tipo_solicitud_id,
				tipo_banco,
				fecha_programacion,			
				status,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'" . $int_tipo_empresa_num_cuenta . "',
				'" . $int_tipo_empresa . "',
				'" . $int_tipo_caja_chica . "',
				'" . $int_tipo_banco . "',
				'" . $created_at . "',
				1,
				" . $user_id . ",
				'" . $created_at . "',
				" . $user_id . ",
				'" . $created_at . "'
				)
			";

		$mysqli->query($query_insert_programacion);
		
		$programacion_id = mysqli_insert_id($mysqli);
		
		if($mysqli->error)
		{
			enviar_error($mysqli->error . $query_insert_programacion);
		}
		// FIN INSERTAR PROGRAMACIÓN

		// INICIO INSERTAR DETALLE PROGRAMACIÓN
		$array_detalle_programacion_id = [];

		foreach ($data_ids_aumento_asignacion as $value_aumento_asignacion_id) 
		{
			// INICIO OBTENER LA CUENTA BANCARIA VIGENTE

			$query_programacion = "
				SELECT
					ac.id AS asignacion_cuenta_bancaria_id,
					ac.asignacion_id
				FROM mepa_aumento_asignacion aa
					INNER JOIN mepa_asignacion_caja_chica a
				    ON aa.asignacion_id = a.id
					INNER JOIN mepa_asignacion_cuenta_bancaria ac
					ON ac.asignacion_id = a.id
				WHERE aa.id = '".$value_aumento_asignacion_id."' AND ac.status = 1
			";

			$list_query_programacion = $mysqli->query($query_programacion);

			$row_count = $list_query_programacion->num_rows;

			if ($row_count > 0) 
			{
				$row = $list_query_programacion->fetch_assoc();
				$asignacion_cuenta_bancaria_id = $row["asignacion_cuenta_bancaria_id"];
				$asignacion_id = $row["asignacion_id"];
			}
			else
			{
				$result["http_code"] = 400;
				$result["result"] = "No existen número de cuenta para dichos usuarios";

				echo json_encode($result);
				exit();
			}

			// FIN OBTENER LA CUENTA BANCARIA VIGENTE

			$query_insert_detalle_programacion = "
				INSERT INTO mepa_caja_chica_programacion_detalle
				(
					mepa_caja_chica_programacion_id,
					nombre_tabla,
					nombre_tabla_id,
					nombre_tabla_cuenta_bancaria_id,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					" . $programacion_id . ",
					'mepa_aumento_asignacion_caja_chica',
					" . $value_aumento_asignacion_id . ",
					".$asignacion_cuenta_bancaria_id.",
					1,
					" . $user_id . ",
					'" . $created_at . "',
					" . $user_id . ",
					'" . $created_at . "'
				)";

			$mysqli->query($query_insert_detalle_programacion);
			
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $query_insert_detalle_programacion);
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
        $result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_guardar_aumento_asignacion_cambios_programacion_de_pago") 
{
	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');
	$array_detalle_programacion_id = [];

	if((int) $user_id>0)
	{
		$programacion_id = $_POST["programacion_id_edit"];
		$id_aumento_asignacion = $_POST["ids_aumento_asignacion"];
		$array_aumento_asignacion_detalle_nuevos = json_decode($id_aumento_asignacion);

		// INICIO OBTENER AUMENTO ASIGNACION DETALLE ACTUALES
		$query_detalle_programacion = "
			SELECT 
			    nombre_tabla_id
			FROM mepa_caja_chica_programacion_detalle
			WHERE status = 1 AND mepa_caja_chica_programacion_id = " . $programacion_id . "
		";

		$list_query = $mysqli->query($query_detalle_programacion);

		if($mysqli->error)
		{
			enviar_error($mysqli->error . $query_detalle_programacion);
		}

		$row_count = $list_query->num_rows;

		$array_aumento_asignacion_detalle_actuales = array();

		if ($row_count > 0) 
		{
			while ($row = $list_query->fetch_assoc()) 
			{
				array_push($array_aumento_asignacion_detalle_actuales, $row["nombre_tabla_id"]);
			}
		}
		// FIN OBTENER AUMENTO ASIGNACION DETALLE ACTUALES

		// INICIO INSERTAR DETALLE PROGRAMACIÓN NUEVOS
		$array_nuevos_ids = array_diff($array_aumento_asignacion_detalle_nuevos, $array_aumento_asignacion_detalle_actuales);
		foreach ($array_nuevos_ids as $value_id) 
		{

			// INICIO OBTENER LA CUENTA BANCARIA VIGENTE

			$query_programacion = "
				SELECT
					ac.id AS asignacion_cuenta_bancaria_id,
					ac.asignacion_id
				FROM mepa_aumento_asignacion aa
					INNER JOIN mepa_asignacion_caja_chica a
				    ON aa.asignacion_id = a.id
					INNER JOIN mepa_asignacion_cuenta_bancaria ac
					ON ac.asignacion_id = a.id
				WHERE aa.id = '".$value_id."' AND ac.status = 1
			";

			$list_query_programacion = $mysqli->query($query_programacion);

			$row_count = $list_query_programacion->num_rows;

			if ($row_count > 0) 
			{
				$row = $list_query_programacion->fetch_assoc();
				$asignacion_cuenta_bancaria_id = $row["asignacion_cuenta_bancaria_id"];
				$asignacion_id = $row["asignacion_id"];
			}
			else
			{
				$result["http_code"] = 400;
				$result["result"] = "No existen número de cuenta para dichos usuarios";

				echo json_encode($result);
				exit();
			}

			// FIN OBTENER LA CUENTA BANCARIA VIGENTE

			$query_insert_detalle_programacion = "
												INSERT INTO mepa_caja_chica_programacion_detalle
												(
													mepa_caja_chica_programacion_id,
													nombre_tabla,
													nombre_tabla_id,
													nombre_tabla_cuenta_bancaria_id,
													status,
													user_created_id,
													created_at,
													user_updated_id,
													updated_at
												)
												VALUES
												(
													" . $programacion_id . ",
													'mepa_aumento_asignacion_caja_chica',
													" . $value_id . ",
													".$asignacion_cuenta_bancaria_id.",
													1,
													" . $user_id . ",
													'" . $created_at . "',
													" . $user_id . ",
													'" . $created_at . "'
												)
												";
			
			$mysqli->query($query_insert_detalle_programacion);
			
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $query_insert_detalle_programacion);
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN NUEVOS

		// INICIO ELIMINAR DETALLE DE LA PROGRAMACION
		$array_aumento_asignacion_debaja = array_diff($array_aumento_asignacion_detalle_actuales, $array_aumento_asignacion_detalle_nuevos);
		foreach ($array_aumento_asignacion_debaja as $value_id_de_baja) 
		{
			$sql_update_aumento_asignacion_de_baja = "
				UPDATE mepa_caja_chica_programacion_detalle
				SET
					status = 0,
					user_updated_id = $user_id,
					updated_at = '$created_at'
				WHERE 
					mepa_caja_chica_programacion_id = $programacion_id 
					AND nombre_tabla_id = $value_id_de_baja;
			";
			
			$mysqli->query($sql_update_aumento_asignacion_de_baja);
			
			if($mysqli->error)
			{
				enviar_error($mysqli->error . $sql_update_aumento_asignacion_de_baja);
			}
		}
		// FIN ELIMINAR DETALLE DE LA PROGRAMACION

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
		$result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_rechazar_asignacion") 
{
	$user_id = $login?$login['id']:null;

	if((int) $user_id > 0)
	{
		$error = '';

		$id_asignacion = $_POST['id_asignacion'];
		$motivo_rechazo = $_POST['motivo_rechazo'];

		$query_update = "
					UPDATE mepa_asignacion_caja_chica
						SET usuario_tesoreria_atencion_id = '".$user_id."',
							situacion_etapa_id_tesoreria = 7,
							motivo_atencion_tesoreria = '".$motivo_rechazo."'
					WHERE id = '".$id_asignacion."'
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
			$respuesta_email = mepa_tesoreria_atencion_send_email_rechazo_asignacion($id_asignacion);

			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["error"] = $error;
			$result["respuesta_email"] = $respuesta_email;
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
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
	
}

function mepa_tesoreria_atencion_send_email_rechazo_asignacion($asignacion_caja_chica_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	$sel_query = $mysqli->query("
	SELECT
		ma.id, mts.nombre AS tipo_solicitud, concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_asignado,
		concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, '')) AS usuario_solicitante,
	    tc.nombre AS usuario_asignado_cargo, 
	    ce.situacion AS situacion_tesoreria, 
	    ma.motivo_atencion_tesoreria,
	    ma.usuario_asignado_id, ma.user_created_id,
	    concat(IFNULL(tpt.nombre, ''),' ', IFNULL(tpt.apellido_paterno, '')) AS usuario_atencion_tesoreria
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
		ON ma.situacion_etapa_id_tesoreria = ce.etapa_id
		LEFT JOIN tbl_usuarios tut
		ON ma.usuario_tesoreria_atencion_id = tut.id
		LEFT JOIN tbl_personal_apt tpt
		ON tut.personal_id = tpt.id
	WHERE ma.id = '".$asignacion_caja_chica_id."'
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
		$situacion_tesoreria = $sel['situacion_tesoreria'];
		$motivo_atencion_tesoreria = $sel['motivo_atencion_tesoreria'];
		$usuario_asignado_id = $sel['usuario_asignado_id'];
		$user_created_id = $sel['user_created_id'];
		
		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';
		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Solicitud Rechazada</b>';
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
			$body .= '<td style="background-color: #ffffdd"><b>Situación:</b></td>';
			$body .= '<td>'.$sel["situacion_tesoreria"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Área Atención:</b></td>';
			$body .= '<td>Tesorería</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario Atención:</b></td>';
			$body .= '<td>'.$sel["usuario_atencion_tesoreria"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'.$sel["motivo_atencion_tesoreria"].'</td>';
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

	$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Asignación Caja Chica Rechazada ID: ".$asignacion_caja_chica_id;
	
	$cc = [
		
	];

	$select_usuarios_enviar_a = 
		"
			SELECT 
				p.correo
			FROM tbl_personal_apt p
				INNER JOIN tbl_usuarios u
				ON p.id = u.personal_id
			WHERE u.id IN ('".$usuario_asignado_id."', '".$user_created_id."')
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
		return $respuesta_email = true;

	} 
	catch (Exception $e) 
	{
		return $respuesta_email = $e;
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_atencion_rechazar_aumento_asignacion") 
{
	$user_id = $login?$login['id']:null;

	if((int) $user_id > 0)
	{
		$error = '';

		$id_aumento = $_POST['id_aumento'];
		$motivo_rechazo = $_POST['motivo_rechazo'];

		$query_update = "
					UPDATE mepa_aumento_asignacion
						SET usuario_tesoreria_atencion_id = '".$user_id."',
							situacion_tesoreria_etapa_id = 7,
							motivo_atencion_tesoreria = '".$motivo_rechazo."'
					WHERE id = '".$id_aumento."'
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
			$respuesta_email = mepa_tesoreria_atencion_send_email_rechazo_aumento_asignacion($id_aumento);

			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["error"] = $error;
			$result["respuesta_email"] = $respuesta_email;
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
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
	
}

function mepa_tesoreria_atencion_send_email_rechazo_aumento_asignacion($aumento_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	$sel_query = $mysqli->query("
	SELECT
		a.id, a.tipo_solicitud_id, mts.nombre AS tipo_solicitud,
	    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_asignado,
		concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, '')) AS usuario_solicitante,
	    ce.situacion AS situacion_tesoreria,
	    concat(IFNULL(tpt.nombre, ''),' ', IFNULL(tpt.apellido_paterno, '')) AS usuario_atencion_tesoreria,
	    a.motivo_atencion_tesoreria,
	    ma.usuario_asignado_id, ma.user_created_id
	FROM mepa_aumento_asignacion a
		INNER JOIN mepa_asignacion_caja_chica ma
		ON a.asignacion_id = ma.id
		INNER JOIN mepa_tipos_solicitud mts
		ON a.tipo_solicitud_id = mts.id
		INNER JOIN tbl_usuarios tu
		ON ma.usuario_asignado_id = tu.id
		INNER JOIN tbl_personal_apt tp
		ON tu.personal_id = tp.id
		INNER JOIN tbl_usuarios tus
		ON ma.user_created_id = tus.id
		INNER JOIN tbl_personal_apt tps
		ON tus.personal_id = tps.id
		INNER JOIN cont_etapa ce
		ON a.situacion_tesoreria_etapa_id = ce.etapa_id
		LEFT JOIN tbl_usuarios tut
		ON a.usuario_tesoreria_atencion_id = tut.id
		LEFT JOIN tbl_personal_apt tpt
		ON tut.personal_id = tpt.id
	WHERE a.id = '".$aumento_id."'
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
		$situacion_tesoreria = $sel['situacion_tesoreria'];
		$usuario_atencion_tesoreria = $sel['usuario_atencion_tesoreria'];
		$motivo_atencion_tesoreria = $sel['motivo_atencion_tesoreria'];
		$usuario_asignado_id = $sel['usuario_asignado_id'];
		$user_created_id = $sel['user_created_id'];
		
		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';
		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Solicitud Rechazada</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Solicitud:</b></td>';
			$body .= '<td>'.$tipo_solicitud.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Usuario por Asignar:</b></td>';
			$body .= '<td>'.$usuario_asignado.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$usuario_solicitante.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Situación:</b></td>';
			$body .= '<td>'.$situacion_tesoreria.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Área Atención:</b></td>';
			$body .= '<td>Tesorería</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario Atención:</b></td>';
			$body .= '<td>'.$usuario_atencion_tesoreria.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'.$motivo_atencion_tesoreria.'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	$sub_titulo_email = "";

	if (env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Aumento de Fondo Caja Chica Rechazada ID: ".$aumento_id;
	
	$cc = [
		
	];

	$select_usuarios_enviar_a = 
		"
			SELECT 
				p.correo
			FROM tbl_personal_apt p
				INNER JOIN tbl_usuarios u
				ON p.id = u.personal_id
			WHERE u.id IN ('".$usuario_asignado_id."', '".$user_created_id."')
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
		return $respuesta_email = true;

	} 
	catch (Exception $e) 
	{
		return $respuesta_email = $e;
	}
}

function enviar_error($error)
{
	$result["http_code"] = 400;
	$result["result"] = 'Ocurrio un error al consultar: ' . $error;
	echo json_encode($result);
	exit();
}

echo json_encode($result);

?>