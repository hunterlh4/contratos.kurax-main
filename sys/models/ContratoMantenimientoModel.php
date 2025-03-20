<?php 
define('PATH_MODELS','/var/www/html/sys/models/');

include '/var/www/html/env.php';
include PATH_MODELS.'Connect.php';

include PATH_MODELS.'contratos/DataContrato.php';
include PATH_MODELS.'contratos/ContratoArrendamiento.php';
include PATH_MODELS.'contratos/MantenimientoCorreo.php';
include PATH_MODELS.'contratos/MantenimientoCargo.php';
include PATH_MODELS.'contratos/MantenimientoCorreoMetodo.php';
include PATH_MODELS.'contratos/MantenimientoCargoMetodo.php';
include PATH_MODELS.'contratos/Correlativo.php';
include PATH_MODELS.'contratos/Contrato.php';
include PATH_MODELS.'contratos/ServicioPublico.php';
include PATH_MODELS.'contratos/NotificacionContrato.php';
include PATH_MODELS.'contratos/ContratoLocacionServicio.php';
include PATH_MODELS.'contratos/ContratoMutuoDinero.php';

