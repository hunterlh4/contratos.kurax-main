<?php

$result = array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (isset($_POST["accion"]) && $_POST["accion"] === "get_reportes_login_log") {
    
    $SecRepLog_fecha_inicio = $_POST['SecRepLog_fecha_inicio'];
    $SecRepLog_fecha_fin = $_POST['SecRepLog_fecha_fin'];
    $SecRepLog_usuario_id = $_POST['SecRepLog_usuario_id'];
    
    $SecRepLog_fecha_inicio = $mysqli->real_escape_string($SecRepLog_fecha_inicio);
    $SecRepLog_fecha_fin = $mysqli->real_escape_string($SecRepLog_fecha_fin);
    $SecRepLog_usuario_id = intval($SecRepLog_usuario_id);

    $query = "
        SELECT
        t2.usuario,
        t1.ip,
        t1.reason,
        t1.created_at 
        FROM tbl_login_log t1
        INNER JOIN tbl_usuarios t2 ON t2.id = t1.user_id
        WHERE t1.created_at BETWEEN '$SecRepLog_fecha_inicio' AND DATE_ADD('$SecRepLog_fecha_fin', INTERVAL 1 DAY) - INTERVAL 1 SECOND";
    
    if ($SecRepLog_usuario_id != 0) {
        $query .= " AND t2.id = $SecRepLog_usuario_id";
    }
    
    $result = $mysqli->query($query);
    
    if (!$result) {
        error_log("Error en la consulta: " . $mysqli->error);
        echo json_encode([
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => []
        ]);
        exit;
    }

    $data = [];
    $cont = 1;

    while ($reg = $result->fetch_assoc()) {
        $data[] = [
            "0" => $cont,
            "1" => $reg['usuario'],
            "2" => $reg['ip'],
            "3" => $reg['reason'],
            "4" => $reg['created_at']
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

if (isset($_POST["accion"]) && $_POST["accion"] === "export_reportes_login_log") {
    $SecRepLog_fecha_inicio = $_POST['fecha_inicio'];
    $SecRepLog_fecha_fin = $_POST['fecha_fin'];
    $SecRepLog_usuario_id = $_POST['usuario_id'];

    $SecRepLog_fecha_inicio = $mysqli->real_escape_string($SecRepLog_fecha_inicio);
    $SecRepLog_fecha_fin = $mysqli->real_escape_string($SecRepLog_fecha_fin);
    $SecRepLog_usuario_id = intval($SecRepLog_usuario_id);

    $query = "
        SELECT
        t2.usuario,
        t1.ip,
        t1.reason,
        t1.created_at 
        FROM tbl_login_log t1
        INNER JOIN tbl_usuarios t2 ON t2.id = t1.user_id
        WHERE t1.created_at BETWEEN '$SecRepLog_fecha_inicio' AND DATE_ADD('$SecRepLog_fecha_fin', INTERVAL 1 DAY) - INTERVAL 1 SECOND";
    
    if ($SecRepLog_usuario_id != 0) {
        $query .= " AND t2.id = $SecRepLog_usuario_id";
    }

    $result = $mysqli->query($query);
    
    if (!$result) {
        error_log("Error en la consulta: " . $mysqli->error);
        echo json_encode([
            "error" => "Error en la consulta: " . $mysqli->error
        ]);
        exit;
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    if (!$data) {
        echo json_encode(["error" => "No data found"]);
        exit;
    }

    $headers = [
        "usuario" => "Usuario",
        "ip" => "IP",
        "reason" => "Razón",
        "created_at" => "Fecha",
    ];

    array_unshift($data, $headers);

    require '../phpexcel/classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->fromArray($data, null, 'A1');
    $date = new DateTime();
    $file_title = "reporte_login_log_" . $date->getTimestamp() . "_" . $SecRepLog_usuario_id;

    if (!file_exists('/var/www/html/export/files_exported/reporte_login_log/')) {
        mkdir('/var/www/html/export/files_exported/reporte_login_log/', 0777, true);
    }

    $excel_path = '/var/www/html/export/files_exported/reporte_login_log/' . $file_title . '.xlsx';
    $excel_path_download = '/export/files_exported/reporte_login_log/' . $file_title . '.xlsx';

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