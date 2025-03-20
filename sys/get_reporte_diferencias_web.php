<?php
include("db_connect.php");
include("sys_login.php");


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST)) {
	$result = [];
	$error = "";

	if ($_POST["accion"] === 'corregir_turno_id_a_nueva_caja') {
		$query = "
				update tbl_saldo_web_transaccion 
					set turno_id = " . $_POST['nueva_caja_id'] . "
					where turno_id = " . $_POST['caja_eliminada_id'] . "";
		$resp = $mysqli->query($query);
		if ($mysqli->error) {

			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al corregir la caja.";
			$result["result"] = $resp;
			$result["error"] = $error;

			echo json_encode($result);
			exit();

		} else {
			$result["http_code"] = 200;
			$result["status"] = "Se actualizó correctamente las transacciones.";
			$result["result"] = $resp;
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}

	if ($_POST["accion"] === 'transacciones_por_caja') {
		$query = ' 
			select 
				swt.cc_id as swtcc_id,
				l.id as local_id,
				l.nombre,
				swt.turno_id,
				swt.monto,
				ce.id as caja_eliminada_id,     
				ce.fecha_eliminacion
			from tbl_saldo_web_transaccion swt
			inner join tbl_caja_eliminados ce on swt.turno_id = ce.id
			inner join tbl_locales l on swt.cc_id = l.cc_id
			where swt.turno_id = ' . $_POST['caja_eliminada_id'] . "";

		$resp = $mysqli->query($query);

		if ($mysqli->error) {
			$error = $mysqli->error;
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		} else {
			$resp = $resp->fetch_all(MYSQLI_ASSOC);

			$result["http_code"] = 200;
			$result["status"] = "Datos registrados correctamente.";
			$result["result"] = $resp;
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}
} else {

	echo 'Error: no posteando';
	exit();
}
