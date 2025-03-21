<?php 

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if(isset($_POST["accion"]) && $_POST["accion"]==="prestamo_tesoreria_atencion_obtener_empresa_numero_cuenta") 
{

	$empresa = $_POST["empresa"];
	
	$query = 
	"
		SELECT
			id,
		    num_cuenta_corriente AS nombre
		FROM cont_num_cuenta
		WHERE status = 1 AND razon_social_id = '".$empresa."' 
			AND moneda_id = 1 AND tipo_pago_id = 2
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
		$result["result"] = "No existen registros.";
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

if(isset($_POST["accion"]) && $_POST["accion"]==="prestamo_tesoreria_atencion_boveda_atencion_pagos")
{

	$tipo_consulta = $_POST["param_tipo_consulta"];
	$param_tipo_banco = $_POST["param_tipo_banco"];
	$param_tipo_tienda = $_POST["param_tipo_tienda"];
	$param_tipo_prestamo = $_POST["param_tipo_prestamo"];
	$param_tipo_empresa = $_POST["param_tipo_empresa"];
	$param_tipo_empresa_num_cuenta = $_POST["param_tipo_empresa_num_cuenta"];
	$param_ids_prestamo_boveda = $_POST["ids_prestamo_boveda"];

	$contador_array_ids = 0;
	$data_param_ids_prestamo = json_decode($param_ids_prestamo_boveda);
	
	$ids_prestamos = '';

	foreach ($data_param_ids_prestamo as $value) 
	{
		if ($contador_array_ids > 0) 
		{
			$ids_prestamos .= ',';
		}
		
		$ids_prestamos .= $value;			
		$contador_array_ids++;
	}

	if($contador_array_ids == 0)
	{
		$ids_prestamos = 0;
	}

	$where_tipo_banco = "";

	if($param_tipo_tienda == 1 || $param_tipo_tienda == 16)
	{
		// Red AT
		// Red IGH
		if($param_tipo_banco == 1)
		{
			// SOLO BANCOS BBVA
			$where_tipo_banco = " AND (ac.banco_id = 12 OR ac.banco_id IS NULL) AND (b.banco_cajero_id = 12 OR b.banco_cajero_id IS NULL) ";

		}
		else if($param_tipo_banco == 2)
		{
			// SOLO BANCOS DIFERENTES A BBVA
			$where_tipo_banco = " AND (ac.banco_id != 12 OR ac.banco_id IS NULL) AND (b.banco_cajero_id != 12 OR b.banco_cajero_id IS NULL)";
		}
	}
	else if($param_tipo_tienda == 9)
	{
		// Red Sportsbars
		if($param_tipo_prestamo == 8)
		{
			if($param_tipo_banco == 1)
			{
				// SOLO BANCOS BBVA
				$where_tipo_banco = " AND b.banco_cajero_id = 12 ";
			}
			else if($param_tipo_banco == 2)
			{
				// SOLO BANCOS DIFERENTES A BBVA
				$where_tipo_banco = " AND b.banco_cajero_id != 12 ";
			}
		}
	}
	
	// INICIO LISTAR LOS PRESTAMOS BOVEDA QUE YA SE REGISTRARON EN ALGUNA PROGRAMACION DE PAGOS

	$query_id_prestamo_programacion_detalle = "
		SELECT
			tbl_caja_prestamo_boveda_id
		FROM tbl_prestamo_programacion_detalle
		WHERE status = 1
	";

	$data_prestamo_en_programacion_detalle = $mysqli->query($query_id_prestamo_programacion_detalle);

	$ids_prestamos_en_programacion_detalle = '';
	$cont_ids_prestamos = 0;

	while($row = $data_prestamo_en_programacion_detalle->fetch_assoc())
	{
		if($cont_ids_prestamos > 0)
		{
			$ids_prestamos_en_programacion_detalle .= ',';
		}

		$ids_prestamos_en_programacion_detalle .= $row["tbl_caja_prestamo_boveda_id"];

		$cont_ids_prestamos++;
	}

	// FIN LISTAR LOS PRESTAMOS BOVEDA QUE YA SE REGISTRARON EN ALGUNA PROGRAMACION DE PAGOS


	$where_not_ids_prestamos = "";
	$where_not_ids_prestamos_seleccioando = "";

	$html = '';
	$tbody = '';
	$ids_todos_prestamo = '';
	$total_monto_programado = 0;

	if($tipo_consulta == '1')
	{
		if($ids_prestamos_en_programacion_detalle != '')
		{
			$where_not_ids_prestamos .= " AND b.id NOT IN(" . $ids_prestamos_en_programacion_detalle . ")";
		}

		if ($ids_prestamos != '') 
		{
			$where_not_ids_prestamos_seleccioando .= " AND b.id NOT IN(" . $ids_prestamos . ")";
		}

		$query = "
			SELECT
				b.id, b.tipo_tienda, b.asignacion_id_num_cuenta, b.tipo_prestamo, 
				a.situacion_etapa_id, l.nombre AS local,
				concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			    tp.dni, tb.nombre AS banco, 
			    ac.id AS num_cuenta_id, ac.num_cuenta, b.monto,
			    b.cajero_usuario_id,
			    b.cliente,
			    b.cliente_dni,
			    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_asignado_cajero,
    			tpc.dni AS dni_cajero, tbc.nombre AS banco_cajero,
    			b.numero_cuenta_cajero,
    			concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, ''), ' ', IFNULL(tps.apellido_materno, '')) AS usuario_sportbars,
    			tps.dni AS usuario_dni_sportbars
			FROM tbl_caja_prestamo_boveda b
				INNER JOIN tbl_locales l
    			ON b.local_id = l.id
				LEFT JOIN mepa_asignacion_caja_chica a
				ON b.user_created_id = a.usuario_asignado_id AND b.asignacion_id_num_cuenta = a.id
				LEFT JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				LEFT JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				LEFT JOIN mepa_asignacion_cuenta_bancaria ac
				ON ac.asignacion_id = a.id AND ac.status = 1
				LEFT JOIN tbl_bancos tb
				ON ac.banco_id = tb.id
				LEFT JOIN tbl_usuarios tuc
				ON b.cajero_usuario_id = tuc.id
				LEFT JOIN tbl_personal_apt tpc
				ON tuc.personal_id = tpc.id
			    LEFT JOIN tbl_bancos tbc
				ON b.banco_cajero_id = tbc.id
				LEFT JOIN tbl_usuarios tus
				ON b.user_created_id = tus.id
				LEFT JOIN tbl_personal_apt tps
				ON tus.personal_id = tps.id
			WHERE (a.situacion_etapa_id = 6 OR a.situacion_etapa_id IS NULL) 
				AND b.situacion_jefe_etapa_id = 2 AND b.situacion_tesoreria_etapa_id = 1
				AND b.tipo_tienda = '".$param_tipo_tienda."' 
				AND b.tipo_prestamo = '".$param_tipo_prestamo."'
				".$where_tipo_banco."
				".$where_not_ids_prestamos."
				".$where_not_ids_prestamos_seleccioando."
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
				$asignacion_id_num_cuenta = $row["asignacion_id_num_cuenta"];
				$boveda_cajero_usuario_id = $row["cajero_usuario_id"];
				$boveda_tipo_tienda = $row["tipo_tienda"];
				$boveda_tipo_prestamo = $row["tipo_prestamo"];

				if($boveda_tipo_tienda == 9)
				{
					// RED SPORTBARS
					if($boveda_tipo_prestamo == 7)
					{
						// PRESTAMO BOVEDA
						if($boveda_cajero_usuario_id != NULL)
						{
							$usuario_asignado = $row["usuario_asignado_cajero"];
							$dni = $row["dni_cajero"];
							$banco = $row["banco_cajero"];
							$num_cuenta = $row["numero_cuenta_cajero"];
						}
						else
						{
							$usuario_asignado = $row["usuario_sportbars"];
							$dni = $row["usuario_dni_sportbars"];
							$banco = "";
							$num_cuenta = "";
						}
					}
					else if($boveda_tipo_prestamo == 8)
					{
						// PRESTAMO PAGO DE PREMIOS
						$usuario_asignado = $row["cliente"];
						$dni = $row["cliente_dni"];
						$banco = $row["banco_cajero"];
						$num_cuenta = $row["numero_cuenta_cajero"];
					}
				}
				else if($boveda_tipo_tienda == 1 || $boveda_tipo_tienda == 16)
				{
					// RED AT
					if($boveda_tipo_prestamo == 7)
					{
						// PRESTAMO BOVEDA
						if($asignacion_id_num_cuenta == 0)
						{
							$usuario_asignado = $row["usuario_asignado_cajero"];
							$dni = $row["dni_cajero"];
							$banco = $row["banco_cajero"];
							$num_cuenta = $row["numero_cuenta_cajero"];
						}
						else
						{
							$usuario_asignado = $row["usuario_asignado"];
							$dni = $row["dni"];
							$banco = $row["banco"];
							$num_cuenta = $row["num_cuenta"];
						}
					}
					else if($boveda_tipo_prestamo == 8)
					{
						// PRESTAMO PAGO DE PREMIOS
						$usuario_asignado = $row["cliente"];
						$dni = $row["cliente_dni"];
						$banco = $row["banco_cajero"];
						$num_cuenta = $row["numero_cuenta_cajero"];
					}
					
				}

				$tbody .= '<tr>';
					$tbody .= '<td>' . $num . '</td>';
					$tbody .= '<td>' . $usuario_asignado . '</td>';
					
					if($param_tipo_tienda == 1 || $param_tipo_tienda == 16)
					{
						// RED AT
						// RED IGH
						$tbody .= '<td>' . $dni . '</td>';
						$tbody .= '<td>' . $banco . '</td>';
						$tbody .= '<td>' . $num_cuenta . '</td>';
					}
					else if($param_tipo_tienda == 9)
					{
						// RED SPORBARS
						$tbody .= '<td>' . $dni . '</td>';

						if($boveda_tipo_prestamo == 8)
						{
							// PRESTAMO DE PREMIOS
							$tbody .= '<td>' . $banco . '</td>';
							$tbody .= '<td>' . $num_cuenta . '</td>';
						}
					}
					$tbody .= '<td>' . $row["local"] . '</td>';
					$tbody .= '<td>' . $row["monto"] . '</td>';
					$tbody .= '<td>';
						$tbody .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Agregar a la programación de pagos" onclick="boveda_tesoreria_atencion_agregar_prestamo_a_la_programacion_pagos(' . $row["id"] . ')">';
						$tbody .= '<i class="fa fa-plus"></i>';
						$tbody .= '</a>';
					$tbody .= '</td>';
				$tbody .= '</tr>';

				if ($num == 1) 
				{
					$ids_todos_prestamo .= $row["id"];
				} 
				else 
				{
					$ids_todos_prestamo .= "," . $row["id"];
				}

				$total_monto_programado += $row["monto"];
				$num += 1;
			}

			$html .= '<table class="table table-bordered" style="font-size: 12px;">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<th colspan="8" style="background-color: #E5E5E5;">';
							$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px; text-align: left;">';
							
							if($param_tipo_prestamo == 7)
							{
								$html .= 'Usuarios: Préstamo Bóveda';	
							}
							else if($param_tipo_prestamo == 8)
							{
								$html .= 'Usuarios: Préstamo Pago de Premios';
							}

							$html .= '</div>';
							$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
								$html .= '<button type="button" class="btn btn-success btn-xs"  title="Agregar todos" style="width: 100px;" onclick="boveda_tesoreria_atencion_prestamo_agregar_varios_a_la_programacion(' . $ids_todos_prestamo . ')">';
								$html .= '<i class="fa fa-plus"></i>';
								$html .= ' Agregar todos';
								$html .= '</button>';
							$html .= '</div>';
						$html .= '</th>';
					$html .= '</tr>';

					$html .= '<tr>';
						$html .= '<th>#</th>';
						$html .= '<th>Usuario</th>';
						
						if($param_tipo_tienda == 1 || $param_tipo_tienda == 16)
						{
							// RED AT
							// RED IGH
							$html .= '<th>DNI</th>';
							$html .= '<th>Banco</th>';
							$html .= '<th>Nº. Cuenta</th>';
						}
						else if($param_tipo_tienda == 9)
						{
							// RED SPORBARS
							$html .= '<th>DNI</th>';

							if($boveda_tipo_prestamo == 8)
							{
								// PRESTAMO DE PREMIOS
								$html .= '<th>Banco</th>';
								$html .= '<th>Nº. Cuenta</th>';
							}
						}
						$html .= '<th>Local</th>';
						$html .= '<th>Monto</th>';
						$html .= '<th>Agregar</th>';
					$html .= '</tr>';
				$html .= '</thead>';
				
				$html .= '<tbody>';
					$html .= $tbody;
				$html .= '</tbody>';
			$html .= '</table>';
		}
		else if ($row_count == 0)
		{
			// NO EXISTEN DATOS PENDIENTE DE PAGOS
			$html .= '<table class="table table-bordered" style="font-size: 12px;">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<th style="background-color: #E5E5E5;">';
						$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px; text-align: left;">';
							
							if($param_tipo_prestamo == 7)
							{
								$html .= 'Usuarios: Préstamo Bóveda';	
							}
							else if($param_tipo_prestamo == 8)
							{
								$html .= 'Usuarios: Préstamo Pago de Premios';
							}
							
							$html .= '</div>';
						$html .= '</th>';
					$html .= '</tr>';
				$html .= '</thead>';
				
				$html .= '<tbody>';
					$html .= '<tr>';
						$html .= '<td style="text-align: center;">No existen registros</td>';
					$html .= '</tr>';
				$html .= '</tbody>';
			$html .= '</table>';
		}
	}
	else
	{
		$where_not_ids_prestamos_seleccioando .= " AND b.id IN(" . $ids_prestamos . ")";

		$query = "
			SELECT
				b.id, b.tipo_tienda, b.asignacion_id_num_cuenta, b.tipo_prestamo, 
				a.situacion_etapa_id, l.nombre AS local,
				concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			    tp.dni, tb.nombre AS banco,
			    ac.id AS num_cuenta_id, ac.num_cuenta, b.monto,
			    b.cajero_usuario_id,
			    b.cliente,
			    b.cliente_dni,
			    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_asignado_cajero,
    			tpc.dni AS dni_cajero, tbc.nombre AS banco_cajero,
    			b.numero_cuenta_cajero,
    			concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, ''), ' ', IFNULL(tps.apellido_materno, '')) AS usuario_sportbars,
    			tps.dni AS usuario_dni_sportbars
			FROM tbl_caja_prestamo_boveda b
				INNER JOIN tbl_locales l
    			ON b.local_id = l.id
				LEFT JOIN mepa_asignacion_caja_chica a
				ON b.user_created_id = a.usuario_asignado_id AND b.asignacion_id_num_cuenta = a.id
				LEFT JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				LEFT JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				LEFT JOIN mepa_asignacion_cuenta_bancaria ac
				ON ac.asignacion_id = a.id AND ac.status = 1
				LEFT JOIN tbl_bancos tb
				ON ac.banco_id = tb.id
				LEFT JOIN tbl_usuarios tuc
				ON b.cajero_usuario_id = tuc.id
				LEFT JOIN tbl_personal_apt tpc
				ON tuc.personal_id = tpc.id
			    LEFT JOIN tbl_bancos tbc
				ON b.banco_cajero_id = tbc.id
				LEFT JOIN tbl_usuarios tus
				ON b.user_created_id = tus.id
				LEFT JOIN tbl_personal_apt tps
				ON tus.personal_id = tps.id
			WHERE (a.situacion_etapa_id = 6 OR a.situacion_etapa_id IS NULL) 
				AND b.situacion_jefe_etapa_id = 2
				AND b.tipo_tienda = '".$param_tipo_tienda."' 
				AND b.tipo_prestamo = '".$param_tipo_prestamo."'
				".$where_tipo_banco."
				".$where_not_ids_prestamos_seleccioando."
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
				$asignacion_id_num_cuenta = $row["asignacion_id_num_cuenta"];
				$boveda_cajero_usuario_id = $row["cajero_usuario_id"];
				$boveda_tipo_tienda = $row["tipo_tienda"];
				$boveda_tipo_prestamo = $row["tipo_prestamo"];

				if($boveda_tipo_tienda == 9)
				{
					// RED SPORTBARS
					if($boveda_tipo_prestamo == 7)
					{
						// PRESTAMO BOVEDA
						if($boveda_cajero_usuario_id != NULL)
						{
							$usuario_asignado = $row["usuario_asignado_cajero"];
							$dni = $row["dni_cajero"];
							$banco = $row["banco_cajero"];
							$num_cuenta = $row["numero_cuenta_cajero"];
						}
						else
						{
							$usuario_asignado = $row["usuario_sportbars"];
							$dni = $row["usuario_dni_sportbars"];
							$banco = "";
							$num_cuenta = "";
						}
					}
					else if($boveda_tipo_prestamo == 8)
					{
						// PRESTAMO PAGO DE PREMIOS
						$usuario_asignado = $row["cliente"];
						$dni = $row["cliente_dni"];
						$banco = $row["banco_cajero"];
						$num_cuenta = $row["numero_cuenta_cajero"];
					}
				}
				else if($boveda_tipo_tienda == 1 || $boveda_tipo_tienda == 16)
				{
					// RED AT
					// RED IGH
					if($boveda_tipo_prestamo == 7)
					{
						// PRESTAMO BOVEDA
						if($asignacion_id_num_cuenta == 0)
						{
							$usuario_asignado = $row["usuario_asignado_cajero"];
							$dni = $row["dni_cajero"];
							$banco = $row["banco_cajero"];
							$num_cuenta = $row["numero_cuenta_cajero"];
						}
						else
						{
							$usuario_asignado = $row["usuario_asignado"];
							$dni = $row["dni"];
							$banco = $row["banco"];
							$num_cuenta = $row["num_cuenta"];
						}
					}
					else if($boveda_tipo_prestamo == 8)
					{
						// PRESTAMO PAGO DE PREMIOS
						$usuario_asignado = $row["cliente"];
						$dni = $row["cliente_dni"];
						$banco = $row["banco_cajero"];
						$num_cuenta = $row["numero_cuenta_cajero"];
					}
				}

				$tbody .= '<tr>';
					$tbody .= '<td>' . $num . '</td>';
					$tbody .= '<td>' . $usuario_asignado . '</td>';

					if($param_tipo_tienda == 1 || $param_tipo_tienda == 16)
					{
						// RED AT
						// RED IGH
						$tbody .= '<td>' . $dni . '</td>';
						$tbody .= '<td>' . $banco . '</td>';
						$tbody .= '<td>' . $num_cuenta . '</td>';
					}
					else if($param_tipo_tienda == 9)
					{
						// RED SPORBARS
						$tbody .= '<td>' . $dni . '</td>';

						if($boveda_tipo_prestamo == 8)
						{
							// PRESTAMO DE PREMIOS
							$tbody .= '<td>' . $banco . '</td>';
							$tbody .= '<td>' . $num_cuenta . '</td>';
						}
					}

					$tbody .= '<td>' . $row["local"] . '</td>';
					$tbody .= '<td>' . $row["monto"] . '</td>';
					
					$tbody .= '<td>';
						$tbody .= '<a class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Quitar de la programación de pagos" onclick="boveda_tesoreria_atencion_quitar_prestamo_de_la_programacion(' . $row["id"] . ')">';
						$tbody .= '<i class="fa fa-minus"></i>';
						$tbody .= '</a>';
					$tbody .= '</td>';
				$tbody .= '</tr>';

				if ($num == 1) 
				{
					$ids_todos_prestamo .= $row["id"];
				} 
				else 
				{
					$ids_todos_prestamo .= "," . $row["id"];
				}

				$total_monto_programado += $row["monto"];
				$num += 1;
			}

			$html .= '<table class="table table-bordered" style="font-size: 12px;">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<th colspan="8" style="background-color: #E5E5E5;">';
							$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px" style="text-align: left;">';
							$html .= 'Usuarios que integran la programación de pago';
							$html .= '</div>';
							$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
								$html .= '<button type="button" class="btn btn-warning btn-xs" title="Quitar todos" style="width: 100px;" onclick="boveda_tesoreria_atencion_prestamo_quitar_varios_a_la_programacion(' . $ids_todos_prestamo . ')">';
								$html .= '<i class="fa fa-minus"></i>';
								$html .= ' Quitar todos';
								$html .= '</button>';
							$html .= '</div>';
						$html .= '</th>';
					$html .= '</tr>';

					$html .= '<tr>';
						$html .= '<th>#</th>';
						$html .= '<th>Usuario</th>';

						if($param_tipo_tienda == 1 || $param_tipo_tienda == 16)
						{
							// RED AT
							// RED IGH
							$html .= '<th>DNI</th>';
							$html .= '<th>Banco</th>';
							$html .= '<th>Nº. Cuenta</th>';
						}
						else if($param_tipo_tienda == 9)
						{
							// RED SPORBARS
							$html .= '<th>DNI</th>';

							if($boveda_tipo_prestamo == 8)
							{
								// PRESTAMO DE PREMIOS
								$html .= '<th>Banco</th>';
								$html .= '<th>Nº. Cuenta</th>';
							}
						}

						$html .= '<th>Local</th>';
						$html .= '<th>Monto</th>';
						$html .= '<th>Quitar</th>';
					$html .= '</tr>';
				$html .= '</thead>';
				
				$html .= '<tbody>';
					$html .= $tbody;

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

				$html .= '</tbody>';
			$html .= '</table>';
		}
		else if ($row_count == 0)
		{
			// NO EXISTEN DATOS PENDIENTE DE PAGOS
			$html .= '<table class="table table-bordered" style="font-size: 12px;">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<th style="background-color: #E5E5E5;">';
							$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px" style="text-align: left;">';
							$html .= 'Usuarios que integran la programación de pago';
							$html .= '</div>';
						$html .= '</th>';
					$html .= '</tr>';
				$html .= '</thead>';
			
				$html .= '<tbody>';
					$html .= '<tr>';
						$html .= '<td style="text-align: center;">No existen registros</td>';
					$html .= '</tr>';
				$html .= '</tbody>';
			$html .= '</table>';
		}
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

if(isset($_POST["accion"]) && $_POST["accion"]==="tesoreria_guardar_prestamo_boveda_programacion_de_pago") 
{

	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');

	if((int) $user_id>0)
	{
		$param_tipo_tienda = $_POST["param_tipo_tienda"];
		$param_tipo_prestamo = $_POST["param_tipo_prestamo"];
		$param_tipo_banco = $_POST["param_tipo_banco"];
		$param_tipo_empresa = $_POST["param_tipo_empresa"];
		$param_tipo_empresa_num_cuenta = $_POST["param_tipo_empresa_num_cuenta"];
		$ids_prestamo = $_POST["ids_prestamo"];
		$data_ids_prestamo = json_decode($ids_prestamo);

		
		// INICIO INSERTAR PROGRAMACIÓN
		$query_insert_programacion = "
			INSERT INTO tbl_prestamo_programacion
			(
				tipo_tienda,
				tipo_prestamo,
				num_cuenta_id,
				empresa_id,
				tipo_banco,
				situacion_etapa_id,
				status,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'" . $param_tipo_tienda . "',
				'" . $param_tipo_prestamo . "',
				'" . $param_tipo_empresa_num_cuenta . "',
				'" . $param_tipo_empresa . "',
				'" . $param_tipo_banco . "',
				2,
				1,
				" . $user_id . ",
				'" . $created_at . "',
				" . $user_id . ",
				'" . $created_at . "'
			)";

		$mysqli->query($query_insert_programacion);
		
		$programacion_id = mysqli_insert_id($mysqli);
		
		if($mysqli->error)
		{
			$result["http_code"] = 400;
			$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query_insert_programacion;
			echo json_encode($result);
			exit();
		}
		// FIN INSERTAR PROGRAMACIÓN

		// INICIO INSERTAR DETALLE PROGRAMACIÓN
		$array_detalle_programacion_id = [];

		foreach ($data_ids_prestamo as $value_prestamo_id) 
		{
			$query_insert_detalle_programacion = "
				INSERT INTO tbl_prestamo_programacion_detalle
				(
					tbl_prestamo_programacion_id,
					tbl_caja_prestamo_boveda_id,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					" . $programacion_id . ",
					" . $value_prestamo_id . ",
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
				$result["http_code"] = 400;
				$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query_insert_detalle_programacion;
				echo json_encode($result);
				exit();

			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN

		$result["http_code"] = 200;
		$result["status"] = "Datos guardados correctamente.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
        $result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }
}

if (isset($_POST["accion"]) && $_POST["accion"]==="tesoreria_atencion_guardar_prestamo_boveda_cambios_programacion_de_pago") 
{
	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');
	$array_detalle_programacion_id = [];

	if((int) $user_id > 0)
	{
		$programacion_id = $_POST["programacion_id_edit"];
		$ids_prestamo = $_POST["ids_prestamo"];
		$array_prestamo_detalle_nuevos = json_decode($ids_prestamo);

		// INICIO OBTENER PRESTAMO DETALLE ACTUALES
		$query_detalle_programacion = "
			SELECT 
			    tbl_caja_prestamo_boveda_id
			FROM tbl_prestamo_programacion_detalle
			WHERE status = 1 AND tbl_prestamo_programacion_id = " . $programacion_id . "
		";

		$list_query = $mysqli->query($query_detalle_programacion);

		if($mysqli->error)
		{
			$result["http_code"] = 400;
			$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query_detalle_programacion;
			echo json_encode($result);
			exit();
		}

		$row_count = $list_query->num_rows;

		$array_prestamo_detalle_actuales = array();

		if ($row_count > 0) 
		{
			while ($row = $list_query->fetch_assoc()) 
			{
				array_push($array_prestamo_detalle_actuales, $row["tbl_caja_prestamo_boveda_id"]);
			}
		}
		// FIN OBTENER PRESTAMO DETALLE ACTUALES

		// INICIO INSERTAR DETALLE PROGRAMACIÓN NUEVOS
		$array_nuevos_prestamos = array_diff($array_prestamo_detalle_nuevos, $array_prestamo_detalle_actuales);

		foreach ($array_nuevos_prestamos as $value_prestamo_id) 
		{
			$query_insert_detalle_programacion = "
				INSERT INTO tbl_prestamo_programacion_detalle
				(
					tbl_prestamo_programacion_id,
					tbl_caja_prestamo_boveda_id,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					" . $programacion_id . ",
					" . $value_prestamo_id . ",
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
				$result["http_code"] = 400;
				$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query_insert_detalle_programacion;
				echo json_encode($result);
				exit();
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN NUEVOS
		
		// INICIO ELIMINAR DETALLE DE LA PROGRAMACION
		$array_prestamo_debaja = array_diff($array_prestamo_detalle_actuales, $array_prestamo_detalle_nuevos);
		foreach ($array_prestamo_debaja as $value_prestamo_id_de_baja) 
		{
			$sql_prestamo_de_baja = "
				DELETE FROM tbl_prestamo_programacion_detalle
				WHERE tbl_prestamo_programacion_id = $programacion_id AND tbl_caja_prestamo_boveda_id = $value_prestamo_id_de_baja
			";
			
			$mysqli->query($sql_prestamo_de_baja);
			
			if($mysqli->error)
			{
				$result["http_code"] = 400;
				$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $sql_prestamo_de_baja;
				echo json_encode($result);
				exit();
			}
		}
		// FIN ELIMINAR DETALLE DE LA PROGRAMACION

		$result["http_code"] = 200;
		$result["status"] = "Datos guardados correctamente.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
		$result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
	
}

echo json_encode($result);

?>