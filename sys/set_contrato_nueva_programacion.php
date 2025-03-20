<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_programacion_de_pago") {

	$user_id = $login?$login['id']:null;
	if((int) $user_id>0){
		$created_at = date('Y-m-d H:i:s');

		$fecha_programacion = $_POST["fecha_programacion"];
		$razon_social_id = $_POST["empresa_id"];
		$tipo_programacion_id = $_POST["tipo_programacion_id"];
		$tipo_concepto_id = $_POST["tipo_concepto_id"];
		$tipo_pago_id = $_POST["tipo_pago_id"];
		$num_cuenta_id = $_POST["banco_id"];
		$valor_cambio = $_POST["tipo_de_cambio"];
		$provision_ids = $_POST["provision_ids"];
		$array_detalle_programacion_id = [];

		$fecha_programacion = date('Y-m-d', strtotime(str_replace('/', '-', $fecha_programacion)));


		// INICIO OBTENER NUM_PROGRAMACIÓN
		$query_obtener_num_programacion = "
		SELECT 
			MAX(numero) AS num_programacion
		FROM 
			cont_programacion
		";

		$list_query = $mysqli->query($query_obtener_num_programacion);

		if($mysqli->error){
			enviar_error($mysqli->error . $query_obtener_num_programacion);
		}

		$row_count = $list_query->num_rows;

		if ($row_count > 0) {
			$row = $list_query->fetch_assoc();
			$num_programacion = $row["num_programacion"] + 1;
			$num_programacion = str_pad($num_programacion, 10, '0', STR_PAD_LEFT);
		} else {
			enviar_error('No se puede obtener el número de programación');
		}
		// FIN OBTENER NUM_PROGRAMACIÓN


		// INICIO OBTENER EL TIPO DE MONEDA
		$query_obtener_tipo_moneda = "
		SELECT 
			moneda_id
		FROM 
			cont_num_cuenta
		WHERE 
			id = " . $num_cuenta_id . "
		";

		$list_query = $mysqli->query($query_obtener_tipo_moneda);

		if($mysqli->error){
			enviar_error($mysqli->error . $query_obtener_tipo_moneda);
		}

		$row_count = $list_query->num_rows;

		if ($row_count > 0) {
			$row = $list_query->fetch_assoc();
			$moneda_id = $row["moneda_id"];
		} else {
			enviar_error('No se puede obtener el tipo de moneda');
		}
		// FIN OBTENER EL TIPO DE MONEDA


		// INICIO OBTENER EL IMPORTE
		$importe = 0;
		$contador_array_ids = 0;
		$data_provision_importe = json_decode($provision_ids);
		$ids = '';

		foreach ($data_provision_importe as $value_provision_id_importe) {
			if ($contador_array_ids > 0) {
				$ids .= ',';
			}
			$ids .= $value_provision_id_importe;			
			$contador_array_ids++;
		}

		if($contador_array_ids == 0){
			enviar_error('La programación de pago no posee acreedores');
		}

		$query = "
		SELECT 
			p.importe,
			ce.tipo_moneda_id
		FROM
			cont_provision p
			INNER JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
		WHERE
			p.id IN(" . $ids . ")
		";

		$list_query = $mysqli->query($query);

		if($mysqli->error){
			enviar_error($mysqli->error . $query);
		}

		$row_count = $list_query->num_rows;

		if ($row_count > 0) {
			while ($row = $list_query->fetch_assoc()) {
				$importe += $row["importe"];
				if ($row["tipo_moneda_id"] != $moneda_id) {
					// enviar_error('Verifique que la moneda de las cuentas de los acreedores sea la misma que el número de cuenta seleccionada.');
				}
			}
		}
		// FIN OBTENER EL MONTO TOTAL


		// INICIO OBTENER EL VALOR DE CAMBIO
		// FIN OBTENER EL VALOR DE CAMBIO


		// INICIO INSERTAR PROGRAMACIÓN
		$query_insert_programacion = "
		INSERT INTO cont_programacion
		(
		numero,
		fecha_programacion,
		razon_social_id,
		tipo_programacion_id,
		tipo_concepto_id,
		tipo_pago_id,
		num_cuenta_id,
		valor_cambio,
		moneda_id,
		importe,
		etapa_id,
		status,
		user_created_id,
		created_at
		)
		VALUES
		(
		'" . $num_programacion . "',
		'" . $fecha_programacion . "',
		" . $razon_social_id . ",
		" . $tipo_programacion_id . ",
		" . $tipo_concepto_id . ",
		" . $tipo_pago_id . ",
		" . $num_cuenta_id . ",
		" . $valor_cambio . ",
		" . $moneda_id . ",
		" . $importe . ",
		2,
		1,
		" . $user_id . ",
		'" . $created_at . "'
		)
		";

		$mysqli->query($query_insert_programacion);
		$programacion_id = mysqli_insert_id($mysqli);
		if($mysqli->error){
			enviar_error($mysqli->error . $query_insert_programacion);
		}
		// FIN INSERTAR PROGRAMACIÓN

		// INICIO INSERTAR DETALLE PROGRAMACIÓN
		$array_detalle_programacion_id = [];

		$provision_ids = $_POST["provision_ids"];
		$data_provision = json_decode($provision_ids);
		// VALIDAR SI TIENE NUMERO DE CUENTA Y NUMERO DE DOCUMENTO

		foreach ($data_provision as $value_provision_id) {
			
			$query_insert_detalle_programacion = "
			INSERT INTO cont_programacion_detalle
			(
			programacion_id,
			provision_id,
			status,
			user_created_id,
			created_at
			)
			VALUES
			(
			" . $programacion_id . ",
			" . $value_provision_id . ",
			1,
			" . $user_id . ",
			'" . $created_at . "'
			)
			";
			$mysqli->query($query_insert_detalle_programacion);
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			if($mysqli->error){
				enviar_error($mysqli->error . $query_insert_detalle_programacion);
			}

			if ($tipo_programacion_id != 3) {
				$query_select_beneficiario = "
				SELECT 
					b.num_docu,
					b.nombre
				FROM 
					cont_provision p
					INNER JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id AND ce.status = 1
					INNER JOIN cont_beneficiarios b ON ce.contrato_id = b.contrato_id AND b.status = 1
				WHERE 
					p.id = $value_provision_id
				";

				$list_query = $mysqli->query($query_select_beneficiario);

				if($mysqli->error){
					enviar_error($mysqli->error . $query_select_beneficiario);
				}

				$row_count = $list_query->num_rows;

				if ($row_count > 0) {
					while ($row = $list_query->fetch_assoc()) {
						$num_docu = $row["num_docu"]!=''?$row["num_docu"]:'0';
						
						$update_campos = "
						UPDATE cont_provision
						SET ";
						if($num_docu == '0'){
						$update_campos .=" num_ruc = $num_docu ,";
							
						}
						$update_campos .="  etapa_id = 3
						WHERE id = $value_provision_id
						";

						$mysqli->query($update_campos);

						if($mysqli->error){
							enviar_error($mysqli->error . $update_campos);
						}
					}
				}
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $array_detalle_programacion_id;
	}else{
        $result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_cambios_del_programacion_de_pago") {

	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');

	$programacion_id = $_POST["programacion_id_edit"];

	$fecha_programacion = $_POST["fecha_programacion"];
	$tipo_concepto_id = $_POST["tipo_concepto_id"];
	$tipo_pago_id = $_POST["tipo_pago_id"];
	$num_cuenta_id = $_POST["banco_id"];
	$valor_cambio = 3.154;
	$provision_ids = $_POST["provision_ids"];
	$array_detalle_programacion_id = [];

	$fecha_programacion = date('Y-m-d', strtotime(str_replace('/', '-', $fecha_programacion)));

	// INICIO OBTENER EL TIPO DE MONEDA
	$query_obtener_tipo_moneda = "
	SELECT 
		moneda_id
	FROM 
		cont_num_cuenta
	WHERE 
		id = " . $num_cuenta_id . "
	";

	$list_query = $mysqli->query($query_obtener_tipo_moneda);

	if($mysqli->error){
		enviar_error($mysqli->error . $query_obtener_tipo_moneda);
	}

	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		$row = $list_query->fetch_assoc();
		$moneda_id = $row["moneda_id"];
	} else {
		enviar_error('No se puede obtener el tipo de moneda');
	}
	// FIN OBTENER EL TIPO DE MONEDA


	// INICIO OBTENER EL IMPORTE
	$importe = 0;
	$contador_array_ids = 0;
	$data_provision_importe = json_decode($provision_ids);
	$ids = '';

	foreach ($data_provision_importe as $value_provision_id_importe) {
		if ($contador_array_ids > 0) {
			$ids .= ',';
		}
		$ids .= $value_provision_id_importe;			
		$contador_array_ids++;
	}

	if($contador_array_ids == 0){
		enviar_error('La programación de pago no posee acreedores');
	}

	$query = "
	SELECT 
		p.importe,
		ce.tipo_moneda_id
	FROM
		cont_provision p
		INNER JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
	WHERE
		p.id IN(" . $ids . ")
	";

	$list_query = $mysqli->query($query);

	if($mysqli->error){
		enviar_error($mysqli->error . $query);
	}

	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		while ($row = $list_query->fetch_assoc()) {
			$importe += $row["importe"];
			if ($row["tipo_moneda_id"] != $moneda_id) {
				enviar_error('Verifique que la moneda de las cuentas de los acreedores sea la misma que el número de cuenta seleccionada.');
			}
		}
	}
	// FIN OBTENER EL MONTO TOTAL


	// INICIO OBTENER EL VALOR DE CAMBIO
	// FIN OBTENER EL VALOR DE CAMBIO


	// INICIO INSERTAR PROGRAMACIÓN
	$query_update_programacion = "
	UPDATE cont_programacion 
	SET 
	    fecha_programacion = '$fecha_programacion',
	    tipo_concepto_id = $tipo_concepto_id,
	    tipo_pago_id = $tipo_pago_id,
	    num_cuenta_id = $num_cuenta_id,
	    valor_cambio = $valor_cambio,
	    moneda_id = $moneda_id,
	    importe = $importe,
	    user_updated_id = $user_id,
	    updated_at = '$created_at',
	    user_edit_id = $user_id,
	    edit_at = '$created_at'
	WHERE
	    id = '$programacion_id'
	";

	$mysqli->query($query_update_programacion);
	if($mysqli->error){
		enviar_error($mysqli->error . $query_update_programacion);
	}
	// FIN INSERTAR PROGRAMACIÓN


	// INICIO OBTENER PROVICIONES ACTUALES
	$query_detalle_programacion = "
	SELECT 
	    provision_id
	FROM 
		cont_programacion_detalle
	WHERE 
		status = 1
		AND programacion_id = " . $programacion_id . "
	";

	$list_query = $mysqli->query($query_detalle_programacion);

	if($mysqli->error){
		enviar_error($mysqli->error . $query_detalle_programacion);
	}

	$row_count = $list_query->num_rows;

	$array_provision_actuales = array();
	if ($row_count > 0) {
		while ($row = $list_query->fetch_assoc()) {
			array_push($array_provision_actuales, $row["provision_id"]);
		}
	}
	// FIN OBTENER PROVICIONES ACTUALES


	$array_detalle_programacion_id = [];
	$provision_ids = $_POST["provision_ids"];
	$array_provision_nuevos = json_decode($provision_ids);


	// INICIO ELIMINAR DETALLE DE LA PROGRAMACION
	$array_provision_debaja = array_diff($array_provision_actuales, $array_provision_nuevos);
	foreach ($array_provision_debaja as $value_provision_id_de_baja) {
		$sql_update_provision_de_baja = "
		UPDATE cont_programacion_detalle
		SET
			status = 0,
			user_updated_id = $user_id,
			updated_at = '$created_at'
		WHERE 
			programacion_id = $programacion_id 
			AND provision_id = $value_provision_id_de_baja;
		";
		$mysqli->query($sql_update_provision_de_baja);
		if($mysqli->error){
			enviar_error($mysqli->error . $sql_update_provision_de_baja);
		}
	}
	// FIN ELIMINAR DETALLE DE LA PROGRAMACION


	// INICIO INSERTAR DETALLE PROGRAMACIÓN NUEVOS
	$array_provision_nuevos_acreedores = array_diff($array_provision_nuevos, $array_provision_actuales);
	foreach ($array_provision_nuevos_acreedores as $value_provision_id) {
		$query_insert_detalle_programacion = "
		INSERT INTO cont_programacion_detalle
		(
		programacion_id,
		provision_id,
		status,
		user_created_id,
		created_at
		)
		VALUES
		(
		" . $programacion_id . ",
		" . $value_provision_id . ",
		1,
		" . $user_id . ",
		'" . $created_at . "'
		)
		";
		$mysqli->query($query_insert_detalle_programacion);
		$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
		if($mysqli->error){
			enviar_error($mysqli->error . $query_insert_detalle_programacion);
		}
	}
	// FIN INSERTAR DETALLE PROGRAMACIÓN NUEVOS

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $array_detalle_programacion_id;
}


function enviar_error($error){
	$result["http_code"] = 400;
	$result["result"] = 'Ocurrio un error : ' . $error;
	echo json_encode($result);
	exit();
}

echo json_encode($result);
?>