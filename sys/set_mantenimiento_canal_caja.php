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


if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_canal_caja_listar_canal_caja")
{
	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'canal_caja' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];

	$query = 
    "
		SELECT
			ct.id, ct.cdv_id, cv.nombre AS canal_venta_nombre,
		    ct.nombre, ct.descripcion, ct.in, ct.out, ct.ord, ct.estado
		FROM tbl_caja_detalle_tipos ct
			LEFT JOIN tbl_canales_venta cv
			ON cv.id = ct.cdv_id
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
				<a onclick="sec_mantenimiento_canal_caja_obtener_canal_caja('.$reg->id.');";
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
					<a onclick="sec_mantenimiento_canal_caja_activar_desactivar_canal_caja('.$reg->id.', 0);";
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
	        		<a onclick="sec_mantenimiento_canal_caja_activar_desactivar_canal_caja('.$reg->id.', 1);";
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
			"1" => $reg->canal_venta_nombre,
			"2" => $reg->nombre,
			"3" => $reg->descripcion,
			"4" => ($reg->in == 1 ? 'Si' : 'No'),
			"5" => ($reg->out == 1 ? 'Si' : 'No'),
			"6" => $reg->ord,
			"7" => ($reg->estado == 1 ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'),
			"8" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mantenimiento_canal_caja_listar_canal_venta") 
{
	
	$usuario_id = $login?$login['id']:null;
	
	if((int)$usuario_id > 0)
	{
		$query = "";

		$query = 
	    "
	        SELECT
				id, nombre
			FROM tbl_canales_venta
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_canal_caja_nuevo")
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_canal_venta = $_POST['form_modal_sec_mantenimiento_canal_caja_param_canal_venta'];
		$param_nombre = $_POST['form_modal_sec_mantenimiento_canal_caja_param_nombre'];
		$param_descripcion = $_POST['form_modal_sec_mantenimiento_canal_caja_param_descripcion'];
		$param_in = $_POST['form_modal_sec_mantenimiento_canal_caja_param_in'];
		$param_out = $_POST['form_modal_sec_mantenimiento_canal_caja_param_out'];
		$param_ord = $_POST['form_modal_sec_mantenimiento_canal_caja_param_ord'];

		$campos_cabecera = "";
		$campos_valores = "";

		if($param_ord != "")
		{
			$campos_cabecera .= ", ord";
			$campos_valores .= " , '".$param_ord."' ";
		}
		
		$error = '';
		
		$query_insert = 
		"
			INSERT INTO tbl_caja_detalle_tipos
			(
				cdv_id,
				nombre,
				descripcion,
				`in`,
				`out`
				".$campos_cabecera.",
				estado,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'".$param_canal_venta."',
				'".$param_nombre."',
				'".$param_descripcion."',
				'".$param_in."',
				'".$param_out."'
				".$campos_valores.",
				'1',
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
			$result["query"] = $query_insert;

			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;
		$result["titulo"] = "Registro exitoso";
		$result["texto"] = "El canal tipo de caja se registró exitosamente";
		
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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_canal_caja_obtener_canal_caja")
{
	$param_canal_caja_id = $_POST["param_canal_caja_id"];
	
	$query = 
	"
		SELECT
			ct.id, ct.cdv_id, cv.nombre AS canal_venta_nombre,
			ct.nombre, ct.descripcion, ct.in, ct.out, ct.ord, ct.estado
		FROM tbl_caja_detalle_tipos ct
			LEFT JOIN tbl_canales_venta cv
			ON cv.id = ct.cdv_id
		WHERE ct.id = {$param_canal_caja_id}
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_canal_caja_editar")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['form_modal_sec_mantenimiento_canal_caja_param_id'];
		$param_canal_venta = $_POST['form_modal_sec_mantenimiento_canal_caja_param_canal_venta'];
		$param_nombre = $_POST['form_modal_sec_mantenimiento_canal_caja_param_nombre'];
		$param_descripcion = $_POST['form_modal_sec_mantenimiento_canal_caja_param_descripcion'];
		$param_in = $_POST['form_modal_sec_mantenimiento_canal_caja_param_in'];
		$param_out = $_POST['form_modal_sec_mantenimiento_canal_caja_param_out'];
		$param_ord = $_POST['form_modal_sec_mantenimiento_canal_caja_param_ord'];
		
		$campos_cabecera = "";
		$campos_valores = "";

		if($param_ord != "")
		{
			$campos_cabecera .= ", ord = '".$param_ord."' ";
			//$campos_valores .= " , '".$param_ord."' ";
		}

		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_caja_detalle_tipos 
				SET cdv_id = '".$param_canal_venta."',
					nombre = '".$param_nombre."',
					descripcion = '".$param_descripcion."',
					`in` = '".$param_in."',
					`out` = '".$param_out."'
					".$campos_cabecera.",
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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_canal_caja_activar_desactivar_canal_caja")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['param_canal_caja_id'];
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
			UPDATE tbl_caja_detalle_tipos 
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