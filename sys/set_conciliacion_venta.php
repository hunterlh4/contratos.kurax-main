<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");
include("/var/www/html/sys/helpers.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_guardar") {
    //global $login, $mysqli;
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
		$proveedor_id = $_POST["proveedor_id"];
        $periodo = $_POST["periodo"];
		$periodo = date("Y-m-d", strtotime($periodo));

        if ((int)$id == 0) {

            //INICIO: VALIDAR SI EXISTE REGISTROS EN EL MISMO MES QUE QUEREMOS REGISTRAR

            $anio = date("Y", strtotime($periodo));
            $mes = date("m", strtotime($periodo));

            // Preparar la consulta para evitar inyecciones SQL
            $query = "
                SELECT 
                    sp.id
                FROM tbl_conci_periodo AS sp
                WHERE sp.status = 1 
                    AND sp.proveedor_id = ? 
                    AND month(sp.periodo) = ? 
                    AND year(sp.periodo) = ?
            ";

            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("iii", $proveedor_id, $mes, $anio);

                $stmt->execute();

                $result_query = $stmt->get_result();

                $recibos = $result_query->num_rows;

                $stmt->close();
            }

            if ($recibos > 0) {
                $result["http_code"] = 400;
                $result["status"] = "Error";
                $result["error"] = "Ya se encuentra registrado el periodo ingresado. Ingrese otro periodo";
                
                echo json_encode($result);
                exit();
            }
	
			//FIN: VALIDAR SI EXISTE REGISTROS EN EL MISMO MES QUE QUEREMOS REGISTRAR

            $error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_periodo = "
                INSERT INTO tbl_conci_periodo (
                    proveedor_id,
                    periodo,
                    status,    
                    created_at,
                    user_created_id
                )  
                VALUES (?,?,1,?,?)
            ";

            $stmtInsert = $mysqli->prepare($sql_insert_periodo);


            if ($stmtInsert) {
                $stmtInsert->bind_param("issi", 
                                        $proveedor_id,
                                        $periodo,                        
                                        $fecha,
                                        $usuario_id
                                    );
                if($stmtInsert->execute()){
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } else{
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " .  $mysqli->error;
                }

                $stmtInsert->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }
        }else{
            $result["http_code"] = 400;
            $result["error"] = "No se puede editar el periodo. Recargue la pagina";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_editar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = [];

    if ((int)$usuario_id > 0) {
        $id = (int)$_POST["id"];
        $proveedor_id = $_POST["proveedor_id"];
        $periodo = $_POST["periodo"];
        $periodo = date("Y-m-d", strtotime($periodo));

        if ($id > 0) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            // INICIO: VALIDAR SI EXISTE REGISTROS EN EL MISMO MES QUE QUEREMOS REGISTRAR
            $anio = date("Y", strtotime($periodo));
            $mes = date("m", strtotime($periodo));

            $query = "
                SELECT 
                    sp.id
                FROM tbl_conci_periodo AS sp
                WHERE sp.status = 1 
                    AND sp.proveedor_id = ? 
                    AND sp.id <> ? 
                    AND month(sp.periodo) = ? 
                    AND year(sp.periodo) = ?
            ";

            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("iiii", $proveedor_id, $id, $mes, $anio);

                if ($stmt->execute()) {
                    $resultStmt = $stmt->get_result();
                    $recibos = $resultStmt->num_rows;
                    $stmt->close();

                    if ($recibos > 0) {
                        $result["http_code"] = 400;
                        $result["status"] = "Error";
                        $result["error"] = "Ya se encuentra registrado el periodo ingresado. Ingrese otro periodo";
                        echo json_encode($result);
                        exit();
                    } else {
                        $sql_update_periodo = "
                            UPDATE tbl_conci_periodo
                            SET 
                                periodo = ?,         
                                updated_at = ?, 
                                user_updated_id = ?
                            WHERE id = ?
                        ";

                        if ($stmtPeriodo = $mysqli->prepare($sql_update_periodo)) {
                            $stmtPeriodo->bind_param("ssii", $periodo, $fecha, $usuario_id, $id);

                            try {
                                $stmtPeriodo->execute();
                                $result["http_code"] = 200;
                                $result["status"] = "Datos obtenidos de gestión.";
                                echo json_encode($result);
                                exit();
                            } catch (Exception $e) {
                                $result["http_code"] = 400;
                                $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                                echo json_encode($result);
                                exit();
                            }

                            $stmtPeriodo->close();
                        } else {
                            $result["http_code"] = 400;
                            $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
                            echo json_encode($result);
                            exit();
                        }
                    }
                } else {
                    $result["http_code"] = 500;
                    $result["status"] = "Error";
                    $result["error"] = "Error al ejecutar la consulta: " . $mysqli->error;
                    echo json_encode($result);
                    exit();
                }
            } else {
                $result["http_code"] = 500;
                $result["status"] = "Error";
                $result["error"] = "Error al preparar la consulta: " . $mysqli->error;
                echo json_encode($result);
                exit();
            }
            // FIN: VALIDAR SI EXISTE REGISTROS EN EL MISMO MES QUE QUEREMOS REGISTRAR
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe el registro seleccionado. Recargue la página.";
            echo json_encode($result);
            exit();
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_importar_calimaco") {

    $periodo_id = $_POST['form_modal_sec_conci_venta_periodo_editar_param_id'];
    $datos = json_decode($_POST['datos'], true);

    $fields_provider = [
            "ID" => "varchar",
            "Fecha" => "varchar",
            "Estado" => "varchar",
            "Fecha de modificación" => "varchar",
            "Usuario" => "varchar",
            "email" => "varchar",
            "Cantidad" => "decimal",
            "ID externo" => "varchar",
            "Método" => "varchar",
            "Respuesta" => "varchar",
            "Agente" => "varchar",
            "Fecha de registro del jugador" => "varchar",
        ];
    $file_columns = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L"];
                
    $data = fn_conci_periodo_upload_file_calimaco($fields_provider, $file_columns);

    if (count($data["data_detalle"]) == 0) {
        fn_conci_set_status_code_response(400, "No hay registros en el archivo.".$linea, "El archivo no coincide debido a los siguientes errores:");
        }

    $response = fn_conci_periodo_save_data_calimaco($data, $fields_provider, $file_columns, $periodo_id);
    print_r(json_encode($response));
}

function fn_conci_periodo_upload_file_calimaco($fields_provider, $file_columns){

    try {
        global $mysqli;
        error_reporting(0);

        require_once '/var/www/html/phpexcel/classes/PHPExcel.php';

        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '10M');
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1G');
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        set_time_limit(0);
        $data = array();

        gc_enable();
        gc_collect_cycles();
        $archivo_name = "form_modal_sec_conci_venta_periodo_importar_calimaco";

        // Obtener formato de archivo
        $calimaco_id = 1;
        $selectQueryFormatoArchivo = "SELECT 
                            af.nombre, af.linea_inicio, ae.nombre, IFNULL(ase.simbolo, '')
                        FROM tbl_conci_archivo_formato af
                        LEFT JOIN tbl_conci_archivo_extension ae 
                        ON af.extension_id = ae.id
                        LEFT JOIN tbl_conci_archivo_separador ase 
                        ON af.separador_id = ase.id
                        WHERE af.status = 1 AND af.id = ? AND af.tipo_archivo_id = 4
                        LIMIT 1";

        $selectStmtFormato = $mysqli->prepare($selectQueryFormatoArchivo);
        $selectStmtFormato->bind_param("i", $calimaco_id);
        $selectStmtFormato->execute();
        $selectStmtFormato->store_result();

        if ($selectStmtFormato->num_rows > 0) {
            $selectStmtFormato->bind_result($param_name, $start_line, $extension, $separator);
            $selectStmtFormato->fetch();
            $selectStmtFormato->close();
            if ($extension == "csv" && $separator == "") {
                throw new Exception("No se encontró el separador para el formato csv del archivo de calimaco");
            }
        } else {
            throw new Exception("No se encontró el formato de calimaco");
        }

        // Validación de archivo
        if (isset($_FILES[$archivo_name]) && $_FILES[$archivo_name]['name'] == "") {
            throw new Exception("Ingresar Archivo excel");
        }

        $ext = pathinfo($_FILES[$archivo_name]['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $_FILES[$archivo_name]['name'];
        $max_file_size = 2.1 * 1024 * 1024;
        $max_file_lines = 24000;

        if ($ext != $extension) {
            $error = 'Extensión de archivo incorrecta: ' . $ext . '<br> Extensión de archivo correcta: ' . $extension;
            fn_conci_set_status_code_response(400, $error, null);
        }

        if (strpos($nombre_archivo, $param_name) === false) {
            fn_conci_set_status_code_response(400, "El nombre del archivo debe contener '".$param_name."'", "Nombre de archivo incorrecto");
        }

        if (strlen($nombre_archivo) > 250) {
            fn_conci_set_status_code_response(400, "El nombre del archivo contiene más de 250 caracteres", "Nombre de archivo incorrecto");
        }

        // Cargar el archivo
        $file_content = file_get_contents($_FILES[$archivo_name]['tmp_name']);

        if ($ext === 'csv') {

            $csvData = array_map(function($line) use ($separator) {
                return str_getcsv($line, $separator);
            }, explode(PHP_EOL, $file_content));

            $excelObj = new PHPExcel();
            $worksheet = $excelObj->getActiveSheet();

            foreach ($csvData as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    $worksheet->setCellValueByColumnAndRow($colIndex, $rowIndex + 1, $cell);
                }
            }
            
        } else {
            $excelReader = PHPExcel_IOFactory::createReader($extension);
            libxml_use_internal_errors(true);

            try {
                $excelObj = $excelReader->loadFromString($file_content);
            } catch (PHPExcel_Reader_Exception $e) {
                fn_conci_set_status_code_response(400, "Error al cargar el archivo: " . $e->getMessage(), "Error de archivo");
                exit;
            }

            $worksheet = $excelObj->getSheet();
        }

        // Verificar columna de inicio
        if ($columna_inicio > 1) {
            for ($i = 0; $i < $columna_inicio - 1; $i++) {
                $worksheet->removeColumnByIndex(0);
            }
        }

        // Validar nombre de columnas
        $check_archivo = fn_conci_check_headers($worksheet, $fields_provider, $file_columns, $start_line);

        if ($check_archivo !== true) {
            fn_conci_set_status_code_response(400, $check_archivo, "El archivo no se procesó debido a los siguientes errores");
        }

        // Obtener registros
        $headerRow = $start_line;
        $firstRow = $headerRow + 1;

        $data = fn_conci_get_data_detail($worksheet, $fields_provider, $firstRow, 0, $file_columns);
        $data["nombre_archivo"] = $nombre_archivo;
        $excelObj->disconnectWorksheets();
        $excelObj->garbageCollect();
        unset($excelObj);
        gc_collect_cycles();
        return $data;

    } catch (PHPExcel_Reader_Exception $ex) {
        fn_conci_set_status_code_response(500, $ex->getMessage(), "Error");
    } catch (Exception $e) {
        fn_conci_set_status_code_response(500, $e->getMessage(), "Error General");
    }
}

function fn_conci_periodo_save_data_calimaco($data, $fields_provider, $file_columns, $periodo_id) {
    global $mysqli, $login;

    try {
        $mysqli->begin_transaction();
        $datetime = date("Y-m-d H:i:s");
        $fecha_inicio = date("Y-m-d", strtotime($_POST['form_modal_sec_conci_periodo_venta_importar_calimaco_fecha_inicio']));
        $fecha_fin = date("Y-m-d", strtotime($_POST['form_modal_sec_conci_venta_periodo_importar_calimaco_fecha_fin']));
        $nombre_archivo = $_FILES["form_modal_sec_conci_venta_periodo_importar_calimaco"]['name'];
        $datos = $data["data_detalle"];
        $tbl_conci_calimaco_metodo_nombre = [];
        $tbl_conci_calimaco_state = [];
        $tbl_conci_calimaco_metodo = [];
        $array_add_transaccion = [];
        $msg_error = [];
        $row = 2;

        // Obtener datos de bd para validación
        $query = "SELECT id, nombre FROM tbl_conci_calimaco_estado WHERE status = 1";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Error en la consulta: ".$mysqli->error);
            } else {
                while ($r = $result->fetch_assoc()) {
                    $tbl_conci_calimaco_state[$r["nombre"]] = $r;
                }
            }
            $stmt->close();
        } else {
            throw new Exception("Error en la preparación de la consulta");
        }

        $query = "SELECT m.id, m.nombre, IFNULL(m.proveedor_id,0) AS proveedor_id, IFNULL(t.metodo,'') AS metodo_formula
                  FROM tbl_conci_calimaco_metodo m
                  LEFT JOIN tbl_conci_proveedor p ON p.id= m.proveedor_id
                  LEFT JOIN tbl_conci_formula_tipo t ON t.id = p.tipo_formula_id 
                  WHERE m.status = 1";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Error en la consulta: ".$mysqli->error);
            } else {
                while ($r = $result->fetch_assoc()) {
                    $tbl_conci_calimaco_metodo[$r["nombre"]] = $r;
                    $tbl_conci_calimaco_metodo_nombre[$r['id']] = $r;
                }
            }
            $stmt->close();
        } else {
            throw new Exception("Error en la preparación de la consulta: ".$mysqli->error);
        }

        // Crear registro de importación
            $sql_insert_importacion = "
                INSERT INTO tbl_conci_calimaco_importacion (
                    nombre_archivo,
                    periodo_id,
                    tipo_archivo_id,
                    fecha_inicio,
                    fecha_fin,
                    status,
                    created_at,
                    user_created_id
                ) VALUES (?, ?, 4, ?, ?, 1, ?, ?)";
            $stmtInsertImportacion = $mysqli->prepare($sql_insert_importacion);
            $stmtInsertImportacion->bind_param("sisssi", $nombre_archivo, $periodo_id, $fecha_inicio, $fecha_fin, $datetime, $login["id"]);
            if (!$stmtInsertImportacion->execute()) throw new Exception("Error al guardar la importación. Comuníquese con soporte.");
            $import_id = $mysqli->insert_id;
            $stmtInsertImportacion->close();

        // Validación de datos
            foreach ($datos as $item_detalle) {
                $estado = $item_detalle["Estado"];
                $metodo = $item_detalle["Método"];
                $fecha_modificacion = $item_detalle["Fecha de modificación"];
                $fecha = $item_detalle["Fecha"];
                $fecha_registro = $item_detalle["Fecha de registro del jugador"];

                if ($estado == "") {
                    $msg_error[] = $file_columns[0] . $row . " - Estado no encontrado";
                    $row++;
                    continue;
                }
                if (!isset($tbl_conci_calimaco_state[$estado])) {
                    $msg_error[] = $file_columns[0] . $row . " - Tipo de estado '" . $estado . "' no encontrado";
                    $row++;
                    continue;
                }

                if ($metodo == "") {
                    $msg_error[] = $file_columns[9] . $row . " - Metodo no encontrado";
                    $row++;
                    continue;
                }
                if (!isset($tbl_conci_calimaco_metodo[$metodo])) {
                    $msg_error[] = $file_columns[9] . $row . " - Metodo '" . $metodo . "' no encontrado";
                    $row++;
                    continue;
                }

                $transaction_id = str_pad($item_detalle["ID"], 11, '0', STR_PAD_RIGHT);

                $add_transaccion_obj = [
                    "transaccion_id" => $transaction_id,
                    "periodo_id" => $periodo_id,
                    "importacion_id" => $import_id,
                    "metodo_id" => $tbl_conci_calimaco_metodo[$metodo]["id"],
                    "fecha" => $fecha,
                    "estado_id" => $tbl_conci_calimaco_state[$estado]["id"],
                    "estado_liquidacion" => 0,
                    "estado_conciliacion" => 0,
                    "fecha_modificacion" => $fecha_modificacion,
                    "usuario" => $item_detalle["Usuario"],
                    "email" => $item_detalle["email"],
                    "cantidad" => $item_detalle["Cantidad"],
                    "id_externo" => $item_detalle["ID externo"],
                    "respuesta" => $item_detalle["Respuesta"],
                    "agente" => $item_detalle["Agente"],
                    "fecha_registro_jugador" => $fecha_registro,
                    "status" => 1,
                    "created_at" => $datetime,
                    "updated_at" => $datetime,
                    "user_created_id" => $login["id"],
                    "user_updated_id" => $login["id"],
                ];

                $array_add_transaccion[] = $add_transaccion_obj;
                $row++;
            }

            if (count($msg_error) > 0) {
                $msg_error_swal = "<div style='text-align:left;max-height:230px;overflow:auto;padding-left:5px'>" . implode("<br>", $msg_error) . "</div>";
                fn_conci_set_status_code_response(400, $msg_error_swal, "No se procesó el archivo debido a los siguientes errores:");
            }

        // Optimizar y verificar existencia

            $transaccion_ids = array_map(function($item) { return $item['transaccion_id']; }, $array_add_transaccion);
            $transaccion_ids_placeholder = implode(',', array_fill(0, count($transaccion_ids), '?'));

            $query_check = "SELECT transaccion_id FROM tbl_conci_calimaco_transaccion WHERE transaccion_id IN ($transaccion_ids_placeholder) AND periodo_id = ?";
            $stmtCheck = $mysqli->prepare($query_check);
            $types = str_repeat('s', count($transaccion_ids)) . 'i';
            $stmtCheck->bind_param($types, ...array_merge($transaccion_ids, [$periodo_id]));
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            $existing_transacciones = [];
            while ($r = $result->fetch_assoc()) {
                $existing_transacciones[$r['transaccion_id']] = true;
            }
            $stmtCheck->close();

    //  Consultar datos de periodo
        $selectQueryPeriodo = "SELECT pi.monto_calimaco, pi.calimaco_count
                                FROM tbl_conci_periodo pi
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
            $selectStmtPeriodo->bind_result($monto_calimaco, $calimaco_count);
            $selectStmtPeriodo->fetch();
            $selectStmtPeriodo->free_result(); 
            $selectStmtPeriodo->close();
            }
        $selectStmtPeriodo->close();
        
        //   Relizar conteo
        $insertados = 0;
        $suma_insertados = 0;
        $actualizados = 0;
        $registros_actualizados = [];
        $registros_nuevos = [];
        foreach ($array_add_transaccion as $transaccion) {
            $exists = isset($existing_transacciones[$transaccion["transaccion_id"]]);

            if ($exists) {
                // Update existing record
                $query_update = "
                    UPDATE tbl_conci_calimaco_transaccion
                    SET 
                        periodo_id = ?, 
                        metodo_id = ?, 
                        fecha = ?, 
                        estado_id = ?, 
                        fecha_modificacion = ?,
                        estado_conciliacion = ?,
                        estado_liquidacion = ?,
                        usuario = ?, 
                        email = ?, 
                        cantidad = ?, 
                        id_externo = ?, 
                        respuesta = ?, 
                        agente = ?, 
                        fecha_registro_jugador = ?, 
                        status = ?, 
                        updated_at = ?, 
                        user_updated_id = ?
                    WHERE transaccion_id = ? AND periodo_id = ?";
                $stmtUpdate = $mysqli->prepare($query_update);
                $stmtUpdate->bind_param("iisisiissdssssisisi",
                    $transaccion["periodo_id"],
                    $transaccion["metodo_id"],
                    $transaccion["fecha"],
                    $transaccion["estado_id"],
                    $transaccion["fecha_modificacion"],
                    $transaccion["estado_conciliacion"],
                    $transaccion["estado_liquidacion"],
                    $transaccion["usuario"],
                    $transaccion["email"],
                    $transaccion["cantidad"],
                    $transaccion["id_externo"],
                    $transaccion["respuesta"],
                    $transaccion["agente"],
                    $transaccion["fecha_registro_jugador"],
                    $transaccion["status"],
                    $transaccion["updated_at"],
                    $transaccion["user_updated_id"],
                    $transaccion["transaccion_id"],
                    $periodo_id
                );

                if (!$stmtUpdate->execute()) throw new Exception("Error al actualizar el registro. Comuníquese con soporte.");
                $actualizados++;
                $registros_actualizados[] = $transaccion;
                $stmtUpdate->close();
            } else {
                // Insert new record
                $registros_nuevos[] = $transaccion;
                $suma_insertados = $suma_insertados + $transaccion["cantidad"];
                $insertados++;
            }
        }

        if (count($registros_nuevos) > 0) {
            // Guardar registros nuevos en lotes
            $batch_size = 1000;
            $batches = array_chunk($registros_nuevos, $batch_size);
            foreach ($batches as $batch) {
                fn_conci_insert_batch($batch);
            }
        }

        // Obtener reporte de importación
        $msg_guardados_swal = $guardados == 1 ? "Guardado:\n".$guardados." registro" : "Guardados:\n".$guardados." registros";
        $msg_registrados_swal = $insertados == 1 ? "Creado:\n".$insertados : "Creados:\n".$insertados;
        $msg_actualizados_swal = $actualizados == 1 ? "Actualizado:\n".$actualizados : "Actualizados:\n".$actualizados;

        if ($actualizados > 0) {
            $msg_registros_actualizados = "<strong>Registros actualizados:</strong><br>";
            foreach ($registros_actualizados as $registro) {
                $metodo_id = str_replace(["'", '"'], "", $registro['metodo_id']);
                $nombre_metodo = isset($metodo_id) ? $tbl_conci_calimaco_metodo_nombre[$metodo_id]['nombre'] : 'Desconocido';
                $msg_registros_actualizados .= "Venta ID: " . $registro['transaccion_id'] . ", Metodo: " . $nombre_metodo . "<br>";
            }
        }

        //  Actualizar periodo   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            $monto_calimaco = $monto_calimaco + $suma_insertados;
            $calimaco_count = $calimaco_count + $insertados;
            
            $sql_update_periodo = "
                UPDATE tbl_conci_periodo SET calimaco_count = ?, monto_calimaco = ? WHERE id = ?";
            $stmtUpdatePeriodo = $mysqli->prepare($sql_update_periodo);
            $stmtUpdatePeriodo->bind_param("idi", $calimaco_count, $monto_calimaco, $periodo_id);
            if (!$stmtUpdatePeriodo->execute()) fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: ".$mysqli->error, "Error");
            $stmtUpdatePeriodo->close();


        // Actualizar registro de importación
            $sql_update_importacion = "
                UPDATE tbl_conci_calimaco_importacion 
                SET created_count = ?, updated_count = ?
                WHERE id = ?";
            $stmtUpdateImportacion = $mysqli->prepare($sql_update_importacion);
            $stmtUpdateImportacion->bind_param("iii", $insertados, $actualizados, $import_id);
            if (!$stmtUpdateImportacion->execute()) throw new Exception("Error al actualizar el registro de importación.");
            $stmtUpdateImportacion->close();

        //  Retornar respuesta

            $mysqli->commit();
            $response = [
                "msg_error" => "",
                "guardados" => $guardados,
                "swal_title" => "Importación exitosa",
                "msg" => "Archivo: " . $data["nombre_archivo"] . "<br> Procesado : " . count($datos) . " registros <br> ".$msg_guardados_swal."<br><br><strong>".$msg_registrados_swal."<br>".$msg_actualizados_swal."</strong><br><br>".$msg_registros_actualizados,
            ];
            return $response;

    } catch (Exception $e) {
        $mysqli->rollback();
        fn_conci_set_status_code_response(400,$e->getMessage(), "Error");
    }
}

function fn_conci_insert_batch($batch_data) {
    global $mysqli;

    $base_sql = "
        INSERT INTO tbl_conci_calimaco_transaccion (
            importacion_id,
            periodo_id,
            estado_conciliacion,
            estado_liquidacion,     
            observacion,
            transaccion_id,
            metodo_id,
            fecha,
            estado_id,
            fecha_modificacion,
            usuario,
            email,
            cantidad,
            id_externo,
            respuesta,
            agente,
            fecha_registro_jugador,
            status,
            created_at,
            user_created_id,
            updated_at,
            user_updated_id
        ) VALUES ";

    $values = [];
    $types = "";
    $params = [];

    foreach ($batch_data as $data) {
        $values[] = "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $types .= "iiiissisisssdssssisisi";
        
        $params = array_merge($params, [
            $data["importacion_id"],
            $data["periodo_id"],
            $data["estado_conciliacion"],
            $data["estado_liquidacion"],
            $data["observacion"],
            $data["transaccion_id"],
            $data["metodo_id"],
            $data["fecha"],
            $data["estado_id"],
            $data["fecha_modificacion"],
            $data["usuario"],
            $data["email"],
            $data["cantidad"],
            $data["id_externo"],
            $data["respuesta"],
            $data["agente"],
            $data["fecha_registro_jugador"],
            $data["status"],
            $data["created_at"],
            $data["user_created_id"],
            $data["updated_at"],
            $data["user_updated_id"]
        ]);
    }

    $sql = $base_sql . implode(", ", $values);

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            throw new Exception("Error executing batch insert: " . $stmt->error);
        }

        $stmt->close();
    } else {
        throw new Exception("Error preparing batch insert statement: " . $mysqli->error);
    }
}

function fn_conci_proveedor_insert_batch($batch_data) {
    global $mysqli;

    $base_sql = "
        INSERT INTO tbl_conci_proveedor_transaccion (
            importacion_id,
            formula_id,
            periodo_id,
            transaccion_id,
            transaccion_proveedor_id,
            estado_id,
            data_json,
            monto,
            comision_total,
            comision_total_calculado,
            estado_conciliacion,
            status,
            created_at,
            user_created_id,
            updated_at,
            user_updated_id
        ) VALUES ";

    $values = [];
    $types = "";
    $params = [];

    foreach ($batch_data as $data) {
        $values[] = "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $types .= "iiissisdddiisisi";
        
        $params = array_merge($params, [
            $data["importacion_id"],
            $data["formula_id"],
            $data["periodo_id"],
            $data["transaccion_id"],
            $data["transaccion_proveedor_id"],
            $data["estado_id"],
            $data["data_json"],
            $data["monto"],
            $data["comision_total"],
            $data["comision_total_calculado"],
            $data["estado_conciliacion"],
            $data["status"],
            $data["created_at"],
            $data["user_created_id"],
            $data["updated_at"],
            $data["user_updated_id"]
        ]);
    }

    $sql = $base_sql . implode(", ", $values);

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            throw new Exception("Error executing batch insert: " . $stmt->error);
        }

        $stmt->close();
    } else {
        throw new Exception("Error preparing batch insert statement: " . $mysqli->error);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_importar_proveedor") {

    $datos = json_decode($_POST['datos'], true);
    $provider_id = $_POST['proveedor_id'];
    $periodo_id = $_POST['periodo_id'];


    //  Obtener tipo de importación ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $selectQueryFormatoArchivo = "SELECT 
                                            tipo_importacion_id
                                        FROM tbl_conci_proveedor 
                                        WHERE id = ? 
                                        LIMIT 1";

        $selectStmtFormato = $mysqli->prepare($selectQueryFormatoArchivo);
        $selectStmtFormato->bind_param("i", $provider_id);

        if (!$selectStmtFormato) {
            fn_conci_set_status_code_response(500, "Error en la preparación de la sentencia SQL: " . $selectStmtFormato->error, "Error");
        }

        $selectStmtFormato->execute();
        $selectStmtFormato->store_result();

        if ($selectStmtFormato->num_rows > 0) {
            $selectStmtFormato->bind_result($tipo_importacion_id);
            $selectStmtFormato->fetch();
            if($tipo_importacion_id == 1){
            $tipo_archivo_id = 3;
            }else{
            $tipo_archivo_id = 1;
            }
        }else{
            fn_conci_set_status_code_response(400, "No se encontro el formato de calimaco", "Error");
        }

    // Consultar las columnas del archivo del proveedor   //////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $selectColumnaProveedor = "SELECT nombre FROM tbl_conci_proveedor_columna WHERE proveedor_id = ? AND tipo_archivo_id = ? AND status = 1 ORDER BY orden ASC";
        $stmt = $mysqli->prepare($selectColumnaProveedor);

        if (!$stmt) {
            fn_conci_set_status_code_response(400, "Error en la preparación de la consulta de columnas de proveedor".$mysqli->error, "Error");
        }

        $stmt->bind_param("ii", $provider_id, $tipo_archivo_id);
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

    $data = fn_conci_periodo_provider_upload_file($fields_provider, $file_columns, $provider_id, $tipo_archivo_id, $periodo_id);

    if (count($data["data_detalle"]) == 0) {
        fn_conci_set_status_code_response(400, "No hay registros en el archivo.".$linea, "El archivo no coincide debido a los siguientes errores:");
    }

    $response = fn_conci_periodo_provider_save_data($data, $fields_provider, $file_columns,$provider_id, $tipo_archivo_id, $periodo_id);

    print_r(json_encode($response));
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_cerrar_conciliacion") {
    global $login;
    global $mysqli;
    $datetime = date("Y-m-d H:i:s");
    $periodo_id = $_POST["periodo_id"];

    try{

        //  Consultar tipo de comisión

            $selectQuery = "SELECT pt.proveedor_id, p.tipo_calculo_id,c.metodo,IFNULL(t.metodo,'')
                FROM tbl_conci_periodo pt
                LEFT JOIN tbl_conci_proveedor p ON p.id=pt.proveedor_id
                LEFT JOIN tbl_conci_calculo_tipo c ON c.id=p.tipo_calculo_id
                LEFT JOIN tbl_conci_formula_tipo t ON t.id = p.tipo_formula_id
                WHERE pt.status = 1 AND pt.id = ?
                LIMIT 1";

            $selectStmt = $mysqli->prepare($selectQuery);
            $selectStmt->bind_param("i", $periodo_id);
            $selectStmt->execute();
            $selectStmt->store_result();

            if ($selectStmt->num_rows > 0) {
                $selectStmt->bind_result($proveedor_id,$proveedor_tipo_calculo_id, $proveedor_metodo_calculo, $proveedor_metodo_formula);
                $selectStmt->fetch();
                $selectStmt->close();
            }


            switch ($proveedor_metodo_calculo) {
                case "MetodoAcumuladoMensual":

                    //  Obtener listado de registros    //////////////////////////////////////////////////////////////////////////////////////////
                                    
                    $selectQuery = "SELECT pt.formula_id, COUNT(pt.id),SUM(pt.comision_total_calculado), SUM(pt.monto)
                    FROM tbl_conci_proveedor_transaccion pt
                    WHERE pt.status = 1 AND pt.periodo_id = ?
                    GROUP BY pt.formula_id" ; 
                    $stmt = $mysqli->prepare($selectQuery);
                    if (!$stmt) throw new Exception("Error en la preparación de la sentencia de comisión " . $mysqli->error);
                    $stmt->bind_param("i", $periodo_id);
                    if (!$stmt->execute()) throw new Exception("Error en la ejecución de la sentencia de comisión " . $mysqli->error);
                    $stmt->bind_result($formula_id, $conteo,$comision_con_igv, $monto_total);

                    break;
                case "MetodoPorTransaccion":

                    //  Obtener formulas

                        if($proveedor_metodo_formula == "") fn_conci_set_status_code_response(400, "No existe el metodo de formula. Registrar formulas en mantenimiento de proveedores", "Error");

                        switch ($proveedor_metodo_formula) {
                            case "FormulaFija":

                                $formulasProveedor = obtenerFormulaFijaProveedor($mysqli, $proveedor_id);
                                break;
                            case "FormulaEscalonada":
                                $formulasProveedor = obtenerFormulaEscalonadaProveedor($mysqli, $proveedor_id);         
                                break;

                            case "FormulaMixta":
                                $formulasProveedor = obtenerFormulaMixtaProveedor($mysqli, $proveedor_id);
                                break;
                            default:
                                fn_conci_set_status_code_response(400, "No existe el metodo de formula. Registrar formulas en mantenimiento de proveedores", "Error");
                        }

                        if (empty($formulasProveedor)) fn_conci_set_status_code_response(400, "No hay reglas definidas para calcular la comisión. Registrar formulas en mantenimiento de proveedores", "Error");

                    //  Obtener totales
                                                        
                    $selectQuery = "SELECT COUNT(pt.id), SUM(pt.monto)
                    FROM tbl_conci_proveedor_transaccion pt
                    WHERE pt.status = 1 AND pt.periodo_id = ?" ; 
                    $stmt = $mysqli->prepare($selectQuery);
                    if (!$stmt) throw new Exception("Error en la preparación de la sentencia de comisión " . $mysqli->error);
                    $stmt->bind_param("i", $periodo_id);
                    if (!$stmt->execute()) throw new Exception("Error en la ejecución de la sentencia de comisión " . $mysqli->error);
                    $stmt->bind_result($conteo,$monto_total);

                    //  Calcular comisión

                    switch ($proveedor_metodo_formula) {
                        case "FormulaFija":
                
                            foreach ($formulasProveedor as $regla) {
        
                                switch ($regla['operador']) {
                                    case ">":
                                        if ($item_detalle[$regla['columna']] > $regla['opcion']) {
                                            $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                            $formula_id = $regla['id'];
                                        }
                                        break;
                                    case ">=": 
                                        if ($item_detalle[$regla['columna']] >= $regla['opcion']) {
                                            $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                            $formula_id = $regla['id'];
                                        }
                                        break;

                                    case "==": 
                                        if ($item_detalle[$regla['columna']] == $regla['opcion']) {
                                            $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                            $formula_id = $regla['id'];
                                        }
                                        break;
                                    case "<": 
                                        if ($item_detalle[$regla['columna']] < $regla['opcion']) {
                                            $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                            $formula_id = $regla['id'];
                                        }
                                        break;

                                    case "<=": 
                                        if ($item_detalle[$regla['columna']] <= $regla['opcion']) {
                                            $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                            $formula_id = $regla['id'];
                                        }
                                        break;

                                    case "<>": 
                                        if ($item_detalle[$regla['columna']] != $regla['opcion']) {
                                            $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                            $formula_id = $regla['id'];
                                        }
                                        break;
                                    default:
                                        break;
                                    }
                            }
        
                            break;
                        case "FormulaEscalonada":
                        
                            foreach ($formulasProveedor as $regla) {
        
                                $desde= (float)$regla["desde"];
                                $hasta= (float)$regla["hasta"];
        
                                if ($monto_total >= $desde && $monto_total <= $hasta) {
                                    $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                    $formula_id = $regla['id'];
                                }

                                }
                            break;
        
                        case "FormulaMixta":
                        
                            foreach ($formulasProveedor as $regla) {
        
                                if ($monto_total >= $regla['desde'] && $monto_total <= $regla['hasta']) {
                                    if (!empty($regla['operador']) && !empty($regla['opcion'])) {
                                        switch ($regla['operador']) {
                                            case ">":
                                                if ($item_detalle[$regla[$columna]] > $regla['opcion']) {
                                                    $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                                    $formula_id = $regla['id'];
                                                }
                                                break;
                                            case ">=": 
                                                if ($item_detalle[$regla[$columna]] >= $regla['opcion']) {
                                                    $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                                    $formula_id = $regla['id'];
                                                }
                                                break;
        
                                            case "==": 
                                                if ($item_detalle[$regla[$columna]] == $regla['opcion']) {
                                                    $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                                    $formula_id = $regla['id'];
                                                }
                                                break;
                                            case "<": 
                                                if ($item_detalle[$regla[$columna]] < $regla['opcion']) {
                                                    $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                                    $formula_id = $regla['id'];
                                                }
                                                break;
        
                                            case "<=": 
                                                if ($item_detalle[$regla[$columna]] <= $regla['opcion']) {
                                                    $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                                    $formula_id = $regla['id'];
                                                }
                                                break;
        
                                            case "<>": 
                                                if ($item_detalle[$regla[$columna]] != $regla['opcion']) {
                                                    $comision_con_igv = calcularComisionTotal($monto_total, $regla);
                                                    $formula_id = $regla['id'];
                                                }
                                                break;
                                            default:
                                                break;
                                            }
                                    } else {
                                            fn_conci_set_status_code_response(400, "Las formulas estan incompletas. Registrar formulas en mantenimiento de proveedores", "Error");
                                    }
                                }
                                }
                                break;
                        default:
                            fn_conci_set_status_code_response(400, "No existe el metodo de formula. Registrar formulas en mantenimiento de proveedores", "Error");
                    }

                    break;
                default:
                throw new Exception("No existe el metodo de importación");
                break;
            }

        //   Realizar el conteo

            $data = [];

            while ($stmt->fetch()) {
                $data[] = [
                    'periodo_id' => $periodo_id,
                    'formula_id' => $formula_id,
                    'count' => $conteo,
                    'comision_total' => $comision_con_igv,
                    'monto_total' => $monto_total,
                    'created_at' => $datetime,
                    'user_created_id' => $login["id"],
                ];
            }
        
            $stmt->close();

            fn_conci_provider_cleanComision($mysqli,$periodo_id,$datetime,$login["id"]);

            foreach ($data as $item) {

                $r = fn_insertarComision($mysqli,$item['periodo_id'],$item['formula_id'], $item['count'],$item['comision_total'],$item['monto_total'],$item['created_at'],$item['user_created_id']);
            }

            $result["http_code"] = 200;
            echo json_encode($result);

    } catch (PHPExcel_Reader_Exception $ex) {
        $result["http_code"] = 400;
        $result["error"] = $ex->getMessage();
        echo json_encode($result);
        exit();
        fn_conci_set_status_code_response(500, $ex->getMessage(), "Error");
    } catch (Exception $e) {
        $result["http_code"] = 400;
        $result["error"] = $e->getMessage();
        echo json_encode($result);
        exit();
    }
            
}

function fn_insertarComision($mysqli, $periodo_id, $formula_id, $conteo, $comision_total, $monto_total, $created_at, $user_created_id) {

    $query_insert_comision = "
        INSERT INTO tbl_conci_proveedor_comision (
            periodo_id,
            formula_id,
            count,
            comision_total,
            monto_total,
            status,
            created_at,
            user_created_id
        )  
        VALUES (?, ?, ?, ?, ?,1, ?, ?)";
    
    $stmtInsertComision = $mysqli->prepare($query_insert_comision);
    
    if ($stmtInsertComision == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertComision->bind_param("iiiddsi", 
                                                    $periodo_id,
                                                    $formula_id,
                                                    $conteo,
                                                    $comision_total,
                                                    $monto_total,            
                                                    $created_at,
                                                    $user_created_id);
    
    if (!$stmtInsertComision->execute()) {
        throw new Exception("Error al guardar las columnas del archivo: " . $mysqli->error);
    }
    $stmtInsertComision->close();
}

function fn_conci_provider_cleanComision($mysqli, $periodo_id, $fecha, $usuario_id){

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_comision
                                WHERE status = 1 AND periodo_id= ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $periodo_id);
        
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if($count > 0){
                $sql_update = "UPDATE tbl_conci_proveedor_comision SET status = 0, updated_at = ?, user_updated_id = ? WHERE status = 1 AND periodo_id = ?";

                $stmtClean = $mysqli->prepare($sql_update);
                $stmtClean->bind_param("sii", $fecha, $usuario_id, $periodo_id);
                if (!$stmtClean->execute()) {
                    fn_conci_set_status_code_response(400, "Error al conciliar el registro de colimaco: " . $mysqli->error, "Error");
                }
                $stmtClean->close();
            }

        } else {
            fn_conci_set_status_code_response(400, "Error en la ejecución de la consulta: " . $mysqli->error, "Error");
            exit();
        }
    } else {
        fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: " . $mysqli->error, "Error");
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_importacion_proveedor_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $importacion_id = $_POST["importacion_id"];

        if ((int)$importacion_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_devolucion = "UPDATE tbl_conci_proveedor_importacion
                                        SET 
                                            status = 0,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                            id = ?";
            $stmt = $mysqli->prepare($query_update_devolucion);
            if (!$stmt) {
                $result = [
                "http_code" => 500,
                "error" => "Error en la preparación de la consulta: " . $mysqli->error
                ];
                echo json_encode($result);
                exit();
            }
            $stmt->bind_param("iss", $usuario_id, $fecha, $importacion_id);

            if ($stmt->execute()) {
                $stmt->close();

                $selectQueryProveedor = "SELECT 
                    i.id, i.periodo_id,
                    COUNT(pt.id),
                    SUM(IFNULL(pt.monto, 0)),
                    SUM(IFNULL(pt.comision_total,0)),
                    SUM(IFNULL(pt.comision_total_calculado,0)),
                    IFNULL(p.monto_proveedor, 0),
                    IFNULL(p.comision_proveedor, 0), 
                    IFNULL(p.comision_calimaco, 0), 
                    IFNULL(p.proveedor_count, 0)
                    FROM tbl_conci_proveedor_transaccion pt
                    LEFT JOIN tbl_conci_proveedor_importacion i ON pt.importacion_id = i.id
                    LEFT JOIN tbl_conci_periodo p ON i.periodo_id = p.id
                    WHERE i.id = ? AND pt.status = 1
                    GROUP BY i.id
                    LIMIT 1";

                $selectStmtProveedor = $mysqli->prepare($selectQueryProveedor);
                if (!$selectStmtProveedor) {
                $result = [
                "http_code" => 500,
                "error" => "Error en la preparación de la consulta: " . $mysqli->error
                ];
                echo json_encode($result);
                exit();
                }

                $selectStmtProveedor->bind_param("i", $importacion_id);
                if ($selectStmtProveedor->execute()) {
                    $selectStmtProveedor->store_result();

                    if ($selectStmtProveedor->num_rows > 0) {
                        $selectStmtProveedor->bind_result(
                                                        $id, $periodo_id, 
                                                        $imp_conteo_proveedor, 
                                                        $imp_monto_proveedor, 
                                                        $imp_comision_proveedor,
                                                        $imp_comision_calimaco, 
                                                        $pe_monto_proveedor,
                                                        $pe_comision_proveedor,
                                                        $pe_comision_calimaco,
                                                        $pe_proveedor_count
                                                        );
                        $selectStmtProveedor->fetch();
                        $selectStmtProveedor->close();

                        $pe_monto_proveedor = $pe_monto_proveedor - $imp_monto_proveedor;
                        $pe_comision_proveedor = $pe_comision_proveedor - $imp_comision_proveedor;
                        $pe_comision_calimaco = $pe_comision_calimaco - $imp_comision_calimaco;
                        $pe_proveedor_count = $pe_proveedor_count - $imp_conteo_proveedor;

                        $cleanResult = fn_conci_venta_cleanImportacion($mysqli, $importacion_id);
                        if ($cleanResult["http_code"] != 200) {
                            echo json_encode($cleanResult);
                            exit();
                            }

                        $updateResult = fn_conci_updatePeriodoImportacion($mysqli, $periodo_id, $usuario_id, $fecha, $pe_monto_proveedor, $pe_comision_proveedor, $pe_comision_calimaco, $pe_proveedor_count);
                        if ($updateResult["http_code"] != 200) {
                            echo json_encode($updateResult);
                            exit();
                            }

                        $result = [
                        "http_code" => 200,
                        "status" => "Transacción eliminada y periodo actualizado exitosamente."
                        ];
                    } else {
                        $result = [
                        "http_code" => 400,
                        "error" => "No se encontraron transacciones para la importación especificada."
                        ];
                    }
                    } else {
                    $result = [
                    "http_code" => 500,
                    "error" => "Error en la ejecución de la consulta: " . $selectStmtProveedor->error
                    ];
                }
            } else {
                $result = [
                "http_code" => 500,
                "error" => "Error en la ejecución de la consulta de actualización: " . $stmt->error
                ];
            }
        } else {
            $result = [
            "http_code" => 400,
            "error" => "El ID de la importación no es válido."
            ];
        }
    } else {
            $result = [
            "http_code" => 400,
            "error" => "La sesión ha caducado. Por favor, inicie sesión nuevamente."
            ];
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_importacion_calimaco_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $importacion_id = $_POST["importacion_id"];

        if ((int)$importacion_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_devolucion = "UPDATE tbl_conci_calimaco_importacion
                                        SET 
                                            status = 0,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                            id = ?";
            $stmt = $mysqli->prepare($query_update_devolucion);
            if (!$stmt) {
                $result = [
                "http_code" => 500,
                "error" => "Error en la preparación de la consulta: " . $mysqli->error
                ];
                echo json_encode($result);
                exit();
            }
            $stmt->bind_param("iss", $usuario_id, $fecha, $importacion_id);

            if ($stmt->execute()) {
                $stmt->close();

                $selectQueryProveedor = "SELECT 
                    i.id, i.periodo_id,
                    COUNT(pt.id),
                    SUM(IFNULL(pt.cantidad, 0)),
                    IFNULL(p.monto_calimaco, 0),
                    IFNULL(p.calimaco_count, 0)
                    FROM tbl_conci_calimaco_transaccion pt
                    LEFT JOIN tbl_conci_calimaco_importacion i ON pt.importacion_id = i.id
                    LEFT JOIN tbl_conci_periodo p ON i.periodo_id = p.id
                    WHERE i.id = ? AND pt.status = 1
                    GROUP BY i.id
                    LIMIT 1";

                $selectStmtProveedor = $mysqli->prepare($selectQueryProveedor);
                if (!$selectStmtProveedor) {
                $result = [
                "http_code" => 500,
                "error" => "Error en la preparación de la consulta: " . $mysqli->error
                ];
                echo json_encode($result);
                exit();
                }

                $selectStmtProveedor->bind_param("i", $importacion_id);
                if ($selectStmtProveedor->execute()) {
                    $selectStmtProveedor->store_result();

                    if ($selectStmtProveedor->num_rows > 0) {
                        $selectStmtProveedor->bind_result(
                                                        $id, $periodo_id, 
                                                        $imp_conteo_calimaco, 
                                                        $imp_monto_calimaco, 
                                                        $pe_monto_calimaco,
                                                        $pe_calimaco_count
                                                        );
                        $selectStmtProveedor->fetch();
                        $selectStmtProveedor->close();

                        $pe_monto_calimaco = $pe_monto_calimaco - $imp_monto_calimaco;
                        $pe_calimaco_count = $pe_calimaco_count - $imp_conteo_calimaco;

                        $cleanResult = fn_conci_venta_cleanCalimacoImportacion($mysqli, $importacion_id);
                        if ($cleanResult["http_code"] != 200) {
                            echo json_encode($cleanResult);
                            exit();
                            }

                        $updateResult = fn_conci_updatePeriodoCalimacoImportacion($mysqli, $periodo_id, $usuario_id, $fecha,$pe_monto_calimaco, $pe_calimaco_count);
                        if ($updateResult["http_code"] != 200) {
                            echo json_encode($updateResult);
                            exit();
                            }

                    $result = [
                    "http_code" => 200,
                    "status" => "Transacción eliminada y periodo actualizado exitosamente."
                    ];
                    } else {
                        $result = [
                        "http_code" => 400,
                        "error" => "No se encontraron transacciones para la importación especificada."
                        ];
                    }
                    } else {
                    $result = [
                    "http_code" => 500,
                    "error" => "Error en la ejecución de la consulta: " . $selectStmtProveedor->error
                    ];
                }
            } else {
                $result = [
                "http_code" => 500,
                "error" => "Error en la ejecución de la consulta de actualización: " . $stmt->error
                ];
            }
        } else {
            $result = [
            "http_code" => 400,
            "error" => "El ID de la importación no es válido."
            ];
        }
    } else {
            $result = [
            "http_code" => 400,
            "error" => "La sesión ha caducado. Por favor, inicie sesión nuevamente."
            ];
    }

    echo json_encode($result);
    exit();
}

function fn_conci_updatePeriodoCalimacoImportacion($mysqli, $periodo_id, $usuario_id, $fecha, $pe_monto_calimaco, $pe_calimaco_count) {
    $result = [];

    $query_update_transaccion = "UPDATE tbl_conci_periodo
                                        SET 
                                            monto_calimaco = ?,
                                            calimaco_count = ?,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                             id = ?";
    $stmt = $mysqli->prepare($query_update_transaccion);
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error al preparar la consulta para anulación: " . $mysqli->error;
        return $result;
    }

    $stmt->bind_param("diisi", $pe_monto_calimaco, $pe_calimaco_count, $usuario_id, $fecha, $periodo_id);
    if ($stmt->execute()) {
        $stmt->close();

        $result["http_code"] = 200;
        $result["status"] = "Datos obtenidos de gestión.";
    } else {
        $result["http_code"] = 400;
        $result["error"] = "Error al anular la transacción: " . $mysqli->error;
    }

    return $result;
}

function fn_conci_venta_cleanCalimacoImportacion($mysqli, $importacion) {
    $result = [];

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_calimaco_transaccion
                                WHERE status = 1 AND importacion_id = ?");
    
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
        return $result;
    }

    $stmt->bind_param("i", $importacion);
    
    if ($stmt->execute()) {
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $sql_update = "DELETE FROM tbl_conci_calimaco_transaccion WHERE importacion_id = ?";
            $stmtClean = $mysqli->prepare($sql_update);

            if (!$stmtClean) {
                $result["http_code"] = 500;
                $result["error"] = "Error en la preparación de la consulta de eliminación: " . $mysqli->error;
                return $result;
            }

            $stmtClean->bind_param("i", $importacion);
            if ($stmtClean->execute()) {
                $stmtClean->close();

                $result["http_code"] = 200;
                $result["status"] = "Importación limpiada con éxito.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la eliminación: " . $mysqli->error;
            }
        } else {
            $result["http_code"] = 200;
            $result["status"] = "No hay transacciones para limpiar.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "Error en la ejecución de la consulta: " . $mysqli->error;
    }

    return $result;
}

function fn_conci_venta_cleanCalimacoPeriodo($mysqli, $periodo) {
    $result = [];

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_calimaco_transaccion
                                WHERE status = 1 AND periodo_id = ?");
    
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
        return $result;
    }

    $stmt->bind_param("i", $periodo);
    
    if ($stmt->execute()) {
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $sql_update = "DELETE FROM tbl_conci_calimaco_transaccion WHERE periodo_id = ?";
            $stmtClean = $mysqli->prepare($sql_update);

            if (!$stmtClean) {
                $result["http_code"] = 500;
                $result["error"] = "Error en la preparación de la consulta de eliminación: " . $mysqli->error;
                return $result;
            }

            $stmtClean->bind_param("i", $periodo);
            if ($stmtClean->execute()) {
                $stmtClean->close();

                $result["http_code"] = 200;
                $result["status"] = "Importación limpiada con éxito.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la eliminación: " . $mysqli->error;
            }
        } else {
            $result["http_code"] = 200;
            $result["status"] = "No hay transacciones para limpiar.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "Error en la ejecución de la consulta: " . $mysqli->error;
    }

    return $result;
}

function fn_conci_venta_cleanCalimacoPeriodoProveedor($mysqli, $periodo) {
    $result = [];

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_transaccion
                                WHERE status = 1 AND periodo_id = ?");
    
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
        return $result;
    }

    $stmt->bind_param("i", $periodo);
    
    if ($stmt->execute()) {
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $sql_update = "DELETE FROM tbl_conci_proveedor_transaccion WHERE periodo_id = ?";
            $stmtClean = $mysqli->prepare($sql_update);

            if (!$stmtClean) {
                $result["http_code"] = 500;
                $result["error"] = "Error en la preparación de la consulta de eliminación: " . $mysqli->error;
                return $result;
            }

            $stmtClean->bind_param("i", $periodo);
            if ($stmtClean->execute()) {
                $stmtClean->close();

                $result["http_code"] = 200;
                $result["status"] = "Importación limpiada con éxito.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la eliminación: " . $mysqli->error;
            }
        } else {
            $result["http_code"] = 200;
            $result["status"] = "No hay transacciones para limpiar.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "Error en la ejecución de la consulta: " . $mysqli->error;
    }

    return $result;
}



function fn_conci_updatePeriodoImportacion($mysqli, $periodo_id, $usuario_id, $fecha, $pe_monto_proveedor, $pe_comision_proveedor, $pe_comision_calimaco, $pe_proveedor_count) {
    $result = [];

    $query_update_transaccion = "UPDATE tbl_conci_periodo
                                        SET 
                                            monto_proveedor = ?,
                                            comision_proveedor = ?,
                                            comision_calimaco = ?,
                                            proveedor_count = ?,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                             id = ?";
    $stmt = $mysqli->prepare($query_update_transaccion);
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error al preparar la consulta para anulación: " . $mysqli->error;
        return $result;
    }

    $stmt->bind_param("dddiisi", $pe_monto_proveedor, $pe_comision_proveedor, $pe_comision_calimaco, $pe_proveedor_count, $usuario_id, $fecha, $periodo_id);
    if ($stmt->execute()) {
        $stmt->close();

        $result["http_code"] = 200;
        $result["status"] = "Datos obtenidos de gestión.";
    } else {
        $result["http_code"] = 400;
        $result["error"] = "Error al anular la transacción: " . $mysqli->error;
    }

    return $result;
}

function fn_conci_venta_cleanImportacion($mysqli, $importacion) {
    $result = [];

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_transaccion
                                WHERE status = 1 AND importacion_id = ?");
    
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
        return $result;
    }

    $stmt->bind_param("i", $importacion);
    
    if ($stmt->execute()) {
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $sql_update = "DELETE FROM tbl_conci_proveedor_transaccion WHERE importacion_id = ?";
            $stmtClean = $mysqli->prepare($sql_update);

            if (!$stmtClean) {
                $result["http_code"] = 500;
                $result["error"] = "Error en la preparación de la consulta de eliminación: " . $mysqli->error;
                return $result;
            }

            $stmtClean->bind_param("i", $importacion);
            if ($stmtClean->execute()) {
                $stmtClean->close();

                $result["http_code"] = 200;
                $result["status"] = "Importación limpiada con éxito.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la eliminación: " . $mysqli->error;
            }
        } else {
            $result["http_code"] = 200;
            $result["status"] = "No hay transacciones para limpiar.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "Error en la ejecución de la consulta: " . $mysqli->error;
    }

    return $result;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_importacion_proveedor_editar") {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_importacion_calimaco_editar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
        $fecha_inicio = date("Y-m-d", strtotime($_POST['fecha_inicio']));
        $fecha_fin = date("Y-m-d", strtotime($_POST['fecha_fin']));

        if ((int)$id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_razon_social = "
					UPDATE tbl_conci_calimaco_importacion
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

function fn_conci_periodo_provider_upload_file($fields_provider, $file_columns, $provider_id, $tipo_archivo_id, $periodo_id) {
    try {
        global $mysqli;
        error_reporting(0);
        require_once '/var/www/html/phpexcel/classes/PHPExcel.php';
        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '10M');
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        set_time_limit(0);
        $data = array();

        gc_enable();
        gc_collect_cycles();

        $archivo_name = "form_modal_sec_conci_venta_periodo_importar_proveedor";

        // Obtener formato de archivo
        $selectQueryFormatoArchivo = "SELECT 
                                af.nombre, af.linea_inicio, af.columna_inicio, ae.nombre, IFNULL(ase.simbolo, '')
                            FROM tbl_conci_archivo_formato af
                            LEFT JOIN tbl_conci_archivo_extension ae 
                            ON af.extension_id = ae.id
                            LEFT JOIN tbl_conci_archivo_separador ase 
                            ON af.separador_id = ase.id
                            WHERE af.proveedor_id = ? AND af.tipo_archivo_id = ?
                            AND af.status = 1
                            LIMIT 1";

        $selectStmtFormato = $mysqli->prepare($selectQueryFormatoArchivo);
        $selectStmtFormato->bind_param("ii", $provider_id, $tipo_archivo_id);

        if (!$selectStmtFormato) {
            fn_conci_set_status_code_response(500, "Error en la preparación de la sentencia SQL: " . $selectStmtFormato->error, "Error");
        }

        $selectStmtFormato->execute();
        $selectStmtFormato->store_result();

        if ($selectStmtFormato->num_rows > 0) {
            $selectStmtFormato->bind_result($param_name, $start_line, $columna_inicio, $extension, $separador);
            $selectStmtFormato->fetch();
            if ($extension == "csv" && $separador == "") {
                fn_conci_set_status_code_response(400, "No se encontró el separador para el formato csv del archivo de calimaco", "Error");
            }
        } else {
            fn_conci_set_status_code_response(400, "No se encontró el formato de calimaco", "Error");
        }

        // Validación de archivo
        if (isset($_FILES[$archivo_name]) && $_FILES[$archivo_name]['name'] == "") {
            fn_conci_set_status_code_response(400, "Ingresar Archivo excel", "Error");
        }

        $ext = pathinfo($_FILES[$archivo_name]['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $_FILES[$archivo_name]['name'];

        if ($ext != $extension) {
            $error = 'Extensión de archivo incorrecta: ' . $ext . '<br> Extensión de archivo correcta: ' . $extension;
            fn_conci_set_status_code_response(400, $error, null);
        }

        if (strpos($_FILES[$archivo_name]['name'], $param_name) === false) {
            fn_conci_set_status_code_response(400, "El nombre del archivo debe contener '" . $param_name . "'", "Nombre de archivo incorrecto");
        }

        if (strlen($_FILES[$archivo_name]['name']) > 250) {
            fn_conci_set_status_code_response(400, "El nombre del archivo contiene más de 250 caracteres", "Nombre de archivo incorrecto");
        }

        // Cargar el archivo
        $tmpfname = $_FILES[$archivo_name]['tmp_name'];

        if (!file_exists($tmpfname)) {
            fn_conci_set_status_code_response(400, "El archivo supera el tamaño permitido.", "Error de archivo");
            exit;
        }


        if ($ext === 'csv') {
            $csvData = array_map(function($line) use ($separador) {
                return str_getcsv($line, $separador);
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
                fn_conci_set_status_code_response(400, "Error al cargar el archivo: " . $e->getMessage(), "Error de archivo");
                exit;
            }

            $worksheet = $excelObj->getSheet();
        }

        $worksheet = $excelObj->getActiveSheet();

        // Verificar columna de inicio
        if ($columna_inicio > 1) {
            $worksheet->removeColumn('A', $columna_inicio - 1);
        }

        // Verificar si hay fórmulas en el archivo
        if (checkForFormulas($worksheet)) {
            fn_conci_set_status_code_response(400, "El archivo contiene fórmulas, lo cual no está permitido. <br>Debe modificar el tipo de importación en la ventana de mantenimiento del proveedor para poder subirlo como archivo csv", "Error de archivo");
            exit;
        }

        $check_archivo = fn_conci_provider_check_headers($worksheet, $fields_provider, $file_columns, $start_line);

        if ($check_archivo !== true) {
            fn_conci_set_status_code_response(400, $check_archivo, "El archivo no se procesó debido a los siguientes errores");
        }

        // Obtener registros
        $headerRow = $start_line;
        $firstRow = $headerRow + 1;

        $data = array();
        $data = fn_conci_provider_get_data_detail($worksheet, $fields_provider, $firstRow, 0, $file_columns);
        $data["nombre_archivo"] = $nombre_archivo;
        $excelObj->disconnectWorksheets();
        $excelObj->garbageCollect();
        unset($excelObj);

        return $data;

    } catch (Exception $ex) {
        fn_conci_set_status_code_response(500, $ex->getMessage(), null);
    }
}

function fn_conci_periodo_provider_save_data($data, $fields_provider, $file_columns ,$provider_id,$tipo_archivo_id,$periodo_id){
    global $mysqli,$login;

    try {
        $mysqli->begin_transaction();

        //  Declaración de variables /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            $fecha_inicio = date("Y-m-d", strtotime($_POST['form_modal_sec_conci_periodo_venta_importar_proveedor_fecha_inicio']));
            $fecha_fin = date("Y-m-d", strtotime($_POST['form_modal_sec_conci_venta_periodo_importar_proveedor_fecha_fin']));
            $nombre_archivo = $_FILES["form_modal_sec_conci_venta_periodo_importar_proveedor"]['name'];
            $datos = $data["data_detalle"];
            $tbl_conci_provider_state = [];
            $msg_error = [];
            $row = 2;
            $array_add_transaccion = [];
            $datetime = date("Y-m-d H:i:s");

        //  Obtener datos de bd para validación //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //  Obtener metodo de importación

                $selectQueryMetodoImportacion = "SELECT 
                                    it.id,it.metodo,cc.nombre_bd,IFNULL(t.metodo,'')
                                FROM tbl_conci_proveedor cp
                                LEFT JOIN tbl_conci_importacion_tipo it
                                ON cp.tipo_importacion_id = it.id
                                LEFT JOIN tbl_conci_calimaco_columna cc
                                ON cp.columna_conciliacion_id = cc.id
                                LEFT JOIN tbl_conci_formula_tipo t
                                ON t.id = cp.tipo_formula_id
                                WHERE cp.id = ? 
                                AND cp.status = 1
                                LIMIT 1";

                $selectStmtMetodoImportacion = $mysqli->prepare($selectQueryMetodoImportacion);
                $selectStmtMetodoImportacion->bind_param("i", $provider_id);

                if (!$selectStmtMetodoImportacion) {
                    fn_conci_set_status_code_response(500, "Error en la preparación de la sentencia SQL: " . $selectStmtFormato->error, "Error");
                }
                $selectStmtMetodoImportacion->execute();
                $selectStmtMetodoImportacion->store_result();

                if ($selectStmtMetodoImportacion->num_rows > 0) {
                    $selectStmtMetodoImportacion->bind_result($id_importacion,$metodo_importacion,$columna_conciliacion_name,$metodo_formula);
                    $selectStmtMetodoImportacion->fetch();
                    if($metodo_importacion == ""){
                        fn_conci_set_status_code_response(400, "No se encontro el metodo de importación del proveedor", "Error");
                    }else{
                        switch ($metodo_importacion) {
                            case "ColumnasArchivoCombinado":
                                $tipo_archivo_id = 3;

                                break;
                            case "ColumnasArchivosIndependientes":
                                $tipo_archivo_id = 1;

                                break;
                            default:
                            throw new Exception("No existe el metodo de importación");
                            break;
                        }
                    }
                    $selectStmtMetodoImportacion->close();

                }else{
                    fn_conci_set_status_code_response(400, "No se encontro el formato de calimaco", "Error");
                }

            //  Lista de estados

                $queryState= "SELECT id, nombre FROM tbl_conci_proveedor_estado WHERE status = 1 AND proveedor_id = ?";
                $stmtState = $mysqli->prepare($queryState);

                if ($stmtState) {
                    $stmtState->bind_param("i", $provider_id);
                    $stmtState->execute();
                    $result = $stmtState->get_result();

                    if (!$result) {
                        fn_conci_set_status_code_response(400, "Error en la consulta: ".$mysqli->error, "Error");
                    } else {
                        $tbl_conci_provider_state = [];
                        while ($r = $result->fetch_assoc()) {
                            $tbl_conci_provider_state[$r["nombre"]] = $r;
                        }
                    }
                    $stmtState->close();
                } else {
                    fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: ".$mysqli->error, "Error");
                }

            //  Obtener listado de formulas    //////////////////////////////////////////////////////////////////////////////////////////
                                        
                if($metodo_formula == "") fn_conci_set_status_code_response(400, "No existe el metodo de formula. Registrar formulas en mantenimiento de proveedores", "Error");

                switch ($metodo_formula) {
                    case "FormulaFija":

                        $formulasProveedor = obtenerFormulaFijaProveedor($mysqli, $provider_id);
                        break;
                    case "FormulaEscalonada":
                        $formulasProveedor = obtenerFormulaEscalonadaProveedor($mysqli, $provider_id);         
                        break;

                    case "FormulaMixta":
                        $formulasProveedor = obtenerFormulaMixtaProveedor($mysqli, $provider_id);
                        break;
                    default:
                        fn_conci_set_status_code_response(400, "No existe el metodo de formula. Registrar formulas en mantenimiento de proveedores", "Error");
                }

                if (empty($formulasProveedor)) fn_conci_set_status_code_response(400, "No hay reglas definidas para calcular la comisión. Registrar formulas en mantenimiento de proveedores", "Error");


            //  Lista de columnas

                $columna_conciliacion_name = "id";
                list($column_provider_id_orden, $column_provider_id_nombre,$column_provider_id_prefijo,$column_provider_id_simbolo,$column_provider_id_sufijo, $column_provider_id_nombreColumna_json, $column_provider_id_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $columna_conciliacion_name);

                $calimaco_column_name_monto = "monto";
                list($column_provider_monto_orden, $column_provider_monto_nombre, $column_provider_monto_prefijo,$column_provider_monto_simbolo,$column_provider_monto_sufijo, $column_provider_monto_nombreColumna_json, $column_provider_monto_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_monto);

                $calimaco_column_name_id_externo = "id_externo";
                list($column_provider_id_externo_orden, $column_provider_id_externo_nombre, $column_provider_id_externo_prefijo,$column_provider_id_externo_simbolo,$column_provider_id_externo_sufijo, $column_provider_id_externo_nombreColumna_json, $column_provider_id_externo_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_id_externo);

                $calimaco_column_name_estado = "estado";
                list($column_provider_estado_orden, $column_provider_estado_nombre, $column_provider_estado_prefijo,$column_provider_estado_simbolo,$column_provider_estado_sufijo, $column_provider_estado_nombreColumna_json, $column_provider_estado_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_estado);
                $column_provider_estado_orden = intval($column_provider_estado_orden)-1;


                if($tipo_archivo_id ==3){
                    $calimaco_column_name_comision = "comision_total";
                    list($column_provider_comision_orden, $column_provider_comision_nombre, $column_provider_comision_prefijo,$column_provider_comision_simbolo,$column_provider_comision_sufijo, $column_provider_comision_nombreColumna_json, $column_provider_comision_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_comision);
                    $column_provider_comision_orden = intval($column_provider_comision_orden)-1;                
                }

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

                if (!$stmtInsertImportacion->execute()) set_status_code_response(400, "No existe registro del tipo de importacion. Contactarse con soporte.", "Error");

                $import_id = $mysqli->insert_id;
                $stmtInsertImportacion->close();

            //  Deglosar data   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
            foreach ($datos as $item_detalle) {

                $registro_completo = [];
                $estado = $item_detalle[$column_provider_estado_nombre];

                //  Validación de datos //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    $estado_temp = "";
                    if ($estado == "") {
                        $msg_error[] = $file_columns[$column_provider_estado_orden] . $row . " - Estado no encontrado";
                        $row++;
                        continue;
                    }
                    if (!isset($tbl_conci_provider_state[$estado])) {
                        $msg_error[] = $file_columns[$column_provider_estado_orden] . $row . " - Tipo de estado '" . $estado . "' no encontrado";
                        $row++;
                        continue;
                    }

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
                    
                    $registro_completo_json = json_encode($registro_completo);

                //  Normalizacion de datos

                    //  Extraer datos

                        if($column_provider_id_formato == 3){
                            $json_transaccion_id = json_decode($item_detalle[$column_provider_id_nombre], true);
                            if (isset($json_transaccion_id[$columnName])) {
                                $columnValueId = $json_transaccion_id[$columnName];
                            }else{
                                fn_conci_set_status_code_response(400, "EL nombre de la columna json que contiene el id no existe o no es correcto", "Error");
                            }

                        }else{
                            $columnValueId = $item_detalle[$column_provider_id_nombre];
                        }

                        $columnValueMonto = $item_detalle[$column_provider_monto_nombre];
                        $columnValueComision = $item_detalle[$column_provider_comision_nombre];

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
                        $clean_transaction_id = trim($clean_transaction_id);

                        //  Verificar si el id es valido

                            if (preg_match('/[a-zA-Z]/', $clean_transaction_id)) {
                                fn_conci_set_status_code_response(400, "El ID a conciliar con Calimaco presenta letras como por ejemplo 'APT-4-581646465'. Puede personalizar la estructura del ID en la ventana de mantenimiento de proveedor para que permita guardar los registros correctamente." , "Error");
                            }

                            if (strpos($clean_transaction_id, '-') !== false) {
                                fn_conci_set_status_code_response(400, "El ID a conciliar con Calimaco presenta guiones como por ejemplo '4-581646465'. Puede personalizar la estructura del ID en la ventana de mantenimiento de proveedor para que permita guardar los registros correctamente." , "Error");
                            }

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
                        //$clean_monto = number_format((float)$clean_monto,2);
                        $clean_monto = (float)$clean_monto;


                        //  COMISIÓN TOTAL

                        if($tipo_archivo_id ==3){

                            if (strpos($columnValueComision, $column_provider_comision_prefijo) === 0) {
                                $clean_comision = substr($columnValueComision, strlen($column_provider_comision_prefijo));
                            } else {
                                $clean_comision = $columnValueComision;
                            }
    
                            $longitud_apt = strlen($column_provider_monto_sufijo);
                            if (substr($columnValueComision, -$longitud_apt) === $column_provider_comision_sufijo) {
                                $clean_comision = substr($columnValueComision, 0, -$longitud_apt);
                            }
    
                            $clean_comision = str_replace($column_provider_comision_simbolo, ".", $clean_comision);
                            $clean_comision = (float)$clean_comision;
                        }else{
                            $clean_comision = 0;
                        }

                        //  Calculo de comisión

                        $comision_con_igv = 0;
                        $formula_id = 0;
            
                        switch ($metodo_formula) {
                            case "FormulaFija":
                    
                                foreach ($formulasProveedor as $regla) {
            
                                    switch ($regla['operador']) {
                                        case ">":
                                            if ($item_detalle[$regla['columna']] > $regla['opcion']) {
                                                $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                $formula_id = $regla['id'];
                                            }
                                            break;
                                        case ">=": 
                                            if ($item_detalle[$regla['columna']] >= $regla['opcion']) {
                                                $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                $formula_id = $regla['id'];
                                            }
                                            break;

                                        case "==": 
                                            if ($item_detalle[$regla['columna']] == $regla['opcion']) {
                                                $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                $formula_id = $regla['id'];
                                            }
                                            break;
                                        case "<": 
                                            if ($item_detalle[$regla['columna']] < $regla['opcion']) {
                                                $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                $formula_id = $regla['id'];
                                            }
                                            break;

                                        case "<=": 
                                            if ($item_detalle[$regla['columna']] <= $regla['opcion']) {
                                                $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                $formula_id = $regla['id'];
                                            }
                                            break;

                                        case "<>": 
                                            if ($item_detalle[$regla['columna']] != $regla['opcion']) {
                                                $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                $formula_id = $regla['id'];
                                            }
                                            break;
                                        default:
                                            break;
                                        }
                                }
            
                                break;
                            case "FormulaEscalonada":
                            
                                foreach ($formulasProveedor as $regla) {
            
                                    $desde= (float)$regla["desde"];
                                    $hasta= (float)$regla["hasta"];
            
                                    if ($clean_monto >= $desde && $clean_monto <= $hasta) {
                                        $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                        $formula_id = $regla['id'];
                                    }

                                    }
                                break;
            
                            case "FormulaMixta":
                            
                                foreach ($formulasProveedor as $regla) {
            
                                    if ($clean_monto >= $regla['desde'] && $clean_monto <= $regla['hasta']) {
                                        if (!empty($regla['operador']) && !empty($regla['opcion'])) {
                                            switch ($regla['operador']) {
                                                case ">":
                                                    if ($item_detalle[$regla[$columna]] > $regla['opcion']) {
                                                        $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                        $formula_id = $regla['id'];
                                                    }
                                                    break;
                                                case ">=": 
                                                    if ($item_detalle[$regla[$columna]] >= $regla['opcion']) {
                                                        $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                        $formula_id = $regla['id'];
                                                    }
                                                    break;
            
                                                case "==": 
                                                    if ($item_detalle[$regla[$columna]] == $regla['opcion']) {
                                                        $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                        $formula_id = $regla['id'];
                                                    }
                                                    break;
                                                case "<": 
                                                    if ($item_detalle[$regla[$columna]] < $regla['opcion']) {
                                                        $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                        $formula_id = $regla['id'];
                                                    }
                                                    break;
            
                                                case "<=": 
                                                    if ($item_detalle[$regla[$columna]] <= $regla['opcion']) {
                                                        $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                        $formula_id = $regla['id'];
                                                    }
                                                    break;
            
                                                case "<>": 
                                                    if ($item_detalle[$regla[$columna]] != $regla['opcion']) {
                                                        $comision_con_igv = calcularComisionTotal($clean_monto, $regla);
                                                        $formula_id = $regla['id'];
                                                    }
                                                    break;
                                                default:
                                                    break;
                                                }
                                        } else {
                                                fn_conci_set_status_code_response(400, "Las formulas estan incompletas. Registrar formulas en mantenimiento de proveedores", "Error");
                                        }
                                    }
                                    }
                                    break;
                                default:
                                    fn_conci_set_status_code_response(400, "No existe el metodo de formula. Registrar formulas en mantenimiento de proveedores", "Error");
                        }

                //  Generar arreglo de registro para guardar
                

                    $add_transaccion_obj = [
                        "transaccion_id" => $clean_transaction_id,
                        "transaccion_proveedor_id" => $item_detalle[$column_provider_id_externo_nombre],
                        "importacion_id" => $import_id,
                        "data_json" => $registro_completo_json,
                        "periodo_id" => $periodo_id,
                        "estado_id" => $tbl_conci_provider_state[$estado]["id"],
                        "monto" =>  $clean_monto,
                        "comision_total" =>  $clean_comision,
                        "comision_total_calculado" =>  $comision_con_igv,
                        "estado_conciliacion" =>  0,
                        "formula_id" =>  $formula_id,
                        "status" => 1,
                        "created_at" => $datetime,
                        "updated_at" => $datetime,
                        "user_created_id" => $login["id"],
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

        //  Guardar registros   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //  Consultar datos de periodo

                $selectQueryPeriodo = "SELECT IFNULL(pi.monto_proveedor,0), IFNULL(pi.proveedor_count,0), IFNULL(pi.comision_calimaco,0), IFNULL(pi.comision_proveedor,0)
                                        FROM tbl_conci_periodo pi
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
                    $selectStmtPeriodo->bind_result($proveedor_monto, $proveedor_count,$comision_calimaco, $comision_proveedor);
                    $selectStmtPeriodo->fetch();
                    $selectStmtPeriodo->free_result(); 
                    $selectStmtPeriodo->close();

                }
                $selectStmtPeriodo->close();

            //  Realizar conteo
            
            $guardados = 0;
            $insertados = 0;
            $actualizados = 0;
            $registros_actualizados = [];
            $msg_registros_actualizados = "";
            $insertados_monto = 0;
            $insertados_comision = 0;
            $registros_nuevos = [];
            $suma_insertados = 0;
            $suma_comision = 0;

            foreach ($array_add_transaccion as $transaccion) {
                $exists = isset($existing_transacciones[$transaccion["transaccion_id"]]);

                if ($exists) {
                    $actualizados++;
                    $registros_actualizados[] = $transaccion;
                } else {
                    // Insert new record
                    $registros_nuevos[] = $transaccion;
                    $suma_insertados = $suma_insertados + $transaccion["monto"];
                    $suma_comision = $suma_comision + $transaccion["comision_total"];
                    $suma_comision_calimaco = $suma_comision_calimaco + $transaccion["comision_total_calculado"];
                    $insertados++;
                }
            }

            if (count($registros_nuevos) > 0) {
                // Guardar registros nuevos en lotes
                $batch_size = 1000;
                $batches = array_chunk($registros_nuevos, $batch_size);
                foreach ($batches as $batch) {
                    fn_conci_proveedor_insert_batch($batch);
                }
            }

        //  Respuesta de guardado    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $msg_guardados_swal = $guardados == 1 ? "Guardado:\n".$guardados." registro" : "Guardados:\n".$guardados." registros";
            $msg_registrados_swal = $insertados == 1 ? "Creado:\n".$insertados : "Creados:\n".$insertados;
            $msg_actualizados_swal = $actualizados == 1 ? "Ya registrado:\n".$actualizados : "Ya registrados:\n".$actualizados;

            if ($actualizados > 0) {
                $msg_registros_actualizados .= "<strong>Registros ya registrados:</strong><br>";

                foreach ($registros_actualizados as $registro) {
                    $msg_registros_actualizados .= "Venta ID: " . $registro['transaccion_id']."<br>";
                }
            }
        //  Actualizar registro de importación   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $sql_update_importacion = "
            UPDATE tbl_conci_proveedor_importacion SET created_count = ?, updated_count = ? WHERE id = ?";

            $stmtUpdateImportacion = $mysqli->prepare($sql_update_importacion);
            $stmtUpdateImportacion->bind_param("iii", $insertados,$actualizados, $import_id);
            if (!$stmtUpdateImportacion->execute()) fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: ".$mysqli->error, "Error");
            $stmtUpdateImportacion->close();

        //  Actualizar periodo   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $proveedor_monto = $proveedor_monto + $suma_insertados;
            $proveedor_count = $proveedor_count + $insertados;
            $comision_calimaco = $comision_calimaco + $suma_comision_calimaco;

            if($tipo_archivo_id == 3){
                $comision_proveedor = $comision_proveedor + $suma_comision;
            }
            
            $sql_update_periodo = "
                UPDATE tbl_conci_periodo SET monto_proveedor = ?, proveedor_count = ?, comision_calimaco = ?, comision_proveedor = ? WHERE id = ?";

            $stmtUpdatePeriodo = $mysqli->prepare($sql_update_periodo);
            $stmtUpdatePeriodo->bind_param("didsi", $proveedor_monto,$proveedor_count, $comision_calimaco, $comision_proveedor, $periodo_id);
            if (!$stmtUpdatePeriodo->execute()) fn_conci_set_status_code_response(400, "Error en la preparación de la consulta: ".$mysqli->error, "Error");
            $stmtUpdatePeriodo->close();

        //  Mostrar reporte

        $mysqli->commit();
        $response = [
            "msg_error" => "",
            "guardados" => $guardados,
            "swal_title" => "Importación exitosa",
            "msg" => "Archivo: " . $data["nombre_archivo"] . "<br> Procesado : " . count($datos) . " registros <br> ".$msg_guardados_swal."<br><br><strong>".$msg_registrados_swal."<br>".$msg_actualizados_swal."</strong><br><br>".$msg_registros_actualizados,
        ];
        return $response;

    } catch (Exception $e) {
        $mysqli->rollback();
        fn_conci_set_status_code_response(400, $e->getMessage(), "Error");
    }
}

function fn_conci_periodo_provider_transaction_command($transaction,$provider_id){
    global $mysqli;
    global $return;

    $existing_record = fn_conci_periodo_provider_searchDuplicated($transaction['transaccion_id'],$transaction['transaccion_proveedor_id'],$provider_id,$transaction['periodo_id']); 

    if ($existing_record) {
        $fields = [];
        $types = '';
        $values = [];
        
        foreach ($transaction as $key => $value) {
            if (!in_array($key, ['transaccion_id', 'periodo_id', 'monto','importacion_id','comision_total_calculado','transaccion_proveedor_id','user_created_id', 'created_at', 'status'])) {
                $fields[] = "$key = ?";
                $types .= fn_conci_getDataType($value);
                $values[] = $value;
                }
            }
        
        $fields_str = implode(', ', $fields);
        $types .= fn_conci_getDataType($transaction['transaccion_proveedor_id']);
        $values[] = $transaction['transaccion_proveedor_id'];
        
        
        $stmt = $mysqli->prepare("UPDATE tbl_conci_proveedor_transaccion SET $fields_str WHERE transaccion_proveedor_id = ? ");
        if ($stmt) {
            $stmt->bind_param($types, ...$values);
        
            if ($stmt->execute()) {
                return ["status" => "actualizado", "registro" => $transaction];
            } else {
                fn_conci_set_status_code_response(400, "Error al actualizar el registro: " . $stmt->error, "Error");
                exit();
            }
        } else {
            fn_conci_set_status_code_response(500, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
            exit();
        }
    } else {
        $keys = implode(", ", array_keys($transaction));
        $placeholders = implode(", ", array_fill(0, count($transaction), '?'));
        $types = '';
        $values = [];
        
        foreach ($transaction as $key => $value) {
            $types .= fn_conci_getDataType($value);
            $values[] = $value;
        }
        
            // Depuración
            //error_log($keys, $placeholders, $types, $values);
        
        $stmt = $mysqli->prepare("INSERT INTO tbl_conci_proveedor_transaccion ($keys) VALUES ($placeholders)");
        if ($stmt) {
            $stmt->bind_param($types, ...$values);
        
            if ($stmt->execute()) {
                return ["status" => "insertado", "monto" => $transaction['monto'], "comision" => $transaction['comision_total_calculado']];
            } else {
                fn_conci_set_status_code_response(400, "Error al insertar nuevo registro: " . $stmt->error, "Error");
                exit();
            }
        } else {
            fn_conci_set_status_code_response(500, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
            exit();
        }
        }
}

function fn_conci_periodo_provider_searchDuplicated($transaccion_id, $transaccion_proveedor_id, $provider_id, $periodo_id){
    global $mysqli;
    global $login;
    $datetime = date("Y-m-d H:i:s");

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_transaccion pt
                                LEFT JOIN tbl_conci_proveedor_importacion pi ON pt.importacion_id = pi.id
                                WHERE pt.status = 1 AND pt.transaccion_proveedor_id = ? AND pi.proveedor_id= ? AND pi.periodo_id = ? AND pt.periodo_id = ?");
    
    if ($stmt) {
        $stmt->bind_param("siii", $transaccion_proveedor_id,  $provider_id, $periodo_id, $periodo_id);
        
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_conciliar_proveedor") {
    global $login;
    global $mysqli;
    $datetime = date("Y-m-d H:i:s");
    $import_id = $_POST["importacion_id"];
    $periodo_id = $_POST["periodo_id"];

    // Obtener listado de registros
    $selectQuery = "SELECT pt.id, pt.transaccion_proveedor_id, pt.transaccion_id, pi.proveedor_id
                    FROM tbl_conci_proveedor_transaccion pt
                    LEFT JOIN tbl_conci_proveedor_importacion pi 
                    ON pt.importacion_id = pi.id
                    WHERE pt.status = 1 AND pi.id = ? AND pt.estado_conciliacion = 0;";

    $stmt = $mysqli->prepare($selectQuery);
    if (!$stmt) {
        fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
    }
    $stmt->bind_param("i", $import_id);
    if (!$stmt->execute()) {
        fn_conci_set_status_code_response(400, "Error al ejecutar la consulta de transacción de proveedor: " . $mysqli->error, "Error");
    }
    $stmt->bind_result($id, $transaccion_proveedor_id, $transaccion_id, $provider_id);

    $data = [];
    while ($stmt->fetch()) {
        $data[] = [
            'id' => $id,
            'transaccion_proveedor_id' => $transaccion_proveedor_id,
            'transaccion_id' => $transaccion_id,
            'provider_id' => $provider_id,
        ];
    }
    $stmt->close();

    // Obtener todos los transacciones_ids para la consulta masiva
    $transaccion_ids = array_column($data, 'transaccion_id');

    // Consulta masiva para Calimaco
    if (count($transaccion_ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($transaccion_ids), '?'));

        $selectQueryCalimaco = "SELECT ct.id, ct.transaccion_id, IFNULL(ct.estado_conciliacion,0) AS estado_conciliacion, IFNULL(ct.venta_proveedor_id,'') AS venta_proveedor_id
                                FROM tbl_conci_calimaco_transaccion ct
                                WHERE ct.status = 1 AND ct.transaccion_id IN ($placeholders) AND ct.periodo_id = ?";

        $stmtCalimaco = $mysqli->prepare($selectQueryCalimaco);
        if (!$stmtCalimaco) {
            fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
        }

        $params = array_merge($transaccion_ids, [$periodo_id]);
        $stmtCalimaco->bind_param(str_repeat('s', count($params)), ...$params);
        if (!$stmtCalimaco->execute()) {
            fn_conci_set_status_code_response(400, "Error al ejecutar la consulta de transacción de Calimaco: " . $mysqli->error, "Error");
        }

        $resultCalimaco = $stmtCalimaco->get_result();
        $calimacoData = [];
        while ($row = $resultCalimaco->fetch_assoc()) {
            $calimacoData[$row['transaccion_id']] = $row;
        }
        $stmtCalimaco->close();
    }

    // Procesar datos
    $conciliados = 0;
    $no_conciliado = 0;
    $duplicados = 0;
    $registros_duplicados = [];
    $msg_registros_duplicados = "";

    foreach ($data as $item) {
        $transaccion_id = $item['transaccion_id'];
        $transaccion_proveedor_id = $item['id'];

        // Verificar si ya está registrado en venta_proveedor_id
        if (isset($calimacoData[$transaccion_id])) {
            $calimacoRow = $calimacoData[$transaccion_id];
            $venta_proveedor_ids = explode(',', $calimacoRow['venta_proveedor_id']);

            if (in_array($transaccion_proveedor_id, $venta_proveedor_ids)) {
                // Ya está registrado, omitir conciliación
                continue;
            }else{
                // Si no está registrado, actualizar el campo de venta_proveedor_id
                if ($calimacoRow['venta_proveedor_id'] === '') {
                    $new_venta_proveedor_id = $transaccion_proveedor_id;
                } else {
                    $new_venta_proveedor_id = $calimacoRow['venta_proveedor_id'] . ',' . $transaccion_proveedor_id;
                }

                // Actualizar el estado de conciliación
                    $estado_conciliacion = $calimacoRow['estado_conciliacion'];
                    $nuevo_estado_conciliacion = ($estado_conciliacion == 0) ? 1 : (($estado_conciliacion == 1) ? 2 : $estado_conciliacion);

                // Actualizar el campo venta_proveedor_id en la base de datos
                $updateQuery = "UPDATE tbl_conci_calimaco_transaccion
                                SET venta_proveedor_id = ?,
                                estado_conciliacion = ?
                                WHERE transaccion_id = ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                $updateStmt->bind_param("sis", $new_venta_proveedor_id, $nuevo_estado_conciliacion, $transaccion_id);
                if (!$updateStmt->execute()) {
                    fn_conci_set_status_code_response(400, "Error al actualizar la venta_proveedor_id: " . $mysqli->error, "Error");
                }
                $updateStmt->close();

                $updateEstadoQuery = "UPDATE tbl_conci_proveedor_transaccion
                                    SET estado_conciliacion = ?
                                    WHERE id = ?";
                $updateEstadoStmt = $mysqli->prepare($updateEstadoQuery);
                $updateEstadoStmt->bind_param("ii", $nuevo_estado_conciliacion, $transaccion_proveedor_id);
                if (!$updateEstadoStmt->execute()) {
                    fn_conci_set_status_code_response(400, "Error al actualizar el estado de conciliación: " . $mysqli->error, "Error");
                }
                $updateEstadoStmt->close();

                if($nuevo_estado_conciliacion == 1){
                    $conciliados++;
                }elseif ($nuevo_estado_conciliacion == 2) {
                    $duplicados++;
                    $registros_duplicados[] = [
                        "transaccion_id" => $transaccion_id,
                        "transaccion_proveedor_id" => $transaccion_proveedor_id
                    ];
                }
            }
        }
    }

    // Consultar datos de importación y periodo
    $selectQueryImport = "SELECT pi.reconciled_count, pi.duplicate_count
                          FROM tbl_conci_proveedor_importacion pi
                          WHERE pi.status = 1 AND pi.id = ?
                          LIMIT 1";

    $selectStmtImport = $mysqli->prepare($selectQueryImport);
    if (!$selectStmtImport) {
        fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
    }
    $selectStmtImport->bind_param("i", $import_id);
    if (!$selectStmtImport->execute()) {
        fn_conci_set_status_code_response(400, "Error al ejecutar la consulta SQL: " . $mysqli->error, "Error");
    }
    $selectStmtImport->bind_result($reconciled_count, $duplicate_count);
    $selectStmtImport->fetch();
    $selectStmtImport->close();

    // Consultar datos de periodo
    $selectQueryPeriodo = "SELECT IFNULL(pi.reconciled_count,0), IFNULL(pi.duplicate_count,0)
                          FROM tbl_conci_periodo pi
                          WHERE pi.status = 1 AND pi.id = ?
                          LIMIT 1";

    $selectStmtPeriodo = $mysqli->prepare($selectQueryPeriodo);
    if (!$selectStmtPeriodo) {
        fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
    }
    $selectStmtPeriodo->bind_param("i", $periodo_id);
    if (!$selectStmtPeriodo->execute()) {
        fn_conci_set_status_code_response(400, "Error al ejecutar la consulta SQL: " . $mysqli->error, "Error");
    }
    $selectStmtPeriodo->bind_result($periodo_reconciled_count, $periodo_duplicate_count);
    $selectStmtPeriodo->fetch();
    $selectStmtPeriodo->close();

    // Actualizar periodo
    $periodo_reconciled_count += $conciliados;
    $periodo_duplicate_count += $duplicados;

    $sql_update_periodo = "UPDATE tbl_conci_periodo SET reconciled_count = ?, duplicate_count = ? WHERE id = ?";
    $stmtUpdatePeriodo = $mysqli->prepare($sql_update_periodo);
    $stmtUpdatePeriodo->bind_param("iii", $periodo_reconciled_count, $periodo_duplicate_count, $periodo_id);
    if (!$stmtUpdatePeriodo->execute()) {
        fn_conci_set_status_code_response(400, "Error al ejecutar la actualización del periodo: " . $mysqli->error, "Error");
    }
    $stmtUpdatePeriodo->close();

    // Actualizar registro de importación
    $sql_update_importacion = "UPDATE tbl_conci_proveedor_importacion SET non_reconciled_count = ?, reconciled_count = ?, duplicate_count = ?, user_updated_id = ?, updated_at = ? WHERE id = ?";
    $stmtUpdateImportacion = $mysqli->prepare($sql_update_importacion);
    $stmtUpdateImportacion->bind_param("iiiisi", $no_conciliado, $conciliados, $duplicados, $login["id"], $datetime, $import_id);
    if (!$stmtUpdateImportacion->execute()) {
        fn_conci_set_status_code_response(400, "Error al ejecutar la actualización de la importación: " . $mysqli->error, "Error");
    }
    $stmtUpdateImportacion->close();

    // Mostrar reporte
    $msg_conciliados_swal = $conciliados == 1 ? "Conciliado:\n".$conciliados : "Conciliados:\n".$conciliados;
    $msg_duplicados_swal = $duplicados == 1 ? "Duplicado:\n".$duplicados : "Duplicados:\n".$duplicados;

    if ($duplicados > 0) {
        $msg_registros_duplicados .= "<strong>Registros duplicados:</strong><br>";
        foreach ($registros_duplicados as $registro) {
            $msg_registros_duplicados .= "Venta ID: " . $registro['transaccion_proveedor_id'] . ", Transacción ID: " . $registro['transaccion_id'] . "<br>";
        }
    }

    $response = [
        "msg_error" => "",
        "guardados" => 1,
        "swal_title" => "Conciliación exitosa",
        "msg" => "<strong>".$msg_conciliados_swal."<br>".$msg_duplicados_swal."</strong><br><br>".$msg_registros_duplicados,
    ];
    print_r(json_encode($response));
}

function fn_conci_periodo_provider_searchReconciled($mysqli, $id, $transaccion_proveedor_id, $transaccion_id, $provider_id, $periodo_id)
{
    global $login;
    $datetime = date("Y-m-d H:i:s");

    $selectQuery = "SELECT ct.id, IFNULL(ct.estado_conciliacion,0), IFNULL(ct.venta_proveedor_id,'')
                    FROM tbl_conci_calimaco_transaccion ct
                    LEFT JOIN tbl_conci_calimaco_metodo cm ON ct.metodo_id = cm.id
                    LEFT JOIN tbl_conci_proveedor p ON cm.proveedor_id = p.id
                    WHERE ct.status = 1 AND ct.transaccion_id = ? AND p.id = ? AND (ct.venta_proveedor_id IS NULL OR FIND_IN_SET(?, ct.venta_proveedor_id) = 0) AND ct.periodo_id = ?
                    LIMIT 1";
    $stmt = $mysqli->prepare($selectQuery);
    if (!$stmt) fn_conci_set_status_code_response(400, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");
    $stmt->bind_param("sisi", $transaccion_id, $provider_id, $id, $periodo_id);
    if (!$stmt->execute()) fn_conci_set_status_code_response(400, "Error al ejecutar la consulta SQL: " . $mysqli->error, "Error");
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($calimaco_id, $estado_conciliacion, $venta_proveedor_ids);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();

        
        // Preparar actualizaciones en bloque
        $sql_update_calimaco = "";
        $venta_proveedor_id = $estado_conciliacion == 0 ? $id : $venta_proveedor_ids . ',' . $id;
        $estado_conciliacion = $estado_conciliacion == 0 ? 1 : 2;
        $sql_update_calimaco = "UPDATE tbl_conci_calimaco_transaccion SET venta_proveedor_id = ?, estado_conciliacion = ?, updated_at = ?, user_updated_id = ? WHERE id = ?";
        
        $stmtConciliarCalimaco = $mysqli->prepare($sql_update_calimaco);
        if (!$stmtConciliarCalimaco) fn_conci_set_status_code_response(400, "Error en la preparación de la consulta de actualización: " . $mysqli->error, "Error");
        $stmtConciliarCalimaco->bind_param("sisi", $venta_proveedor_id, $estado_conciliacion, $datetime, $login["id"], $calimaco_id);
        if (!$stmtConciliarCalimaco->execute()) fn_conci_set_status_code_response(400, "Error al ejecutar la consulta de actualización: " . $mysqli->error, "Error");
        $stmtConciliarCalimaco->close();

        // Actualizar proveedor
        $sql_update_proveedor = "UPDATE tbl_conci_proveedor_transaccion SET estado_conciliacion = ?, updated_at = ?, user_updated_id = ? WHERE id = ?";
        $stmtConciliarProveedor = $mysqli->prepare($sql_update_proveedor);
        if (!$stmtConciliarProveedor) fn_conci_set_status_code_response(400, "Error en la preparación de la consulta de actualización: " . $mysqli->error, "Error");
        $stmtConciliarProveedor->bind_param("sii", $datetime, $login["id"], $id);
        if (!$stmtConciliarProveedor->execute()) fn_conci_set_status_code_response(400, "Error al ejecutar la consulta de actualización: " . $mysqli->error, "Error");
        $stmtConciliarProveedor->close();

        return ["status" => $estado_conciliacion == 1 ? "conciliado" : "duplicado", "calimaco_id" => $calimaco_id, "proveedor_id" => $id];
    } else {
        return ["status" => "no_conciliado"];
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_observar_proveedor") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
		$observacion = $_POST["observacion"];
        $etapa_id = 1;
        if ((int)$id > 0) {
			$error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_razon_social = "
                UPDATE tbl_conci_proveedor_transaccion
                    SET 
                        observacion = ?,     
                        updated_at = ?, 
                        user_updated_id = ?
                    WHERE id = ?";

            $stmt = $mysqli->prepare($sql_update_razon_social);


            if ($stmt) {
                $stmt->bind_param("ssii", 
                                            $observacion,
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
            $result["error"] = "La transacción no esta activa. Contactarse con soporte";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_observar_calimaco") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
		$observacion = $_POST["observacion"];
        $etapa_id = 1;
        if ((int)$id > 0) {
			$error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_razon_social = "
                UPDATE tbl_conci_calimaco_transaccion
                    SET 
                        observacion = ?,     
                        updated_at = ?, 
                        user_updated_id = ?
                    WHERE id = ?";

            $stmt = $mysqli->prepare($sql_update_razon_social);


            if ($stmt) {
                $stmt->bind_param("ssii", 
                                            $observacion,
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
            $result["error"] = "La transacción no esta activa. Contactarse con soporte";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

function fn_conci_updatePeriodoDevolucion($mysqli, $periodo_id, $usuario_id, $fecha,$monto_devolucion,$comision_devolucion, $devolucion_count){


    $query_update_transaccion = "UPDATE tbl_conci_periodo
                                        SET 
                                            monto_devolucion = ?,
                                            comision_devolucion = ?,
                                            devolucion_count = ?,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                             id = ?";
    $stmt = $mysqli->prepare($query_update_transaccion);
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error al preparar la consulta para anulación: " . $mysqli->error;
    } else {
        $stmt->bind_param("ddiisi", $monto_devolucion, $comision_devolucion, $devolucion_count,$usuario_id, $fecha, $periodo_id);
        if ($stmt->execute()) {
            $stmt->close();

            return true;
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
                       
        } else {
            $result["http_code"] = 400;
            $result["error"] = "Error al anular la transacción: " . $mysqli->error;
        }
    }
}
//  IMPORTAR ARCHIVO CALIMACO   ////////////////////////////////////////////////////////////////////////////////////////////

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
    $currentColumn = 0;
    $errores = [];
    foreach ($file_columns as $value) {
        $cellValue = trim($worksheet->getCell($file_columns[$currentColumn] .  $start_line)->getValue());
        if ($cellValue == "") {
            $errores[] = "-Columna  " . $file_columns[$currentColumn] . " : Se esperaba <strong>" . array_keys($fields)[$currentColumn] . "</strong>";
            $currentColumn++;
            continue;
        }
        if ($cellValue != array_keys($fields)[$currentColumn]) {
            $errores[] = "-Columna " . $file_columns[$currentColumn] . " : " . $cellValue . ". Se esperaba <strong>" . array_keys($fields)[$currentColumn] . "</strong>";
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

function fn_conci_provider_check_headers($worksheet, $fields, $file_columns, $start_line){
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

function calcularComisionTotal($monto, $regla) {
    $comision_porcentual = ($monto * $regla['comision_porcentual']) / 100;
    $comision_total = $comision_porcentual + $regla['comision_fija'];
    $comision_con_igv = $comision_total * (1 + $regla['igv'] / 100);
    return $comision_con_igv;
}

function obtenerFormulaFijaProveedor($mysqli, $provider_id) {

    $stmtFormulas = $mysqli->prepare("
        SELECT 
            f.id,
            c.nombre,
            o.nombre_bd,
            op.nombre,
            f.comision_porcentual,
            f.comision_fija,
            f.igv
        FROM tbl_conci_proveedor_formula f
        LEFT JOIN tbl_conci_proveedor_columna c
        ON c.id = f.columna_id
        LEFT JOIN tbl_conci_formula_operador o
        ON o.id = f.operador_id
        LEFT JOIN tbl_conci_formula_opcion op
        ON op.id = f.opcion_id
        WHERE f.proveedor_id = ?
        AND f.status = 1;
    ");

    if (!$stmtFormulas) {
        throw new Exception("Error al preparar la consulta de estados del proveedor.");
    }

    $stmtFormulas->bind_param("i", $provider_id);

    if (!$stmtFormulas->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmtFormulas->bind_result($id, $columna, $operador, $opcion, $comision_porcentual, $comision_fija, $igv);

    $formulas = [];

    while ($stmtFormulas->fetch()) {
        $formulas[] = [
            'id' => $id,
            'columna' => $columna,
            'operador' => $operador,
            'opcion' => $opcion,
            'comision_porcentual' => $comision_porcentual,
            'comision_fija' => $comision_fija,
            'igv' => $igv
        ];
    }

    $stmtFormulas->close();

    return $formulas;
}

function obtenerFormulaEscalonadaProveedor($mysqli, $provider_id) {

    $stmtFormulas = $mysqli->prepare("
        SELECT 
            id,
            desde,
            hasta,
            comision_porcentual,
            comision_fija,
            igv
        FROM tbl_conci_proveedor_formula
        WHERE proveedor_id = ? 
        AND status = 1
    ");

    if (!$stmtFormulas) {
        throw new Exception("Error al preparar la consulta de estados del proveedor.");
    }

    $stmtFormulas->bind_param("i", $provider_id);

    if (!$stmtFormulas->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmtFormulas->bind_result($id, $desde, $hasta, $comision_porcentual, $comision_fija, $igv);

    $formulas = [];

    while ($stmtFormulas->fetch()) {
        $formulas[] = [
            'id' => $id,
            'desde' => $desde,
            'hasta' => $hasta,
            'comision_porcentual' => $comision_porcentual,
            'comision_fija' => $comision_fija,
            'igv' => $igv
        ];
    }

    $stmtFormulas->close();

    return $formulas;
}

function obtenerFormulaMixtaProveedor($mysqli, $provider_id) {

    $stmtFormulas = $mysqli->prepare("
        SELECT 
            f.id,
            c.nombre,
            o.nombre_bd,
            op.nombre,
            f.desde,
            f.hasta,
            f.comision_porcentual,
            f.comision_fija,
            f.igv
        FROM tbl_conci_proveedor_formula f
        LEFT JOIN tbl_conci_proveedor_columna c
        ON c.id = f.columna_id
        LEFT JOIN tbl_conci_formula_operador o
        ON o.id = f.operador_id
        LEFT JOIN tbl_conci_formula_opcion op
        ON op.id = f.opcion_id
        WHERE f.proveedor_id = ?
        AND f.status = 1
    ");

    if (!$stmtFormulas) {
        throw new Exception("Error al preparar la consulta de estados del proveedor.");
    }

    $stmtFormulas->bind_param("i", $provider_id);

    if (!$stmtFormulas->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmtFormulas->bind_result($id, $columna, $operador, $opcion, $desde, $hasta, $comision_porcentual, $comision_fija, $igv);

    $formulas = [];

    while ($stmtFormulas->fetch()) {
        $formulas[] = [
            'id' => $id,
            'columna' => $columna,
            'operador' => $operador,
            'opcion' => $opcion,
            'desde' => $desde,
            'hasta' => $hasta,
            'comision_porcentual' => $comision_porcentual,
            'comision_fija' => $comision_fija,
            'igv' => $igv
        ];
    }

    $stmtFormulas->close();

    return $formulas;
}

function fn_conci_get_data_detail($worksheet, $fields, $currentRow, $currentColumn, $file_columns){
    $data_detalle = array();
    $cellValue = "";
    $data_row = array();
    $firstColumn = $currentColumn;
    $row_val = 1;
    do {
        $cellValue = trim($worksheet->getCell($file_columns[$currentColumn] . $currentRow)->getValue());
        if ($cellValue != "") {
            foreach ($fields as $key => $value) {
                switch ($value) {
                    case "datetime":
                        $data_row[$key] = excel_date_to_php($worksheet->getCell($file_columns[$currentColumn] . $currentRow));
                        break;
                    case "decimal":
                        $data_row[$key] = number_format((float)trim($worksheet->getCell($file_columns[$currentColumn] . $currentRow)->getValue()), 3, '.', '');
                        break;
                    default:
                        $data_row[$key] = trim($worksheet->getCell($file_columns[$currentColumn] . $currentRow)->getValue());
                }
                $currentColumn++;
            }
            array_push($data_detalle, $data_row);
        }
        $currentColumn = $firstColumn;
        $currentRow++;
    } while ($cellValue != "");

    return ["data_detalle" => $data_detalle,
        "rows" => count($data_detalle)
    ];
}

function fn_conci_provider_get_data_detail($worksheet, $fields, $currentRow, $currentColumn, $file_columns) {
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
                        SELECT pc.id, pc.nombre,pc.prefijo,ars.simbolo,pc.sufijo,pc.nombreColumna_json,pc.formato_id, pc.orden
                        FROM tbl_conci_proveedor_columna pc
                        LEFT JOIN tbl_conci_archivo_separador ars ON pc.separador_id = ars.id
                        WHERE pc.proveedor_id = ? AND pc.tipo_archivo_id = ?
                        ORDER BY pc.id
                    ) AS subquery
                    WHERE subquery.id IN (
                        SELECT pc.id
                        FROM tbl_conci_proveedor_columna pc
                        LEFT JOIN tbl_conci_calimaco_columna cc ON pc.columna_id = cc.id
                        WHERE cc.nombre_bd = ? AND pc.tipo_archivo_id = ?
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_periodo_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $periodo_id = $_POST["periodo_id"];

        if ((int)$periodo_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_devolucion = "UPDATE tbl_conci_periodo
                                        SET 
                                            status = 0,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                            id = ?";
            $stmt = $mysqli->prepare($query_update_devolucion);
            if (!$stmt) {
                $result = [
                "http_code" => 500,
                "error" => "Error en la preparación de la consulta: " . $mysqli->error
                ];
                echo json_encode($result);
                exit();
            }
            $stmt->bind_param("iss", $usuario_id, $fecha, $periodo_id);

            if ($stmt->execute()) {
                $stmt->close();

                $cleanResult = fn_conci_venta_cleanCalimacoPeriodo($mysqli, $periodo_id);

                if ($cleanResult["http_code"] != 200) {
                    echo json_encode($cleanResult);
                    exit();
                }

                $cleanResultProveedor = fn_conci_venta_cleanCalimacoPeriodoProveedor($mysqli, $periodo_id);

                if ($cleanResultProveedor["http_code"] != 200) {
                    echo json_encode($cleanResultProveedor);
                    exit();
                }

                $result = ["http_code" => 200];
            } else {
                $result = [
                "http_code" => 500,
                "error" => "Error en la ejecución de la consulta de actualización" 
                ];
            }
        } else {
            $result = [
            "http_code" => 400,
            "error" => "El ID de la importación no es válido."
            ];
        }
    } else {
            $result = [
            "http_code" => 400,
            "error" => "La sesión ha caducado. Por favor, inicie sesión nuevamente."
            ];
    }

    echo json_encode($result);
    exit();
}


?>