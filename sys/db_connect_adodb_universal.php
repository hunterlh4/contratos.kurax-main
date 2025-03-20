<?php
/**
 * @author Edwin Huarhua
 * Variables de conexion a base datos usada por el framework ADODB
 * Arquitectura MVC custom
 */
require_once('vendor/adodb5/adodb.inc.php');
require_once('vendor/adodb5/adodb-active-record.inc.php');
require_once('vendor/adodb5/adodb-exceptions.inc.php');
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$ADODB_FORCE_VALUE = ADODB_FORCE_IGNORE;
$_adodb_host=env('DB_HOST_UNIVERSAL');
$_adodb_user=env('DB_USERNAME_UNIVERSAL');
$_adodb_pass=env('DB_PASSWORD_UNIVERSAL');
$_adodb_database= env('DB_DATABASE_UNIVERSAL');
$_adodb_port='3306';

$_adb = ADONewConnection('mysqli'); # eg 'mysql' o 'postgres'
$_adb->port = $_adodb_port;
$_adb->setFetchMode(ADODB_FETCH_ASSOC);
$_adb->connect($_adodb_host,$_adodb_user,$_adodb_pass,$_adodb_database);
$_adb->setCharset('utf8');
ADOdb_Active_Record::SetDatabaseAdapter($_adb);
date_default_timezone_set("America/Lima");
//--------------------------------------------------------------------------------------//
?>