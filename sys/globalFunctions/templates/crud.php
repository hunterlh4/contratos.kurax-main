<?php

/*
Inserta data en una tabla
INPUT: (string)nombre de la tabla, array de los parametros
OUTPUT: true  o array con errores
*/
function insertTable($table, $values){
    $result = false;
    global $mysqli;

    $query = "INSERT INTO $table (";
    $i = 1;
    foreach ($values as $key => $value) {
        $query .= "`$key`";
        if (count($values) > $i) {
            $query .= ",";
        }
        $i++;
    }

    $query .= ") VALUES (";
    $i = 1;
    foreach ($values as $key => $value) {
        $query .= "'$value'";
        if (count($values) > $i) {
            $query .= ",";
        }
        $i++;
    }

    $query .= ")";

    $mysqli->query($query);
    $mysqli_error = $mysqli->error;

    if($mysqli_error){
        $result["error"]="mysql";
        $result["mysqli_error"]=$mysqli_error;
        $result["query"]=$query;
    } else {
        $result = true;
    }

    return $result;
}

/*
Inserta data en una tabla a otra conexion
INPUT: conexion mysqli, (string)nombre de la tabla, array de los parametros
OUTPUT: true  o array con errores
*/
function insertDevTable($mysqli, $table, $values){
    $result = false;

    $query = "INSERT INTO $table (";
    $i = 1;
    foreach ($values as $key => $value) {
        $query .= "`$key`";
        if (count($values) > $i) {
            $query .= ",";
        }
        $i++;
    }

    $query .= ") VALUES (";
    $i = 1;
    foreach ($values as $key => $value) {
        $value = $value == ''? 'NULL': "'$value'";
        $query .= "$value";
        if (count($values) > $i) {
            $query .= ",";
        }
        $i++;
    }

    $query .= ")";

    $mysqli->query($query);
    $mysqli_error = $mysqli->error;

    if($mysqli_error){
        $result["error"]="mysql";
        $result["mysqli_error"]=$mysqli_error;
        $result["query"]=$query;
    } else {
        $result = true;
    }

    return $result;
}
?>