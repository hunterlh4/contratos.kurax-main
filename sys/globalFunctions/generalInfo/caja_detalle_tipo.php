<?php

/* Obtener el nombre a partir del id 
INPUT: -
OUTPUT: Array con todos los caja detalle tipos
*/
function getAllCajaDetalleTipos(){
    global $mysqli;
    $query = "SELECT * FROM tbl_caja_detalle_tipos";

    $result = $mysqli->query($query);
    $tipos = [];
    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    }
    
    while ($r = $result->fetch_assoc()) {
        $tipos[$r['id']] = $r;
    }

    $result = $tipos;

    return $result;   
}

?>