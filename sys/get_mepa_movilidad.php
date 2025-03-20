<?php

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");
require_once("get_mepa_documento_correlativo.php");

function fnc_sec_mepa_movilidad_validar_fechas($fecha_rango_del, $fecha_rango_al, $fechasBd)
{
	$fecha_rango_del = date("Y-m-d", strtotime($fecha_rango_del));
	$fecha_rango_al = date("Y-m-d", strtotime($fecha_rango_al));
	$fechas_array = array();
	while ($fecha_rango_del <= $fecha_rango_al) {
		array_push($fechas_array, $fecha_rango_del);
		$fecha_rango_del = date("Y-m-d", strtotime($fecha_rango_del . "+ 1 days"));
	}
	$fechas_array_bd = array();
	foreach ($fechasBd as $key => $fechas) {
		$fecha_del = $fechas['fecha_del'];
		$fecha_al = $fechas['fecha_al'];
		while ($fecha_del <= $fecha_al) {
			array_push($fechas_array_bd, $fecha_del);
			$fecha_del = date("Y-m-d", strtotime($fecha_del . "+ 1 days"));
		}
	}
	$fecha_repetida = array_intersect($fechas_array, $fechas_array_bd);
	return ($fecha_repetida);
}

// -----------------MOVILIDAD DETALLE --------------//
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_get_data_caja_chica_movilidad") {
	try {
		$cc_movilidad_id = $_POST['cc_movilidad_id'];
		$error = '';
		$querySelect = "
		SELECT mccm.id,
			mccm.num_correlativo,
			mccm.fecha_del,
			mccm.fecha_al,
			IFNULL((SELECT    
			mza.centro_costo
			FROM
			mepa_caja_chica_liquidacion     AS mccl
			LEFT JOIN mepa_asignacion_caja_chica AS macc
				ON  macc.id = mccl.asignacion_id
			LEFT JOIN mepa_zona_asignacion  AS mza
				ON  mza.id = macc.zona_asignacion_id
			WHERE  mccl.id_movilidad = mccm.id LIMIT 1),'S/N') AS cc_id,
			mccm.status,
			mccm.user_created_id,
			u.usuario,
			CONCAT(pa.nombre,' ',pa.apellido_paterno,' ',pa.apellido_materno )as nombre,
			pa.dni,
			mccm.created_at,
			mccm.updated_at
		FROM   mepa_caja_chica_movilidad AS mccm
		LEFT JOIN tbl_usuarios AS u ON u.id = mccm.user_created_id
		LEFT JOIN tbl_personal_apt AS pa ON pa.id = u.personal_id
		WHERE mccm.id = {$cc_movilidad_id}
		";
		$resultQuery = $mysqli->query($querySelect);
		$arrayReturn = new stdClass();
		while ($li = $resultQuery->fetch_assoc()) {
			$arrayReturn->id = $li['id'];
			if ($li['num_correlativo']==''||$li['num_correlativo']==null) {
				$arrayReturn->num_correlativo = 'S/N';
			}else{
				$arrayReturn->num_correlativo = $li['num_correlativo'];
			}		
			$arrayReturn->fecha_del = $li['fecha_del'];
			$arrayReturn->fecha_al = $li['fecha_al'];
			$arrayReturn->cc_id = $li['cc_id'];
			$arrayReturn->status = $li['status'];
			$arrayReturn->user_created_id = (int)$li['user_created_id'];
			$arrayReturn->usuario = $li['usuario'];
			$arrayReturn->dni = $li['dni'];
			$arrayReturn->nombre = $li['nombre'];
			$arrayReturn->created_at = $li['created_at'];
			$arrayReturn->updated_at = $li['updated_at'];
			if(isset($login["id"])){
				$arrayReturn->user_request_id =((int)$login["id"]);
			}
		}

		if ($mysqli->error) {
			$error .= 'Error: ' . $mysqli->error . $query;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Dato obtenido.";
			$result["error"] = false;
			$result["data"] = $arrayReturn;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"] = $e->getMessage();
		$result["error"] = true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_get_data_caja_chica_listar_detalle_movilidad") {
	try {
		$cc_movilidad_id = $_POST['cc_movilidad_id'];
		$error = '';
		$querySelect = "
		SELECT
			mccmd.id,
			mccmd.id_mepa_caja_chica_movilidad,
			mccmd.fecha,
			mccmd.partida_destino,
			mccmd.motivo_traslado,
			mccmd.monto,
			mccmd.estado,
			mccmd.created_at,
			mccmd.updated_at
		FROM
			mepa_caja_chica_movilidad_detalle AS mccmd
		WHERE 
		 mccmd.estado = 1
		AND mccmd.id_mepa_caja_chica_movilidad =  '{$cc_movilidad_id}'
		";
		$resultQuery = $mysqli->query($querySelect);
		$arrayReturn = array();
		while ($li = $resultQuery->fetch_assoc()) {
			$temp = new stdClass();
			$temp->id = $li['id'];
			$temp->id_mepa_caja_chica_movilidad = $li['id_mepa_caja_chica_movilidad'];
			$temp->fecha = $li['fecha'];
			$temp->partida_destino = strtoupper($li['partida_destino']);
			$temp->motivo_traslado = strtoupper($li['motivo_traslado']);
			$temp->monto = $li['monto'];
			$temp->estado = $li['estado'];
			$temp->created_at = $li['created_at'];
			$temp->updated_at = $li['updated_at'];
			array_push($arrayReturn, $temp);
			unset($temp);
		}

		if ($mysqli->error) {
			$error .= 'Error: ' . $mysqli->error . $query;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Datos Listados.";
			$result["error"] = false;
			$result["data"] = $arrayReturn;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"] = $e->getMessage();
		$result["error"] = true;
	}
}
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_get_data_caja_chica_guardar_detalle_movilidad") {
	try {
		$error = '';
		$cc_movilidad_id = $_POST["nameCajaChicaMovilidad"];
		$queryComprobarEstado = "
		SELECT mccm.id,
			mccm.num_correlativo,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.created_at,
			mccm.updated_at
		FROM   mepa_caja_chica_movilidad AS mccm
		WHERE mccm.id = {$cc_movilidad_id}
		and mccm.status = 2
		";
		$resultQueryComprobarEstado = $mysqli->query($queryComprobarEstado);
		$arrayResultQueryComprobarEstado = array();
		while ($li = $resultQueryComprobarEstado->fetch_assoc()) {
			$arrayResultQueryComprobarEstado[] = $li;
		}
		if (count($arrayResultQueryComprobarEstado) > 0) {
			$result["http_code"] = 406;
			$result["status"] = "No aceptado";
			$result["message"] = "Registro de movilidad Ya cerrado";
			$result["error"] = true;
		} else {
			$queryGuardarMovilidadDetalle = "
			INSERT INTO mepa_caja_chica_movilidad_detalle
		   (       
			   id_mepa_caja_chica_movilidad,
			   fecha,
			   partida_destino,
			   motivo_traslado,
			   monto,
			   estado,
			   created_at
		   )
		   VALUES
		   (
			   {$_POST["nameCajaChicaMovilidad"]} ,
			   '{$_POST["name_sec_mepa_movilidad_fecha_detalle"]}'  ,
			   '{$_POST['name_sec_mepa_movilidad_partida_destino_detalle']}' ,
			   '{$_POST['name_sec_mepa_movilidad_motivo_detalle']}'  ,
			   {$_POST['name_sec_mepa_movilidad_subtotal_viaje_detalle']}  ,
			1 ,
			   now() 
			)";
			$resultQuery = $mysqli->query($queryGuardarMovilidadDetalle);
			$viewError = $mysqli->error;
			if ($mysqli->error) {
				$error .= 'Error: ' . $mysqli->error . $queryGuardarMovilidadDetalle;
			}
			if ($error == '') {
				$result["http_code"] = 200;
				$result["status"] = "Agregado.";
				$result["error"] = false;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error";
				$result["error"] = true;
			}
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_get_data_caja_chica_cerrar_detalle_movilidad") {
	try {
		$error = '';
		$cc_movilidad_id = $_POST["cc_movilidad_id"];
		$tipo_solicitud = 3;
		$queryComprobarEstado = "
		SELECT mccm.id,
			mccm.num_correlativo,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.created_at,
			mccm.updated_at
		FROM   mepa_caja_chica_movilidad AS mccm
		WHERE mccm.id = {$cc_movilidad_id}
		and mccm.status = 2
		";
		$resultQueryComprobarEstado = $mysqli->query($queryComprobarEstado);
		$arrayResultQueryComprobarEstado = array();
		while ($li = $resultQueryComprobarEstado->fetch_assoc()) {
			$arrayResultQueryComprobarEstado[] = $li;
		}
		if (count($arrayResultQueryComprobarEstado) > 0) {

			$result["http_code"] = 406;
			$result["status"] = "No aceptado";
			$result["message"] = "Registro de movilidad Ya cerrado";
			$result["error"] = true;
		} else {
			$mepaDocumentoCorrelativo = new MepaDocumentoCorrelativo;
			$input = new stdClass;
			$input->id_usuario= (int)$_POST['id_usuario'];
			$input->tipo_solicitud= $tipo_solicitud;
			$queryCerrarMovilidadDetalle = "
			UPDATE
				mepa_caja_chica_movilidad
			SET
				STATUS              = 2,
				monto_cierre        = REPLACE((
					SELECT IF(sum(monto) IS NULL, 0.00, FORMAT(sum(monto), 2)) AS total
					FROM   mepa_caja_chica_movilidad_detalle AS mccmd
					WHERE  mccmd.id_mepa_caja_chica_movilidad = {$cc_movilidad_id}
						AND mccmd.estado = 1
				), ',', '')
			WHERE
				id = {$cc_movilidad_id}			
			";
			$resultQuery = $mysqli->query($queryCerrarMovilidadDetalle);
			$viewError = $mysqli->error;

			if ($mysqli->error) {
				$error .= 'Error: ' . $mysqli->error . $queryCerrarMovilidadDetalle;
			}
			if ($error == '') {
				$result["http_code"] = 200;
				$result["status"] = "Ok.";
				$result["error"] = false;
			} else {
				$result["http_code"] = 400;
				$result["status"] = $error;
				$result["error"] = true;
			}
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_delete_caja_chica_movilidad_detalle") {
	try {
		$error = '';
		$cc_movilidad_id = $_POST["cc_movilidad_id"];
		$idMovilidadDetalle = $_POST["idMovilidadDetalle"];
		$queryComprobarEstado = "
		SELECT mccm.id,
			mccm.num_correlativo,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.created_at,
			mccm.updated_at
		FROM   mepa_caja_chica_movilidad AS mccm
		WHERE mccm.id = {$cc_movilidad_id}
		and mccm.status = 2
		";
		$resultQueryComprobarEstado = $mysqli->query($queryComprobarEstado);
		$arrayResultQueryComprobarEstado = array();
		while ($li = $resultQueryComprobarEstado->fetch_assoc()) {
			$arrayResultQueryComprobarEstado[] = $li;
		}
		if (count($arrayResultQueryComprobarEstado) > 0) {

			$result["http_code"] = 406;
			$result["status"] = "No aceptado";
			$result["message"] = "Registro de movilidad Ya cerrado";
			$result["error"] = true;
		} else {
			$queryCerrarMovilidadDetalle = "
			UPDATE mepa_caja_chica_movilidad_detalle
			SET				
				estado = 0 ,				
				updated_at = now()
			WHERE id = {$idMovilidadDetalle}
			";
			$resultQuery = $mysqli->query($queryCerrarMovilidadDetalle);
			$viewError = $mysqli->error;

			if ($mysqli->error) {
				$error .= 'Error: ' . $mysqli->error . $queryCerrarMovilidadDetalle;
			}
			if ($error == '') {
				$result["http_code"] = 200;
				$result["status"] = "Registro Eliminado.";
				$result["error"] = false;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error";
				$result["error"] = true;
			}
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}

// -----------------MOVILIDAD  --------------//

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_get_data_list_solicitud_movilidad") {
	try {
		$userId = (int)$_POST['userId'];
		$error = '';
		$querySelect = "
		SELECT
			mccm.id,
			NULL as id_liquidacion,
			mccm.num_correlativo,
			mccm.id_tipo_solicitud_movilidad,
			s.nombre AS tipo_solicitud_movilidad,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.created_at,
			mccm.estado,
			IFNULL((SELECT sum(monto) FROM mepa_caja_chica_movilidad_detalle AS mccmd WHERE mccmd.estado=1 and mccmd.id_mepa_caja_chica_movilidad = mccm.id),0) monto_detalle,
			IFNULL((SELECT count(*) FROM mepa_caja_chica_movilidad_detalle AS mccmd WHERE mccmd.estado=1 and mccmd.id_mepa_caja_chica_movilidad = mccm.id),0) cantidad_detalle
		FROM
			mepa_caja_chica_movilidad AS mccm
			INNER JOIN mepa_tipos_solicitud s
    		ON mccm.id_tipo_solicitud_movilidad = s.id
		WHERE 
					mccm.user_created_id = {$userId}
					AND mccm.estado = 1 order by mccm.created_at desc
		";
		$resultQuery = $mysqli->query($querySelect);
		$arrayReturn = array();
		while ($li = $resultQuery->fetch_assoc()) {
			$temp = new stdClass();
			$temp->id = $li['id'];
			$temp->id_liquidacion = $li['id_liquidacion'];
			$temp->num_correlativo = $li['num_correlativo'];
			$temp->id_tipo_solicitud_movilidad = $li['id_tipo_solicitud_movilidad'];
			$temp->tipo_solicitud_movilidad = $li['tipo_solicitud_movilidad'];
			$temp->fecha_del = $li['fecha_del'];
			$temp->fecha_al = $li['fecha_al'];
			$temp->user_created_id = $li['user_created_id'];
			$temp->created_at = $li['created_at'];
			$temp->status = $li['status'];
			$temp->estado = $li['estado'];
			$temp->monto_detalle = $li['monto_detalle'];
			$temp->cantidad_detalle = $li['cantidad_detalle'];
			array_push($arrayReturn, $temp);
			unset($temp);
		}

		if ($mysqli->error) {
			$error .= 'Error: ' . $mysqli->error . $query;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Dato obtenido.";
			$result["error"] = false;
			$result["data"] = $arrayReturn;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"] = $e->getMessage();
		$result["error"] = true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_data_caja_chica_guardar_solicitud_movilidad") {
	try {
		$error = '';
		$idUsuarioLogin = $_POST["idUsuarioLogin"];
		$tipoSolicitud = $_POST["tipoSolicitud"];
		$tipoUsuarioVolante = $_POST["tipoUsuarioVolante"];
		$queryComprobarEstado = "
		SELECT
			mccm.id,
			mccm.num_correlativo,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.estado,
			mccm.created_at,
			mccm.updated_at
		FROM
			mepa_caja_chica_movilidad AS mccm
		WHERE mccm.status = 1
		AND mccm.user_created_id = {$idUsuarioLogin}
		";
		$resultQueryComprobarEstado = $mysqli->query($queryComprobarEstado);
		$arrayResultQueryComprobarEstado = array();
		while ($li = $resultQueryComprobarEstado->fetch_assoc()) {
			$arrayResultQueryComprobarEstado[] = $li;
		}
		if (count($arrayResultQueryComprobarEstado) > 0) {
			$result["http_code"] = 406;
			$result["status"] = "No aceptado";
			$result["message"] = "Existen una Solicitud de Movilidad Abierta";
			$result["error"] = true;
		} else {
			$queryCruceFechas = "
			SELECT mccm.fecha_del,mccm.fecha_al FROM mepa_caja_chica_movilidad AS mccm
			WHERE mccm.user_created_id = {$idUsuarioLogin}
			AND  mccm.estado  = 1
			AND mccm.fecha_del >= (NOW()-interval 5 YEAR)
			";
			$resultQueryCruce = $mysqli->query($queryCruceFechas);
			$fechasBd = array();
			while ($li = $resultQueryCruce->fetch_assoc()) {
				$fechasBd[] = $li;
			}
			$estadoFechas = fnc_sec_mepa_movilidad_validar_fechas($_POST['fechaInicioSolicitud'], $_POST['fechaFinSolicitud'], $fechasBd);
			if (count($estadoFechas) > 0) {
				$result["http_code"] = 406;
				$result["status"] = "No aceptado";
				$result["message"] = "Existen cruce en las fechas enviadas";
				$result["error"] = true;
			} else {
				$mepaDocumentoCorrelativo = new MepaDocumentoCorrelativo;
				$input = new stdClass;
				$input->id_usuario= (int)$idUsuarioLogin;
				$input->tipo_solicitud= 3;
				$data_return = $mepaDocumentoCorrelativo->fnGenerarCorrelativo($input);
				$num_correlativo = substr(str_repeat(0, 9).$data_return->num_correlativo, - 9);
				
				// SI ES CAJERO VOLANTE TENEMOS QUE INGRESAR EL USUARIO CAJERO VOLANTE
				if($tipoSolicitud == 8)
				{
					$queryGuardarMovilidad = "
										INSERT INTO mepa_caja_chica_movilidad
										(	
											fecha_del,
											fecha_al,
											status,
											num_correlativo,
											id_tipo_solicitud_movilidad,
											id_usuario_volante,
											user_created_id,
											estado,
											created_at
										)
										VALUES
										(							
											'{$_POST['fechaInicioSolicitud']}',
											'{$_POST['fechaFinSolicitud']}',
											1,
											'{$num_correlativo}',
											'{$tipoSolicitud}',
											'{$tipoUsuarioVolante}',
											{$idUsuarioLogin},
											1,
											now()
										)
									";
				}
				else
				{
					$queryGuardarMovilidad = "
										INSERT INTO mepa_caja_chica_movilidad
										(	
											fecha_del,
											fecha_al,
											status,
											num_correlativo,
											id_tipo_solicitud_movilidad,
											user_created_id,
											estado,
											created_at
										)
										VALUES
										(							
											'{$_POST['fechaInicioSolicitud']}',
											'{$_POST['fechaFinSolicitud']}',
											1,
											'{$num_correlativo}',
											'{$tipoSolicitud}',
											{$idUsuarioLogin},
											1,
											now()
										)
									";
				}
				
				$resultQuery = $mysqli->query($queryGuardarMovilidad);
				$viewError = $mysqli->error;
				if ($mysqli->error) {
					$error .= 'Error: ' . $mysqli->error . $queryGuardarMovilidad;
				}
				if ($error == '') {
					$result["http_code"] = 200;
					$result["status"] = "Agregado.";
					$result["error"] = false;
				} else {
					$result["http_code"] = 400;
					$result["status"] = "Ocurrio un error";
					$result["error"] = true;
				}
			}
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_data_caja_chica_update_solicitud_movilidad_cc_id") {
	try {
		$error = '';
		$cc_id = $_POST["cc_id"];
		$idCajaChicaMovilidad = $_POST["idCajaChicaMovilidad"];
		$queryComprobarEstado = "
		SELECT mccm.id,
			mccm.num_correlativo,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.created_at,
			mccm.updated_at
		FROM   mepa_caja_chica_movilidad AS mccm
		WHERE mccm.id = {$idCajaChicaMovilidad}
		and mccm.status = 2
		";
		$resultQueryComprobarEstado = $mysqli->query($queryComprobarEstado);
		$arrayResultQueryComprobarEstado = array();
		while ($li = $resultQueryComprobarEstado->fetch_assoc()) {
			$arrayResultQueryComprobarEstado[] = $li;
		}
		if (count($arrayResultQueryComprobarEstado) > 0) {

			$result["http_code"] = 406;
			$result["status"] = "No aceptado";
			$result["message"] = "Registro de movilidad Ya cerrado";
			$result["error"] = true;
		} else {
			$queryActualizarCcId = "
			UPDATE mepa_caja_chica_movilidad
			SET
				cc_id = {$cc_id} ,
				updated_at = now()
			WHERE id = {$idCajaChicaMovilidad}
			";
			$resultQuery = $mysqli->query($queryActualizarCcId);
			$viewError = $mysqli->error;

			if ($mysqli->error) {
				$error .= 'Error: ' . $mysqli->error . $queryActualizarCcId;
			}
			if ($error == '') {
				$result["http_code"] = 200;
				$result["status"] = "Registro Actualizado.";
				$result["error"] = false;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error";
				$result["error"] = true;
			}
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_solicitud_movilidad_listar_movilidad_supervisor")
{
	$param_tipo_solicitud = $_POST['param_tipo_solicitud'];

	$usuario_id = $login?$login['id']:null;

	$query = "
		SELECT
			mccm.id,
			l.id_movilidad,
			l.num_correlativo AS num_liquidacion,
			mccm.num_correlativo,
			mccm.id_tipo_solicitud_movilidad,
			s.nombre AS tipo_solicitud_movilidad,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.created_at,
			mccm.estado,
			IFNULL(
					(
						SELECT sum(monto) 
						FROM mepa_caja_chica_movilidad_detalle AS mccmd 
						WHERE mccmd.estado = 1 AND mccmd.id_mepa_caja_chica_movilidad = mccm.id
					)
				,0) monto_detalle,
			IFNULL(
					(
						SELECT count(*) 
						FROM mepa_caja_chica_movilidad_detalle AS mccmd 
						WHERE mccmd.estado = 1 AND mccmd.id_mepa_caja_chica_movilidad = mccm.id
					)
				,0) cantidad_detalle
		FROM
			mepa_caja_chica_movilidad AS mccm
			INNER JOIN mepa_tipos_solicitud s
			ON mccm.id_tipo_solicitud_movilidad = s.id
			LEFT JOIN mepa_caja_chica_liquidacion l
    		ON mccm.id = l.id_movilidad
		WHERE mccm.user_created_id = {$usuario_id} AND mccm.estado = 1 AND mccm.id_tipo_solicitud_movilidad = {$param_tipo_solicitud}
		ORDER BY mccm.id desc
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => ($reg->id_movilidad == NULL) ? 'Movilidad sin Adjuntar' : 'Movilidad Adjuntada',
			"2" => $reg->num_liquidacion,
			"3" => $reg->num_correlativo,
			"4" => $reg->tipo_solicitud_movilidad,
			"5" => $reg->fecha_del.' - '.$reg->fecha_al,
			"6" => $reg->monto_detalle,
			"7" => $reg->cantidad_detalle,
			"8" => ($reg->status == 1) ? '<span class="badge badge-primary">Abierto</span>' : '<span class="badge badge-danger">Cerrado</span>',
			"9" => ($reg->id_tipo_solicitud_movilidad == 7) ? 
				'
				<a   
                    class="btn btn-info btn btn-sm btn-round" 
                    href="./?sec_id=mepa&sub_sec_id=solicitud_movilidad_detalle&cc_movilidad='.$reg->id.'"
                    data-toggle="tooltip" 
                    data-placement="top" 
                    title="Acceder al detalle">
                    <i class="fa fa-eye"></i>
                </a>
				' 
				:
				'
				<a   
                    class="btn btn-info btn btn-sm btn-round" 
                    href="./?sec_id=mepa&sub_sec_id=solicitud_movilidad_volante_detalle&cc_movilidad='.$reg->id.'"
                    data-toggle="tooltip"
                    data-placement="top" 
                    title="Acceder al detalle">
                    <i class="fa fa-eye"></i>
                </a>
				'
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_solicitud_movilidad_listar_movilidad_cajero_volante")
{
	$param_tipo_solicitud = $_POST['param_tipo_solicitud'];

	$usuario_id = $login?$login['id']:null;

	$query = "
		SELECT
			mccm.id,
			l.id_movilidad,
			l.num_correlativo AS num_liquidacion,
			mccm.num_correlativo,
			mccm.id_tipo_solicitud_movilidad,
			mccm.id_usuario_volante,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_volante,
			s.nombre AS tipo_solicitud_movilidad,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.created_at,
			mccm.estado,
			IFNULL(
					(
						SELECT sum(monto) 
						FROM mepa_caja_chica_movilidad_detalle AS mccmd 
						WHERE mccmd.estado = 1 AND mccmd.id_mepa_caja_chica_movilidad = mccm.id
					)
				,0) monto_detalle,
			IFNULL(
					(
						SELECT count(*) 
						FROM mepa_caja_chica_movilidad_detalle AS mccmd 
						WHERE mccmd.estado = 1 AND mccmd.id_mepa_caja_chica_movilidad = mccm.id
					)
				,0) cantidad_detalle
		FROM
			mepa_caja_chica_movilidad AS mccm
			INNER JOIN mepa_tipos_solicitud s
			ON mccm.id_tipo_solicitud_movilidad = s.id
			INNER JOIN tbl_usuarios tu
			ON mccm.id_usuario_volante = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			LEFT JOIN mepa_caja_chica_liquidacion l
    		ON mccm.id = l.id_movilidad
		WHERE mccm.user_created_id = {$usuario_id} AND mccm.estado = 1 AND mccm.id_tipo_solicitud_movilidad = {$param_tipo_solicitud}
		ORDER BY mccm.id desc
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => ($reg->id_movilidad == NULL) ? 'Movilidad sin Adjuntar' : 'Movilidad Adjuntada',
			"2" => $reg->num_liquidacion,
			"3" => $reg->num_correlativo,
			"4" => $reg->tipo_solicitud_movilidad,
			"5" => $reg->usuario_volante,
			"6" => $reg->fecha_del.' - '.$reg->fecha_al,
			"7" => $reg->monto_detalle,
			"8" => $reg->cantidad_detalle,
			"9" => ($reg->status == 1) ? '<span class="badge badge-primary">Abierto</span>' : '<span class="badge badge-danger">Cerrado</span>',
			"10" => ($reg->id_tipo_solicitud_movilidad == 7) ? 
				'
				<a   
                    class="btn btn-info btn btn-sm btn-round" 
                    href="./?sec_id=mepa&sub_sec_id=solicitud_movilidad_detalle&cc_movilidad='.$reg->id.'"
                    data-toggle="tooltip" 
                    data-placement="top" 
                    title="Acceder al detalle">
                    <i class="fa fa-eye"></i>
                </a>
				' 
				:
				'
				<a   
                    class="btn btn-info btn btn-sm btn-round" 
                    href="./?sec_id=mepa&sub_sec_id=solicitud_movilidad_volante_detalle&cc_movilidad='.$reg->id.'"
                    data-toggle="tooltip"
                    data-placement="top" 
                    title="Acceder al detalle">
                    <i class="fa fa-eye"></i>
                </a>
				'
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"] === "mepa_solicitud_movilidad_guardar_nueva_movilidad") {
	try
	{
		$param_fecha_inicio = $_POST["mepa_solicitud_movilidad_modal_param_fecha_inicio"];
		$param_fecha_inicio = date("Y-m-d", strtotime($param_fecha_inicio));

		$param_fecha_fin = $_POST["mepa_solicitud_movilidad_modal_param_fecha_fin"];
		$param_fecha_fin = date("Y-m-d", strtotime($param_fecha_fin));

		$param_tipo_solicitud = $_POST["mepa_solicitud_movilidad_modal_param_tipo_solicitud"];
		$param_cajero_volante = $_POST["mepa_solicitud_movilidad_modal_param_cajero_volante"];

		$error = '';
		$usuario_id = $login?$login['id']:null;

		$queryComprobarEstado = "
			SELECT
				mccm.id,
				mccm.num_correlativo,
				mccm.fecha_del,
				mccm.fecha_al,
				mccm.status,
				mccm.user_created_id,
				mccm.estado,
				mccm.created_at,
				mccm.updated_at
			FROM
				mepa_caja_chica_movilidad AS mccm
			WHERE mccm.status = 1 AND mccm.user_created_id = {$usuario_id}
		";

		$resultQueryComprobarEstado = $mysqli->query($queryComprobarEstado);
		
		$arrayResultQueryComprobarEstado = array();

		while ($li = $resultQueryComprobarEstado->fetch_assoc())
		{
			$arrayResultQueryComprobarEstado[] = $li;
		}

		if (count($arrayResultQueryComprobarEstado) > 0)
		{
			$result["http_code"] = 406;
			$result["status"] = "No aceptado";
			$result["message"] = "Existe una Solicitud de Movilidad Abierta. Por favor para poder crear nueva solicitud es necesario cerrar las solicitudes Movilidad abiertas";
			$result["error"] = true;
		}
		else
		{
			$queryCruceFechas = 
			"
				SELECT
					mccm.fecha_del,mccm.fecha_al 
				FROM mepa_caja_chica_movilidad AS mccm
				WHERE mccm.user_created_id = {$usuario_id} 
					AND  mccm.estado  = 1 
					AND mccm.id_tipo_solicitud_movilidad = '".$param_tipo_solicitud."'
					AND mccm.fecha_del >= (NOW()-interval 5 YEAR)
			";

			$resultQueryCruce = $mysqli->query($queryCruceFechas);
			
			$fechasBd = array();
			
			while ($li = $resultQueryCruce->fetch_assoc())
			{
				$fechasBd[] = $li;
			}

			$estadoFechas = fnc_sec_mepa_movilidad_validar_fechas($param_fecha_inicio, $param_fecha_fin, $fechasBd);
			
			if (count($estadoFechas) > 0)
			{
				$result["http_code"] = 406;
				$result["status"] = "No aceptado";
				$result["message"] = "Existen cruce en las fechas enviadas";
				$result["error"] = true;

				echo json_encode($result, JSON_UNESCAPED_UNICODE);
				exit();
			}

			$mepaDocumentoCorrelativo = new MepaDocumentoCorrelativo;
			$input = new stdClass;
			$input->id_usuario= (int)$usuario_id;
			$input->tipo_solicitud= 3;
			$data_return = $mepaDocumentoCorrelativo->fnGenerarCorrelativo($input);
			$num_correlativo = substr(str_repeat(0, 9).$data_return->num_correlativo, - 9);


			// SI ES CAJERO VOLANTE TENEMOS QUE INGRESAR EL USUARIO CAJERO VOLANTE
			if($param_tipo_solicitud == 8)
			{
				$queryGuardarMovilidad = "
											INSERT INTO mepa_caja_chica_movilidad
											(	
												fecha_del,
												fecha_al,
												status,
												num_correlativo,
												id_tipo_solicitud_movilidad,
												id_usuario_volante,
												user_created_id,
												estado,
												created_at
											)
											VALUES
											(							
												'{$param_fecha_inicio}',
												'{$param_fecha_fin}',
												1,
												'{$num_correlativo}',
												'{$param_tipo_solicitud}',
												'{$param_cajero_volante}',
												{$usuario_id},
												1,
												now()
											)";
			}
			else
			{
				$queryGuardarMovilidad = "
											INSERT INTO mepa_caja_chica_movilidad
											(	
												fecha_del,
												fecha_al,
												status,
												num_correlativo,
												id_tipo_solicitud_movilidad,
												user_created_id,
												estado,
												created_at
											)
											VALUES
											(							
												'{$param_fecha_inicio}',
												'{$param_fecha_fin}',
												1,
												'{$num_correlativo}',
												'{$param_tipo_solicitud}',
												{$usuario_id},
												1,
												now()
											)";
			}

			$resultQuery = $mysqli->query($queryGuardarMovilidad);
			
			$viewError = $mysqli->error;

			if($mysqli->error)
			{
				$error .= 'Error: ' . $mysqli->error . $queryGuardarMovilidad;
			}

			if($error == '')
			{
				$result["http_code"] = 200;
				$result["status"] = "Agregado.";
				$result["error"] = false;
			}
			else
			{
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error";
				$result["error"] = true;
			}
		}
	}
	catch (Exception $e)
	{
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_solicitud_movilidad_detalle_guardar_editar_fechas")
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_id_movilidad = $_POST['param_id_movilidad'];
		
		$param_fecha_del = $_POST["param_fecha_del"];
		$param_fecha_del = date("Y-m-d", strtotime($param_fecha_del));

		$param_fecha_al = $_POST["param_fecha_al"];
		$param_fecha_al = date("Y-m-d", strtotime($param_fecha_al));

		$error = '';

		$query_select = 
		"
		    SELECT
				num_correlativo, fecha_del, fecha_al
			FROM mepa_caja_chica_movilidad
			WHERE id != '".$param_id_movilidad."'
				AND estado = 1
    			AND user_created_id = '".$usuario_id."' 
				AND id_tipo_solicitud_movilidad = 7 
				AND fecha_del BETWEEN '".$param_fecha_del."' AND '".$param_fecha_al."'
				AND fecha_al BETWEEN '".$param_fecha_del."' AND '".$param_fecha_al."'
		";

		$query_validar_fechas = $mysqli->query($query_select);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Error al registrar.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		$row_count = mysqli_num_rows($query_validar_fechas);

		if($row_count > 0)
		{
		    $result["http_code"] = 400;
			$result["status"] = "Cruces de Fechas";
			$result["error"] = "Las fechas seleccionadas ya existen.";

			echo json_encode($result);
			exit();
		}

		$query_update = 
		"
			UPDATE mepa_caja_chica_movilidad 
				SET fecha_del = '".$param_fecha_del."', 
					fecha_al = '".$param_fecha_al."'
			WHERE id = '".$param_id_movilidad."' 
		";

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Error al registrar.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		if($error == '')
		{
			$result["http_code"] = 200;
			$result["status"] = "Registro existoso.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Error al registrar.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_solicitud_movilidad_detalle_activar")
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_id_movilidad = $_POST['param_id_movilidad'];
		
		$error = '';

		$query_update = 
		"
			UPDATE mepa_caja_chica_movilidad 
				SET status = 1
			WHERE id = '".$param_id_movilidad."' 
		";

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["titulo"] = "Error al activar.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;
		$result["titulo"] = "Activación existosa.";
		$result["descripcion"] = $error;

		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
		$result["titulo"] ="Sesión perdida.";
        $result["descripcion"] ="Por favor vuelva a iniciar sesión.";

	}

	echo json_encode($result);
	exit();
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
