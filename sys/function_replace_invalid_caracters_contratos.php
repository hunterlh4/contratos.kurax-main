<?php

function replace_invalid_caracters($cadena) {
	$cadena = trim($cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("\\", "", $cadena);
	$cadena = str_replace("^", "", $cadena);
	$cadena = str_replace("`", "", $cadena);
	$cadena = str_replace("|", "", $cadena);
	$cadena = str_replace("~", "", $cadena);
	$cadena = str_replace("¢", "", $cadena);
	$cadena = str_replace("£", "", $cadena);
	$cadena = str_replace("¤", "", $cadena);
	$cadena = str_replace("¥", "", $cadena);
	$cadena = str_replace("¦", "", $cadena);
	$cadena = str_replace("§", "", $cadena);
	$cadena = str_replace("¨", "", $cadena);
	$cadena = str_replace("ª", "", $cadena);
	$cadena = str_replace("«", "", $cadena);
	$cadena = str_replace("¬", "", $cadena);
	$cadena = str_replace("®", "", $cadena);
	$cadena = str_replace("°", "", $cadena);
	$cadena = str_replace("±", "", $cadena);
	$cadena = str_replace("²", "", $cadena);
	$cadena = str_replace("³", "", $cadena);
	$cadena = str_replace("´", "", $cadena);
	$cadena = str_replace("µ", "", $cadena);
	$cadena = str_replace("¶", "", $cadena);
	$cadena = str_replace("'", "", $cadena);	
	return $cadena;

}

function replace_invalid_caracters_vista($cadena) {
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace('"', '', $cadena);
	$cadena = preg_replace("/[\r\n|\n|\r]+/", " ", $cadena);	
	return $cadena;
	
}

?>