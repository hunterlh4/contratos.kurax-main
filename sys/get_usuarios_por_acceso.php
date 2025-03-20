<?php

$result = array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (isset($_POST["accion"]) && $_POST["accion"] === "get_usuarios_por_acceso") {
    
    $SecAcceso_usuario_id = $_POST['SecAcceso_usuario_id'];
    $SecAcceso_usuario_id = intval($SecAcceso_usuario_id);
    
    $SecAcceso_area_id = $_POST['SecAcceso_area_id'];
    $SecAcceso_area_id = intval($SecAcceso_area_id);
    
    $SecAcceso_cargo_id = $_POST['SecAcceso_cargo_id'];
    $SecAcceso_cargo_id = intval($SecAcceso_cargo_id);

    $query = "SELECT
        t1.id,
        CONCAT(t2.nombre,' ',t2.apellido_paterno) personal,
        t3.nombre area,
        t4.nombre cargo,
        t6.titulo,
        t5.boton_nombre,
        t6.id AS menu_id,
        t6.relacion_id
    FROM tbl_usuarios t1
    INNER JOIN tbl_personal_apt t2 ON t2.id = t1.personal_id
    INNER JOIN tbl_areas t3 ON t3.id = t2.area_id
    INNER JOIN tbl_cargos t4 ON t4.id = t2.cargo_id
    INNER JOIN tbl_permisos t5 ON t5.usuario_id = t1.id
    INNER JOIN tbl_menu_sistemas t6 ON t6.id = t5.menu_id
    WHERE t1.estado = 1 AND t2.estado = 1 AND t5.estado = 1
    AND (t6.relacion_id = 0 OR t6.relacion_id IS NULL) AND t5.boton_id = 1";
    
    if ($SecAcceso_usuario_id != 0) {
        $query .= " AND t1.id = $SecAcceso_usuario_id";
    }

    if ($SecAcceso_area_id != 0) {
        $query .= " AND t3.id = $SecAcceso_area_id";
    }

    if ($SecAcceso_cargo_id != 0) {
        $query .= " AND t4.id = $SecAcceso_cargo_id";
    }
    
    $query .= " ORDER BY t6.id";
    
    $result = $mysqli->query($query);

    $personalData = [];
    $menuData = [];

    // Procesar los resultados y estructurarlos
    while ($row = $result->fetch_assoc()) {
        // Guardar datos de usuario, área y cargo
        if (!isset($personalData[$row['id']])) {
            $personalData[$row['id']] = [
                'personal' => $row['personal'],
                'area' => $row['area'],
                'cargo' => $row['cargo']
            ];
        }

        // Solo guardar datos de menú principal si no está ya en la lista
        if (!isset($menuData[$row['id']])) {
            $menuData[$row['id']] = [];
        }

        if (!in_array($row['titulo'], $menuData[$row['id']])) {
            $menuData[$row['id']][] = $row['titulo'];
        }
    }

    // Generar datos para el listado
    $data = [];
    $cont = 1;
    foreach ($personalData as $userId => $userData) {
        $menuColumna = isset($menuData[$userId]) ? implode("<br>", $menuData[$userId]) : '';

        $data[] = [
            "0" => $cont,
            "1" => $userData['personal'],
            "2" => $userData['area'],
            "3" => $userData['cargo'],
            "4" => $menuColumna
        ];
        $cont++;
    }

    $resultado = [
        "sEcho" => 1,
        "iTotalRecords" => count($data),
        "iTotalDisplayRecords" => count($data),
        "aaData" => $data
    ];

    echo json_encode($resultado);
    exit;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "export_usuarios_por_acceso") {
    $SecAcceso_usuario_id = $_POST['SecAcceso_usuario_id'];
    $SecAcceso_usuario_id = intval($SecAcceso_usuario_id);

    $SecAcceso_area_id = $_POST['SecAcceso_area_id'];
    $SecAcceso_area_id = intval($SecAcceso_area_id);

    $SecAcceso_cargo_id = $_POST['SecAcceso_cargo_id'];
    $SecAcceso_cargo_id = intval($SecAcceso_cargo_id);

    $query = "SELECT
        t1.id,
        CONCAT(t2.nombre,' ',t2.apellido_paterno) AS personal,
        t3.nombre AS area,
        t4.nombre AS cargo,
        t6.titulo AS menu_titulo
    FROM tbl_usuarios t1
    INNER JOIN tbl_personal_apt t2 ON t2.id = t1.personal_id
    INNER JOIN tbl_areas t3 ON t3.id = t2.area_id
    INNER JOIN tbl_cargos t4 ON t4.id = t2.cargo_id
    INNER JOIN tbl_permisos t5 ON t5.usuario_id = t1.id
    INNER JOIN tbl_menu_sistemas t6 ON t6.id = t5.menu_id
    WHERE t1.estado = 1 AND t2.estado = 1 AND t5.estado = 1
    AND (t6.relacion_id = 0 OR t6.relacion_id IS NULL) AND t5.boton_id = 1";

    if ($SecAcceso_usuario_id != 0) {
        $query .= " AND t1.id = $SecAcceso_usuario_id";
    }

    if ($SecAcceso_area_id != 0) {
        $query .= " AND t3.id = $SecAcceso_area_id";
    }

    if ($SecAcceso_cargo_id != 0) {
        $query .= " AND t4.id = $SecAcceso_cargo_id";
    }

    $query .= " ORDER BY t6.id";

    $result = $mysqli->query($query);

    if (!$result) {
        error_log("Error en la consulta: " . $mysqli->error);
        echo json_encode([
            "error" => "Error en la consulta: " . $mysqli->error
        ]);
        exit;
    }

    $personalData = [];
    $menuData = [];

    // Procesar los resultados y estructurarlos
    while ($row = $result->fetch_assoc()) {
        if (!isset($personalData[$row['id']])) {
            $personalData[$row['id']] = [
                'personal' => $row['personal'],
                'area' => $row['area'],
                'cargo' => $row['cargo']
            ];
        }

        if (!isset($menuData[$row['id']])) {
            $menuData[$row['id']] = [];
        }

        if (!in_array($row['menu_titulo'], $menuData[$row['id']])) {
            $menuData[$row['id']][] = $row['menu_titulo'];
        }
    }

    $data = [];
    foreach ($personalData as $userId => $userData) {
        $menuColumna = isset($menuData[$userId]) ? implode(", ", $menuData[$userId]) : '';

        $data[] = [
            'personal' => $userData['personal'],
            'area' => $userData['area'],
            'cargo' => $userData['cargo'],
            'menu' => $menuColumna
        ];
    }

    if (empty($data)) {
        echo json_encode(["error" => "No data found"]);
        exit;
    }

    $headers = [
        "personal" => "Personal",
        "area" => "Área",
        "cargo" => "Cargo",
        "menu" => "Menú"
    ];

    array_unshift($data, $headers);

    require '../phpexcel/classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->fromArray($data, null, 'A1');
    $date = new DateTime();
    $file_title = "reporte_usuarios_acceso_" . $date->getTimestamp() . "_" . $SecAcceso_usuario_id;

    if (!file_exists('/var/www/html/export/files_exported/reporte_usuarios_acceso/')) {
        mkdir('/var/www/html/export/files_exported/reporte_usuarios_acceso/', 0777, true);
    }

    $excel_path = '/var/www/html/export/files_exported/reporte_usuarios_acceso/' . $file_title . '.xlsx';
    $excel_path_download = '/export/files_exported/reporte_usuarios_acceso/' . $file_title . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $file_title . '.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    try {
        $objWriter->save($excel_path);
    } catch (PHPExcel_Writer_Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
        exit;
    }

    echo json_encode([
        "path" => $excel_path_download,
        "url" => $file_title . '.xlsx',
        "tipo" => "excel",
        "ext" => "xlsx",
        "size" => filesize($excel_path),
        "fecha_registro" => date("d-m-Y H:i:s")
    ]);

    exit;
}



echo json_encode($result);

?>