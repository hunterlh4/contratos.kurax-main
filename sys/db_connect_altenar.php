<?php
require_once '/var/www/html/env.php';

$con_db_name=env('DB_DATABASE_ALTENAR');
$con_host=env('DB_HOST_ALTENAR');
$con_user=env('DB_USERNAME_ALTENAR');
$con_pass=env('DB_PASSWORD_ALTENAR');
$mysqli = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);

if (mysqli_connect_errno()) {
    printf("Conexion fallida: %s\n", mysqli_connect_error());
    exit();
}
$mysqli->query("SET CHARACTER SET utf8");
$date = date("Y-m-d H:i:s");
?>
