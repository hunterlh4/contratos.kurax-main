<?php
include 'adodb.inc.php';
//include 'adodb-exceptions.inc.php';

$db = ADONewConnection('pgsql');

$host_good = 'localhost';
$host_evil = '1.3.3.7';
$host_evil = '192.168.1.1';
$user_good = 'dregad';
$user_evil = "'dregad' host='$host_evil'";
$user_evil2 = "'dregad' host='$host_good'";
$password = 'C0yote71';
$database = 'bugtracker';

//var_dump($db->connect($host_good, $user_good, $password, $database)); // success
//var_dump($db->connect($host_evil, $user_good, $password, $database)); // expected failure
var_dump($db->connect($host_evil, $user_evil2, $password, $database)); // expected failure
//var_dump($db->connect($host_good, $user_evil, $password, $database)); // !!!
