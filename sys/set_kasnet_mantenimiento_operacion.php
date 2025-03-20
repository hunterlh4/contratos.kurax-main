<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS

if (isset($_POST["accion"]) && $_POST["accion"] === "kasnet_mant_operacion_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id = $_POST["form_modal_kasnet_mant_operacion_param_id"] ?? 0;
        $param_nombre = $_POST["form_modal_kasnet_mant_operacion_param_nombre"];
        $param_tipo_id = $_POST["form_modal_kasnet_mant_operacion_param_tipo_id"];

        $mysqli->begin_transaction();

        try {
            if ((int)$id > 0) {

                // Actualización de un registro existente
                
                $stmt = $mysqli->prepare("
                    SELECT 
                        IFNULL(o.nombre, ''), 
                        IFNULL(t.nombre, '')
                    FROM tbl_kasnet_operacion o
                    LEFT JOIN tbl_kasnet_operacion_tipo t ON t.id=o.tipo_id
                    WHERE o.id = ?
                    LIMIT 1
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($nombre, $tipo_id);
                $stmt->fetch();
                $stmt->close();

                $campos_originales = ['nombre' => $nombre, 'tipo_id' => $tipo_id];
                $cambios_realizados = false;

                $historial_query = $mysqli->prepare("
                    INSERT INTO tbl_kasnet_operacion_historial_cambio (
                        operacion_id, valor_anterior, valor_nuevo, nombre_campo, 
                        status, user_created_id, created_at
                    ) VALUES (?, ?, ?, ?, 1, ?, ?)
                ");

                foreach ($campos_originales as $campo => $valor_anterior) {
                    $valor_nuevo = $_POST[$campo];
                    if ($valor_anterior !== $valor_nuevo) {
                        $cambios_realizados = true;
                        $historial_query->bind_param("isssis", $id, $valor_anterior, $valor_nuevo, $campo, $usuario_id, $fecha);
                        $historial_query->execute();
                    }
                }

                $historial_query->close();

                if ($cambios_realizados) {
                    $query_update = "
                        UPDATE tbl_kasnet_operacion
                        SET nombre = ?, tipo_id = ?, user_updated_id = ?, updated_at = ?
                        WHERE id = ?
                    ";
                    $stmt = $mysqli->prepare($query_update);
                    $stmt->bind_param("siisi", $param_nombre, $param_tipo_id, $usuario_id, $fecha, $id);
                    $stmt->execute();
                    $stmt->close();

                } else {
                   throw new Exception("No hay cambios para guardar");
                }

            } else {

                // Creación de un nuevo registro

                $stmt = $mysqli->prepare("
                    INSERT INTO tbl_kasnet_operacion (nombre, tipo_id, status, created_at, user_created_id)
                    VALUES (?, ?, 1, ?, ?)
                ");
                $stmt->bind_param("sisi", $param_nombre, $param_tipo_id,  $fecha, $usuario_id);
                $stmt->execute();
                $comision_id = $stmt->insert_id;
                $stmt->close();

            }

            $mysqli->commit();
            $result = ["http_code" => 200, "titulo" => "Operación exitosa", "descripcion" => "La operación se guardó exitosamente"];
        } catch (Exception $e) {
            $mysqli->rollback();
            $result = ["http_code" => 400, "titulo" => "Error", "descripcion" => $e->getMessage()];
        }
    } else {
        $result = ["http_code" => 400, "titulo" => "Error", "descripcion" => "La sesión ha caducado. Por favor, vuelva a iniciar sesión."];
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "kasnet_mant_operacion_cambiar_estado") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); 

    if ((int)$usuario_id > 0) {
        $id_operacion = $_POST["id_operacion"];
        $estado = $_POST["estado"];

        if ((int)$id_operacion > 0) {

            if($estado ==1){
                $new_estado = "Activo";
                $last_estado = "Inactivo";
            }else{
                $new_estado = "Inactivo";
                $last_estado = "Activo";
            }

            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_cuenta_contable = "UPDATE tbl_kasnet_operacion 
                                         SET 
                                             status = ?,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_cuenta_contable);
            $stmt->bind_param("iiss", $estado,$usuario_id, $fecha, $id_operacion);

            if ($stmt->execute()) {

                $campo = "status";

                $historial_query = $mysqli->prepare("
                    INSERT INTO tbl_kasnet_operacion_historial_cambio (
                        operacion_id, valor_anterior, valor_nuevo, nombre_campo, 
                        status, user_created_id, created_at
                    ) VALUES (?, ?, ?, ?, 1, ?, ?)
                ");

                $historial_query->bind_param("isssis", $id_operacion, $last_estado, $new_estado, $campo, $usuario_id, $fecha);
                $historial_query->execute();
                $historial_query->close();

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