<?php
include_once("db_connect.php");
include("global_config.php");
include_once("sys_login.php");
include_once("globalFunctions/generalInfo/local.php");

if (isset($_POST["accion"]) && $_POST["accion"] == 'cargar_manual') {
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if ($ext == "pdf") {
        $result = false;

        $file = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];
        $size = $_FILES['file']['size'];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $menuTitulo = $_POST['menuTitulo'];
        $tipoManual = $_POST['tipoManual'];
        if($tipoManual == 1){
            $nombretipoManual= "M. Usuario";
        }else{
            $nombretipoManual= "G. Acceso";
        }

        //$filename = strtolower($nombretipoManual."_".preg_replace('/.[\w]+$/', '', $menuTitulo)."_".date('YmdHis') . "." . $ext);
        //$filename = strtolower($nombretipoManual."_".$menuTitulo. "." . $ext);
        $filename = $nombretipoManual."_".$menuTitulo. "." . $ext;

        if (isset($_POST['menuId'])) {
            $menuId = $_POST['menuId'];

            if ($size <= 10485760) {
                $path = '/var/www/html';
                $filepath = '/files_bucket/manuales/' . $filename;
                $moved = move_uploaded_file($tmp, $path.$filepath);

                if ($moved) {

                    //inactivar el anterior
                    $query = "   UPDATE tbl_archivos_manuales
                                set estado = 0, updated_at = now(), user_updated_id = " . $login['id'] . " 
                                WHERE menu_sistemas_id = $menuId and tipo = $tipoManual
                                ";

                    $mysqli->query($query);

                    $query = "INSERT INTO tbl_archivos_manuales(tipo, filepath, menu_sistemas_id, estado, created_at, user_created_id) 
                    VALUES( $tipoManual, '$filepath', $menuId, 1, now(), " . $login['id'] . ")";

                    $mysqli->query($query);


                    echo json_encode([
                        'estado' => 'success',
                        'msg' => 'Manual cargado con éxito.'
                    ]);
                    exit();
                } else {
                    $msg .= 'No se pudo guardar el archivo. Error con el archivo o la carpeta de destino.';
                }
            } else {
                $msg .= 'Archivo demasiado grande.';
            }
        } else {
            $msg = 'No existe el menu. ';
        }



        echo (json_encode([
            'estado' => 'warning',
            'msg' => $msg
        ]));
        exit();
    } else {
        echo json_encode([
            'error' => true,
            'msg' => 'Revise el formato del archivo que intenta importar.'
        ]);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] == 'get_path_manuales') {

    $tipoManual = $_POST['tipoManual'];
    $menuId = $_POST['menuId'];

    $query = "SELECT id, tipo, filepath FROM tbl_archivos_manuales
                WHERE tipo = $tipoManual AND menu_sistemas_id = $menuId AND estado = 1 ";

    $result = $mysqli->query($query);

    $manuales = [];
    while ($r = $result->fetch_assoc()) {
        $manuales[$r['tipo']] = $r['filepath'];
    }

    $response = [
        'estado' => 'success',
    ];

    if (isset($manuales[$tipoManual])) {
        $response[$tipoManual] = $manuales[$tipoManual];
    } else {
        $response[$tipoManual] = "0";
    }

    echo json_encode($response);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] == 'delete_manual') {

    $tipoManual = $_POST['tipoManual'];
    $menuId = $_POST['menuId'];

    $query = "  UPDATE tbl_archivos_manuales
                set estado = 0, updated_at = now(), user_updated_id = " . $login['id'] . " 
                WHERE estado = 1 AND menu_sistemas_id = $menuId and tipo = $tipoManual
            ";

    $mysqli->query($query);

    echo json_encode([
        'estado' => 'success',
        'msg' => 'Se eliminó el manual.'
    ]);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "get_historico_cambios") {
    $tipoManual = $_POST['tipoManual'];
    $menuId = $_POST['menuId'];

    $query = "
			SELECT 
            DATE_FORMAT(am.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
			u.usuario AS user_created_id,
            DATE_FORMAT(am.updated_at, '%d/%m/%Y %H:%i:%s') AS updated_at,
            uu.usuario AS user_updated_id,
            am.estado
		FROM tbl_archivos_manuales am
        LEFT JOIN tbl_usuarios u
		ON am.user_created_id = u.id
        LEFT JOIN tbl_usuarios uu
		ON am.user_updated_id = uu.id
        WHERE tipo = $tipoManual AND menu_sistemas_id = $menuId
        ORDER BY am.created_at DESC;
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $list_query = $stmt->get_result();
    $stmt->close();

    $data = [];

    if ($mysqli->error) {
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => ''
        ];

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_assoc()) {
			$estadoHTML = ($reg['estado'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

            $data[] = [
                "0" => $cont,
                "1" => $reg['created_at'],
                "2" => $reg['user_created_id'],
                "3" => $estadoHTML,
                "4" => $reg['updated_at'],
                "5" => $reg['user_updated_id']
            ];

            $cont++;
        }

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
    }

    echo json_encode($resultado);
}
