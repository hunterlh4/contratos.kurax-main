<?php
date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_detalle_programacion_obtener_detalle"){
	$programacion_id = $_POST["programacion_id"];
	$query_1 = "SELECT p.id as programacion_id, p.numero programacion_numero,
				p.fecha_programacion, p.tipo_concepto_id, tc.nombre concepto, p.valor_cambio ,
				p.tipo_pago_id, tp.nombre tipo_pago,
				p.num_cuenta_id, nc.num_cuenta_corriente as banco,
				p.moneda_id, 
				(CASE WHEN p.moneda_id = 1 THEN 'MN' WHEN p.moneda_id = 2 THEN 'ME' END) AS moneda,
				p.importe as importe, p.etapa_id, ep.nombre etapa,
				if(isnull(cp.nombre),'',cp.nombre) as nombre_archivo, if(isnull(cp.extension),'',cp.extension) as extension_archivo,
				#auditoria
				if(isnull(concat(pp.nombre, ' ', pp.apellido_paterno)),'-',concat(pp.nombre, ' ', pp.apellido_paterno)) as elaborado_por,
				if(isnull(p.created_at),'-',p.created_at) as fecha_elaboracion,
				if(isnull(concat(pp_2.nombre, ' ', pp_2.apellido_paterno)),'-',concat(pp_2.nombre, ' ', pp_2.apellido_paterno))  as editado_por,
				if(isnull(p.edit_at),'-',p.edit_at) as fecha_edicion,
				if(isnull(concat(pp_3.nombre, ' ', pp_3.apellido_paterno)),'-',concat(pp_3.nombre, ' ', pp_3.apellido_paterno)) as procesado_por,
				if(isnull(p.process_at),'-',p.process_at) as fecha_proceso,
				if(isnull(concat(pp_4.nombre, ' ', pp_4.apellido_paterno)),'-',concat(pp_4.nombre, ' ', pp_4.apellido_paterno))as eliminado_por,
				if(isnull(p.delete_at),'-',p.delete_at) as fecha_eliminacion
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
				#proceso
				LEFT JOIN tbl_usuarios u_3 on p.user_process_id = u_3.id
				LEFT JOIN tbl_personal_apt pp_3 on u_3.personal_id = pp_3.id
				#eliminacion
				LEFT JOIN tbl_usuarios u_4 on p.user_delete_id = u_4.id
				LEFT JOIN tbl_personal_apt pp_4 on u_4.personal_id = pp_4.id
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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_detalle_programacion_obtener_acreedores"){
	$programacion_id = $_POST["programacion_id"];
	$usuario_id = $login?$login['id']:null;
	// verificar si el usuario tiene todos los permisos locales 
	$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;

	$list_query_permisos = $mysqli->query($query);
	$query = "
	SELECT 
		pd.id AS det_pro_id, 
		pd.programacion_id, 
		pd.provision_id, 
		p.num_ruc AS codigo,
		b.num_docu as num_documento,
		b.nombre AS acreedor, 
		p.fecha_actual AS periodo,
		CONCAT('1683-', p.mes, p.anio) AS num_doc, 
		10 dia_pago, p.periodo_fin AS fecha_vencimiento,
		(
			CASE 
				WHEN ce.tipo_moneda_id = 1 THEN 'MN' 
				WHEN ce.tipo_moneda_id = 2 THEN 'ME' 
			END
		) AS moneda,
		p.importe AS programado, 
		if(isnull(concat(c.cc_id, '-', c.nombre_tienda)),'',concat(c.cc_id, '-', c.nombre_tienda)) AS centro_costo
	FROM 
		cont_programacion_detalle pd
		LEFT JOIN cont_provision p ON pd.provision_id = p.id
		LEFT JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
		LEFT JOIN cont_beneficiarios b ON ce.contrato_id = b.contrato_id
		LEFT JOIN cont_contrato c ON ce.contrato_id = c.contrato_id
		LEFT JOIN tbl_locales tl ON tl.contrato_id = c.contrato_id
		LEFT JOIN tbl_usuarios_locales tul ON tul.local_id = tl.id

	WHERE 
		programacion_id = $programacion_id
		AND pd.status = 1 
		AND p.status = 1 
		AND ce.status = 1 
		 
		AND c.status = 1 
	";
	if ($list_query_permisos->num_rows > 0) {
		$query.= ' AND  tul.usuario_id ='.$usuario_id.' GROUP BY pd.id
		ORDER BY p.id DESC';
	}else{
		$query.= ' GROUP BY pd.id';

	} 
	$list_query = $mysqli->query($query);
	$list_acreedores_programacion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_acreedores_programacion[] = $li;
	}
	$result=array();
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if (count($list_acreedores_programacion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay acreedores.";
	} elseif (count($list_acreedores_programacion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_acreedores_programacion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los acreedores de la programacion.";
	}
	$result["query"] = $query;
	echo json_encode($result);
}


?>