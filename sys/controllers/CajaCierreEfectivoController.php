<?php 



define('PATH_MODEL','/var/www/html/sys/models/');
define('PATH_CONTROLLER','/var/www/html/sys/controllers/');

include PATH_MODEL.'CajaCierreEfectivoModel.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

include PATH_CONTROLLER.'caja/CierreEfectivoController.php';
include PATH_CONTROLLER.'caja/CierreEfectivoDenominacionController.php';




