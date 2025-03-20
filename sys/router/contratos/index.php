<?php
//Router
include('./../src/Router.php');
//import controllers
include '/var/www/html/sys/controllers/ContratoController.php';

$path = '/sys/router/contratos/';

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router = new Router();
$router->setBasePath($path);

$ruta = '';
$request = file_get_contents('php://input');
if (!empty($_POST['action']) && isset($_POST['action'])) {
  $ruta = $_POST['action'];
} else {
  $request = json_decode($request, true);
  $ruta = $request['action'];
}

//routes
$router->addRoute('/obtener_empresas', 'DataContratoController@ObtenerEmpresas', 'POST');
$router->addRoute('/obtener_personal_responsable', 'DataContratoController@obtener_personal_responsable', 'POST');
$router->addRoute('/obtener_propietario', 'DataContratoController@obtener_propietario', 'POST');
$router->addRoute('/obtener_ocupante', 'DataContratoController@obtener_ocupante', 'POST');
$router->addRoute('/obtener_tipo_persona', 'DataContratoController@obtener_tipo_persona', 'POST');
$router->addRoute('/obtener_tipo_doc_identidad', 'DataContratoController@obtener_tipo_doc_identidad', 'POST');
$router->addRoute('/obtener_departartamentos', 'DataContratoController@obtener_departartamentos', 'POST');
$router->addRoute('/obtener_provincias_segun_departamento', 'DataContratoController@obtener_provincias_segun_departamento', 'POST');
$router->addRoute('/obtener_distritos_segun_provincia', 'DataContratoController@obtener_distritos_segun_provincia', 'POST');
$router->addRoute('/obtener_tipo_compromiso_pago', 'DataContratoController@obtener_tipo_compromiso_pago', 'POST');
$router->addRoute('/obtener_tipo_compromiso_pago_arbitrio', 'DataContratoController@obtener_tipo_compromiso_pago_arbitrio', 'POST');
$router->addRoute('/obtener_moneda_de_contrato', 'DataContratoController@obtener_moneda_de_contrato', 'POST');
$router->addRoute('/obtener_tipo_pago_renta', 'DataContratoController@obtener_tipo_pago_renta', 'POST');
$router->addRoute('/obtener_tipo_afectacion_igv', 'DataContratoController@obtener_tipo_afectacion_igv', 'POST');
$router->addRoute('/obtener_tipo_venta', 'DataContratoController@obtener_tipo_venta', 'POST');
$router->addRoute('/obtener_tipo_adelantos', 'DataContratoController@obtener_tipo_adelantos', 'POST');
$router->addRoute('/obtener_tipo_impuesto_a_la_renta', 'DataContratoController@obtener_tipo_impuesto_a_la_renta', 'POST');
$router->addRoute('/obtener_tipo_periodo_de_gracia', 'DataContratoController@obtener_tipo_periodo_de_gracia', 'POST');
$router->addRoute('/obtener_tipo_periodo', 'DataContratoController@obtener_tipo_periodo', 'POST');
$router->addRoute('/obtener_tipo_incrementos', 'DataContratoController@obtener_tipo_incrementos', 'POST');
$router->addRoute('/obtener_tipo_pago_incrementos', 'DataContratoController@obtener_tipo_pago_incrementos', 'POST');
$router->addRoute('/obtener_tipo_continuidad_pago', 'DataContratoController@obtener_tipo_continuidad_pago', 'POST');
$router->addRoute('/obtener_tipo_anios_incrementos', 'DataContratoController@obtener_tipo_anios_incrementos', 'POST');
$router->addRoute('/obtener_tipo_cuota_extraordinaria', 'DataContratoController@obtener_tipo_cuota_extraordinaria', 'POST');
$router->addRoute('/obtener_tipo_inflacion', 'DataContratoController@obtener_tipo_inflacion', 'POST');
$router->addRoute('/obtener_tipo_periodicidad', 'DataContratoController@obtener_tipo_periodicidad', 'POST');
$router->addRoute('/obtener_tipo_anio_mes', 'DataContratoController@obtener_tipo_anio_mes', 'POST');
$router->addRoute('/obtener_tipo_aplicacion', 'DataContratoController@obtener_tipo_aplicacion', 'POST');
$router->addRoute('/obtener_meses', 'DataContratoController@obtener_meses', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_bancos', 'DataContratoController@obtener_bancos', 'POST');
$router->addRoute('/obtener_tipo_monto_a_depositar', 'DataContratoController@obtener_tipo_monto_a_depositar', 'POST');
$router->addRoute('/obtener_tipo_anexos', 'DataContratoController@obtener_tipo_anexos', 'POST');
$router->addRoute('/obtener_meses_adelantos', 'DataContratoController@obtener_meses_adelantos', 'POST');
$router->addRoute('/obtener_abogados', 'DataContratoController@obtener_abogados', 'POST');
$router->addRoute('/obtener_aprobador', 'DataContratoController@ObtenerAprobador', 'POST');
$router->addRoute('/obtener_cargo_aprobador', 'DataContratoController@ObtenerCargoAprobador', 'POST');

$router->addRoute('/calcular_monto_segun_impuesto', 'ContratoArrendamientoController@calcular_monto_segun_impuesto', 'POST');





// PROPIETARIO - ARRENDADOR
$router->addRoute('/registrar_propietario', 'PropietarioController@registrar_personal', 'POST');
$router->addRoute('/validar_modificacion_propietario', 'PropietarioController@validar_personal_modificacion', 'POST');
$router->addRoute('/modificar_propietario', 'PropietarioController@modificar_personal', 'POST');
// $router->addRoute('/modificar_propietario_prueba', 'PropietarioController@modificar_personal_prueba', 'POST');

// ARRENDATARIO - INQUILINO
$router->addRoute('/registrar_arrendatario', 'ArrendatarioController@registrar_arrendatario', 'POST');
$router->addRoute('/validar_modificacion_arrendatario', 'ArrendatarioController@validar_arrendatario_modificacion', 'POST');
$router->addRoute('/modificar_arrendatario', 'ArrendatarioController@modificar_arrendatario', 'POST');





$router->addRoute('/beneficiario/registrar', 'BeneficiarioController@registrar', 'POST');
$router->addRoute('/beneficiario/modificar', 'BeneficiarioController@modificar', 'POST');
$router->addRoute('/tipo_archivo/registrar', 'TipoArchivoController@registrar', 'POST');
$router->addRoute('/responsable-ir/registrar', 'ResponsableIRController@registrar', 'POST');
$router->addRoute('/responsable-ir/modificar', 'ResponsableIRController@modificar', 'POST');

// CONTRATOS
// ROUTER CONTRATOS ARRENDAMIENTO
$router->addRoute('/contrato_arrendamiento/registrar', 'ContratoArrendamientoController@registrar', 'POST');


$router->addRoute('/contrato_arrendamiento/actualizar_codigo_contrato', 'ContratoArrendamientoController@actualizar_codigo_contrato', 'POST');

///VISTA DETALLE DE SOLICITUD
$router->addRoute('/contrato_arrendamiento/obtener_propietarios', 'ContratoArrendamientoController@obtener_propietarios', 'POST');
$router->addRoute('/contrato_arrendamiento/agregar_contrato_arrendamiento', 'ContratoArrendamientoController@agregar_contrato_arrendamiento', 'POST');
$router->addRoute('/contrato_arrendamiento/reenviar_email_solicitud_contrato_locales_detallado', 'ContratoArrendamientoController@reenviar_email_solicitud_contrato_locales_detallado', 'POST');
$router->addRoute('/contrato_arrendamiento/reenviar_email_solicitud_arrendamiento', 'ContratoArrendamientoController@reenviar_email_solicitud_arrendamiento', 'POST');
$router->addRoute('/contrato_arrendamiento/enviar_email_solicitud_arrendamiento', 'ContratoArrendamientoController@enviar_email_solicitud_arrendamiento', 'POST');
$router->addRoute('/contrato_arrendamiento_firma/enviar_email_firma_contrato_arrendamiento', 'ContratoArrendamientoController@enviar_email_firma_contrato_arrendamiento', 'POST');





//DATA IMPORTACION DE DATA
$router->addRoute('/contrato_proveedor/carga_historica', 'DataImportController@contrato_proveedores', 'POST');
$router->addRoute('/contrato_arrendamiento/actualizacion_data', 'DataImportController@update_contrato_arrendamiento', 'POST');

///UPDATE ADENDAS
$router->addRoute('/adenda_arrendamiento/actualizacion_data', 'DataImportController@update_adenda_contrato_arrendamiento', 'POST');



//NUEVA EMPRESA
$router->addRoute('/v2/obtener_contratos_arrendamientos', 'DataImportController@obtener_contratos_arrendamiento_v2', 'POST');
$router->addRoute('/v2/registrar_contratos_arrendamientos', 'DataImportController@registrar_contratos_arrendamiento_v2', 'POST');

$router->addRoute('/v2/registrar_local_caja', 'DataImportController@registrar_local_caja', 'POST');

//migracion de locales
$router->addRoute('/v2/migracion_de_tiendas', 'DataImportController@migracion_de_tiendas', 'POST');
//migracion de personal sap
$router->addRoute('/v2/importacion_codigo_sap', 'DataImportController@importacion_codigo_sap', 'POST');
//Importacion de areas en contratos de proveedores
$router->addRoute('/v2/proveedores/actualizar_areas', 'DataImportController@actualizar_area_contrato_proveedores', 'POST');
//Migracion de locales igh a freegames
$router->addRoute('/migracion/locales/igh_freegames', 'DataImportController@migracion_tienda_igh_freegames', 'POST');

//Agregar contrato de locacion de servicios, mandatos, y mutuo dinero
$router->addRoute('/contrato_locacionservicio/registrar', 'ContratoLocacionServicioController@registrar', 'POST');
$router->addRoute('/contrato_locacionservicio/enviar_email_confirmacion_aprobar_contrato_locacion', 'ContratoLocacionServicioController@enviar_email_confirmacion_aprobar_contrato_locacion', 'POST');
$router->addRoute('/contrato_locacionservicio/enviar_email_confirmacion_firmar_contrato_locacion', 'ContratoLocacionServicioController@enviar_email_confirmacion_firmar_contrato_locacion', 'POST');

$router->addRoute('/contrato_mandato/registrar', 'ContratoMandatoController@registrar', 'POST');
$router->addRoute('/contrato_mandato/enviar_email_confirmacion_aprobar_contrato_mandato', 'ContratoMandatoController@enviar_email_confirmacion_aprobar_contrato_mandato', 'POST');
$router->addRoute('/contrato_mandato/enviar_email_confirmacion_firmar_contrato_mandato', 'ContratoMandatoController@enviar_email_confirmacion_firmar_contrato_mandato', 'POST');


$router->addRoute('/contrato_mutuodinero/registrar', 'ContratoMutuoDineroController@registrar', 'POST');
$router->addRoute('/contrato_mutuodinero/enviar_email_confirmacion_aprobar_contrato_mutuodinero', 'ContratoMutuoDineroController@enviar_email_confirmacion_aprobar_contrato_mutuodinero', 'POST');
$router->addRoute('/contrato_mutuodinero/enviar_email_confirmacion_firmar_contrato_mutuodinero', 'ContratoMutuoDineroController@enviar_email_confirmacion_firmar_contrato_mutuodinero', 'POST');



// Ejecutar el enrutamiento
$response = $router->dispatch($path . $ruta, $_SERVER['REQUEST_METHOD']);
// Imprimir la respuesta
echo $response;

exit();


/*
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');
$router->addRoute('/obtener_forma_pago', 'DataContratoController@obtener_forma_pago', 'POST');




/// INICIO CONTRATO DE ARRENDAMIENTO  -  EDITAR

// DATOS GENERALES (OBSERVACIONES  )
Route::add('/arrendamiento/obtener_datos_generales', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController($request);
  return $response->obtener_datos_generales($request);
},'POST');

// DATOS PROPIETARIO
Route::add('/arrendamiento/obtener_datos_propietario', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController($request);
  return $response->obtener_datos_propietario($request);
},'POST');


// DATOS INMUEBLE
Route::add('/arrendamiento/obtener_datos_inmueble', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController($request);
  return $response->obtener_datos_inmueble($request);
},'POST');

// DATOS CONDICIONES ECONOMICAS  (IMPUESTO A LA RENTA , VIGENCIA ,FECHA SUSCRIPCION CONTRATO)
Route::add('/arrendamiento/obtener_datos_condiciones_economicas', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController($request);
  return $response->obtener_datos_condiciones_economicas($request);
},'POST');

// DATOS ADELANTOS
Route::add('/arrendamiento/obtener_datos_adelantos', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController($request);
  return $response->obtener_datos_adelantos($request);
},'POST');
// DATOS INCREMENTOS
Route::add('/arrendamiento/obtener_datos_incrementos', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_incrementos($request);
},'POST');

// DATOS BENEFICIARIOS
Route::add('/arrendamiento/obtener_datos_beneficiarios', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_beneficiarios($request);
},'POST');

// DATOS INFLACIONES
Route::add('/arrendamiento/obtener_datos_inflaciones', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_inflaciones($request);
},'POST');
// DATOS CUOTAS ETRAORDINARAS
Route::add('/arrendamiento/obtener_datos_cuotas_extraordinarias', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_cuotas_extraordinarias($request);
},'POST');

// **    CAMBIOS DE AUDITORIA 
Route::add('/arrendamiento/obtener_datos_cambios_auditoria', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_cambios_auditoria($request);
},'POST');

// **    CAMBIOS DE DIRECCION MUNICIPAL 
Route::add('/arrendamiento/obtener_datos_direccion_municipal', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_direccion_municipal($request);
},'POST');
// **    CAMBIOS DE LICENCIA DE FUNCIONAMIENTO 
Route::add('/arrendamiento/obtener_datos_licencia_funcionamiento', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_licencia_funcionamiento($request);
},'POST');

// **    OBTENER DATOS CERTIFICADO DE INDECI 
Route::add('/arrendamiento/obtener_datos_certificado_indeci', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_certificado_indeci($request);
},'POST');

// **    OBTENER DATOS ANUNCIOS PUBLICITARIOS
Route::add('/arrendamiento/obtener_datos_anuncio_publicitario', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_anuncio_publicitario($request);
},'POST');

// **    OBTENER DATOS Declaración Jurada de Actividades Simultáneas:
Route::add('/arrendamiento/obtener_datos_anuncio_publicitario', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_anuncio_publicitario($request);
},'POST');

// **    OBTENER DATOS CONTRATO FIRMADO
Route::add('/arrendamiento/obtener_datos_contratos_firmados', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_contratos_firmados($request);
},'POST');
// **    OBTENER DATOS ARCHIVOS DE CONTRATO
Route::add('/arrendamiento/obtener_datos_archivos_contrato', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_archivos_contrato($request);
},'POST');

// **    OBTENER DATOS ADENDAS
Route::add('/arrendamiento/obtener_datos_adendas', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_adendas($request);
},'POST');

// **    OBTENER DATOS ADENDAS DETALLE
Route::add('/arrendamiento/obtener_datos_adendas_detalle', function() {
  $request = JsonRequestHandler::handle();
  $response = new ContratoArrendamientoController();
  return $response->obtener_datos_adendas_detalle($request);
},'POST');
*/
