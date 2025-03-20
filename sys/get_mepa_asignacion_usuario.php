<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_asignacion_grupo_usuarios") {
    
    $area_id = $_POST['area_id'];
    $param_usuario = $_POST['param_usuario'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    $login_usuario_id = $login?$login['id']:null;

    $where_redes = "";

    $select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
            AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);

    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
            if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_redes = " AND rs.red_id IN ($ids_data_select_red) ";
    }

    $where_area = !empty($area_id) ? " AND uadp.area_id = '$area_id' " : "";
    $where_usuario = !empty($param_usuario) ? 
    "
        INNER JOIN mepa_usuario_asignacion_detalle ad 
        ON ua.id = ad.mepa_usuario_asignacion_id AND ad.status = 1 AND ad.usuario_id = '$param_usuario'
    " : "";
    
    $where_fecha_inicio = "";
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $where_fecha_inicio = " AND ua.created_at BETWEEN DATE_FORMAT(STR_TO_DATE('$fecha_inicio 00:00:00', '%d-%m-%Y %H:%i:%s'), '%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(STR_TO_DATE('$fecha_fin 23:59:59', '%d-%m-%Y %H:%i:%s'), '%Y-%m-%d %H:%i:%s')";
    } elseif (!empty($fecha_inicio)) {
        $where_fecha_inicio = " AND ua.created_at >= DATE_FORMAT(STR_TO_DATE('$fecha_inicio 00:00:00', '%d-%m-%Y %H:%i:%s'), '%Y-%m-%d %H:%i:%s')";
    } elseif (!empty($fecha_fin)) {
        $where_fecha_inicio = " AND ua.created_at <= DATE_FORMAT(STR_TO_DATE('$fecha_fin 23:59:59', '%d-%m-%Y %H:%i:%s'), '%Y-%m-%d %H:%i:%s')";
    }

    $query = 
    "
        SELECT
            ua.id,
            ua.titulo,
            ua.descripcion,
            CONCAT(IFNULL(uadp.nombre, ''), ' ', IFNULL(uadp.apellido_paterno, ''), ' ', IFNULL(uadp.apellido_materno, '')) AS usuario_creador,
            uadp.area_id,
            ar.nombre AS area_creador,
            (
                SELECT
                    CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, ''))
                FROM mepa_usuario_asignacion_detalle uad_sub
                INNER JOIN tbl_usuarios uc ON uad_sub.usuario_id = uc.id
                INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
                WHERE uad_sub.mepa_usuario_asignacion_id = ua.id AND uad_sub.mepa_asignacion_rol_id = 2 AND uad_sub.status=1
                LIMIT 1
            ) AS usuario_aprobador,
            DATE_FORMAT(STR_TO_DATE(ua.created_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s') AS fecha_creacion,
            CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_creacion
        FROM mepa_usuario_asignacion ua
            INNER JOIN mepa_usuario_asignacion_detalle uad ON ua.id = uad.mepa_usuario_asignacion_id
            INNER JOIN tbl_usuarios uc ON ua.user_created_id = uc.id
            INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
            INNER JOIN tbl_usuarios ucp ON uad.usuario_id = ucp.id
            INNER JOIN tbl_personal_apt uadp ON ucp.personal_id = uadp.id
            INNER JOIN tbl_razon_social rs
            ON uadp.razon_social_id = rs.id
            INNER JOIN tbl_areas ar ON uadp.area_id = ar.id
            $where_usuario
        WHERE ua.status = 1 AND uad.status=1
            $where_redes
            $where_area
            $where_fecha_inicio
        GROUP BY ua.id
        ORDER BY ua.id DESC
    ";
    $stmt = $mysqli->prepare($query);
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
            "7" => '',
            "8" => '',
            "9" => '',
            "10" => ''
        ];

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_object()) {
            $data[] = [
                "0" => $cont,
                "1" => $reg->titulo,
                "2" => $reg->area_creador,
                "3" => $reg->usuario_creador,
                "4" => $reg->usuario_aprobador,
                "5" => $reg->fecha_creacion,
                "6" => '<a class="btn btn-rounded btn-info btn-xs" data-toggle="tooltip" data-placement="top"
                            onclick="sec_mepa_obtener_grupo(' . $reg->id . ')"
                            title="Ver detalle">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a class="btn btn-rounded btn-primary btn-xs" data-toggle="tooltip" data-placement="top"
                            onclick="sec_mepa_obtener_usuarios_por_grupo_id(' . $reg->id . ')"
                            title="Editar integrantes">
                            <i class="fa fa-pencil"></i>
                        </a>
                        <a class="btn btn-rounded btn-warning btn-xs" data-toggle="tooltip" data-placement="top"
                            onclick="sec_mepa_obtener_historico_usuarios_por_grupo_id(' . $reg->id . ')"
                            title="Ver historico de integrantes">
                            <i class="fa fa-history"></i>
                        </a>'
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_asignacion_usuarios") {
    $grupo_id = $_POST['grupo_id'];

    $query = "
        SELECT 
            uad.id,
            CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_nombre,
            uc.usuario,
            p.correo,
            p.dni,
            uad.mepa_asignacion_rol_id AS rol_id,
            ar.nombre AS usuario_rol,
            uad.status,
            DATE_FORMAT(STR_TO_DATE(uad.created_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s') AS fecha_creacion
        FROM mepa_usuario_asignacion_detalle uad
        INNER JOIN mepa_usuario_asignacion ua ON uad.mepa_usuario_asignacion_id = ua.id
        INNER JOIN tbl_usuarios uc ON uad.usuario_id = uc.id
        INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
        INNER JOIN mepa_asignacion_rol ar ON uad.mepa_asignacion_rol_id = ar.id
        WHERE uad.mepa_usuario_asignacion_id = ? AND uad.status = 1;
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $grupo_id);
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
            "7" => '',
            "8" => '',
            "9" => '',
            "10" => ''
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

            if ($reg['rol_id'] != 1 && $reg['rol_id'] != 2) {
                $eliminarButton = '<a class="btn btn-rounded btn-danger btn-xs" data-toggle="tooltip" data-placement="top"
                                    onclick="sec_mepa_eliminar_usuario(' . $reg['id'] . ')"
                                    title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </a>';
            }

            $data[] = [
                "0" => $cont,
                "1" => $reg['usuario_nombre'],
                "2" => $reg['usuario'],
                "3" => $reg['correo'],
                "4" => $reg['usuario_rol'],
                "5" => $reg['dni'],
                "6" => $reg['fecha_creacion'],
                "7" => $eliminarButton
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_asignacion_grupo_detalle") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT
                    ua.id,
                    ua.titulo,
                    ua.descripcion,
                    ua.reportar_gerencia,
                    CONCAT(IFNULL(uadp.nombre, ''), ' ', IFNULL(uadp.apellido_paterno, ''), ' ', IFNULL(uadp.apellido_materno, '')) AS usuario_creador_nombre,
                    uad.usuario_id AS usuario_creador,
                    uadp.area_id,
                    ar.nombre AS area_creador,
                    (
                        SELECT
                            uad_sub.usuario_id
                        FROM mepa_usuario_asignacion_detalle uad_sub
                        INNER JOIN tbl_usuarios uc ON uad_sub.usuario_id = uc.id
                        INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
                        WHERE uad_sub.mepa_usuario_asignacion_id = ua.id AND uad_sub.mepa_asignacion_rol_id = 2 AND uad_sub.status = 1
                        LIMIT 1
                    ) AS usuario_aprobador,
                    ua.created_at AS fecha_creacion,
                    CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_creacion
                FROM mepa_usuario_asignacion ua
                INNER JOIN mepa_usuario_asignacion_detalle uad ON ua.id = uad.mepa_usuario_asignacion_id
                INNER JOIN tbl_usuarios uc ON ua.user_created_id = uc.id
                INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
                INNER JOIN tbl_usuarios ucp ON uad.usuario_id = ucp.id
                INNER JOIN tbl_personal_apt uadp ON ucp.personal_id = uadp.id
                INNER JOIN tbl_areas ar ON uadp.area_id = ar.id
                WHERE ua.status = 1 AND uad.mepa_asignacion_rol_id = 1 AND ua.id = ? AND uad.status = 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($grupo_id, $titulo, $descripcion, $reportar_gerencia, $usuario_creador_nombre, $usuario_creador_id, $area_id, $area_creador, $usuario_aprobador_id, $fecha_creacion, $usuario_creacion);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $grupo_id,
                        'usuario_creador_nombre' => $usuario_creador_nombre,
                        'titulo' => $titulo,
                        'descripcion' => $descripcion,
                        'reportar_gerencia' => $reportar_gerencia,
                        'usuario_creador_id' => $usuario_creador_id,
                        'usuario_aprobador_id' => $usuario_aprobador_id,
                        'fecha_creacion' => $fecha_creacion,
                        'usuario_creacion' => $usuario_creacion,
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


if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_obtener_usuarios_asignacion") {
    try 
    {
        $login_usuario_id = $login?$login['id']:null;

        $where_redes = "";

        $select_red =
        "
            SELECT
                l.red_id
            FROM tbl_usuarios_locales ul
                INNER JOIN tbl_locales l
                ON ul.local_id = l.id
            WHERE ul.usuario_id = '".$login_usuario_id."'
                AND ul.estado = 1 AND l.red_id IS NOT NULL
            GROUP BY l.red_id
        ";

        $data_select_red = $mysqli->query($select_red);

        $row_count_data_select_red = $data_select_red->num_rows;

        $ids_data_select_red = '';
        $contador_ids = 0;
        
        if ($row_count_data_select_red > 0) 
        {
            while ($row = $data_select_red->fetch_assoc()) 
            {
                if ($contador_ids > 0) 
                {
                    $ids_data_select_red .= ',';
                }

                $ids_data_select_red .= $row["red_id"];           
                $contador_ids++;
            }

            $where_redes = " AND rs.red_id IN ($ids_data_select_red) ";
        }
        
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
                ".$where_redes."
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


if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_obtener_areas") {
    try {
        $query = "SELECT id, nombre FROM tbl_areas ORDER BY nombre ASC;";
        
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
            $result["result"] = "El área no existe.";
        } elseif (count($list) > 0) {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $list;
        } else {
            $result["http_code"] = 400;
            $result["result"] = "El área no existe.";
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


if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_asignacion_usuarios_creador") {
    $grupo_id = $_POST['grupo_id'];

    try {
        $stmt = $mysqli->prepare("
            SELECT 
                uad.id,
                CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_nombre,
                uc.usuario,
                p.correo,
                p.dni,
                ar.nombre AS usuario_rol,
                uad.status,
                CASE
                    WHEN uad.status = 1 THEN 'Activo'
                    ELSE 'Inactivo'
                END AS estado,
                CONCAT(IFNULL(pr.nombre, ''), ' ', IFNULL(pr.apellido_paterno, ''), ' ', IFNULL(pr.apellido_materno, '')) AS usuario_registro,
                DATE_FORMAT(STR_TO_DATE(uad.created_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s') AS fecha_inicio,
                CASE
                    WHEN uad.status = 1 THEN NULL
                    ELSE
                        CONCAT(IFNULL(pd.nombre, ''), ' ', IFNULL(pd.apellido_paterno, ''), ' ', IFNULL(pd.apellido_materno, ''))
                    END 
                    AS usuario_desactivacion,
                CASE
                    WHEN uad.status = 1 THEN NULL
                    ELSE DATE_FORMAT(STR_TO_DATE(uad.updated_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s')
                    END AS fecha_fin 
            FROM mepa_usuario_asignacion_detalle uad
                INNER JOIN mepa_usuario_asignacion ua ON uad.mepa_usuario_asignacion_id = ua.id
                INNER JOIN tbl_usuarios uc ON uad.usuario_id = uc.id
                INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
                INNER JOIN mepa_asignacion_rol ar ON uad.mepa_asignacion_rol_id = ar.id
                INNER JOIN tbl_usuarios ur
                ON uad.user_created_id = ur.id
                INNER JOIN tbl_personal_apt pr 
                ON ur.personal_id = pr.id
                INNER JOIN tbl_usuarios ud
                ON uad.user_updated_id = ud.id
                INNER JOIN tbl_personal_apt pd 
                ON ud.personal_id = pd.id
            WHERE uad.mepa_usuario_asignacion_id = ?
            AND uad.mepa_asignacion_rol_id = 1
            ORDER BY uad.created_at DESC
        ");

        $stmt->bind_param("s", $grupo_id);
        $stmt->execute();
        $stmt->bind_result($id, $usuario_nombre, $usuario, $correo, $dni, $usuario_rol, $status, $estado, $usuario_registro, $fecha_inicio, $usuario_desactivacion, $fecha_fin);

        $data = [];

        while ($stmt->fetch()) {
            $estadoHTML = ($status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

            $data[] = [
                "0" => count($data) + 1,
                "1" => $usuario_nombre,
                "2" => $correo,
                "3" => $usuario_rol,
                "4" => $estadoHTML,
                "5" => $usuario_registro,
                "6" => $fecha_inicio,
                "7" => $usuario_desactivacion,
                "8" => $fecha_fin,
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
                    "5" => '',
                    "6" => '',
                    "7" => '',
                    "8" => '',
                    "9" => '',
                    "10" => '',
                ],
            ],
        ];

        echo json_encode($resultado);
    }
}


if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_asignacion_usuarios_aprobador") {
    $grupo_id = $_POST['grupo_id'];

    try {
        $stmt = $mysqli->prepare("
            SELECT 
                uad.id,
                CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_nombre,
                uc.usuario,
                p.correo,
                p.dni,
                ar.nombre AS usuario_rol,
                uad.status,
                CASE
                    WHEN uad.status = 1 THEN 'Activo'
                    ELSE 'Inactivo'
                END AS estado,
                CONCAT(IFNULL(pr.nombre, ''), ' ', IFNULL(pr.apellido_paterno, ''), ' ', IFNULL(pr.apellido_materno, '')) AS usuario_registro,
                DATE_FORMAT(STR_TO_DATE(uad.created_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s') AS fecha_inicio,
                CASE
                    WHEN uad.status = 1 THEN NULL
                    ELSE
                        CONCAT(IFNULL(pd.nombre, ''), ' ', IFNULL(pd.apellido_paterno, ''), ' ', IFNULL(pd.apellido_materno, ''))
                    END 
                    AS usuario_desactivacion,
                CASE
                    WHEN uad.status = 1 THEN NULL
                    ELSE DATE_FORMAT(STR_TO_DATE(uad.updated_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s')
                    END AS fecha_fin
            FROM mepa_usuario_asignacion_detalle uad
                INNER JOIN mepa_usuario_asignacion ua ON uad.mepa_usuario_asignacion_id = ua.id
                INNER JOIN tbl_usuarios uc ON uad.usuario_id = uc.id
                INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
                INNER JOIN mepa_asignacion_rol ar ON uad.mepa_asignacion_rol_id = ar.id
                INNER JOIN tbl_usuarios ur
                ON uad.user_created_id = ur.id
                INNER JOIN tbl_personal_apt pr 
                ON ur.personal_id = pr.id
                INNER JOIN tbl_usuarios ud
                ON uad.user_updated_id = ud.id
                INNER JOIN tbl_personal_apt pd 
                ON ud.personal_id = pd.id
            WHERE uad.mepa_usuario_asignacion_id = ?
                AND uad.mepa_asignacion_rol_id = 2
            ORDER BY uad.created_at DESC
        ");

        $stmt->bind_param("s", $grupo_id);
        $stmt->execute();
        $stmt->bind_result($id, $usuario_nombre, $usuario, $correo, $dni, $usuario_rol, $status, $estado, $usuario_registro, $fecha_inicio, $usuario_desactivacion, $fecha_fin);

        $data = [];

        while ($stmt->fetch()) {
            $estadoHTML = ($status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

            $data[] = [
                "0" => count($data) + 1,
                "1" => $usuario_nombre,
                "2" => $correo,
                "3" => $usuario_rol,
                "4" => $estadoHTML,
                "5" => $usuario_registro,
                "6" => $fecha_inicio,
                "7" => $usuario_desactivacion,
                "8" => $fecha_fin,
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
                    "5" => '',
                    "6" => '',
                    "7" => '',
                    "8" => '',
                    "9" => '',
                    "10" => '',
                ],
            ],
        ];

        echo json_encode($resultado);
    }
}


if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_asignacion_usuarios_integrante") {
    $grupo_id = $_POST['grupo_id'];

    try {
        $stmt = $mysqli->prepare("
            SELECT 
                uad.id,
                CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_nombre,
                uc.usuario,
                p.correo,
                p.dni,
                ar.nombre AS usuario_rol,
                uad.status,
                CASE
                    WHEN uad.status = 1 THEN 'Activo'
                    ELSE 'Inactivo'
                END AS estado,
                CONCAT(IFNULL(pr.nombre, ''), ' ', IFNULL(pr.apellido_paterno, ''), ' ', IFNULL(pr.apellido_materno, '')) AS usuario_registro,
                DATE_FORMAT(STR_TO_DATE(uad.created_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s') AS fecha_inicio,
                CASE
                    WHEN uad.status = 1 THEN NULL
                    ELSE
                        CONCAT(IFNULL(pd.nombre, ''), ' ', IFNULL(pd.apellido_paterno, ''), ' ', IFNULL(pd.apellido_materno, ''))
                    END 
                    AS usuario_desactivacion,
                CASE
                    WHEN uad.status = 1 THEN NULL
                    ELSE DATE_FORMAT(STR_TO_DATE(uad.updated_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s')
                    END AS fecha_fin            
            FROM mepa_usuario_asignacion_detalle uad
                INNER JOIN mepa_usuario_asignacion ua ON uad.mepa_usuario_asignacion_id = ua.id
                INNER JOIN tbl_usuarios uc ON uad.usuario_id = uc.id
                INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
                INNER JOIN mepa_asignacion_rol ar ON uad.mepa_asignacion_rol_id = ar.id
                INNER JOIN tbl_usuarios ur
                ON uad.user_created_id = ur.id
                INNER JOIN tbl_personal_apt pr 
                ON ur.personal_id = pr.id
                INNER JOIN tbl_usuarios ud
                ON uad.user_updated_id = ud.id
                INNER JOIN tbl_personal_apt pd 
                ON ud.personal_id = pd.id
            WHERE uad.mepa_usuario_asignacion_id = ?
                AND uad.mepa_asignacion_rol_id = 3
            ORDER BY uad.created_at DESC
        ");

        $stmt->bind_param("s", $grupo_id);
        $stmt->execute();
        $stmt->bind_result($id, $usuario_nombre, $usuario, $correo, $dni, $usuario_rol, $status, $estado, $usuario_registro, $fecha_inicio, $usuario_desactivacion, $fecha_fin);

        $data = [];

        while ($stmt->fetch()) {
            $estadoHTML = ($status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

            $data[] = [
                "0" => count($data) + 1,
                "1" => $usuario_nombre,
                "2" => $correo,
                "3" => $usuario_rol,
                "4" => $estadoHTML,
                "5" => $usuario_registro,
                "6" => $fecha_inicio,
                "7" => $usuario_desactivacion,
                "8" => $fecha_fin
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
                    "5" => '',
                    "6" => '',
                    "7" => '',
                    "8" => '',
                    "9" => '',
                    "10" => '',
                ],
            ],
        ];

        echo json_encode($resultado);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_consultar_usuario_en_grupo") {

    $usuario_id = mysqli_real_escape_string($mysqli, $_POST['usuario_id']);
    $grupo_id = mysqli_real_escape_string($mysqli, $_POST['grupo_id']);

    try {
        $stmt = $mysqli->prepare("SELECT COUNT(*) as count, ua.titulo 
                                  FROM mepa_usuario_asignacion_detalle uad
                                  INNER JOIN mepa_usuario_asignacion ua ON ua.id = uad.mepa_usuario_asignacion_id
                                  WHERE uad.usuario_id = ? AND uad.mepa_usuario_asignacion_id != ? AND uad.status=1 AND ua.status=1");

        $stmt->bind_param("ii", $usuario_id, $grupo_id);
        $stmt->execute();
        $stmt->bind_result($conteo, $grupo_titulo);
        $stmt->fetch();
        $stmt->close();

        if ($conteo > 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario ya está registrado en el grupo: " . $grupo_titulo;
        } elseif ($conteo == 0) {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $conteo;
        } else {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        }

    } catch (Exception $e) {
        $result["consulta_error"] = $e->getMessage();
    }

    echo json_encode($result);
    exit();
}
