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


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_recaudacion_registrar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = [];

    if ((int)$usuario_id > 0) {
        $id = (int)$_POST["id"];
        $proveedor_id = $_POST["proveedor_id"];
        $fecha_recaudacion = date("Y-m-d", strtotime($_POST['fecha']));
        $cuenta_id = $_POST["cuenta_id"];
        $monto = (float)$_POST["monto"];

        if ($id > 0) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            // INICIO: VALIDAR SI EXISTE EL TIPO DE CAMBIO DE ESA FECHA

            $query = "
                        SELECT 
                            pi.id, pi.monto_compra
                        FROM tbl_conci_tipo_cambio pi
                        LEFT JOIN tbl_conci_proveedor_cuenta_bancaria cb ON pi.moneda_id = cb.moneda_id
                        WHERE pi.status = 1 AND pi.fecha = ? AND cb.id = ?
                        LIMIT 1";

            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("si", $fecha_recaudacion, $cuenta_id);

                if ($stmt->execute()) {
                    $stmt->store_result(); 

                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($id_tipo_cambio, $monto_compra);
                        $stmt->fetch();
                    }else{
                        $result["http_code"] = 400;
                        $result["status"] = "Error";
                        $result["error"] = "No se encontro un tipo de cambio para esa fecha";
                        echo json_encode($result);
                        exit();
                    }
                    
                    $stmt->close();

                        $stmtProveedor = $mysqli->prepare("
                            SELECT 
                                IFNULL(p.comision_moneda_id,'')
                            FROM tbl_conci_proveedor p
                            WHERE p.id=?
                            LIMIT 1
                        ");

                        $stmtProveedor->bind_param("i", $proveedor_id);
                        if (!$stmtProveedor->execute()){
                            $result["http_code"] = 400;
                            $result["error"] = "Error en la preparación de la consulta del proveedor: " . $mysqli->error;
                            echo json_encode($result);
                            exit();
                        };
                        $stmtProveedor->bind_result($comision_moneda_id);

                        if (!$stmtProveedor->fetch()){
                            $result["http_code"] = 400;
                            $result["error"] = "No se encontraron datos del proveedor seleccionado: " . $mysqli->error;
                            echo json_encode($result);
                            exit();
                        }
                        $stmtProveedor->close();

                        if($comision_moneda_id == ""){
                            $result["http_code"] = 400;
                            $result["status"] = "Error";
                            $result["error"] = "No se encontro la moneda de cambio de la liquidación de comisiones. Registrar moneda de cambio en la ventana de mantenimiento";
                            echo json_encode($result);
                            exit();
                        }

                        $sql_insert_recaudacion = "
                            INSERT INTO tbl_conci_proveedor_recaudacion (
                                proveedor_id,
                                periodo_id,
                                cuenta_id,
                                monto,
                                fecha,
                                status,    
                                created_at,
                                user_created_id
                            )  
                            VALUES (?,?,?,?,?,1,?,?)
                        ";


                        if ($stmtRecaudacion = $mysqli->prepare($sql_insert_recaudacion)) {

                            if($comision_moneda_id != 1){
                                $monto = $monto * (float)$monto_compra;
                            }
                            $stmtRecaudacion->bind_param("iiidssi", $proveedor_id, $id, $cuenta_id, $monto, $fecha_recaudacion, $fecha, $usuario_id);

                            try {
                                $stmtRecaudacion->execute();

                                //  CONSULTAR DATOS DE PERIODO

                                $selectQueryPeriodo = "SELECT IFNULL(pi.monto_recaudado,0)
                                                        FROM tbl_conci_periodo pi
                                                        WHERE pi.status = 1 AND pi.id = ?
                                                        LIMIT 1";

                                $selectStmtPeriodo = $mysqli->prepare($selectQueryPeriodo);
                                if (!$selectStmtPeriodo) {
                                    $result["http_code"] = 200;
                                    $result["status"] = "Datos obtenidos de gestión.";
                                    echo json_encode($result);
                                    exit();                            }
                                $selectStmtPeriodo->bind_param("i", $id);
                                if (!$selectStmtPeriodo->execute()) {
                                    $result["http_code"] = 200;
                                    $result["status"] = "Datos obtenidos de gestión.";
                                    echo json_encode($result);
                                    exit();                            }
                                $selectStmtPeriodo->store_result();

                                if ($selectStmtPeriodo->num_rows > 0) {
                                    $selectStmtPeriodo->bind_result($monto_recaudado);
                                    $selectStmtPeriodo->fetch();
                                    $selectStmtPeriodo->free_result(); 
                                    $selectStmtPeriodo->close();

                                }
                                $selectStmtPeriodo->close();

                                //  ACTUALIZAR PERIODO

                                $monto_recaudado = $monto_recaudado + $monto;
                                
                                $sql_update_periodo = "
                                    UPDATE tbl_conci_periodo SET monto_recaudado = ? WHERE id = ?";

                                $stmtUpdatePeriodo = $mysqli->prepare($sql_update_periodo);
                                $stmtUpdatePeriodo->bind_param("di", $monto_recaudado, $id);
                                if (!$stmtUpdatePeriodo->execute()){
                                    $result["http_code"] = 200;
                                    $result["status"] = "Datos obtenidos de gestión.";
                                    echo json_encode($result);
                                    exit();
                                }
                                $stmtUpdatePeriodo->close();


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

                        $stmtRecaudacion->close();
                    } else {
                        $result["http_code"] = 400;
                        $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
                        echo json_encode($result);
                        exit();
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_recaudacion_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $recaudacion_id = $_POST["recaudacion_id"];
        $periodo_id = $_POST["periodo_id"];

        if ((int)$recaudacion_id > 0) {
            $error = '';

            //  Obtener Registro de Recaudación

                $stmt = $mysqli->prepare("
                    SELECT 
                        monto
                    FROM tbl_conci_proveedor_recaudacion
                    WHERE id=? AND status = 1
                    LIMIT 1");

                $stmt->bind_param("i", $recaudacion_id);
                $stmt->execute();
                $stmt->bind_result($recaudacion_monto);

                if ($stmt->fetch()) {
                } else {
                    echo json_encode([
                        'status' => 404,
                        'message' => 'No se encontraron datos de la recaudación seleccionada. Recargue la pagina.',
                    ]);
                }

                $stmt->close();

            //  ACTUALIZAR ESTADO
                $query_update_recaudacion = "UPDATE tbl_conci_proveedor_recaudacion 
                                            SET 
                                                status = 0,
                                                user_updated_id = ?,
                                                updated_at = ?
                                            WHERE 
                                                id = ?";
                $stmt = $mysqli->prepare($query_update_recaudacion);
                $stmt->bind_param("iss", $usuario_id, $fecha, $recaudacion_id);

                if ($stmt->execute()) {
                    $stmt->close();

                    $selectQueryProveedor = "
                            SELECT 
                                IFNULL(p.monto_recaudado, 0)
                            FROM tbl_conci_periodo p
                            WHERE p.id = ? AND p.status = 1
                            LIMIT 1
                        ";

                        if ($selectStmtProveedor = $mysqli->prepare($selectQueryProveedor)) {
                            $selectStmtProveedor->bind_param("i", $periodo_id);
                            $selectStmtProveedor->execute();
                            $selectStmtProveedor->store_result();

                            if ($selectStmtProveedor->num_rows > 0) {
                                $selectStmtProveedor->bind_result($periodo_monto);
                                $selectStmtProveedor->fetch();
                                $selectStmtProveedor->close();

                                $periodo_monto = $periodo_monto - $recaudacion_monto;

                                if (!fn_conci_updatePeriodoRecaudacion($mysqli, $periodo_id, $usuario_id, $fecha, $periodo_monto)) {
                                    $result = [
                                        "http_code" => 400,
                                        "error" => "Error al actualizar el periodo"
                                    ];
                                } else {
                                    $result = ["http_code" => 200];
                                }
                            } else {
                                $result = [
                                    "http_code" => 400,
                                    "error" => "Sin nombre"
                                ];
                            }
                        } else {
                            $result = [
                                "http_code" => 400,
                                "error" => "Error en la preparación de la consulta: " . $mysqli->error
                            ];
                        }
                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmt->error;
                }
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


function fn_conci_updatePeriodoRecaudacion($mysqli, $periodo_id, $usuario_id, $fecha,$periodo_monto){


    $query_update_transaccion = "UPDATE tbl_conci_periodo
                                        SET 
                                            monto_recaudado = ?,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                             id = ?";
    $stmt = $mysqli->prepare($query_update_transaccion);
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error al preparar la consulta para anulación: " . $mysqli->error;
    } else {
        $stmt->bind_param("disi", $periodo_monto,$usuario_id, $fecha, $periodo_id);
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
?>