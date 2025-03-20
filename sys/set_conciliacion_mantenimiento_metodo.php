<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_metodo_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
		$nombre = $_POST["nombre"];
        $proveedor_id = $_POST["proveedor_id"];

        if ((int)$id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_razon_social = "
					UPDATE tbl_conci_calimaco_metodo
					SET 
                        nombre = ?,
                        proveedor_id = ?,         
                        updated_at = ?, 
                        user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_razon_social);


            if ($stmt) {
                $stmt->bind_param("sisii", 
                                            $nombre,
                                            $proveedor_id,
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

            $sql_insert_razon_social = "
                INSERT INTO tbl_conci_calimaco_metodo (
                    nombre,
                    proveedor_id,
                    status,    
                    created_at,
                    user_created_id
                )  
                VALUES (?,?,1,?,?)
            ";

            $stmt = $mysqli->prepare($sql_insert_razon_social);


            if ($stmt) {
                $stmt->bind_param("sisi", 
                                        $nombre,
                                        $proveedor_id,                        
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_metodo_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); 

    if ((int)$usuario_id > 0) {
        $id_metodo = $_POST["id_metodo"];

        if ((int)$id_metodo > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_cuenta_contable = "UPDATE tbl_conci_calimaco_metodo 
                                         SET 
                                             status = 0,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_cuenta_contable);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_metodo);

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

