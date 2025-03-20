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


if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mantenimiento_canal_venta_listar_servicios") 
{
	
	$usuario_id = $login?$login['id']:null;
	
	if((int)$usuario_id > 0)
	{
		$query = "";

		$query = 
	    "
	        SELECT
				id AS servicio_id, nombre
			FROM tbl_servicios
			WHERE estado = 1
	    ";

	    $list_query = $mysqli->query($query);
	    
	    if($mysqli->error)
		{
			$result["http_code"] = 400;
	        $result["titulo"] ="Error.";
	        $result["texto"] = $mysqli->error;
	        $result["codigo"] = 2;
			exit();
		}

		$list = array();
		
		while ($li = $list_query->fetch_assoc()) 
		{
			$list[] = $li;
		}

		if(count($list) == 0)
		{
			$result["http_code"] = 400;
			$result["titulo"] ="";
			$result["codigo"] = 1;
			$result["texto"] = "No se encontro resultados";
		} 
		elseif (count($list) > 0) 
		{
			$result["http_code"] = 200;
			$result["titulo"] = "Datos obtenidos de gestion.";
			$result["texto"] = $list;
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["titulo"] ="";
			$result["codigo"] = 1;
			$result["texto"] = "No existen registros";
		}

		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida.";
        $result["texto"] ="Por favor vuelva a iniciar sesión.";
        $result["codigo"] = 2;
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_canal_venta_listar_canal_venta")
{
	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'canal_venta' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];

    $query = 
    "
		SELECT
			c.id, c.servicio_id, s.nombre AS servicio_nombre, c.en_liquidacion, 
		    c.nombre, c.codigo, c.descripcion, c.hex_color, c.estado, c.pago_manual
		FROM tbl_canales_venta c
		LEFT JOIN tbl_servicios s
		ON c.servicio_id = s.id
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$botones = '';
		if(in_array("edit", $usuario_permisos[$menu_permiso]))
		{
			$botones .= 
			'
				<a onclick="sec_mantenimiento_canal_venta_obtener_canal_venta('.$reg->id.');";
               		class="btn btn-warning btn-sm"
                	data-toggle="tooltip" data-placement="top" title="Editar">
                    <span class="fa fa-pencil"></span>
                </a>
			';
		}

		if($reg->estado == 1)
		{
			if(in_array("desactivar", $usuario_permisos[$menu_permiso]))
			{
				$botones .= 
				'
					<a onclick="sec_mantenimiento_canal_venta_activar_desactivar_canal_venta('.$reg->id.', 0);";
		           		class="btn btn-danger btn-sm"
		            	data-toggle="tooltip" data-placement="top" title="Desactivar">
		                <span class="fa fa-close"></span>
		            </a>
				';	
			}
		}
		else 
		{
			if(in_array("activar", $usuario_permisos[$menu_permiso]))
			{
				$botones .= 
				'
	        		<a onclick="sec_mantenimiento_canal_venta_activar_desactivar_canal_venta('.$reg->id.', 1);";
		           		class="btn btn-primary btn-sm"
		            	data-toggle="tooltip" data-placement="top" title="Activar">
		                <span class="fa fa-check"></span>
		            </a>
	        	';
			}
		}

		if($botones == "")
		{
			$botones .= "No tienes permisos.";
		}

        $data[] = array(
			"0" => $reg->id,
			"1" => $reg->servicio_nombre,
			"2" => ($reg->en_liquidacion == 1 ? 'Si' : 'No' ),
			"3" => $reg->nombre,
			"4" => $reg->codigo,
			"5" => $reg->descripcion,
			"6" => $reg->hex_color,
			"7" => ($reg->pago_manual == 1 ? 'Si':'No'),
			"8" => ($reg->estado == 1 ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'),
			"9" => $botones
		);
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_canal_venta_nuevo")
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_servicio = $_POST['form_modal_sec_mantenimiento_canal_venta_param_servicio'];
		$param_aplica_liquidacion = $_POST['form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion'];
		$param_nombre = $_POST['form_modal_sec_mantenimiento_canal_venta_param_nombre'];
		$param_codigo = $_POST['form_modal_sec_mantenimiento_canal_venta_param_codigo'];
		$param_descripcion = $_POST['form_modal_sec_mantenimiento_canal_venta_param_descripcion'];
		$param_color_hexadecimal = $_POST['form_modal_sec_mantenimiento_canal_venta_param_color_hexadecimal'];
		$param_pago_manual = $_POST['form_modal_sec_mantenimiento_canal_venta_param_aplica_pago_manual'];
		$param_estado = $_POST['form_modal_sec_mantenimiento_canal_venta_param_estado'];

		$error = '';
		
		$query_insert = 
		"
			INSERT INTO tbl_canales_venta
			(
				servicio_id,
				en_liquidacion,
				nombre,
				codigo,
				descripcion,
				hex_color,
				pago_manual,
				estado,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'".$param_servicio."',
				'".$param_aplica_liquidacion."',
				'".$param_nombre."',
				'".$param_codigo."',
				'".$param_descripcion."',
				'".$param_color_hexadecimal."',
				'".$param_pago_manual."',
				'".$param_estado."',
				'".$usuario_id."',
                '".date('Y-m-d H:i:s')."',
				'".$usuario_id."',
				'".date('Y-m-d H:i:s')."'
			)
		";

		$mysqli->query($query_insert);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["titulo"] = "Error al registrar.";
			$result["texto"] = $error;

			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;
		$result["titulo"] = "Registro exitoso";
		$result["texto"] = "El canal de venta se registró exitosamente";
		
		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida.";
        $result["texto"] ="Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_canal_venta_obtener_canal_venta")
{
	$param_canal_venta_id = $_POST["param_canal_venta_id"];
	
	$query = 
	"
		SELECT
			c.id, c.servicio_id, s.nombre AS servicio_nombre, c.en_liquidacion, 
		    c.nombre, c.codigo, c.descripcion, c.hex_color, c.estado, c.pago_manual
		FROM tbl_canales_venta c
			LEFT JOIN tbl_servicios s
			ON c.servicio_id = s.id
		WHERE c.id = {$param_canal_venta_id}
		LIMIT 1
	";

	$list_query = $mysqli->query($query);
	
	$lista_datos = array();

	while ($li = $list_query->fetch_assoc())
	{
		$lista_datos[] = $li;
	}
	
	if ($mysqli->error)
	{
		$result["error"] = $mysqli->error;
		
		$result["http_code"] = 400;
		$result["titulo"] = "Error";
		$result["texto"] = $mysqli->error;
	}

	if (count($lista_datos) == 0)
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["texto"] = "No se encontraron registros";
	}
	elseif (count($lista_datos) > 0)
	{
		$result["http_code"] = 200;
		$result["titulo"] = "Se obtuvo datos";
		$result["texto"] = $lista_datos;
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_canal_venta_editar")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['form_modal_sec_mantenimiento_canal_venta_param_id'];
		$param_servicio = $_POST['form_modal_sec_mantenimiento_canal_venta_param_servicio'];
		$param_aplica_liquidacion = $_POST['form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion'];
		$param_nombre = $_POST['form_modal_sec_mantenimiento_canal_venta_param_nombre'];
		$param_codigo = $_POST['form_modal_sec_mantenimiento_canal_venta_param_codigo'];
		$param_descripcion = $_POST['form_modal_sec_mantenimiento_canal_venta_param_descripcion'];
		$param_color_hexadecimal = $_POST['form_modal_sec_mantenimiento_canal_venta_param_color_hexadecimal'];
		$param_pago_manual = $_POST['form_modal_sec_mantenimiento_canal_venta_param_aplica_pago_manual'];
		$param_estado = $_POST['form_modal_sec_mantenimiento_canal_venta_param_estado'];
		
		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_canales_venta 
				SET servicio_id = '".$param_servicio."',
					en_liquidacion = '".$param_aplica_liquidacion."',
					nombre = '".$param_nombre."',
					codigo = '".$param_codigo."',
					descripcion = '".$param_descripcion."',
					hex_color = '".$param_color_hexadecimal."',
					pago_manual = '".$param_pago_manual."',
					estado = '".$param_estado."',
					user_updated_id = '".$usuario_id."',
					updated_at = '".$fecha."'
			WHERE id = {$param_id}
		";

		$mysqli->query($query_update);

		if($mysqli->error)
        {
            $error = $mysqli->error;

            $result["http_code"] = 400;
            $result["titulo"] = "Error al editar.";
            $result["texto"] = $error;
            $result["query"] = $query_update;

            echo json_encode($result);
            exit();
        }

        $result["http_code"] = 200;
		$result["titulo"] = "Edición exitosa";
		$result["texto"] = "El Canal de Venta se editó exitosamente";
		
		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida.";
        $result["texto"] ="Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_canal_venta_activar_desactivar_canal_venta")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['param_canal_venta_id'];
		$param_valor = $_POST['param_valor'];
		
		$titulo = "";
		$titulo_error = "";

	    if($param_valor == 1)
	    {
	        // Activar
	        $titulo = "Activación existosa";
	        $titulo_error = "activar";
	    }
	    else
	    {
	        //Desactivar
	        $titulo = "Desactivación existosa";
	        $titulo_error = "desactivar";
	    }

		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_canales_venta 
				SET estado = '".$param_valor."',
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
            $result["texto"] = $error;
            $result["query"] = $query_update;

            echo json_encode($result);
            exit();
        }

        $result["http_code"] = 200;
		$result["titulo"] = $titulo;
		$result["texto"] = "El Canal de Venta se actualizó exitosamente";
		
		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida.";
        $result["texto"] ="Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}
?>