<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

//////////   FUNCIONES PARA RAZON SOCIAL

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_razon_social_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
		$nombre = $_POST["nombre"];
        $subdiario = $_POST["subdiario"];
        $codigo_empresa = $_POST["codigo_empresa"];
        $estado_vale = $_POST["estado_vale"];
        $ruc = $_POST["ruc"];
        $canal = $_POST["canal"];
        $red = $_POST["red"];
        $estado_tesoreria = $_POST["estado_tesoreria"];
        $subdiario_contabilidad = $_POST["subdiario_contabilidad"];
        $codigo_sap = $_POST["codigo_sap"];
        $subdiario_compra_con_igv = $_POST["subdiario_compra_con_igv"];
        $subdiario_compra_sin_igv = $_POST["subdiario_compra_sin_igv"];
        $subdiario_cancelacion_caja_chica = $_POST["subdiario_cancelacion_caja_chica"];
        $habilitado_servicios_publicos = $_POST["habilitado_servicios_publicos"];
        $habilitado_prestamo_boveda = $_POST["habilitado_prestamo_boveda"];
        $habilitado_recargas_kasnet = $_POST["habilitado_recargas_kasnet"];

        if ((int)$id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_razon_social = "
					UPDATE tbl_razon_social
					SET 
                        nombre = ?, 
                        subdiario = ?, 
                        codigo_empresa = ?, 
                        estado_vale = ?, 
                        ruc = ?, 
                        canal_id = ?, 
                        red_id = ?, 
                        estado_tesoreria = ?, 
                        subdiario_contabilidad = ?, 
                        codigo_sap = ?, 
                        subdiario_compra_con_igv = ?, 
                        subdiario_compra_sin_igv = ?, 
                        subdiario_cancelacion_caja_chica = ?, 
                        permiso_servicios_publicos = ?, 
                        habilitado_prestamo_boveda = ?, 
                        habilitado_recargas_kasnet = ?, 
                        updated_at = ?, 
                        user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_razon_social);


            if ($stmt) {
                $stmt->bind_param("sssssiissssssssssii", 
                                                        $nombre, 
                                                        $subdiario,
                                                        $codigo_empresa, 
                                                        $estado_vale, 
                                                        $ruc,
                                                        $canal,
                                                        $red,
                                                        $estado_tesoreria,
                                                        $subdiario_contabilidad,
                                                        $codigo_sap,
                                                        $subdiario_compra_con_igv,
                                                        $subdiario_compra_sin_igv,
                                                        $subdiario_cancelacion_caja_chica,
                                                        $habilitado_servicios_publicos,
                                                        $habilitado_prestamo_boveda,
                                                        $habilitado_recargas_kasnet,
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
                INSERT INTO tbl_razon_social (
                    nombre,
                    subdiario,
                    codigo_empresa,
                    estado_vale,
                    ruc,
                    canal_id,
                    red_id,
                    estado_tesoreria,
                    subdiario_contabilidad,
                    codigo_sap,
                    subdiario_compra_con_igv,
                    subdiario_compra_sin_igv,
                    subdiario_cancelacion_caja_chica,
                    permiso_servicios_publicos,
                    habilitado_prestamo_boveda,
                    habilitado_recargas_kasnet,
                    status,
                    created_at,
                    user_created_id
                )  
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 1, ?, ?)
            ";

            $stmt = $mysqli->prepare($sql_insert_razon_social);


            if ($stmt) {
                $stmt->bind_param("sssssiissssssssssi", 
                                        $nombre, 
                                        $subdiario,
                                        $codigo_empresa, 
                                        $estado_vale, 
                                        $ruc,
                                        $canal,
                                        $red,
                                        $estado_tesoreria,
                                        $subdiario_contabilidad,
                                        $codigo_sap,
                                        $subdiario_compra_con_igv,
                                        $subdiario_compra_sin_igv,
                                        $subdiario_cancelacion_caja_chica,
                                        $habilitado_servicios_publicos,
                                        $habilitado_prestamo_boveda,
                                        $habilitado_recargas_kasnet,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_razon_social_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id_razon_social = $_POST["id_razon_social"];

        if ((int)$id_razon_social > 0) {
            $error = '';

            $query_update_subdiario = "
                UPDATE tbl_razon_social 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

            $stmt = $mysqli->prepare($query_update_subdiario);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_razon_social);

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