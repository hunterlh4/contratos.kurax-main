<?php
define('PATH_MODELS', '/var/www/html/sys/models/');

include '/var/www/html/env.php';
include PATH_MODELS . 'Connect.php';

include PATH_MODELS . 'contratos/DataContrato.php';
include PATH_MODELS . 'contratos/ContratoArrendamiento.php';
include PATH_MODELS . 'contratos/Propietario.php';
include PATH_MODELS . 'contratos/Arrendatario.php';
include PATH_MODELS . 'contratos/Beneficiario.php';
include PATH_MODELS . 'contratos/TipoArchivo.php';
include PATH_MODELS . 'contratos/Correlativo.php';
include PATH_MODELS . 'contratos/Inmueble.php';
include PATH_MODELS . 'contratos/CondicionEconomica.php';
include PATH_MODELS . 'contratos/Incremento.php';
include PATH_MODELS . 'contratos/Inflacion.php';
include PATH_MODELS . 'contratos/CuotaExtraordinaria.php';
include PATH_MODELS . 'contratos/Adelanto.php';
include PATH_MODELS . 'contratos/Archivo.php';
include PATH_MODELS . 'contratos/ResponsableIR.php';
include PATH_MODELS . 'provision/Provisiones.php';
include PATH_MODELS . 'contratos/ContratoArrendamientoDetalle.php';
include PATH_MODELS . 'contratos/InmuebleSuministro.php';

include PATH_MODELS . 'contratos/DataImport.php';
include PATH_MODELS . 'contratos/ContratoArrendamientoV2.php';
include PATH_MODELS . 'contratos/ContratoLocacionServicio.php';
include PATH_MODELS . 'contratos/ContratoMandato.php';
include PATH_MODELS . 'contratos/ContratoMutuoDinero.php';
