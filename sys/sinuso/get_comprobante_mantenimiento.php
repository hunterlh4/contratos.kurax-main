<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   PROVEEDOR

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mantenimiento_proveedor_listar")
{
    $usuario_id = $login ? $login['id'] : null;

	$query = "
        SELECT
            id,nombre,ruc,status
        FROM tbl_comprobante_proveedor c
        WHERE 1=1 
        ORDER BY created_at DESC
	";

	$list_query = $mysqli->query($query);

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{

        $estadoHTML = ($reg->status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

        $botones = "";

        if($reg->status == 1){
            $botones = '<button type=button" class="btn btn-rounded btn-primary btn-xs" onclick="sec_comp_mant_proveedor_obtener('.$reg->id.')">
                            <i class="fa fa-pencil"></i>												
                        </button>
                        <a class="btn btn-rounded btn-danger btn-xs" data-toggle="tooltip" data-placement="top"
                    onclick="sec_comp_mant_proveedor_eliminar(' .$reg->id. ')"
                    title="Eliminar">
                    <i class="fa fa-trash"></i>
                </a>';
        }
        
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->ruc,
			"3" => $estadoHTML,
			"4" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mantenimiento_proveedor_verificar_ruc")
{
	$ruc = $_POST["ruc"];
	$id_proveedor = $_POST["id_proveedor"];

	// Consultar si el número de recibo ya fue registrado
	$selectQuery = "
					SELECT 
						sd.ruc,
						sd.nombre,
						u.usuario
					FROM tbl_comprobante_proveedor sd
					LEFT JOIN tbl_usuarios u
					ON sd.user_created_id = u.id
					LEFT JOIN tbl_personal_apt p
					ON p.id = u.personal_id
					WHERE sd.status = '1' AND sd.id<> ? AND sd.ruc=?
					ORDER BY sd.ruc ASC
					LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_proveedor, $ruc);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($ruc, $nombre, $usuario);
		$selectStmt->fetch();

		$result["http_code"] = 200;
		$result["titulo"] = "El RUC: ". $ruc ." ya fue registrado con el nombre:".$nombre." por el usuario:". $usuario;
	}else{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mantenimiento_proveedor_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    id, 
                    ruc,
                    nombre
                FROM tbl_comprobante_proveedor 
                WHERE id=?
                ORDER BY nombre ASC
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($id_proveedor, $ruc, $nombre);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $id_proveedor,
                        'ruc' => $ruc,
                        'nombre' => $nombre
                    ],
                ]);
            } else {
                echo json_encode([
                    'status' => 404,
                    'message' => 'No se encontraron datos para el ID proporcionado.',
                ]);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                'status' => 500,
                'message' => 'Error en la consulta SQL: ' . $e->getMessage(),
            ]);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'ID no válido']);
    }
}

/////////    MOTIVO


if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mantenimiento_motivo_listar")
{
    $usuario_id = $login ? $login['id'] : null;

	$query = "
        SELECT
            id,nombre,descripcion,status
        FROM tbl_comprobante_motivo_reversion c
        ORDER BY created_at DESC
	";

	$list_query = $mysqli->query($query);

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{

        $estadoHTML = ($reg->status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

        $botones = "";

        if($reg->status == 1){
            $botones = '<button type=button" class="btn btn-rounded btn-primary btn-xs" onclick="sec_comp_mant_motivo_obtener('.$reg->id.')">
                            <i class="fa fa-pencil"></i>												
                        </button>
                        <a class="btn btn-rounded btn-danger btn-xs" data-toggle="tooltip" data-placement="top"
                    onclick="sec_comp_mant_motivo_eliminar(' .$reg->id. ')"
                    title="Eliminar">
                    <i class="fa fa-trash"></i>
                </a>';
        }

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->descripcion,
			"3" => $estadoHTML,
			"4" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mantenimiento_motivo_verificar_nombre")
{
	$nombre = $_POST["nombre"];
	$id_motivo = $_POST["id_motivo"];

	// Consultar si el número de recibo ya fue registrado
	$selectQuery = "
					SELECT 
						sd.nombre,
                        sd.descripcion,
						u.usuario
					FROM tbl_comprobante_motivo_reversion sd
					LEFT JOIN tbl_usuarios u
					ON sd.user_created_id = u.id
					LEFT JOIN tbl_personal_apt p
					ON p.id = u.personal_id
					WHERE sd.status = '1' AND sd.id<> ? AND sd.nombre=?
					LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_motivo, $nombre);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($nombre_motivo, $descripcion, $usuario);
		$selectStmt->fetch();

		$result["http_code"] = 200;
		$result["titulo"] = "El motivo: ". $nombre_motivo ." ya fue registrado con el nombre:".$descripcion." por el usuario:". $usuario;
	}else{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mantenimiento_motivo_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    id, 
                    nombre,
                    descripcion
                FROM tbl_comprobante_motivo_reversion 
                WHERE id=?
                ORDER BY nombre ASC
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($id_motivo, $nombre, $descripcion);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $id_motivo,
                        'descripcion' => $descripcion,
                        'nombre' => $nombre
                    ],
                ]);
            } else {
                echo json_encode([
                    'status' => 404,
                    'message' => 'No se encontraron datos para el ID proporcionado.',
                ]);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                'status' => 500,
                'message' => 'Error en la consulta SQL: ' . $e->getMessage(),
            ]);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'ID no válido']);
    }
}

