<?php
include("db_connect.php");
include("sys_login.php");

$result=array();
$usuario_id = $login ? $login['id'] : null;
$fecha = date("Y-m-d H:i:s");

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_zona_sub_gerente")
{
    $sub_gerente_usuario_update = $_POST["param_modal_sub_gerente_usuario_update"];
    $sub_gerente_zona = $_POST["update_sub_gerente_zona"];

    $query_update_zona_jefe=$mysqli->query("UPDATE tbl_zonas SET sub_gerente_id='$sub_gerente_usuario_update' WHERE id = '$sub_gerente_zona'");

    if($query_update_zona_jefe)
    {
        echo "Actualización finalizada";
    } 
    else
    {
        echo "Error de actualización";
    }

    exit();

} else if (isset($_POST["accion"]) && $_POST["accion"] === "buscar_otras_zonas") {
    $result = [];
    $result["error"] = false;
    $query_search_jefe_otra_zona = "SELECT nombre FROM tbl_zonas WHERE jop_id = '{$_POST['update_jefe']}' LIMIT 1";
    $search_jefe_otra_zona = $mysqli->query($query_search_jefe_otra_zona);
    while ($row = $search_jefe_otra_zona->fetch_assoc()) {
        $result['otra_zona'] = $row["nombre"];
        $result["error"] = true;
    }
    echo json_encode($result);
    exit();
}

$zona_id_update=$_POST['update_zona'];


    // Dar de baja al usuario anterior

        // Obtener id de usuario
        $stmt_select_personal_anterior_id = $mysqli->prepare("SELECT jop_id FROM tbl_zonas WHERE id = ? LIMIT 1");
        $stmt_select_personal_anterior_id->bind_param("i", $zona_id_update);
        $stmt_select_personal_anterior_id->execute();
        $personal_anterior_id_update = $stmt_select_personal_anterior_id->get_result()->fetch_assoc();
        $stmt_select_personal_anterior_id->close();

        $id_personal_anterior = $personal_anterior_id_update["jop_id"];

        // Obtener id de usuario
        $stmt_select_usuario_anterior_id = $mysqli->prepare("SELECT id FROM tbl_usuarios WHERE personal_id = ? LIMIT 1");
        $stmt_select_usuario_anterior_id->bind_param("i", $id_personal_anterior);
        $stmt_select_usuario_anterior_id->execute();
        $usuario_anterior_id_update = $stmt_select_usuario_anterior_id->get_result()->fetch_assoc();
        $stmt_select_usuario_anterior_id->close();
    
        $id_usuario_anterior = $usuario_anterior_id_update["id"];

        // Eliminar permisos de usuario anterior a locales de la zona
        $stmt_delete_permisos_usuario_anterior = $mysqli->prepare("UPDATE tbl_usuarios_locales ul
                                                    INNER JOIN tbl_locales l ON l.id = ul.local_id
                                                    SET ul.estado = 0, ul.user_updated_id = ?, ul.updated_at = ? 
                                                    WHERE ul.usuario_id = ? AND l.zona_id=?");
        $stmt_delete_permisos_usuario_anterior->bind_param("isii", $usuario_id, $fecha, $id_usuario_anterior,$zona_id_update);
        $stmt_delete_permisos_usuario_anterior->execute();
        $stmt_delete_permisos_usuario_anterior->close();

    // Guardar jefe de zona

        $id_update=$_POST['update_jefe'];

        // Seleccionar el ID de usuario
        $stmt_select_usuario_id = $mysqli->prepare("SELECT id FROM tbl_usuarios WHERE personal_id = ? LIMIT 1");
        $stmt_select_usuario_id->bind_param("i", $id_update);
        $stmt_select_usuario_id->execute();
        $usuario_id_update = $stmt_select_usuario_id->get_result()->fetch_assoc();
        $stmt_select_usuario_id->close();

        $id_usuario_update = $usuario_id_update["id"];
        // Asignar id de usuario a zona

        $query_update_zona_jefe=$mysqli->query("UPDATE tbl_zonas SET jop_id='$id_update' WHERE id = '$zona_id_update'");

        // Se actualiza la zona del personal jefe
        $query_update_personal_jefe_antiguo=$mysqli->query("UPDATE tbl_personal_apt SET zona_id = NULL WHERE id = '$id_personal_anterior'");
        $query_update_personal_jefe=$mysqli->query("UPDATE tbl_personal_apt SET zona_id = '$zona_id_update' WHERE id = '$id_update'");

        // Eliminar permisos actuales de usuario nuevo a locales
        $stmt_delete_permisos = $mysqli->prepare("UPDATE tbl_usuarios_locales SET estado = 0, user_updated_id = ?, updated_at = ? WHERE usuario_id = ?");
        $stmt_delete_permisos->bind_param("isi", $usuario_id, $fecha, $id_usuario_update);
        $stmt_delete_permisos->execute();
        $stmt_delete_permisos->close();

        // Obtener zonas asignadas al usuario
        $stmt_zonas_asignadas = $mysqli->prepare("SELECT id FROM tbl_zonas WHERE jop_id = ?");
        $stmt_zonas_asignadas->bind_param("i", $id_update);
        $stmt_zonas_asignadas->execute();
        $list_query_zonas_asignadas = $stmt_zonas_asignadas->get_result();
        $stmt_zonas_asignadas->close();

        // Crear permisos de usuario a locales de zonas asignadas
        $stmt_insert_permisos = $mysqli->prepare("INSERT INTO tbl_usuarios_locales (usuario_id, local_id, estado, user_created_id, created_at) 
                                                SELECT ?, id, 1, ?, ? FROM tbl_locales WHERE zona_id = ?");

        while ($fila = $list_query_zonas_asignadas->fetch_array()) {
            $stmt_insert_permisos->bind_param("issi", $id_usuario_update, $usuario_id, $fecha, $fila["id"]);
            $stmt_insert_permisos->execute();
        }

        $stmt_insert_permisos->close();


if($stmt_insert_permisos) {
    echo "Actualización finalizada";
} else{
    echo "Error de actualización";
}
?>