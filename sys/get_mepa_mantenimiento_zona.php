<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_mantenimiento_zona_listar")
{
    $query = "
        SELECT
            za.id,
            za.nombre AS nombre,
            za.centro_costo,
            za.created_at,
            za.status,
            lr.nombre AS red
        FROM mepa_zona_asignacion za
        LEFT JOIN tbl_locales_redes lr
        ON za.tbl_locales_redes_id = lr.id
        ORDER BY za.nombre ASC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{

        $estadoHTML = ($reg->status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

        $botones = '<a onclick="sec_mepa_mantenimiento_zona_ver('.$reg->id.');";
                        class="btn btn-info btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Ver">
                        <span class="fa fa-eye"></span>
                    </a>
                    <a onclick="sec_mepa_mantenimiento_zona_obtener('.$reg->id.');";
                        class="btn btn-warning btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Editar">
                        <span class="fa fa-pencil"></span>
                    </a>';                

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->centro_costo,
			"3" => $reg->red,
			"4" => $estadoHTML,
            "5" => $reg->created_at,
			"6" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_mantenimiento_zona_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    za.id, 
                    za.nombre,
                    za.centro_costo,
                    za.status,
                    za.tbl_locales_redes_id,
                    IFNULL(za.created_at, '') AS created_at,
                    IFNULL(za.updated_at, '') AS updated_at,
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM mepa_zona_asignacion za
                LEFT JOIN tbl_usuarios u ON u.id=za.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id=za.user_updated_id
                WHERE za.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $zona_id, 
                                $nombre, 
                                $centro_costo,
                                $satus,
                                $red_id,
                                $created_at,
                                $updated_at,
                                $usuario_create,
                                $usuario_update);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $zona_id,
                        'nombre' => $nombre,
                        'centro_costo' => $centro_costo,
                        'satus' => $satus,                     
                        'red_id' => $red_id,                    
                        'created_at' =>  $created_at,
                        'updated_at' => $updated_at,
                        'usuario_create' => $usuario_create,
                        'usuario_update' => $usuario_update
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_mantenimiento_zona_red_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT
                id, nombre
            FROM tbl_locales_redes
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}