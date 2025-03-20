<?php

/*
Obtiene parametro de la tabla tbl_parametros_generales
INPUT: codigo de parametro
OUTPUT: valor del parametro
*/
function getParameterGeneral($codigo_parameter){

    global $mysqli;
    $valor = false;

    $query = "select codigo, valor from tbl_parametros_generales
	            where codigo like '$codigo_parameter'";

	$list_query = $mysqli->query($query);

	if ($mysqli->error) {
		echo $mysqli->error;
	} else {
		$parametros = array();
		while ($li = $list_query->fetch_assoc()) {
			$parametros[] = $li;
		}
		foreach ($parametros as $parametro) {
			switch ($parametro['codigo']) {
				case $codigo_parameter:
					$valor = strval($parametro['valor']);
					break;
			}
		}

        return $valor;

	}
    
    return false;

}
?>