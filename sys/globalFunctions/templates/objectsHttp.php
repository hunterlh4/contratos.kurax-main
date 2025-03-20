<?php
/*
Genera un result en formato json 
INPUT: array de parametros
OUTPUT: array en formato json
*/
function generateResultHttp($parameters){
    $result = [];
    foreach (array_keys($parameters) as $key) {
        $result[ $key ] = $parameters[$key];
    }
    return json_encode($result);
}
?>