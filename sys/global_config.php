<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set("America/Lima");
//header('Content-Type: text/html; charset=utf-8');
header('Content-Type: charset=utf-8');
$sistema_id = 1;
$site_title = "Gestion - Kurax";
$sec_id="home";
$sub_sec_id=false;
$item_id=false;
$login=false;

$ip = null;
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$datetime=date("Y-m-d H:i:s");
if(isset($_GET["sec_id"])){
	$sec_id=$_GET["sec_id"];
}
if(isset($_GET["sub_sec_id"])){
	$sub_sec_id=$_GET["sub_sec_id"];
}
if(isset($_GET["item_id"])){
	$item_id=$_GET["item_id"];
}

$admin = false;
//print_r($_SERVER);
// $css_cache = $js_cache = "20171229";
// $css_cache = $js_cache = "20171229";
if(strstr($_SERVER["SCRIPT_FILENAME"], "at_liquidaciones_v2")){
	//$admin=true;
	// $css_cache = $js_cache = time();
}
$css_cache = $js_cache = time();
// $css_cache = $js_cache = date("Y-m-d");

?>
