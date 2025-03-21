<?php

function pdoStatement($query, $index = null)
{
	// $serverName = "138.59.65.155"; 
	$serverName = "bd-server-01.apuestatotal.pe";
	//	$serverName = "192.168.10.123"; 
	$connectionOptions = [
		"Database" => "ApuestaTotal",
		"Uid" => "db_api_leech",
		"PWD" => "db_api_leech"
	];

	$pdo = sqlsrv_connect($serverName, $connectionOptions);
	if ($pdo === false) die(print_r(sqlsrv_errors(), true));

	sqlsrv_query($pdo, "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	sqlsrv_query($pdo, "BEGIN TRANSACTION");

	$response = [];
	$statement = sqlsrv_query($pdo, $query);
	if ($index) {
		while ($r = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC)) {
			$response[$r[$index]] = $r;
		}
	} else {
		while ($response[] = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC));
	}
	unset($response[count($response) - 1]);

	sqlsrv_query($pdo, "COMMIT TRANSACTION");

	sqlsrv_close($pdo);
	return $response;
}
