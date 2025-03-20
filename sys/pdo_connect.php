<?php 

$server = '138.59.65.155';
$port = '55501';
$user = 'db_api_leech';
$pass = 'db_api_leech';
$database = 'ApuestaTotal';
$pdo = "";

try {
	$pdo = new \PDO(
		sprintf(
			"dblib:host=%s;dbname=%s;port=%s",
			$server,
			$database,
			$port
		),
		$user,
		$pass
	);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { echo "There was a problem connecting. " . $e->getMessage(); }

function pdoStatement($query){
	$statement = $GLOBALS['pdo']->prepare($query);
	$statement->execute();
	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

?>