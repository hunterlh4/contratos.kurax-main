<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_tipo_cambio_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
		$moneda_id = $_POST["moneda_id"];
        $fecha_cambio = date("Y-m-d", strtotime($_POST['fecha']));
		$monto_venta = $_POST["monto_venta"];
		$monto_compra = $_POST["monto_compra"];

        $where_tipo_cambio = "";
        if($id > 0){
            $where_tipo_cambio = " AND pi.id != ".$id;
        }

        //  Validacion de fecha
            $query = "
                        SELECT 
                            pi.id
                        FROM tbl_conci_tipo_cambio pi
                        WHERE pi.status = 1 AND pi.fecha = ? AND pi.moneda_id = ?
                        $where_tipo_cambio";

            if ($stmt = $mysqli->prepare($query)) {

                $stmt->bind_param("si", $fecha_recaudacion, $moneda_id);

                if ($stmt->execute()) {
                    $resultStmt = $stmt->get_result();
                    $tipo_cambio = $resultStmt->num_rows;
                    $stmt->close();

                    if ($tipo_cambio > 0) {
                        $result["http_code"] = 400;
                        $result["status"] = "Error";
                        $result["error"] = "Ya existe el tipo de cambio para esa fecha";
                        echo json_encode($result);
                        exit();
                    } else {

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


        if ((int)$id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_tipo_cambio = "
					UPDATE tbl_conci_tipo_cambio
					SET 
                        moneda_id = ?,
                        fecha = ?,  
                        monto_venta = ?,         
                        monto_compra = ?, 
                        updated_at = ?, 
                        user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_tipo_cambio);


            if ($stmt) {
                $stmt->bind_param("isddsii", 
                                            $moneda_id,
                                            $fecha_cambio,
                                            $monto_venta,
                                            $monto_compra,
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

            $error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_tipo_cambio = "
                INSERT INTO tbl_conci_tipo_cambio (
                    moneda_id,
                    fecha,
                    monto_venta,
                    monto_compra,
                    status,    
                    created_at,
                    user_created_id
                )  
                VALUES (?,?,?,?,1,?,?)
            ";

            $stmt = $mysqli->prepare($sql_insert_tipo_cambio);


            if ($stmt) {
                $stmt->bind_param("isddsi", 
                                        $moneda_id,
                                        $fecha_cambio,  
                                        $monto_venta,
                                        $monto_compra,                
                                        $fecha,
                                        $usuario_id
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
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_tipo_cambio_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); 

    if ((int)$usuario_id > 0) {
        $id_tipo_cambio = $_POST["id_tipo_cambio"];

        if ((int)$id_tipo_cambio > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_cuenta_contable = "UPDATE tbl_conci_tipo_cambio 
                                         SET 
                                             status = 0,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_cuenta_contable);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_tipo_cambio);

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

