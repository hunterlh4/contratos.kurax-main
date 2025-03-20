<?php
include("db_connect.php");

function fnc_terminal_auto_servicio_get_caja_by_caja_id(int $caja_id): array
{
    global $mysqli;
    $query_result = [
        'error' => false
    ];
    $query_select = "SELECT 
       sqc.id, 
       sqc.turno_id as turno,
       sqc.fecha_operacion,
       ssql.cc_id,
       ssql.id as local_id,
       UPPER(CONCAT(pl.nombre, ' ', pl.apellido_paterno)) as cajero,
       UPPER(ssql.nombre) as local_nombre,
       u.usuario,
       sqc.fecha_operacion
FROM tbl_caja sqc
         JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
		 JOIN tbl_locales ssql ON ssql.id = sqlc.local_id 
         JOIN tbl_usuarios u ON u.id = sqc.usuario_id 
         JOIN tbl_personal_apt pl ON pl.id = u.personal_id 
WHERE sqc.id = " . $caja_id . "
ORDER BY sqc.id DESC;";
    $result = $mysqli->query($query_select);
    if ($mysqli->error) {
        $query_result['msj'] = $mysqli->error;
        $query_result['error'] = true;
        $query_result['http_code'] = 400;
        $query_result['query'] = $query_select;
    } else {
        $query_result['data'] = [];
        while ($row = $result->fetch_assoc()) {
            $query_result['data'][] = $row;
        }
    }
    return $query_result;
}
