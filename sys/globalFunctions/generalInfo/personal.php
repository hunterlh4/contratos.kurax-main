<?php
/**
 * Valida si el DNI de un personal ya existe o no en la bd
 * Input: $dni
 * Output: texto si existe o no, y si existe te devuelve datos del personal en un array.
 */
function validarDNIPersonal($dni){
	global $mysqli;
	$exist_dni = $mysqli->query("SELECT 
									dni, estado, TRIM(concat(ifnull(nombre,''), ' ', ifnull(apellido_paterno, ''), ' ', ifnull(apellido_materno,''))) as nombre_completo
								FROM tbl_personal_apt WHERE dni ='" . $dni . "' AND estado in (0,1) LIMIT 1")->fetch_assoc();
	if ($exist_dni) {
		$flag_dni = 'existe';
	} else {
		$flag_dni = 'no_existe';
	}

	$result = [
		'flag_dni' => $flag_dni,
		'personal' => $exist_dni
	];

	return $result;
}