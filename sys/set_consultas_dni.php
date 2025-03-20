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

//////////   FUNCIONES PARA RAZON SOCIAL

if (isset($_POST["accion"]) && $_POST["accion"] === "consultas_dni_user_save") {

    $usuario_id = $login['id'] ?? null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id = (int)$_POST["id"];
        $param_nombres = $_POST["form_modal_sec_consulta_dni_param_nombres"];
        $param_apellido_paterno = $_POST["form_modal_sec_consulta_dni_param_apellido_paterno"];
        $param_apellido_materno = $_POST["form_modal_sec_consulta_dni_param_apellido_materno"];

        if ($id > 0) {
            $stmt = $mysqli->prepare("
                SELECT id, IFNULL(nombres, '') nombres, IFNULL(apellido_paterno, '') apellido_paterno, IFNULL(apellido_materno, '') apellido_materno
                FROM tbl_consultas_dni
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($user_id, $nombres, $apellido_paterno, $apellido_materno);
            $stmt->fetch();
            $stmt->close();

            $historial_query = $mysqli->prepare("
                INSERT INTO tbl_consultas_dni_historial_cambios (dni_id, valor_anterior, valor_nuevo, nombre_campo, status, user_created_id, created_at)
                VALUES (?, ?, ?, ?, 1, ?, ?)
            ");

            $campos_originales = [
                'nombres' => $nombres,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno
            ];

            $cambios_realizados = false;

            foreach ($campos_originales as $campo => $valor_anterior) {
                $valor_nuevo = $_POST["form_modal_sec_consulta_dni_param_$campo"];
                if ((string)$valor_anterior !== (string)$valor_nuevo) {
                    $cambios_realizados = true;
                    $historial_query->bind_param("isssis", $id, $valor_anterior, $valor_nuevo, $campo, $usuario_id, $fecha);
                    $historial_query->execute();
                }
            }

            $historial_query->close();

            if ($cambios_realizados) {
                $query_update_comprobante = "
                    UPDATE tbl_consultas_dni 
                    SET nombres = ?, apellido_paterno = ?, apellido_materno = ?
                    WHERE id = ?
                ";

                $update_stmt = $mysqli->prepare($query_update_comprobante);
                $update_stmt->bind_param("sssi", $param_nombres, $param_apellido_paterno, $param_apellido_materno, $id);
                $update_stmt->execute();

                if ($update_stmt->error) {
                    echo json_encode([
                        "http_code" => 400,
                        "titulo" => "Error al editar.",
                        "descripcion" => $update_stmt->error,
                        "query" => $query_update_comprobante
                    ]);
                    exit();
                }
                $update_stmt->close();

                echo json_encode([
                    "http_code" => 200,
                    "titulo" => "Edición exitosa",
                    "descripcion" => "El cliente se editó exitosamente."
                ]);
            } else {
                echo json_encode([
                    "http_code" => 400,
                    "titulo" => "Editar",
                    "descripcion" => "No hay cambios para guardar."
                ]);
            }
        }
    } else {
        echo json_encode([
            "http_code" => 400,
            "titulo" => "Error.",
            "descripcion" => "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña."
        ]);
    }
    exit();
}
