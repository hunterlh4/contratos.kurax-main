<?php



define('PATH_MODEL', '/var/www/html/sys/models/');
define('PATH_CONTROLLER', '/var/www/html/sys/controllers/');

include PATH_MODEL . 'ContratoModel.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';


include PATH_CONTROLLER . 'contratos/DataContratoController.php';
include PATH_CONTROLLER . 'contratos/ContratoArrendamientoController.php';
include PATH_CONTROLLER . 'contratos/PropietarioController.php';
include PATH_CONTROLLER . 'contratos/ArrendatarioController.php';

include PATH_CONTROLLER . 'contratos/BeneficiarioController.php';
include PATH_CONTROLLER . 'contratos/TipoArchivoController.php';
include PATH_CONTROLLER . 'contratos/ResponsableIRController.php';
include PATH_CONTROLLER . 'contratos/DataImportController.php';

include PATH_CONTROLLER . 'provisiones/ProvisionesController.php';
include PATH_CONTROLLER . 'contratos/ContratoLocacionServicioController.php';
include PATH_CONTROLLER . 'contratos/ContratoMandatoController.php';
include PATH_CONTROLLER . 'contratos/ContratoMutuoDineroController.php';
