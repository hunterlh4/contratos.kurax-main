<?php
include("db_connect.php");
include("sys_login.php");

function get_num_files($id)
{
    global $mysqli;
    $numrows = 0;
    $query = "SELECT archivo AS img from tbl_archivos WHERE tabla='tbl_registro_premios' AND item_id=" . $id . " ";
    $result = $mysqli->query($query);
    $numrows = $result->num_rows;
    return $numrows;
}

function consultar_archivos($ticket_id, $type): array
{
    $photo_type = "tipo = 'foto'";
    if (isset($type)) {
        if ($type === "markt") {
            $photo_type = "(tipo = 'foto' OR tipo = 'foto_markt')";
        } else {
            $photo_type = "tipo = 'foto_" . $type . "'";
        }
    }
    global $mysqli;
    $query = "SELECT count(id) AS cant from tbl_archivos WHERE item_id =" . $ticket_id . " AND tabla ='tbl_registro_premios' AND  " . $photo_type;
    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $consulta = $r;
    return $consulta;
}

function get_reporte_premios_data($filters, $export): array
{
    global $mysqli, $login;

    $data = $filters;
    $data['offset'] =( $data['limit'] ?? 0) * ($data['page'] ?? 0);

    $where = " WHERE u.id NOT IN (249) ";

    if($login["usuario_locales"]){
        $where .=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
    }

    if ($data["local"] && $data["local"] !== "-1") {
        $where .= " AND l.id = $data[local] ";
    }
    if ($data["fecha_inicio"]) {
        $where .= " AND g.created_at >= '$data[fecha_inicio]' ";
    }

    if ($data["fecha_fin"]) {
        $where .= " AND g.created_at < '$data[fecha_fin]' ";
    }

    if ($data["texto"]){
        $where .= "
            AND 
            (
                g.ticket_id LIKE '%$data[texto]%' OR
                l.nombre LIKE '%$data[texto]%' OR
                g.created_at LIKE '%$data[texto]%' OR
                g.monto_apostado LIKE '%$data[texto]%' OR
                g.monto_entregado LIKE '%$data[texto]%' OR
                g.num_doc LIKE '%$data[texto]%' OR
                u.usuario LIKE '%$data[texto]%'
            )
        ";
    }

    if ($data["tipo"] >= 0){
        $where .= " AND g.tipo_registro = '$data[tipo]' ";
    }

    if (($data['limit'] ?? 0) != 0 && $export === false) {
        $limites = "LIMIT {$data['limit']} OFFSET {$data['offset']}";
    } else {
        $limites = " ";
    }

    $order_by_column = $data['colName'];
    $order_by = $data['order'];

    if ($order_by_column !== "default" && $order_by !== "default") {
        $order_by_command = "ORDER BY $order_by_column $order_by";
    } else {
        $order_by_command = "ORDER BY g.created_at DESC";
    }

    $query = "  SELECT
                    g.id,
                    g.ticket_id,
                    l.nombre,
                    g.created_at,
                    g.monto_apostado,
                    g.monto_entregado,
                    g.num_doc,
                    CASE
                        WHEN g.tipo_doc = 0 THEN CONCAT(
                            IFNULL(cd.nombres, ''),
                            ' ',
                            IFNULL(cd.apellido_materno, ''),
                            ' ',
                            IFNULL(cd.apellido_paterno, '')
                        )
                        WHEN g.tipo_doc = 2
                        OR g.tipo_doc = 1 THEN IFNULL(
                            (select CONCAT(
                                    IFNULL(ce.nombres, ''), ' ', IFNULL(ce.apellido_materno, ''), ' ', IFNULL(ce.apellido_paterno, '')
                                ) as nombre_cliente
                            from tbl_cliente_extranjero ce
                            where
                                ce.num_doc = g.num_doc
                            limit 1)
                        , 'NO DEFINIDO')
                    ELSE 'NO DEFINIDO'
                    END as cliente_ganador,
                    g.tipo_doc,
                    CASE g.tipo_doc
                        WHEN '0' THEN 'DNI'
                        WHEN '1' THEN 'CARNÉ EXTRANJERÍA'
                        WHEN '2' THEN 'PASAPORTE'
                        ELSE 'NO DEFINIDO'
                    END AS nombre_tipo_doc,  
                    u.usuario,
                    g.tipo_registro AS tipo_premio,
                    CASE g.tipo_registro
                        WHEN '0' THEN 'JACKPOT'
                        WHEN '1' THEN 'BINGO'
                        WHEN '2' THEN 'PREMIO MAYOR'
                        WHEN '3' THEN 'SORTEO'
                        ELSE 'NO DEFINIDO'
                    END AS premio,
                    (SELECT nombre FROM tbl_ubigeo WHERE cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND cod_prov = '00' AND cod_dist = '00') AS departamento,
                    (SELECT nombre FROM tbl_ubigeo WHERE cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND cod_dist = '00') AS provincia,
                    (SELECT nombre FROM tbl_ubigeo WHERE cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND cod_dist = SUBSTRING(l.ubigeo_id, 5, 2)) AS distrito
                FROM
                    tbl_registro_premios g
                INNER JOIN tbl_locales l ON l.id = g.paid_local_id
                INNER JOIN tbl_usuarios u ON u.id = g.user_id
                LEFT JOIN  tbl_consultas_dni cd on cd.dni = g.num_doc
                    $where
                    AND g.status = 1
                    $order_by_command
                    $limites
                    ";

    $result = $mysqli->query($query);

    $num_rows = $mysqli->query("SELECT
        g.id,
        g.ticket_id,
        l.nombre,
        g.created_at,
        g.monto_apostado,
        g.monto_entregado,
        g.num_doc,
        g.tipo_doc,
        u.usuario,
        g.tipo_registro AS tipo_premio
        FROM
        tbl_registro_premios g
        INNER JOIN tbl_locales l ON l.id = g.paid_local_id
        INNER JOIN tbl_usuarios u ON u.id = g.user_id
        " . $where . "
        AND g.status = 1
        ")->num_rows;

    $consulta = [];

    while ($r = $result->fetch_assoc()) $consulta[] = $r;

    return [
        "num_rows" => $num_rows,
        "consulta" => $consulta,
    ];
}

if (isset($_POST['open_modal_premios'])) {
    $data_id = $_POST['open_modal_premios']['id'];
    $data_type = $_POST['open_modal_premios']['type'];

    $photo_type = "tipo = 'foto'";
    if (isset($data_type)) {
        if ($data_type === "markt") {
            $photo_type = "(tipo = 'foto' OR tipo = 'foto_markt')";
        } else {
            $photo_type = "tipo = 'foto_" . $data_type . "'";
        }
    }

    $owl = [];
    $query = "SELECT archivo AS img from tbl_archivos WHERE tabla='tbl_registro_premios' AND item_id=" . $data_id . " AND " . $photo_type;
    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $consulta[] = $r;
    foreach ($consulta as $key => $value) {
        $retorna['items'][$key] = $value;
    }
    echo json_encode($retorna);
}

function getArchivosData($value){

    $photo_types = array(
        array(
            "type" => "markt",
            "label" => "Marketing",
        ),
        array(
            "type" => "id",
            "label" => "Documento de Identidad",
        ),
        array(
            "type" => "vouch",
            "label" => "Comprobante",
        ),
    );

    $conteoFotos = array();
    foreach ($photo_types as $photo_type) {
        $conteoFotos[] = consultar_archivos($value['id'], $photo_type["type"]);
    }

    return [
        'photo_types' => $photo_types,
        'conteoFotos' => $conteoFotos,
    ];
}

if (isset($_POST['get_tabla_reporte_premios'])) {
    $data = $_POST["get_tabla_reporte_premios"];


    /*if ($login["usuario_locales"]) {
        $locales_acces = implode(',', $login["usuario_locales"]);
        $locales_acceso = "AND l.id IN  (" . $locales_acces . ")";
    }*/

    $consulta_data = get_reporte_premios_data($data, false);
    $consulta = $consulta_data["consulta"];
    $num_rows = $consulta_data["num_rows"];

    $html = "";

    foreach ($consulta as $key => $value) {

        $numrows = get_num_files($value['id']);

        $html .= "<tr>";
        $html .= "<td>" . $value['ticket_id'] . "</td>";

        if ($value['tipo_premio'] == 0) {
            $html .= "<td> JACKPOT </td>";
        } elseif ($value['tipo_premio'] == 1) {
            $html .= "<td> BINGO </td>";
        } else if ($value['tipo_premio'] == 2) {
            $html .= "<td> PREMIO MAYOR </td>";
        } else if ($value['tipo_premio'] == 3) {
            $html .= "<td> SORTEO </td>";
        } else if ($value['tipo_premio'] == 6) {
            $html .= "<td> TORITO </td>";
        }
         else {
            $html .= "<td> NO DEFINIDO </td>";
        }

        $archivos_data = getArchivosData($value);
        $photo_types = $archivos_data['photo_types'];
        $conteoFotos = $archivos_data['conteoFotos'];

        $html .= "<td>" . $value['nombre'] . "</td>";
        $html .= "<td>" . ($value['departamento'] ?? '-') . "</td>";
        $html .= "<td>" . ($value['provincia'] ?? '-') . "</td>";
        $html .= "<td>" . ($value['distrito'] ?? "-") . "</td>";
        $html .= "<td>" . $value['created_at'] ?? "-" . "</td>";
        $html .= "<td>" . $value['monto_apostado'] . "</td>";
        $html .= "<td>" . $value['monto_entregado'] . "</td>";
        $html .= "<td>" . $value['num_doc'] . "</td>";
        $html .= "<td>" . $value['usuario'] . "</td>";
        for ($i = 0; $i < count($photo_types); $i++) {
            if ($conteoFotos[$i]['cant'] > 0) {
                $html .= "<td><button class='btn btn-rounded btn-primary btn-xs showImgs' type='button' name='button' data-id=" . $value['id'] . " data-type='" . $photo_types[$i]["type"] . "'><i class='fa fa-picture-o' aria-hidden='true'></i> (" . $conteoFotos[$i]['cant'] . ") </button></td>";
            } else {
                $html .= "<td><button class='btn btn-rounded btn-danger btn-xs showImgs' disabled='true' type='button' name='button' data-id=" . $value['id'] . "><i class='fa fa-picture-o' aria-hidden='true'></i> (0) </button></td>";
            }
        }

        $html .= "</tr>";
    }
    echo json_encode(['tabla' => $html, 'num_rows' => $num_rows]);
}

if (isset($_POST['get_tabla_reporte_premios_export_xls'])) {
    global $mysqli;

    $data = $_POST['get_tabla_reporte_premios_export_xls'];
    $result = get_reporte_premios_data($data, true);
    $result_data = $result["consulta"];

    if (!$result_data) {
        echo json_encode([
            "error" => "Export error"
        ]);
        exit;
    }
    
    foreach ($result_data as $key => $value) {
        $archivos_data = getArchivosData($value);
        $photo_types = $archivos_data['photo_types'];
        $conteoFotos = $archivos_data['conteoFotos'];
        for ($i = 0; $i < count($photo_types); $i++) {
            if ($conteoFotos[$i]['cant'] > 0) {
                $result_data[$key]['photo_'.$photo_types[$i]['type']] = 'Sí';
            } else {
                $result_data[$key]['photo_'.$photo_types[$i]['type']] = 'No';
            }
        }
    }

    $headers = [
        "id" => "Id",
        "ticket_id" => "Ticket Id",
        "nombre" => "Nombre del local",
        "created_at" => "Fecha",
        "monto_apostado" => "Monto Apostado",
        "monto_entregado" => "Monto entregado",
        "num_doc" => "Número de Documento",
        "cliente_ganador" => "CLIENTE GANADOR",
        "tipo_doc" => "Tipo Doc Id",
        "nombre_tipo_doc" => "Tipo de Documento",
        "usuario" => "Usuario",
        "tipo_premio" => "Tipo de Premio",
        "premio" => "Nombre de Premio",
        "departamento" => "Departemento",
        "provincia" => "Provincia",
        "distrito" => "Distrito",
        "marketing" => "Marketing",
        "docs" => "DOC",
        "comprobantes" => "Comprobante",
    ];
    array_unshift($result_data, $headers);

    require_once '../phpexcel/classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
    $date = new DateTime();
    $file_title = "reporte_premios_" .$date->getTimestamp();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $excel_path = '/var/www/html/export/files_exported/reporte_premios/' . $file_title . '.xls';
    $excel_path_download = '/export/files_exported/reporte_premios/' . $file_title . '.xls';
    $url = $file_title . '.xls';
    try {
        $objWriter->save($excel_path);
    } catch (PHPExcel_Writer_Exception $e) {
        echo json_encode(["error" => $e]);
        exit;
    }

    $insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
    $insert_cmd .= " VALUES ('" . $url . "','excel','xls','" . filesize($excel_path) . "','" . date("Y-m-d h:i:s") . "','" . $login["id"] . "')";
    $mysqli->query($insert_cmd);

    echo json_encode(array(
        "path" => $excel_path_download,
        "url" => $file_title . '.xls',
        "tipo" => "excel",
        "ext" => "xls",
        "size" => filesize($excel_path),
        "fecha_registro" => date("d-m-Y h:i:s"),
        "sql" => $insert_cmd
    ));
    exit;
}

if (isset($_POST['get_tabla_reporte_premios_caja'])) {
    $data = $_POST["get_tabla_reporte_premios_caja"];
    global $mysqli;

    $caja_id = $data["caja_id"];

    $query = "
        SELECT
            lc.local_id,
            c.fecha_operacion
        FROM
            tbl_caja AS c
        LEFT JOIN
            tbl_local_cajas AS lc
            ON c.local_caja_id = lc.id
        WHERE
            c.estado= 0
            AND c.id = $caja_id
        LIMIT 1
    ";

    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $local_rows[] = $r;
    if(!empty($local_rows)){
        $local_id = $local_rows[0]["local_id"];
        $fecha_operacion = $local_rows[0]["fecha_operacion"];

        $query = "
        SELECT
            g.id,
            g.ticket_id,
            CASE
                WHEN g.tipo_registro = 0 THEN 'JACKPOT'
                WHEN g.tipo_registro = 1 THEN 'BINGO'
                WHEN g.tipo_registro = 2 THEN 'PREMIO MAYOR'
                WHEN g.tipo_registro = 3 THEN 'SORTEO'
                ELSE 'NO DEFINIDO'
            END AS tipo_premio,
            l.nombre,
            g.created_at,
            g.monto_apostado,
            g.monto_entregado,
            CASE
                WHEN g.tipo_doc = 0 THEN 'DNI'
                WHEN g.tipo_doc = 1 THEN 'CARNÉ EXTRANJERÍA'
                WHEN g.tipo_doc = 2 THEN 'PASAPORTE'                
                ELSE 'NO DEFINIDO'
            END AS tipo_doc,            
            g.num_doc,
            u.usuario
        FROM
            tbl_registro_premios g
        INNER JOIN 
            tbl_locales l ON l.id = g.paid_local_id
        INNER JOIN 
            tbl_usuarios u ON u.id = g.user_id
        WHERE
            g.paid_local_id = $local_id
            AND g.caja_id = 0
            AND g.status = 1
            AND date(g.created_at) >= '".$fecha_operacion."'
        ";

        $result = $mysqli->query($query);

        $rows = null;
        while ($r = $result->fetch_assoc()) $rows[] = $r;
        echo json_encode(['rows' => $rows]);
    }
    else{
        echo json_encode(['rows' => null]);
    }
}

if (isset($_POST['get_reporte_premios_images'])) {
    $data = $_POST["get_reporte_premios_images"];
    global $mysqli;
    $caja_id = $data["caja_id"];

    $query = "
        SELECT
           rp.id,
           rp.ticket_id,
           CASE
                WHEN rp.tipo_registro = 0 THEN 'JACKPOT'
                WHEN rp.tipo_registro = 1 THEN 'BINGO'
                WHEN rp.tipo_registro = 2 THEN 'PREMIO MAYOR'
                WHEN rp.tipo_registro = 3 THEN 'SORTEO'
                ELSE 'NO DEFINIDO'
           END AS tipo_premio,
           rp.created_at,
           rp.created_at,
           rp.monto_entregado,
           a.archivo,
           a.tipo
        FROM
            tbl_archivos a
        INNER JOIN
            tbl_registro_premios rp ON a.item_id = rp.id
        INNER JOIN
            tbl_caja c  ON c.id = rp.caja_id
        WHERE
              a.tipo LIKE 'foto_%'
          AND a.tabla ='tbl_registro_premios'
          AND c.id = $caja_id
        ";

    $result = $mysqli->query($query);
    $rows = null;
    while ($r = $result->fetch_assoc()) $rows[] = $r;

    $rows_grouped = [];
    if (is_array($rows)){
        foreach ($rows as $row) {
            $rows_grouped[$row["id"]]["id"] = $row["id"];
            $rows_grouped[$row["id"]]["ticket_id"] = $row["ticket_id"];
            $rows_grouped[$row["id"]]["tipo_premio"] = $row["tipo_premio"];
            $rows_grouped[$row["id"]]["created_at"] = $row["created_at"];
            $rows_grouped[$row["id"]]["monto_entregado"] = $row["monto_entregado"];
            $rows_grouped[$row["id"]][$row["tipo"]][] = $row["archivo"];
        }
    }

    echo json_encode(['rows' => $rows_grouped]);

    
}

?>
