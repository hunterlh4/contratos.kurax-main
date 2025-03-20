<?php

include("db_connect.php");
include("sys_login.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_register") {

	$busqueda_web_id = (int)$_POST["web_id"];
	$query_1 = "
	SELECT
		r.id,
		r.DocType,
		r.DocNumber,
		r.FirstName,
		r.LastName,
		r.BirthDate,
		r.Gender,
		r.Email,
		r.Password,
		r.Address,
		r.Depa,
		r.Prov,
		r.Dist,
		r.MobilePhone,
		r.TermsCheckbox,
		r.NoMobilePhone,
		r.ip,
		r.ClientId
	FROM
		at_web.registers AS r
		WHERE r.ClientId = " . $busqueda_web_id;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_transaccion) === 0) {
		$result["http_code"] = 204;
		$result["error"] = true;
		$result["status"] = "No hay registros.";
		$result["data"] = $list_transaccion;
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["error"] = false;
		$result["data"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["error"] = true;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "modificar_register") {

	$id_registers = isset($_POST["id_registers"])? (int)$_POST["id_registers"] : 0;
	$numero_doc = $_POST["numero_doc"];
	$correo = $_POST["correo"];
	$telefono = $_POST["telefono"];
	$query_1 = "
	UPDATE at_web.registers
	SET	
			DocNumber = '{$numero_doc}',
			Email = '{$correo}',
			MobilePhone = '{$telefono}'
	WHERE id = " . $id_registers;
	if ($id_registers > 0) {
		$mysqli->query($query_1);
		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
			$duplicado = $mysqli->error;
			if (strlen(stristr($duplicado, 'DUPLICATE')) > 0) {
				$result["mensaje"] = 'NRO de documento ya registrado';
			}
		}
		// if ($mysqli->affected_rows === 0) {
		// 	$result["http_code"] = 204;
		// 	$result["error"] = true;
		// 	$result["mensaje"] = "El registro no fue afectado.";
		// } 
		if (($mysqli->affected_rows > 0) || ($mysqli->affected_rows === 0) ) {
			$result["http_code"] = 200;
			$result["error"] = false;
			$result["mensaje"] = "Registro actualizado con éxito.";
		} else {
			$result["http_code"] = 400;
			$result["error"] = true;
			$result["status"] = "Ocurrió un error al consultar los registros.";
		}
	} else {
		$result["http_code"] = 400;
		$result["error"] = true;
		$result["mensaje"] = "Ocurrió un error al enviar los datos.";
	}
}

echo json_encode($result);
