<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

//  Periodo

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_historial_periodo") {
    $proveedor_id = $_POST["proveedor_id"];

    $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'conciliacion' AND sub_sec_id = 'venta' LIMIT 1")->fetch_assoc();
    $menu_permiso = $menu_id_consultar["id"];


    $where_proveedor = "";
    if($proveedor_id != ""){
        $where_proveedor .= " AND pi.proveedor_id = '".$proveedor_id."' ";
    }
    try {

        $mysqli->query("SET lc_time_names = 'es_ES'");

        $selectQuery = " SELECT 
                            pi.id,
                            pi.proveedor_id,
                            p.nombre,
                            pi.periodo,
                            DATE_FORMAT(pi.periodo, '%M %Y'),
                            pi.conciliacion_completada,
                            ifnull(pi.calimaco_count,0),
                            ifnull(pi.proveedor_count,0),
                            ifnull(pi.reconciled_count,0),
                            ifnull(pi.duplicate_count,0),
                            ifnull(SUM(pim.reconciled_count),0),
                            ifnull(SUM(pim.non_reconciled_count),0),
                            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_periodo pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        LEFT JOIN tbl_conci_proveedor p ON p.id = pi.proveedor_id
                        LEFT JOIN tbl_conci_proveedor_importacion pim ON pim.periodo_id = pi.id
                        WHERE pi.status =1
                        $where_proveedor 
                        GROUP BY pi.id
                        ORDER BY pi.created_at DESC" ; 
    
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $proveedor_id, $proveedor, $periodo, $periodo_formato, $conciliacion_completada, $calimaco_count, $proveedor_count, $reconciled_count, $duplicate_count, $imp_reconciled_count, $imp_non_reconciled_count, $created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";

            
            if($conciliacion_completada ==0){

                $botones .= '<a class="btn btn-rounded btn-info btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_historial_btn_ver('. $id . ','. $proveedor_id.')"
                            title="Ver"><i class="fa fa-eye"></i></a>';

                $botones .= ' <a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_historial_btn_editar('. $id . ','. $proveedor_id.')"
                            title="Editar"><i class="fa fa-pencil"></i></a>';

                $botones .= ' <a class="btn btn-rounded btn-success btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_historial_btn_comision('. $id . ')"
                            title="Conteo de comisión"><i class="fa fa-calculator"></i></a>';

            if(in_array("btn_conci_periodo_eliminar", $usuario_permisos[$menu_permiso])):

                $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_btn_eliminar('. $id . ')"
                            title="Eliminar"><i class="fa fa-trash"></i></a>';
            endif;
            }
            
            if($imp_reconciled_count != 0  || $imp_non_reconciled_count != 0){
                $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_historial_btn_no_conciliado('. $id . ')"
                            title="No conciliados"><i class="fa fa-minus-circle"></i></a>';
            }
                

            $data[] = [
                "0" => count($data) + 1,
                "1" => $proveedor,
                "2" => $periodo_formato,
                "3" => $calimaco_count,
                "4" => $proveedor_count,
                "5" => $reconciled_count,
                "6" => $duplicate_count,
                "7" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $mysqli->query("SET lc_time_names = 'es_ES'");

            $stmt = $mysqli->prepare("
                SELECT 
                    fo.id, 
                    p.nombre,
                    i.metodo,
                    fo.periodo,
                    DATE_FORMAT(fo.periodo, '%M %Y'),
                    IFNULL(fo.created_at, ''),
                    IFNULL(fo.updated_at, ''),
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM tbl_conci_periodo fo
                LEFT JOIN tbl_conci_proveedor p ON p.id=fo.proveedor_id
                LEFT JOIN tbl_conci_importacion_tipo i ON i.id=p.tipo_importacion_id
                LEFT JOIN tbl_usuarios u ON u.id=fo.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id=fo.user_updated_id
                WHERE fo.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $periodo_id, 
                                $proveedor, 
                                $metodo_importacion,
                                $periodo,
                                $periodo_formato,
                                $created_at,
                                $updated_at,
                                $usuario_create,
                                $usuario_update);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $periodo_id,
                        'proveedor' => $proveedor,
                        'metodo_importacion' => $metodo_importacion,
                        'periodo' => $periodo,
                        'periodo_formato' => $periodo_formato,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_importacion_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $mysqli->query("SET lc_time_names = 'es_ES'");

            $stmt = $mysqli->prepare("
                SELECT 
                    fo.id, 
                    p.nombre,
                    IFNULL(fo.fecha_inicio,''),
                    IFNULL(fo.fecha_fin,''),
                    IFNULL(fo.created_at, ''),
                    IFNULL(fo.updated_at, ''),
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM tbl_conci_proveedor_importacion fo
                LEFT JOIN tbl_conci_proveedor p ON p.id=fo.proveedor_id
                LEFT JOIN tbl_usuarios u ON u.id=fo.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id=fo.user_updated_id
                WHERE fo.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $periodo_id, 
                                $proveedor, 
                                $fecha_inicio,
                                $fecha_fin,
                                $created_at,
                                $updated_at,
                                $usuario_create,
                                $usuario_update);

            if ($stmt->fetch()) {

                if($fecha_inicio != '') $fecha_inicio = date("d-m-Y", strtotime($fecha_inicio));  
                if($fecha_fin != '') $fecha_fin = date("d-m-Y", strtotime($fecha_fin)); 

                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $periodo_id,
                        'proveedor' => $proveedor,
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin' => $fecha_fin,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_importacion_calimaco_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $mysqli->query("SET lc_time_names = 'es_ES'");

            $stmt = $mysqli->prepare("
                SELECT 
                    fo.id, 
                    IFNULL(fo.fecha_inicio,''),
                    IFNULL(fo.fecha_fin,''),
                    IFNULL(fo.created_at, ''),
                    IFNULL(fo.updated_at, ''),
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM tbl_conci_calimaco_importacion fo
                LEFT JOIN tbl_usuarios u ON u.id=fo.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id=fo.user_updated_id
                WHERE fo.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $periodo_id, 
                                $fecha_inicio,
                                $fecha_fin,
                                $created_at,
                                $updated_at,
                                $usuario_create,
                                $usuario_update);

            if ($stmt->fetch()) {

                if($fecha_inicio != '') $fecha_inicio = date("d-m-Y", strtotime($fecha_inicio));  
                if($fecha_fin != '') $fecha_fin = date("d-m-Y", strtotime($fecha_fin)); 

                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $periodo_id,
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin' => $fecha_fin,
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


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_historial_importacion_proveedor") {
    $proveedor_id = $_POST["proveedor_id"];
    $periodo_id = $_POST["periodo_id"];

    $where_proveedor = "";
    $where_periodo = "";

    if($proveedor_id != ""){
        $where_proveedor .= " AND pi.proveedor_id = '".$proveedor_id."' ";
    }

    if($periodo_id != ""){
        $where_periodo .= " AND pi.periodo_id = '".$periodo_id."' ";
    }
    try {

        $selectQuery = " SELECT 
                            pi.id,
                            pi.nombre_archivo,
                            pi.fecha_inicio,
                            pi.fecha_fin,
                            pi.proveedor_id,
                            pi.periodo_id,
                            ifnull(pi.created_count,0),
                            ifnull(pi.updated_count,0),
                            ifnull(pi.reconciled_count,0),
                            ifnull(pi.duplicate_count,0),
                            ifnull(pi.non_reconciled_count,0),
                            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_proveedor_importacion pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        WHERE pi.status =1 
                        AND (pi.tipo_archivo_id = 1 OR pi.tipo_archivo_id = 3) -- Tipo de archivo de ventas y combinado
                        $where_proveedor
                        $where_periodo
                        ORDER BY pi.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre_archivo, $fecha_inicio, $fecha_fin, $proveedor_id,$periodo_id,$created_count, $updated_count, $reconciled_count, $duplicate_count,$non_reconciled_count,$created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";
            $botones .= '<a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="sec_conci_venta_importar_proveedor_btn_editar('. $id.')"
                            title="Editar">
                            <i class="fa fa-pencil"></i>
                        </a>';

            $botones .= ' <a class="btn btn-rounded btn-success btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_importar_archivo_proveedor_btn_conciliar('. $id.','.$periodo_id.')"
                            title="Conciliar">
                            <i class="fa fa-refresh"></i>
                        </a>';
                        
            $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_importacion_btn_eliminar('. $id.')"
                            title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </a>';
                        

            //if($non_reconciled_count != 0){
                
            //}
            $data[] = [
                "0" => count($data) + 1,
                "1" => $fecha_inicio,
                "2" => $fecha_fin,
                "3" => $created_count,
                "4" => $updated_count,
                "5" => $reconciled_count,
                "6" => $duplicate_count,
                "7" => $usuario,
                "8" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_historial_importacion_noconciliado_proveedor") {
    $periodo_id = $_POST["periodo_id"];

    $where_importacion= "";

    if($periodo_id != ""){
        $where_importacion .= " AND ct.periodo_id = '".$periodo_id."' ";
    }

    try {

        $selectQuery = " SELECT
                            ct.id,
                            ct.transaccion_id,
                            CONCAT(pi.fecha_inicio, ' - ',pi.fecha_fin),
                            pe.nombre AS estado_proveedor,
                            ct.monto,
                            CASE ct.estado_conciliacion
                                WHEN 1 THEN 'SI'
                                WHEN 0 THEN 'NO'
                                WHEN 2 THEN 'DUPLICADO'
                                ELSE 'Desconocido'
                            END AS estado_conciliacion,
                            ct.observacion
                        FROM tbl_conci_proveedor_transaccion ct
                            LEFT JOIN tbl_conci_proveedor_importacion pi
                            ON ct.importacion_id = pi.id
                            LEFT JOIN tbl_conci_proveedor cp
                            ON pi.proveedor_id = cp.id
                            LEFT JOIN tbl_conci_proveedor_estado pe
                            ON ct.estado_id = pe.id
                        WHERE ct.status = 1 AND ct.estado_conciliacion = 0
                        $where_importacion
                        ORDER BY ct.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $transaccion_id, $archivo, $estado_proveedor, $monto, $estado_conciliacion,$observacion);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";
            $botones .= '<a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_btn_registrar_observacion_proveedor('. $id.')"
                            title="Observar">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>';

            $data[] = [
                "0" => count($data) + 1,
                "1" => $transaccion_id,
                "2" => $archivo,
                "3" => $estado_proveedor,
                "4" => $monto,
                "5" => $estado_conciliacion,
                "6" => $observacion,
                "7" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_historial_importacion_noconciliado_calimaco") {
    $periodo_id = $_POST["periodo_id"];

    $where_importacion= "";

    if($periodo_id != ""){
        $where_importacion .= " AND ct.periodo_id = '".$periodo_id."' ";
    }

    try {

        $selectQuery = "SELECT
                            ct.id,
                            ct.transaccion_id,
                            CONCAT(pi.fecha_inicio, ' - ',pi.fecha_fin),
                            ce.nombre AS estado_calimaco,
                            ct.cantidad,
                            CASE ct.estado_conciliacion
                                WHEN 1 THEN 'SI'
                                WHEN 0 THEN 'NO'
                                WHEN 2 THEN 'DUPLICADO'
                                ELSE 'Desconocido'
                            END AS estado_conciliacion,
                            ct.observacion
                        FROM tbl_conci_calimaco_transaccion ct
                            LEFT JOIN tbl_conci_calimaco_importacion pi
                            ON ct.importacion_id = pi.id
                            LEFT JOIN tbl_conci_calimaco_estado ce
                            ON ct.estado_id = ce.id 
                        WHERE ct.status = 1 AND ct.estado_conciliacion = 0
                        $where_importacion
                        ORDER BY ct.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $transaccion_id, $archivo,$estado_calimaco, $monto, $estado_conciliacion,$observacion);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";
            $botones .= '<a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_btn_registrar_observacion_calimaco('. $id.')"
                            title="Observar">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>';

            $data[] = [
                "0" => count($data) + 1,
                "1" => $transaccion_id,
                "2" => $archivo,
                "3" => $estado_calimaco,
                "4" => $monto,
                "5" => $estado_conciliacion,
                "6" => $observacion,
                "7" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_historial_importacion_calimaco") {
    try {

        $periodo_id = $_POST["periodo_id"];

        $selectQuery = " SELECT 
                            ci.id,
                            ci.nombre_archivo,
                            ci.fecha_inicio,
                            ci.fecha_fin,
                            ifnull(ci.created_count,0),
                            ifnull(ci.updated_count,0),
                            DATE_FORMAT(ci.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_calimaco_importacion ci
                        LEFT JOIN tbl_usuarios u ON ci.user_created_id = u.id
                        WHERE ci.status =1 
                        AND (ci.tipo_archivo_id = 4)
                        AND ci.periodo_id = ?
                        ORDER BY ci.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        $stmt->bind_param("i", $periodo_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre_archivo, $fecha_inicio, $fecha_fin, $created_count, $updated_count, $created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";

            $botones .= '<a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="sec_conci_venta_importar_calimaco_btn_editar('. $id.')"
                            title="Editar">
                            <i class="fa fa-pencil"></i>
                        </a>';
            $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_importacion_calimaco_btn_eliminar('. $id.')"
                            title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </a>';
            $data[] = [
                "0" => count($data) + 1,
                "1" => $nombre_archivo,
                "2" => $fecha_inicio,
                "3" => $fecha_fin,
                "4" => $created_count,
                "5" => $updated_count,
                "6" => $created_at,
                "7" => $usuario,
                "8" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_listar"){
    $usuario_id = $login ? $login['id'] : null;

	$proveedor_estado_id = $_POST['proveedor_estado_id'];
    $calimaco_estado_id = $_POST['calimaco_estado_id'];
	$estado_conciliacion = $_POST['estado_conciliacion'];
    $proveedor_id = $_POST['proveedor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];

    //  FILTROS

        $where_proveedor="";
        $where_estado_proveedor="";
        $where_estado_conciliacion="";
        $where_estado_calimaco="";
        
        if ($estado_conciliacion != ""){
            $where_estado_conciliacion = " AND ct.estado_conciliacion = ".$estado_conciliacion." ";
        }

        if ($calimaco_estado_id != ""){
            $where_estado_calimaco = " AND ct.estado_id = ".$calimaco_estado_id." ";
        }

        if ($proveedor_id != ""){
            $where_proveedor = " AND cp.id = ".$proveedor_id." ";

            if ($proveedor_estado_id != 0){
                $where_estado_proveedor = " AND pe.id = ".$proveedor_estado_id." ";
            }
        }
     
        $where_fecha ="";

        if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
            $where_fecha = " AND ct.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            } elseif (!Empty($fecha_inicio)) {
                $where_fecha = " AND ct.fecha >= '$fecha_inicio 00:00:00'";
            } elseif (!Empty($fecha_fin)) {
                $where_fecha = " AND ct.fecha <= '$fecha_fin 23:59:59'";
        }

    // PERMISOS DE ETAPAS

        $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'conciliacion' AND sub_sec_id = 'venta' LIMIT 1")->fetch_assoc();
        $menu_permiso = $menu_id_consultar["id"];

   
	$query = "
        SELECT
            ct.id,
            ct.periodo_id,
            cp.nombre AS nombre_proveedor,
            IFNULL(pt.id,'') AS venta_proveedor,
            IFNULL(pe.nombre,'') AS estado_proveedor,
            ct.transaccion_id,
            DATE_FORMAT(ct.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
            ce.nombre AS estado_calimaco,
            ct.cantidad,
            CASE ct.estado_conciliacion
                WHEN 1 THEN 'SI'
                WHEN 0 THEN 'NO'
                WHEN 2 THEN 'DUPLICADO'
                ELSE 'Desconocido'
            END AS estado_conciliacion,
            ct.status
        FROM tbl_conci_calimaco_transaccion ct
            LEFT JOIN tbl_conci_calimaco_estado ce
            ON ct.estado_id = ce.id
            LEFT JOIN tbl_conci_calimaco_metodo cm
            ON ct.metodo_id = cm.id
            LEFT JOIN tbl_conci_proveedor cp
            ON cm.proveedor_id = cp.id
            LEFT JOIN tbl_conci_periodo p
            ON ct.periodo_id = p.id
            LEFT JOIN tbl_conci_proveedor_transaccion pt
				ON pt.id = (
					SELECT CAST(SUBSTRING_INDEX(ct.venta_proveedor_id, ',', 1) AS UNSIGNED)
				)
			LEFT JOIN tbl_conci_proveedor_estado pe
            ON pt.estado_id = pe.id
        WHERE ct.status = 1 AND p.status = 1
        $where_proveedor
        $where_estado_conciliacion
        $where_estado_proveedor
        $where_estado_calimaco
        $where_fecha
        ORDER BY ct.cantidad ASC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{      
        $botones = "";

        $botones .= '<a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                    onclick="sec_conci_venta_anular(' . $reg->id . ')"
                    title="Anular">
                    <i class="fa fa-ban"></i>
                    </a> ';

        $botones .= '<a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                    onclick="sec_conci_venta_devolucion(' . $reg->id . ',' . $reg->cantidad . ','.$reg->periodo_id.')"
                    title="Devolución">
                    <i class="fa fa-undo"></i>
                    </a> ';

        //if(in_array("btn_comp_anular", $usuario_permisos[$menu_permiso]) && $reg->status != 0):

        if($reg->venta_proveedor != ""){
            $botones .= '<a class="btn btn-rounded btn-info btn-sm" data-toggle="tooltip" data-placement="top"
                onclick="sec_conci_venta_detalle_conciliacion_proveedor(' . $reg->id . ',' . $reg->periodo_id . ')"
                title="Ver Registro Conciliado">
                <i class="fa fa-info-circle"></i>
            </a>';

        }
           
        //endif;
        $color_resaltar = '';
        if ($reg->estado_conciliacion == 'NO') {
            $color_resaltar = 'red';
        } elseif ($reg->estado_conciliacion == 'DUPLICADO') {
            $color_resaltar = 'yellow';
        }
        //$stmt->close();
        
		$data[] = array(
            "0" => count($data) + 1,
			"1" => $reg->fecha,
            "2" => $reg->nombre_proveedor,
            "3" => $reg->estado_proveedor,
			"4" => $reg->transaccion_id,
            "5" => $reg->estado_calimaco,
			"6" => $reg->cantidad,
			"7" => $reg->estado_conciliacion,
			"8" => $botones,
            "9" => $color_resaltar
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_exportar"){
    require_once '../phpexcel/classes/PHPExcel.php';
    
    $usuario_id = $login ? $login['id'] : null;

    $formato = $_POST['formato'];
	$proveedor_estado_id = $_POST['proveedor_estado_id'];
    $calimaco_estado_id = $_POST['calimaco_estado_id'];
	$estado_conciliacion = $_POST['estado_conciliacion'];
    $proveedor_id = $_POST['proveedor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];

    //  FILTROS

        $where_proveedor="";
        $where_estado_proveedor="";
        $where_estado_conciliacion="";
        $where_estado_calimaco="";
        
        if ($estado_conciliacion != ""){
            $where_estado_conciliacion = " AND ct.estado_conciliacion = ".$estado_conciliacion." ";
        }

        if ($calimaco_estado_id != ""){
            $where_estado_calimaco = " AND ct.estado_id = ".$calimaco_estado_id." ";
        }

        if ($proveedor_id != ""){
            $where_proveedor = " AND cm.proveedor_id = ".$proveedor_id." ";

            if ($proveedor_estado_id != 0){
                $where_estado_proveedor = " AND pe.id = ".$proveedor_estado_id." ";
            }
        }
     
        $where_fecha ="";

        if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
            $where_fecha = " AND ct.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            } elseif (!Empty($fecha_inicio)) {
                $where_fecha = " AND ct.fecha >= '$fecha_inicio 00:00:00'";
            } elseif (!Empty($fecha_fin)) {
                $where_fecha = " AND ct.fecha <= '$fecha_fin 23:59:59'";
        }

   
	$query = "
        SELECT
            ct.id,
            cp.nombre AS nombre_proveedor,
            IFNULL(pt.id,'') AS venta_proveedor,
            IFNULL(pe.nombre,'') AS estado_proveedor,
            ct.transaccion_id,
            DATE_FORMAT(ct.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
            ce.nombre AS estado_calimaco,
            ct.cantidad,
            CASE ct.estado_conciliacion
                WHEN 1 THEN 'SI'
                WHEN 0 THEN 'NO'
                WHEN 2 THEN 'DUPLICADO'
                ELSE 'Desconocido'
            END AS estado_conciliacion,
            ct.observacion,
            ct.status
        FROM tbl_conci_calimaco_transaccion ct
            LEFT JOIN tbl_conci_calimaco_estado ce
            ON ct.estado_id = ce.id
            LEFT JOIN tbl_conci_calimaco_metodo cm
            ON ct.metodo_id = cm.id
            LEFT JOIN tbl_conci_proveedor cp
            ON cm.proveedor_id = cp.id
            LEFT JOIN tbl_conci_proveedor_transaccion pt
				ON pt.id = (
					SELECT CAST(SUBSTRING_INDEX(ct.venta_proveedor_id, ',', 1) AS UNSIGNED)
				)
			LEFT JOIN tbl_conci_proveedor_estado pe
            ON pt.estado_id = pe.id
        WHERE ct.status = 1 
        $where_proveedor
        $where_estado_conciliacion
        $where_estado_proveedor
        $where_estado_calimaco
        $where_fecha
        ORDER BY ct.id DESC
	";

	$list_query = $mysqli->query($query);
    $data =  array();


    //  Verificar si la carpeta existe

        $path = "/var/www/html/files_bucket/conciliacion/reportes/";

        if (!is_dir($path)) 
        {

            $data_return = array(
                "error" => 'No existe la carpeta "reportes" en la ruta "/files_bucket/conciliacion/" del servidor. Comunicarse con soporte',
                "titulo" => "Error al exportar",
                "http_code" => 400
            );
            echo json_encode($data_return);
            exit;
        }

    //  Limpia todos los archivos en la carpeta

        $files = glob($path . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    //  Creación de titulos de columnas de archivo

        switch ($formato){
            case "excel":
        
                $objPHPExcel = new PHPExcel();

                // Se asignan las propiedades del libro

                    $objPHPExcel->getProperties()->setCreator("AT") 
                                ->setDescription("Reporte de Conciliación");
            
                    $titulosColumnas = array('Nº', 
                                            'Fecha y hora', 
                                            'Proveedor',
                                            'Estado Proveedor', 
                                            'ID', 
                                            'Estado Calimaco', 
                                            'Monto', 
                                            'Conciliacion',
                                            'Observación'
                                        );
            
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', $titulosColumnas[0])
                        ->setCellValue('B1', $titulosColumnas[1])
                        ->setCellValue('C1', $titulosColumnas[2])
                        ->setCellValue('D1', $titulosColumnas[3])
                        ->setCellValue('E1', $titulosColumnas[4])
                        ->setCellValue('F1', $titulosColumnas[5])
                        ->setCellValue('G1', $titulosColumnas[6])
                        ->setCellValue('H1', $titulosColumnas[7])
                        ->setCellValue('I1', $titulosColumnas[8]);
            
                        $cont = 0;
            
                    $i = 2; 
            
            
                    while($reg = $list_query->fetch_object()) 
                    {      
            
                        $cont ++;
            
                        $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $cont)
                        ->setCellValue('B'.$i, $reg->fecha)
                        ->setCellValue('C'.$i, $reg->nombre_proveedor)
                        ->setCellValue('D'.$i, $reg->estado_proveedor)
                        ->setCellValue('E'.$i, $reg->transaccion_id)
                        ->setCellValue('F'.$i, $reg->estado_calimaco)
                        ->setCellValue('G'.$i, $reg->cantidad)
                        ->setCellValue('H'.$i, $reg->estado_conciliacion)
                        ->setCellValue('I'.$i, $reg->observacion);
                        
                        $i++;
                    }
        
                //  Estilización de excel
        
                    $estiloNombresColumnas = array(
                        'font' => array(
                            'name'      => 'Calibri',
                            'bold'      => true,
                            'italic'    => false,
                            'strike'    => false,
                            'size'      => 10,
                            'color'     => array(
                                'rgb' => 'FFFFFF' // Color blanco
                            )
                        ),
                        'fill' => array(
                            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array(
                                'rgb' => '00008B' // Color azul oscuro
                            )
                        ),
                        'alignment' => array(
                            'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'wrap'      => false
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                
                
                    $estiloInformacion = new PHPExcel_Style();
                    $estiloInformacion->applyFromArray( array(
                        'font' => array(
                            'name'  => 'Arial',
                            'color' => array(
                                'rgb' => '000000'
                            )
                        )
                    ));
            
                    $estilo_centrar = array(
                        'alignment' =>  array(
                            'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'wrap'      => false
                        )
                    );
            
                    $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(63);
            
                    $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($estiloNombresColumnas);
                    $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:U".($i-1));
                    $objPHPExcel->getActiveSheet()->getStyle('A1:I'.($i-1))->applyFromArray($estilo_centrar);
            
                    $objPHPExcel->getActiveSheet()->getStyle('G2:G'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
            
                    for($i = 'A'; $i <= 'I'; $i++)
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
                    }
            
                    $objPHPExcel->getActiveSheet()->setTitle('Conciliación');
                    
                    $objPHPExcel->setActiveSheetIndex(0);
            
                //  Descargar excel
            
                    $file_name = "Reporte de Conciliacion ".date("Ymd");
                    ini_set('display_errors', 0);
                    ini_set('display_startup_errors', 0);
                    error_reporting(0);
                    
                    // Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
                    header('Cache-Control: max-age=0');
            
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    $excel_path = '/var/www/html/files_bucket/conciliacion/reportes/'.$file_name.'.xls';
                    $excel_path_download = '/files_bucket/conciliacion/reportes/'.$file_name.'.xls';
                    try 
                    {
                        $objWriter->save($excel_path);
                    } 
                    catch (Exception $e)
                    {
                        $data_return = array(
                            "error" => $e,
                            "titulo" => "Error al guardar el excel",
                            "http_code" => 400
                        );
                        echo json_encode($data_return);
                        exit;
                    }
            
                    $data_return = array(
                        "ruta_archivo" => $excel_path_download,
                        "http_code" => 200
                    );
                    echo json_encode($data_return);
                    exit;
                break;
            case "csv":

                //  Definir titulo de columnas
                    $titulosColumnas = array(
                        'Nº', 
                        'Fecha y hora', 
                        'Proveedor',
                        'Estado Proveedor', 
                        'ID', 
                        'Estado Calimaco', 
                        'Monto', 
                        'Conciliacion',
                        'Observación'
                    );
                
                // Generar el contenido CSV
                    $csv_content = implode(',', $titulosColumnas) . "\n";
                    
                    $cont = 0;
                    
                    $i = 2;
                    
                    while($reg = $list_query->fetch_object()) {      
                        $cont++;
                    
                        // Generar fila CSV
                        $csv_content .= "$cont,";
                        $csv_content .= "{$reg->fecha},";
                        $csv_content .= "{$reg->nombre_proveedor},";
                        $csv_content .= "{$reg->estado_proveedor},";
                        $csv_content .= "{$reg->transaccion_id},";
                        $csv_content .= "{$reg->estado_calimaco},";
                        $csv_content .= "{$reg->cantidad},";
                        $csv_content .= "{$reg->estado_conciliacion},";
                        $csv_content .= "{$reg->observacion}\n";
                    
                        $i++;
                    }
                    
                // Nombre del archivo y ruta de descarga
                $file_name = "Reporte Conciliacion " . date("Ymd") . ".csv";
                $csv_path = '/var/www/html/files_bucket/conciliacion/reportes/' . $file_name;
                
                // Guardar el archivo CSV
                    try {
                        file_put_contents($csv_path, $csv_content);
                    
                        $data_return = array(
                            "ruta_archivo" => '/files_bucket/conciliacion/reportes/' . $file_name,
                            "http_code" => 200
                        );
                        echo json_encode($data_return);
                        exit;
                    } catch (Exception $e) {
                        $data_return = array(
                            "error" => $e->getMessage(),
                            "titulo" => "Error al guardar el CSV",
                            "http_code" => 400
                        );
                        echo json_encode($data_return);
                        exit;
                    }
                break;

            default:
                $data_return = array(
                    "error" => $e,
                    "titulo" => "No es posible descargarlo en ese formato. Comunicarse con soporte",
                    "http_code" => 400
                );
                echo json_encode($data_return);
                exit;
        }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_proveedor_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                p.id, p.nombre
            FROM tbl_conci_proveedor p
            WHERE p.status = 1 AND p.tipo_importacion_id IS NOT NULL
            ORDER BY p.nombre ASC;
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
            $result["result"] = "El comprobante no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_proveedor_estado_listar") {
    try {
		$proveedor_id = $_POST["proveedor_id"];

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_conci_proveedor_estado
            WHERE status = 1 AND proveedor_id = ?
            ORDER BY nombre ASC;
        ");
        $stmt->bind_param("i", $proveedor_id);
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
            $result["result"] = "El comprobante no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_calimaco_estado_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_conci_calimaco_estado
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
            $result["result"] = "El comprobante no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_proveedor_obtener_metodo") 
{
	try{
		$proveedor_id = $_POST["proveedor_id"];
		$selectQuery = "SELECT 
							tp.metodo
						FROM tbl_conci_proveedor p
                        LEFT JOIN tbl_conci_importacion_tipo tp ON p.tipo_importacion_id=tp.id
						WHERE tp.status = '1' AND p.status = '1' AND p.id = ? 
						LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $proveedor_id);
		$selectStmt->execute();
		$selectStmt->store_result();

		if ($selectStmt->num_rows > 0) {
			$selectStmt->bind_result($metodo);
			$selectStmt->fetch();

			$result["http_code"] = 200;
			$result["metodo"] = $metodo;
		}else{
			$result["http_code"] = 400;
			$result["titulo"] = "Alerta";
			$result["metodo"] = "Sin nombre";
		}
		$selectStmt->close();
		echo json_encode($result);
		exit();
	} catch (Exception $e) {
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
}


//  Historial de camkbios

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_historial_importacion_proveedor_listar") {
    try {

        $stmt = $mysqli->prepare("SELECT
                                    p.id, p.nombre
                                FROM tbl_conci_proveedor p
                                WHERE p.status = 1 AND p.tipo_importacion_id IS NOT NULL
                                ORDER BY p.nombre ASC;");

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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_historial_importacion_proveedor") {
    $proveedor_id = $_POST["proveedor_id"];

    $where_proveedor = "";
    if($proveedor_id != ""){
        $where_proveedor .= " AND pi.proveedor_id = '".$proveedor_id."' ";
    }
    try {

        $selectQuery = " SELECT 
                            pi.id,
                            pi.nombre_archivo,
                            pi.fecha_inicio,
                            pi.fecha_fin,
                            ifnull(pi.created_count,0),
                            ifnull(pi.updated_count,0),
                            ifnull(pi.reconciled_count,0),
                            ifnull(pi.duplicate_count,0),
                            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_proveedor_importacion pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        WHERE pi.status =1 
                        AND (pi.tipo_archivo_id = 1 OR pi.tipo_archivo_id = 3) -- Tipo de archivo de ventas y combinado
                        $where_proveedor 
                        ORDER BY pi.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre_archivo, $fecha_inicio, $fecha_fin, $created_count, $updated_count, $reconciled_count, $duplicate_count,$created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = '<a class="btn btn-rounded btn-success btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_importar_archivo_proveedor_btn_conciliar(' . $id . ')"
                            title="Conciliar">
                            <i class="fa fa-refresh"></i>
                        </a>';
            $data[] = [
                "0" => count($data) + 1,
                "1" => $fecha_inicio,
                "2" => $fecha_fin,
                "3" => $created_count,
                "4" => $updated_count,
                "5" => $reconciled_count,
                "6" => $duplicate_count,
                "7" => $created_at,
                "8" => $usuario,
                "9" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_historial_importacion_calimaco") {
    try {

        $selectQuery = " SELECT 
                            ci.id,
                            ci.nombre_archivo,
                            ci.fecha_inicio,
                            ci.fecha_fin,
                            ifnull(ci.created_count,0),
                            ifnull(ci.updated_count,0),
                            DATE_FORMAT(ci.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_calimaco_importacion ci
                        LEFT JOIN tbl_usuarios u ON ci.user_created_id = u.id
                        WHERE ci.status =1 
                        AND (ci.tipo_archivo_id = 4) -- Tipo de archivo de calimaco
                        ORDER BY ci.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        $stmt->execute();
        $stmt->bind_result($id, $nombre_archivo, $fecha_inicio, $fecha_fin, $created_count, $updated_count, $created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {

            $data[] = [
                "0" => count($data) + 1,
                "1" => $nombre_archivo,
                "2" => $fecha_inicio,
                "3" => $fecha_fin,
                "4" => $created_count,
                "5" => $updated_count,
                "6" => $created_at,
                "7" => $usuario
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_detalle_proveedor") {
    try {

        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $calimaco_id = $mysqli->query("SELECT transaccion_id FROM tbl_conci_calimaco_transaccion WHERE id= $id LIMIT 1")->fetch_assoc();

        $transaccion_id = $calimaco_id["transaccion_id"];

        $periodo_id = isset($_POST['periodo_id']) ? $_POST['periodo_id'] : null;

        // Consultar para obtener todas las transacciones relacionadas con el ID del proveedor
        $selectQuery = "SELECT 
                            pt.id,
                            pt.data_json,
                            DATE_FORMAT(pt.updated_at, '%d/%m/%Y %H:%i:%s') AS updated_at,
                            u.usuario
                        FROM tbl_conci_proveedor_transaccion pt
                        LEFT JOIN tbl_usuarios u ON pt.user_updated_id = u.id
                        WHERE pt.status = 1 AND pt.transaccion_id = ? AND pt.periodo_id = ?"; // Eliminado el LIMIT 1 para obtener todas las transacciones
        $stmt = $mysqli->prepare($selectQuery);
        $stmt->bind_param("si", $transaccion_id, $periodo_id);
        $stmt->execute();
        $stmt->bind_result($id, $data_json, $updated_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $json_data = json_decode($data_json, true);
            $data[] = $json_data;
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
                    "error" => 'Comunicarse con Soporte, error: ' . $e->getMessage(),
                ],
            ],
        ];

        echo json_encode($resultado);
    }
}


/////////////////////////////////////////////////////////////////////////////////////

//////////   FUNCIONES PARA COMPROBANTES


if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    c.id, 
                    c.proveedor_id,
                    c.tipo_comprobante_id,
                    c.num_documento,
                    IFNULL(c.fecha_emision, '') fecha_emision, 
			        IFNULL(c.fecha_vencimiento, '') fecha_vencimiento,
                    c.razon_social_id,
                    c.area_id,
                    c.etapa_id,
                    c.moneda_id,
                    c.monto,
                    c.status,
                    IFNULL(c.created_at, '') AS created_at,
                    IFNULL(c.updated_at, '') AS updated_at,
                    co.ceco_id,
                    co.num_orden_pago,
                    cf.banco_id,
                    cf.moneda_id,
                    cf.num_cuenta_corriente,
                    cf.num_cuenta_interbancaria,
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update,
					IFNULL(cd_cpdf.nombre, '') AS ad_comprobante_pdf,
                    IFNULL(cd_cxml.nombre, '') AS ad_comprobante_xml,
                    IFNULL(cd_cs.nombre, '') AS ad_contrato_servicio,
                    IFNULL(cd_gr.nombre, '') AS ad_guia_remision,
                    IFNULL(cd_ac.nombre, '') AS ad_acta_conformidad,
                    IFNULL(cd_oc.nombre, '') AS ad_orden_compra
                FROM tbl_comprobante c
                LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
                LEFT JOIN tbl_comprobante_orden_compra co ON co.comprobante_id = c.id
                LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
                LEFT JOIN tbl_comprobante_documento cd_cpdf ON cd_cpdf.comprobante_id = c.id AND cd_cpdf.tipo_documento_id =1 AND cd_cpdf.status=1
                LEFT JOIN tbl_comprobante_documento cd_cxml ON cd_cxml.comprobante_id = c.id AND cd_cxml.tipo_documento_id =2 AND cd_cxml.status=1
                LEFT JOIN tbl_comprobante_documento cd_cs ON cd_cs.comprobante_id = c.id AND cd_cs.tipo_documento_id =3 AND cd_cs.status=1
                LEFT JOIN tbl_comprobante_documento cd_gr ON cd_gr.comprobante_id = c.id AND cd_gr.tipo_documento_id =4 AND cd_gr.status=1
                LEFT JOIN tbl_comprobante_documento cd_ac ON cd_ac.comprobante_id = c.id AND cd_ac.tipo_documento_id =5 AND cd_ac.status=1
                LEFT JOIN tbl_comprobante_documento cd_oc ON cd_oc.comprobante_id = c.id AND cd_oc.tipo_documento_id =6 AND cd_oc.status=1
                WHERE c.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $comprobante_id, 
                                $proveedor_id, 
                                $tipo_comprobante_id,
                                $num_documento,
                                $fecha_emision,
                                $fecha_vencimiento,
                                $razon_social_id,
                                $area_id,
                                $etapa_id,
                                $moneda_id,
                                $monto,
                                $status,
                                $created_at,
                                $updated_at,
                                $oc_ceco_id,
                                $oc_num_orden_pago,
                                $fp_banco_id,
                                $fp_moneda_id,
                                $fp_num_cuenta_corriente,
                                $fp_num_cuenta_interbancaria,
                                $usuario_create,
                                $usuario_update,
                                $ad_comprobante_pdf,
                                $ad_comprobante_xml,
                                $ad_contrato_servicio,
                                $ad_guia_remision,
                                $ad_acta_conformidad,
                                $ad_orden_compra
                            );

            if($fecha_emision != '') $fecha_emision = date("d-m-Y", strtotime($fecha_emision));  
            if($fecha_vencimiento != '') $fecha_vencimiento = date("d-m-Y", strtotime($fecha_vencimiento));  
                                                 
            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $comprobante_id,
                        'proveedor_id' => $proveedor_id,
                        'tipo_comprobante_id' => $tipo_comprobante_id,
                        'num_documento' => $num_documento,
                        'fecha_emision' => $fecha_emision,
                        'fecha_vencimiento' => $fecha_vencimiento,
                        'razon_social_id' => $razon_social_id,
                        'area_id' => $area_id,
                        'etapa_id' => $etapa_id,
                        'moneda_id' => $moneda_id,
                        'monto' => $monto,
                        'status' => $status,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'oc_ceco_id' => $oc_ceco_id,
                        'oc_num_orden_pago' => $oc_num_orden_pago,
                        'fp_banco_id' => $fp_banco_id,
                        'fp_moneda_id' => $fp_moneda_id,
                        'fp_num_cuenta_corriente' => $fp_num_cuenta_corriente,
                        'fp_num_cuenta_interbancaria' => $fp_num_cuenta_interbancaria,
                        'usuario_create' => $usuario_create,
                        'usuario_update' => $usuario_update,
                        'ad_comprobante_pdf' => $ad_comprobante_pdf,
                        'ad_comprobante_xml' => $ad_comprobante_xml,
                        'ad_contrato_servicio' => $ad_contrato_servicio,
                        'ad_guia_remision' => $ad_guia_remision,
                        'ad_acta_conformidad' => $ad_acta_conformidad,
                        'ad_orden_compra' => $ad_orden_compra
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

//////////   FUNCIONES PARA SUBDIARIOS

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_proveedor_obtener_nombre")   /////////////  NEW
{
	try{
		$proveedor_id = $_POST["proveedor_id"];
		$selectQuery = "SELECT 
							nombre
						FROM tbl_comprobante_proveedor 
						WHERE status = '1' AND id = ? 
						ORDER BY nombre ASC LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $proveedor_id);
		$selectStmt->execute();
		$selectStmt->store_result();

		if ($selectStmt->num_rows > 0) {
			$selectStmt->bind_result($descripcion);
			$selectStmt->fetch();

			$result["http_code"] = 200;
			$result["descripcion"] = $descripcion;
		}else{
			$result["http_code"] = 400;
			$result["titulo"] = "Alerta";
			$result["descripcion"] = "Sin nombre";
		}
		$selectStmt->close();
		echo json_encode($result);
		exit();
	} catch (Exception $e) {
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_empresa_at_obtener_nombre")   /////////////  NEW
{
	try{
		$razon_social_id = $_POST["razon_social_id"];
		$selectQuery = "SELECT 
							nombre
						FROM tbl_razon_social 
						WHERE status = '1' AND id = ? 
						ORDER BY nombre ASC LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $razon_social_id);
		$selectStmt->execute();
		$selectStmt->store_result();

		if ($selectStmt->num_rows > 0) {
			$selectStmt->bind_result($descripcion);
			$selectStmt->fetch();

			$result["http_code"] = 200;
			$result["descripcion"] = $descripcion;
		}else{
			$result["http_code"] = 400;
			$result["titulo"] = "Alerta";
			$result["descripcion"] = "Sin nombre";
		}
		$selectStmt->close();
		echo json_encode($result);
		exit();
	} catch (Exception $e) {
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_ceco_obtener_descripcion")   /////////////  NEW
{
	try{
		$cc_id = $_POST["cc_id"];
		$selectQuery = "SELECT * FROM (
                            SELECT 
                                nombre,
                                cc_id
                                FROM tbl_locales 
                                WHERE estado = '1' AND cc_id <> ''
                        UNION ALL
                            SELECT
                                nombre,
                                centro_costo AS cc_id
                                FROM mepa_zona_asignacion
                                WHERE status = 1
                        ) AS combined_tables
                        WHERE cc_id = ?
                        LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("s", $cc_id);
		$selectStmt->execute();
		$selectStmt->store_result();

		if ($selectStmt->num_rows > 0) {
			$selectStmt->bind_result($descripcion,$cc_id);
			$selectStmt->fetch();

			$result["http_code"] = 200;
			$result["descripcion"] = "(".$cc_id.") ".$descripcion;
		}else{
			$result["http_code"] = 400;
			$result["titulo"] = "Alerta";
			$result["descripcion"] = "Sin descripción";
		}
		$selectStmt->close();
		echo json_encode($result);
		exit();
	} catch (Exception $e) {
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
}
//////////   FUNCIONES PARA CANALES Y REDES

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_moneda_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT(nombre,' (',simbolo,')') AS nombre
            FROM tbl_moneda
            WHERE estado = 1 AND nombre IS NOT NULL
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_area_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_areas
            WHERE estado = 1 AND nombre IS NOT NULL
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_ceco_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT * FROM (
                SELECT
                    cc_id AS id, 
                    cc_id
                FROM tbl_locales
                WHERE estado = 1 AND nombre IS NOT NULL AND cc_id IS NOT NULL
                UNION ALL
                SELECT
                    centro_costo AS id, 
                    centro_costo AS cc_id
                FROM mepa_zona_asignacion
                WHERE status = 1 AND centro_costo IS NOT NULL
                ) AS combined_tables
                ORDER BY id ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_banco_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre
            FROM tbl_bancos
            WHERE estado = 1 AND nombre IS NOT NULL
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_proveedor_listar_ruc") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                ruc
            FROM tbl_comprobante_proveedor
            WHERE status = 1
            ORDER BY ruc ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_proveedor_listar_ruc_nombre") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT('(',ruc,') ',nombre) AS nombre
            FROM tbl_comprobante_proveedor
            WHERE status = 1 AND ruc IS NOT NULL AND nombre IS NOT NULL
            ORDER BY ruc ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_empresa_at_listar_ruc") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                ruc
            FROM tbl_razon_social
            WHERE status = 1 AND ruc IS NOT NULL
            ORDER BY ruc ASC;
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
            $result["result"] = "La empresa AT no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_empresa_at_listar_nombre") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT('(',ruc,') ',nombre) AS nombre
            FROM tbl_razon_social
            WHERE status = 1 AND ruc IS NOT NULL
            ORDER BY ruc ASC;
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
            $result["result"] = "La empresa AT no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_tipo_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_comprobante_tipo
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
            $result["result"] = "El comprobante no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_etapa_listar") {
    try {

        $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'comprobante' AND sub_sec_id = 'pago' LIMIT 1")->fetch_assoc();

        $menu_permiso = $menu_id_consultar["id"];

        //  INICIO PERMISOS DE ESTADOS

        $selectQueryPermisoEtapa = "SELECT 
                ce.id,
                ce.nombre,
                ce.permiso
            FROM tbl_comprobante_etapa ce
            WHERE ce.status = 1
            ";

        $stmtPermisoEtapa = $mysqli->prepare($selectQueryPermisoEtapa);
        $stmtPermisoEtapa->execute();
        $stmtPermisoEtapa->bind_result($id_etapa, $nombre_etapa, $permiso_etapa);

        $etapas = "1";
        $etapasArray = [];

        while ($stmtPermisoEtapa->fetch()) {
        if (in_array($permiso_etapa, $usuario_permisos[$menu_permiso])) {
            $etapasArray[] = $id_etapa;
        }
        }

        //  VERIFICAR SI EL ARREGLO ESTA VACIO
        if(!empty($etapasArray)){

            // Condiciones de etapa

            foreach ($etapasArray as $etapa) {
                if ($etapa > 1) {
                    if ($etapa==5) {
                        $etapasArray[] = $etapa - 2;
            
                    }else{
                        $etapasArray[] = $etapa - 1;
                        }
                    }
                }

            // Filtrar duplicados y ordenar
            $etapasArray = array_unique($etapasArray);
            sort($etapasArray);

            // Si existe la etapa 2, incluir la etapa 4
                
            if (in_array(1, $etapasArray)) {
                $etapasArray[] = 4;
                }
            else{

                // Eliminar la etapa 4 si existe en $etapasArray
                $key = array_search(4, $etapasArray);
                if ($key !== false) {
                    unset($etapasArray[$key]);
                }
                }

            if (in_array(2, $etapasArray) && !in_array(3, $etapasArray)) {
                    // Eliminar la etapa 4 si existe en $etapasArray
                    $key = array_search(2, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                }
    
            if (in_array(3, $etapasArray) && !in_array(5, $etapasArray)) {
                    // Eliminar la etapa 4 si existe en $etapasArray
                    $key = array_search(3, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                    //$etapasArray[] = 5;
                    $etapasArray[] = 6;

                }
            if (in_array(6, $etapasArray) && !in_array(2, $etapasArray)) {
                // Eliminar la etapa Observar por tesoreria si existe en $etapasArray y no existe la etapa enviado
                $key = array_search(6, $etapasArray);
                if ($key !== false) {
                    unset($etapasArray[$key]);
                }
                }
            // Filtrar duplicados y ordenar
            $etapasArray = array_unique($etapasArray);
            sort($etapasArray);
                        
            // Crear la cadena de etapas
            $etapas = implode(",", $etapasArray);

            $where_permiso_etapa = " AND  id IN (".$etapas.") ";


        }else{
            $where_permiso_etapa = " AND id IN (".$etapas.") ";
        }

        $stmtPermisoEtapa->close();

        //  FIN PERMISOS ESTADOS


        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre
            FROM tbl_comprobante_etapa
            WHERE status = 1 AND nombre IS NOT NULL
            $where_permiso_etapa
            ;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_motivo_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre
            FROM tbl_comprobante_motivo_reversion
            WHERE status = 1 AND nombre IS NOT NULL
            ;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_tipo_documento_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre, extension, obligatorio
            FROM tbl_comprobante_documento_tipo
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
            $result["result"] = "El comprobante no existe.";
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

//---------- Historico de cambios de cuentas contables

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_obtener_campos") {
    try {

        $stmt = $mysqli->prepare("
                                SELECT campo AS id, nombre_campo AS nombre
                                FROM tbl_comprobante_campos
                                WHERE status = 1;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_obtener_historico_etapas") {
    $comprobante_id = $_POST['comprobante_id'];

    try {

        $selectQuery = " SELECT * FROM (
                            SELECT 
                                che.id, 
                                ce.nombre AS etapa_nombre, 
                                che.motivo, 
                                DATE_FORMAT(che.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
                                u.usuario AS usuario_create
                            FROM tbl_comprobante_historial_etapas che
                            LEFT JOIN tbl_comprobante_etapa ce ON che.etapa_id = ce.id
                            LEFT JOIN tbl_usuarios u ON u.id = che.user_updated_id
                            WHERE che.comprobante_id = ?
                            UNION ALL
                            SELECT 
                                che.id, 
                                CASE
                                    WHEN che.estado_id = 1 THEN 'Activo'
                                    WHEN che.estado_id = 0 THEN 'Inactivo'
                                    ELSE 'Desconocido'
                                    END AS estado_nombre, 
                                che.motivo, 
                                che.created_at, 
                                u.usuario AS usuario_create
                            FROM tbl_comprobante_historial_estado che
                            LEFT JOIN tbl_usuarios u ON u.id = che.user_created_id
                            WHERE che.comprobante_id = ?
                        ) AS combined_tables
                        ORDER BY created_at DESC;
                        " ; 

        $stmt = $mysqli->prepare($selectQuery);

        $stmt->bind_param("ii", $comprobante_id, $comprobante_id);
        $stmt->execute();
        $stmt->bind_result($id, $etapa_nombre, $motivo, $created_at, $usuario_create);

        $data = [];

        while ($stmt->fetch()) {

            $data[] = [
                "0" => count($data) + 1,
                "1" => $etapa_nombre,
                "2" => $motivo,
                "3" => $created_at,
                "4" => $usuario_create
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

//  EXPORTAR

if (isset($_POST["accion"]) && $_POST["accion"]==="comp_exportar_listado")
{
    $usuario_id = $login ? $login['id'] : null;

	$proveedor_id = $_POST['proveedor_id'];
	$razon_social_id = $_POST['razon_social_id'];
	$etapa_id = $_POST['etapa_id'];
    $estado_id = $_POST['estado_id'];
	$fecha_inicio_registro = $_POST['fecha_inicio_registro'];
	$fecha_fin_registro = $_POST['fecha_fin_registro'];
	$fecha_inicio_emision = $_POST['fecha_inicio_emision'];
	$fecha_fin_emision = $_POST['fecha_fin_emision'];

    //  Filtro de busqueda

    $where_proveedor="";
    $where_razon_social="";
    $where_etapa="";
    $where_estado="";

    //  FILTROS

        if (!Empty($proveedor_id))
        {
            $where_proveedor = " AND c.proveedor_id = '".$proveedor_id."' ";
        }

        if (!Empty($razon_social_id))
        {
            $where_razon_social = " AND c.razon_social_id = '".$razon_social_id."' ";
        }

        if (!Empty($etapa_id))
        {
            $where_etapa = " AND c.etapa_id = '".$etapa_id."' ";
        }

        if ($estado_id != "")
        {
            $where_estado = " AND c.status = '".$estado_id."' ";
        }

        $where_fecha_emision= "";
        $where_fecha_registro ="";

        if (!Empty($fecha_inicio_registro) && !Empty($fecha_fin_registro)) {
            $where_fecha_registro = " AND c.created_at BETWEEN '$fecha_inicio_registro 00:00:00' AND '$fecha_fin_registro 23:59:59'";
        } elseif (!Empty($fecha_inicio_registro)) {
            $where_fecha_registro = " AND c.created_at >= '$fecha_inicio_registro 00:00:00'";
        } elseif (!Empty($fecha_fin_registro)) {
            $where_fecha_registro = " AND c.created_at <= '$fecha_fin_registro 23:59:59'";
        }

        if (!Empty($fecha_inicio_emision) && !Empty($fecha_fin_emision)) {
            $where_fecha_emision = " AND c.created_at BETWEEN '$fecha_inicio_emision 00:00:00' AND '$fecha_fin_emision 23:59:59'";
        } elseif (!Empty($fecha_inicio_emision)) {
            $where_fecha_emision = " AND c.created_at >= '$fecha_inicio_emision 00:00:00'";
        } elseif (!Empty($fecha_fin_emision)) {
            $where_fecha_emision = " AND c.created_at <= '$fecha_fin_emision 23:59:59'";
        }

    // PERMISOS ESTADOS

    $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'comprobante' AND sub_sec_id = 'pago' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];

    //  INICIO PERMISOS DE ESTADOS

        $selectQueryPermisoEtapa = "SELECT 
                                    ce.id,
                                    ce.nombre,
                                    ce.permiso
                                FROM tbl_comprobante_etapa ce
                                WHERE ce.status = 1
                                ";

    $stmtPermisoEtapa = $mysqli->prepare($selectQueryPermisoEtapa);
    $stmtPermisoEtapa->execute();
    $stmtPermisoEtapa->bind_result($id_etapa, $nombre_etapa, $permiso_etapa);

    $etapas = "1";
    $etapasArray = [];

    while ($stmtPermisoEtapa->fetch()) {
        if (in_array($permiso_etapa, $usuario_permisos[$menu_permiso])) {
        $etapasArray[] = $id_etapa;
        }
    }


    if(!empty($etapasArray)){

        // Condiciones de etapa

        foreach ($etapasArray as $etapa) {
            if ($etapa > 1) {
                if ($etapa==5) {
                    $etapasArray[] = $etapa - 2;
        
                }else{
                    $etapasArray[] = $etapa - 1;
                    }
                }
            }

        // Filtrar duplicados y ordenar
        $etapasArray = array_unique($etapasArray);
        sort($etapasArray);

        // Si existe la etapa 2, incluir la etapa 4
            
        if (in_array(1, $etapasArray)) {
            $etapasArray[] = 4;
            }
        else{

            // Eliminar la etapa 4 si existe en $etapasArray
            $key = array_search(4, $etapasArray);
            if ($key !== false) {
                unset($etapasArray[$key]);
            }
            }

        if (in_array(2, $etapasArray) && !in_array(3, $etapasArray)) {
                // Eliminar la etapa 4 si existe en $etapasArray
                $key = array_search(2, $etapasArray);
                if ($key !== false) {
                    unset($etapasArray[$key]);
                }
            }

        if (in_array(3, $etapasArray) && !in_array(5, $etapasArray)) {
                // Eliminar la etapa 4 si existe en $etapasArray
                $key = array_search(3, $etapasArray);
                if ($key !== false) {
                    unset($etapasArray[$key]);
                }
                
            }

        // Filtrar duplicados y ordenar
        $etapasArray = array_unique($etapasArray);
        sort($etapasArray);
                    
        // Crear la cadena de etapas
        $etapas = implode(",", $etapasArray);

        $where_permiso_etapa = " AND  c.etapa_id IN (".$etapas.") ";


    }else{
        $where_permiso_etapa = " AND c.etapa_id IN (".$etapas.") ";
    }

    $stmtPermisoEtapa->close();

    //  FIN PERMISOS ESTADOS

	$query = "
        SELECT
            c.id,
            c.created_at,
            u.usuario AS usuario_create,
            ce.nombre AS etapa_nombre,
            tc.nombre AS tipo_nombre,
            c.num_documento,
            c.fecha_emision,
            c.fecha_vencimiento,
            cp.ruc AS proveedor_ruc,
            cp.nombre AS proveedor_nombre,
            rz.ruc AS empresa_at_ruc,
            rz.nombre AS empresa_at_nombre,
            c.monto,
            CONCAT(m.nombre,' (',m.simbolo,')') AS moneda,
            a.nombre AS area_nombre,
            b.nombre AS banco_nombre,
            CONCAT(mcf.nombre,' (',mcf.simbolo,')') AS fp_moneda,
            cf.num_cuenta_corriente,
            cf.num_cuenta_interbancaria,    
            c.updated_at,
            ua.usuario AS usuario_update
        FROM tbl_comprobante c
            LEFT JOIN tbl_comprobante_etapa ce ON c.etapa_id = ce.id
            LEFT JOIN tbl_comprobante_proveedor cp  ON c.proveedor_id = cp.id
            LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
            LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
                LEFT JOIN tbl_comprobante_tipo tc ON tc.id = c.tipo_comprobante_id
                LEFT JOIN tbl_razon_social rz ON rz.id = c.razon_social_id
                LEFT JOIN tbl_areas a ON a.id = c.area_id
                LEFT JOIN tbl_bancos b ON cf.banco_id = b.id
                LEFT JOIN tbl_moneda m ON m.id = c.moneda_id
                LEFT JOIN tbl_moneda mcf ON mcf.id = cf.moneda_id
        WHERE 1=1 
        $where_proveedor
        $where_razon_social
        $where_etapa
        $where_estado
        $where_fecha_emision
        $where_fecha_registro
        $where_permiso_etapa
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/comprobantes/reportes/";

	if (!is_dir($path)) 
	{

        $data_return = array(
            "error" => 'No existe la carpeta "reportes" en la ruta "/files_bucket/comprobantes/" del servidor',
            "titulo" => "Error al exportar el excel",
            "http_code" => 400
        );
        echo json_encode($data_return);
        exit;
	}

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$titulosColumnas = array('Nº', 
                            'Fecha de registro', 
                            'Usuario que registró', 
                            'Etapa', 
                            'Tipo de comprobante', 
                            'Número de documento', 
                            'F. de Emisión', 
                            'F. de vencimiento', 
                            'Monto del importe', 
                            'Moneda', 
                            'RUC del Proveedor', 
                            'Nombre del proveedor', 
                            'RUC de Empresa AT', 
                            'Nombre de Empresa AT', 
                            'Área Solicitante', 
                            'Banco',
                            'Moneda',
                            'Nro. Cuenta Corriente',
                            'Nro. Código de Cuenta Interbancaria CCI.',
                            'F. de ultima modificación',
                            'Usuario que modifico'
                        );

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
        ->setCellValue('B1', $titulosColumnas[1])
        ->setCellValue('C1', $titulosColumnas[2])
        ->setCellValue('D1', $titulosColumnas[3])
        ->setCellValue('E1', $titulosColumnas[4])
        ->setCellValue('F1', $titulosColumnas[5])
        ->setCellValue('G1', $titulosColumnas[6])
        ->setCellValue('H1', $titulosColumnas[7])
        ->setCellValue('I1', $titulosColumnas[8])
        ->setCellValue('J1', $titulosColumnas[9])
        ->setCellValue('K1', $titulosColumnas[10])
        ->setCellValue('L1', $titulosColumnas[11])
        ->setCellValue('M1', $titulosColumnas[12])
        ->setCellValue('N1', $titulosColumnas[13])
        ->setCellValue('O1', $titulosColumnas[14])
        ->setCellValue('P1', $titulosColumnas[15])
        ->setCellValue('Q1', $titulosColumnas[16])
        ->setCellValue('R1', $titulosColumnas[17])
        ->setCellValue('S1', $titulosColumnas[18])
        ->setCellValue('T1', $titulosColumnas[19])
        ->setCellValue('U1', $titulosColumnas[20]);
    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['created_at'])
		->setCellValue('C'.$i, $fila['usuario_create'])
		->setCellValue('D'.$i, $fila['etapa_nombre'])
		->setCellValue('E'.$i, $fila['tipo_nombre'])
		->setCellValue('F'.$i, $fila['num_documento'])
		->setCellValue('G'.$i, $fila['fecha_emision'])
		->setCellValue('H'.$i, $fila['fecha_vencimiento'])
		->setCellValue('I'.$i, $fila['monto'])
		->setCellValue('J'.$i, $fila['moneda'])
		->setCellValue('K'.$i, $fila['proveedor_ruc'])
		->setCellValue('L'.$i, $fila['proveedor_nombre'])
		->setCellValue('M'.$i, $fila['empresa_at_ruc'])
		->setCellValue('N'.$i, $fila['empresa_at_nombre'])
		->setCellValue('O'.$i, $fila['area_nombre'])
        ->setCellValue('P'.$i, $fila['banco_nombre'])
		->setCellValue('Q'.$i, $fila['fp_moneda'])
        ->setCellValue('R'.$i, $fila['num_cuenta_corriente'])
		->setCellValue('S'.$i, $fila['num_cuenta_interbancaria'])
		->setCellValue('T'.$i, $fila['updated_at'])
		->setCellValue('U'.$i, $fila['usuario_update']);
		
		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => '000000'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => 'FFFFFF')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    ),
	    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	// SIRVE PARA PONER ALTURA A LA FILA, EN ESTE CASO A LA FILA 1.
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(63);

	$objPHPExcel->getActiveSheet()->getStyle('A1:U1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:U".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:U'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('I2:I'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'U'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Comprobantes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
    
    $file_name = "Comprobantes_".date("Ymd");
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/comprobantes/reportes/'.$file_name.'.xls';
    $excel_path_download = '/files_bucket/comprobantes/reportes/'.$file_name.'.xls';
	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
        $data_return = array(
            "error" => $e,
            "titulo" => "Error al guardar el excel",
            "http_code" => 400
        );
        echo json_encode($data_return);
		exit;
	}

    $data_return = array(
        "ruta_archivo" => $excel_path_download,
        "http_code" => 200
    );
	echo json_encode($data_return);
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_exportar_zip") {
    $comprobante_id = $_POST['comprobante_id'];


	// DATA DE RESUMEN DE SOLICITUD
	$queryResumenSolicitud = "
            SELECT 
            c.id, 
            c.proveedor_id,
            c.tipo_comprobante_id,
            c.num_documento,
            cp.ruc AS proveedor_ruc,
            IFNULL(c.fecha_emision, '') fecha_emision, 
            IFNULL(c.fecha_vencimiento, '') fecha_vencimiento,
            c.razon_social_id,
            c.area_id,
            c.etapa_id,
            c.moneda_id,
            c.monto,
            c.status,
            IFNULL(c.created_at, '') AS created_at,
            IFNULL(c.updated_at, '') AS updated_at,
            co.ceco_id,
            co.num_orden_pago,
            cf.banco_id,
            cf.moneda_id,
            cf.num_cuenta_corriente,
            cf.num_cuenta_interbancaria,
            u.usuario AS usuario_create,
            ua.usuario AS usuario_update,
            p.correo AS correo_create
        FROM tbl_comprobante c
        LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
        LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
        LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id 
        LEFT JOIN tbl_comprobante_proveedor cp ON cp.id = c.proveedor_id
        LEFT JOIN tbl_comprobante_orden_compra co ON co.comprobante_id = c.id
        LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
        WHERE c.id= '".$comprobante_id."'
        LIMIT 1
        ";
	$list_query_resumen = $mysqli->query($queryResumenSolicitud)->fetch_assoc();
	$num_documento=$list_query_resumen["num_documento"];
	$fecha_emision=$list_query_resumen["fecha_emision"];


    // PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
    $path = '/var/www/html/files_bucket/comprobantes/archivos_zip/';

    if (!is_dir($path)) 
	{
        echo json_encode(array(
            "descripcion" => 'No existe la carpeta "archivos_zip" en la ruta "/files_bucket/comprobantes/" del servidor',
            "titulo" =>  'Error al exportar zip',
            "http_code" => 400
        ));
        exit();

		//mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/comprobantes/archivos_zip/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file);
	}

    // Crear un archivo ZIP en la dirección especificada
    $zipFilename = 'Comprobante - '.$num_documento.' - '.$fecha_emision.'.zip';

	$zip = new ZipArchive();
	// Ruta absoluta
	$nombreArchivoZip = $path . $zipFilename;
	$nombreArchivoZipRuta = '/files_bucket/comprobantes/archivos_zip/' . $zipFilename;

	if (!$zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
		exit("Error abriendo ZIP en $nombreArchivoZip");
	}

	////////////////////////////////////////////////////////////////////////////
	$sel_query_detalle_liquidacion = $mysqli->prepare("
            SELECT
                dl.id,
                dl.nombre,
                dl.extension,
                dl.download
            FROM tbl_comprobante_constancia dl
            WHERE dl.comprobante_id = ?
            AND dl.status =1
            UNION
            SELECT
                dl.id,
                dl.nombre,
                dl.extension,
                dl.download
            FROM tbl_comprobante_documento dl
            WHERE dl.comprobante_id = ?
            AND dl.status =1
        ");
        $sel_query_detalle_liquidacion->bind_param("ii", $comprobante_id, $comprobante_id);
        $sel_query_detalle_liquidacion->execute();

        $list_query_liquidacion = $sel_query_detalle_liquidacion->get_result();
        $row_count_detalle_liquidacion = $list_query_liquidacion->num_rows;

        if ($row_count_detalle_liquidacion > 0) {
            while ($row = $list_query_liquidacion->fetch_assoc()) {
                $fileToInclude = $row['nombre'];
                $fileFullPath = $row['download']. $row['nombre'];
                // Agregar el archivo al archivo ZIP
                $zip->addFile($fileFullPath, $fileToInclude);
            }
        }
	/////////////////////////////////////////////////////////////////////////////
	// No olvides cerrar el archivo
	$resultado = $zip->close();
    //chmod($zipFilename, 0777);
    
	if ($resultado) {
		
		$jsonResponse = json_encode(array(
            "ruta_archivo" => $nombreArchivoZipRuta,
            "http_code" => 200
        ));

        echo $jsonResponse;
		// Cerrar la conexión a la base de datos
		$mysqli->close();
	} else {
		echo "Error creando archivo";
	}
    
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_da_descargar") {
    $comprobante_id = $_POST['comprobante_id'];
    $tipo_documento_id = $_POST['tipo_documento_id'];

	////////////////////////////////////////////////////////////////////////////
	$selectQuery = "SELECT nombre,extension
                    FROM tbl_comprobante_documento
                    WHERE comprobante_id = ?
                    AND tipo_documento_id= ?
                    AND status=1";

    $selectStmt = $mysqli->prepare($selectQuery);
    $selectStmt->bind_param("ii", $comprobante_id, $tipo_documento_id);
    $selectStmt->execute();
    $selectStmt->store_result();

    if ($selectStmt->num_rows > 0) {
        $selectStmt->bind_result($nombre,$extension);
        $selectStmt->fetch();
        $nombreArchivoZipRuta = '/files_bucket/comprobantes/documentos/' . $nombre;

		$jsonResponse = json_encode(array(
            "ruta_archivo" => $nombreArchivoZipRuta,
            "nombre" => $nombre,
            "extension" => $extension

        ));

        echo $jsonResponse;
		
	} else {
		echo "Error creando archivo";
	}
    
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_obtener_pago") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    c.id, 
                    IFNULL(cf.banco_id, 0) AS banco_id,
                    IFNULL(cf.moneda_id, 0) AS moneda_id,
                    cf.num_cuenta_corriente,
                    cf.num_cuenta_interbancaria,
					IFNULL(cc_pd.nombre, '') AS cd_pago_detraccion,
                    IFNULL(cc_pd.monto, '') AS cd_pd_monto,
                    IFNULL(cc_pd.moneda_id, '') AS cd_pd_moneda_id,
                    IFNULL(cc_pp.nombre, '') AS cd_pago_proveedor,
                    IFNULL(cc_pp.monto, '') AS cd_pp_monto,
                    IFNULL(cc_pp.moneda_id, '') AS cd_pp_moneda_id
                FROM tbl_comprobante c
                LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
                LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
                LEFT JOIN tbl_comprobante_constancia cc_pd ON cc_pd.comprobante_id = c.id AND cc_pd.tipo_constancia_id =1 AND cc_pd.status=1
                LEFT JOIN tbl_comprobante_constancia cc_pp ON cc_pp.comprobante_id = c.id AND cc_pp.tipo_constancia_id =2 AND cc_pp.status=1
                WHERE c.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $comprobante_id,                            
                                $fp_banco_id,
                                $fp_moneda_id,
                                $fp_num_cuenta_corriente,
                                $fp_num_cuenta_interbancaria,
                                $cd_pago_detraccion,
                                $cd_pd_monto,
                                $cd_pd_moneda_id,
                                $cd_pago_proveedor,
                                $cd_pp_monto,
                                $cd_pp_moneda_id
                            );
                                    
            if ($stmt->fetch() && $fp_banco_id != 0) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $comprobante_id,                    
                        'fp_banco_id' => $fp_banco_id,
                        'fp_moneda_id' => $fp_moneda_id,
                        'fp_num_cuenta_corriente' => $fp_num_cuenta_corriente,
                        'fp_num_cuenta_interbancaria' => $fp_num_cuenta_interbancaria,
                        'cd_pago_detraccion' => $cd_pago_detraccion,
                        'cd_pd_monto' => $cd_pd_monto,
                        'cd_pd_moneda_id' => $cd_pd_moneda_id,
                        'cd_pago_proveedor' => $cd_pago_proveedor,
                        'cd_pp_monto' => $cd_pp_monto,
                        'cd_pp_moneda_id' => $cd_pp_moneda_id
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

//  ANULACION
