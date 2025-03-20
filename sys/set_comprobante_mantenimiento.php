<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");
include("function_replace_invalid_caracters.php");
include '/var/www/html/sys/envio_correos.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//  PROVEEDOR

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mant_proveedor_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$proveedor_id = $_POST["proveedor_id"];
		$ruc = $_POST["ruc"];
        $nombre = $_POST["nombre"];

        if ((int)$proveedor_id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_subdiario = "
					UPDATE tbl_comprobante_proveedor
					SET ruc = ?, nombre = ?, updated_at = ?, user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_subdiario);


            if ($stmt) {
                $stmt->bind_param("sssii", $ruc, $nombre, $fecha, $usuario_id,$proveedor_id);

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

            $sql_insert_subdiario = "
                INSERT INTO tbl_comprobante_proveedor (
                    ruc,
                    nombre,
                    status,
                    created_at,
                    user_created_id
                )  
                VALUES (?, ?, 1, ?, ?)
            ";

            $stmt = $mysqli->prepare($sql_insert_subdiario);


            if ($stmt) {
                $stmt->bind_param("sssi", $ruc, $nombre, $fecha, $usuario_id);

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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mant_proveedor_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id_proveedor = $_POST["id_proveedor"];

        if ((int)$id_proveedor > 0) {
            $error = '';

            $query_update_proveedor = "
                UPDATE tbl_comprobante_proveedor 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

            $stmt = $mysqli->prepare($query_update_proveedor);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_proveedor);

            if ($stmt->execute()) {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestion.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe el subdiario";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

//  MOTIVO

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mant_motivo_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id_motivo = $_POST["id_motivo"];
		$descripcion = $_POST["descripcion"];
        $nombre = $_POST["nombre"];

        if ((int)$id_motivo > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_subdiario = "
					UPDATE tbl_comprobante_motivo_reversion
					SET descripcion = ?, nombre = ?, updated_at = ?, user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_subdiario);


            if ($stmt) {
                $stmt->bind_param("sssii", $descripcion, $nombre, $fecha, $usuario_id,$id_motivo);

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

            $sql_insert_subdiario = "
                INSERT INTO tbl_comprobante_motivo_reversion (
                    descripcion,
                    nombre,
                    status,
                    created_at,
                    user_created_id
                )  
                VALUES (?, ?, 1, ?, ?)
            ";

            $stmt = $mysqli->prepare($sql_insert_subdiario);


            if ($stmt) {
                $stmt->bind_param("sssi", $descripcion, $nombre, $fecha, $usuario_id);

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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_mant_motivo_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id_motivo = $_POST["id_motivo"];

        if ((int)$id_motivo > 0) {
            $error = '';

            $query_update_subdiario = "
                UPDATE tbl_comprobante_motivo_reversion 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

            $stmt = $mysqli->prepare($query_update_subdiario);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_motivo);

            if ($stmt->execute()) {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestion.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe el subdiario";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}