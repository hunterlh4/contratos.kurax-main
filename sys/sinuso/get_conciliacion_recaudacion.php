<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

//  Periodo

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_recaudacion_historial_periodo") {
    $proveedor_id = $_POST["proveedor_id"];

    $where_proveedor = "";
    if($proveedor_id != ""){
        $where_proveedor .= " AND pi.proveedor_id = '".$proveedor_id."' ";
    }
    try {

        $mysqli->query("SET lc_time_names = 'es_ES'");

        $selectQuery = "SELECT 
                            pi.id,
                            pi.proveedor_id,
                            p.nombre,
                            pi.periodo,
                            DATE_FORMAT(pi.periodo, '%M %Y'),
                            ifnull(pi.comision_calimaco,0),
                            ifnull(pi.comision_proveedor,0),
                            ifnull(pi.comision_calimaco,0)- ifnull(pi.comision_proveedor,0),
                            ifnull(pi.monto_recaudado,0),
                            ifnull(pi.comision_calimaco,0)- ifnull(pi.monto_recaudado,0),
                            pi.recaudacion_completada
                        FROM tbl_conci_periodo pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        LEFT JOIN tbl_conci_proveedor p ON p.id = pi.proveedor_id
                        WHERE pi.status =1 -- AND conciliacion_completada = 1
                        $where_proveedor 
                        GROUP BY pi.id
                        ORDER BY pi.created_at DESC" ; 
    
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $proveedor_id, $proveedor, $periodo, $periodo_formato, $comision_calimaco, $comision_proveedor, $comision_diferencia, $monto_recaudado, $monto_no_recaudado, $recaudacion_completada);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";

            /*
            $botones .= '<a class="btn btn-rounded btn-primary btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_venta_periodo_historial_btn_ver(' . $id . ')"
                            title="Editar">
                            <i class="fa fa-eye"></i></a>';
            */

            if($recaudacion_completada ==0){
                $botones .= ' <a class="btn btn-rounded btn-warning btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_recaudacion_periodo_historial_btn_editar('. $id . ','. $proveedor_id.')"
                            title="Editar">
                            <i class="fa fa-pencil"></i></a>';
            }

            $color_resaltar = '';
            if ($monto_recaudado <= 0.01) {
                $color_resaltar = 'red';
            }
            $data[] = [
                "0" => count($data) + 1,
                "1" => $proveedor,
                "2" => $periodo_formato,
                "3" => $comision_calimaco,
                "4" => $comision_proveedor,
                "5" => $comision_diferencia,
                "6" => $monto_recaudado,
                "7" => $monto_no_recaudado,
                "8" => $botones,
                "9" => $color_resaltar
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_recaudacion_periodo_historial_recaudacion") {
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
                            pi.periodo_id,
                            b.nombre,
                            m.nombre,
                            cb.cuenta_corriente,
                            cb.cuenta_interbancaria,
                            CONCAT('Compra: ',ifnull(tc.monto_compra,0),' - Venta: ',ifnull(tc.monto_venta,0)),
                            ifnull(pi.monto,0),
                            DATE_FORMAT(tc.fecha, '%d/%m/%Y'),
                            u.usuario
                        FROM tbl_conci_proveedor_recaudacion pi
                        LEFT JOIN tbl_usuarios u ON pi.user_created_id = u.id
                        LEFT JOIN tbl_conci_proveedor_cuenta_bancaria cb ON pi.cuenta_id = cb.id
                        LEFT JOIN tbl_moneda m ON cb.moneda_id = m.id
                        LEFT JOIN tbl_bancos b ON cb.banco_id = b.id
                        LEFT JOIN tbl_conci_tipo_cambio tc ON pi.fecha = tc.fecha AND tc.moneda_id = cb.moneda_id
                        WHERE pi.status =1 
                        $where_proveedor
                        $where_periodo
                        ORDER BY pi.created_at DESC" ; 
        $stmt = $mysqli->prepare($selectQuery);
        //$stmt->bind_param("i", $proveedor_id);
        $stmt->execute();
        $stmt->bind_result($id, $periodo_id, $banco, $moneda, $cuenta_corriente, $cuenta_interbancaria, $tipo_cambio, $monto,$created_at, $usuario);

        $data = [];

        while ($stmt->fetch()) {
            $botones = "";
            
            $botones .= '<a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_recaudacion_btn_eliminar(' . $id . ','.$periodo_id.')"
                            title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </a>';
            
            $data[] = [
                "0" => count($data) + 1,
                "1" => $banco,
                "2" => $moneda,
                "3" => $cuenta_interbancaria,
                "4" => $tipo_cambio,
                "5" => $monto,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_recaudacion_cuenta_bancaria_listar") {
    try {
        $proveedor_id = $_POST["proveedor_id"];


        $stmt = $mysqli->prepare("
        SELECT
                p.id,CONCAT(p.cuenta_interbancaria,' - ',b.nombre,' - ',m.sigla) AS nombre
            FROM tbl_conci_proveedor_cuenta_bancaria p
            LEFT JOIN tbl_bancos b ON p.banco_id = b.id
            LEFT JOIN tbl_moneda m ON p.moneda_id = m.id
            WHERE p.status = 1 AND p.proveedor_id = ?
            ORDER BY p.cuenta_interbancaria ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $mysqli->query("SET lc_time_names = 'es_ES'");

            $stmt = $mysqli->prepare("
                SELECT 
                    fo.id, 
                    p.nombre,
                    fo.periodo,
                    DATE_FORMAT(fo.periodo, '%M %Y'),
                    IFNULL(fo.created_at, ''),
                    IFNULL(fo.updated_at, '')t,
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM tbl_conci_periodo fo
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
                        $where_periodo
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_recaudacion_exportar"){
    require_once '../phpexcel/classes/PHPExcel.php';
    
    $usuario_id = $login ? $login['id'] : null;
    $formato = $_POST['formato'];
    $proveedor_id = $_POST['proveedor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];
    $periodo = $_POST['periodo'];


    //  FILTROS

        $where_proveedor="";
        $where_fecha ="";
        $where_periodo ="";

        if ($proveedor_id != 0){
            $where_proveedor = " AND p.id = ".$proveedor_id." ";
        }

        if (!Empty($periodo)){
            $where_periodo = " AND pi.periodo = ".$periodo." ";
        }

        if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
            $where_fecha = " AND pi.periodo BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            } elseif (!Empty($fecha_inicio)) {
                $where_fecha = " AND pi.periodo >= '$fecha_inicio 00:00:00'";
            } elseif (!Empty($fecha_fin)) {
                $where_fecha = " AND pi.periodo <= '$fecha_fin 23:59:59'";
        }
   
	$query = "
        SELECT 
            pi.id,
            pi.proveedor_id,
            p.nombre,
            pi.periodo,
            DATE_FORMAT(pi.periodo, '%M %Y') AS periodo,
            ifnull(pi.comision_calimaco,0) AS comision_calimaco,
            ifnull(pi.comision_proveedor,0) AS comision_proveedor,
            ifnull(pi.comision_calimaco,0)- ifnull(pi.comision_proveedor,0) AS diferencia,
            ifnull(pi.monto_recaudado,0) AS monto_recaudado,
            CASE WHEN (ifnull(pi.comision_calimaco,0) - ifnull(pi.monto_recaudado,0)) > 0 THEN (ifnull(pi.comision_calimaco,0) - ifnull(pi.monto_recaudado,0)) ELSE 0 END AS faltante,
            CASE WHEN (ifnull(pi.monto_recaudado,0) - ifnull(pi.comision_calimaco,0)) > 0 THEN (ifnull(pi.monto_recaudado,0) - ifnull(pi.comision_calimaco,0)) ELSE 0 END AS sobrante,
            DATE_FORMAT(pi.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            uc.usuario AS usuario_create,
            DATE_FORMAT(pi.updated_at, '%d/%m/%Y %H:%i:%s') AS updated_at,
            up.usuario AS usuario_update
            FROM tbl_conci_periodo pi
            LEFT JOIN tbl_usuarios uc ON uc.id = pi.user_created_id
            LEFT JOIN tbl_usuarios up ON up.id = pi.user_updated_id
            LEFT JOIN tbl_conci_proveedor p ON p.id = pi.proveedor_id
        WHERE pi.status = 1
        $where_proveedor
        $where_periodo
        $where_fecha
        ORDER BY pi.periodo DESC
	";

    $mysqli->query("SET lc_time_names = 'es_ES'");

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
                                ->setDescription("Reporte de Recaudación");
            
                    $titulosColumnas = array('Nº', 
                                            'Proveedor', 
                                            'Periodo',
                                            'Comisión Calimaco', 
                                            'Comisión Proveedor', 
                                            'Diferencia', 
                                            'Monto Recaudado', 
                                            'Faltante', 
                                            'Sobrante', 
                                            'Fecha de Creación', 
                                            'Usuario Creador', 
                                            'Fecha de Modificación', 
                                            'Usuario Modificador'
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
                        ->setCellValue('I1', $titulosColumnas[8])
                        ->setCellValue('J1', $titulosColumnas[9])
                        ->setCellValue('K1', $titulosColumnas[10])
                        ->setCellValue('L1', $titulosColumnas[11])
                        ->setCellValue('M1', $titulosColumnas[12]);
            
                        $cont = 0;
            
                    $i = 2; 
            
            
                    while($reg = $list_query->fetch_object()) 
                    {      
            
                        $cont ++;
            
                        $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $cont)
                        ->setCellValue('B'.$i, $reg->nombre)
                        ->setCellValue('C'.$i, $reg->periodo)
                        ->setCellValue('D'.$i, $reg->comision_calimaco)
                        ->setCellValue('E'.$i, $reg->comision_proveedor)
                        ->setCellValue('F'.$i, $reg->diferencia)
                        ->setCellValue('G'.$i, $reg->monto_recaudado)
                        ->setCellValue('H'.$i, $reg->faltante)
                        ->setCellValue('I'.$i, $reg->sobrante)
                        ->setCellValue('J'.$i, $reg->created_at)
                        ->setCellValue('K'.$i, $reg->usuario_create)
                        ->setCellValue('L'.$i, $reg->updated_at)
                        ->setCellValue('M'.$i, $reg->usuario_update);
                        
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
            
                    $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->applyFromArray($estiloNombresColumnas);
                    $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:U".($i-1));
                    $objPHPExcel->getActiveSheet()->getStyle('A1:M'.($i-1))->applyFromArray($estilo_centrar);
            
                    $objPHPExcel->getActiveSheet()->getStyle('G2:G'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
            
                    for($i = 'A'; $i <= 'M'; $i++)
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
                    }
            
                    $objPHPExcel->getActiveSheet()->setTitle('Recaudación');
                    
                    $objPHPExcel->setActiveSheetIndex(0);
            
                //  Descargar excel
            
                    $file_name = "Reporte de Recaudación - ".date("Ymd");
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

                    $titulosColumnas = array('Nº', 
                        'Fecha de anulacion', 
                        'Fecha de Transacción',
                        'Etapa', 
                        'Primera Fecha Autorización', 
                        'Usuario Autorizador', 
                        'Segunda Fecha Autorización', 
                        'Usuario Autorizador', 
                        'Tipo', 
                        'Motivo', 
                        'Proveedor', 
                        'Usuario Registrado', 
                        'Transaccion ID', 
                        'Monto',
                        'Fecha creación de solicitud',
                        'Usuario creador de solicitud',
                        'Fecha modificación de solicitud',
                        'Usuario modificador de solicitud'
                    );
                
                // Generar el contenido CSV
                    $csv_content = implode(',', $titulosColumnas) . "\n";
                    
                    $cont = 0;
                    
                    $i = 2;
                    
                    while($reg = $list_query->fetch_object()) {      
                        $cont++;
                    
                        // Generar fila CSV
                        $csv_content .= "$cont,";
                        $csv_content .= "{$reg->created_at},";
                        $csv_content .= "{$reg->fecha},";
                        $csv_content .= "{$reg->nombre_etapa},";
                        $csv_content .= "{$reg->first_authorized_at},";
                        $csv_content .= "{$reg->second_authorized_at},";
                        $csv_content .= "{$reg->first_user_authorized},";
                        $csv_content .= "{$reg->second_user_authorized},";
                        $csv_content .= "{$reg->nombre_tipo},";
                        $csv_content .= "{$reg->motivo},";
                        $csv_content .= "{$reg->nombre_proveedor},";
                        $csv_content .= "{$reg->usuario_registrado},";
                        $csv_content .= "{$reg->transaccion_id},";
                        $csv_content .= "{$reg->cantidad},";
                        $csv_content .= "{$reg->created_at},";
                        $csv_content .= "{$reg->updated_at},";
                        $csv_content .= "{$reg->user_created},";
                        $csv_content .= "{$reg->user_updated}\n";
                    
                        $i++;
                    }
                    
                // Nombre del archivo y ruta de descarga
                $file_name = "Reporte de Anulación - " . date("Ymd") . ".csv";
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


//  Historial de cambios

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
