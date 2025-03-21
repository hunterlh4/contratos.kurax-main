<?php
include("db_connect.php");
include("sys_login.php");
$return = array();
include("get_caja_local_cajas.php");
include("get_caja_monto_inicial.php");
include("get_caja_reporte.php");
include("get_caja_validados.php");
include("get_caja_jackpot.php");
include("get_caja_compare.php");
include("get_caja_auditoria.php");
include("get_caja_faltantes.php");
include("get_caja_turnos.php");
include("get_caja_depositos.php");
include("get_caja_reporte_eliminados.php");
?>