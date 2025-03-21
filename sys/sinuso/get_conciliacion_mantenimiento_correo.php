<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_correo_listar")
{

    $estado_id = $_POST["estado_id"];
    $menu_id = isset($_POST["proveedor_id"]) ? $_POST["proveedor_id"] : 0;

    $where_proveedor = "";
    $where_estado = "";

    if($estado_id != ""){
        $where_estado = " AND cm.status = $estado_id ";
    }

    if($menu_id != 0){
        $where_proveedor = " AND cm.menu_id = $menu_id ";
    }

    try {

        $selectQuery = "SELECT
                            cm.id,
                            cm.nombre,
                            m.titulo,
                            DATE_FORMAT(cm.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario,
                            cm.status
                        FROM tbl_conci_correo_metodo cm
                        LEFT JOIN tbl_usuarios u ON cm.user_created_id = u.id
                        LEFT JOIN tbl_menu_sistemas m ON cm.menu_id = m.id
                        WHERE 1=1
                            $where_estado 
                            $where_proveedor 
                        ORDER BY cm.created_at ASC" ; 
        $stmt = $mysqli->prepare($selectQuery);

        //$stmt->bind_param("i", $comprobante_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $menu, $created_at, $usuario, $status);

        $data = [];

        while ($stmt->fetch()) {

            $estadoHTML = ($status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

            $botones = "";

            if($status == 1){
                $botones .= '<a onclick="conci_mant_correo_obtener('.$id.');";
                                        class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Editar">
                                        <span class="fa fa-pencil"></span>
                                    </a>
                                    <a onclick="conci_mant_correo_eliminar('.$id.');" 
                                        class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Eliminar"> 
                                        <span class="fa fa-trash"></span>
                                    </a> ';
                $botones .= '<a onclick="conci_mant_correo_btn_obtener_usuarios('.$id.');";
                                    class="btn btn-primary btn-sm"
                                    data-toggle="tooltip" data-placement="top" title="Ver usuarios">
                                    <span class="fa fa-users"></span>
                                </a>';
                                    
            }
    
            $botones .= ' <a onclick="conci_mant_correo_ver('.$id.');";
                            class="btn btn-info btn-sm"
                            data-toggle="tooltip" data-placement="top" title="Ver">
                            <span class="fa fa-eye"></span>
                        </a>
                       ';

            $data[] = [
                "0" => count($data) + 1,
                "1" => $nombre,
                "2" => $menu,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_correo_menu_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                titulo
            FROM tbl_menu_sistemas
            WHERE sec_id = 'conciliacion' AND sub_sec_id IS NULL
            -- WHERE relacion_id = 424
            ORDER BY titulo ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_correo_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    cm.id, 
                    cm.nombre,
                    cm.metodo,
                    IFNULL(cm.menu_id,0),
                    IFNULL(cm.created_at, ''),
                    IFNULL(cm.updated_at, ''),
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM tbl_conci_correo_metodo cm
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
                                $metodo,
                                $menu_id,
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
                        'metodo' => $metodo,
                        'menu_id' => $menu_id,
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


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_correo_validar")
{
	$param_metodo = $_POST["metodo"];
	$id_correo = $_POST["id_correo"];

	// Consultar si el número de recibo ya fue registrado
	$selectQuery = "
					SELECT 
						sd.nombre,
						u.usuario
					FROM tbl_conci_correo_metodo sd
					LEFT JOIN tbl_usuarios u
					ON sd.user_created_id = u.id
					WHERE sd.status = '1' AND sd.id<> ? AND sd.metodo=?
					LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_correo, $param_metodo);
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


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_correo_listar_usuarios") {
    try 
    {
        
        $query = 
        "
            SELECT u.id, 
                CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,''), ' - ',IFNULL(p.dni,'')) AS nombre  
            FROM tbl_personal_apt AS p 
                LEFT JOIN tbl_usuarios AS u ON u.personal_id = p.id
                INNER JOIN tbl_razon_social rs
                ON p.razon_social_id = rs.id
            WHERE p.estado = 1 AND u.estado = 1 
                AND p.correo IS NOT NULL
            GROUP BY u.id
            ORDER BY nombre ASC;
        ";

        $list_query = $mysqli->query($query);
        $list = [];

        while ($li = $list_query->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception($mysqli->error);
        }

        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } elseif (count($list) > 0) {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        } else {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_correo_obtener_usuarios") {
    $metodo_id = $_POST['metodo_id'];

    $query = "
        SELECT 
            uad.id,
            CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_nombre,
            uc.usuario,
            p.correo,
            p.dni,
            uad.status,
            DATE_FORMAT(STR_TO_DATE(uad.created_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s') AS fecha_creacion
        FROM tbl_conci_correo uad
        INNER JOIN tbl_usuarios uc ON uad.usuario_id = uc.id
        INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
        WHERE uad.metodo_id = ? AND uad.status = 1;
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $metodo_id);
    $stmt->execute();
    $list_query = $stmt->get_result();
    $stmt->close();

    $data = [];

    if ($mysqli->error) {
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => '', 
            "5" => '', 
            "6" => '',
        ];

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_assoc()) {
            $eliminarButton = '';

            $eliminarButton = '<a class="btn btn-rounded btn-danger btn-xs" data-toggle="tooltip" data-placement="top"
                                    onclick="conci_mant_correo_btn_eliminar_usuario(' . $reg['id'] . ')"
                                    title="Eliminar"><i class="fa fa-trash"></i></a>';

            $data[] = [
                "0" => $cont,
                "1" => $reg['usuario_nombre'],
                "2" => $reg['usuario'],
                "3" => $reg['correo'],
                "4" => $reg['dni'],
                "5" => $reg['fecha_creacion'],
                "6" => $eliminarButton
            ];

            $cont++;
        }

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
    }

    echo json_encode($resultado);
}