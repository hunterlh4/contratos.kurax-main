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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_producto_listar_producto")
{
	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'producto' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];

    $query = 
    "
		SELECT
			p.id, p.nombre, p.descripcion, 
			cv.id AS canal_venta_id, cv.nombre AS canal_venta_nombre, p.estado
		FROM tbl_productos p
		LEFT JOIN tbl_canales_venta cv
		ON cv.id = p.cdv_id
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
				<a onclick="sec_mantenimiento_producto_obtener_producto('.$reg->id.');";
               		class="btn btn-info btn-sm"
                	data-toggle="tooltip" data-placement="top" title="Editar">
                    <span class="fa fa-pencil"></span>
                </a>
			';
		}

		if(in_array("ver_historial", $usuario_permisos[$menu_permiso]))
		{
			$botones .= 
			'
				<a onclick="sec_mantenimiento_producto_historial_cambios('.$reg->id.');";
               		class="btn btn-warning btn-sm"
                	data-toggle="tooltip" data-placement="top" title="Historial de cambios">
                    <span class="fa fa-history"></span>
                </a>
			';
		}

		if($reg->estado == 1)
		{
			if(in_array("desactivar", $usuario_permisos[$menu_permiso]))
			{
				$botones .= 
				'
					<a onclick="sec_mantenimiento_producto_activar_desactivar_producto('.$reg->id.', 0);";
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
	        		<a onclick="sec_mantenimiento_producto_activar_desactivar_producto('.$reg->id.', 1);";
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
			"1" => $reg->nombre,
			"2" => $reg->descripcion,
			"3" => $reg->canal_venta_nombre,
			"4" => ($reg->estado == 1 ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'),
			"5" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mantenimiento_producto_listar_canal_venta") 
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
			$result["texto"] = "No se encontro resultados.";
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
			$result["texto"] = "No existen registros.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_producto_nuevo")
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_nombre = $_POST['form_modal_sec_mantenimiento_producto_param_nombre'];
		$param_descripcion = $_POST['form_modal_sec_mantenimiento_producto_param_descripcion'];
		$param_canal_venta = $_POST['form_modal_sec_mantenimiento_producto_param_canal_venta'];

		$error = '';
		
		$query_insert = 
		"
			INSERT INTO tbl_productos
			(
				nombre,
				descripcion,
				cdv_id,
				estado,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'".$param_nombre."',
				'".$param_descripcion."',
				'".$param_canal_venta."',
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

			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;
		$result["titulo"] = "Registro exitoso";
		$result["texto"] = "El producto se registró exitosamente.";
		
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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_producto_obtener_producto")
{
	$param_producto_id = $_POST["param_producto_id"];
	
	$query = 
	"
		SELECT
			p.id, p.nombre, p.descripcion, 
			cv.id AS canal_venta_id, cv.nombre AS canal_venta_nombre, p.estado
		FROM tbl_productos p
			LEFT JOIN tbl_canales_venta cv
			ON cv.id = p.cdv_id
		WHERE p.id = {$param_producto_id}
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

	if(count($lista_datos) == 0)
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["texto"] = "No se encontraron registros.";
	}
	elseif(count($lista_datos) > 0)
	{
		$result["http_code"] = 200;
		$result["titulo"] = "Se obtuvo datos";
		$result["texto"] = $lista_datos;
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_producto_editar")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['form_modal_sec_mantenimiento_producto_param_id'];
		$param_nombre = $_POST['form_modal_sec_mantenimiento_producto_param_nombre'];
		$param_descripcion = $_POST['form_modal_sec_mantenimiento_producto_param_descripcion'];
		$param_canal_venta = $_POST['form_modal_sec_mantenimiento_producto_param_canal_venta'];
		
		$error = '';
		
		//INICIO: GUARDAR EN LA TABLA DE HISTORIAL

        $query_select = 
		"
			SELECT
				id, nombre, descripcion, cdv_id, estado
			FROM tbl_productos
			WHERE id = {$param_id}
		";

		$query = $mysqli->query($query_select);
	    $cant_registro = $query->num_rows;

	    if($cant_registro > 0)
	    {
	        $reg = $query->fetch_assoc();
	        $select_id = $reg["id"];
	        $select_nombre = $reg["nombre"];
	        $select_descripcion = $reg["descripcion"];
	        $select_cdv_id = $reg["cdv_id"];
	        $select_estado = $reg["estado"];

	        $query_insert = 
			"
				INSERT INTO tbl_productos_historial_cambios
				(
					tbl_productos_id,
					nombre,
					descripcion,
					cdv_id,
					estado,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					'".$select_id."',
					'".$select_nombre."',
					'".$select_descripcion."',
					'".$select_cdv_id."',
					'".$select_estado."',
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
				$result["titulo"] = "Error al registrar el historia de cambios.";
				$result["texto"] = $query_insert;
				$result["query"] = $query_insert;

				echo json_encode($result);
				exit();
			}
	    }
	    else
	    {
	    	$result["http_code"] = 400;
            $result["titulo"] = "Error.";
            $result["texto"] = "No se encontro el registro del producto.";

            echo json_encode($result);
            exit();
	    }

        //FIN: GUARDAR EN LA TABLA DE HISTORIAL

		$query_update = 
		"
			UPDATE tbl_productos 
				SET nombre = '".$param_nombre."',
					descripcion = '".$param_descripcion."',
					cdv_id = '".$param_canal_venta."',
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
		$result["texto"] = "El Producto se editó exitosamente.";
		
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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_producto_activar_desactivar_producto")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['param_producto_id'];
		$param_valor = $_POST['param_valor'];
		
		$titulo = "";
		$titulo_texto = "";
		$titulo_error = "";

		//INICIO: GUARDAR EN LA TABLA DE HISTORIAL

        $query_select = 
		"
			SELECT
				id, nombre, descripcion, cdv_id, estado
			FROM tbl_productos
			WHERE id = {$param_id}
		";

		$query = $mysqli->query($query_select);
	    $cant_registro = $query->num_rows;

	    if($cant_registro > 0)
	    {
	        $reg = $query->fetch_assoc();
	        $select_id = $reg["id"];
	        $select_nombre = $reg["nombre"];
	        $select_descripcion = $reg["descripcion"];
	        $select_cdv_id = $reg["cdv_id"];
	        $select_estado = $reg["estado"];

	        $query_insert = 
			"
				INSERT INTO tbl_productos_historial_cambios
				(
					tbl_productos_id,
					nombre,
					descripcion,
					cdv_id,
					estado,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					'".$select_id."',
					'".$select_nombre."',
					'".$select_descripcion."',
					'".$select_cdv_id."',
					'".$select_estado."',
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
				$result["titulo"] = "Error al registrar el historia de cambios.";
				$result["texto"] = $query_insert;
				$result["query"] = $query_insert;

				echo json_encode($result);
				exit();
			}
	    }
	    else
	    {
	    	$result["http_code"] = 400;
            $result["titulo"] = "Error.";
            $result["texto"] = "No se encontro el registro del producto.";

            echo json_encode($result);
            exit();
	    }

        //FIN: GUARDAR EN LA TABLA DE HISTORIAL

	    if($param_valor == 1)
	    {
	        // Activar
	        $titulo = "Activación exitosa";
	        $titulo_texto = "El Producto se activó exitosamente.";
	        $titulo_error = "activar";
	    }
	    else
	    {
	        //Desactivar
	        $titulo = "Desactivación exitosa";
	        $titulo_texto = "El Producto se desactivó exitosamente.";
	        $titulo_error = "desactivar";
	    }

		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_productos 
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
		$result["texto"] = $titulo_texto;
		
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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mantenimiento_producto_historial_cambios")
{
	$param_producto_id = $_POST['param_producto_id'];

	$query = 
	"
		SELECT
			p.id, CONVERT(p.nombre USING utf8) AS nombre, 
			CONVERT(p.descripcion USING utf8) AS descripcion,
			CONVERT(cv.id USING utf8) AS canal_venta_id, 
			CONVERT(cv.nombre USING utf8) AS canal_venta_nombre, 
			CONVERT(p.estado USING utf8) AS estado,
			CONVERT(concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) USING utf8)  AS usuario_actualizacion,
			CONVERT(p.updated_at USING utf8) AS fecha_actualizacion, 
			CONVERT('Actual' USING utf8) AS situacion
		FROM tbl_productos p
			LEFT JOIN tbl_canales_venta cv
			ON cv.id = p.cdv_id
			LEFT JOIN tbl_usuarios tu
			ON p.user_created_id = tu.id
			LEFT JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE p.id = {$param_producto_id}
		UNION ALL
		SELECT
			ph.id, CONVERT(ph.nombre USING utf8) AS nombre, 
			CONVERT(ph.descripcion USING utf8) AS descripcion,
			CONVERT(cv.id USING utf8) AS canal_venta_id, 
			CONVERT(cv.nombre USING utf8) AS canal_venta_nombre, 
			CONVERT(ph.estado USING utf8) AS estado,
			CONVERT(concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) USING utf8) AS usuario_actualizacion,
			CONVERT(ph.updated_at USING utf8) AS fecha_actualizacion, 
			CONVERT('Historial' USING utf8) AS situacion
		FROM tbl_productos_historial_cambios ph
			LEFT JOIN tbl_canales_venta cv
			ON cv.id = ph.cdv_id
			LEFT JOIN tbl_usuarios tu
			ON ph.user_created_id = tu.id
			LEFT JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE ph.tbl_productos_id = {$param_producto_id}
		ORDER BY situacion ASC, fecha_actualizacion DESC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->descripcion,
			"3" => $reg->canal_venta_nombre,
			"4" => ($reg->estado == 1 ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'),
			"5" => $reg->usuario_actualizacion,
			"6" => $reg->fecha_actualizacion,
			"7" => $reg->situacion
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
?>