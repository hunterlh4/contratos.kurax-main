<?php
date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_procesar_programacion_obtener_datos"){
	$programacion_id = $_POST["programacion_id"];
	$query_1 = "SELECT p.id as programacion_id, p.numero programacion_numero,
				p.fecha_programacion, p.tipo_concepto_id, tc.nombre concepto, p.valor_cambio ,
				p.tipo_pago_id, tp.nombre tipo_pago,
				p.num_cuenta_id, nc.num_cuenta_corriente as banco,
				p.moneda_id, m.sigla as moneda, p.importe as importe, p.etapa_id, ep.nombre etapa,
				if(isnull(cp.nombre),'',cp.nombre) as nombre_archivo, if(isnull(cp.extension),'',cp.extension) as extension_archivo,
				#auditoria
				if(isnull(concat(pp.nombre, ' ', pp.apellido_paterno)),'-',concat(pp.nombre, ' ', pp.apellido_paterno)) as elaborado_por,
				if(isnull(p.created_at),'-',p.created_at) as fecha_elaboracion,
				if(isnull(concat(pp_2.nombre, ' ', pp_2.apellido_paterno)),'-',concat(pp_2.nombre, ' ', pp_2.apellido_paterno))  as editado_por,
				if(isnull(p.edit_at),'-',p.edit_at) as fecha_edicion
				FROM cont_programacion p
				INNER JOIN cont_tipo_concepto tc on p.tipo_concepto_id = tc.id
				INNER JOIN cont_tipo_pago_programacion tp on p.tipo_pago_id = tp.id
				INNER JOIN cont_num_cuenta nc on p.num_cuenta_id = nc.id
				INNER JOIN tbl_moneda m on p.moneda_id = m.id
				INNER JOIN cont_etapa_programacion ep on p.etapa_id = ep.id
				LEFT JOIN cont_comprobantes_pago cp on p.comprobante_pago_id = cp.id
				#elaboracion
				INNER JOIN tbl_usuarios u on p.user_created_id = u.id
				INNER JOIN tbl_personal_apt pp on u.personal_id = pp.id
				#edicion
				LEFT JOIN tbl_usuarios u_2 on p.user_edit_id = u_2.id
				LEFT JOIN tbl_personal_apt pp_2 on u_2.personal_id = pp_2.id
				WHERE p.id = " . $programacion_id;

	$list_query = $mysqli->query($query_1);
	$list_detalle_programacion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_detalle_programacion[] = $li;
	}
	$result=array();
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if (count($list_detalle_programacion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay detalle de la programacion.";
	} elseif (count($list_detalle_programacion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_detalle_programacion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el detalle de la programacion.";
	}
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_procesar_programacion_listar_subdiarios"){
	$query = "SELECT id subdiario_id, 
				concat(num_cuenta, ' ', descripcion) descripcion 
				FROM cont_subdiario
				WHERE status = 1";

	$list_query = $mysqli->query($query);
	$list_proc_subdiarios = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_proc_subdiarios[] = $li;
	}
	$result=array();
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if (count($list_proc_subdiarios) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay subdiarios.";
	} elseif (count($list_proc_subdiarios) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_proc_subdiarios;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los subdiarios.";
	}
	echo json_encode($result);
}
?>