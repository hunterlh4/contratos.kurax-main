<?php
$return = array();
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");

if (isset($_POST["get_incidencias_ca_historial_table"])) {
    if ($login == false) {
        $response = array(
            "error" => true,
            "login" => $login
        );
        echo json_encode($response);
        die();
    }
    $data = $_POST["get_incidencias_ca_historial_table"];
    $user_id = $data['user_id'] ?? 0;
    //$red_id_where = $data['red_id'] === "-1" ? "" : " AND loc.red_id = '$data[red_id]' ";

    $limit = 10;
    if ($login["area_id"] == 21 && $login["cargo_id"] == 16)//operaciones  jefe
    {
        $limit = 50;
    }
    if ($login["area_id"] == 15 && $login["cargo_id"] == 4)//comercial  supervisor
    {
        $limit = 50;
    }
    if ($login["area_id"] == 28 && $login["cargo_id"] == 4)//agentes supervisor
    {
        $limit = 50;
    }


    // Regular WHERE
    $where = "";
    if ($login["area_id"] == "21" && $login["cargo_id"] == "16") {
        $locales_id = implode(", ", $login["usuario_locales"]);
        $where = "loc.id IN ($locales_id) AND loc.red_id = 5 ";
    } else if ($login["area_id"] == "21" && $login["cargo_id"] == "4") {//operaciones  supervisor
        $locales_id = implode(", ", $login["usuario_locales"]);
        $where = "loc.id IN ($locales_id) ";
    } else if ($login["area_id"] == "15" && $login["cargo_id"] == "4") {//comercial  supervisor
        $locales_id = implode(", ", $login["usuario_locales"]);
        $where = "loc.id IN ($locales_id) ";
    } else {
        $where = "usu.id= $user_id";
    }

    $query =
        "
            SELECT inci.id as id
                ,inci.created_at as fecha
                ,usu.usuario as usuario
                ,usu.id as usuario_id
                ,loc.nombre as tienda
                ,loc.phone as telefono
                ,inci.incidencia_txt as incidencia
                ,CASE
                    WHEN inci.satisfaccion = 0 THEN 'Nada Satisfecho'
                    WHEN inci.satisfaccion = 1 THEN 'Poco Satisfecho'
                    WHEN inci.satisfaccion = 2 THEN 'Neutral'
                    WHEN inci.satisfaccion = 3 THEN 'Muy Satisfecho'
                    WHEN inci.satisfaccion = 4 THEN 'Totalmente Satisfecho'
                    ELSE null 
                 END AS satisfaccion                                    
                ,CASE 
                    WHEN inci.estado=0 THEN 'Nuevo' 
                    WHEN inci.estado=2 then 'Asignado'
                    ELSE 'Atendido'       
                    END AS estado
                ,ag.usuario as agente
                ,IFNULL(st.solucion_txt, inci.solucion_txt) as observacion,
                inci.tipo_incidencia,
                inci.detalle_incidencia
            FROM tbl_soporte_incidencias inci 
            left join tbl_locales loc on  inci.local_id=loc.id
            left join tbl_usuarios usu on usu.id= inci.user_id
            left join tbl_usuarios ag on ag.id= inci.update_user_id
            left join tbl_personal_apt usu_age_pers on usu_age_pers.id= ag.personal_id
            left join tbl_servicio_tecnico st on st.soporte_incidencias_id = inci.id
            where  $where
            group by inci.id
            order by  inci.id desc 
            LIMIT $limit
        ";
    $result = $mysqli->query($query);
    $result_data = array();
    while ($r = $result->fetch_assoc()) {
        $r["same"] = $user_id == $r["usuario_id"];
        $result_data[] = $r;
    }

    if (count($result_data) > 0) {
        $response = array(
            "data" => $result_data
        );
        echo json_encode($response);
        return;
    } else {
        $response = array(
            "error" => true,
            "error_msg" => "No hay registros",
            "query" => $query,
        );
        echo json_encode($response);
        return;
    }
} else if (isset($_POST["get_incidencias_ca_historial_table_excel"])) {
    $data = $_POST["get_incidencias_ca_historial_table_excel"];

    $user_id = $data['user_id'] ?? 0;

    $where = "";
    if ($login["area_id"] == "21" && $login["cargo_id"] == "16") {
        $locales_id = implode(", ", $login["usuario_locales"]);
        $where = "loc.id IN ($locales_id) ";
    } else {
        $where = "usu.id= $user_id";
    }

    $result_data = array();
    $result_data[] = [
        "id" => "Id",
        "fecha" => "Fecha",
        "usuario" => "Usuario",
        "tienda" => "Tienda",
        "telefono" => "Telefono",
        "incidencia" => "Incidencia",
        "satisfaccion" => "Satisfaccion",
        "estado" => "Estado",
        "agente" => "Agente",
        "observacion" => "Observacion",
    ];
    $query =
        "SELECT inci.id as id
                ,inci.created_at as fecha
                ,usu.usuario as usuario
                ,loc.nombre as tienda
                ,loc.phone as telefono
                ,inci.incidencia_txt as incidencia
                ,CASE
                    WHEN inci.satisfaccion = 0 THEN 'Nada Satisfecho'
                    WHEN inci.satisfaccion = 1 THEN 'Poco Satisfecho'
                    WHEN inci.satisfaccion = 2 THEN 'Neutral'
                    WHEN inci.satisfaccion = 3 THEN 'Muy Satisfecho'
                    WHEN inci.satisfaccion = 4 THEN 'Totalmente Satisfecho'
                    ELSE null 
                 END AS satisfaccion                                    
                ,CASE 
                    WHEN inci.estado=0 THEN 'Nuevo' 
                    WHEN inci.estado=2 then 'Asignado'
                    ELSE 'Atendido'       
                    END AS estado
                ,ag.usuario as agente
                ,IFNULL(st.solucion_txt, inci.solucion_txt) as observacion
            FROM tbl_soporte_incidencias inci 
            left join tbl_locales loc on  inci.local_id=loc.id
            left join tbl_usuarios usu on usu.id= inci.user_id
            left join tbl_usuarios ag on ag.id= inci.update_user_id
            left join tbl_personal_apt usu_age_pers on usu_age_pers.id= ag.personal_id
            left join tbl_servicio_tecnico st on st.soporte_incidencias_id = inci.id
            where  $where AND DATE(inci.created_at) BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()
            order by  inci.id desc                     
        ";

    $result = $mysqli->query($query);

    while ($r = $result->fetch_assoc()) {
        $result_data[] = $r;
    }

    require_once '../phpexcel/classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();

    try {
        $objPHPExcel->getActiveSheet()->fromArray($result_data);
    } catch (PHPExcel_Exception $e) {
        echo $e;
        die;
    }

    $titulo_reporte = "incidencias_${user_id}_" . date("Y-m-d");
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $titulo_reporte . '.xls"');
    header('Cache-Control: max-age=0');

    if (!file_exists('/var/www/html/export/files_exported/incidencias_ca/')) {
        mkdir('/var/www/html/export/files_exported/incidencias_ca/', 0777, true);
    }

    $excel_path = '/var/www/html/export/files_exported/incidencias_ca/' . $titulo_reporte . '.xls';
    $download_path = '/export/files_exported/incidencias_ca/' . $titulo_reporte . '.xls';

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save($excel_path);

    //header('Location: /export/files_exported/inc idencias_ca/'.$titulo_reporte . '.xls');

    echo json_encode(array(
        "path" => $download_path,
        "url" => $titulo_reporte . '.xls',
        "tipo" => "excel",
        "ext" => "xls",
        "size" => filesize($excel_path),
        "fecha_registro" => date("d-m-Y h:i:s"),
    ));

    exit;
} else if (isset($_POST["get_incidencias_ca_get_soporte_notas"])) {
    global $login;
    $area_id = (int)$login["area_id"];
    $cargo_id = (int)$login["cargo_id"];
    if (($area_id === 21 && $cargo_id === 5) || ($area_id === 6 && $cargo_id === 9)) //{ "cargo_id": "Cajero", "area_id": "Operaciones" }
    {
        $user_id = (int)$login["id"];
        global $mysqli;
        $query = "SELECT 
            sn.id,
            sn.nota_txt,
            sn.created_at,
            sn.estado,
            sn.imagen
FROM   tbl_soporte_notas AS sn
WHERE  sn.estado = 1
       AND sn.id NOT IN (SELECT asx.soporte_notas_id
                         FROM   tbl_soporte_notas_usuarios AS asx
                         WHERE  asx.usuario_id = {$user_id}
                                AND asx.estado = 1);";
        $data = [];
        $result = $mysqli->query($query);
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode([
            "message" => "Ok.",
            "data" => $data,
            "error" => false
        ], JSON_THROW_ON_ERROR);
    } else {
        echo json_encode([
            "message" => "El usuario no es cajero.",
            "data" => [],
            "error" => true
        ], JSON_THROW_ON_ERROR);
    }
} else if (isset($_POST["get_incidencias_ca_insert_soporte_notas_usuarios"])) {
    global $login;
    $soporte_notas_id = (int)$_POST['soporte_notas_id'];
    $user_id = (int)$login['id'];
    global $mysqli;

    $query = "SELECT id, estado FROM tbl_soporte_notas_usuarios WHERE soporte_notas_id = {$soporte_notas_id} AND usuario_id = {$user_id} LIMIT 1;";

    $result = $mysqli->query($query);

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data = $row;
    }

    if (count($data)) {
        $id = (int)$data['id'];
        $estado = (int)$data['estado'];
        if ($estado === 0) {
            $query = "UPDATE tbl_soporte_notas_usuarios SET estado = 1 WHERE id = {$id}";
        }
    } else {
        $query = "INSERT INTO tbl_soporte_notas_usuarios (soporte_notas_id, usuario_id, estado) VALUES ({$soporte_notas_id}, {$user_id}, 1);";
    }

    $result = $mysqli->query($query);

    echo json_encode([
        'message' => 'Ok.',
        'data' => [
            'insert_result' => $result
        ],
        'error' => false
    ], JSON_THROW_ON_ERROR);
}
?>