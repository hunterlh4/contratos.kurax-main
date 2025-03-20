<?php
$result = array();
include("db_connect.php");
include("sys_login.php");

// include("function_replace_invalid_caracters.php");
require_once '../phpexcel/classes/PHPExcel.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_DEPRECATED);


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER CUENTAS
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_transacciones") {

	$query = "	
SELECT
	e.id,
	e.label,
	IFNULL( e.description, '' ) description,
	e.color,
	IFNULL(et.descripcion, '') tipo,
	IFNULL(e.tipo, 1) tipo_id,
	e.created_at,
	u.usuario user_reg,
	IFNULL( e.updated_at, '' ) updated_at,
	IFNULL( up.usuario, '' ) user_edi,
	( SELECT COUNT( * ) FROM tbl_televentas_clientes_etiqueta ce WHERE ce.etiqueta_id = e.id AND ce.`status` = 1 ) cant_clientes 
FROM
	tbl_televentas_etiqueta e
	JOIN tbl_usuarios u ON u.id = e.created_user_id
	LEFT JOIN tbl_usuarios up ON up.id = e.updated_user_id 
	LEFT JOIN tbl_televentas_etiqueta_tipo et ON IFNULL(e.tipo, 1) = et.id
WHERE
	e.`status` =1
	
	";

	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
	}
}



//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER COMISIONES
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_comisiones") {
	$html = '';

	$query = "	
	SELECT 
		c.id,
		c.comision_monto,
		c.status,
		IFNULL(c.created_at, '') created_at,
		u.usuario user_reg,
		IFNULL(c.updated_at, '') updated_at,
		IFNULL(up.usuario, '') user_edi
	FROM
		tbl_televentas_tipo_comision c
		JOIN tbl_usuarios u ON u.id = c.id_user_created
		LEFT JOIN tbl_usuarios up ON up.id = c.id_user_updated
	ORDER BY comision_monto ASC
	";

	$list_query = $mysqli->query($query);
	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		$html .= '<table class="table table-bordered table-striped no-mb" id="tbl_comisiones_data">';
		$html .= '<thead>';
		$html .= '<tr>';

		$html .= '<th>Comisión</th>';
		$html .= '<th>Estado</th>';
		$html .= '<th>Creada por</th>';
		$html .= '<th>Creada el</th>';
		$html .= '<th>Actualizada por</th>';
		$html .= '<th>Actualizada el</th>';
		$html .= '<th>Acciones</th>';

		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		while ($row = $list_query->fetch_assoc()) {
			$status = $row["status"];		

			if ($status == '1') {
				$estado = '<span class="label label-success">Activo</span>';
			} else {
				$estado = '<span class="label label-danger">Inactivo</span>';
			}

			$html .= '<tr>';
			$html .= '<td>' . $row["comision_monto"] . '</td>';
			$html .= '<td>' . $estado . '</td>';
			$html .= '<td>' . $row["user_reg"] . '</td>';
			$html .= '<td>' . $row["created_at"] . '</td>';
			$html .= '<td>' . $row["user_edi"] . '</td>';
			$html .= '<td>' . $row["updated_at"] . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="top" title="Editar comisión" onclick="obtener_comision(' . $row["id"] . ',\'' . $row["comision_monto"] . '\', ' . $row["status"] . ')"><i class="fa fa-edit"></i> Editar</a>';
			$html .= '</td>';
			$html .= '</tr>';

		}

		$html .= '</tbody>';
		$html .= '</table>';
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if ($row_count == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
	} elseif ($row_count > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $html;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
	}
}



//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER MOTIVOS
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_motivos") {
	$html = '';

	$query = "	
		SELECT 
			m.id,
			m.motivo,
			mt.tipo tipo_motivo,
			m.tipo_id id_tipo,
			m.estado,
			IFNULL(m.created_at, '') created_at,
			u.usuario user_reg,
			IFNULL(m.updated_at, '') updated_at,
			IFNULL(up.usuario, '') user_edi
		FROM
			tbl_televentas_motivos m
			INNER JOIN tbl_televentas_motivos_tipo mt ON m.tipo_id = mt.id
			INNER JOIN tbl_usuarios u ON u.id = m.user_created
			LEFT JOIN tbl_usuarios up ON up.id = m.user_updated
		ORDER BY m.id ASC
	";

	if($mysqli->error){
		$result["query_2_error"] = $mysqli->error;
		echo json_encode($result); exit();
	}

	$list_query = $mysqli->query($query);
	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		$html .= '<table class="table table-bordered table-striped no-mb" id="tbl_motivos_data">';
		$html .= '<thead>';
		$html .= '<tr>';

		$html .= '<th>Motivo</th>';
		$html .= '<th>Tipo Motivo</th>';
		$html .= '<th>Estado</th>';
		$html .= '<th>Creada por</th>';
		$html .= '<th>Creada el</th>';
		$html .= '<th>Actualizada por</th>';
		$html .= '<th>Actualizada el</th>';
		$html .= '<th>Acciones</th>';

		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		while ($row = $list_query->fetch_assoc()) {
			$status = $row["estado"];		

			if ($status == '1') {
				$estado = '<span class="label label-success">Activo</span>';
			} else {
				$estado = '<span class="label label-danger">Inactivo</span>';
			}

			$html .= '<tr>';
			$html .= '<td>' . $row["motivo"] . '</td>';
			$html .= '<td>' . $row["tipo_motivo"] . '</td>';
			$html .= '<td>' . $estado . '</td>';
			$html .= '<td>' . $row["user_reg"] . '</td>';
			$html .= '<td>' . $row["created_at"] . '</td>';
			$html .= '<td>' . $row["user_edi"] . '</td>';
			$html .= '<td>' . $row["updated_at"] . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="top" title="Editar comisión" onclick="obtener_motivos(' . $row["id"] . ',\'' . $row["motivo"] . '\',\'' . $row["id_tipo"] . '\', ' . $row["estado"] . ')"><i class="fa fa-edit"></i> Editar</a>';
			$html .= '</td>';
			$html .= '</tr>';

		}

		$html .= '</tbody>';
		$html .= '</table>';
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		echo json_encode($result); exit();
	}

	if ($row_count == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
	} elseif ($row_count > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $html;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
	}
}


//*******************************************************************************************************************
// GUARDAR MOTIVO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_motivo") {
	include("function_replace_invalid_caracters.php");

	$cod_motivo = $_POST["cod_motivo"];
	$motivo = $_POST["motivo"];
	$tipo = $_POST["tipo"];

	$usuario_id = $login?$login['id']:0;
	if((int) $usuario_id >0){
		$query_validar_duplicado = "
		SELECT 
			id
		FROM tbl_televentas_motivos 
		WHERE 
			motivo = '".$motivo."' 
			AND tipo_id = '".$tipo."' 
		";

		$list_query = $mysqli->query($query_validar_duplicado);
		$row_count = $list_query->num_rows;

		$id = $cod_motivo;
		while ($row = $list_query->fetch_assoc()) {
			$id = $row["id"];
		}

		if ($row_count > 0 && (((int) $cod_motivo === 0) || ((int) $cod_motivo != $id))) {
			$result["http_code"] = 400;
			$result["status"] = "Ya existe un motivo con el valor ingresado.";
			echo json_encode($result); exit();
		}

		if((int) $cod_motivo === 0){
			$insert_1=" 
				INSERT INTO tbl_televentas_motivos
					(
					`motivo`,
					`tipo_id`,
					`estado`,
					`user_created`,
					`created_at`
					) VALUES (
					'".$motivo."',
					'".$tipo."',
					'1',
					'".$usuario_id."',
					now()
					)";
			$mysqli->query($insert_1);
			if($mysqli->error){
				$result["insert_1_error"] = $mysqli->error;
				echo json_encode($result); exit();
		}
		}
		if((int) $cod_motivo > 0){
			$estado = $_POST["estado"];
			$update_1=" 
				UPDATE tbl_televentas_motivos 
				SET motivo = '".$motivo."', 
					tipo_id = '".$tipo."', 
					estado = ".$estado.", 
					user_updated = '".$usuario_id."', 
					updated_at = now() 
				WHERE id = '". $cod_motivo ."' 
					";
			$mysqli->query($update_1);
			if($mysqli->error){
				$result["update_1_error"] = $mysqli->error;
				echo json_encode($result); exit();
			}
		}

		$query_2 ="
			SELECT 
				id
			FROM tbl_televentas_motivos 
			WHERE 
				motivo = '".$motivo."' 
				AND tipo_id = '".$tipo."' 
			";
		//$result["consulta_query"] = $query_3;
		$list_query=$mysqli->query($query_2);
		$list_2=array();
		while ($li=$list_query->fetch_assoc()) {
			$list_2[]=$li;
		}
		if($mysqli->error){
			$result["query_2_error"] = $mysqli->error;
			echo json_encode($result); exit();
		}
		if(count($list_2)==1){
			$result["http_code"] = 200;
			$result["status"] ="ok";
		} else {
			$result["http_code"] = 400;
			$result["status"] ="Ocurrió un error al registrar el motivo.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Sesión perdida.";
	}
}




//*******************************************************************************************************************
// GUARDAR ETIQUETA
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_etiqueta") {
	include("function_replace_invalid_caracters.php");

	$cod_etiqueta = $_POST["cod_etiqueta"];
	$i_etiqueta = strtoupper(trim(replace_invalid_caracters($_POST["i_etiqueta"])));
	$txa_observacion = strtoupper(trim(replace_invalid_caracters($_POST["txa_observacion"])));
	$i_color = $_POST["i_color"];
	$tipo = $_POST["tipo"];

	$usuario_id = $login?$login['id']:0;
	if((int) $usuario_id >0){
		if((int) $cod_etiqueta === 0){
			$insert_1=" 
				INSERT INTO tbl_televentas_etiqueta
					(
					`label`,
					`description`,
					`color`,
					`level`,
					`tipo`,
					`status`,
					`created_user_id`,
					`created_at`
					) VALUES (
					'".$i_etiqueta."',
					'".$txa_observacion."',
					'".$i_color."',
					'1',
					'" . $tipo . "',
					'1',
					'".$usuario_id."',
					now()
					)";
			$mysqli->query($insert_1);
			if($mysqli->error){
				$result["insert_1_error"] = $mysqli->error;
			}
		}
		if((int) $cod_etiqueta > 0){
			$update_1=" 
				UPDATE tbl_televentas_etiqueta 
				SET label = '".$i_etiqueta."', 
					description = '".$txa_observacion."', 
					color = '".$i_color."', 
					tipo = '" . $tipo . "',
				updated_at = now() 
				WHERE id = '". $cod_etiqueta ."' 
					";
			$mysqli->query($update_1);
			if($mysqli->error){
				$result["update_1_error"] = $mysqli->error;
			}
		}

		$query_2 ="
			SELECT 
				e.id,
				e.label
			FROM tbl_televentas_etiqueta e
			WHERE 
				e.label = '".$i_etiqueta."' 
				AND e.description = '".$txa_observacion."' 
				AND e.color = '".$i_color."' 
			";
		//$result["consulta_query"] = $query_3;
		$list_query=$mysqli->query($query_2);
		$list_2=array();
		while ($li=$list_query->fetch_assoc()) {
			$list_2[]=$li;
		}
		if($mysqli->error){
			$result["query_2_error"] = $mysqli->error;
		}
		if(count($list_2)==1){
			$result["http_code"] = 200;
			$result["status"] ="ok";
		} else {
			$result["http_code"] = 400;
			$result["status"] ="Ocurrió un error al registrar la etiqueta.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Sesión perdida.";
	}
}




//*******************************************************************************************************************
// GUARDAR COMISIÓN
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_comision") {
	include("function_replace_invalid_caracters.php");

	$cod_comision = $_POST["cod_comision"];
	$i_comision = $_POST["i_comision"];

	$usuario_id = $login?$login['id']:0;
	if((int) $usuario_id >0){
		$query_validar_duplicado = "
		SELECT 
			id
		FROM tbl_televentas_tipo_comision 
		WHERE 
			comision_monto = '".$i_comision."' 
		";

		$list_query = $mysqli->query($query_validar_duplicado);
		$row_count = $list_query->num_rows;

		$id = $cod_comision;
		while ($row = $list_query->fetch_assoc()) {
			$id = $row["id"];
		}

		if ($row_count > 0 && (((int) $cod_comision === 0) || ((int) $cod_comision != $id))) {
			$result["http_code"] = 400;
			$result["status"] = "Ya existe una comisión con el valor ingresado.";
			echo json_encode($result);
			exit();
		}

		if((int) $cod_comision === 0){
			$insert_1=" 
				INSERT INTO tbl_televentas_tipo_comision
					(
					`comision_monto`,
					`status`,
					`id_user_created`,
					`created_at`
					) VALUES (
					'".$i_comision."',
					'1',
					'".$usuario_id."',
					now()
					)";
			$mysqli->query($insert_1);
			if($mysqli->error){
				$result["insert_1_error"] = $mysqli->error;
			}
		}
		if((int) $cod_comision > 0){
			$i_estado = $_POST["i_estado"];
			$update_1=" 
				UPDATE tbl_televentas_tipo_comision 
				SET comision_monto = '".$i_comision."', 
					status = ".$i_estado.", 
					id_user_updated = '".$usuario_id."', 
				updated_at = now() 
				WHERE id = '". $cod_comision ."' 
					";
			$mysqli->query($update_1);
			if($mysqli->error){
				$result["update_1_error"] = $mysqli->error;
			}
		}

		$query_2 ="
			SELECT 
				id
			FROM tbl_televentas_tipo_comision 
			WHERE 
				comision_monto = '".$i_comision."' 
			";
		//$result["consulta_query"] = $query_3;
		$list_query=$mysqli->query($query_2);
		$list_2=array();
		while ($li=$list_query->fetch_assoc()) {
			$list_2[]=$li;
		}
		if($mysqli->error){
			$result["query_2_error"] = $mysqli->error;
		}
		if(count($list_2)==1){
			$result["http_code"] = 200;
			$result["status"] ="ok";
		} else {
			$result["http_code"] = 400;
			$result["status"] ="Ocurrió un error al registrar la comisión.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Sesión perdida.";
	}
}










//*******************************************************************************************************************
//*******************************************************************************************************************
// ELIMINAR ETIQUETA
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_etiqueta") {

	$cod_etiqueta = $_POST["cod_etiqueta"];

	$usuario_id = $login ? $login['id'] : null;
	if ((int) $usuario_id > 0) {

		$query_1 = "
			SELECT 
				e.id,
				e.label
			FROM tbl_televentas_etiqueta e
			WHERE 
				e.id = '".$cod_etiqueta."' 
				AND e.status = '1' 
				";
		//$result["consulta_query"] = $query_1;
		$list_query = $mysqli->query($query_1);
		$list_transaccion = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if ($mysqli->error) {
			$result["query_1_error"] = $mysqli->error;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "La etiqueta ya ha sido eliminada.";
		} elseif (count($list_transaccion) == 1) {

			$query_update = "
						UPDATE tbl_televentas_etiqueta 
						SET 
							status = '0',
							updated_user_id= '" . $usuario_id . "',
							updated_at=now()
						WHERE id = '" . $cod_etiqueta . "'
					";
			$mysqli->query($query_update);
			if ($mysqli->error) {
				$result["update_error"] = $mysqli->error;
			}

			$query_2 ="
				SELECT 
					e.id,
					e.label
				FROM tbl_televentas_etiqueta e
				WHERE 
					e.id = '".$cod_etiqueta."' 
					AND e.status = '0' 
				";
			//$result["consulta_query"] = $query_3;
			$list_query=$mysqli->query($query_2);
			$list_2=array();
			while ($li=$list_query->fetch_assoc()) {
				$list_2[]=$li;
			}
			if($mysqli->error){
				$result["query_2_error"] = $mysqli->error;
			}
			if(count($list_2)==1){
				$result["http_code"] = 200;
				$result["status"] ="ok";
			} else {
				$result["http_code"] = 400;
				$result["status"] ="Ocurrió un error al eliminar la etiqueta.";
			}

		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el estado de la transacción.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida, actualice la página.";
	}
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER LIMITE REGISTRADO
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_limites_reg") {

	$query = "SELECT IFNULL(valor, 0) limite,
			(SELECT IFNULL(valor, 0) max_aten             
			FROM tbl_televentas_parametros  
			WHERE estado = 1 AND nombre_codigo ='max_aten') max_aten, 
			(SELECT IFNULL(valor, 0) limite_terc             
			FROM tbl_televentas_parametros  
			WHERE estado = 1 AND nombre_codigo ='limite_terc') limite_terc
			FROM tbl_televentas_parametros 
			WHERE estado = 1 AND nombre_codigo ='limite'  Limit 1";

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER PARAMETROS DE COMPROBANTE DE PAGOS SIN NOTIFICAR
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_paremetros_comprobante_de_pago_sin_notificar") {

	$query = "SELECT valor
	FROM tbl_televentas_parametros
	WHERE nombre_codigo = 'num_minutos_consultar_voucher_sin_envio'";
	$list_query = $mysqli->query($query);
	$num_minutos_consultar_voucher_sin_envio = "";
	while ($li = $list_query->fetch_assoc()) {
		$num_minutos_consultar_voucher_sin_envio = $li["valor"];
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$query = "SELECT valor
	FROM tbl_televentas_parametros
	WHERE nombre_codigo = 'rango_dias_consultar_voucher_sin_envio'";
	$list_query = $mysqli->query($query);
	$rango_dias_consultar_voucher_sin_envio = "";
	while ($li = $list_query->fetch_assoc()) {
		$rango_dias_consultar_voucher_sin_envio = $li["valor"];
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if ($num_minutos_consultar_voucher_sin_envio == "" || $rango_dias_consultar_voucher_sin_envio == "") {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} else {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["num_minutos_consultar_voucher_sin_envio"] = $num_minutos_consultar_voucher_sin_envio;
		$result["rango_dias_consultar_voucher_sin_envio"] = $rango_dias_consultar_voucher_sin_envio;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_limite_terceros") {

	$limite_terc = $_POST["limite_terc"]; 

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "UPDATE tbl_televentas_parametros 
			SET valor = '". $limite_terc . "',
			updated_user_id = ". $usuario_id . ",
			updated_at = now()
			WHERE estado = 1 AND nombre_codigo ='limite_terc'";

	$mysqli->query($query);

	$result["query"] = $query;
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER LIMITE DE DIAS PARA EDITAR LOS DEPOSITOS Y RETIROS APROBADOS
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_limites_dias_para_editar_depositos_retiros") {

	$query = "SELECT valor
			FROM tbl_televentas_parametros  
			WHERE estado = 1
			AND nombre_codigo = 'editar_depositos_pagos' Limit 1";

	$list_query = $mysqli->query($query);
	$list_limit = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_limit[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_limit) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros de límites de días para editar los depósitos y retiros.";
	} elseif (count($list_limit) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_limit;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los límites de días";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER SUPERVISORES
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_supervisores") {

	$query = "	
			SELECT 
				u.id,
				CONCAT(p.nombre, ' ', p.apellido_paterno) as supervisor,
				c.nombre AS cargo,
				g.nombre AS grupo,
				g.id grupo_id,
				u.estado
			FROM tbl_usuarios  u
				LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
				LEFT JOIN tbl_cargos c ON (c.id = p.cargo_id)
				LEFT JOIN tbl_usuarios_grupos g ON (g.id = u.grupo_id)
			WHERE u.estado = 1 
			#and u.grupo_id = 31  #televentas-supervisor
				and c.id = 4
				ORDER BY CONCAT(p.nombre, ' ', p.apellido_paterno) ASC";

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER PROGRAMACIONES
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_programaciones_supervisores") {
	$supervisor = $_POST["supervisor"];
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

    $where_supervisor = '';
    if($supervisor > 0){
    	$where_supervisor = ' AND tp.user_id = ' . $supervisor;
    }

	$query = "	
			SELECT 
				IFNULL(tp.id, 0) id, 
				tp.user_id,
				CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno,'')) as supervisor,
				desde, hasta, created_at, created_user_id
			FROM tbl_televentas_programaciones tp
				INNER JOIN tbl_usuarios u ON tp.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE tipo_programacion = 1 AND tp.desde >= '" . $desde_time . "' AND tp.hasta <= '" . $hasta_time . "' " . $where_supervisor ;

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// GUARDAR PROGRAMACION
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_programacion_horario_supervisor") {
	$supervisor = $_POST["supervisor"];
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "
				INSERT INTO tbl_televentas_programaciones 
				(user_id, desde, hasta, tipo_programacion, created_at, created_user_id, status)
				VALUES (" 
				. $supervisor . ",'" 
				. $desde_time . "','" 
				. $hasta_time . "',
					1,
					now()," 
				. $usuario_id . ",1)";

	$mysqli->query($query);
	$result["query"] = $query;
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_limite_clientes") {
 
	$limite = $_POST["limite"]; 

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "UPDATE tbl_televentas_parametros 
			SET valor = '". $limite . "',
			updated_user_id = ". $usuario_id . ",
			updated_at = now()
			WHERE estado = 1 AND nombre_codigo ='limite'";

	$mysqli->query($query);

	$result["query"] = $query;
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_num_minutos_consultar_voucher_sin_envio") {

	$num_minutos = $_POST["num_minutos"];

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "
	UPDATE tbl_televentas_parametros
	SET valor = '" . $num_minutos . "',
	updated_user_id = " . $usuario_id . ",
	updated_at = now()
	WHERE nombre_codigo = 'num_minutos_consultar_voucher_sin_envio'";

	$mysqli->query($query);

	$result["query"] = $query;
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_rango_dias_consultar_voucher_sin_envio") {

	$num_dias = $_POST["num_dias"];

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "
	UPDATE tbl_televentas_parametros
	SET valor = '" . $num_dias . "',
	updated_user_id = " . $usuario_id . ",
	updated_at = now()
	WHERE nombre_codigo = 'rango_dias_consultar_voucher_sin_envio'";

	$mysqli->query($query);

	$result["query"] = $query;
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_max_aten_clientes") {
 
	$max_aten = $_POST["max_aten"]; 

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "UPDATE tbl_televentas_parametros 
			SET valor = '". $max_aten . "',
			updated_user_id = ". $usuario_id . ",
			updated_at = now()
			WHERE estado = 1 AND nombre_codigo ='max_aten'";

	$mysqli->query($query);

	$result["query"] = $query;
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "modificar_limite_dias_para_editar_reti_depos_aprobados") {
 
	$limite_dias = $_POST["limite_dias"]; 

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query_up = "UPDATE tbl_televentas_parametros 
			SET valor = $limite_dias,
			updated_at = now(),
			updated_user_id = " . $usuario_id . "
		WHERE estado = 1 AND nombre_codigo = 'editar_depositos_pagos' ";
	$mysqli->query($query_up);

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_programaciones_supervisores_guardadas") {
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

	$query = "	
			SELECT 
				IFNULL(tp.id, 0) id, 
				tp.user_id,
				CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno,'')) as supervisor,
				desde, hasta, created_at, created_user_id
			FROM tbl_televentas_programaciones tp
				INNER JOIN tbl_usuarios u ON tp.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE ((tp.desde >= '" . $desde_time . "' AND tp.desde <= '" . $hasta_time . "') 
				OR (tp.hasta >= '" . $desde_time . "' AND tp.hasta <= '" . $hasta_time . "')) AND  tipo_programacion = 1 ";

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
		$result["query"] = $query;
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_programaciones_supervisores_edicion") {
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];
	$programacion = $_POST["programacion"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

	$query = "	
			SELECT 
				IFNULL(tp.id, 0) id, 
				tp.user_id,
				CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno,'')) as supervisor,
				desde, hasta, created_at, created_user_id
			FROM tbl_televentas_programaciones tp
				INNER JOIN tbl_usuarios u ON tp.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE ((tp.desde >= '" . $desde_time . "' AND tp.desde <= '" . $hasta_time . "') 
				OR (tp.hasta >= '" . $desde_time . "' AND tp.hasta <= '" . $hasta_time . "')) 
				AND tp.id not in (" . $programacion . ") AND tipo_programacion = 1";

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
		$result["query"] = $query;
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
		$result["query"] = $query;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// ACTUALIZAR PROGRAMACION
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "actualizar_programacion_horario_supervisor") {
	$programacion = $_POST["programacion"];
	$supervisor = $_POST["supervisor"];
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "
				UPDATE tbl_televentas_programaciones 
					SET desde = '" . $desde .  "', 
						hasta = '" . $hasta . "', 
						updated_at = now(), 
						updated_user_id = " . $usuario_id . "
				WHERE id = " . $programacion;

	$mysqli->query($query);
	$result["query"] = $query;
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}



//*******************************************************************************************************************
//*******************************************************************************************************************
// DINERO AT
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "listar_eventos_dinero_at") {
	$query = "
		SELECT 
			e.id,
			e.nombre,
			e.codigo_evento,
			DATE(e.fecha_inicio) fecha_inicio,
			DATE(e.fecha_fin) fecha_fin,
			IFNULL(e.tipo_monto, 1) tipo_monto,
			IFNULL(e.porcentaje_monto_minimo, 0) porcentaje_monto_minimo,
			e.monto_cliente,
			e.limite_clientes,
			IFNULL(e.tipo_conversion, 1) tipo_conversion,
			IFNULL(e.conversion_maxima, 0) conversion_maxima,
            ROUND(e.monto_cliente * e.limite_clientes, 2) limite_monto,
			IFNULL(e.rollover, '') rollover,
			e.juegos_virtuales_activo,
			e.bingo_activo,
			e.sportbook_activo,
			-- IF( date(e.created_at) = date(now()), '1', '0' ) eliminable,
			IF( e.fecha_fin >= date(now()), '1', '0' ) importable,
            IFNULL((SELECT COUNT(c.dinero_at_evento_id) FROM tbl_televentas_dinero_at_eventos_clientes c WHERE c.dinero_at_evento_id = e.id GROUP BY c.dinero_at_evento_id), 0) cantidad_clientes,
			IFNULL(
				(	SELECT 
						COUNT(DISTINCT ec.cliente_id) AS cant_clientes
					FROM tbl_televentas_dinero_at_eventos e1
						LEFT JOIN tbl_televentas_dinero_at_eventos_clientes ec ON e1.id = ec.dinero_at_evento_id
						LEFT JOIN tbl_televentas_clientes_transaccion ct ON ec.cliente_id = ct.cliente_id
					WHERE e.id = ct.evento_dineroat_id
						AND ec.dinero_at_evento_id = e.id
						AND ct.estado != 0 
						AND ct.tipo_id IN (4,5)
				)
			, 0 ) cant_clientes_usando
            -- IFNULL((SELECT SUM(c.monto) FROM tbl_televentas_dinero_at_eventos_clientes c WHERE c.dinero_at_evento_id = e.id), 0) monto_utilizado
		FROM
			tbl_televentas_dinero_at_eventos e
		WHERE
			estado = 1
		ORDER BY e.created_at DESC
	";
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["query_error"] = $mysqli->error;
		$result["query"] = $query;
		echo json_encode($result);exit();
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) <= 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay registro de eventos promocionales.";
			$result["data"] = count($list);
			echo json_encode($result);exit();
		} else {
			$result["http_code"] = 200;
			$result["status"] = "Datos mostrados correctamente.";
			$result["data"] = $list;
		}
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_clientes_evento_at") {
	$evento_id = $_POST["evento_id"];

	$query = "
		SELECT
			IF(c.tipo_doc = 0, 'DNI', 'CE/PTP') tipo_doc,
			IFNULL(c.num_doc, '') num_doc,
			CONCAT(IFNULL(c.nombre, ''),' ',IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '')) nombre_completo,
			( SELECT IF(COUNT(ct.id)>0, 'SI', 'NO')
				FROM wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion ct
				WHERE evento_dineroat_id = $evento_id
				AND ct.cliente_id = c.id
                AND ct.estado != 0 
				AND tipo_id IN (4,5) ) uso
		FROM tbl_televentas_dinero_at_eventos_clientes ec
			INNER JOIN tbl_televentas_dinero_at_eventos e ON ec.dinero_at_evento_id = e.id
			INNER JOIN tbl_televentas_clientes c ON ec.cliente_id = c.id
		WHERE dinero_at_evento_id = $evento_id
		ORDER BY ec.id DESC;
	";
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["query_error"] = $mysqli->error;
		$result["query"] = $query;
		echo json_encode($result);exit();
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) <= 0) {
			$result["http_code"] = 400;
			$result["status"] = "No se encuentran registrados clientes en esta promoción.";
			$result["data"] = count($list);
			echo json_encode($result);exit();
		} else {
			$result["http_code"] = 200;
			$result["status"] = "Mostrando clientes registrados en la promoción.";
			$result["data"] = $list;
		}
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_evento_promocional_dinero_at") {
	include("function_replace_invalid_caracters.php");
	date_default_timezone_set('America/Lima');
	$fecha_actual = date('Y-m-d');
	$fecha_hora = date('Y-m-d H:i:s');
	$usuario_id = $login ? $login['id'] : null;

	$nombre 		 = $_POST["nombre"];
	$codigo       	 = replace_invalid_caracters($_POST["codigo"]);
	$descripcion     = $_POST["descripcion"];
	$fecha_inicio    = $_POST["fecha_inicio"];
	$fecha_fin       = $_POST["fecha_fin"];
	$tipo_monto 	 = $_POST["tipo_monto"];
	$monto_cliente 	 = $_POST["monto_cliente"];
	$monto_minimo    = $_POST["monto_minimo"];
	$tipo_conversion = $_POST["tipo_conversion"];
	$conversion_max  = $_POST["conversion_max"];
	$clientes_limite = $_POST["clientes_limite"];
	$check_virtuales = $_POST["check_virtuales"];
	$check_bingo     = $_POST["check_bingo"];
	$check_sportbook = $_POST["check_sportbook"];
	$rollover        = $_POST["rollover"];

	if ( !(strlen($nombre) > 0) ){
		$result["http_code"] = 400;
		$result["status"] = "Ingresar el nombre.";
		echo json_encode($result);exit();
	}
	if ( !(strlen($codigo) > 0) ){
		$result["http_code"] = 400;
		$result["status"] = "Ingresar el código.";
		echo json_encode($result);exit();
	}
	if ( strlen($fecha_inicio) !== 10 ){
		$result["http_code"] = 400;
		$result["status"] = "Ingresar una fecha inicio válida.";
		echo json_encode($result);exit();
	}
	if ( strlen($fecha_fin) !== 10 ){
		$result["http_code"] = 400;
		$result["status"] = "Ingresar una fecha fin válida.";
		echo json_encode($result);exit();
	}
	if ( $fecha_inicio < $fecha_actual ){
		$result["http_code"] = 400;
		$result["status"] = "La fecha inicio debe ser como mínimo la fecha de hoy.";
		echo json_encode($result);exit();
	}
	if ( !((float)$monto_cliente > 0) ) {
		$result["http_code"] = 400;
		$result["status"] = "Monto incorrecto.";
		$result["monto_cliente"] = ((float) $monto_cliente > 0) ? true : false;
		echo json_encode($result);exit();
	}
	if ( !((float)$conversion_max > 0) ) {
		$result["http_code"] = 400;
		$result["status"] = "Monto de conversion maxima, incorrecto.";
		$result["conversion_max"] = ((float) $conversion_max > 0) ? true : false;
		echo json_encode($result);exit();
	}
	if ( !((int)$clientes_limite > 0) ) {
		$result["http_code"] = 400;
		$result["status"] = "Ingrese un límite de clientes válido.";
		echo json_encode($result);exit();
	}
	/* if ( $check_virtuales === false && $check_bingo === false && $check_sportbook === false  ){
		$result["status"] = $check_virtuales. " " .$check_bingo. " " .$check_sportbook;
		$result["http_code"] = 400;
		echo json_encode($result);exit();
	} */
	
	if ( empty($monto_minimo) ) {
		$monto_minimo = 'NULL';
	}

	$query_1 = "
		SELECT codigo_evento
		FROM tbl_televentas_dinero_at_eventos 
		WHERE codigo_evento = '" .$codigo. "' AND estado = 1
		LIMIT 1
		";
	$list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
		$result["query_1"] = $query_1;
		echo json_encode($result);exit();
	} else {
		while ($li_1 = $list_query_1->fetch_assoc()) {
			$list_1[] = $li_1;
		}
		if (count($list_1) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "Ya existe un evento promocional con el mismo código, por favor cambiar el código.";
			echo json_encode($result);exit();
		} else {
			$query_insert = "
				INSERT INTO tbl_televentas_dinero_at_eventos (
					nombre,
					descripcion,
					codigo_evento,
					estado,
					fecha_inicio,
					fecha_fin,
					tipo_monto,
					monto_cliente,
					porcentaje_monto_minimo,
					limite_clientes,
					tipo_conversion,
					conversion_maxima,
					juegos_virtuales_activo,
					bingo_activo,
					sportbook_activo,
					rollover,
					user_id,
					updated_user_id,
					created_at,
					updated_at
				) VALUES (
					'" .$nombre. "',
					'" .$descripcion. "',
					'" .$codigo. "',
					1,
					'" .$fecha_inicio. "',
					'" .$fecha_fin. "',
					$tipo_monto,
					$monto_cliente,
					$monto_minimo,
					$clientes_limite,
					$tipo_conversion,
					$conversion_max,
					$check_virtuales,
					$check_bingo,
					$check_sportbook,
					$rollover,
					$usuario_id,
					$usuario_id,
					'" .$fecha_hora. "',
					'" .$fecha_hora. "'
				)
			";
			$mysqli->query($query_insert);
			if ($mysqli->error) {
				$result["http_code"] = 400;
				$result["query_insert"] = $query_insert;
				$result["query_insert_error"] = $mysqli->error;
				echo json_encode($result);exit();
			} else {
				$result["http_code"] = 200;
				$result["status"] = "Datos registrados correctamente.";
			}
		}
	}

} 

if (isset($_POST["accion"]) && $_POST["accion"] === "evento_dineroAT_mostrar") {

	$evento_id = (int)$_POST["evento_id"];

	$query = "
		SELECT 
			id id_evento,
			nombre,
			descripcion,
			DATE(fecha_inicio) fecha_inicio,
			DATE(fecha_fin) fecha_fin,
			limite_clientes
		FROM
			tbl_televentas_dinero_at_eventos
		WHERE
			estado = 1
			AND id = $evento_id
	";
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["query"] = $query;
		$result["query_error"] = $mysqli->error;
		echo json_encode($result);exit();
	}
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if (count($list) === 0) {
		$result["http_code"] = 400;
		$result["status"] = "No existe registro del evento promocional, vuelva a cargar la página.";
		$result["data"] = count($list);
		echo json_encode($result);exit();
	} else {
		$result["http_code"] = 200;
		$result["status"] = "Se muestran los datos a editar.";
		$result["data"] = $list;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_cambios_evento_dineroAT") {
	include("function_replace_invalid_caracters.php");
	date_default_timezone_set('America/Lima');
	$fecha_actual = date('Y-m-d');
	$fecha_hora = date('Y-m-d H:i:s');
	$usuario_id = $login ? $login['id'] : 0;

	if ( !((int)$usuario_id > 0) ) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}

	$evento_id 		 = (int)$_POST["evento_id"];
	$nombre 		 = $_POST["nombre"];
	$descripcion     = $_POST["descripcion"];
	$fecha_inicio    = $_POST["fecha_inicio"];
	$fecha_fin       = $_POST["fecha_fin"];
	$clientes_limite = $_POST["clientes_limite"];

	if ( !(strlen($nombre) > 0) ){
		$result["http_code"] = 400;
		$result["status"] = "Ingresar el nombre.";
		echo json_encode($result);exit();
	}

	if ( strlen($fecha_inicio) !== 10 ){
		$result["http_code"] = 400;
		$result["status"] = "Ingresar una fecha inicio válida.";
		echo json_encode($result);exit();
	}
	if ( strlen($fecha_fin) !== 10 ){
		$result["http_code"] = 400;
		$result["status"] = "Ingresar una fecha fin válida.";
		echo json_encode($result);exit();
	}
	// Declarar variables de tiempo, para comparar 
	$timestamp_inicio = strtotime($fecha_inicio);
	$timestamp_fin = strtotime($fecha_fin);
	$timestamp_actual = strtotime($fecha_actual);
	if ( $timestamp_fin < $timestamp_actual ){
		$result["http_code"] = 400;
		$result["status"] = "La fecha fin debe ser como mínimo la fecha de hoy.";
		echo json_encode($result);exit();
	}




	if ( !((int)$clientes_limite > 0) ) {
		$result["http_code"] = 400;
		$result["status"] = "Ingrese un límite de clientes válido.";
		echo json_encode($result);exit();
	}

	$query_cant_clientesEvento = "
		SELECT COUNT(ec.cliente_id) cant_clientes, DATE(e.fecha_inicio) fecha_inicio
		FROM tbl_televentas_dinero_at_eventos_clientes ec
		INNER JOIN tbl_televentas_dinero_at_eventos e ON ec.dinero_at_evento_id = e.id
		WHERE ec.dinero_at_evento_id = $evento_id AND e.estado = 1;
	";
	$list_query = $mysqli->query($query_cant_clientesEvento);
	if ($mysqli->error) {
		$result["query"] = $query_cant_clientesEvento;
		$result["query_error"] = $mysqli->error;
		echo json_encode($result);exit();
	}
	while ($li = $list_query->fetch_assoc()) {
		$cant_clientesEvento = $li["cant_clientes"];
		$fecha_inicio_eventoActual = $li["fecha_inicio"];
	}
	if ( isset($cant_clientesEvento) ) {
		if( $cant_clientesEvento > $clientes_limite ){
			$result["http_code"] = 400;
			$result["status"] = "La cantidad límite de clientes no puede ser menor a la cantidad de clientes que ya se tienen importados en la promoción.";
			echo json_encode($result);exit();
		}
		/* if(empty($fecha_inicio_eventoActual)){
			$result["http_code"] = 400;
			$result["status"] = "No se pudo actualizar, la promoción a sido eliminada por otro usuario.";
			echo json_encode($result);exit();
		} */
		$timestamp_fecha_inicio_eventoActual = strtotime($fecha_inicio_eventoActual);
		if ( $timestamp_fecha_inicio_eventoActual <= $timestamp_actual ) {
			// if ( $timestamp_inicio != $timestamp_fecha_inicio_eventoActual ) {
			if ( $timestamp_inicio != $timestamp_fecha_inicio_eventoActual ) {
				$result["http_code"] = 400;
				$result["status"] = "La fecha de inicio no puede ser modificada, por que la promoción ya entró en vigencia.";
				echo json_encode($result);exit();
			}
		}


		// Modificar que las fechas no interfieran al CRON y los balances modificados















		$query_clients_events = "
			SELECT ec.cliente_id cliente_id
			FROM tbl_televentas_dinero_at_eventos_clientes ec
			INNER JOIN tbl_televentas_dinero_at_eventos e ON ec.dinero_at_evento_id = e.id
			WHERE e.estado = 1 AND ec.dinero_at_evento_id = $evento_id
		";
		$list_clients_events = $mysqli->query($query_clients_events);
		$clientes_evento_actual = array();
		if ($mysqli->error) {
			$result["query"] = $query_clients_events;
			$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		}
		while ($li = $list_clients_events->fetch_assoc()) {
			array_push($clientes_evento_actual, $li["cliente_id"]);
		}

		if ( count($clientes_evento_actual) >= 1 ){
		$clientes_evento_actual_string = implode(',', $clientes_evento_actual);
	
			// Realizar un consulta si los clientes de este evento pertenecen a otro evento promocional activo, si es asi == ERROR
			$query_cruce_clientes = "
				SELECT
					ec.cliente_id,
					tc.num_doc
				FROM wwwapuestatotal_gestion.tbl_televentas_dinero_at_eventos e
					INNER JOIN wwwapuestatotal_gestion.tbl_televentas_dinero_at_eventos_clientes ec ON e.id = ec.dinero_at_evento_id
					INNER JOIN tbl_televentas_clientes tc ON ec.cliente_id = tc.id
				WHERE 
					e.estado = 1
					AND e.id != $evento_id 
					AND ( DATE(e.fecha_inicio) BETWEEN '$fecha_inicio' AND '$fecha_fin'
						  OR DATE(e.fecha_fin) BETWEEN '$fecha_inicio' AND '$fecha_fin'
						  OR ( DATE(e.fecha_inicio) <= '$fecha_inicio' AND DATE(e.fecha_fin) >= '$fecha_fin' )
						)
					AND ec.cliente_id IN ($clientes_evento_actual_string) 
				GROUP BY ec.cliente_id
			";
			$list_cruce_clientes = $mysqli->query($query_cruce_clientes);
			if ($mysqli->error) {
				$result["query"] = $query_cruce_clientes;
				$result["query_error"] = $mysqli->error;
				echo json_encode($result);exit();
			} else {
				$lista_clientes = array();
				$clientes_no_aptos = array();
				while ($li = $list_cruce_clientes->fetch_assoc()) {
					$lista_clientes[] = $li;
					array_push($clientes_no_aptos, $li["num_doc"]);
				}
				$clientes_no_aptos_string = implode(',', $clientes_no_aptos);
				if ( count($lista_clientes) > 0 ){
					$result["http_code"] = 400;
					$result["status"] = "No se puede actualizar las fechas, los clientes ($clientes_no_aptos_string) pertenecen a otras promociones. ";
					$result["clientes_evento_actual"] = $clientes_evento_actual_string;
					echo json_encode($result);exit();
				}
			}
		}

		$update = "
			UPDATE
				tbl_televentas_dinero_at_eventos
			SET 
				fecha_inicio    = '" . $fecha_inicio . "',
				fecha_fin       = '" . $fecha_fin . "',
				descripcion     = '" . $descripcion . "',
				limite_clientes = '" . $clientes_limite . "',
				updated_at      = '" . $fecha_hora . "',
				updated_user_id = '" . $usuario_id . "'
			WHERE
				id = $evento_id
				AND estado = 1
		";
		$mysqli->query($update);
		$num_rows_affected = (int)$mysqli->affected_rows;
		if ($mysqli->error) {
			$result["query"] = $update;
			$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		}
		if ( $num_rows_affected === 0 ){
			$result["http"] = 400;
			$result["status"] = "Por favor actualizar (F5), la promoción a sido eliminada por otro usuario.";
			echo json_encode($result);exit();
		}
		$result["http_code"] = 200;
		$result["status"] = "Se actualizó la promoción de manera exitosa.";
		
	}

} 

if (isset($_POST["accion"]) && $_POST["accion"]==="dineroAT_exportar_clientes_registrados") {
	global $mysqli;

	$evento_id  = $_POST["evento_id"];

	$query = "
		SELECT
			IF(c.tipo_doc = 0, 'DNI', 'CE/PTP') tipo_doc,
			IFNULL(c.num_doc, '') num_doc,
			CONCAT(IFNULL(c.nombre, ''),' ',IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '')) nombre_completo,
			( SELECT IF(COUNT(ct.id)>0, 'SI', 'NO')
				FROM wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion ct
				WHERE evento_dineroat_id = $evento_id
				AND ct.cliente_id = c.id
                AND ct.estado != 0 
				AND tipo_id IN (4,5) ) uso
		FROM tbl_televentas_dinero_at_eventos_clientes ec
			INNER JOIN tbl_televentas_dinero_at_eventos e ON ec.dinero_at_evento_id = e.id
			INNER JOIN tbl_televentas_clientes c ON ec.cliente_id = c.id
		WHERE dinero_at_evento_id = $evento_id
		ORDER BY ec.id DESC;
	";
	$list_query = $mysqli->query($query);
	$result_data = array();
	if ($mysqli->error) {
		// $result["query_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$headers = [
			"tipo_doc" => "Tipo de documento",
			"num_doc" => "Nro de documento",
			"nombre_completo" => "Nombre",
			"uso" => "Usó"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "clientes_promocion_bonoAT" . $date->getTimestamp();

		if (!file_exists('/var/www/html/export/files_exported/reporte_premios/')) {
			mkdir('/var/www/html/export/files_exported/reporte_premios/', 0777, true);
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$excel_path = '/var/www/html/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$excel_path_download = '/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$url = $file_title . '.xls';

		try {
			$objWriter->save($excel_path);
		} catch (PHPExcel_Writer_Exception $e) {
			echo json_encode(["error" => $e]);
			exit;
		}

		$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
		$insert_cmd .= " VALUES ('" . $url . "','excel','xls','" . filesize($excel_path) . "','" . date("Y-m-d h:i:s") . "','" . $login["id"] . "')";
		$mysqli->query($insert_cmd);

		echo json_encode(array(
			"path" => $excel_path_download,
			"url" => $file_title . '.xls',
			"tipo" => "excel",
			"ext" => "xls",
			"size" => filesize($excel_path),
			"fecha_registro" => date("d-m-Y h:i:s"),
			"sql" => $insert_cmd
		));
		exit;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_evento_dinero_at") {

	date_default_timezone_set('America/Lima');
	$evento_id = $_POST["evento_id"];
	$usuario_id = $login ? $login['id'] : null;
	$date_time = date('Y-m-d H:i:s');

	if ((int) $usuario_id > 0) {

		// Consultar si existen clientes que ya usaron algo de su dinero promocional
		$query="
			SELECT id
			FROM wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion
			WHERE evento_dineroat_id = $evento_id
				AND estado != 0
				AND tipo_id IN (4,5)
		";
		$list_trans = $mysqli->query($query);
		if ($mysqli->error) {
			$result["query"] = $query;
			$result["query_error"] = $mysqli->error;
		}else{
			$list_transacciones = array();
			while ($li = $list_trans->fetch_assoc()) {
				$list_transacciones[] = $li;
			}
			if ( count($list_transacciones) > 0 ){
				$result["http_code"] = 400;
				$result["status"] = "No se puede eliminar la promoción, existen clientes que ya hicieron uso del monto asignado.";
				echo json_encode($result);exit();
			} 
		}

		$command = " 
			UPDATE
				tbl_televentas_dinero_at_eventos
			SET 
				estado = 0,
				updated_at = '" . $date_time . "',
				updated_user_id = '" . $usuario_id . "'
			WHERE
				id = $evento_id
				AND estado = 1
		";
		$mysqli->query($command);
		if ($mysqli->error) {
			$result["query"] = $command;
			$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		} else {

			$clientes_promo_a_eliminar = "
				SELECT 
					ec.cliente_id
				FROM tbl_televentas_dinero_at_eventos_clientes ec
					INNER JOIN tbl_televentas_dinero_at_eventos e ON ec.dinero_at_evento_id = e.id
				WHERE e.id = $evento_id
			";
			$list_clientes = $mysqli->query($clientes_promo_a_eliminar);
			if ($mysqli->error) {
				$result["query_clientes_promo_a_eliminar"] = $clientes_promo_a_eliminar;
				$result["query_error_clientes_promo_a_eliminar"] = $mysqli->error;
				echo json_encode($result);exit();
			} else {
				while ($li = $list_clientes->fetch_assoc()) {
					$cliente_id = $li["cliente_id"];

					$respu = sec_tlv_man_etiq_query_tbl_televentas_clientes_transaccion(18, $cliente_id, 0, 0, 6, $evento_id, $usuario_id);

					if ( (int)$respu["http_code"]===400){
						$result["http_code"] = 400;
						$result["status"] = "Error al obtener el id de la clientes_transaccion_id.";
						echo json_encode($result);exit();
					} else if ( (int)$respu["http_code"]===200) {
						$transaccion_id = $respu["id"];
					}

					sec_tlv_man_etiq_query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, $cliente_id, 6, 0, 0, 0);

					sec_tlv_man_etiq_query_tbl_televentas_clientes_balance('update', $cliente_id, 6, 0);

					$etiquetas_del = "
						UPDATE tbl_televentas_clientes_etiqueta
						SET
							status = 0,
							updated_user_id = '" . $usuario_id . "',
							updated_at = '" . $date_time . "'
						WHERE client_id = $cliente_id 
							AND etiqueta_id = 43
							AND status = 1
					";
					$mysqli->query($etiquetas_del);
					if ($mysqli->error) {
						$result["query-etiquetas_del"] = $etiquetas_del;
						$result["query_error-etiquetas_del"] = $mysqli->error;
						echo json_encode($result);exit();
					}
				}
			}

			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = "Promoción Eliminada";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "importar_archivo_dinero_at") {
	
	if ( count($_FILES["archivo"]) < 0 ){
		$result["http_code"] = 400;
		$result["status"] = "El archivo no tiene data.";
		echo json_encode($result);exit();
	}
	
	date_default_timezone_set("America/Lima");
	$fecha_actual = date('Y-m-d');
	$usuario_id = $login?$login['id']:0;
	$evento_id = (int)$_POST["evento_id"];
	$archivo = $_FILES["archivo"]["tmp_name"];

	if( (int)$usuario_id === 0 ){
		$result["http_code"] = 400;
		$result["status"] ="Sesión perdida, actualice la página.";
		echo json_encode($result);exit();
	}

	$archivo_excel = PHPExcel_IOFactory::load($archivo);
	$hoja = $archivo_excel->getActiveSheet();

	// Obtener el número de filas en la hoja de cálculo
	$cant_filas = $hoja->getHighestRow();

	$num_filas_validas_a = 0;
	for ($fila = 1; $fila <= $cant_filas; $fila++) {
		$valor_celda_a = trim($hoja->getCell('A' . $fila)->getValue());
		if ($valor_celda_a !== '') {
			$num_filas_validas_a++;
		}
		if( $valor_celda_a == '' ){
			$result["http_code"] = 400;
			$result["status"] = "La celda A " .$fila. " se encuentra vacía.";
			echo json_encode($result);exit();
		}
	}
	if ($num_filas_validas_a <= 1){
		$result["http_code"] = 400;
		$result["status"] = "La plantilla se encuentra vacía.";
		echo json_encode($result);exit();
	}
	$rows = $hoja->toArray();
	array_shift($rows);

	$select = "
		SELECT
			limite_clientes,
			IFNULL(rollover, 0) rollover,
			tipo_monto,
			monto_cliente,
			IFNULL(porcentaje_monto_minimo, 0) monto_minimo,
			DATE(fecha_inicio) fecha_inicio,
			DATE(fecha_fin) fecha_fin,
			(SELECT COUNT(dinero_at_evento_id) FROM tbl_televentas_dinero_at_eventos_clientes WHERE dinero_at_evento_id = $evento_id) cont_clientes
		FROM
			tbl_televentas_dinero_at_eventos
		WHERE
			estado = 1 AND id = $evento_id
		LIMIT 1
	";
	$list_select = $mysqli->query($select);
	if ($mysqli->error) {
		$result["query"] = $select;
		$result["query_error"] = $mysqli->error;
		echo json_encode($result);exit();
	} else {
		while ($li = $list_select->fetch_assoc()) {
			$lim_clientes = $li["limite_clientes"];
			$cont_clientes = $li["cont_clientes"];
			$tipo_monto = $li["tipo_monto"];
			$rollover = $li["rollover"];
			$monto_cliente = $li["monto_cliente"];
			$monto_minimo = $li["monto_minimo"];
			$fecha_inicio_evento_actual = $li["fecha_inicio"];
			$fecha_fin_evento_actual = $li["fecha_fin"];
		}
	}

	// Validar que no se permitan importar mas de los clientes permitidos
	$clientes_permitidos = $lim_clientes - $cont_clientes;
	if ( $clientes_permitidos === 0 ){
		$result["http_code"] = 400;
		$result["status"] = "Esta promoción ya no permite registrar mas clientes.";
		echo json_encode($result);exit();
	}

	// Recorrer las filas del archivo Excel
	$clientes_array = array();
	$clientes_no_registrados = array();
	$cont_clientes_importados = 0;
	$cont_clientes_no_registrados = 0;
	foreach ($rows as $row) {
		$nro_documento = trim($row[0]);
		$cliente_id = '';
		$estado = 1;
		$rollover_monto = 'NULL';

		if ( $cont_clientes_importados >= $clientes_permitidos ){
			$clientes_no_registrados[$cont_clientes_no_registrados]["nro_doc"] = $nro_documento;
			$clientes_no_registrados[$cont_clientes_no_registrados]["motivo"] = "Excede límite de clientes por promoción";
			$cont_clientes_no_registrados++;
			continue;
		}

		// Consultar el ID del cliente
		$query = "
			SELECT id
			FROM tbl_televentas_clientes
			WHERE num_doc = '$nro_documento'
			LIMIT 1
		";
		$list_query = $mysqli->query($query);
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Error al consultar el id del cliente.";
			$result["query"] = $query;
			$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		}
		$list_cliente = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_cliente[] = $li;
		}
		if ( count($list_cliente) === 1 ){// Si hay ID del cliente (REGISTRADO)
			$cliente_id = $list_cliente[0]["id"];

			$consultar_clientes_evento_activo = "
				SELECT
					ec.cliente_id,
					tc.num_doc
				FROM wwwapuestatotal_gestion.tbl_televentas_dinero_at_eventos e
					INNER JOIN wwwapuestatotal_gestion.tbl_televentas_dinero_at_eventos_clientes ec ON e.id = ec.dinero_at_evento_id
					INNER JOIN tbl_televentas_clientes tc ON ec.cliente_id = tc.id
				WHERE 
					(DATE(e.fecha_inicio) BETWEEN '$fecha_inicio_evento_actual' AND '$fecha_fin_evento_actual' OR
					DATE(e.fecha_fin) BETWEEN '$fecha_inicio_evento_actual' AND '$fecha_fin_evento_actual' OR
					(DATE(e.fecha_inicio) <= '$fecha_inicio_evento_actual' AND DATE(e.fecha_fin) >= '$fecha_fin_evento_actual'))
					AND e.estado = 1
					AND ec.cliente_id = $cliente_id
				LIMIT 1
			";
			$list_consultar_clientes_evento_activo = $mysqli->query($consultar_clientes_evento_activo);
			if ($mysqli->error) {
				$result["query"] = $consultar_clientes_evento_activo;
				$result["query_error"] = $mysqli->error;
				echo json_encode($result);exit();
			}
			$list_cliente_no_reg = array();
			while ($li = $list_consultar_clientes_evento_activo->fetch_assoc()) {
				$list_cliente_no_reg[] = $li;
				$clientes_no_registrados[$cont_clientes_no_registrados]["nro_doc"] = $li["num_doc"];
				$clientes_no_registrados[$cont_clientes_no_registrados]["motivo"] = "Cliente pertenece a una promoción vigente.";
				$cont_clientes_no_registrados++;
			}
			if ( count($list_cliente_no_reg) === 1 ){
				continue;
			}

			// Sumamos el balance depende si es por porcentaje o monto normal
			if( (int)$tipo_monto === 2 ){
				$query_monto_max = "
					SELECT 
						IFNULL(MAX(monto_deposito), 0) recarga_mayor
					FROM wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion 
					WHERE tipo_id = 26
						AND cliente_id = $cliente_id
						AND estado = 1
						AND monto_deposito >= $monto_minimo
						AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
						AND created_at <= NOW();
				";
				$list_monto_max = $mysqli->query($query_monto_max);
				if ($mysqli->error) {
					$result["query"] = $query_monto_max;
					$result["query_error"] = $mysqli->error;
					echo json_encode($result);exit();
				}
				while ($li = $list_monto_max->fetch_assoc()) {
					$recarga_mayor = (float)$li["recarga_mayor"];
				}
				if ( $recarga_mayor > 0 ){
					$monto_cliente = ($recarga_mayor * $monto_cliente) / 100;
				} else if ( $recarga_mayor == 0 ) {
					$monto_cliente = $recarga_mayor;
					$estado = 0;
					/* $clientes_no_registrados[$cont_clientes_no_registrados]["nro_doc"] = $nro_documento;
					$clientes_no_registrados[$cont_clientes_no_registrados]["motivo"] = "No cuenta con recargas en el periodo de tiempo estimado.";
					$cont_clientes_no_registrados++;
					continue; */
				}
			}

			if ( (int)$rollover > 0 ) {
				$rollover_monto = $monto_cliente*$rollover;
			}

			array_push($clientes_array, $cliente_id);

			$sql = "
				INSERT INTO tbl_televentas_dinero_at_eventos_clientes (
					cliente_id,
					dinero_at_evento_id,
					monto,
					estado,
					rollover_monto,
					user_id,
					created_at
				) VALUES (
					$cliente_id,
					$evento_id,
					$monto_cliente,
					$estado,
					$rollover_monto,
					$usuario_id,
					NOW()
				)
			";
			$mysqli->query($sql);
			if ($mysqli->error) {
				$result["query"] = $sql;
				$result["query_error"] = $mysqli->error;
				echo json_encode($result);exit();
			}

			// Solo se asignará el balance y la etiqueta si la fecha actual de el evento comienza es mayor o igual a la fecha de inicio del evento, sino entrará por el CRON

			$timestamp_actual = strtotime($fecha_actual);
			$timestamp_inicio_evento_actual = strtotime($fecha_inicio_evento_actual);
			if ( $timestamp_actual >= $timestamp_inicio_evento_actual) { 

				if ( $monto_cliente == 0 ) {
					$cont_clientes_importados++;
					continue;
				}

				$query_consultar_etiqueta = "
					SELECT 
						ce.id
					FROM tbl_televentas_clientes_etiqueta ce
					WHERE 
						ce.client_id = '" . $cliente_id . "' 
						AND ce.etiqueta_id = 43
						AND ce.status = 1 
				";
				$list_query_ce = $mysqli->query($query_consultar_etiqueta);
				if ($mysqli->error) {
					$result["query"] = $query_consultar_etiqueta;
					$result["query_error"] = $mysqli->error;
					echo json_encode($result);exit();
				}
				$list_ce = array();
				while ($li_ce = $list_query_ce->fetch_assoc()) {
					$list_ce[] = $li_ce;
				}
				if (count($list_ce) === 0) { // Si no tiene la etiqueta, se le inserta
					$insert_etiqueta = "
						INSERT INTO tbl_televentas_clientes_etiqueta
						(
							client_id,
							etiqueta_id,
							status,
							created_user_id,
							created_at
						) VALUES (
							'" . $cliente_id . "',
							43,
							'1',
							'" . $usuario_id . "',
							NOW()
						)
					";
					$mysqli->query($insert_etiqueta);
					if ($mysqli->error) {
						$result["query"] = $insert_etiqueta;
						$result["query_error"] = $mysqli->error;
						echo json_encode($result);exit();
					}
				}

				// Consultamos el balance dinero_at
				$list_balances = sec_tlv_man_etiq_obtener_balances($cliente_id);
				$balance = $list_balances[0]["balance"];
				$balance_dinero_at = $list_balances[0]["balance_dinero_at"];
				$nuevo_balance_dinero_at = $balance_dinero_at + $monto_cliente;

				$respu = sec_tlv_man_etiq_query_tbl_televentas_clientes_transaccion(17, $cliente_id, $monto_cliente, $nuevo_balance_dinero_at, 6, $evento_id, $usuario_id);

				if ( (int)$respu["http_code"]===400){
					$result["http_code"] = 400;
					$result["status"] = "Error al obtener el id de la clientes_transaccion_id.";
					echo json_encode($result);exit();
				} else if ( (int)$respu["http_code"]===200) {
					$transaccion_id = $respu["id"];
				}

				sec_tlv_man_etiq_query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, $cliente_id, 6, $balance_dinero_at, $monto_cliente, $nuevo_balance_dinero_at);

				sec_tlv_man_etiq_query_tbl_televentas_clientes_balance('update', $cliente_id, 6, $nuevo_balance_dinero_at);
				$result["balance_aumentado"] = "Se aumentó el balance hoy, porque la promo comienza HOY.";
			}else{
				$result["balance_aumentado"] = "No se aumentó el balance hoy, porque la promo comienza el día '".$fecha_inicio_evento_actual."'.";
			}
			$cont_clientes_importados++;
		} else {
			$clientes_no_registrados[$cont_clientes_no_registrados]["nro_doc"] = $nro_documento;
			$clientes_no_registrados[$cont_clientes_no_registrados]["motivo"] = "No es cliente.";
			$cont_clientes_no_registrados++;
			continue;
		}
	}

	$result["http_code"] = 200;
	$result["status"] = "Ok";
	$result["clientes_registrados_total"] = $clientes_array;
	$result["clientes_no_registrados"] = $clientes_no_registrados;
	$result["cont_clientes_importados"] = $cont_clientes_importados;
	$result["cont_clientes_no_importados"] = $cont_clientes_no_registrados;

}


//*******************************************************************************************************************
// ETIQUETAS MASIVAS
//*******************************************************************************************************************

if (isset($_GET["accion"]) && $_GET["accion"]=== "SecManEtiTlv_modal_lista_etiq") {
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$query ="
		SELECT
			e.id as cod_etiq, 		
			IFNULL(e.label, '') label,
			IFNULL(e.description, '') description 
		FROM
		tbl_televentas_etiqueta e 
		WHERE e.status = 1
		 
		HAVING 
			label LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
		LIMIT 10
		";
	//$result["consulta_query"] = $query;
	$list_query=$mysqli->query($query);
	$list_registros=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registros[]=$li;
			$temp_array['codigo'] = $li['cod_etiq'];
            $temp_array['value'] = strtoupper('' . $li['label'] . ' - ' . $li['description']);
            $temp_array['label'] = $temp_array['value'];
            array_push($result, $temp_array);
		}
	}

	if(count($list_registros)===0){
		$result["http_code"] = 204;
		//$result["status"] = "No hay registros.";
		$result["result"] = $list_registros;
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	} elseif(count($list_registros)>0){
		$result["http_code"] = 200;
		$result["result"] = $list_registros;
	} else {
		$result["http_code"] = 400;
		//$result["status"] ="Ocurrió un error al consultar.";
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	}
}


if (isset($_GET["accion"]) && $_GET["accion"]=== "SecManEtiTlv_modal_lista_etiq_cli") {
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$query ="
		SELECT
			c.id as cod_cli, 		
			IFNULL(c.num_doc, '') num_doc,
			IFNULL(c.telefono, '') telefono,
			IFNULL(c.web_id, '') web_id,
			IFNULL(c.player_id, '') player_id,
			IFNULL(c.web_full_name, '') web_full_name,
			CONCAT(IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '')) AS cliente
		FROM
			tbl_televentas_clientes c
		 
		HAVING 
			cliente LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
			OR num_doc LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
			LIMIT 10
		";
	//$result["consulta_query"] = $query;
	$list_query=$mysqli->query($query);
	$list_registros=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registros[]=$li;
			$temp_array['codigo'] = $li['cod_cli'];
            $temp_array['value'] = strtoupper('' . $li['num_doc'] . ' - ' . $li['cliente']);
            $temp_array['label'] = $temp_array['value'];
            array_push($result, $temp_array);
		}
	}

	if(count($list_registros)===0){
		$result["http_code"] = 204;
		//$result["status"] = "No hay registros.";
		$result["result"] = $list_registros;
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	} elseif(count($list_registros)>0){
		$result["http_code"] = 200;
		$result["result"] = $list_registros;
	} else {
		$result["http_code"] = 400;
		//$result["status"] ="Ocurrió un error al consultar.";
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]=== "guardar_temp_clientes_etiquetas") {

	$etiqueta = $_POST["etiqueta"]; 
	$cliente = $_POST["cliente"]; 
	$usuario_id = $login ? $login['id'] : null;

	$query ="SELECT id
			FROM tbl_televentas_clientes_etiqueta
			WHERE 
			client_id = '".$cliente . "'
			AND etiqueta_id = '".$etiqueta."'
			AND status = 1";

	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
	}

	if (count($list) == 0) {

		$query_list_temp = "
			SELECT
				(SELECT label FROM tbl_televentas_etiqueta WHERE id= '".$etiqueta. "' ) as label,
				CONCAT(IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '')) AS cliente 
			FROM  tbl_televentas_clientes c
			WHERE  c.id = '".$cliente. "'
			ORDER BY c.id ASC
		";

		$list_query_temp = $mysqli->query($query_list_temp);
		$list_temp = array();
		if ($mysqli->error) {
			$result["query_list_temp_error"] = $mysqli->error;
		} else {
			while ($li_temp = $list_query_temp->fetch_assoc()) {
					$list_temp[] = $li_temp;
			}
		}
			
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_temp;
	
	}else{
		$result["http_code"] = 400;
		$result["status"] = "El cliente ya cuenta con la etiqueta registrada y activa";
		$result["result"] = $list;
	}
 

}


if (isset($_POST["accion"]) && $_POST["accion"]=== "sec_mant_guardar_etiquetas_masivas") {

	$usuario_id = $login ? $login['id'] : null;
	$list_temp_cli = $_POST["array_temp_cli"];
 
	foreach($list_temp_cli as $cliente){
		foreach($cliente as $posicion=>$datos){
			$cliente_id = $cliente["cliente_id"];
			$etiqueta_id = $cliente["etiqueta_id"];	

			$query_list_temp = "SELECT id
			FROM tbl_televentas_clientes_etiqueta 
			WHERE  client_id = '".$cliente_id."' 
			AND etiqueta_id = '".$etiqueta_id."'
			";

			$list_query_temp = $mysqli->query($query_list_temp);
			$list_temp = array();
			if ($mysqli->error) {
				$result["query_busq_temp_error"] = $mysqli->error;
			} else {
				while ($li_temp = $list_query_temp->fetch_assoc()) {
					$list_temp[] = $li_temp;
				}
			}
			
			if (count($list_temp) > 0) {

				$query_update = "
				UPDATE tbl_televentas_clientes_etiqueta
					SET 
						status = 1, 
						updated_user_id = '".$usuario_id."', 
						updated_at = now()
					WHERE client_id = '".$cliente_id."' 
					AND etiqueta_id = '".$etiqueta_id."'
					LIMIT 1
					";
				$mysqli->query($query_update);
				if ($mysqli->error) {
					$result["update_etiq_error"] = $mysqli->error;
				}

			}else{

				$query_insert = "INSERT INTO tbl_televentas_clientes_etiqueta (
						client_id,
						etiqueta_id,
						status,
						created_user_id,
						created_at
						) VALUES (
						$cliente_id,
						$etiqueta_id,
						1,
						'" . $usuario_id . "',						
						now()
					)
					";
					$mysqli->query($query_insert);
					if ($mysqli->error) {
						$result["query_insert_etq_cli"] = $mysqli->error;
					}
				
			}
			 	 
		}

	}
	 
	$result["http_code"] = 200;
	$result["status"] = "Listado registrado";
	$result["result"] = $list_temp_cli;

}


//*******************************************************************************************************************
// FIN ETIQUETAS MASIVAS
//*******************************************************************************************************************




function sec_tlv_man_etiq_get_cliente_por_dni($dni) {
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.apuestatotal.com/v2/dni?dni=" . $dni,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_HTTPHEADER => [
			"Accept: application/json",
			"Authorization: Bearer " . env('TELEVENTAS_API_TOKEN')
		],
	]);
	$response = json_decode(curl_exec($curl), true);
	$err = curl_error($curl);
	curl_close($curl);
	$consulta = ($response["result"] ?? []);
	return $consulta;
}

function sec_tlv_man_etiq_obtener_balances($cliente_id){
	global $mysqli;

	$query_balances = "
		SELECT 
		  c.id,
		  IFNULL(ba1.balance, -99999999) balance, 
		  IFNULL(ba2.balance, -99999999) balance_bono_disponible, 
		  IFNULL(ba3.balance, -99999999) balance_bono_utilizado, 
		  IFNULL(ba4.balance, -99999999) balance_deposito,
		  IFNULL(ba5.balance, -99999999) balance_retiro_disponible,
		  IFNULL(ba6.balance, -99999999) balance_dinero_at
		FROM 
		  tbl_televentas_clientes c 
		  LEFT JOIN tbl_televentas_clientes_balance ba1 ON ba1.cliente_id = c.id AND ba1.tipo_balance_id = 1 
		  LEFT JOIN tbl_televentas_clientes_balance ba2 ON ba2.cliente_id = c.id AND ba2.tipo_balance_id = 2 
		  LEFT JOIN tbl_televentas_clientes_balance ba3 ON ba3.cliente_id = c.id AND ba3.tipo_balance_id = 3 
		  LEFT JOIN tbl_televentas_clientes_balance ba4 ON ba4.cliente_id = c.id AND ba4.tipo_balance_id = 4 
		  LEFT JOIN tbl_televentas_clientes_balance ba5 ON ba5.cliente_id = c.id AND ba5.tipo_balance_id = 5 
		  LEFT JOIN tbl_televentas_clientes_balance ba6 ON ba6.cliente_id = c.id AND ba6.tipo_balance_id = 6 
		WHERE 
		c.id= $cliente_id
		";
	$list_query_balances = $mysqli->query($query_balances);
	$list_balance = array();
	if (!($mysqli->error)) {
		while ($li = $list_query_balances->fetch_assoc()) { $list_balance[] = $li; }
		if(count($list_balance)>0){
			if((float)$list_balance[0]["balance"]<-9999999) {
				sec_tlv_man_etiq_query_tbl_televentas_clientes_balance('insert', $cliente_id, 1, 0);
				$list_balance[0]["balance"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_bono_disponible"]<-9999999) {
				sec_tlv_man_etiq_query_tbl_televentas_clientes_balance('insert', $cliente_id, 2, 0);
				$list_balance[0]["balance_bono_disponible"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_bono_utilizado"]<-9999999) {
				sec_tlv_man_etiq_query_tbl_televentas_clientes_balance('insert', $cliente_id, 3, 0);
				$list_balance[0]["balance_bono_utilizado"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_deposito"]<-9999999) {
				sec_tlv_man_etiq_query_tbl_televentas_clientes_balance('insert', $cliente_id, 4, 0);
				$list_balance[0]["balance_deposito"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_retiro_disponible"]<-9999999) {
				sec_tlv_man_etiq_query_tbl_televentas_clientes_balance('insert', $cliente_id, 5, 0);
				$list_balance[0]["balance_retiro_disponible"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_dinero_at"]<-9999999) {
				sec_tlv_man_etiq_query_tbl_televentas_clientes_balance('insert', $cliente_id, 6, 0);
				$list_balance[0]["balance_dinero_at"] = number_format(0, 2, '.', '');
			}
		}
	}
	return $list_balance;
}

function sec_tlv_man_etiq_query_tbl_televentas_clientes_balance($action, $cliente_id, $tipo_id, $balance){
	global $mysqli;

	if($action==='insert') {
		$query = " 
			INSERT INTO tbl_televentas_clientes_balance (
				cliente_id,
				tipo_balance_id,
				balance,
				created_at,
				updated_at
			) VALUES (
				" . $cliente_id . ",
				" . $tipo_id . ",
				" . $balance . ",
				now(),
				now()
			)";
		$mysqli->query($query);
	}
	if($action==='update') {
		$query = " 
			UPDATE tbl_televentas_clientes_balance 
			SET
				balance = '" . $balance . "',
				updated_at = now()
			WHERE cliente_id = " . $cliente_id . " AND tipo_balance_id = " . $tipo_id . " 
		";
		$mysqli->query($query);
	}
}

function sec_tlv_man_etiq_query_tbl_televentas_clientes_transaccion($tipo_transaccion, $cliente_id, $monto, $nuevo_balance, $id_tipo_balance, $evento_dineroat_id, $usuario_id){
	global $mysqli;

	$query = " 
		INSERT INTO tbl_televentas_clientes_transaccion (
			tipo_id,
			cliente_id,
			monto,
			nuevo_balance,
			estado,
			created_at,
			update_user_at,
			id_tipo_balance,
			evento_dineroat_id,
			user_id
		) VALUES (
			" . $tipo_transaccion . ",
			" . $cliente_id . ",
			" . $monto . ",
			" . $nuevo_balance . ",
			1,
			now(),
			now(),
			'". $id_tipo_balance ."',
			'". $evento_dineroat_id ."',
			'". $usuario_id ."'
		)";
	$mysqli->query($query);

	$select = "
		SELECT id
		FROM tbl_televentas_clientes_transaccion
		WHERE tipo_id = $tipo_transaccion
			AND cliente_id = $cliente_id
			AND monto = $monto
			AND nuevo_balance = $nuevo_balance
			AND estado = 1
			AND id_tipo_balance = $id_tipo_balance
			AND evento_dineroat_id = $evento_dineroat_id
			AND user_id = $usuario_id
		LIMIT 1
	";
	$list_select = $mysqli->query($select);
	$res_ct_id = array();
	if ($mysqli->error) {
		$res_ct_id["http_code"] = 400;
		$res_ct_id["select_clien_trans"] = $select;
		$res_ct_id["select_clien_trans_error"] = $mysqli->error;
		$res_ct_id["status"] = "Error al insertar en la tabla clientes_transaccion";
		echo json_encode($res_ct_id);exit();
	}
	$list_id = array();
	while ($li = $list_select->fetch_assoc()) {
		$list_id[] = $li;
		$id = $li["id"];
	}
	if (count($list_id) === 0){
		$res_ct_id["http_code"] = 400;
		$res_ct_id["status"] = "error al consutar el id en clientes_transaccion.";
	}else{
		$res_ct_id["http_code"] = 200;
		$res_ct_id["id"] = $id;
	}
	return $res_ct_id;

}

function sec_tlv_man_etiq_query_tbl_televentas_clientes_balance_transaccion($action, $transaccion_id, $cliente_id, $tipo_balance_id, $balance_actual, $monto, $balance_nuevo){
	global $mysqli;
	global $login;

	$user_id = $login ? $login['id'] : 0;

	if($action==='insert') {
		$query = "
			INSERT INTO tbl_televentas_clientes_balance_transaccion (
				transaccion_id,
				cliente_id,
				tipo_balance_id,
				balance_actual,
				monto,
				balance_nuevo,
				user_id,
				created_at
			) VALUES (
				'" . $transaccion_id . "',
				'" . $cliente_id . "',
				'" . $tipo_balance_id . "',
				'" . $balance_actual . "',
				'" . $monto . "',
				'" . $balance_nuevo . "',
				'" . $user_id . "',
				now()
			)
		";
		$mysqli->query($query);
	}
}







echo json_encode($result);
