<?php
if (isset($_POST["get_caja_reporte_eliminados"])) {
    $data = $_POST["get_caja_reporte_eliminados"];
    $query = "
        SELECT
            ce.fecha_eliminacion,
            IF (
                CONCAT(pa.nombre, ' ', pa.apellido_paterno) IS NOT NULL,
                CONCAT(pa.nombre, ' ', pa.apellido_paterno),
                u.usuario
            ) as usuario_eliminacion,
            ce.turno_id as turno,
            fecha_operacion
        FROM
            tbl_caja_eliminados ce
            LEFT JOIN tbl_usuarios u ON u.id = ce.usuario_eliminacion
            LEFT JOIN tbl_personal_apt pa ON u.personal_id = pa.id
            LEFT JOIN tbl_local_cajas lc ON lc.id = ce.local_caja_id
        WHERE
            lc.local_id = '$data'
        ORDER BY ce.fecha_eliminacion DESC
        LIMIT
            10;
    ";

    $result = $mysqli->query($query);
    $report = array();
    while ($row = $result->fetch_assoc()) {
        /*$login = json_decode($row['login'], true);
        $result_data = json_decode($row['data'], true);

        $row_data = array();
        $row_data['fecha_eliminacion'] = $row['fecha_registro'];
        $row_data['usuario_eliminacion'] = "$login[nombre] $login[apellido_paterno]";
        $row_data['turno'] = $result_data['turno'];
        $row_data['fecha_operacion'] = $result_data['fecha_operacion'];*/

        $row_data = array();
        $row_data['fecha_eliminacion'] = $row['fecha_eliminacion'] ?? "No registrado";
        $row_data['usuario_eliminacion'] = $row['usuario_eliminacion'] ?? "No registrado";
        $row_data['turno'] = $row['turno'] ?? "No registrado";
        $row_data['fecha_operacion'] = $row['fecha_operacion'] ?? "No registrado";

        $report[] = $row_data;
    }

    echo json_encode($report, true);
}
