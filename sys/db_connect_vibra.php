<?php
require_once '/var/www/html/env.php';

$con_vibra_db_name = env('VIBRA_DATABASE');
$con_vibra_host = env('VIBRA_HOST');
$con_vibra_user = env('VIBRA_USER');
$con_vibra_pass = env('VIBRA_SECRET');
$con_vibra_port = env('VIBRA_PORT');

// Conexión a la base de datos PostgreSQL
$pgsql_vibra = pg_connect("host=$con_vibra_host port=$con_vibra_port dbname=$con_vibra_db_name user=$con_vibra_user password=$con_vibra_pass");

if (!$pgsql_vibra) {
    echo "Error al conectar a PostgreSQL.\n";
    exit;
} 
pg_set_client_encoding($pgsql_vibra, "utf8");

$date = date("Y-m-d H:i:s");

?>
