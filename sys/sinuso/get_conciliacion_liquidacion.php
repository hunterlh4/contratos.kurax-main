<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_historial_periodo") {
    $proveedor_id = $_POST["proveedor_id"];

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
                            ifnull(pi.comision_calimaco,0),
                            ifnull(pi.comision_proveedor,0),
                            ifnull(pi.liquidated_count,0),
                            ifnull(pi.non_matching_count,0),
                            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_periodo pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        LEFT JOIN tbl_conci_proveedor p ON p.id = pi.proveedor_id
                        WHERE pi.status =1
                        $where_proveedor 
                        ORDER BY pi.created_at DESC" ; 
    
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $proveedor_id, $proveedor, $periodo, $periodo_formato, $conciliacion_completada, $comision_calimaco, $comision_proveedor, $liquidated_count, $non_matching_count,$created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";

            /*
            $botones .= '<a class="btn btn-rounded btn-primary btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_liquidacion_periodo_historial_btn_ver(' . $id . ')"
                            title="Editar">
                            <i class="fa fa-eye"></i></a>';
            */

            if($conciliacion_completada ==0){
                $botones .= ' <a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_liquidacion_periodo_historial_btn_editar('. $id . ','. $proveedor_id.')"
                            title="Editar">
                            <i class="fa fa-pencil"></i></a>';
            }

            $data[] = [
                "0" => count($data) + 1,
                "1" => $proveedor,
                "2" => $periodo_formato,
                "3" => $comision_calimaco,
                "4" => $comision_proveedor,
                "5" => $liquidated_count,
                "6" => $non_matching_count,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_periodo_obtener") {
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
                    IFNULL(fo.updated_at, '')t,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_periodo_historial_importacion_proveedor") {
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
                            ifnull(pi.updated_count,0),
                            ifnull(pi.liquidated_count,0),
                            ifnull(pi.non_existent_count,0),
                            ifnull(pi.non_matching_count,0),
                            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_proveedor_importacion pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        WHERE pi.status =1 
                        AND (pi.tipo_archivo_id = 2) -- Tipo de archivo de liquidacions y combinado
                        $where_proveedor
                        $where_periodo
                        ORDER BY pi.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre_archivo, $fecha_inicio, $fecha_fin, $proveedor_id,$periodo_id,$updated_count, $liquidated_count, $non_existent_count, $non_matching_count,$created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";
            $botones .= '<a class="btn btn-rounded btn-success btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_liquidacion_periodo_importar_archivo_proveedor_btn_liquidar('. $id.','.$periodo_id.')"
                            title="Liquidar">
                            <i class="fa fa-refresh"></i>
                        </a>';

            $botones .= ' <a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                        onclick="sec_conci_liquidacion_importar_proveedor_btn_editar('. $id.')"
                        title="Editar">
                        <i class="fa fa-pencil"></i>
                    </a>';
            $data[] = [
                "0" => count($data) + 1,
                "1" => $fecha_inicio,
                "2" => $fecha_fin,
                "3" => $liquidated_count,
                "4" => $non_matching_count,
                "5" => $non_existent_count,
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
                            ifnull(pi.updated_count,0),
                            ifnull(pi.liquidated_count,0),
                            ifnull(pi.non_existent_count,0),
                            ifnull(pi.non_matching_count,0),
                            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_proveedor_importacion pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        WHERE pi.status =1 
                        AND (pi.tipo_archivo_id = 3) -- Tipo de archivo de liquidacions y combinado
                        $where_proveedor
                        $where_periodo
                        ORDER BY pi.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre_archivo, $fecha_inicio, $fecha_fin, $proveedor_id,$periodo_id,$updated_count, $liquidated_count, $non_existent_count, $non_matching_count,$created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";
            $botones .= '<a class="btn btn-rounded btn-success btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_liquidacion_periodo_importar_archivo_proveedor_btn_liquidar('. $id.','.$periodo_id.')"
                            title="Liquidar">
                            <i class="fa fa-refresh"></i>
                        </a>';

            $botones .= ' <a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                        onclick="sec_conci_liquidacion_importar_proveedor_btn_editar('. $id.')"
                        title="Editar">
                        <i class="fa fa-pencil"></i>
                    </a>';
            $data[] = [
                "0" => count($data) + 1,
                "1" => $fecha_inicio,
                "2" => $fecha_fin,
                "3" => $liquidated_count,
                "4" => $non_matching_count,
                "5" => $non_existent_count,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_listar"){
    $usuario_id = $login ? $login['id'] : null;

	$proveedor_estado_id = $_POST['proveedor_estado_id'];
    $calimaco_estado_id = $_POST['calimaco_estado_id'];
	$estado_liquidacion = $_POST['estado_liquidacion'];
    $proveedor_id = $_POST['proveedor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];

    //  FILTROS

        $where_proveedor="";
        $where_estado_proveedor="";
        $where_estado_liquidacion="";
        $where_estado_calimaco="";
        
        if ($estado_liquidacion != ""){
            $where_estado_liquidacion = " AND ct.estado_liquidacion = ".$estado_liquidacion." ";
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
            IFNULL(pt.comision_total,0.00) AS comision_total,
            IFNULL(pt.comision_total_calculado,0.00) AS comision_total_calculado,
            CASE IFNULL(ct.estado_liquidacion,0)
                WHEN 1 THEN 'COMPLETADO'
                WHEN 0 THEN 'NO LIQUIDADO'
                WHEN 2 THEN 'INCOMPLETO'
                WHEN 3 THEN 'ANULADO'
                ELSE 'Desconocido'
            END AS estado_liquidacion,
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
        WHERE ct.status = 1 AND (ct.estado_conciliacion = 1 OR ct.estado_conciliacion =2)
        $where_proveedor
        $where_estado_liquidacion
        $where_estado_proveedor
        $where_estado_calimaco
        ORDER BY ct.id DESC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{      
        $botones = "";
        //if(in_array("btn_comp_anular", $usuario_permisos[$menu_permiso]) && $reg->status != 0):

        if($reg->estado_liquidacion == 'COMPLETADO'){
            $botones .= '<a class="btn btn-rounded btn-info btn-sm" data-toggle="tooltip" data-placement="top"
                onclick="sec_conci_liquidacion_detalle_liquidacion_proveedor(' . $reg->id . ',' . $reg->periodo_id . ')"
                title="Ver detalle de liquidación">
                <i class="fa fa-info-circle"></i>
            </a>
        ';

        $botones .= '<a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
        onclick="sec_conci_venta_anular(' . $reg->id . ')"
        title="Anular">
        <i class="fa fa-ban"></i>
        </a> ';
        }
           
        //endif;
        $color_resaltar = '';
        if ($reg->estado_liquidacion == 'INCOMPLETO') {
            $color_resaltar = 'yellow';
        }elseif ($reg->estado_liquidacion == 'NO LIQUIDADO') {
            $color_resaltar = 'red';
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
            "7" => $reg->comision_total_calculado,
            "8" => $reg->comision_total,
			"9" => $reg->estado_liquidacion,
			"10" => $botones,
            "11" => $color_resaltar
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_proveedor_listar") {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_proveedor_estado_listar") {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_calimaco_estado_listar") {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_proveedor_obtener_metodo") 
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_historial_importacion_proveedor_listar") {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_historial_importacion_proveedor") {
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
                            ifnull(pi.updated_count,0),
                            ifnull(pi.liquidated_count,0),
                            ifnull(pi.non_existent_count,0),
                            ifnull(pi.non_matching_count,0),
                            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_proveedor_importacion pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        WHERE pi.status =1 
                        AND (pi.tipo_archivo_id = 1 OR pi.tipo_archivo_id = 2)
                        $where_proveedor 
                        ORDER BY pi.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre_archivo, $fecha_inicio, $fecha_fin, $updated_count, $liquidated_count, $non_existent_count,$non_matching_count,$created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = '<a class="btn btn-rounded btn-success btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_liquidacion_importar_archivo_proveedor_btn_liquidar(' . $id . ')"
                            title="Liquidar">
                            <i class="fa fa-refresh"></i>
                        </a>';
            $data[] = [
                "0" => count($data) + 1,
                "1" => $fecha_inicio,
                "2" => $fecha_fin,
                "3" => $updated_count,
                "4" => $non_existent_count,
                "5" => $liquidated_count,
                "6" => $non_matching_count,
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
                            ifnull(pi.updated_count,0),
                            ifnull(pi.liquidated_count,0),
                            ifnull(pi.non_existent_count,0),
                            ifnull(pi.non_matching_count,0),
                            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s'),
                            u.usuario
                        FROM tbl_conci_proveedor_importacion pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        WHERE pi.status =1 
                        AND (pi.tipo_archivo_id = 1 OR pi.tipo_archivo_id = 2)
                        $where_proveedor 
                        ORDER BY pi.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $nombre_archivo, $fecha_inicio, $fecha_fin, $updated_count, $liquidated_count, $non_existent_count,$non_matching_count,$created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = '<a class="btn btn-rounded btn-success btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_liquidacion_importar_archivo_proveedor_btn_liquidar(' . $id . ')"
                            title="Liquidar">
                            <i class="fa fa-refresh"></i>
                        </a>';
            $data[] = [
                "0" => count($data) + 1,
                "1" => $fecha_inicio,
                "2" => $fecha_fin,
                "3" => $updated_count,
                "4" => $non_existent_count,
                "5" => $liquidated_count,
                "6" => $non_matching_count,
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


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_historial_importacion_calimaco") {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_detalle_proveedor") {
    try {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $periodo_id = isset($_POST['periodo_id']) ? $_POST['periodo_id'] : null;
        $calimaco_id = $mysqli->query("SELECT transaccion_id FROM tbl_conci_calimaco_transaccion WHERE id= $id LIMIT 1")->fetch_assoc();

        $transaccion_id = $calimaco_id["transaccion_id"];

        $selectQuery = "SELECT 
                            pt.id,
                            pt.data_json_liquidacion,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_importacion_obtener") {
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