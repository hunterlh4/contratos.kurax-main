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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_validar_listar_mis_transacciones_validadas")
{
	$login_id = $login?$login['id']:null;
	$param_global_fecha = date('Y-m-d');

	$query = 
	"
		SELECT 
	        t.id, 
	        DATE_FORMAT(t.fecha_deposito, '%Y-%m-%d %H:%i') AS fecha, 
	        t.monto_deposito, t.nombre_depositante,
	        t.estado_transaccion_id AS estado_id, 
	        te.descripcion AS estado_nombre,
	        br.nombre AS origen_registro, 
	        t.numero_operacion,
	        t.fecha_deposito,
	        concat(IFNULL(pr.nombre, ''),' ', IFNULL(pr.apellido_paterno, ''),' ', IFNULL(pr.apellido_materno, '')) AS usuario_revision,
	        t.motivo_rechazo_id, tmr.nombre AS motivo_rechazo, t.otro_motivo_rechazo,
	        t.atencion, t.fecha_atencion,
	        t.created_at AS fecha_creacion,
	        DATE_FORMAT(DATE_SUB(t.fecha_deposito, INTERVAL 3 MINUTE), '%H:%i') AS hora_menos,
	        DATE_FORMAT(DATE_ADD(t.fecha_deposito, INTERVAL 3 MINUTE), '%H:%i') AS hora_mas
	    FROM tbl_billetera_transacciones t
	        INNER JOIN tbl_billetera_registro br
			ON t.billetera_registro_id = br.id
	        INNER JOIN tbl_billetera_transacciones_estados te
	        ON t.estado_transaccion_id = te.id
	        LEFT JOIN tbl_billetera_motivos_rechazo tmr
    		ON t.motivo_rechazo_id = tmr.id
    		LEFT JOIN tbl_usuarios ur
			ON t.usuario_revision_id = ur.id
			LEFT JOIN tbl_personal_apt pr
			ON ur.personal_id = pr.id
	    WHERE t.status = 1 
	        AND t.cajero_id = '".$login_id."' 
	        AND 
		    (
		    	(
		    		t.estado_transaccion_id IN (2, 4) 
		    		AND DATE_FORMAT(t.fecha_validacion, '%Y-%m-%d') = '".$param_global_fecha."'
		    	)
		    	OR
		    	(
		    		t.estado_transaccion_id in (3) AND t.billetera_registro_id = 2
		    		AND DATE_FORMAT(t.created_at, '%Y-%m-%d') = '".$param_global_fecha."'
		    	)
		    )
	    ORDER BY t.id DESC
	";

	$list_query = $mysqli->query($query);

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["result"] = 'Ocurrio un error al consultar: '.$mysqli->error.' - '.$query;
		echo json_encode($result);
		exit();
	}

	$query_informacion_local = 
	"
		SELECT
			l.id, CONCAT(l.nombre, ' [', l.cc_id ,']') AS local
		FROM tbl_locales l
		WHERE l.id = '".$_COOKIE['usuario_local_id']."' 
	";

	$list_query_informacion_local = $mysqli->query($query_informacion_local);
	$row_count = $list_query_informacion_local->num_rows;

	$param_info_local_id = "";
	$param_info_local_nombre = "";
	$param_info_local_usuario = $login["usuario"];
	$param_info_local_fecha = "";

	if ($row_count > 0) 
	{
		$row = $list_query_informacion_local->fetch_assoc();
		$param_info_local_id = $row["id"];
		$param_info_local_nombre = $row["local"];
		$param_info_local_fecha = date('Y-m-d');
	}
	
	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["result"] = 'Ocurrio un error al consultar: '.$mysqli->error.' - '.$query_informacion_local;
		echo json_encode($result);
		exit();
	}


	$row_count = $list_query->num_rows;

	$num = 0;
	$num_validados = 0;

	if($row_count > 0)
	{
		while ($row = $list_query->fetch_assoc())
		{
			$num += 1;

			$tbody .= '<tr>';
				$tbody .= '<td class="text-center">'.$num.'</td>';
				$tbody .= '<td class="text-center">'.$row["id"].'</td>';
				$tbody .= '<td class="text-center">'.$row["fecha"].'</td>';
				$tbody .= '<td class="text-center">S/ '.$row["monto_deposito"].'</td>';
				$tbody .= '<td class="text-center">'.$row["nombre_depositante"].'</td>';
				if($row["estado_id"] == 4)
				{
					if($row["motivo_rechazo_id"] == "")
					{
						$row["motivo_rechazo"] = $row["otro_motivo_rechazo"];
					}

					$tbody .= '<td class="text-center" data-toggle="tooltip" data-placement="top" title="Clic para ver detalle" style="color: red; font-size: 16px; font-weight: bold; cursor: pointer;" onclick="sec_billetera_validar_ver_detalle_rechazo(\''.$row["usuario_revision"].'\', \''.$row["motivo_rechazo"].'\');">'.$row["estado_nombre"].'</td>';
				}
				else if($row["estado_id"] == 2)
				{
					$tbody .= '<td class="text-center" style="color: green; font-size: 16px; font-weight: bold;">'.$row["estado_nombre"].'</td>';
				}
				else if($row["estado_id"] == 3)
				{
					$tbody .= '<td class="text-center" style="color: gray; font-size: 16px; font-weight: bold;">'.$row["estado_nombre"].'</td>';
				}
				$tbody .= '<td class="text-center">'.$row["origen_registro"].'</td>';
				$tbody .= '<td class="text-center">'.$row["numero_operacion"].'</td>';

				if($row["estado_id"] == 2)
				{
					$atendido = false;

					if($row["atencion"] == "1")
					{
						$tbody .= '<td class="text-center"><input type="checkbox" onclick="sec_billetera_validar_guardar_atencion('.$row["id"].', 0);" checked="true" style="width: 17%; height: 30px;"></td>';
					}
					else
					{
						$tbody .= '<td class="text-center"><input type="checkbox" onclick="sec_billetera_validar_guardar_atencion('.$row["id"].', 1);" style="width: 17%; height: 30px;"></td>';
					}
				}
				else
				{
					$tbody .= '<td class="text-center"></td>';
				}
				
			$tbody .= '</tr>';

			if($row["estado_id"] == 2)
			{
				$total_monto += $row["monto_deposito"];
				$num_validados += 1;
			}
		}

		$result["http_code"] = 200;
		$result["titulo"] = "Datos obtenidos de gestion.";
		$result["data"] = $tbody;
		$result["total_monto"] = "S/ ".number_format($total_monto, 2, '.', ',');
		$result["cant_registros"] = $num_validados;
		$result["informacion_local"] = "Usuario: ".$param_info_local_usuario." - Local: ".$param_info_local_nombre." - Fecha de registro: ".$param_info_local_fecha;

		echo json_encode($result);
		exit();
	}
	else
	{
		$tbody .= '<tr>';
			$tbody .= '<th class="text-center" colspan="9">No existen registros</th>';
		$tbody .= '</tr>';

		$result["http_code"] = 400;
		$result["titulo"] = "No se encontraron registros.";
		$result["data"] = $tbody;
		$result["total_monto"] = "S/ 0.00";
		$result["cant_registros"] = $num_validados;
		$result["informacion_local"] = "Usuario: ".$param_info_local_usuario." - Local: ".$param_info_local_nombre." - Fecha de registro: ".$param_info_local_fecha;

		echo json_encode($result);
		exit();
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_validar_guardar_atencion")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['param_transaccion_id'];
		$param_valor = $_POST['param_valor'];
		
		$titulo = "";
		$descripcion = "";
		$titulo_error = "";

	    if($param_valor == 1)
	    {
	        // Activar
	        $titulo = "Registro exitoso";
	        $descripcion = "Operación atendida.";
	        $titulo_error = "pasar a atendido";
	    }
	    else
	    {
	        //Desactivar
	        $titulo = "Registro exitoso";
	        $descripcion = "Operación No atendida.";
	        $titulo_error = "pasar a NO atendido";
	    }

		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_billetera_transacciones 
				SET atencion = '".$param_valor."',
					fecha_atencion = '".$fecha."',
					user_updated_id = '".$usuario_id."',
					updated_at = '".$fecha."'
			WHERE id = {$param_id}
		";

		$mysqli->query($query_update);

		if($mysqli->error)
        {
            $error = $mysqli->error;

            $result["http_code"] = 400;
            $result["titulo"] = "Error al '".$titulo_error."'. ";
            $result["descripcion"] = $error;
            $result["query"] = $query_update;

            echo json_encode($result);
            exit();
        }

        $result["http_code"] = 200;
		$result["titulo"] = $titulo;
		$result["descripcion"] = $descripcion;
		
		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida.";
        $result["descripcion"] ="Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_validar_listar_transacciones")
{
	$param_fecha = $_POST['param_fecha'];
	$param_fecha = date("Y-m-d", strtotime($param_fecha));
	
	$param_hora = $_POST["param_hora"];
	$param_monto = $_POST["param_monto"];
	$param_depositante = $_POST["param_depositante"];

	$param_fecha_hora = $param_fecha.' '.$param_hora;
	$cant_palabras = explode(" ", $param_depositante);
	
	$where_nombre_depositante = "";

	foreach($cant_palabras as $palabra)
	{
		if($palabra != "")
		{
			$where_nombre_depositante .= " AND t.nombre_depositante REGEXP '[[:<:]]".$palabra."[[:>:]]' ";
		}
	}

	$query = 
	"
		SELECT 
			t.id, DATE_FORMAT(t.fecha_deposito, '%Y-%m-%d') AS fecha, DATE_FORMAT(t.fecha_deposito, '%H:%i') AS hora,
		    t.monto_deposito, t.nombre_depositante,
		    t.estado_transaccion_id AS estado_id, te.descripcion AS estado_nombre,
		    t.fecha_deposito,
		    DATE_FORMAT(DATE_SUB(t.fecha_deposito, INTERVAL 3 MINUTE), '%H:%i') AS hora_menos,
		    DATE_FORMAT(DATE_ADD(t.fecha_deposito, INTERVAL 3 MINUTE), '%H:%i') AS hora_mas
		FROM tbl_billetera_transacciones t
		INNER JOIN tbl_billetera_transacciones_estados te
		ON t.estado_transaccion_id = te.estado
		WHERE t.estado_transaccion_id = 1 
			AND DATE_FORMAT(t.fecha_deposito, '%Y-%m-%d') = '".$param_fecha."'
		    AND DATE_FORMAT(t.fecha_deposito, '%H:%i') 
				BETWEEN DATE_FORMAT(DATE_SUB('".$param_fecha_hora."', INTERVAL 3 MINUTE), '%H:%i') 
		        AND DATE_FORMAT(DATE_ADD('".$param_fecha_hora."', INTERVAL 3 MINUTE), '%H:%i')
		    AND t.monto_deposito = '".$param_monto."'
		    ".$where_nombre_depositante."
	";

	$list_query = $mysqli->query($query);

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["titulo"] = 'Ocurrio un error al consultar';
		$result["descripcion"] = 'Error: '.$mysqli->error.' - Query: '.$query;
		echo json_encode($result);
		exit();
	}

	$row_count = $list_query->num_rows;

	$num = 0;
	$num_validados = 0;

	if($row_count > 0)
	{
		while ($row = $list_query->fetch_assoc())
		{
			$tbody .= '<tr>';
				$tbody .= '<td class="text-center">'.$row["id"].'</td>';
				$tbody .= '<td class="text-center">'.$row["fecha"].'</td>';
				$tbody .= '<td class="text-center">'.$row["hora"].'</td>';
				$tbody .= '<td class="text-center">S/ '.number_format($row["monto_deposito"], 2, '.', ',').'</td>';
				$tbody .= '<td class="text-center">'.$row["nombre_depositante"].'</td>';
				$tbody .= '<td class="text-center">'.$row["estado_nombre"].'</td>';
				$tbody .= '<td class="text-center"><a onclick="sec_billetera_validar_transaccion('.$row["id"].', \''.$row["fecha"].'\', \''.$row["hora"].'\', \''.number_format($row["monto_deposito"], 2, '.', ',').'\', \''.$row["nombre_depositante"].'\');";
                        class="btn btn-info btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Validar">
                        <span class="fa fa-newspaper-o"></span>
                    </a></td>';
			$tbody .= '</tr>';
		}

		$result["http_code"] = 200;
		$result["titulo"] = "Datos obtenidos de gestion.";
		$result["descripcion"] = "";
		$result["data"] = $tbody;

		echo json_encode($result);
		exit();
	}
	else
	{
		$tbody .= '<tr>';
			$tbody .= '<th class="text-center" colspan="8">No existen registros</th>';
		$tbody .= '</tr>';

		$result["http_code"] = 400;
		$result["titulo"] = "No se encontró yapes pendientes";
		$result["descripcion"] = "";
		$result["data"] = $tbody;

		echo json_encode($result);
		exit();
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_validar_transaccion") 
{
	$login_id = $login?$login['id']:null;

	if((int)$login_id > 0)
	{
		$param_id = $_POST['param_id'];

		$auditoria_fecha = date('Y-m-d H:i:s');
		$error = '';

		$query_update = 
		"
			UPDATE tbl_billetera_transacciones 
				SET estado_transaccion_id = 2,
					cajero_id = '".$login_id."',
					fecha_validacion = '".date('Y-m-d H:i:s')."',
					local_id = '".$_COOKIE['usuario_local_id']."',
					user_updated_id = '".$login_id."',
					updated_at = '".date('Y-m-d H:i:s')."'
			WHERE id = '".$param_id."'
		";

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			$result["http_code"] = 400;
			$result["titulo"] = "Ocurrio un error.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}
		else
		{
			$result["http_code"] = 200;
			$result["titulo"] = "Validación exitoso";
			$result["descripcion"] = "La validación se guardo correctamente.";

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida";
        $result["descripcion"] ="Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}

	echo json_encode($result);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_validar_guardar_nueva_transaccion") 
{
	$login_id = $login?$login['id']:null;

	if((int)$login_id > 0)
	{
		$param_fecha = $_POST['form_modal_sec_billetera_validar_param_fecha'];
		$param_fecha = date("Y-m-d", strtotime($param_fecha));
		$param_hora = $_POST['form_modal_sec_billetera_validar_param_hora'];
		$param_monto = str_replace(",","",$_POST["form_modal_sec_billetera_validar_param_monto"]);
		$param_depositante = $_POST['form_modal_sec_billetera_validar_param_depositante'];
		$param_num_operacion = $_POST['form_modal_sec_billetera_validar_param_num_operacion'];
		$param_telefono = $_POST['form_modal_sec_billetera_validar_param_telefono'];
		$param_observacion = $_POST['form_modal_sec_billetera_validar_param_observacion'];

		$param_fecha_hora = $param_fecha.' '.$param_hora;
		$auditoria_fecha = date('Y-m-d H:i:s');
		$error = '';
		
		$query_insert = 
		"
			INSERT INTO tbl_billetera_transacciones
			(
				fecha_deposito,
				nombre_depositante,
				monto_deposito,
				billetera_telefono_id,
				billetera_registro_id,
				estado_transaccion_id,
				numero_operacion,
				observacion,
				cajero_id,
				local_id,
				status,
				user_created_id,
				created_at
			)
			VALUES
			(
				'".$param_fecha_hora."',
				'".$param_depositante."',
				'".$param_monto."',
				'".$param_telefono."',
				2,
				3,
				'".$param_num_operacion."',
				'".$param_observacion."',
				'".$login_id."',
				'".$_COOKIE['usuario_local_id']."',
				1,
				'".$login_id."',
				'".$auditoria_fecha."'
			)
		";

		$mysqli->query($query_insert);

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			$result["http_code"] = 400;
			$result["titulo"] = "Ocurrio un error.";
			$result["descripcion"] = $error;
			$result["query"] = $query_insert;

			echo json_encode($result);
			exit();
		}
		else
		{
			$result["http_code"] = 200;
			$result["titulo"] = "Registro exitoso";
			$result["descripcion"] = "La solicitud se envio correctamente.";

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida";
        $result["descripcion"] ="Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}

	echo json_encode($result);
	exit();
}

?>