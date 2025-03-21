<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_devolucion_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $devolucion_id = $_POST["devolucion_id"];
        $calimaco_id = $_POST["calimaco_id"];

        if ((int)$devolucion_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_devolucion = "UPDATE tbl_conci_devolucion_solicitud 
                                         SET 
                                             status = 0,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_devolucion);
            $stmt->bind_param("iss", $usuario_id, $fecha, $devolucion_id);

            if ($stmt->execute()) {
                $stmt->close();

                $selectQueryProveedor = "
                        SELECT 
                            ct.id, ct.periodo_id, 
                            IFNULL(pt.monto, 0), IFNULL(pt.comision_total_calculado, 0), 
                            IFNULL(p.monto_devolucion, 0), IFNULL(p.comision_devolucion, 0), 
                            IFNULL(p.devolucion_count, 0)
                        FROM tbl_conci_calimaco_transaccion ct
                        LEFT JOIN tbl_conci_periodo p ON ct.periodo_id = p.id
                        LEFT JOIN tbl_conci_proveedor_transaccion pt ON pt.id = (
                            SELECT CAST(SUBSTRING_INDEX(ct.venta_proveedor_id, ',', 1) AS UNSIGNED)
                        )
                        WHERE ct.id = ? AND pt.status = 1
                        LIMIT 1
                    ";

                    if ($selectStmtProveedor = $mysqli->prepare($selectQueryProveedor)) {
                        $selectStmtProveedor->bind_param("i", $calimaco_id);
                        $selectStmtProveedor->execute();
                        $selectStmtProveedor->store_result();

                        if ($selectStmtProveedor->num_rows > 0) {
                            $selectStmtProveedor->bind_result($id, $periodo_id, $monto, $comision_total_calculado, $monto_devolucion, $comision_devolucion, $devolucion_count);
                            $selectStmtProveedor->fetch();
                            $selectStmtProveedor->close();

                            $monto_devolucion = $monto_devolucion - $monto;
                            $comision_devolucion = $comision_devolucion - $comision_total_calculado;
                            $devolucion_count = $devolucion_count - 1;

                            if (!fn_conci_updatePeriodoDevolucion($mysqli, $periodo_id, $usuario_id, $fecha, $monto_devolucion, $comision_devolucion, $devolucion_count)) {
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


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_devolucion_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $transaccion_calimaco_id = (int)$_POST["transaccion_calimaco_id"];
        $periodo_id = (int)$_POST["periodo_id"];

        if ($transaccion_calimaco_id > 0) {
            $error = '';
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_solicitud_anulacion = "
                INSERT INTO tbl_conci_devolucion_solicitud (
                    transaccion_id, status, created_at, user_created_id
                )  
                VALUES (?, 1, ?, ?)
            ";

            if ($stmtSolicitudAnulacion = $mysqli->prepare($sql_insert_solicitud_anulacion)) {
                $stmtSolicitudAnulacion->bind_param("isi", $transaccion_calimaco_id, $fecha, $usuario_id);

                try {
                    $stmtSolicitudAnulacion->execute();

                    $selectQueryProveedor = "
                        SELECT 
                            ct.id, ct.periodo_id, 
                            IFNULL(pt.monto, 0), IFNULL(pt.comision_total_calculado, 0), 
                            IFNULL(p.monto_devolucion, 0), IFNULL(p.comision_devolucion, 0), 
                            IFNULL(p.devolucion_count, 0)
                        FROM tbl_conci_calimaco_transaccion ct
                        LEFT JOIN tbl_conci_periodo p ON ct.periodo_id = p.id
                        LEFT JOIN tbl_conci_proveedor_transaccion pt ON pt.id = (
                            SELECT CAST(SUBSTRING_INDEX(ct.venta_proveedor_id, ',', 1) AS UNSIGNED)
                        )
                        WHERE ct.id = ? AND pt.status = 1
                        LIMIT 1
                    ";

                    if ($selectStmtProveedor = $mysqli->prepare($selectQueryProveedor)) {
                        $selectStmtProveedor->bind_param("i", $transaccion_calimaco_id);
                        $selectStmtProveedor->execute();
                        $selectStmtProveedor->store_result();

                        if ($selectStmtProveedor->num_rows > 0) {
                            $selectStmtProveedor->bind_result($id, $periodo_id, $monto, $comision_total_calculado, $monto_devolucion, $comision_devolucion, $devolucion_count);
                            $selectStmtProveedor->fetch();
                            $selectStmtProveedor->close();

                            $monto_devolucion += $monto;
                            $comision_devolucion += $comision_total_calculado;
                            $devolucion_count += 1;

                            if (!fn_conci_updatePeriodoDevolucion($mysqli, $periodo_id, $usuario_id, $fecha, $monto_devolucion, $comision_devolucion, $devolucion_count)) {
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
                                "error" => "Sin registro de transaccion"
                            ];
                        }
                    } else {
                        $result = [
                            "http_code" => 400,
                            "error" => "Error en la preparación de la consulta: " . $mysqli->error
                        ];
                    }
                } catch (Exception $e) {
                    $result = [
                        "http_code" => 400,
                        "error" => "Error al ejecutar la consulta: " . $e->getMessage()
                    ];
                }
            } else {
                $result = [
                    "http_code" => 400,
                    "error" => "Error en la preparación de la consulta: " . $mysqli->error
                ];
            }
        } else {
            $result = [
                "http_code" => 400,
                "error" => "La transacción no está activa. Contactarse con soporte"
            ];
        }
    } else {
        $result = [
            "http_code" => 400,
            "error" => "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña."
        ];
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