<?php

/*Obtener el id del menú a partir del sec_id 
INPUT: sec_id (varchar)
OUTPUT: id menu (int)
*/
function getMenuBySecId($sec_id){
    global $mysqli;
    $query = "
	SELECT
    t1.id
    FROM tbl_menu_sistemas t1
    WHERE t1.sec_id =  '".$sec_id."'
    LIMIT 1";

    $result = $mysqli->query($query)->fetch_assoc()['id'];
    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    }

    return $result;   
}

?>