<?php 



define('PATH_MODEL','/var/www/html/sys/models/');
define('PATH_CONTROLLER','/var/www/html/sys/controllers/');

include PATH_MODEL.'ContratoMantenimientoModel.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

include PATH_CONTROLLER.'contratos/DataContratoController.php';
include PATH_CONTROLLER.'contratos/MantenimientoCorreoController.php';
include PATH_CONTROLLER.'contratos/MantenimientoCargoController.php';
include PATH_CONTROLLER.'contratos/MantenimientoCorreoMetodoController.php';
include PATH_CONTROLLER.'contratos/MantenimientoCargoMetodoController.php';
include PATH_CONTROLLER.'contratos/MantenimientoCorrelativoController.php';
include PATH_CONTROLLER.'contratos/MantenimientoServicioPublicoController.php';
include PATH_CONTROLLER.'contratos/NotificacionContratoController.php';



