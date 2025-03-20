<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS

if (isset($_POST["accion"]) && $_POST["accion"] === "kasnet_mant_operacion_listar")
{

    $estado_id = $_POST["estado_id"];
    $tipo_id = isset($_POST["tipo_id"]) ? $_POST["tipo_id"] : 0;

    $where_tipo = "";
    $where_estado = "";

    if($estado_id != ""){
        $where_estado = " AND o.status = $estado_id ";
    }

    if($tipo_id != 0){
        $where_tipo = " AND o.tipo_id = $tipo_id ";
    }

    try {

        $selectQuery = "SELECT
                            o.id,
                            o.nombre,
                            IFNULL(t.nombre,''),
                            DATE_FORMAT(o.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario,
                            o.status
                        FROM tbl_kasnet_operacion o
                        LEFT JOIN tbl_usuarios u ON o.user_created_id = u.id
                        LEFT JOIN tbl_kasnet_operacion_tipo t ON t.id=o.tipo_id
                        WHERE 1=1
                            $where_estado 
                            $where_tipo 
                        ORDER BY o.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);

        //$stmt->bind_param("i", $comprobante_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $tipo, $created_at, $usuario, $status);

        $data = [];

        while ($stmt->fetch()) {
            $estadoHTML = "";

            switch ($status) {
                case 1: $estadoHTML = '<button type=button" class="btn btn-rounded btn-success btn-xs" onclick="kasnet_mant_operacion_btn_cambiar_estado('.$id.',0)">
                        Activo </button>'; 
                    break;
                case 0: $estadoHTML = '<button type=button" class="btn btn-rounded btn-danger btn-xs" onclick="kasnet_mant_operacion_btn_cambiar_estado('.$id.',1)">
                        Inactivo</button>'; 
                    break;
            }


            $botones_accion = "";

            if($status == 1){
                $botones_accion = '<a onclick="kasnet_mant_operacion_obtener('.$id.');";
                                        class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Editar">
                                        <span class="fa fa-pencil"></span>
                                    </a>';
            }
    
            $botones = '<a onclick="kasnet_mant_operacion_btn_ver('.$id.');";
                            class="btn btn-info btn-sm"
                            data-toggle="tooltip" data-placement="top" title="Ver">
                            <span class="fa fa-eye"></span></a>
                         '.$botones_accion.
                         ' <a onclick="kasnet_mant_operacion_btn_historico_cambios('.$id.');";
                            class="btn btn-primary btn-sm"
                            data-toggle="tooltip" data-placement="top" title="Historial de cambios">
                            <span class="fa fa-history"></span>
                        </a>
                       ';

            $data[] = [
                "0" => count($data) + 1,
                "1" => $nombre,
                "2" => $tipo,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "kasnet_mant_operacion_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    o.id, 
                    o.nombre,
                    IFNULL(o.tipo_id,0),
                    DATE_FORMAT(o.created_at, '%d/%m/%Y %H:%i:%s'),
                    DATE_FORMAT(o.updated_at, '%d/%m/%Y %H:%i:%s'),
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM tbl_kasnet_operacion o
                LEFT JOIN tbl_usuarios u ON u.id=o.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id=o.user_updated_id
                LEFT JOIN tbl_kasnet_operacion_tipo t ON t.id=o.tipo_id
                WHERE o.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $metodo_id, 
                                $nombre,
                                $tipo_id,
                                $created_at,
                                $updated_at,
                                $usuario_create,
                                $usuario_update);

            if ($stmt->fetch()) {
                $stmt->close();

                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $metodo_id,
                        'nombre' => $nombre,
                        'tipo_id' => $tipo_id,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "kasnet_mant_operacion_validar")
{
	$param_nombre = $_POST["nombre"];
	$id_operacion = $_POST["id_operacion"];

	// Consultar si el número de recibo ya fue registrado
	$selectQuery = "
					SELECT 
						sd.nombre,
						u.usuario
					FROM tbl_kasnet_operacion sd
					LEFT JOIN tbl_usuarios u
					ON sd.user_created_id = u.id
					WHERE sd.status = '1' AND sd.id<> ? AND sd.nombre=?
					LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_operacion, $param_nombre);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($nombre, $usuario);
		$selectStmt->fetch();

		$result["http_code"] = 200;
		$result["titulo"] = 'La operación "'. $nombre .'" ya fue registrado por el usuario "'. $usuario.'"';
	}else{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "kasnet_mant_operacion_tipo_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_kasnet_operacion_tipo
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


if (isset($_POST["accion"]) && $_POST["accion"] === "kasnet_mant_operacion_historico") {
    $operacion_id = $_POST['operacion_id'];

    try {

        $selectQuery = "SELECT 
                            ch.id,
                            ch.valor_anterior,
                            ch.valor_nuevo,
                            ifnull(cc.nombre_campo,''),
                            DATE_FORMAT(ch.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
                            u.usuario
                        FROM tbl_kasnet_operacion_historial_cambio ch
                        LEFT JOIN tbl_usuarios u ON ch.user_created_id = u.id
                        LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
                        LEFT JOIN tbl_kasnet_operacion_historial_campo cc ON cc.campo = ch.nombre_campo
                        WHERE ch.status =1 AND ch.operacion_id = ?
                        ORDER BY ch.created_at DESC" ; 
        //echo $selectQuery;
        $stmt = $mysqli->prepare($selectQuery);

        $stmt->bind_param("i", $operacion_id);
        $stmt->execute();
        $stmt->bind_result($id, $valor_anterior, $valor_nuevo, $nombre_campo, $fecha_registro, $usuario);

        $data = [];

        while ($stmt->fetch()) {

            $data[] = [
                "0" => count($data) + 1,
                "1" => $valor_anterior,
                "2" => $valor_nuevo,
                "3" => $nombre_campo,
                "4" => $fecha_registro,
                "5" => $usuario
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