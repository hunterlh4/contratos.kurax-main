<?php
$config = array();
$config_command = "SELECT config_id, config_data FROM tbl_configuracion WHERE estado = '1'";
$config_query = $mysqli->query($config_command);
if ($mysqli->error) {
    print_r($mysqli->error);
    echo "\n";
    echo $config_command;
    exit();
}
while ($c = $config_query->fetch_assoc()) {
    $config[$c["config_id"]] = $c["config_data"];
}
if (array_key_exists("files_version", $config)) {
    $css_cache = $js_cache = $config["files_version"];
}
if (array_key_exists("jackpot_pago_texto", $config)) {
    $jackpot_pago_texto = $config["jackpot_pago_texto"];
}
if (array_key_exists("jackpot_pago_cliente_marketing", $config)) {
    $jackpot_pago_cliente_marketing = $config["jackpot_pago_cliente_marketing"];
}
if (array_key_exists("jackpot_pago_cliente_db", $config)) {
    $jackpot_pago_cliente_db = $config["jackpot_pago_cliente_db"];
}
if (array_key_exists("registro_premios_sorteo", $config)) {
    $registro_premios_sorteo = $config["registro_premios_sorteo"];
}
?>
