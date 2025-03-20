<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_metodo_listar")
{

    $estado_id = $_POST["estado_id"];
    $proveedor_id = isset($_POST["proveedor_id"]) ? $_POST["proveedor_id"] : 0;

    $where_proveedor = "";
    $where_estado = "";

    if($estado_id != ""){
        $where_estado = " AND ce.status = $estado_id ";
    }

    if($proveedor_id != 0){
        $where_proveedor = " AND ce.proveedor_id = $proveedor_id ";
    }

    try {

        $selectQuery = "SELECT
                            ce.id,
                            ce.nombre,
                            p.nombre,
                            DATE_FORMAT(ce.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario,
                            ce.status
                        FROM tbl_conci_calimaco_metodo ce
                        LEFT JOIN tbl_usuarios u ON ce.user_created_id = u.id
                        LEFT JOIN tbl_conci_proveedor p ON ce.proveedor_id = p.id
                        WHERE 1=1
                            $where_estado 
                            $where_proveedor 
                        ORDER BY ce.created_at ASC" ; 
        $stmt = $mysqli->prepare($selectQuery);

        //$stmt->bind_param("i", $comprobante_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $proveedor, $created_at, $usuario, $status);

        $data = [];

        while ($stmt->fetch()) {

            $estadoHTML = ($status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

            $botones_accion = "";

            if($status == 1){
                $botones_accion = '<a onclick="conci_mant_metodo_obtener('.$id.');";
                                        class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Editar">
                                        <span class="fa fa-pencil"></span>
                                    </a>
                                    <a onclick="conci_mant_metodo_eliminar('.$id.');" 
                                        class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Eliminar"> 
                                        <span class="fa fa-trash"></span>
                                    </a>';
            }
    
            $botones = '<a onclick="conci_mant_metodo_ver('.$id.');";
                            class="btn btn-info btn-sm"
                            data-toggle="tooltip" data-placement="top" title="Ver">
                            <span class="fa fa-eye"></span>
                        </a>
                       '.$botones_accion;

            $data[] = [
                "0" => count($data) + 1,
                "1" => $nombre,
                "2" => $proveedor,
                "3" => $estadoHTML,
                "4" => $usuario,
                "5" => $created_at,
                "6" => $botones
            ];
        }

        $stmt->close();

        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data,
        ];

        echo json_encode($resultado);
    } catch (Exception $e) {
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => [
                [
                    "0" => "error",
                    "1" => 'Comunicarse con Soporte, error: ' . $e->getMessage(),
                    "2" => '',
                    "3" => '',
                    "4" => '',
                    "5" => ''
                ],
            ],
        ];

        echo json_encode($resultado);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_metodo_proveedor_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_proveedor
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
            $result["result"] = "No se encontraron registros.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_metodo_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    cm.id, 
                    cm.nombre,
                    IFNULL(cm.proveedor_id,0),
                    DATE_FORMAT(cm.created_at, '%d/%m/%Y %H:%i:%s'),
                    DATE_FORMAT(cm.updated_at, '%d/%m/%Y %H:%i:%s'),
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM tbl_conci_calimaco_metodo cm
                LEFT JOIN tbl_usuarios u ON u.id=cm.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id=cm.user_updated_id
                WHERE cm.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $metodo_id, 
                                $nombre,
                                $proveedor_id,
                                $created_at,
                                $updated_at,
                                $usuario_create,
                                $usuario_update);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $metodo_id,
                        'nombre' => $nombre,
                        'proveedor_id' => $proveedor_id,
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


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_metodo_validar")
{
	$param_nombre = $_POST["nombre"];
	$id_metodo = $_POST["id_metodo"];

	// Consultar si el número de recibo ya fue registrado
	$selectQuery = "
					SELECT 
						sd.nombre,
						u.usuario
					FROM tbl_conci_calimaco_metodo sd
					LEFT JOIN tbl_usuarios u
					ON sd.user_created_id = u.id
					WHERE sd.status = '1' AND sd.id<> ? AND sd.nombre=?
					LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_metodo, $param_nombre);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($nombre, $usuario);
		$selectStmt->fetch();

		$result["http_code"] = 200;
		$result["titulo"] = 'La opción "'. $nombre .'" ya fue registrado por el usuario "'. $usuario.'"';
	}else{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	
	echo json_encode($result);
	exit();
}