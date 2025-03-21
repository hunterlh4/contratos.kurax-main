<?php

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_mantenimiento_grupo_billetera_listar_billetera")
{
	$query = "	SELECT
					b.id, b.nombre, b.descripcion,
					b.status AS estado,
					IF(u.personal_id is NULL, u.usuario, concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, ''))) AS usuario,
					b.created_at AS fecha_creacion
				FROM tbl_billeteras b
					inner JOIN tbl_usuarios u
					ON b.user_created_id = u.id
					left JOIN tbl_personal_apt p
					ON u.personal_id = p.id
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$botones = '';

		$botones .= 
		'
			<a onclick="sec_billetera_mantenimiento_grupo_billetera_obtener_billetera('.$reg->id.');";
           		class="btn btn-warning btn-sm"
            	data-toggle="tooltip" data-placement="top" title="Editar">
                <span class="fa fa-pencil"></span>
            </a>
		';

		if($reg->estado == 1)
		{
			$botones .= 
			'
				<a onclick="sec_billetera_mantenimiento_grupo_billetera_activar_desactivar_billetera('.$reg->id.', 0);";
	           		class="btn btn-danger btn-sm"
	            	data-toggle="tooltip" data-placement="top" title="Desactivar">
	                <span class="fa fa-close"></span>
	            </a>
			';
		}
		else 
		{
			$botones .= 
			'
        		<a onclick="sec_billetera_mantenimiento_grupo_billetera_activar_desactivar_billetera('.$reg->id.', 1);";
	           		class="btn btn-primary btn-sm"
	            	data-toggle="tooltip" data-placement="top" title="Activar">
	                <span class="fa fa-check"></span>
	            </a>
        	';
		}

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->descripcion,
			"3" => ($reg->estado == 1) ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Desactivado</span>',
			"4" => $reg->usuario,
			"5" => $reg->fecha_creacion,
			"6" => $botones
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"query" => $query,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_mantenimiento_grupo_billetera_guardar") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";
	$respuesta_titulo = "";

	if((int)$login_usuario_id > 0)
	{
		$param_id = $_POST["param_id"];
		$param_nombre = $_POST["param_nombre"];
		$param_descripcion = $_POST["param_descripcion"];
		$tipo_accion = $_POST["tipo_accion"];

		if($tipo_accion == 1)
		{
			// INSERT

			// INICIO: VERIFICAR SI EL NOMBRE YA EXISTE
			$query_verificar = 
			"
				SELECT
					id, nombre, descripcion
				FROM tbl_billeteras
				WHERE LOWER(nombre) = LOWER('".$param_nombre."') 
			";

			$query_verificar_data = $mysqli->query($query_verificar);

			$row_count = mysqli_num_rows($query_verificar_data);

			if($row_count > 0)
			{
			    $result["http_code"] = 400;
				$result["titulo"] = "Ya existe";
				$result["descripcion"] = "El nombre ya se encuentra registrado.";

				echo json_encode($result);
				exit();
			}

			// FIN: VERIFICAR SI EL NOMBRE YA EXISTE

			$query = 
			"
				INSERT INTO tbl_billeteras
				(
					nombre,
					descripcion,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					'".$param_nombre."',
					'".$param_descripcion."',
					1,
					'".$login_usuario_id."',
					'".$created_at."',
					'".$login_usuario_id."',
					'".$created_at."'
				)
			";

			$respuesta_titulo = "Registro exitoso";
		}
		else
		{
			// UPDATE
			
			$query = 
			"
				UPDATE tbl_billeteras 
					SET nombre = '".$param_nombre."',
						descripcion = '".$param_descripcion."',
						user_updated_id = '".$login_usuario_id."',
						updated_at = '".$created_at."'
				WHERE id = '".$param_id."'
			";

			$respuesta_titulo = "Edición exitoso";
		}

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if($error == '') 
		{
			$result["http_code"] = 200;
			$result["titulo"] = $respuesta_titulo;
			$result["descripcion"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
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

if(isset($_POST["accion"]) && $_POST["accion"] === "sec_billetera_mantenimiento_grupo_billetera_obtener_billetera")
{
	$param_billetera_id = $_POST["param_billetera_id"];
	
	$query = 
	"
		SELECT
			id, nombre, descripcion
		FROM tbl_billeteras
		WHERE id = {$param_billetera_id}
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
		$result["descripcion"] = $mysqli->error;
	}

	if (count($lista_datos) == 0)
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros.";
	}
	elseif (count($lista_datos) > 0)
	{
		$result["http_code"] = 200;
		$result["titulo"] = "Se obtuvo datos";
		$result["descripcion"] = "";
		$result["data"] = $lista_datos;
	}

	echo json_encode($result);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"] === "sec_billetera_mantenimiento_grupo_billetera_activar_desactivar_billetera")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['param_billetera_id'];
		$param_valor = $_POST['param_valor'];
		
		$titulo = "";
		$descripcion = "";
		$titulo_error = "";

	    if($param_valor == 1)
	    {
	        // Activar
	        $titulo = "Activación existosa";
	        $descripcion = "La billetera se activo exitosamente.";
	        $titulo_error = "activar";
	    }
	    else
	    {
	        //Desactivar
	        $titulo = "Desactivación existosa";
	        $descripcion = "La billetera se desactivo exitosamente.";
	        $titulo_error = "desactivar";
	    }

		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_billeteras 
				SET status = '".$param_valor."',
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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_mantenimiento_grupo_billetera_cuenta_listar_billetera_cuenta")
{
	$query = "	SELECT
					bc.id, bc.numero_cuenta, bc.numero_cci, bc.nombre_corto,
					bc.banco_id, b.nombre AS banco, 
					bc.razon_social_id, rz.nombre AS empresa,
					bc.status AS estado,
					IF(u.personal_id is NULL, u.usuario, concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')))  AS usuario,
					bc.created_at AS fecha_creacion
				FROM tbl_billetera_cuentas bc
					INNER JOIN tbl_bancos b
					ON bc.banco_id = b.id
					INNER JOIN tbl_razon_social rz
					ON bc.razon_social_id = rz.id
					INNER JOIN tbl_usuarios u
					ON bc.user_created_id = u.id
					LEFT JOIN tbl_personal_apt p
					ON u.personal_id = p.id
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$botones = '';

		$botones .= 
		'
			<a onclick="sec_billetera_mantenimiento_grupo_billetera_cuenta_obtener_billetera_cuenta('.$reg->id.');";
           		class="btn btn-warning btn-sm"
            	data-toggle="tooltip" data-placement="top" title="Editar">
                <span class="fa fa-pencil"></span>
            </a>
		';

		if($reg->estado == 1)
		{
			$botones .= 
			'
				<a onclick="sec_billetera_mantenimiento_grupo_billetera_cuenta_activar_desactivar_billetera_cuenta('.$reg->id.', 0);";
	           		class="btn btn-danger btn-sm"
	            	data-toggle="tooltip" data-placement="top" title="Desactivar">
	                <span class="fa fa-close"></span>
	            </a>
			';
		}
		else 
		{
			$botones .= 
			'
        		<a onclick="sec_billetera_mantenimiento_grupo_billetera_cuenta_activar_desactivar_billetera_cuenta('.$reg->id.', 1);";
	           		class="btn btn-primary btn-sm"
	            	data-toggle="tooltip" data-placement="top" title="Activar">
	                <span class="fa fa-check"></span>
	            </a>
        	';
		}

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->numero_cuenta,
			"2" => $reg->numero_cci,
			"3" => $reg->nombre_corto,
			"4" => $reg->banco,
			"5" => $reg->empresa,
			"6" => ($reg->estado == 1) ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Desactivado</span>',
			"7" => $reg->usuario,
			"8" => $reg->fecha_creacion,
			"9" => $botones
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"query" => $query,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_mantenimiento_grupo_billetera_cuenta_guardar") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";
	$respuesta_titulo = "";

	if((int)$login_usuario_id > 0)
	{
		$param_id = $_POST["param_id"];
		$param_num_cuenta = $_POST["param_num_cuenta"];
		$param_num_cuenta_cci = $_POST["param_num_cuenta_cci"];
		$param_nombre_corto = $_POST["param_nombre_corto"];
		$param_tipo_banco = $_POST["param_tipo_banco"];
		$param_tipo_empresa = $_POST["param_tipo_empresa"];
		$tipo_accion = $_POST["tipo_accion"];

		if($tipo_accion == 1)
		{
			// INSERT

			// INICIO: VERIFICAR SI EL NUMERO DE CUENTA YA EXISTE
			$query_verificar = 
			"
				SELECT
					id, numero_cuenta
				FROM tbl_billetera_cuentas
				WHERE LOWER(numero_cuenta) = LOWER('".$param_num_cuenta."') 
					AND banco_id = '".$param_tipo_banco."'
					AND razon_social_id = '".$param_tipo_empresa."'
			";

			$query_verificar_data = $mysqli->query($query_verificar);

			$row_count = mysqli_num_rows($query_verificar_data);

			if($row_count > 0)
			{
			    $result["http_code"] = 400;
				$result["titulo"] = "Ya existe";
				$result["descripcion"] = "El Número de cuenta ya se encuentra registrado.";

				echo json_encode($result);
				exit();
			}

			// FIN: VERIFICAR SI EL NUMERO DE CUENTA YA EXISTE

			$query = 
			"
				INSERT INTO tbl_billetera_cuentas
				(
					numero_cuenta,
					numero_cci,
					nombre_corto,
					banco_id,
					razon_social_id,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					'".$param_num_cuenta."',
					'".$param_num_cuenta_cci."',
					'".$param_nombre_corto."',
					'".$param_tipo_banco."',
					'".$param_tipo_empresa."',
					1,
					'".$login_usuario_id."',
					'".$created_at."',
					'".$login_usuario_id."',
					'".$created_at."'
				)
			";

			$respuesta_titulo = "Registro exitoso";
		}
		else
		{
			// UPDATE
			
			$query = 
			"
				UPDATE tbl_billetera_cuentas 
					SET numero_cuenta = '".$param_num_cuenta."',
						numero_cci = '".$param_num_cuenta_cci."',
						nombre_corto = '".$param_nombre_corto."',
						banco_id = '".$param_tipo_banco."',
						razon_social_id = '".$param_tipo_empresa."',
						user_updated_id = '".$login_usuario_id."',
						updated_at = '".$created_at."'
				WHERE id = '".$param_id."'
			";

			$respuesta_titulo = "Edición exitoso";
		}

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if($error == '') 
		{
			$result["http_code"] = 200;
			$result["titulo"] = $respuesta_titulo;
			$result["descripcion"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
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

if(isset($_POST["accion"]) && $_POST["accion"] === "sec_billetera_mantenimiento_grupo_billetera_cuenta_obtener_billetera_cuenta")
{
	$param_billetera_cuenta_id = $_POST["param_billetera_cuenta_id"];
	
	$query = 
	"
		SELECT
			id, numero_cuenta, numero_cci, nombre_corto, banco_id, razon_social_id
		FROM tbl_billetera_cuentas
		WHERE id = {$param_billetera_cuenta_id}
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
		$result["descripcion"] = $mysqli->error;
	}

	if (count($lista_datos) == 0)
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros.";
	}
	elseif (count($lista_datos) > 0)
	{
		$result["http_code"] = 200;
		$result["titulo"] = "Se obtuvo datos";
		$result["descripcion"] = "";
		$result["data"] = $lista_datos;
	}

	echo json_encode($result);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"] === "sec_billetera_mantenimiento_grupo_billetera_cuenta_activar_desactivar_billetera_cuenta")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['param_billetera_cuenta_id'];
		$param_valor = $_POST['param_valor'];
		
		$titulo = "";
		$descripcion = "";
		$titulo_error = "";

	    if($param_valor == 1)
	    {
	        // Activar
	        $titulo = "Activación existosa";
	        $descripcion = "La cuenta se activo exitosamente.";
	        $titulo_error = "activar";
	    }
	    else
	    {
	        //Desactivar
	        $titulo = "Desactivación existosa";
	        $descripcion = "La cuenta se desactivo exitosamente.";
	        $titulo_error = "desactivar";
	    }

		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_billetera_cuentas 
				SET status = '".$param_valor."',
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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_mantenimiento_grupo_billetera_telefono_listar_billetera_telefono")
{
	$query = "	SELECT
					bt.id, bt.numero_telefono,
					bt.billetera_cuenta_id, bc.numero_cuenta, bc.numero_cci, 
					bc.banco_id, tb.nombre AS banco, 
					bc.razon_social_id, rz.nombre AS empresa,
					bt.billetera_id, b.nombre AS billetera,
					bt.status AS estado,
					IF(u.personal_id is NULL, u.usuario, concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')))  AS usuario_creacion,
					bt.created_at AS fecha_creacion
				FROM tbl_billetera_telefonos bt
					INNER JOIN tbl_billetera_cuentas bc
					ON bt.billetera_cuenta_id = bc.id
					INNER JOIN tbl_bancos tb
					ON bc.banco_id = tb.id
					INNER JOIN tbl_razon_social rz
					ON bc.razon_social_id = rz.id
					INNER JOIN tbl_billeteras b
					ON bt.billetera_id = b.id
					INNER JOIN tbl_usuarios u
					ON bt.user_created_id = u.id
					LEFT JOIN tbl_personal_apt p
					ON u.personal_id = p.id
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$botones = '';

		$botones .= 
		'
			<a onclick="sec_billetera_mantenimiento_grupo_billetera_telefono_obtener_billetera_telefono('.$reg->id.');";
           		class="btn btn-warning btn-sm"
            	data-toggle="tooltip" data-placement="top" title="Editar">
                <span class="fa fa-pencil"></span>
            </a>
		';

		if($reg->estado == 1)
		{
			$botones .= 
			'
				<a onclick="sec_billetera_mantenimiento_grupo_billetera_telefono_activar_desactivar_billetera_telefono('.$reg->id.', 0);";
	           		class="btn btn-danger btn-sm"
	            	data-toggle="tooltip" data-placement="top" title="Desactivar">
	                <span class="fa fa-close"></span>
	            </a>
			';
		}
		else 
		{
			$botones .= 
			'
        		<a onclick="sec_billetera_mantenimiento_grupo_billetera_telefono_activar_desactivar_billetera_telefono('.$reg->id.', 1);";
	           		class="btn btn-primary btn-sm"
	            	data-toggle="tooltip" data-placement="top" title="Activar">
	                <span class="fa fa-check"></span>
	            </a>
        	';
		}

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->numero_telefono,
			"2" => $reg->billetera,
			"3" => $reg->numero_cuenta,
			"4" => $reg->numero_cci,
			"5" => $reg->banco,
			"6" => $reg->empresa,
			"7" => ($reg->estado == 1) ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Desactivado</span>',
			"8" => $reg->usuario_creacion,
			"9" => $reg->fecha_creacion,
			"10" => $botones
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"query" => $query,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_mantenimiento_grupo_billetera_telefono_guardar") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";
	$respuesta_titulo = "";

	if((int)$login_usuario_id > 0)
	{
		$param_id = $_POST["param_id"];
		$param_telefono = $_POST["param_telefono"];
		$param_descripcion = $_POST["param_descripcion"];
		$param_numero_cuenta = $_POST["param_numero_cuenta"];
		$param_tipo_billetera = $_POST["param_tipo_billetera"];
		$tipo_accion = $_POST["tipo_accion"];

		if($tipo_accion == 1)
		{
			// INSERT

			// INICIO: VERIFICAR SI EL TELÉFONO YA EXISTE
			$query_verificar = 
			"
				SELECT
					id, numero_telefono
				FROM tbl_billetera_telefonos
				WHERE numero_telefono = '".$param_telefono."' 
					AND billetera_cuenta_id = '".$param_numero_cuenta."' 
				    AND billetera_id = '".$param_tipo_billetera."'
			";

			$query_verificar_data = $mysqli->query($query_verificar);

			$row_count = mysqli_num_rows($query_verificar_data);

			if($row_count > 0)
			{
			    $result["http_code"] = 400;
				$result["titulo"] = "Ya existe";
				$result["descripcion"] = "El Teléfono ya se encuentra registrado con el mismo Número de Cuenta, con el mismo Banco y con la misma Empresa.";

				echo json_encode($result);
				exit();
			}

			// FIN: VERIFICAR SI EL TELÉFONO YA EXISTE

			$query = 
			"
				INSERT INTO tbl_billetera_telefonos
				(
					numero_telefono,
					descripcion,
					billetera_cuenta_id,
					billetera_id,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					'".$param_telefono."',
					'".$param_descripcion."',
					'".$param_numero_cuenta."',
					'".$param_tipo_billetera."',
					1,
					'".$login_usuario_id."',
					'".$created_at."',
					'".$login_usuario_id."',
					'".$created_at."'
				)
			";

			$respuesta_titulo = "Registro exitoso";
		}
		else
		{
			// UPDATE
			
			$query = 
			"
				UPDATE tbl_billetera_telefonos 
					SET numero_telefono = '".$param_telefono."',
						descripcion = '".$param_descripcion."',
						billetera_cuenta_id = '".$param_numero_cuenta."',
						billetera_id = '".$param_tipo_billetera."',
						user_updated_id = '".$login_usuario_id."',
						updated_at = '".$created_at."'
				WHERE id = '".$param_id."'
			";

			$respuesta_titulo = "Edición exitoso";
		}

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if($error == '') 
		{
			$result["http_code"] = 200;
			$result["titulo"] = $respuesta_titulo;
			$result["descripcion"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
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

if(isset($_POST["accion"]) && $_POST["accion"] === "sec_billetera_mantenimiento_grupo_billetera_telefono_obtener_billetera_telefono")
{
	$param_billetera_telefono_id = $_POST["param_billetera_telefono_id"];
	
	$query = 
	"
		SELECT
			id, numero_telefono, descripcion, billetera_cuenta_id, billetera_id
		FROM tbl_billetera_telefonos
		WHERE id = {$param_billetera_telefono_id}
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
		$result["descripcion"] = $mysqli->error;
	}

	if (count($lista_datos) == 0)
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros.";
	}
	elseif (count($lista_datos) > 0)
	{
		$result["http_code"] = 200;
		$result["titulo"] = "Se obtuvo datos";
		$result["descripcion"] = "";
		$result["data"] = $lista_datos;
	}

	echo json_encode($result);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"] === "sec_billetera_mantenimiento_grupo_billetera_telefono_activar_desactivar_billetera_telefono")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['param_billetera_telefono_id'];
		$param_valor = $_POST['param_valor'];
		
		$titulo = "";
		$descripcion = "";
		$titulo_error = "";

	    if($param_valor == 1)
	    {
	        // Activar
	        $titulo = "Activación existosa";
	        $descripcion = "El Teléfono se activo exitosamente.";
	        $titulo_error = "activar";
	    }
	    else
	    {
	        //Desactivar
	        $titulo = "Desactivación existosa";
	        $descripcion = "EL Teléfono se desactivo exitosamente.";
	        $titulo_error = "desactivar";
	    }

		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_billetera_telefonos 
				SET status = '".$param_valor."',
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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_listar_billetera_motivo_rechazo")
{
	$query = 
	"	SELECT
			bmr.id, bmr.nombre, bmr.descripcion, 
		    bmr.status AS estado,
		  	IF(u.personal_id is NULL, u.usuario, concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')))  AS usuario_creacion,
			bmr.created_at AS fecha_creacion
		FROM tbl_billetera_motivos_rechazo bmr
			INNER JOIN tbl_usuarios u
			ON bmr.user_created_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$botones = '';

		$botones .= 
		'
			<a onclick="sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_obtener_billetera_motivo_rechazo('.$reg->id.');";
           		class="btn btn-warning btn-sm"
            	data-toggle="tooltip" data-placement="top" title="Editar">
                <span class="fa fa-pencil"></span>
            </a>
		';

		if($reg->estado == 1)
		{
			$botones .= 
			'
				<a onclick="sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_activar_desactivar_billetera_motivo_rechazo('.$reg->id.', 0);";
	           		class="btn btn-danger btn-sm"
	            	data-toggle="tooltip" data-placement="top" title="Desactivar">
	                <span class="fa fa-close"></span>
	            </a>
			';
		}
		else 
		{
			$botones .= 
			'
        		<a onclick="sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_activar_desactivar_billetera_motivo_rechazo('.$reg->id.', 1);";
	           		class="btn btn-primary btn-sm"
	            	data-toggle="tooltip" data-placement="top" title="Activar">
	                <span class="fa fa-check"></span>
	            </a>
        	';
		}

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->descripcion,
			"3" => ($reg->estado == 1) ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Desactivado</span>',
			"4" => $reg->usuario_creacion,
			"5" => $reg->fecha_creacion,
			"6" => $botones
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"query" => $query,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_guardar") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";
	$respuesta_titulo = "";

	if((int)$login_usuario_id > 0)
	{
		$param_id = $_POST["param_id"];
		$param_nombre = $_POST["param_nombre"];
		$param_descripcion = $_POST["param_descripcion"];
		$tipo_accion = $_POST["tipo_accion"];

		if($tipo_accion == 1)
		{
			// INSERT

			// INICIO: VERIFICAR SI EL NOMBRE YA EXISTE
			$query_verificar = 
			"
				SELECT
					id, nombre, descripcion
				FROM tbl_billetera_motivos_rechazo
				WHERE LOWER(nombre) = LOWER('".$param_nombre."') 
			";

			$query_verificar_data = $mysqli->query($query_verificar);

			$row_count = mysqli_num_rows($query_verificar_data);

			if($row_count > 0)
			{
			    $result["http_code"] = 400;
				$result["titulo"] = "Ya existe";
				$result["descripcion"] = "El nombre ya se encuentra registrado.";

				echo json_encode($result);
				exit();
			}

			// FIN: VERIFICAR SI EL NOMBRE YA EXISTE

			$query = 
			"
				INSERT INTO tbl_billetera_motivos_rechazo
				(
					nombre,
					descripcion,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					'".$param_nombre."',
					'".$param_descripcion."',
					1,
					'".$login_usuario_id."',
					'".$created_at."',
					'".$login_usuario_id."',
					'".$created_at."'
				)
			";

			$respuesta_titulo = "Registro exitoso";
		}
		else
		{
			// UPDATE
			
			$query = 
			"
				UPDATE tbl_billetera_motivos_rechazo 
					SET nombre = '".$param_nombre."',
						descripcion = '".$param_descripcion."',
						user_updated_id = '".$login_usuario_id."',
						updated_at = '".$created_at."'
				WHERE id = '".$param_id."'
			";

			$respuesta_titulo = "Edición exitoso";
		}

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if($error == '') 
		{
			$result["http_code"] = 200;
			$result["titulo"] = $respuesta_titulo;
			$result["descripcion"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
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

if(isset($_POST["accion"]) && $_POST["accion"] === "sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_obtener_billetera_motivo_rechazo")
{
	$param_billetera_motivo_rechazo_id = $_POST["param_billetera_motivo_rechazo_id"];
	
	$query = 
	"
		SELECT
			id, nombre, descripcion
		FROM tbl_billetera_motivos_rechazo
		WHERE id = {$param_billetera_motivo_rechazo_id}
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
		$result["descripcion"] = $mysqli->error;
	}

	if (count($lista_datos) == 0)
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros.";
	}
	elseif (count($lista_datos) > 0)
	{
		$result["http_code"] = 200;
		$result["titulo"] = "Se obtuvo datos";
		$result["descripcion"] = "";
		$result["data"] = $lista_datos;
	}

	echo json_encode($result);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"] === "sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_activar_desactivar_billetera_motivo_rechazo")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');

	if((int)$usuario_id > 0)
	{
		$param_id = $_POST['param_billetera_motivo_rechazo_id'];
		$param_valor = $_POST['param_valor'];
		
		$titulo = "";
		$descripcion = "";
		$titulo_error = "";

	    if($param_valor == 1)
	    {
	        // Activar
	        $titulo = "Activación existosa";
	        $descripcion = "El motivo rechazo se activo exitosamente.";
	        $titulo_error = "activar";
	    }
	    else
	    {
	        //Desactivar
	        $titulo = "Desactivación existosa";
	        $descripcion = "El motivo rechazo se desactivo exitosamente.";
	        $titulo_error = "desactivar";
	    }

		$error = '';
		
		$query_update = 
		"
			UPDATE tbl_billetera_motivos_rechazo 
				SET status = '".$param_valor."',
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
?>