<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");
include("/var/www/html/sys/helpers.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

function fn_conci_set_status_code_response($code, $message, $title){
    if (empty($code)) {
        $code = 200;
    }

    $http = array(
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        400 => 'HTTP/1.1 400 Formato de Archivo Incorrecto',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Large',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Time-out',
        505 => 'HTTP/1.1 505 HTTP Version Not Supported',
    );
    if ($title != null) {
        $http[$code] = substr($http[$code], 0, 13) . " " . $title;
    }

    header($http[$code]);
    header('Content-type: text/plain');

    if (!empty($message)) {
        exit($message);
    } else {
        exit();
    }
}

function fn_conci_check_headers($worksheet, $fields, $file_columns, $start_line){
    $errores = [];
    $currentColumn = 0;

    foreach ($file_columns as $column) {
        $cellValue = trim($worksheet->getCell($column . $start_line)->getValue());

        // Verificar si el valor de la celda está vacío
        if ($cellValue == "") {
            $errores[] = "-Columna " . $column . " : Se esperaba <strong>" . $fields[$currentColumn]['nombre'] . "</strong>";
            $currentColumn++;
            continue;
        }

        // Verificar si el valor de la celda no coincide con el esperado
        if ($cellValue != $fields[$currentColumn]['nombre']) {
            $errores[] = "-Columna " . $column . " : " . $cellValue . ". Se esperaba <strong>" . $fields[$currentColumn]['nombre'] . "</strong>";
            $currentColumn++;
            continue;
        }

        $currentColumn++;
    }

    if (count($errores) > 0) {
        return "<div style='text-align:left;padding-left:3px'>" . implode("<br>", $errores) . "</div>";
    }
    return true;
}

function fn_conci_get_data_detail($worksheet, $fields, $currentRow, $currentColumn, $file_columns){
    $data_detalle = array();
    $cellValue = "";
    $data_row = array();
    $firstColumnIndex = $currentColumn;
    $row_val = 1;
    
    do {
        $cellValue = trim($worksheet->getCell($file_columns[$currentColumn - $firstColumnIndex] . $currentRow)->getValue());
        
        if ($cellValue != "") {
            foreach ($fields as $index => $field) {
                $key = $field['nombre'];
                $value_type = $field['formato'];
                
                switch ($value_type) {
                    case "datetime":
                        $data_row[$key] = excel_date_to_php($worksheet->getCell($file_columns[$currentColumn - $firstColumnIndex] . $currentRow));
                        break;
                    case "decimal":
                        $data_row[$key] = number_format((float) trim($worksheet->getCell($file_columns[$currentColumn - $firstColumnIndex] . $currentRow)->getValue()), 3, '.', '');
                        break;
                    default:
                        $data_row[$key] = trim($worksheet->getCell($file_columns[$currentColumn - $firstColumnIndex] . $currentRow)->getValue());
                }
                $currentColumn++;
            }
            
            array_push($data_detalle, $data_row);
        }
        
        $currentColumn = $firstColumnIndex;
        $currentRow++;
    } while ($cellValue != "");
    
    return [
        "data_detalle" => $data_detalle,
        "rows" => count($data_detalle)
    ];
}

function fn_conci_getDataType($value) {
    if (is_int($value)) {
        return 'i';
    } elseif (is_double($value)) {
        return 'd';
    } else {
        return 's';
    }
}

//  IMPORTAR ARCHIVO PROVEEDOR   ////////////////////////////////////////////////////////////////////////////////////////////

function checkForFormulas($worksheet) {
    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); 
        
        foreach ($cellIterator as $cell) {
            if ($cell->isFormula()) {
                return true; 
            }
        }
    }
    return false;
}

function fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name) {

    $mysqli->query("SET @orden := 0");

    $queryColumn = "SELECT 
                        subquery.orden, 
                        subquery.nombre, 
                        IFNULL(subquery.prefijo,''), 
                        IFNULL(subquery.simbolo,''),  
                        IFNULL(subquery.sufijo,''),  
                        IFNULL(subquery.nombreColumna_json,''), 
                        subquery.formato_id
                    FROM (
                        SELECT pc.id, pc.nombre,pc.prefijo,ars.simbolo,pc.sufijo,pc.nombreColumna_json,pc.formato_id,pc.orden
                        FROM tbl_conci_proveedor_columna pc
                        LEFT JOIN tbl_conci_archivo_separador ars ON pc.separador_id = ars.id
                        WHERE pc.proveedor_id = ? AND pc.tipo_archivo_id = ? AND pc.status = 1
                        ORDER BY pc.id
                    ) AS subquery
                    WHERE subquery.id IN (
                        SELECT pc.id
                        FROM tbl_conci_proveedor_columna pc
                        LEFT JOIN tbl_conci_calimaco_columna cc ON pc.columna_id = cc.id
                        WHERE cc.nombre_bd = ? AND pc.tipo_archivo_id = ? AND pc.status = 1
                        AND pc.proveedor_id = ?
                    );";

    $stmtColumn = $mysqli->prepare($queryColumn);

    if (!$stmtColumn) {
        fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: " . $mysqli->error, "Error");
    }

    $stmtColumn->bind_param("iisii", $provider_id, $tipo_archivo_id, $calimaco_column_name, $tipo_archivo_id, $provider_id);
    $stmtColumn->execute();
    $stmtColumn->store_result();

    if ($stmtColumn->num_rows > 0) {
        $stmtColumn->bind_result($column_provider_orden, 
                                    $column_provider_nombre,
                                    $column_provider_prefijo, 
                                    $column_provider_simbolo,
                                    $column_provider_sufijo,
                                    $column_provider_nombreColumna_json,
                                    $column_provider_formato_id);
        $stmtColumn->fetch();
        $stmtColumn->close(); 
        return [$column_provider_orden, 
                $column_provider_nombre, 
                $column_provider_prefijo, 
                $column_provider_simbolo, 
                $column_provider_sufijo, 
                $column_provider_nombreColumna_json,
                $column_provider_formato_id
            ];
    } else {
        fn_conci_set_status_code_response(400, "No se encontró una columna que corresponda a '{$calimaco_column_name}' de Calimaco: " . $mysqli->error, "Error");
    }
}

//  PERIODO

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_periodo_importar_proveedor") {

    $datos = json_decode($_POST['datos'], true);
    $periodo_id = $_POST["periodo_id"];
    $provider_id = $_POST['proveedor_id'];
    $tipo_archivo_id = 2;

    // Consultar las columnas del archivo del proveedor   //////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $selectColumnaProveedor = "SELECT nombre FROM tbl_conci_proveedor_columna WHERE proveedor_id = ? AND tipo_archivo_id = ? AND status = 1";
        $stmt = $mysqli->prepare($selectColumnaProveedor);

        if (!$stmt) {
            fn_conci_set_status_code_response(400, "Error en la preparación de la consulta de columnas de proveedor".$mysqli->error, "Error");
        }

        $stmt->bind_param("ii", $provider_id,$tipo_archivo_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $fields_provider = [];
        $file_columns = [];

        if ($result->num_rows > 0) {
            $index = 0;
            while($row = $result->fetch_assoc()) {
                $column_name = $row["nombre"];
                $fields_provider[] = [
                    'nombre' => $column_name,
                    'formato' => 'varchar' 
                ];
            }
        } else {
            fn_conci_set_status_code_response(400, "No se encontraron registros de columnas. Contactarse con soporte.", "Error");
        }

        $stmt->close();

    $numero_columnas = count($fields_provider);
    $file_columns = range('A', chr(ord('A') + $numero_columnas - 1));

    $data = fn_conci_provider_periodo_upload_file($fields_provider, $file_columns, $provider_id, $tipo_archivo_id);

    if (count($data["data_detalle"]) == 0) {
        fn_conci_set_status_code_response(400, "No hay registros en el archivo.".$linea, "El archivo no coincide debido a los siguientes errores:");
    }

    $response = fn_conci_provider_periodo_save_data($data, $fields_provider, $file_columns,$provider_id, $tipo_archivo_id, $periodo_id);

    print_r(json_encode($response));
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_periodo_liquidar_proveedor") {
    global $login;
    global $mysqli;
    $datetime = date("Y-m-d H:i:s");
    $import_id = $_POST["importacion_id"];
    $periodo_id = $_POST["periodo_id"];

    //  Consultar datos de periodo

        $selectQueryPeriodo = "SELECT IFNULL(pi.liquidated_count,0), IFNULL(pi.non_matching_count,0),i.metodo
            FROM tbl_conci_periodo pi
            LEFT JOIN tbl_conci_proveedor p ON p.id=pi.proveedor_id
            LEFT JOIN tbl_conci_importacion_tipo i ON i.id=p.tipo_importacion_id
            WHERE pi.status = 1 AND pi.id = ?
            LIMIT 1";

        $selectStmtPeriodo = $mysqli->prepare($selectQueryPeriodo);
        if (!$selectStmtPeriodo) {
        fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
        }
        $selectStmtPeriodo->bind_param("i", $periodo_id);
        if (!$selectStmtPeriodo->execute()) {
        fn_conci_set_status_code_response(400, "Error al ejecutar la consulta SQL: " . $selectQueryCalimaco . " - " . $mysqli->error, "Error");
        }
        $selectStmtPeriodo->store_result();

        if ($selectStmtPeriodo->num_rows > 0) {
        $selectStmtPeriodo->bind_result($periodo_liquidated_count, $periodo_non_matching_count, $metodo_importacion);
        $selectStmtPeriodo->fetch();
        $selectStmtPeriodo->free_result(); 
        $selectStmtPeriodo->close();

        }
        $selectStmtPeriodo->close();


        switch($metodo_importacion){
            case "ColumnasArchivoCombinado":
                $selectQuery = "SELECT pt.id, pt.transaccion_proveedor_id, pt.transaccion_id,pi.proveedor_id
                    FROM tbl_conci_proveedor_transaccion pt
                    LEFT JOIN tbl_conci_proveedor_importacion pi 
                    ON pt.importacion_id = pi.id
                    WHERE pt.status = 1 AND pt.estado_conciliacion != 0 AND pt.importacion_id = ?;" ; 
                $stmt = $mysqli->prepare($selectQuery);
                if (!$stmt) fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
                $stmt->bind_param("i", $import_id);
                if (!$stmt->execute()) fn_conci_set_status_code_response(400, "Error al actualizar el registro: " . $mysqli->error, "Error");
                break;
            
                break;
            case "ColumnasArchivosIndependientes":
            
                $selectQuery = "SELECT pt.id, pt.transaccion_proveedor_id, pt.transaccion_id,pi.proveedor_id
                    FROM tbl_conci_proveedor_transaccion pt
                    LEFT JOIN tbl_conci_proveedor_importacion pi 
                    ON pt.importacion_liquidacion_id = pi.id
                    WHERE pt.status = 1 AND pt.estado_conciliacion != 0 AND pt.importacion_liquidacion_id = ?;" ; 
                $stmt = $mysqli->prepare($selectQuery);
                if (!$stmt) fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
                $stmt->bind_param("i", $import_id);
                if (!$stmt->execute()) fn_conci_set_status_code_response(400, "Error al actualizar el registro: " . $mysqli->error, "Error");
                break;

            default:
                console.log('Metodo no reconocido');
        }

        $stmt->bind_result($id, $transaccion_proveedor_id,$transaccion_id, $provider_id);

        //  Realizar conteo

        $data = [];
        $liquidados = 0;
        $no_coinciden = 0;
        $registros_duplicados = [];
        $msg_registros_duplicados = "";

        while ($stmt->fetch()) {
            $data[] = [
                'id' => $id,
                'transaccion_proveedor_id' => $transaccion_proveedor_id,
                'transaccion_id' => $transaccion_id,
                'provider_id' => $provider_id,
                'comision_total' => $comision_total
            ];
        }
        
        $stmt->close();

        foreach ($data as $item) {

             //  Proceso de conciliación 

            $r = fn_conci_periodo_provider_searchReconciled($mysqli,$item['id'],$item['transaccion_proveedor_id'], $item['transaccion_id'],$item['provider_id'],$item['comision_total'],$periodo_id);

            if ($r['status'] == "liquidado") {
                $liquidados++;
            } elseif ($r['status'] == "no_coinciden") {
                $no_coinciden++;
                $registros_duplicados[] = $r['registro'];
            }
        }

    //  Consultar datos de importación  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $selectQueryImport = "SELECT pi.liquidated_count, pi.non_matching_count
                                FROM tbl_conci_proveedor_importacion pi
                                WHERE pi.status = 1 AND pi.id = ?
                                LIMIT 1";

        $selectStmtImport = $mysqli->prepare($selectQueryImport);
        if (!$selectStmtImport) {
            fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
        }
        $selectStmtImport->bind_param("i", $import_id);
        if (!$selectStmtImport->execute()) {
            fn_conci_set_status_code_response(400, "Error al ejecutar la consulta SQL: " . $selectQueryCalimaco . " - " . $mysqli->error, "Error");
        }
        $selectStmtImport->store_result();

        if ($selectStmtImport->num_rows > 0) {
            $selectStmtImport->bind_result($liquidated_count, $duplicate_count);
            $selectStmtImport->fetch();
            $selectStmtImport->free_result(); 
            $selectStmtImport->close();

        }
        $selectStmtImport->close();

    //  Actualizar periodo   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

         $periodo_liquidated_count = $periodo_liquidated_count + $liquidados;
         $periodo_non_matching_count = $periodo_non_matching_count + $no_coinciden;
         
         $sql_update_periodo = "
             UPDATE tbl_conci_periodo SET liquidated_count = ?, non_matching_count = ? WHERE id = ?";
 
         $stmtUpdatePeriodo = $mysqli->prepare($sql_update_periodo);
         $stmtUpdatePeriodo->bind_param("iii", $periodo_liquidated_count, $periodo_non_matching_count, $periodo_id);
         if (!$stmtUpdatePeriodo->execute()) fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: ".$mysqli->error, "Error");
         $stmtUpdatePeriodo->close();

    //  Respuesta de guardado    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $liquidados = $liquidados + $liquidated_count;
        $no_coinciden = $no_coinciden + $non_matching_count;

        $msg_conciliados_swal = $liquidados == 1 ? "Liquidado:\n".$liquidados : "Liquidados:\n".$liquidados;
        $msg_duplicados_swal = $no_coinciden == 1 ? "No coinciden:\n".$no_coinciden : "No coinciden:\n".$no_coinciden;

        if ($no_coinciden > 0) {
            $msg_registros_duplicados .= "<strong>Liquidados incompletos:</strong><br>";

            foreach ($registros_duplicados as $registro) {
                $msg_registros_duplicados .= "Venta ID: ".$registro["transaccion_id"].", Duplicados proveedor: ".$registro["transaccion_proveedor_id"]."<br>";
            }
        }

    //  Actualizar registro de importación   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $sql_update_importacion = "
        UPDATE tbl_conci_proveedor_importacion SET liquidated_count = ?, non_matching_count = ?, user_updated_id = ?, updated_at = ? WHERE id = ?";

        $stmtUpdateImportacion = $mysqli->prepare($sql_update_importacion);
        $stmtUpdateImportacion->bind_param("iiisi", $liquidados,$no_coinciden, $login["id"],$datetime, $import_id);
        if (!$stmtUpdateImportacion->execute()) fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: ".$mysqli->error, "Error");
        $stmtUpdateImportacion->close();

    //  Mostrar reporte //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $response = [
        "msg_error" => "",
        "guardados" => 1,
        "swal_title" => "Liquidación exitosa",
        "msg" => "<strong>".$msg_conciliados_swal."<br>".$msg_duplicados_swal."</strong><br><br>".$msg_registros_duplicados,
        ];
        print_r(json_encode($response));
            
}

function fn_conci_periodo_provider_searchReconciled($mysqli, $id, $transaccion_proveedor_id, $transaccion_id, $provider_id, $comision_total,$periodo_id){
    global $return;
    
    $datetime = date("Y-m-d H:i:s");

    $selectQueryCalimaco = "SELECT ct.id, IFNULL(ct.venta_proveedor_id,''),IFNULL(pt.comision_total,0),IFNULL(pt.comision_total_calculado,0)
                            FROM tbl_conci_calimaco_transaccion ct
                            LEFT JOIN tbl_conci_calimaco_metodo cm ON ct.metodo_id = cm.id
                            LEFT JOIN tbl_conci_proveedor p ON cm.proveedor_id = p.id
                            LEFT JOIN tbl_conci_proveedor_transaccion pt
                                ON pt.id = (
                                    SELECT CAST(SUBSTRING_INDEX(ct.venta_proveedor_id, ',', 1) AS UNSIGNED)
                                )
                            WHERE ct.status = 1 AND ct.transaccion_id = ? AND p.id = ? AND ct.periodo_id = ? AND (ct.venta_proveedor_id IS NULL OR FIND_IN_SET(?, ct.venta_proveedor_id) = 1)
                            LIMIT 1";

    $selectStmt = $mysqli->prepare($selectQueryCalimaco);
    if (!$selectStmt) {
        fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
    }
    $selectStmt->bind_param("siis", $transaccion_id, $provider_id, $periodo_id, $id);
    if (!$selectStmt->execute()) {
        fn_conci_set_status_code_response(400, "Error al ejecutar la consulta SQL: " . $selectQueryCalimaco . " - " . $mysqli->error, "Error");
    }
    $selectStmt->store_result();

    if ($selectStmt->num_rows > 0) {
        $selectStmt->bind_result($calimaco_id, $venta_proveedor_ids, $proveedor_comision_total, $sistema_comision_total);
        $selectStmt->fetch();

        $selectStmt->free_result(); 
        $selectStmt->close();

        $sql_update_calimaco = "";
        if (round($proveedor_comision_total, 2) == round($sistema_comision_total, 2)) {
            $sql_update_calimaco = "UPDATE tbl_conci_calimaco_transaccion SET estado_liquidacion = 1, updated_at = ?, user_updated_id = ? WHERE id = ?";
        } else {
            $sql_update_calimaco = "UPDATE tbl_conci_calimaco_transaccion SET estado_liquidacion = 2, updated_at = ?, user_updated_id = ? WHERE id = ?";
        }

        $stmtConciliarCalimaco = $mysqli->prepare($sql_update_calimaco);
        if (!$stmtConciliarCalimaco) {
            fn_conci_set_status_code_response(400, "Error en la preparación de la consulta de actualización: " . $mysqli->error, "Error");
        }
        $stmtConciliarCalimaco->bind_param("sii", $datetime, $login["id"], $calimaco_id);
        if (!$stmtConciliarCalimaco->execute()) {
            fn_conci_set_status_code_response(400, "Error al ejecutar la consulta de actualización: " . $mysqli->error, "Error");
        }
        $stmtConciliarCalimaco->close();

        if (round($proveedor_comision_total, 2) == round($sistema_comision_total, 2)) {
            return ["status" => "liquidado"];
        } else {
            $registro = [
                "transaccion_id" => $transaccion_id,
                "transaccion_proveedor_id" => $transaccion_proveedor_id
            ];
            return ["status" => "no_coinciden", "registro" => $registro];
        }
    }
}
/*
function fn_conci_provider_periodo_save_data($data, $fields_provider, $file_columns, $provider_id, $tipo_archivo_id, $periodo_id) {
    global $mysqli;
    global $login;

    try {
        $mysqli->begin_transaction();

        // Declaración de variables
        $fecha_inicio = date("Y-m-d", strtotime($_POST['form_modal_sec_conci_periodo_liquidacion_importar_proveedor_fecha_inicio']));
        $fecha_fin = date("Y-m-d", strtotime($_POST['form_modal_sec_conci_liquidacion_periodo_importar_proveedor_fecha_fin']));
        $nombre_archivo = $_FILES["form_modal_sec_conci_liquidacion_periodo_importar_proveedor"]['name'];
        $datos = $data["data_detalle"];
        $tbl_conci_provider_state = [];
        $msg_error = [];
        $array_add_transaccion = [];
        $datetime = date("Y-m-d H:i:s");

        // Obtener datos de bd para validación

        // Obtener metodo de importación
        $selectQueryMetodoImportacion = "
            SELECT it.id, it.metodo, cc.nombre_bd
            FROM tbl_conci_proveedor cp
            LEFT JOIN tbl_conci_importacion_tipo it ON cp.tipo_importacion_id = it.id
            LEFT JOIN tbl_conci_calimaco_columna cc ON cp.columna_conciliacion_id = cc.id
            WHERE cp.id = ? AND cp.status = 1
            LIMIT 1";

        $selectStmtMetodoImportacion = $mysqli->prepare($selectQueryMetodoImportacion);
        $selectStmtMetodoImportacion->bind_param("i", $provider_id);
        $selectStmtMetodoImportacion->execute();
        $selectStmtMetodoImportacion->bind_result($id_importacion, $metodo_importacion, $columna_conciliacion_name);
        $selectStmtMetodoImportacion->fetch();
        $selectStmtMetodoImportacion->close();

        if (empty($metodo_importacion)) {
            throw new Exception("No se encontró el método de importación del proveedor.");
        }

        // Lista de estados
        $queryState = "SELECT id, nombre FROM tbl_conci_proveedor_estado WHERE status = 1 AND proveedor_id = ?";
        $stmtState = $mysqli->prepare($queryState);
        $stmtState->bind_param("i", $provider_id);
        $stmtState->execute();
        $result = $stmtState->get_result();

        while ($r = $result->fetch_assoc()) {
            $tbl_conci_provider_state[$r["nombre"]] = $r;
        }
        $stmtState->close();

        // Lista de columnas
        $columna_conciliacion_name = "id";
        list($column_provider_id_orden, $column_provider_id_nombre, $column_provider_id_prefijo, $column_provider_id_simbolo, $column_provider_id_sufijo, $column_provider_id_nombreColumna_json, $column_provider_id_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $columna_conciliacion_name);

        $calimaco_column_name_monto = "comision_total";
        list($column_provider_monto_orden, $column_provider_monto_nombre, $column_provider_monto_prefijo, $column_provider_monto_simbolo, $column_provider_monto_sufijo, $column_provider_monto_nombreColumna_json, $column_provider_monto_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_monto);

        $calimaco_column_name_id_externo = "id_externo";
        list($column_provider_id_externo_orden, $column_provider_id_externo_nombre, $column_provider_id_externo_prefijo, $column_provider_id_externo_simbolo, $column_provider_id_externo_sufijo, $column_provider_id_externo_nombreColumna_json, $column_provider_id_externo_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_id_externo);

        // Crear registro de importación
        $sql_insert_importacion = "
            INSERT INTO tbl_conci_proveedor_importacion (
                nombre_archivo, tipo_importacion_id, proveedor_id, periodo_id, tipo_archivo_id, fecha_inicio, fecha_fin, status, created_at, user_created_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)";

        $stmtInsertImportacion = $mysqli->prepare($sql_insert_importacion);
        $stmtInsertImportacion->bind_param("siiiisssi", $nombre_archivo, $id_importacion, $provider_id, $periodo_id, $tipo_archivo_id, $fecha_inicio, $fecha_fin, $datetime, $login["id"]);
        $stmtInsertImportacion->execute();
        $import_id = $stmtInsertImportacion->insert_id;
        $stmtInsertImportacion->close();

        // Procesar datos
        foreach ($datos as $item_detalle) {
            $registro_completo = [];
            foreach ($fields_provider as $field) {
                $column = $field['nombre'];
                $column_sin_puntos = str_replace('.', '', $column);
                $registro_completo[$column_sin_puntos] = $item_detalle[$column] ?? null;
            }
            $registro_completo_json = json_encode($registro_completo);

            $columnValueId = ($column_provider_id_formato == 3) ? json_decode($item_detalle[$column_provider_id_nombre], true)[$columnName] ?? null : $item_detalle[$column_provider_id_nombre];
            $columnValueMonto = $item_detalle[$column_provider_monto_nombre];

            $clean_transaction_id = str_replace([$column_provider_id_prefijo, $column_provider_id_simbolo, $column_provider_id_sufijo], ['', '.', ''], $columnValueId);
            $clean_monto = str_replace([$column_provider_monto_prefijo, $column_provider_monto_simbolo, $column_provider_monto_sufijo], ['', '.', ''], $columnValueMonto);

            $array_add_transaccion[] = [
                "transaccion_id" => $clean_transaction_id,
                "transaccion_proveedor_id" => $item_detalle[$column_provider_id_externo_nombre],
                "importacion_liquidacion_id" => $import_id,
                "data_json_liquidacion" => $registro_completo_json,
                "comision_total" => $clean_monto,
                "updated_at" => $datetime,
                "user_updated_id" => $login["id"]
            ];
        }

        // Optimizar y verificar existencia
        if (!empty($array_add_transaccion)) {
            $transaccion_ids = array_map(fn($item) => $item['transaccion_id'], $array_add_transaccion);
            $transaccion_proveedor_ids = array_map(fn($item) => $item['transaccion_proveedor_id'], $array_add_transaccion);

            if (!empty($transaccion_ids) && !empty($transaccion_proveedor_ids)) {
                $query_check = "
                    SELECT p.transaccion_id
                    FROM tbl_conci_proveedor_transaccion p
                    LEFT JOIN tbl_conci_proveedor_importacion i ON i.id = p.importacion_id
                    WHERE (p.transaccion_id IN (" . implode(',', array_fill(0, count($transaccion_ids), '?')) . ") OR p.transaccion_proveedor_id IN (" . implode(',', array_fill(0, count($transaccion_proveedor_ids), '?')) . "))
                    AND i.proveedor_id = ? AND p.periodo_id = ?";

                $stmtCheck = $mysqli->prepare($query_check);
                $types = str_repeat('s', count($transaccion_ids) + count($transaccion_proveedor_ids)) . 'ii';
                $params = array_merge($transaccion_ids, $transaccion_proveedor_ids, [$provider_id, $periodo_id]);
                $stmtCheck->bind_param($types, ...$params);
                $stmtCheck->execute();
                $result = $stmtCheck->get_result();
                $existing_transacciones = array_column($result->fetch_all(MYSQLI_ASSOC), 'transaccion_id');
                $stmtCheck->close();
            } else {
                $existing_transacciones = [];
            }
        }

        // Consultar datos de periodo
        $selectQueryPeriodo = "SELECT pi.comision_proveedor, pi.updated_count, pi.non_existent_count FROM tbl_conci_periodo pi WHERE pi.status = 1 AND pi.id = ? LIMIT 1";
        $selectStmtPeriodo = $mysqli->prepare($selectQueryPeriodo);
        $selectStmtPeriodo->bind_param("i", $periodo_id);
        $selectStmtPeriodo->execute();
        $selectStmtPeriodo->bind_result($comision_proveedor, $updated_count, $non_existent_count);
        $selectStmtPeriodo->fetch();
        $selectStmtPeriodo->close();

        // Insertar nuevas transacciones
        $query = "
            INSERT INTO tbl_conci_proveedor_transaccion (
                transaccion_id, transaccion_proveedor_id, importacion_id, periodo_id, data_json_liquidacion, comision_total, estado_id, status, user_created_id, created_at, user_updated_id, updated_at
            ) VALUES ";
        $values = [];
        $params = [];
        foreach ($array_add_transaccion as $transaccion) {
            if (!in_array($transaccion['transaccion_id'], $existing_transacciones)) {
                $values[] = "(?, ?, ?, ?, ?, ?, 1, 1, ?, ?, ?, ?)";
                $params = array_merge($params, [
                    $transaccion['transaccion_id'], $transaccion['transaccion_proveedor_id'], $import_id, $periodo_id, $transaccion['data_json_liquidacion'],
                    $transaccion['comision_total'], $login["id"], $datetime, $login["id"], $datetime
                ]);
            }
        }
        $query .= implode(',', $values);

        if (!empty($values)) {
            $stmt = $mysqli->prepare($query);
            $types = str_repeat('sssssssssss', count($values));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }

        // Actualizar datos de periodo
        $count_updated = $updated_count + count($existing_transacciones);
        $count_non_existent = $non_existent_count + (count($array_add_transaccion) - count($existing_transacciones));
        $comision_proveedor += array_sum(array_map(fn($transaccion) => $transaccion['comision_total'], $array_add_transaccion));


         //  Respuesta de guardado    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

         $msg_guardados_swal = $guardados == 1 ? "Guardado:\n".$guardados." registro" : "Guardados:\n".$guardados." registros";
         $msg_no_existe_swal = $no_existe == 1 ? "No existen:\n".$no_existe : "No existentes:\n".$no_existe;
         $msg_liquidados_swal = $liquidados == 1 ? "Actualizado:\n".$liquidados : "Actualizados:\n".$liquidados;

        $updateQueryPeriodo = "
            UPDATE tbl_conci_periodo
            SET updated_count = ?, non_existent_count = ?, comision_proveedor = ?, updated_at = ?, user_updated_id = ?
            WHERE id = ?";

        $updateStmtPeriodo = $mysqli->prepare($updateQueryPeriodo);
        $updateStmtPeriodo->bind_param("iidssi", $count_updated, $count_non_existent, $comision_proveedor, $datetime, $login["id"], $periodo_id);
        $updateStmtPeriodo->execute();
        $updateStmtPeriodo->close();

        $mysqli->commit();

        $response = [
            "msg_error" => "",
            "guardados" => $guardados,
            "swal_title" => "Importación exitosa",
            "msg" => "Archivo: " . $data["nombre_archivo"] . "<br> Procesado : " . count($datos) . " registros <br> ".$msg_guardados_swal."<br><br><strong>".$msg_no_existe_swal."<br>".$msg_liquidados_swal."</strong><br><br>".$$msg_registros_no_existe,
        ];
        
    } catch (Exception $e) {
        $mysqli->rollback();
        $response = [
            'error' => true,
            'msg' => 'No se ha realizado la importación. Error: ' . $e->getMessage(),
            'class' => 'text-danger'
        ];
    }

    return $response;
}
*/

function fn_conci_provider_periodo_save_data($data, $fields_provider, $file_columns ,$provider_id,$tipo_archivo_id, $periodo_id){
    global $mysqli;
    global $login;

    try {

    //  Declaración de variables /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        $fecha_inicio = date("Y-m-d", strtotime($_POST['form_modal_sec_conci_periodo_liquidacion_importar_proveedor_fecha_inicio']));
        $fecha_fin = date("Y-m-d", strtotime($_POST['form_modal_sec_conci_liquidacion_periodo_importar_proveedor_fecha_fin']));
        $nombre_archivo = $_FILES["form_modal_sec_conci_liquidacion_periodo_importar_proveedor"]['name'];
        $datos = $data["data_detalle"];
        $tbl_conci_provider_state = [];
        $msg_error = [];
        $row = 2;
        $array_add_transaccion = [];
        $datetime = date("Y-m-d H:i:s");

    //  Obtener datos de bd para validación //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //  Obtener metodo de importación

            $selectQueryMetodoImportacion = "SELECT 
                                it.id,it.metodo,cc.nombre_bd
                            FROM tbl_conci_proveedor cp
                            LEFT JOIN tbl_conci_importacion_tipo it
                            ON cp.tipo_importacion_id = it.id
                            LEFT JOIN tbl_conci_calimaco_columna cc
                            ON cp.columna_conciliacion_id = cc.id
                            WHERE cp.id = ? 
                            AND cp.status = 1
                            LIMIT 1";

            $selectStmtMetodoImportacion = $mysqli->prepare($selectQueryMetodoImportacion);
            $selectStmtMetodoImportacion->bind_param("i", $provider_id);

            if (!$selectStmtMetodoImportacion) {
                throw new Exception("Error consultar la importacion.");            
            }
            $selectStmtMetodoImportacion->execute();
            $selectStmtMetodoImportacion->store_result();

            if ($selectStmtMetodoImportacion->num_rows > 0) {
                $selectStmtMetodoImportacion->bind_result($id_importacion,$metodo_importacion,$columna_conciliacion_name);
                $selectStmtMetodoImportacion->fetch();
                if($metodo_importacion == ""){
                    throw new Exception("No se encontro el metodo de importación del proveedor.");            
                }
                $selectStmtMetodoImportacion->close();

            }else{
                throw new Exception("No se encontro el formato de calimaco.");            
            }

        //  Lista de estados

            $queryState= "SELECT id, nombre FROM tbl_conci_proveedor_estado WHERE status = 1 AND proveedor_id = ?";
            $stmtState = $mysqli->prepare($queryState);

            if ($stmtState) {
                $stmtState->bind_param("i", $provider_id);
                $stmtState->execute();
                $result = $stmtState->get_result();

                if (!$result) {
                    throw new Exception("Error en la consulta de estados del proveedor.");            
                } else {
                    $tbl_conci_provider_state = [];
                    while ($r = $result->fetch_assoc()) {
                        $tbl_conci_provider_state[$r["nombre"]] = $r;
                    }
                }
                $stmtState->close();
            } else {
                throw new Exception("Error en la preparación de la consulta.");            
            }

        //  Lista de columnas

            $columna_conciliacion_name = "id";
            list($column_provider_id_orden, $column_provider_id_nombre,$column_provider_id_prefijo,$column_provider_id_simbolo,$column_provider_id_sufijo, $column_provider_id_nombreColumna_json, $column_provider_id_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $columna_conciliacion_name);

            $calimaco_column_name_monto = "comision_total";
            list($column_provider_monto_orden, $column_provider_monto_nombre, $column_provider_monto_prefijo,$column_provider_monto_simbolo,$column_provider_monto_sufijo, $column_provider_monto_nombreColumna_json, $column_provider_monto_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_monto);

            $calimaco_column_name_id_externo = "id_externo";
            list($column_provider_id_externo_orden, $column_provider_id_externo_nombre, $column_provider_id_externo_prefijo,$column_provider_id_externo_simbolo,$column_provider_id_externo_sufijo, $column_provider_id_externo_nombreColumna_json, $column_provider_id_externo_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_id_externo);

        //  Crear registro de importación   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $sql_insert_importacion = "
                INSERT INTO tbl_conci_proveedor_importacion (
                        nombre_archivo,
                        tipo_importacion_id,
                        proveedor_id,
                        periodo_id,
                        tipo_archivo_id,
                        fecha_inicio,
                        fecha_fin,
                        status,
                        created_at,
                        user_created_id
                    )  
                    VALUES (?,?,?,?,?,?,?,1,?,?)";

            $stmtInsertImportacion = $mysqli->prepare($sql_insert_importacion);
            $stmtInsertImportacion->bind_param("siiiisssi", 
                                                            $nombre_archivo,
                                                            $id_importacion,
                                                            $provider_id,
                                                            $periodo_id,
                                                            $tipo_archivo_id,
                                                            $fecha_inicio,
                                                            $fecha_fin,                             
                                                            $datetime,
                                                            $login["id"]
                );

            if (!$stmtInsertImportacion->execute()) throw new Exception("No se creó el registro de importación del proveedor.");

            $import_id = $mysqli->insert_id;
            $stmtInsertImportacion->close();

        //  Deglosar data   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
        foreach ($datos as $item_detalle) {

            //print_r($datos);

            $registro_completo = [];
            
            //  Generar json de datos

                    foreach ($fields_provider as $field) {
                        $column = $field['nombre'];
                        $type = $field['formato'];
                        
                        $column_sin_puntos = str_replace('.', '', $column);

                        if (isset($item_detalle[$column])) {
                            $registro_completo[$column_sin_puntos] = $item_detalle[$column];
                        } else {
                            $registro_completo[$column_sin_puntos] = null;
                        }
                    }
                
                //print_r($registro_completo);

                $registro_completo_json = json_encode($registro_completo);

            //  Normalizacion de datos

                //  Extraer datos


                    if($column_provider_id_formato == 3){
                        $json_transaccion_id = json_decode($item_detalle[$column_provider_id_nombre], true);
                        if (isset($json_transaccion_id[$columnName])) {
                            $columnValueId = $json_transaccion_id[$columnName];
                        }else{
                            throw new Exception("El nombre de la columna json que contiene el id no existe o no es correcto.");
                        }

                    }else{
                        $columnValueId = $item_detalle[$column_provider_id_nombre];
                    }

                    $columnValueMonto = $item_detalle[$column_provider_monto_nombre];


                // Limpiar datos

                    //  ID

                    if (strpos($columnValueId, $column_provider_id_prefijo) == 0) {
                        $clean_transaction_id = substr($columnValueId, strlen($column_provider_id_prefijo));
                    } else {
                        $clean_transaction_id = $columnValueId;
                    }

                    $longitud_apt = strlen($column_provider_id_sufijo);
                    if (substr($columnValueId, -$longitud_apt) === $column_provider_id_sufijo) {
                        $clean_transaction_id = substr($columnValueId, 0, -$longitud_apt);
                    }

                    $clean_transaction_id = str_replace($column_provider_id_simbolo, ".", $clean_transaction_id);


                    //  MONTO

                    if (strpos($columnValueMonto, $column_provider_monto_prefijo) === 0) {
                        $clean_monto = substr($columnValueMonto, strlen($column_provider_monto_prefijo));
                    } else {
                        $clean_monto = $columnValueMonto;
                    }

                    $longitud_apt = strlen($column_provider_monto_sufijo);
                    if (substr($columnValueMonto, -$longitud_apt) === $column_provider_monto_sufijo) {
                        $clean_monto = substr($columnValueMonto, 0, -$longitud_apt);
                    }

                    $clean_monto = str_replace($column_provider_monto_simbolo, ".", $clean_monto);

                $add_transaccion_obj = [
                    "transaccion_id" => $clean_transaction_id,
                    "transaccion_proveedor_id" => $item_detalle[$column_provider_id_externo_nombre],
                    "importacion_liquidacion_id" => $import_id,
                    "data_json_liquidacion" => $registro_completo_json,
                    "comision_total" =>  $clean_monto,
                    "updated_at" => $datetime,
                    "user_updated_id" => $login["id"],
                ];
                $array_add_transaccion[] = $add_transaccion_obj;
                $row++;
        }
        if (count($msg_error) > 0) {
            $msg_error_swal = "<div style='text-align:left;max-height:230px;overflow:auto;padding-left:5px'>" . implode("<br>", $msg_error) . "</div>";
            fn_conci_set_status_code_response(400, $msg_error_swal, "No se proceso el archivo debido a los siguientes errores:");
        }

    // Optimizar y verificar existencia

        if (!empty($array_add_transaccion)) {
            $transaccion_ids = array_map(function($item) { return $item['transaccion_id']; }, $array_add_transaccion);
            $transaccion_proveedor_ids = array_map(function($item) { return $item['transaccion_proveedor_id']; }, $array_add_transaccion);

            if (!empty($transaccion_ids) && !empty($transaccion_proveedor_ids)) {
                $transaccion_ids_placeholder = implode(',', array_fill(0, count($transaccion_ids), '?'));
                $transaccion_proveedor_ids_placeholder = implode(',', array_fill(0, count($transaccion_proveedor_ids), '?'));

                $query_check = "SELECT p.transaccion_id FROM tbl_conci_proveedor_transaccion p LEFT JOIN tbl_conci_proveedor_importacion i ON i.id = p.importacion_id WHERE (p.transaccion_id IN ($transaccion_ids_placeholder) OR p.transaccion_proveedor_id IN ($transaccion_proveedor_ids_placeholder)) AND i.proveedor_id = ? AND p.periodo_id = ?";

                $stmtCheck = $mysqli->prepare($query_check);

                if ($stmtCheck === false) {
                    die('Error en la preparación de la consulta: ' . $mysqli->error);
                }

                $types = str_repeat('s', count($transaccion_ids)) . str_repeat('s', count($transaccion_proveedor_ids)) . 'ii';
                $params = array_merge($transaccion_ids, $transaccion_proveedor_ids, [$provider_id, $periodo_id]);

                $stmtCheck->bind_param($types, ...$params);

                $stmtCheck->execute();
                $result = $stmtCheck->get_result();
                $existing_transacciones = [];
                while ($r = $result->fetch_assoc()) {
                    $existing_transacciones[$r['transaccion_id']] = true;
                }
                $stmtCheck->close();
            } else {
                $existing_transacciones = [];
            }
        } else {
            $existing_transacciones = [];
        }

    //  Consultar datos de periodo

            $selectQueryPeriodo = "SELECT pi.comision_proveedor, pi.updated_count, pi.non_existent_count
                                    FROM tbl_conci_periodo pi
                                    WHERE pi.status = 1 AND pi.id = ?
                                    LIMIT 1";

            $selectStmtPeriodo = $mysqli->prepare($selectQueryPeriodo);
            if (!$selectStmtPeriodo) {
                throw new Exception("Error al consultar el período");            
            }
            $selectStmtPeriodo->bind_param("i", $periodo_id);
            if (!$selectStmtPeriodo->execute()) {
                throw new Exception("Error al ejecutar la consulta del período");            
            }
            $selectStmtPeriodo->store_result();

            if ($selectStmtPeriodo->num_rows > 0) {
                $selectStmtPeriodo->bind_result($comision_proveedor, $updated_count,$non_existent_count);
                $selectStmtPeriodo->fetch();
                $selectStmtPeriodo->free_result(); 
                $selectStmtPeriodo->close();

            }
            $selectStmtPeriodo->close();

        //  Realizar conteo

        $guardados = 0;
        $no_existe = 0;
        $liquidados = 0;
        $registros_no_existe = [];
        $msg_registros_no_existe = "";
        $monto_comision = "";
        $comision_total = 0;
        $suma_comision_proveedor= 0;


        foreach ($array_add_transaccion as $transaccion) {
            $exists = isset($existing_transacciones[$transaccion["transaccion_id"]]);

            if ($exists) {

                $query_update = "
                    UPDATE tbl_conci_proveedor_transaccion
                    SET 
                        importacion_liquidacion_id = ?,
                        comision_total = ?, 
                        data_json_liquidacion = ?,
                        updated_at = ?, 
                        user_updated_id = ?
                    WHERE transaccion_id = ? AND periodo_id = ?";
                $stmtUpdate = $mysqli->prepare($query_update);
                $stmtUpdate->bind_param("idssisi",
                    $transaccion["importacion_liquidacion_id"],
                    $transaccion["comision_total"],
                    $transaccion["data_json_liquidacion"],
                    $transaccion["updated_at"],
                    $transaccion["user_updated_id"],
                    $transaccion["transaccion_id"],
                    $periodo_id
                );

                if (!$stmtUpdate->execute()) throw new Exception("Error al actualizar el registro. Comuníquese con soporte.");
                $stmtUpdate->close();

                $liquidados++;
                $suma_comision_proveedor = $suma_comision_proveedor + $transaccion["comision_total"];
                $registros_actualizados[] = $transaccion;
            } else {
                $no_existe++;
            }
            $guardados++;
        }

    //  Respuesta de guardado    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $msg_guardados_swal = $guardados == 1 ? "Guardado:\n".$guardados." registro" : "Guardados:\n".$guardados." registros";
        $msg_no_existe_swal = $no_existe == 1 ? "No existen:\n".$no_existe : "No existentes:\n".$no_existe;
        $msg_liquidados_swal = $liquidados == 1 ? "Actualizado:\n".$liquidados : "Actualizados:\n".$liquidados;

    //  Actualizar registro de importación   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $sql_update_importacion = "
        UPDATE tbl_conci_proveedor_importacion SET non_existent_count = ?, updated_count = ? WHERE id = ?";

        $stmtUpdateImportacion = $mysqli->prepare($sql_update_importacion);
        $stmtUpdateImportacion->bind_param("iii", $no_existe,$liquidados, $import_id);
        if (!$stmtUpdateImportacion->execute()) throw new Exception("Error al consultar el período");
        $stmtUpdateImportacion->close();

    //  Actualizar periodo   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $comision_proveedor = $comision_proveedor + $suma_comision_proveedor;
        $updated_count = $updated_count + $liquidados;
        $non_existent_count = $non_existent_count + $no_existe;
        
        $sql_update_periodo = "
            UPDATE tbl_conci_periodo SET comision_proveedor = ?, updated_count = ?, non_existent_count = ? WHERE id = ?";

        $stmtUpdatePeriodo = $mysqli->prepare($sql_update_periodo);
        $stmtUpdatePeriodo->bind_param("diii", $comision_proveedor, $updated_count, $non_existent_count, $periodo_id);
        if (!$stmtUpdatePeriodo->execute()) throw new Exception("Error al guardar el período");
        $stmtUpdatePeriodo->close();

    //  Mostrar reporte
        $mysqli->commit();
        $response = [
            "msg_error" => "",
            "guardados" => $guardados,
            "swal_title" => "Importación exitosa",
            "msg" => "Archivo: " . $data["nombre_archivo"] . "<br> Procesado : " . count($datos) . " registros <br> ".$msg_guardados_swal."<br><br><strong>".$msg_no_existe_swal."<br>".$msg_liquidados_swal."</strong><br><br>".$$msg_registros_no_existe,
        ];
        return $response;

    } catch (Exception $e) {
        $mysqli->rollback();
        fn_conci_set_status_code_response(400, $e->getMessage(), "Error");
    }
}

function fn_conci_provider_periodo_upload_file($fields_provider, $file_columns, $provider_id, $tipo_archivo_id){
    try {
        global $mysqli;
        require_once '/var/www/html/phpexcel/classes/PHPExcel.php';

        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '10M');
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        set_time_limit(0);
        $data = array();

        gc_enable();
        gc_collect_cycles();

        $archivo_name="form_modal_sec_conci_liquidacion_periodo_importar_proveedor";

        //  Obtener formato de archivo  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $selectQueryFormatoArchivo = "SELECT 
                                af.nombre, af.linea_inicio,af.columna_inicio, ae.nombre,IFNULL(ase.simbolo, '')
                            FROM tbl_conci_archivo_formato af
                            LEFT JOIN tbl_conci_archivo_extension ae 
                            ON af.extension_id = ae.id
                            LEFT JOIN tbl_conci_archivo_separador ase 
                            ON af.separador_id = ase.id
                            WHERE af.proveedor_id = ? AND af.tipo_archivo_id = ?
                            AND af.status = 1
                            LIMIT 1";

            $selectStmtFormato = $mysqli->prepare($selectQueryFormatoArchivo);
            $selectStmtFormato->bind_param("is", $provider_id,$tipo_archivo_id);

            if (!$selectStmtFormato) {
                fn_conci_set_status_code_response(500, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
            }

            $selectStmtFormato->execute();
            $selectStmtFormato->store_result();

            if ($selectStmtFormato->num_rows > 0) {
                $selectStmtFormato->bind_result($param_name,$start_line,$columna_inicio, $extension,$separator);
                $selectStmtFormato->fetch();
                if($extension == "csv" && $separator == ""){
                    fn_conci_set_status_code_response(400, "No se encontro el separador para el formato csv del archivo de calimaco", "Error");
                }
            }else{
                fn_conci_set_status_code_response(400, "No se encontro el formato de calimaco", "Error");
            }

        //  Validación de archivo   ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            if (isset($_FILES[$archivo_name]) && $_FILES[$archivo_name]['name'] == "") {
                fn_conci_set_status_code_response(400, "Ingresar Archivo excel", "Error");
            }

            $ext = pathinfo($_FILES[$archivo_name]['name'], PATHINFO_EXTENSION);
            $nombre_archivo = $_FILES[$archivo_name]['name'];

            if (pathinfo($_FILES[$archivo_name]['name'], PATHINFO_EXTENSION) != $extension) {
                $error = 'Extensión de archivo incorrecta: '.pathinfo($_FILES[$archivo_name]['name'], PATHINFO_EXTENSION).'<br> Extensión de archivo correcta: '. $extension;
                fn_conci_set_status_code_response(400, $error, "Error");
            }

            if (strpos($_FILES[$archivo_name]['name'], $param_name) === false) {
                fn_conci_set_status_code_response(400, "El nombre del archivo debe contener '".$param_name."'", "Nombre de archivo incorrecto");
            }

            if (strlen($_FILES[$archivo_name]['name']) > 250) {
                fn_conci_set_status_code_response(400, "El nombre del archivo contiene mas de 250 caracteres", "Nombre de archivo  incorrecto");
            }  

        //  Cargar el archivo   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $tmpfname = $_FILES[$archivo_name]['tmp_name'];

            if (!file_exists($tmpfname)) {
                fn_conci_set_status_code_response(400, "El archivo supera el tamaño maximo. No debe superar los 1.9MB o tener mas de 20 000 lineas totales", "Error de archivo");
                exit;
            }

            if ($ext === 'csv') {
                $csvData = array_map(function($line) use ($separator) {
                    return str_getcsv($line, $separator);
                }, file($tmpfname));

                $excelObj = new PHPExcel();
                $worksheet = $excelObj->getActiveSheet();

                foreach ($csvData as $rowIndex => $row) {
                    foreach ($row as $colIndex => $cell) {
                        $worksheet->setCellValueByColumnAndRow($colIndex, $rowIndex + 1, $cell);
                    }
                }
            } else {
                $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
                libxml_use_internal_errors(true);

                try {
                    $excelObj = $excelReader->load($tmpfname);
                } catch (PHPExcel_Reader_Exception $e) {
                    set_status_code_response(400, "Error al cargar el archivo: " . $e->getMessage(), "Error de archivo");
                    exit;
                }

                $worksheet = $excelObj->getSheet();
            }
                        

        //  Verficiar columna de inicio

            $worksheet = $excelObj->getActiveSheet();

            if($columna_inicio > 1){
                // Eliminar las columnas especificadas
                for ($i = 0; $i < $columna_inicio-1; $i++) {
                    // Siempre eliminamos la primera columna (índice 0) en cada iteración
                    $worksheet->removeColumnByIndex(0);
                }
            }

        // Verificar si hay fórmulas en el archivo
            if (checkForFormulas($worksheet)) {
                fn_conci_set_status_code_response(400, "El archivo contiene fórmulas, lo cual no está permitido. <br>Debe modificar el tipo de importación en el la ventana de mantenimiento del proveedor para poder subirlo como archivo csv", "Error de archivo");
                exit;
            }
            
        //  Validar nombre de columnas  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $worksheet = $excelObj->getSheet();

            $check_archivo = fn_conci_check_headers($worksheet, $fields_provider, $file_columns, $start_line);

            if ($check_archivo !== true) {
                fn_conci_set_status_code_response(400, $check_archivo, "El archivo no se proceso debido a los siguientes errores");
            }

        //  Obtener registros   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $headerRow = $start_line;
            $firstRow = $headerRow + 1;

            $data = array();
            $data = fn_conci_get_data_detail($worksheet, $fields_provider, $firstRow, 0, $file_columns);
            $data["nombre_archivo"] = $nombre_archivo;
            $excelObj->disconnectWorksheets();
            $excelObj->garbageCollect();
            unset($excelObj);
            return $data;

    } catch (Exception $ex) {
        fn_conci_set_status_code_response(500, $ex->getMessage(), "Error");
    }
}

function fn_conci_provider_periodo_searchDuplicated($transaccion_id, $transaccion_proveedor_id, $provider_id, $periodo_id){
    global $mysqli;
    global $login;
    $datetime = date("Y-m-d H:i:s");

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_transaccion pt
                                LEFT JOIN tbl_conci_proveedor_importacion pi ON pt.importacion_id = pi.id
                                WHERE pt.status = 1 AND pt.transaccion_proveedor_id = ? AND pi.proveedor_id= ? AND pt.periodo_id = ?");
    
    if ($stmt) {
        $stmt->bind_param("sii", $transaccion_proveedor_id,  $provider_id, $periodo_id);
        
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            return $count > 0;
            
        } else {
            fn_conci_set_status_code_response(400, "Error en la ejecución de la consulta: " . $mysqli->error, "Error");
            exit();
        }
    } else {
        fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: " . $mysqli->error, "Error");
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_liquidacion_importacion_proveedor_editar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
        $fecha_inicio = date("Y-m-d", strtotime($_POST['fecha_inicio']));
        $fecha_fin = date("Y-m-d", strtotime($_POST['fecha_fin']));

        if ((int)$id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_razon_social = "
					UPDATE tbl_conci_proveedor_importacion
					SET 
                        fecha_inicio = ?,
                        fecha_fin = ?,         
                        updated_at = ?, 
                        user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_razon_social);


            if ($stmt) {
                $stmt->bind_param("sssii", 
                                            $fecha_inicio,
                                            $fecha_fin,
                                            $fecha,
                                            $usuario_id,
                                            $id
                                            );

                try {
                    $stmt->execute();
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }

		}else{
            $result["http_code"] = 400;
            $result["error"] = "NO se encontro el registro de importación";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

?>