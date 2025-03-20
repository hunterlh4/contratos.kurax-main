<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");
include("/var/www/html/sys/helpers.php");
require_once '../phpexcel/classes/PHPExcel.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


//error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_test_importar") {

    $datos = json_decode($_POST['datos'], true);
    $archivo_tipo=$_POST["archivo_tipo"];
    //set_status_code_response(400, "Columnas:". $datos, "Error");

    //  Inicio Verificar si se tienen las columnas requeridas

        $columnaSincronia = json_decode($_POST['columnaSincronia'], true);

        
        $columnaSincronia = array_filter($columnaSincronia, function($columna) {
            return $columna['id'] != 0;
            });
            
        $columnaSincronia = array_values($columnaSincronia);
            

        $stmt = $mysqli->prepare("SELECT id FROM tbl_conci_calimaco_columna WHERE status = 1 AND sincronia_$archivo_tipo=1");

        if (!$stmt) {
            set_status_code_response(400, "Error en la preparación de la consulta", "Error");
            }

        $stmt->execute();
        $columnasBD = [];

        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $columnasBD[] = $li['id'];
            }

        if ($mysqli->error) {
            set_status_code_response(400, "Error en la consulta", "Error");
            }

        $columnasNoEncontradas = array_diff($columnasBD, $columnaSincronia);

        if (!empty($columnasNoEncontradas)) {

            $idsNoEncontrados = implode(",", array_map('intval', $columnasNoEncontradas));

            $stmt = $mysqli->prepare("SELECT nombre FROM tbl_conci_calimaco_columna WHERE status = 1 AND id IN ($idsNoEncontrados)");
            if (!$stmt) {
                set_status_code_response(400, "Error en la preparación de la consulta para obtener nombres".$idsNoEncontrados, "Error");
                return;
                }

            $stmt->execute();
            $result_set = $stmt->get_result();

            $nombresNoEncontrados = [];
            while ($li = $result_set->fetch_assoc()) {
                $nombresNoEncontrados[] = $li['nombre'];
                }

            $nombresNoEncontradosStr = implode(", ", $nombresNoEncontrados);

            set_status_code_response(400, "No se registraron las siguientes columnas calimaco: '".$nombresNoEncontradosStr."'", "Faltan columnas Calimaco");
            die();
        }

    //  Consultar separador

        $separador_id = $_POST["separador"];

        $selectQueryFormatoArchivo = "SELECT IFNULL(simbolo, '') FROM tbl_conci_archivo_separador WHERE id = ? LIMIT 1";

        $selectStmtFormato = $mysqli->prepare($selectQueryFormatoArchivo);
        $selectStmtFormato->bind_param("i", $separador_id);

        if (!$selectStmtFormato)  set_status_code_response(500, "Error en la preparación de la sentencia SQL: " . $mysqli->error, "Error");

        $selectStmtFormato->execute();
        $selectStmtFormato->store_result();

        if ($selectStmtFormato->num_rows > 0) {
            $selectStmtFormato->bind_result($separador);
            $selectStmtFormato->fetch();
        }

    //  Columnas de archivo

        $formato_query = "SELECT id,tipo FROM tbl_conci_columna_tipo WHERE status = 1";

        $stmtFormato = $mysqli->prepare($formato_query);

        if (!$stmtFormato) set_status_code_response(400, "No existen registros de formatos. Contactarse con soporte.", "Error");
        
        $stmtFormato->execute();
        $result = $stmtFormato->get_result();
            
        $formato = [];
        while ($row = $result->fetch_assoc()) {
            $formato[$row["id"]] = $row["tipo"];
        }
        
        $stmtFormato->close();

        // Procesar los datos recibidos para generar $fields_provider
        $fields_provider = [];
        foreach ($datos as $dato) {
            foreach ($dato as $nombre => $formato_id) {
                $fields_provider[] = [
                    'nombre' => $nombre,
                    'formato' => $formato[$formato_id]
                ];
            }
        }
        
    // Generar $file_columns basado en el número de nombres en $datos

        $numero_columnas = count($fields_provider);
        $file_columns = range('A', chr(ord('A') + $numero_columnas - 1));
            
    //  Cargar archivo para su verificación

        $linea = $_POST["lineaInicio"];

        $data = cargar_archivo($fields_provider, $file_columns, $separador);

        if (count($data["data_detalle"]) == 0) {
                set_status_code_response(400, "No hay registros en el archivo.".$linea, "El archivo no coincide debido a los siguientes errores:");
            }
        
        $response = [
            "msg_error" => "",
            "guardados" => $guardados,
            "swal_title" => "El archivo de ".$archivo_tipo." coincide correctamente",
            "msg" => ""
        ];

        print_r(json_encode($response));

}


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array();

    if ((int)$usuario_id > 0) {
        (int)$proveedor_id = $_POST["form_modal_conci_mant_proveedor_id"];
        
		$nombre = trim($_POST["form_modal_conci_mant_proveedor_param_nombre"]);
        $nombre_corto = trim($_POST["form_modal_conci_mant_proveedor_param_nombre_corto"]);
        $tipo_calculo_id = $_POST["form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id"];
        $tipo_formula_metodo = $_POST["form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id"];
        $tipo_importacion_metodo = $_POST["form_modal_conci_mant_proveedor_param_tipo_importacion_id"];
        $columna_conciliacion_id = $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_id"];
        $comision_moneda_id = $_POST["form_modal_conci_mant_proveedor_liquidacion_moneda_id"];

        if($tipo_importacion_metodo == "ColumnasArchivoCombinado"){
            $columna_conciliacion_id = $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_id"];

        }else{
            $columna_conciliacion_id = $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_id"];

        }

        try {
            $mysqli->begin_transaction();


            if ((int)$proveedor_id > 0) {
                
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                    //  Estado del proveedor

                    $stmt = $mysqli->prepare("
                        SELECT 
                            p.nombre,
                            p.nombre_corto,
                            p.tipo_importacion_id,
                            p.tipo_formula_id       
                        FROM tbl_conci_proveedor p
                        WHERE p.id=?
                        LIMIT 1");

                    $stmt->bind_param("i", $proveedor_id);
                    $stmt->execute();
                    $stmt->bind_result(
                                        $nombre_anterior, 
                                        $nombre_corto_anterior,
                                        $tipo_importacion_id_anterior,
                                        $tipo_formula_id_anterior);
                    $stmt->fetch();
                    $stmt->close();

                //  Identificar id del metodo de importación

                    $selectTipoImportacionQuery = "SELECT 
                                        id
                                    FROM tbl_conci_importacion_tipo 
                                    WHERE status = 1 AND metodo = ? 
                                    LIMIT 1";

                    $selectTipoImportacionStmt = $mysqli->prepare($selectTipoImportacionQuery);
                    $selectTipoImportacionStmt->bind_param("s", $tipo_importacion_metodo);
                    if (!$selectTipoImportacionStmt->execute()) throw new Exception("Error al preparar la consulta de tipo de importación del proveedor. Comunicarse con soporte.");

                    $selectTipoImportacionStmt->store_result();
                    $selectTipoImportacionStmt->bind_result($tipo_importacion_id);
                    $selectTipoImportacionStmt->fetch();
                    if ($selectTipoImportacionStmt->num_rows <= 0) throw new Exception("No existe el método de importación. Comunicarse con soporte.");
                    
                    //  Identificar id del metodo de formula de comisión

                    $selectTipoFormulaQuery = "SELECT 
                                    id
                                FROM tbl_conci_formula_tipo 
                                WHERE status = 1 AND metodo = ? 
                                LIMIT 1";

                    $selectTipoImportacionStmt = $mysqli->prepare($selectTipoFormulaQuery);
                    $selectTipoImportacionStmt->bind_param("s", $tipo_formula_metodo);
                    if (!$selectTipoImportacionStmt->execute()) throw new Exception("Error al preparar la consulta de tipo de fórmula del proveedor. Comunicarse con soporte.");

                    $selectTipoImportacionStmt->store_result();
                    $selectTipoImportacionStmt->bind_result($tipo_formula_id);
                    $selectTipoImportacionStmt->fetch();
                    if ($selectTipoImportacionStmt->num_rows <= 0) throw new Exception("No existe el método de fórmula de comisión. Comunicarse con soporte.");
                
                //  Actualizar datos del proveedor

                    $sql_insert_proveedor = "
                            UPDATE tbl_conci_proveedor
                            SET 
                                nombre = ?, 
                                nombre_corto = ?, 
                                comision_moneda_id = ?,
                                tipo_importacion_id = ?, 
                                tipo_calculo_id = ?,
                                tipo_formula_id = ?,
                                columna_conciliacion_id = ?,             
                                updated_at = ?, 
                                user_updated_id = ?
                            WHERE id = ?
                    ";

                    $stmtUpdateProveedor = $mysqli->prepare($sql_insert_proveedor);


                    if ($stmtUpdateProveedor) {
                        $stmtUpdateProveedor->bind_param("ssiiiiisii", 
                                                $nombre, 
                                                $nombre_corto, 
                                                $comision_moneda_id, 
                                                $tipo_importacion_id,
                                                $tipo_calculo_id,
                                                $tipo_formula_id,
                                                $columna_conciliacion_id,
                                                $fecha,
                                                $usuario_id,
                                                $proveedor_id
                                                );

                    try {
                        $stmtUpdateProveedor->execute();
                        $result["http_code"] = 200;
                        $result["status"] = "Datos obtenidos de gestión.";
                    } catch (Exception $e) {
                        $result["http_code"] = 400;
                        $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                        echo json_encode($result);
                        exit;
                    }

                    $stmtUpdateProveedor->close();

                //  Registrar personalización de importación y columnas de archivos
                    fn_conci_provider_cleanImportacion($mysqli, $proveedor_id, $fecha, $usuario_id);
                    fn_conci_provider_cleanColumna($mysqli, $proveedor_id, $fecha, $usuario_id);

                    if($tipo_importacion_id_anterior != $tipo_importacion_id){
                        fn_personalizarImportacion($mysqli, $tipo_importacion_metodo, $proveedor_id, $fecha, $usuario_id);
                    }else{
                        fn_personalizarImportacion_Update($mysqli, $tipo_importacion_metodo, $proveedor_id, $fecha, $usuario_id);
                    }

                    
                //  Registrar formulas de Comisión
                    fn_conci_provider_cleanFormulas($mysqli, $proveedor_id, $fecha, $usuario_id);

                    fn_personalizarFormulas_Update($mysqli, $tipo_formula_metodo, $proveedor_id, $fecha, $usuario_id);


                //  Registrar Cuentas bancarias

                    fn_conci_provider_cleanCuentaBancaria($mysqli, $proveedor_id, $fecha, $usuario_id);

                    $cuentasBancariaJSON = $_POST['cuentasBancariaJSON'];
                    $cuentasBancaria = json_decode($cuentasBancariaJSON, true);

                    foreach ($cuentasBancaria as $cuentaBancaria) {
                        if($cuentaBancaria['id'] == 0){
                            fn_insertarCuentaBancaria($mysqli, 
                                                            $proveedor_id,
                                                            $cuentaBancaria['banco'], 
                                                            $cuentaBancaria['moneda'], 
                                                            $cuentaBancaria['cuentacorriente'], 
                                                            $cuentaBancaria['cuentainterbancaria'],
                                                            $fecha, 
                                                            $usuario_id);
                        }else{
                            fn_updateCuentaBancaria($mysqli, 
                                                            $cuentaBancaria['banco'], 
                                                            $cuentaBancaria['moneda'], 
                                                            $cuentaBancaria['cuentacorriente'], 
                                                            $cuentaBancaria['cuentainterbancaria'],
                                                            $fecha, 
                                                            $usuario_id,
                                                            $cuentaBancaria['id']);
                        }
                    }

                //  Insertar Estados de proveedor
                    fn_conci_provider_cleanEstados($mysqli, $proveedor_id, $fecha, $usuario_id);

                    $columnasEstadosJSON = $_POST['columnasEstadosJSON'];
                    $columnasEstados = json_decode($columnasEstadosJSON, true);

                    foreach ($columnasEstados as $columnaEstado) {
                        if($columnaEstado['id'] == 0){

                            fn_insertarEstado($mysqli, 
                                $proveedor_id,
                                $columnaEstado['nombre'], 
                                $columnaEstado['estado'], 
                                $fecha, 
                                $usuario_id);
                        }else{
                            fn_updateEstado($mysqli, 
                                $columnaEstado['nombre'], 
                                $columnaEstado['estado'], 
                                $fecha, 
                                $usuario_id,
                                $columnaEstado['id']);
                        }
                        }

                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
                    echo json_encode($result);
                    exit;
                }

            }else{
                    
                //  Inicio Verificar si se tienen las columnas requeridas

                /*
                    switch($tipo_importacion_metodo){
                        case "ColumnasArchivoCombinado":

                            $columnaSincronia = json_decode($_POST['columnasArchivoCombinadoJSON'], true);

                            $archivo_tipo ="combinado";

                            $columnaSincronia = array_filter($columnaSincronia, function($columna) {
                                return $columna['id'] != 0;
                                });
                                
                            $columnaSincronia = array_values($columnaSincronia);
                                
        
                            $stmt = $mysqli->prepare("SELECT id FROM tbl_conci_calimaco_columna WHERE status = 1 AND sincronia_$archivo_tipo=1");
        
                            if (!$stmt)  throw new Exception("Error al consultar las columnas obligatorias.");
        
                            $stmt->execute();
                            $columnasBD = [];
        
                            $result_set = $stmt->get_result();
        
                            while ($li = $result_set->fetch_assoc()) {
                                $columnasBD[] = $li['id'];
                                }
        
                            if ($mysqli->error) throw new Exception("Error en la consulta.");
        
                            $columnasNoEncontradas = array_diff($columnasBD, $columnaSincronia);
        
                            if (!empty($columnasNoEncontradas)) {
        
                                $idsNoEncontrados = implode(",", array_map('intval', $columnasNoEncontradas));
        
                                $stmt = $mysqli->prepare("SELECT nombre FROM tbl_conci_calimaco_columna WHERE status = 1 AND id IN ($idsNoEncontrados)");
                                if (!$stmt) throw new Exception("Error al consultar el nombre de las columnas obligatorias.");
        
                                $stmt->execute();
                                $result_set = $stmt->get_result();
        
                                $nombresNoEncontrados = [];
                                while ($li = $result_set->fetch_assoc()) {
                                    $nombresNoEncontrados[] = $li['nombre'];
                                    }
        
                                $nombresNoEncontradosStr = implode(", ", $nombresNoEncontrados);
        
                                $stmt->close();
                                throw new Exception("No se registraron las siguientes columnas calimaco para el archivo combinado: '".$nombresNoEncontradosStr."'");
                            }
                        break;

                        case "ColumnasArchivosIndependientes":

                            $columnaSincroniaVenta = json_decode($_POST['columnasArchivoVentaJSON'], true);
                            $archivo_tipo ="venta";

                            $columnaSincroniaVenta = array_filter($columnaSincroniaVenta, function($columna) {
                                return $columna['id'] != 0;
                                });
                                
                            $columnaSincroniaVenta = array_values($columnaSincroniaVenta);
                                
        
                            $stmt = $mysqli->prepare("SELECT id FROM tbl_conci_calimaco_columna WHERE status = 1 AND sincronia_$archivo_tipo=1");
        
                            if (!$stmt)  throw new Exception("Error al consultar las columnas obligatorias.");
        
                            $stmt->execute();
                            $columnasBD = [];
        
                            $result_set = $stmt->get_result();
        
                            while ($li = $result_set->fetch_assoc()) {
                                $columnasBD[] = $li['id'];
                                }
        
                            if ($mysqli->error) {
                                set_status_code_response(400, "Error en la consulta", "Error");
                                }
        
                            $columnasNoEncontradas = array_diff($columnasBD, $columnaSincroniaVenta);
        
                            if (!empty($columnasNoEncontradas)) {
        
                                $idsNoEncontrados = implode(",", array_map('intval', $columnasNoEncontradas));
        
                                $stmt = $mysqli->prepare("SELECT nombre FROM tbl_conci_calimaco_columna WHERE status = 1 AND id IN ($idsNoEncontrados)");
                                if (!$stmt) throw new Exception("Error al consultar el nombre de las columnas obligatorias.");
        
                                $stmt->execute();
                                $result_set = $stmt->get_result();
        
                                $nombresNoEncontrados = [];
                                while ($li = $result_set->fetch_assoc()) {
                                    $nombresNoEncontrados[] = $li['nombre'];
                                    }
        
                                $nombresNoEncontradosStr = implode(", ", $nombresNoEncontrados);
                                $stmt->close();

                                throw new Exception("No se registraron las siguientes columnas calimaco para el archivo de venta: '".$nombresNoEncontradosStr."'");
                            }

                            $columnaSincroniaLiquidacion = json_decode($_POST['columnasArchivoLiquidacionJSON'], true);
                            $archivo_tipo ="liquidacion";

                            $columnaSincroniaLiquidacion = array_filter($columnaSincroniaLiquidacion, function($columna) {
                                return $columna['id'] != 0;
                                });
                                
                            $columnaSincroniaLiquidacion = array_values($columnaSincroniaLiquidacion);
                                
        
                            $stmt = $mysqli->prepare("SELECT id FROM tbl_conci_calimaco_columna WHERE status = 1 AND sincronia_$archivo_tipo=1");
        
                            if (!$stmt)  throw new Exception("Error al consultar las columnas obligatorias.");
        
                            $stmt->execute();
                            $columnasBD = [];
        
                            $result_set = $stmt->get_result();
        
                            while ($li = $result_set->fetch_assoc()) {
                                $columnasBD[] = $li['id'];
                                }
        
                            if ($mysqli->error) {
                                set_status_code_response(400, "Error en la consulta", "Error");
                                }
        
                            $columnasNoEncontradas = array_diff($columnasBD, $columnaSincroniaLiquidacion);
        
                            if (!empty($columnasNoEncontradas)) {
        
                                $idsNoEncontrados = implode(",", array_map('intval', $columnasNoEncontradas));
        
                                $stmt = $mysqli->prepare("SELECT nombre FROM tbl_conci_calimaco_columna WHERE status = 1 AND id IN ($idsNoEncontrados)");
                                if (!$stmt) throw new Exception("Error al consultar el nombre de las columnas obligatorias.");
        
                                $stmt->execute();
                                $result_set = $stmt->get_result();
        
                                $nombresNoEncontrados = [];
                                while ($li = $result_set->fetch_assoc()) {
                                    $nombresNoEncontrados[] = $li['nombre'];
                                    }
        
                                $nombresNoEncontradosStr = implode(", ", $nombresNoEncontrados);
                                $stmt->close();

                                throw new Exception("No se registraron las siguientes columnas calimaco para el archivo de liquidación: '".$nombresNoEncontradosStr."'",);
                            }

                        break;
                        default:
                        break;
                    }
                */
                
                //  Identificar id del metodo de importación

                    $selectTipoImportacionQuery = "SELECT 
                                    id
                                FROM tbl_conci_importacion_tipo 
                                WHERE status = 1 AND metodo = ? 
                                LIMIT 1";

                    $selectTipoImportacionStmt = $mysqli->prepare($selectTipoImportacionQuery);
                    $selectTipoImportacionStmt->bind_param("s", $tipo_importacion_metodo);
                    if (!$selectTipoImportacionStmt->execute()) throw new Exception("Error al preparar la consulta de tipo de importación del proveedor. Comunicarse con soporte.");

                    $selectTipoImportacionStmt->store_result();
                    $selectTipoImportacionStmt->bind_result($tipo_importacion_id);
                    $selectTipoImportacionStmt->fetch();
                    if ($selectTipoImportacionStmt->num_rows <= 0) throw new Exception("No existe el método de importación. Comunicarse con soporte.");

                //  Identificar id del metodo de formula de comisión

                    $selectTipoFormulaQuery = "SELECT 
                                    id
                                FROM tbl_conci_formula_tipo 
                                WHERE status = 1 AND metodo = ? 
                                LIMIT 1";

                    $selectTipoImportacionStmt = $mysqli->prepare($selectTipoFormulaQuery);
                    $selectTipoImportacionStmt->bind_param("s", $tipo_formula_metodo);
                    if (!$selectTipoImportacionStmt->execute()) throw new Exception("Error al preparar la consulta de tipo de fórmula del proveedor. Comunicarse con soporte.");

                    $selectTipoImportacionStmt->store_result();
                    $selectTipoImportacionStmt->bind_result($tipo_formula_id);
                    $selectTipoImportacionStmt->fetch();
                    if ($selectTipoImportacionStmt->num_rows <= 0) throw new Exception("No existe el método de fórmula de comisión. Comunicarse con soporte.");
                
                //  Registrar datos del proveedor

                    $sql_insert_proveedor = "
                            INSERT INTO tbl_conci_proveedor (
                                nombre,
                                nombre_corto,
                                comision_moneda_id,
                                tipo_importacion_id,
                                tipo_calculo_id,
                                tipo_formula_id,
                                columna_conciliacion_id,
                                status,
                                created_at,
                                user_created_id
                            )  
                            VALUES (?,?,?,?,?,?,?,1,?,?)
                        ";

                    $stmtInsertProveedor = $mysqli->prepare($sql_insert_proveedor);
                    $stmtInsertProveedor->bind_param("ssiiiiisi", 
                                                                    $nombre, 
                                                                    $nombre_corto,
                                                                    $comision_moneda_id,
                                                                    $tipo_importacion_id,
                                                                    $tipo_calculo_id,
                                                                    $tipo_formula_id,
                                                                    $columna_conciliacion_id,                        
                                                                    $fecha,
                                                                    $usuario_id
                    );

                    if (!$stmtInsertProveedor->execute()) throw new Exception("Error al guardar al proveedor. Comuníquese con soporte.");

                    $proveedor_id = $mysqli->insert_id;
                    $stmtInsertProveedor->close();

                //  Registrar personalización de importación y columnas de archivos

                    fn_personalizarImportacion($mysqli, $tipo_importacion_metodo, $proveedor_id, $fecha, $usuario_id);

            
                //  Registrar formulas de Comisión

                    //fn_personalizarFormulas($mysqli, $tipo_formula_metodo, $proveedor_id, $fecha, $usuario_id);

                //  Registrar Cuentas bancarias

                    $cuentasBancariaJSON = $_POST['cuentasBancariaJSON'];
                    $cuentasBancaria = json_decode($cuentasBancariaJSON, true);

                    foreach ($cuentasBancaria as $cuentaBancaria) {
                        fn_insertarCuentaBancaria($mysqli, 
                            $proveedor_id,
                            $cuentaBancaria['banco'], 
                            $cuentaBancaria['moneda'], 
                            $cuentaBancaria['cuentacorriente'], 
                            $cuentaBancaria['cuentainterbancaria'],
                            $fecha, 
                            $usuario_id);
                        }
                //  Insertar Estados de proveedor

                    $columnasEstadosJSON = $_POST['columnasEstadosJSON'];
                    $columnasEstados = json_decode($columnasEstadosJSON, true);

                    foreach ($columnasEstados as $columnaEstado) {
                        fn_insertarEstado($mysqli, 
                            $proveedor_id,
                            $columnaEstado['nombre'], 
                            $columnaEstado['estado'], 
                            $fecha, 
                            $usuario_id);
                        }


            }
            $mysqli->commit();
            $result["http_code"] = 200;
            $result["titulo"] = "Creación exitosa.";
            $result["descripcion"] = "El proveedor se registro éxitosamente";
            echo json_encode($result);
            exit;

        } catch (Exception $e) {
            $result["http_code"] = 400;
            $result["error"] = $e->getMessage();
            echo json_encode($result);
            exit;
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

function fn_conci_provider_cleanImportacion($mysqli, $provider_id, $fecha, $usuario_id){

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_archivo_formato
                                WHERE status = 1 AND proveedor_id= ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $provider_id);
        
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if($count > 0){
                $sql_update = "UPDATE tbl_conci_archivo_formato SET status = 0, updated_at = ?, user_updated_id = ? WHERE status = 1 AND proveedor_id = ?";

                $stmtClean = $mysqli->prepare($sql_update);
                $stmtClean->bind_param("sii", $fecha, $usuario_id, $provider_id);
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

function fn_conci_provider_cleanColumna($mysqli, $provider_id, $fecha, $usuario_id){

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_columna
                                WHERE status = 1 AND proveedor_id= ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $provider_id);
        
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if($count > 0){
                $sql_update = "UPDATE tbl_conci_proveedor_columna SET status = 0, updated_at = ?, user_updated_id = ? WHERE status = 1 AND proveedor_id = ?";

                $stmtClean = $mysqli->prepare($sql_update);
                $stmtClean->bind_param("sii", $fecha, $usuario_id, $provider_id);
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

function fn_conci_provider_cleanFormulas($mysqli, $provider_id, $fecha, $usuario_id){

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_formula
                                WHERE status = 1 AND proveedor_id= ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $provider_id);
        
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if($count > 0){
                $sql_update = "UPDATE tbl_conci_proveedor_formula SET status = 0, updated_at = ?, user_updated_id = ? WHERE status = 1 AND proveedor_id = ?";

                $stmtClean = $mysqli->prepare($sql_update);
                $stmtClean->bind_param("sii", $fecha, $usuario_id, $provider_id);
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

function fn_conci_provider_cleanCuentaBancaria($mysqli, $provider_id, $fecha, $usuario_id){

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_cuenta_bancaria
                                WHERE status = 1 AND proveedor_id= ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $provider_id);
        
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if($count > 0){
                $sql_update = "UPDATE tbl_conci_proveedor_cuenta_bancaria SET status = 0, updated_at = ?, user_updated_id = ? WHERE status = 1 AND proveedor_id = ?";

                $stmtClean = $mysqli->prepare($sql_update);
                $stmtClean->bind_param("sii", $fecha, $usuario_id, $provider_id);
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

function fn_conci_provider_cleanEstados($mysqli, $provider_id, $fecha, $usuario_id){

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM tbl_conci_proveedor_estado
                                WHERE status = 1 AND proveedor_id= ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $provider_id);
        
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if($count > 0){
                $sql_update = "UPDATE tbl_conci_proveedor_estado SET status = 0, updated_at = ?, user_updated_id = ? WHERE status = 1 AND proveedor_id = ?";

                $stmtClean = $mysqli->prepare($sql_update);
                $stmtClean->bind_param("sii", $fecha, $usuario_id, $provider_id);
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

function cargar_archivo($fields_provider, $file_columns, $separador)
{
    try {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '500');
        set_time_limit(0);
        $data = array();
        $archivo_name=$_POST["archivo_name"];
        $linea_inicio = isset($_POST["lineaInicio"]) ? intval($_POST["lineaInicio"]) : 1;
        $columnaInicio = isset($_POST["columnaInicio"]) ? intval($_POST["columnaInicio"]) : 1;

        if (isset($_FILES[$archivo_name]) && $_FILES[$archivo_name]['name'] != "") {
            $ext = pathinfo($_FILES[$archivo_name]['name'], PATHINFO_EXTENSION);
            $param_nombre_corto = trim($_POST["form_modal_conci_mant_proveedor_param_nombre_corto"]);
            $param_formato_archivo_venta_ext = $_POST["formato_name"];

            if ($ext == $param_formato_archivo_venta_ext) {
                $max_file_size = 2.1 * 1024 * 1024;               
                $max_file_lines = 24000;
                $nombre_archivo = $_FILES[$archivo_name]['name'];
                $tmpfname = $_FILES[$archivo_name]['tmp_name'];
                //  Verificación de archivo
                
                    if ($_FILES[$archivo_name]['size'] > $max_file_size) {
                        set_status_code_response(400, "El archivo supera el peso máximo permitido (1 MB)", "Error de archivo");
                        exit;
                    }
                    
                    if (strpos($_FILES[$archivo_name]['name'], $param_nombre_corto) === false) {
                        set_status_code_response(400, "El nombre del archivo debe contener '".$param_nombre_corto."'", "Nombre de archivo incorrecto");
                    }

                    if (strlen($_FILES[$archivo_name]['name']) > 250) {
                        set_status_code_response(400, "El nombre del archivo contiene mas de 250 caracteres", "Nombre de archivo incorrecto");
                    }  

                    if (!file_exists($tmpfname)) {
                        set_status_code_response(400, "El archivo supera el tamaño maximo. No debe superar los 2.1MB o tener mas de 24 000 lineas totales", "Error de archivo");
                        exit;
                    }

                //  Obtener datos de archivo

                    if ($ext === 'csv') {
                        // Procesar archivo CSV
                        $csvData = array_map(function($line) use ($separador) {
                            return str_getcsv($line, $separador);
                        }, file($tmpfname));
            
                        // Crear un objeto PHPExcel y añadir datos del CSV
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

                    if($columnaInicio > 1){
                        // Eliminar las columnas especificadas
                        for ($i = 0; $i < $columnaInicio-1; $i++) {
                            // Siempre eliminamos la primera columna (índice 0) en cada iteración
                            $worksheet->removeColumnByIndex(0);
                        }
                    }

                //  Validar nombre de columnas  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////


                $check_archivo = check_headers($worksheet, $fields_provider, $file_columns, $linea_inicio);

                if ($check_archivo !== true) {
                    set_status_code_response(400, $check_archivo, "El archivo no coincide debido a los siguientes errores");
                }
                $headerRow = $linea_inicio;
                $firstRow = $headerRow + 1;

                //$firstRow = 2;
                $data = array();
                $data = get_data_detalle($worksheet, $fields_provider, $firstRow, 0, $file_columns);
                $data["nombre_archivo"] = $nombre_archivo;
                $excelObj->disconnectWorksheets();
                $excelObj->garbageCollect();
                unset($excelObj);
            } else {
                $error = 'Extensión de archivo incorrecta: '.$ext.'<br> Extensión de archivo correcta: '.$param_formato_archivo_venta_ext;
                set_status_code_response(400, $error, "Error");
            }
        } else {
            set_status_code_response(400, "Ingresar Archivo excel", "Error");
        }

        return $data;
    } catch (PHPExcel_Reader_Exception $ex) {
        
        set_status_code_response(500, $ex->getMessage(), "Error");
    } catch (Exception $e) {
        set_status_code_response(500, $e->getMessage(), "Error General");
    }
}

function set_status_code_response($code, $message, $title)
{
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

function check_headers($worksheet, $fields, $file_columns, $linea_inicio)
{
    $errores = [];
    $currentColumn = 0;

    foreach ($file_columns as $column) {
        $cellValue = trim($worksheet->getCell($column . $linea_inicio)->getValue());

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

    
function get_data_detalle($worksheet, $fields, $currentRow, $currentColumn, $file_columns)
{
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

function fn_personalizarImportacion($mysqli, $metodo, $proveedor_id,$fecha,$usuario_id){

    $nombre_corto = trim($_POST["form_modal_conci_mant_proveedor_param_nombre_corto"]);

    switch ($metodo) {
        case "ColumnasArchivoCombinado":

            //  Declaración de variables

                $separador_archivoCombinado = $_POST["form_modal_conci_mant_proveedor_archivo_combinado_separador_id"];
                $extension_archivoCombinado = $_POST["form_modal_conci_mant_proveedor_archivo_combinado_extension_id"];
                $linea_inicio_archivoCombinado = $_POST["form_modal_conci_mant_proveedor_archivo_combinado_linea_inicio"];
                $columna_inicio_archivoCombinado = $_POST["form_modal_conci_mant_proveedor_archivo_combinado_columna_inicio"];
                $columnasArchivoCombinadoJSON = $_POST['columnasArchivoCombinadoJSON'];
                $columnasArchivoCombinado = json_decode($columnasArchivoCombinadoJSON, true);

                if (empty($columnasArchivoCombinado)) {
                    throw new Exception("La tabla de columnas del archivo combinado esta vacia");
                }

            //  Crear formato de personalizaciónn de archivo

                $sql_insert_combinado = "
                            INSERT INTO tbl_conci_archivo_formato (
                                proveedor_id,
                                nombre,
                                tipo_archivo_id,
                                extension_id,
                                separador_id,
                                linea_inicio,
                                columna_inicio,
                                status,
                                created_at,
                                user_created_id
                            )  
                            VALUES (?,?,3,?,?,?,?,1,?,?)
                        ";

                $stmtInsertFormatoCombinado = $mysqli->prepare($sql_insert_combinado);
                $stmtInsertFormatoCombinado->bind_param("isiiiisi", 
                                                                $proveedor_id, 
                                                                $nombre_corto,
                                                                $extension_archivoCombinado,
                                                                $separador_archivoCombinado,
                                                                $linea_inicio_archivoCombinado,
                                                                $columna_inicio_archivoCombinado,                         
                                                                $fecha,
                                                                $usuario_id
                                                                );

                if (!$stmtInsertFormatoCombinado->execute()){
                    throw new Exception("Error al guardar la personalización de importación combinada: " . $mysqli->error);
                }
                $stmtInsertFormatoCombinado->close();

            //  Registrar columnas del archivo
                $orden = 1;

                foreach ($columnasArchivoCombinado as $columnaArchivoCombinado) {
                    $column_id = fn_insertarColumna($mysqli, 
                                                            $columnaArchivoCombinado['nombre'], 
                                                            $proveedor_id, 
                                                            3,                                      //  Tipo de archivo venta
                                                            $columnaArchivoCombinado['formato'], 
                                                            $columnaArchivoCombinado['columna'], 
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id);

                    if($columnaArchivoCombinado['columna'] == 1){  //          Id
                        $column_provider_id = $column_id;
                        }
                    if($columnaArchivoCombinado['columna'] == 3){   //          Monto
                        $column_provider_monto_id = $column_id;
                        }
                    if($columnaArchivoCombinado['columna'] == 8){   //          Comisión Total
                        $column_provider_comision_id = $column_id;
                        }
                    $orden++;
                    }

            //  Registrar personalización de id calimaco


                $sql_update_provider_columna_id = "UPDATE tbl_conci_proveedor_columna SET formato_id = ?, nombreColumna_json = ?, prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
                
                $stmtUpdateProviderColumnId = $mysqli->prepare($sql_update_provider_columna_id);
                $stmtUpdateProviderColumnId->bind_param("isssii", 
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo"],
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_nombre_json"],
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_prefijo"], 
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_sufijo"], 
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_separador_id"],
                                                $column_provider_id);
                if (!$stmtUpdateProviderColumnId->execute())    throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);

                $stmtUpdateProviderColumnId->close();

                $sql_update_provider_columna_monto = "UPDATE tbl_conci_proveedor_columna SET prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
            
                $stmtUpdateProviderColumnAmount = $mysqli->prepare($sql_update_provider_columna_monto);
                $stmtUpdateProviderColumnAmount->bind_param("ssii", 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_prefijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_sufijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_separador_id"],
                                                    $column_provider_monto_id);
                if (!$stmtUpdateProviderColumnAmount->execute()) set_status_code_response(400, "Error en la preparación de la consulta: ".$mysqli->error, "Error");
                $stmtUpdateProviderColumnAmount->close();

                $sql_update_provider_columna_comision = "UPDATE tbl_conci_proveedor_columna SET prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
        
                $stmtUpdateProviderColumnCommission = $mysqli->prepare($sql_update_provider_columna_comision);
                $stmtUpdateProviderColumnCommission->bind_param("ssii", 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_prefijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_sufijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_separador_id"],
                                                $column_provider_comision_id);
                if (!$stmtUpdateProviderColumnCommission->execute())  throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);
                $stmtUpdateProviderColumnCommission->close();

            return true;
            break;
        case "ColumnasArchivosIndependientes":

            //  Declaración de variables

                $separador_archivoVenta = $_POST["form_modal_conci_mant_proveedor_archivo_venta_separador_id"];
                $extension_archivoVenta = $_POST["form_modal_conci_mant_proveedor_archivo_venta_extension_id"];
                $linea_inicio_archivoVenta = $_POST["form_modal_conci_mant_proveedor_archivo_venta_linea_inicio"];
                $columna_inicio_archivoVenta = $_POST["form_modal_conci_mant_proveedor_archivo_venta_columna_inicio"];
                $columnasArchivoVentaJSON = $_POST['columnasArchivoVentaJSON'];

                $separador_archivoLiquidacion = $_POST["form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id"];
                $extension_archivoLiquidacion = $_POST["form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id"];
                $linea_inicio_archivoLiquidacion = $_POST["form_modal_conci_mant_proveedor_archivo_liquidacion_linea_inicio"];
                $columna_inicio_archivoLiquidacion = $_POST["form_modal_conci_mant_proveedor_archivo_liquidacion_columna_inicio"];

                $columnasArchivoLiquidacionJSON = $_POST['columnasArchivoLiquidacionJSON'];
                
                $columnasArchivoVenta = json_decode($columnasArchivoVentaJSON, true);
                $columnasArchivoLiquidacion = json_decode($columnasArchivoLiquidacionJSON, true);

                if (empty($columnasArchivoVenta) || empty($columnasArchivoLiquidacion) ) {
                    throw new Exception("La tabla de columnas del archivo de ventas o liquidación esta vacia");
                }

            //  Crear formato de personalizaciónn de archivo

                $sql_insert_venta = "
                            INSERT INTO tbl_conci_archivo_formato (
                                proveedor_id,
                                nombre,
                                tipo_archivo_id,
                                extension_id,
                                separador_id,
                                linea_inicio,
                                columna_inicio,
                                status,
                                created_at,
                                user_created_id
                            )  
                            VALUES (?,?,1,?,?,?,?,1,?,?)
                        ";

                $stmtInsertFormatoVenta = $mysqli->prepare($sql_insert_venta);
                $stmtInsertFormatoVenta->bind_param("isiiiisi", 
                                                                $proveedor_id, 
                                                                $nombre_corto,
                                                                $extension_archivoVenta,
                                                                $separador_archivoVenta,
                                                                $linea_inicio_archivoVenta,
                                                                $columna_inicio_archivoVenta,                            
                                                                $fecha,
                                                                $usuario_id
                                                                );

                if (!$stmtInsertFormatoVenta->execute()) {
                    throw new Exception("Error al guardar la personalización de importación independiente de venta: " . $mysqli->error);
                    }

                $stmtInsertFormatoVenta->close();

                $sql_insert_liquidacion = "
                            INSERT INTO tbl_conci_archivo_formato (
                                proveedor_id,
                                nombre,
                                tipo_archivo_id,
                                extension_id,
                                separador_id,
                                linea_inicio,
                                columna_inicio,
                                status,
                                created_at,
                                user_created_id
                            )  
                            VALUES (?,?,2,?,?,?,?,1,?,?)
                        ";

                $stmtInsertFormatoLiquidacion = $mysqli->prepare($sql_insert_liquidacion);
                $stmtInsertFormatoLiquidacion->bind_param("isiiiisi", 
                                                                $proveedor_id, 
                                                                $nombre_corto,
                                                                $extension_archivoLiquidacion,
                                                                $separador_archivoLiquidacion,
                                                                $linea_inicio_archivoLiquidacion,
                                                                $columna_inicio_archivoLiquidacion,                           
                                                                $fecha,
                                                                $usuario_id
                                                                );

                if (!$stmtInsertFormatoLiquidacion->execute()) {
                    throw new Exception("Error al guardar la personalización de importación independiente de liquidación: " . $mysqli->error);
                    }
                $stmtInsertFormatoLiquidacion->close();

            //  Registrar columnas de archivos

                $orden = 1;
                foreach ($columnasArchivoVenta as $columnaArchivoVenta) {
                    $column_id = fn_insertarColumna($mysqli, 
                                                            $columnaArchivoVenta['nombre'], 
                                                            $proveedor_id, 
                                                            1,                                      //  Tipo de archivo venta
                                                            $columnaArchivoVenta['formato'], 
                                                            $columnaArchivoVenta['columna'], 
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id);

                    if($columnaArchivoVenta['columna'] == 1){  //          Id
                        $column_provider_venta_id = $column_id;
                        }
                    if($columnaArchivoVenta['columna'] == 3){   //          Monto
                        $column_provider_venta_monto_id = $column_id;
                        }
                    $orden ++;
                    }
                $orden = 1;

                foreach ($columnasArchivoLiquidacion as $columnaArchivoLiquidacion) {
                    $column_id = fn_insertarColumna($mysqli, 
                                                            $columnaArchivoLiquidacion['nombre'], 
                                                            $proveedor_id, 
                                                            2,                                      //  Tipo de archivo liquidación
                                                            $columnaArchivoLiquidacion['formato'], 
                                                            $columnaArchivoLiquidacion['columna'], 
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id);

                    if($columnaArchivoVenta['columna'] == 1){  //          Id
                        $column_provider_liquidacion_id = $column_id;
                        }
                    if($columnaArchivoVenta['columna'] == 8){   //          Comisión Total
                        $column_provider_liquidacion_comision_total_id = $column_id;
                        }
                    $orden ++;

                    }

            //  Registrar personalización de id calimaco archivo  venta

                    $sql_update_provider_columna_id = "UPDATE tbl_conci_proveedor_columna SET formato_id = ?, nombreColumna_json = ?, prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
                    
                    $stmtUpdateProviderColumnId = $mysqli->prepare($sql_update_provider_columna_id);
                    $stmtUpdateProviderColumnId->bind_param("isssii", 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_tipo"],
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_nombre_json"],
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_prefijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_sufijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_separador_id"],
                                                    $column_provider_venta_id);
                    if (!$stmtUpdateProviderColumnId->execute())    throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);

                    $stmtUpdateProviderColumnId->close();

                    $sql_update_provider_columna_monto = "UPDATE tbl_conci_proveedor_columna SET prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
                
                    $stmtUpdateProviderColumnAmount = $mysqli->prepare($sql_update_provider_columna_monto);
                    $stmtUpdateProviderColumnAmount->bind_param("ssii", 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_prefijo"], 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_sufijo"], 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_separador_id"],
                                                        $column_provider_venta_monto_id);
                    if (!$stmtUpdateProviderColumnAmount->execute()) throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);
                    $stmtUpdateProviderColumnAmount->close();


                    $sql_update_liquidacion_provider_columna_id = "UPDATE tbl_conci_proveedor_columna SET formato_id = ?, nombreColumna_json = ?, prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
                    
                    $stmtUpdateLiquidacionProviderColumnId = $mysqli->prepare($sql_update_liquidacion_provider_columna_id);
                    $stmtUpdateLiquidacionProviderColumnId->bind_param("isssii", 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_tipo"],
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_nombre_json"],
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_prefijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_sufijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_separador_id"],
                                                    $column_provider_liquidacion_id);
                    if (!$stmtUpdateLiquidacionProviderColumnId->execute())    throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);

                    $stmtUpdateLiquidacionProviderColumnId->close();

                    $sql_update_liquidacion_provider_columna_comision = "UPDATE tbl_conci_proveedor_columna SET prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
            
                    $stmtUpdateLiquidacionProviderColumnCommission = $mysqli->prepare($sql_update_liquidacion_provider_columna_comision);
                    $stmtUpdateLiquidacionProviderColumnCommission->bind_param("ssii", 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_prefijo"], 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_sufijo"], 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_separador_id"],
                                                    $column_provider_liquidacion_comision_total_id);
                    if (!$stmtUpdateLiquidacionProviderColumnCommission->execute())  throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);
                    $stmtUpdateLiquidacionProviderColumnCommission->close();

                    
                //  Registrar columnas de archivos

                return true;    
                break;
            default:
                throw new Exception("No existe el metodo de importación");
                break;
        }
}

function fn_personalizarImportacion_Update($mysqli, $metodo, $proveedor_id,$fecha,$usuario_id){

    $nombre_corto = trim($_POST["form_modal_conci_mant_proveedor_param_nombre_corto"]);

    switch ($metodo) {
        case "ColumnasArchivoCombinado":

            //  Declaración de variables

                $separador_archivoCombinado = $_POST["form_modal_conci_mant_proveedor_archivo_combinado_separador_id"];
                $extension_archivoCombinado = $_POST["form_modal_conci_mant_proveedor_archivo_combinado_extension_id"];
                $linea_inicio_archivoCombinado = $_POST["form_modal_conci_mant_proveedor_archivo_combinado_linea_inicio"];
                $columna_inicio_archivoCombinado = $_POST["form_modal_conci_mant_proveedor_archivo_combinado_columna_inicio"];
                $columnasArchivoCombinadoJSON = $_POST['columnasArchivoCombinadoJSON'];
                $columnasArchivoCombinado = json_decode($columnasArchivoCombinadoJSON, true);

                if (empty($columnasArchivoCombinado)) {
                    throw new Exception("La tabla de columnas del archivo combinado esta vacia");
                }

            //  Crear formato de personalizaciónn de archivo

                $sql_insert_combinado = "
                            UPDATE tbl_conci_archivo_formato
                            SET 
                                nombre = ?,
                                tipo_archivo_id = 3,
                                extension_id = ?,
                                separador_id = ?,
                                linea_inicio = ?,
                                columna_inicio = ?,
                                status = 1,
                                updated_at = ?,
                                user_updated_id = ?
                            WHERE proveedor_id = ?
                        ";

                $stmtInsertFormatoCombinado = $mysqli->prepare($sql_insert_combinado);
                $stmtInsertFormatoCombinado->bind_param("siiiisii", 
                                                                $nombre_corto,
                                                                $extension_archivoCombinado,
                                                                $separador_archivoCombinado,
                                                                $linea_inicio_archivoCombinado,
                                                                $columna_inicio_archivoCombinado,                         
                                                                $fecha,
                                                                $usuario_id,
                                                                $proveedor_id
                                                                );

                if (!$stmtInsertFormatoCombinado->execute()){
                    throw new Exception("Error al guardar la personalización de importación combinada: " . $mysqli->error);
                }
                $stmtInsertFormatoCombinado->close();

            //  Registrar columnas del archivo
                $orden = 1;

                foreach ($columnasArchivoCombinado as $columnaArchivoCombinado) {
                    if($columnaArchivoCombinado['id'] == 0){

                        $column_id = fn_insertarColumna($mysqli, 
                                                            $columnaArchivoCombinado['nombre'], 
                                                            $proveedor_id, 
                                                            3,                                      //  Tipo de archivo liquidación
                                                            $columnaArchivoCombinado['formato'], 
                                                            $columnaArchivoCombinado['columna'],
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id);

                    }else{
                        $column_id = fn_updateColumna($mysqli, 
                                                            $columnaArchivoCombinado['nombre'], 
                                                            3,                                      //  Tipo de archivo venta
                                                            $columnaArchivoCombinado['formato'], 
                                                            $columnaArchivoCombinado['columna'], 
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id,
                                                            $columnaArchivoCombinado['id'], 
                                                        );
                    }
                    
                    if($columnaArchivoCombinado['columna'] == 1){  //          Id
                        $column_provider_id = $column_id;
                        }
                    if($columnaArchivoCombinado['columna'] == 3){   //          Monto
                        $column_provider_monto_id = $column_id;
                        }
                    if($columnaArchivoCombinado['columna'] == 8){   //          Comisión Total
                        $column_provider_comision_id = $column_id;
                        }
                    $orden++;
                    }

            //  Registrar personalización de id calimaco


                $sql_update_provider_columna_id = "UPDATE tbl_conci_proveedor_columna SET formato_id = ?, nombreColumna_json = ?, prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
                
                $stmtUpdateProviderColumnId = $mysqli->prepare($sql_update_provider_columna_id);
                $stmtUpdateProviderColumnId->bind_param("isssii", 
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo"],
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_nombre_json"],
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_prefijo"], 
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_sufijo"], 
                                                $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_separador_id"],
                                                $column_provider_id);
                if (!$stmtUpdateProviderColumnId->execute())    throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);

                $stmtUpdateProviderColumnId->close();

                $sql_update_provider_columna_monto = "UPDATE tbl_conci_proveedor_columna SET prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
            
                $stmtUpdateProviderColumnAmount = $mysqli->prepare($sql_update_provider_columna_monto);
                $stmtUpdateProviderColumnAmount->bind_param("ssii", 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_prefijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_sufijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_separador_id"],
                                                    $column_provider_monto_id);
                if (!$stmtUpdateProviderColumnAmount->execute()) set_status_code_response(400, "Error en la preparación de la consulta: ".$mysqli->error, "Error");
                $stmtUpdateProviderColumnAmount->close();

                $sql_update_provider_columna_comision = "UPDATE tbl_conci_proveedor_columna SET prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
        
                $stmtUpdateProviderColumnCommission = $mysqli->prepare($sql_update_provider_columna_comision);
                $stmtUpdateProviderColumnCommission->bind_param("ssii", 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_prefijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_sufijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_separador_id"],
                                                $column_provider_comision_id);
                if (!$stmtUpdateProviderColumnCommission->execute())  throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);
                $stmtUpdateProviderColumnCommission->close();

            return true;
            break;
        case "ColumnasArchivosIndependientes":

            //  Declaración de variables

                $separador_archivoVenta = $_POST["form_modal_conci_mant_proveedor_archivo_venta_separador_id"];
                $extension_archivoVenta = $_POST["form_modal_conci_mant_proveedor_archivo_venta_extension_id"];
                $linea_inicio_archivoVenta = $_POST["form_modal_conci_mant_proveedor_archivo_venta_linea_inicio"];
                $columna_inicio_archivoVenta = $_POST["form_modal_conci_mant_proveedor_archivo_venta_columna_inicio"];

                $separador_archivoLiquidacion = $_POST["form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id"];
                $extension_archivoLiquidacion = $_POST["form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id"];
                $linea_inicio_archivoLiquidacion = $_POST["form_modal_conci_mant_proveedor_archivo_liquidacion_linea_inicio"];
                $columna_inicio_archivoLiquidacion = $_POST["form_modal_conci_mant_proveedor_archivo_liquidacion_columna_inicio"];

                $columnasArchivoVentaJSON = $_POST['columnasArchivoVentaJSON'];
                $columnasArchivoLiquidacionJSON = $_POST['columnasArchivoLiquidacionJSON'];

                
                $columnasArchivoVenta = json_decode($columnasArchivoVentaJSON, true);
                $columnasArchivoLiquidacion = json_decode($columnasArchivoLiquidacionJSON, true);

                if (empty($columnasArchivoVenta) || empty($columnasArchivoLiquidacion) ) {
                    throw new Exception("La tabla de columnas del archivo de ventas o liquidación esta vacia");
                }

            //  Crear formato de personalizaciónn de archivo

                $sql_insert_venta = "
                            UPDATE tbl_conci_archivo_formato
                            SET 
                                nombre = ?,
                                extension_id = ?,
                                separador_id = ?,
                                linea_inicio = ?,
                                columna_inicio = ?,
                                status = 1,
                                updated_at = ?,
                                user_updated_id = ?
                            WHERE proveedor_id = ? AND tipo_archivo_id = 1
                        ";

                $stmtInsertFormatoVenta = $mysqli->prepare($sql_insert_venta);
                $stmtInsertFormatoVenta->bind_param("siiiisii", 
                                                $nombre_corto,
                                                $extension_archivoVenta,
                                                $separador_archivoVenta,
                                                $linea_inicio_archivoVenta,
                                                $columna_inicio_archivoVenta,                         
                                                $fecha,
                                                $usuario_id,
                                                $proveedor_id
                                                );


                if (!$stmtInsertFormatoVenta->execute()) {
                    throw new Exception("Error al guardar la personalización de importación independiente de venta: " . $mysqli->error);
                    }

                $stmtInsertFormatoVenta->close();

                $sql_insert_liquidacion = "
                            UPDATE tbl_conci_archivo_formato
                            SET 
                                nombre = ?,
                                extension_id = ?,
                                separador_id = ?,
                                linea_inicio = ?,
                                columna_inicio = ?,
                                status = 1,
                                updated_at = ?,
                                user_updated_id = ?
                            WHERE proveedor_id = ? AND tipo_archivo_id = 2
                        ";

                $stmtInsertFormatoLiquidacion = $mysqli->prepare($sql_insert_liquidacion);
                $stmtInsertFormatoLiquidacion->bind_param("siiiisii", 
                                                $nombre_corto,
                                                $extension_archivoLiquidacion,
                                                $separador_archivoLiquidacion,
                                                $linea_inicio_archivoLiquidacion,
                                                $columna_inicio_archivoLiquidacion,                         
                                                $fecha,
                                                $usuario_id,
                                                $proveedor_id
                                                );

                if (!$stmtInsertFormatoLiquidacion->execute()) {
                    throw new Exception("Error al guardar la personalización de importación independiente de liquidación: " . $mysqli->error);
                    }
                $stmtInsertFormatoLiquidacion->close();

                //  Registrar columnas de archivos
                $orden = 1;
                foreach ($columnasArchivoVenta as $columnaArchivoVenta) {

                    if( $columnaArchivoVenta['id'] == 0){
                        $column_id = fn_insertarColumna($mysqli, 
                                                            $columnaArchivoVenta['nombre'], 
                                                            $proveedor_id, 
                                                            1,                                      //  Tipo de archivo venta
                                                            $columnaArchivoVenta['formato'], 
                                                            $columnaArchivoVenta['columna'], 
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id);
                    }else{
                        $column_id = fn_updateColumna($mysqli, 
                                                            $columnaArchivoVenta['nombre'], 
                                                            1,                                      //  Tipo de archivo venta
                                                            $columnaArchivoVenta['formato'], 
                                                            $columnaArchivoVenta['columna'],
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id,
                                                            $columnaArchivoVenta['id'], 
                                                        );
                    }

                    if($columnaArchivoVenta['columna'] == 1){  //          Id
                        $column_provider_venta_id = $column_id;
                        }
                    if($columnaArchivoVenta['columna'] == 3){   //          Monto
                        $column_provider_venta_monto_id = $column_id;
                        }
                    $orden++;
                    }
                
                $orden = 1;
                foreach ($columnasArchivoLiquidacion as $columnaArchivoLiquidacion) {
                    if( $columnaArchivoLiquidacion['id'] == 0){

                        $column_id = fn_insertarColumna($mysqli, 
                                                            $columnaArchivoLiquidacion['nombre'], 
                                                            $proveedor_id, 
                                                            2,                                      //  Tipo de archivo liquidación
                                                            $columnaArchivoLiquidacion['formato'], 
                                                            $columnaArchivoLiquidacion['columna'],
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id);

                    }else{
                        $column_id = fn_updateColumna($mysqli, 
                                                            $columnaArchivoLiquidacion['nombre'], 
                                                            2,                                      //  Tipo de archivo venta
                                                            $columnaArchivoLiquidacion['formato'], 
                                                            $columnaArchivoLiquidacion['columna'],
                                                            $orden,
                                                            $fecha, 
                                                            $usuario_id,
                                                            $columnaArchivoLiquidacion['id'], 
                                                        );
                    }

                    if($columnaArchivoLiquidacion['columna'] == 1){  //          Id
                        $column_provider_liquidacion_id = $column_id;
                        }
                    if($columnaArchivoLiquidacion['columna'] == 8){   //          Comisión Total
                        $column_provider_liquidacion_comision_total_id = $column_id;
                        }
                    $orden++;
                    }

                //  Registrar personalización de id calimaco archivo  venta

                    $sql_update_provider_columna_id = "UPDATE tbl_conci_proveedor_columna SET formato_id = ?, nombreColumna_json = ?, prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
                        
                    $stmtUpdateProviderColumnId = $mysqli->prepare($sql_update_provider_columna_id);
                    $stmtUpdateProviderColumnId->bind_param("isssii", 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_tipo"],
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_nombre_json"],
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_prefijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_sufijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_separador_id"],
                                                    $column_provider_venta_id);
                    if (!$stmtUpdateProviderColumnId->execute())    throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);

                    $stmtUpdateProviderColumnId->close();

                    $sql_update_provider_columna_monto = "UPDATE tbl_conci_proveedor_columna SET prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
                
                    $stmtUpdateProviderColumnAmount = $mysqli->prepare($sql_update_provider_columna_monto);
                    $stmtUpdateProviderColumnAmount->bind_param("ssii", 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_prefijo"], 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_sufijo"], 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_separador_id"],
                                                        $column_provider_venta_monto_id);
                    if (!$stmtUpdateProviderColumnAmount->execute()) throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);
                    $stmtUpdateProviderColumnAmount->close();


                    $sql_update_liquidacion_provider_columna_id = "UPDATE tbl_conci_proveedor_columna SET formato_id = ?, nombreColumna_json = ?, prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
                    
                    $stmtUpdateLiquidacionProviderColumnId = $mysqli->prepare($sql_update_liquidacion_provider_columna_id);
                    $stmtUpdateLiquidacionProviderColumnId->bind_param("isssii", 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_tipo"],
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_nombre_json"],
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_prefijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_sufijo"], 
                                                    $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_separador_id"],
                                                    $column_provider_liquidacion_id);
                    if (!$stmtUpdateLiquidacionProviderColumnId->execute())    throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);

                    $stmtUpdateLiquidacionProviderColumnId->close();

                    $sql_update_liquidacion_provider_columna_comision = "UPDATE tbl_conci_proveedor_columna SET prefijo = ?, sufijo = ?, separador_id = ? WHERE id = ?";
            
                    $stmtUpdateLiquidacionProviderColumnCommission = $mysqli->prepare($sql_update_liquidacion_provider_columna_comision);
                    $stmtUpdateLiquidacionProviderColumnCommission->bind_param("ssii", 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_prefijo"], 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_sufijo"], 
                                                        $_POST["form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_separador_id"],
                                                    $column_provider_liquidacion_comision_total_id);
                    if (!$stmtUpdateLiquidacionProviderColumnCommission->execute())  throw new Exception("Error al guardar las columnas a conciliarse" . $mysqli->error);
                    $stmtUpdateLiquidacionProviderColumnCommission->close();

                return true;    
                break;
            default:
                throw new Exception("No existe el metodo de importación");
                break;
        }
}
function fn_personalizarFormulas($mysqli, $metodo, $proveedor_id,$fecha,$usuario_id){

    //  Declaración de variables

        $formulasComisionJSON = $_POST['formulasComisionJSON'];
        $formulasComision = json_decode($formulasComisionJSON, true);
    
        if (empty($formulasComision)) {
            throw new Exception("La tabla de columnas del archivo combinado esta vacia");
            }

    switch ($metodo) {
        case "FormulaFija":
            foreach ($formulasComision as $formulaComision) {
                fn_insertarFormulaMixta($mysqli, 
                    $proveedor_id, 
                    $formulaComision['nombre'], 
                    $formulaComision['operador'], 
                    $formulaComision['valor'], 
                    number_format((float)$formulaComision['porcentaje'], 2, '.', ''), 
                    number_format((float)$formulaComision['constante'], 2, '.', ''), 
                    number_format((float)$formulaComision['igv'], 2, '.', ''), 
                    $fecha, 
                    $usuario_id);
                }
            return true;
            break;
        case "FormulaEscalonada":
            foreach ($formulasComision as $formulaComision) {
                fn_insertarFormulaEscalonada($mysqli, 
                    $proveedor_id,
                    number_format((float)$formulaComision['desde_escalonada'], 2, '.', ''),
                    number_format((float)$formulaComision['hasta_escalonada'], 2, '.', ''), 
                    number_format((float)$formulaComision['porcentaje_escalonada'], 2, '.', ''), 
                    number_format((float)$formulaComision['constante_escalonada'], 2, '.', ''), 
                    number_format((float)$formulaComision['igv_escalonada'], 2, '.', ''),
                    $fecha, 
                    $usuario_id);
                }
            return true;
            break;
        case "FormulaMixta":
            foreach ($formulasComision as $formulaComision) {
                fn_insertarFormulaMixta($mysqli, 
                    $proveedor_id, 
                    $formulaComision['nombre'], 
                    $formulaComision['operador'], 
                    $formulaComision['valor'], 
                    number_format((float)$formulaComision['desde_mixta'], 2, '.', ''),
                    number_format((float)$formulaComision['hasta_mixta'], 2, '.', ''),
                    number_format((float)$formulaComision['porcentaje_mixta'], 2, '.', ''),
                    number_format((float)$formulaComision['constante_mixta'], 2, '.', ''),
                    number_format((float)$formulaComision['igv_mixta'], 2, '.', ''),
                    $fecha, 
                    $usuario_id);
                }
            return true;
            break;
        default:
            throw new Exception("No existe el metodo de la formula");
            break;
    }
}

function fn_personalizarFormulas_Update($mysqli, $metodo, $proveedor_id,$fecha,$usuario_id){

    //  Declaración de variables

        $formulasComisionJSON = $_POST['formulasComisionJSON'];
        $formulasComision = json_decode($formulasComisionJSON, true);
    
        if (empty($formulasComision)) {
            throw new Exception("La tabla de columnas del archivo combinado esta vacia");
            }

    switch ($metodo) {
        case "FormulaFija":
            foreach ($formulasComision as $formulaComision) {

                if($formulaComision['id'] == 0){

                    fn_insertarFormulaFija($mysqli, 
                                                    $proveedor_id, 
                                                    $formulaComision['nombre'], 
                                                    $formulaComision['operador'], 
                                                    $formulaComision['valor'], 
                                                    number_format((float)$formulaComision['porcentaje'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['constante'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['igv'], 2, '.', ''), 
                                                    $fecha, 
                                                    $usuario_id);

                }else{
                    fn_updateFormulaFija($mysqli, 
                                                    $formulaComision['nombre'], 
                                                    $formulaComision['operador'], 
                                                    $formulaComision['valor'], 
                                                    number_format((float)$formulaComision['porcentaje'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['constante'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['igv'], 2, '.', ''), 
                                                    $fecha, 
                                                    $usuario_id,
                                                    $formulaComision['id']);
                }
            }
            return true;
            break;
        case "FormulaEscalonada":
            foreach ($formulasComision as $formulaComision) {
                if($formulaComision['id'] == 0){
                    fn_insertarFormulaEscalonada($mysqli, 
                                                    $proveedor_id,
                                                    number_format((float)$formulaComision['desde_escalonada'], 2, '.', ''),
                                                    number_format((float)$formulaComision['hasta_escalonada'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['porcentaje_escalonada'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['constante_escalonada'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['igv_escalonada'], 2, '.', ''), 
                                                    $fecha, 
                                                    $usuario_id);
                }else{
                    fn_updateFormulaEscalonada($mysqli, 
                                                    number_format((float)$formulaComision['desde_escalonada'], 2, '.', ''),
                                                    number_format((float)$formulaComision['hasta_escalonada'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['porcentaje_escalonada'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['constante_escalonada'], 2, '.', ''), 
                                                    number_format((float)$formulaComision['igv_escalonada'], 2, '.', ''), 
                                                    $fecha, 
                                                    $usuario_id,
                                                    $formulaComision['id'], 
                                                );
                }
                }
            return true;
            break;
        case "FormulaMixta":
            foreach ($formulasComision as $formulaComision) {
                if($formulaComision['id'] == 0){

                    fn_insertarFormulaMixta($mysqli, 
                                                    $proveedor_id, 
                                                    $formulaComision['nombre'], 
                                                    $formulaComision['operador'], 
                                                    $formulaComision['valor'], 
                                                    number_format((float)$formulaComision['desde_mixta'], 2, '.', ''),
                                                    number_format((float)$formulaComision['hasta_mixta'], 2, '.', ''),
                                                    number_format((float)$formulaComision['porcentaje_mixta'], 2, '.', ''),
                                                    number_format((float)$formulaComision['constante_mixta'], 2, '.', ''),
                                                    number_format((float)$formulaComision['igv_mixta'], 2, '.', ''),
                                                    $fecha, 
                                                    $usuario_id);
                }else{
                    fn_updateFormulaMixta($mysqli, 
                                                    $formulaComision['nombre'], 
                                                    $formulaComision['operador'], 
                                                    $formulaComision['valor'], 
                                                    number_format((float)$formulaComision['desde_mixta'], 2, '.', ''),
                                                    number_format((float)$formulaComision['hasta_mixta'], 2, '.', ''),
                                                    number_format((float)$formulaComision['porcentaje_mixta'], 2, '.', ''),
                                                    number_format((float)$formulaComision['constante_mixta'], 2, '.', ''),
                                                    number_format((float)$formulaComision['igv_mixta'], 2, '.', ''),
                                                    $fecha, 
                                                    $usuario_id,
                                                    $formulaComision['id']);
                }
            }
            return true;
            break;
        default:
            throw new Exception("No existe el metodo de la formula");
            break;
    }
}


function fn_insertarColumna($mysqli, $nombre, $proveedor_id, $tipo_archivo_id, $formato, $columna, $orden, $fecha, $usuario_id) {

    $query_insert_columna = "
        INSERT INTO tbl_conci_proveedor_columna (
            nombre,
            proveedor_id,
            tipo_archivo_id,
            formato_id,
            columna_id,
            orden,
            status,
            created_at,
            user_created_id
        )  
        VALUES (?, ?, ?, ?, ?, ?,1, ?, ?)";
    
    $stmtInsertProveedorColumna = $mysqli->prepare($query_insert_columna);
    
    if ($stmtInsertProveedorColumna == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorColumna->bind_param("siiiiisi", 
                                                    $nombre, 
                                                    $proveedor_id,
                                                    $tipo_archivo_id,
                                                    $formato,
                                                    $columna,
                                                    $orden,            
                                                    $fecha,
                                                    $usuario_id);
    
    if (!$stmtInsertProveedorColumna->execute()) {
        throw new Exception("Error al guardar las columnas del archivo: " . $mysqli->error);
    }
    $inserted_id = $mysqli->insert_id;

    $stmtInsertProveedorColumna->close();
    return $inserted_id;
}

function fn_updateColumna($mysqli, $nombre, $tipo_archivo_id, $formato, $columna, $orden, $fecha, $usuario_id, $id) {

    $query_update_columna = "
        UPDATE tbl_conci_proveedor_columna
        SET
            nombre = ?,
            tipo_archivo_id = ?,
            formato_id = ?,
            columna_id = ?,
            orden = ?,
            status = 1,
            updated_at = ?,
            user_updated_id = ?
        WHERE id = ?";
    
    $stmtUpdateProveedorColumna = $mysqli->prepare($query_update_columna);
    
    if ($stmtUpdateProveedorColumna == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtUpdateProveedorColumna->bind_param("siiiisii", 
                                                    $nombre, 
                                                    $tipo_archivo_id,
                                                    $formato,
                                                    $columna,
                                                    $orden,            
                                                    $fecha,
                                                    $usuario_id,
                                                    $id);
    
    if (!$stmtUpdateProveedorColumna->execute()) {
        throw new Exception("Error al guardar las columnas del archivo: " . $mysqli->error);
    }

    $stmtUpdateProveedorColumna->close();
    return $id;
}


function fn_insertarFormulaMixta($mysqli, $proveedor_id, $columna_id, $operador_id, $opcion_id, $desde, $hasta,$comision_porcentual, $comision_fija, $igv, $fecha, $usuario_id) {

    $query_insert_columna = "
        INSERT INTO tbl_conci_proveedor_formula (
            proveedor_id,
            columna_id,
            operador_id,
            opcion_id,
            desde,
            hasta,
            comision_porcentual,
            comision_fija,
            igv,
            status,
            created_at,
            user_created_id
        )  
        VALUES (?,?,?,?,?,?,?,?,?,1,?,?)";
    
    $stmtInsertProveedorColumna = $mysqli->prepare($query_insert_columna);
    
    if ($stmtInsertProveedorColumna == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorColumna->bind_param("iiiidddddsi", 
                                                    $proveedor_id,
                                                    $columna_id,
                                                    $operador_id,
                                                    $opcion_id,
                                                    $desde,
                                                    $hasta,
                                                    $comision_porcentual,
                                                    $comision_fija,
                                                    $igv,          
                                                    $fecha,
                                                    $usuario_id);
    
    if (!$stmtInsertProveedorColumna->execute()) {
        throw new Exception("Error al guardar las formulas mixtas: " . $mysqli->error);
    }
    $stmtInsertProveedorColumna->close();

}

function fn_updateFormulaMixta($mysqli, $columna_id, $operador_id, $opcion_id, $desde, $hasta,$comision_porcentual, $comision_fija, $igv, $fecha, $usuario_id, $id) {

    $query_insert_columna = "
        UPDATE tbl_conci_proveedor_formula 
        SET
            columna_id=?,
            operador_id=?,
            opcion_id=?,
            desde=?,
            hasta=?,
            comision_porcentual=?,
            comision_fija=?,
            igv=?,
            status=1,
            updated_at = ?,
            user_updated_id = ?
        WHERE id = ?";
    
    $stmtInsertProveedorColumna = $mysqli->prepare($query_insert_columna);
    
    if ($stmtInsertProveedorColumna == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorColumna->bind_param("iiidddddsii", 
                                                    $columna_id,
                                                    $operador_id,
                                                    $opcion_id,
                                                    $desde,
                                                    $hasta,
                                                    $comision_porcentual,
                                                    $comision_fija,
                                                    $igv,          
                                                    $fecha,
                                                    $usuario_id,
                                                    $id);
    
    if (!$stmtInsertProveedorColumna->execute()) {
        throw new Exception("Error al guardar las formulas mixtas: " . $mysqli->error);
    }
    $stmtInsertProveedorColumna->close();

}

function fn_insertarFormulaFija($mysqli, $proveedor_id, $columna_id, $operador_id, $opcion_id,$comision_porcentual, $comision_fija, $igv, $fecha, $usuario_id) {

    $query_insert_columna = "
        INSERT INTO tbl_conci_proveedor_formula (
            proveedor_id,
            columna_id,
            operador_id,
            opcion_id,
            comision_porcentual,
            comision_fija,
            igv,
            status,
            created_at,
            user_created_id
        )  
        VALUES (?,?,?,?,?,?,?,1,?,?)";
    
    $stmtInsertProveedorColumna = $mysqli->prepare($query_insert_columna);
    
    if ($stmtInsertProveedorColumna == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorColumna->bind_param("iiiidddsi", 
                                                    $proveedor_id,
                                                    $columna_id,
                                                    $operador_id,
                                                    $opcion_id,
                                                    $comision_porcentual,
                                                    $comision_fija,
                                                    $igv,          
                                                    $fecha,
                                                    $usuario_id);
    
    if (!$stmtInsertProveedorColumna->execute()) {
        throw new Exception("Error al guardar las formulas fijas: " . $mysqli->error);
    }
    $stmtInsertProveedorColumna->close();

}

function fn_updateFormulaFija($mysqli, $columna_id, $operador_id, $opcion_id,$comision_porcentual, $comision_fija, $igv, $fecha, $usuario_id, $id) {

    $query_insert_columna = "
        UPDATE tbl_conci_proveedor_formula
        SET 
            columna_id=?,
            operador_id=?,
            opcion_id=?,
            comision_porcentual=?,
            comision_fija=?,
            igv=?,
            status=1,
            updated_at=?,
            user_updated_id=?
        WHERE id = ?";
    
    $stmtInsertProveedorColumna = $mysqli->prepare($query_insert_columna);
    
    if ($stmtInsertProveedorColumna == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorColumna->bind_param("iiidddsii", 
                                                    $columna_id,
                                                    $operador_id,
                                                    $opcion_id,
                                                    $comision_porcentual,
                                                    $comision_fija,
                                                    $igv,          
                                                    $fecha,
                                                    $usuario_id);
    
    if (!$stmtInsertProveedorColumna->execute()) {
        throw new Exception("Error al guardar las formulas fijas: " . $mysqli->error);
    }
    $stmtInsertProveedorColumna->close();

}

function fn_insertarFormulaEscalonada($mysqli, $proveedor_id, $desde, $hasta,$comision_porcentual, $comision_fija, $igv, $fecha, $usuario_id) {

    $query_insert_columna = "
        INSERT INTO tbl_conci_proveedor_formula (
            proveedor_id,
            desde,
            hasta,
            comision_porcentual,
            comision_fija,
            igv,
            status,
            created_at,
            user_created_id
        )  
        VALUES (?,?,?,?,?,?,1,?,?)";
    
    $stmtInsertProveedorColumna = $mysqli->prepare($query_insert_columna);
    
    if ($stmtInsertProveedorColumna == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorColumna->bind_param("idddddsi", 
                                                    $proveedor_id,                                               
                                                    $desde,
                                                    $hasta,
                                                    $comision_porcentual,
                                                    $comision_fija,
                                                    $igv,          
                                                    $fecha,
                                                    $usuario_id);
    
    if (!$stmtInsertProveedorColumna->execute()) {
        throw new Exception("Error al guardar las formulas escalonadas: " . $mysqli->error);
    }
    $stmtInsertProveedorColumna->close();

}

function fn_updateFormulaEscalonada($mysqli, $desde, $hasta,$comision_porcentual, $comision_fija, $igv, $fecha, $usuario_id, $id) {

    //throw new Exception("Desde: " . $desde.", Hasta:" . $hasta.", Comision Porcentual:" . $comision_porcentual.", Comisión fija:" . $comision_fija.", IGV:" . $igv);

    $query_insert_columna = "
        UPDATE tbl_conci_proveedor_formula
        SET   
            desde=?,
            hasta=?,
            comision_porcentual=?,
            comision_fija=?,
            igv=?,
            status=1,
            updated_at=?,
            user_updated_id=?
        WHERE id = ?";
    
    $stmtInsertProveedorColumna = $mysqli->prepare($query_insert_columna);
    
    if ($stmtInsertProveedorColumna == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorColumna->bind_param("dddddsii",
                                                    $desde,
                                                    $hasta,
                                                    $comision_porcentual,
                                                    $comision_fija,
                                                    $igv,          
                                                    $fecha,
                                                    $usuario_id,
                                                    $id);
    
    if (!$stmtInsertProveedorColumna->execute()) {
        throw new Exception("Error al guardar las formulas escalonadas: " . $mysqli->error);
    }
    $stmtInsertProveedorColumna->close();

}

function fn_insertarCuentaBancaria($mysqli, $proveedor_id, $banco_id, $moneda_id, $cuenta_corriente,$cuenta_interbancaria, $fecha, $usuario_id) {

    $query_insert_cuenta = "
        INSERT INTO tbl_conci_proveedor_cuenta_bancaria (
            proveedor_id,
            banco_id,
            moneda_id,
            cuenta_corriente,
            cuenta_interbancaria,
            status,
            created_at,
            user_created_id
        )  
        VALUES (?,?,?,?,?,1,?,?)";
    
    $stmtInsertProveedorCuenta = $mysqli->prepare($query_insert_cuenta);
    
    if ($stmtInsertProveedorCuenta == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorCuenta->bind_param("iiisssi", 
                                                    $proveedor_id,
                                                    $banco_id,
                                                    $moneda_id,
                                                    $cuenta_corriente,
                                                    $cuenta_interbancaria,
                                                    $fecha,
                                                    $usuario_id);
    
    if (!$stmtInsertProveedorCuenta->execute()) {
        throw new Exception("Error al guardar las cuentas bancarias: " . $mysqli->error);
    }
    $stmtInsertProveedorCuenta->close();

}

function fn_updateCuentaBancaria($mysqli, $banco_id, $moneda_id, $cuenta_corriente,$cuenta_interbancaria, $fecha, $usuario_id, $id) {

    $query_insert_cuenta = "
        UPDATE tbl_conci_proveedor_cuenta_bancaria
        SET
            banco_id =?,
            moneda_id=?,
            cuenta_corriente=?,
            cuenta_interbancaria=?,
            status=1,
            updated_at=?,
            user_updated_id=?
        WHERE id = ?";
    
    $stmtInsertProveedorCuenta = $mysqli->prepare($query_insert_cuenta);
    
    if ($stmtInsertProveedorCuenta == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorCuenta->bind_param("iisssii", 
                                                    $banco_id,
                                                    $moneda_id,
                                                    $cuenta_corriente,
                                                    $cuenta_interbancaria,
                                                    $fecha,
                                                    $usuario_id,
                                                    $id);
    
    if (!$stmtInsertProveedorCuenta->execute()) {
        throw new Exception("Error al guardar las cuentas bancarias: " . $mysqli->error);
    }
    $stmtInsertProveedorCuenta->close();

}

function fn_insertarEstado($mysqli, $proveedor_id, $nombre, $status, $fecha, $usuario_id) {

    $query_insert_estado = "
        INSERT INTO tbl_conci_proveedor_estado (
            proveedor_id,
            nombre,
            estado,
            status,
            created_at,
            user_created_id
        )  
        VALUES (?,?,?,1,?,?)";
    
    $stmtInsertProveedorEstado = $mysqli->prepare($query_insert_estado);
    
    if ($stmtInsertProveedorEstado == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorEstado->bind_param("isisi", 
                                                    $proveedor_id,
                                                    $nombre,
                                                    $status,
                                                    $fecha,
                                                    $usuario_id);
    
    if (!$stmtInsertProveedorEstado->execute()) {
        throw new Exception("Error al guardar los estados: " . $mysqli->error);
    }
    $stmtInsertProveedorEstado->close();

}

function fn_updateEstado($mysqli, $nombre, $status, $fecha, $usuario_id, $id) {

    $query_insert_estado = "
        UPDATE tbl_conci_proveedor_estado
        SET 
            nombre=?,
            estado=?,
            status=1,
            updated_at=?,
            user_updated_id=?
        WHERE id = ?";
    
    $stmtInsertProveedorEstado = $mysqli->prepare($query_insert_estado);
    
    if ($stmtInsertProveedorEstado == false) {
        throw new Exception("Error al preparar la consulta de registro de columnas: " . $mysqli->error);
    }
    
    $stmtInsertProveedorEstado->bind_param("sisii", 
                                                    $nombre,
                                                    $status,
                                                    $fecha,
                                                    $usuario_id,
                                                    $id);
    
    if (!$stmtInsertProveedorEstado->execute()) {
        throw new Exception("Error al guardar los estados: " . $mysqli->error);
    }
    $stmtInsertProveedorEstado->close();

}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); 

    if ((int)$usuario_id > 0) {
        $proveedor_id = $_POST["proveedor_id"];

        if ((int)$proveedor_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_cuenta_contable = "UPDATE tbl_conci_proveedor 
                                         SET 
                                             status = 0,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_cuenta_contable);
            $stmt->bind_param("iss", $usuario_id, $fecha, $proveedor_id);

            if ($stmt->execute()) {

                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestión.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "El ID del comprobante no es válido.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
    }

    echo json_encode($result);
    exit();
}
?>