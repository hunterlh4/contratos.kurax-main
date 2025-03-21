<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


$tipos_and_estados = "
	(
		(tra.tipo_id IN (2,3,4,5,6,14,15,17,18,19,20,21,26,34,36,37) and tra.estado=1) 
		OR (tra.tipo_id IN (1,7,8,9,10,12,13,16,22,23,24,25,27,30,31,36,38,39,11,14,21,28,29,32,33,35))
    )
";


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_v2") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_tipo_transaccion = $_POST["tipo_transaccion"];
	$local                     = $_POST["local"];
	$busqueda_cajero           = $_POST["cajero"];
	$estado_cierre             = $_POST["estado_cierre"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];
	$bono             		   = $_POST["bono"];
	$proveedor             	   = $_POST["proveedor"];
	$num_transaccion           = $_POST["num_transaccion"];
	$tipo_saldo          	   = $_POST["tipo_saldo"];
	$caja_vip				   = $_POST["caja_vip"];
	$lugar				       = $_POST["lugar"];


	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	$where_fecha = "";
	if( (int) $tipo_busqueda === 1) { // POR CAJA
		$where_fecha = " 
			AND ( 
				(DATE ( caj.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
				OR 
				(DATE ( ce.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
			) 
		";
	} else if( (int) $tipo_busqueda === 2) { // POR FECHA DE ABONO
		$where_fecha = " 
			AND tra.registro_deposito IS NOT NULL 
			AND DATE(tra.registro_deposito) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra.registro_deposito) <= '".$busqueda_fecha_fin."' 
		";
	} else { // POR FECHA DE TRANSACCIÓN
		$where_fecha = " 
			AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."'
		";
	}

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		switch ((int)$busqueda_tipo_transaccion) {
			/*case 1:
				$where_tipo_transaccion=" 
				AND (
					#(tra.tipo_id = 1 and tra.estado = 1 and IFNULL(tra_2.estado, 0) != 3 and IFNULL(tra.caja_vip,0) IN (0,2)) 
					#OR (tra.tipo_id = 1 and tra.estado = 1 AND IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) = 1) 
					#OR (tra.tipo_id = 26 and tra.estado = 1 and IFNULL(tra.caja_vip,0) = 1)
					(tra.tipo_id = 26 and tra.estado = 1)
				)";
				break;
			case 11:
				$where_tipo_transaccion=" 
				AND (
					#(tra.tipo_id = 9 and tra.estado = 2 and IFNULL(tra.caja_vip, 0) = 0)
		            #OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip, 0) = 1)
					(tra.tipo_id = 11 )
		         )";
				break;
			case 21:
				$where_tipo_transaccion=" AND tra.tipo_id = 21 and tra.estado = 2";
				break;
			case 28:
				$where_tipo_transaccion=" 
				AND (
					(tra.tipo_id = 28 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 0) 
					OR (tra.tipo_id = 29 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 1)
				)";
				break;*/
			case 41:
				$where_tipo_transaccion="
				AND ( tt.is_bonus = 1 and tra.tipo_id = 4 )";					
				break;
			case 51:
				$where_tipo_transaccion="
				AND ( tt.is_bonus = 1  and tra.tipo_id = 5)";					
				break;
			default:
				$where_tipo_transaccion=" AND tra.tipo_id =" . $busqueda_tipo_transaccion;
				break;
		}
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){ // Si es cajero
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_bono="";
	if( (int) $bono > 0 ){
		$where_bono = " AND tra.bono_id = " . $bono;
	}

	$where_proveedor="";
	if( (int) $proveedor > 0 ){
		$where_proveedor = " AND tra.api_id = " . $proveedor;
	}


	$where_num_transaccion = "";
	if(strlen($num_transaccion)>0){
		$where_num_transaccion = " AND tra.txn_id = '" . $num_transaccion."'";
	}

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	$where_lugar="";
	if( (int) $lugar === 2 ){  
		$where_lugar=" AND tra.caja_vip = 3 ";
	}else if ( (int) $lugar === 1 ) {
		$where_lugar=" AND IFNULL(tra.caja_vip, 0) IN (0,2) ";
	}else if ( (int) $lugar === 0 ) {
		$where_lugar="";
	}

	$where_local="";
	if( (int) $local > 0 ){
		$where_local=" AND ( loc.cc_id='".$local."' OR ce_ssql.cc_id='".$local."' ) ";
	}

	$where_estado_cierre="";
	if( (int) $estado_cierre > 0 ){
		if( (int) $estado_cierre === 1 ){//Cierre activo
			$where_estado_cierre=" AND caj.id > 0 ";
		}
		if( (int) $estado_cierre === 2 ){//Cierre eliminado
			$where_estado_cierre=" AND ce.id > 0 ";
		}
	}

	$where_cuenta = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '' && $_POST["cuenta"] !== null) {
		$where_cuenta = " AND tra.cuenta_id IN ( " . implode(",",$_POST["cuenta"]) . " ) " ;
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(tra.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(tra.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$where_cajavip = "";
	if (!$caja_vip == 1) {
		$where_cajavip = " AND IFNULL(tra.caja_vip, 0) IN (0,2) " ;
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"			
			OR ttc.contact_type LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.telefono LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.txn_id LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.tipo_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR cuen.cuenta_descripcion LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.comision_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.bono_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.total_recarga LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR usu_val.usuario LIKE "%'.$_POST["search"]["value"].'%" ';

			$diccionario = array(1=>'DEPOSITO',2=>'RECARGA WEB',3=>'RETORNO RECARGA WEB',4=>'APUESTA REGISTRADA',5=>'APUESTA PAGADA');
			foreach ($diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR tra.tipo_id LIKE "%'.$key.'%"';
				}
			}

			$tc_tipo_doc_diccionario = array(0=>'DNI',8=>'DNI',1=>'CARNE EXTRANJERIA',2=>'PASAPORTE');
			foreach ($tc_tipo_doc_diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR cli.tipo_doc LIKE "%'.$key.'%"';
				}
			}
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"tra.cliente_id",
		2=>"loc.nombre",
		3=>"tra.created_at",
		4=>"tra.registro_deposito",
		5=>"tra.tipo_id",
		6=>"ttc.contact_type",
		7=>"cli.telefono",
		8=>"tra.txn_id",
		9=>"cli.tipo_doc",
		10=>"cli.num_doc",
		11=>"tra.web_id",
		12=>"cli.nombre",
		13=>"cuen.cuenta_descripcion",
		14=>"tra.monto_deposito",
		15=>"tra.comision_monto",
		16=>"tra.monto",
		17=>"tra.bono_monto",
		18=>"tra.total_recarga",
		19=>"usu.usuario",
		20=>"usu_val.usuario"
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY tra.id ASC';
		}
	} else {
		$order = ' ORDER BY tra.id ASC';
	}

	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	// QUERY
	$query_1 ="
		SELECT
			tra.id,
			tra.cliente_id,
			IFNULL( tra.web_id, '' ) web_id,
			tra.tipo_id cod_tipo_transaccion,
			IFNULL( tt.is_bonus, '' ) is_bonus, 
			(CASE 
				WHEN tt.is_bonus in (1) THEN CONCAT(ttt.nombre, ' Gratis')
				ELSE ttt.nombre 
			END) AS tipo_transaccion,
			tra.created_at fecha_hora_registro,
			IFNULL(tra.registro_deposito, '') registro_deposito,
			usu.usuario AS cajero,
			IFNULL(usu_val.usuario, '') AS validador,
			IFNULL(usu_val_sup.usuario, '') AS validado_por,
			(CASE cli.tipo_doc WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE 'NO DEFINIDO' END) AS tipo_doc,
			IFNULL(cli.num_doc, '') num_doc,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(cli.telefono, '') telefono,
			tra.estado AS estado_id,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			IFNULL(tra.monto_deposito, 0) AS deposito,
			IFNULL(tra.comision_monto, 0) AS comision_monto,
			IFNULL(tra.monto, 0) AS monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(tra.total_recarga, 0) AS total_recarga,
			IFNULL(ttc.contact_type, '') AS tipo_contacto,
			IFNULL(caj.id, 0) validacion_cierre,
			UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) local_cierre,
			IFNULL(caj.id, ce.id) id_turno_cierre,
			IFNULL(caj.turno_id, ce.turno_id) turno_cierre,
			IFNULL(rec_bon.nombre, 'Ninguno') bono_nombre,
			IFNULL(ta.name, '') proveedor_nombre,
			(CASE 
				WHEN tra.tipo_id in (4,5,15,19,33,34) THEN IFNULL(tra.txn_id, '') 
				WHEN tra.tipo_id in (2) THEN IFNULL(tra.id, '') 
				ELSE '' 
			END) AS txn_id,
			CASE WHEN tra.tipo_id in (2) THEN  IFNULL(tra.txn_id, '') ELSE '' END AS operation_id,
			IFNULL(tra.observacion_cajero, '') observacion_cajero,
			IF(tra.id_tipo_balance = 6, 'Promocional', 'Real') tipo_saldo
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttt ON ttt.id = tra.tipo_id
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
			LEFT JOIN tbl_usuarios usu_val_sup ON usu_val_sup.id = tra.user_valid_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			LEFT JOIN tbl_televentas_recargas_bono rec_bon ON tra.bono_id = rec_bon.id
			LEFT JOIN tbl_televentas_proveedor ta ON tra.api_id = ta.id
			LEFT JOIN tbl_televentas_tickets tt ON tra.txn_id = tt.ticket_id AND tra.api_id = tt.proveedor_id
		WHERE "
		.$tipos_and_estados."
		AND tra.estado is not null
		AND tra.cliente_id > 0 
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4
		"
		.$where_users_test
		.$where_fecha
		.$where_tipo_transaccion
		.$where_cajero
		.$where_local
		.$where_estado_cierre
		.$where_estado_cierre
		.$where_cuenta
		.$where_cliente
		.$where_bono
		.$where_proveedor
		.$where_num_transaccion
		.$where_tipo_saldo
		.$where_lugar
		.$nombre_busqueda
		.$where_cajavip
		.$order
		.$limit;
	//echo $query_1;
	$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$list_transaccion=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_transaccion[]=$li;
		}
	}

	// Cantidades
	$query_COUNT ="
		SELECT
			COUNT(*) cant
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			LEFT JOIN tbl_televentas_tickets tt ON tra.txn_id = tt.ticket_id
		WHERE
		".$tipos_and_estados."
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_local ." 
		".$where_estado_cierre ." 
		".$where_cuenta."
		".$where_cliente."
		".$where_bono."
		".$where_proveedor."
		".$where_num_transaccion."
		".$where_tipo_saldo."
		".$where_lugar."
		".$nombre_busqueda ."
		".$where_cajavip."
		";
	//echo $query_1;
	$result["consulta_query_COUNT"] = $query_COUNT;
	$list_query_COUNT=$mysqli->query($query_COUNT);
	$list_transaccion_COUNT=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_COUNT->fetch_assoc()) {
			$list_transaccion_COUNT[]=$li;
		}
	}

	if(count($list_transaccion)===0){
		$result["http_code"] = 204;
		$result["status"]    = "No hay transacciones.";
		$result["data"]      = $list_transaccion;
	} elseif(count($list_transaccion)>0){
		$result["http_code"]       = 200;
		$result["status"]          = "ok";
		$result["draw"]            = intval($_POST["draw"]);
		$result["recordsTotal"]    = $list_transaccion_COUNT[0]["cant"];
		$result["recordsFiltered"] = $list_transaccion_COUNT[0]["cant"];
		$result["data"]            = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"]    ="Ocurrió un error al consultar transacciones.";
		$result["data"]      = $list_transaccion;
		$result["resumen"]   = $list_transaccion_COUNT;
	}
}

function fnc_like_match($str, $searchTerm) {
    $searchTerm = strtolower($searchTerm);
    $str = strtolower($str);
    $pos = strpos($str, $searchTerm);
    if ($pos === false)
        return false;
    else
        return true;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_resumen_v2") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_tipo_transaccion = $_POST["tipo_transaccion"];
	$local                     = $_POST["local"];
	$busqueda_cajero           = $_POST["cajero"];
	$estado_cierre             = $_POST["estado_cierre"];

	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$bono                      = $_POST["bono"];

	$proveedor             	   = $_POST["proveedor"];
	$num_transaccion           = $_POST["num_transaccion"];

	$tipo_saldo          	   = $_POST["tipo_saldo"];

	$caja_vip				   = $_POST["caja_vip"];

	$lugar				       = $_POST["lugar"];


	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
		";
		if((int)$busqueda_tipo_transaccion === 1) {// si es deposito
			$where_users_test="	
				AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
			";
		}
	}

	$where_fecha = "";
	if( (int) $tipo_busqueda === 1) { // POR CAJA
		$where_fecha = " 
			AND ( 
					(DATE ( caj.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
					OR 
					(DATE ( ce.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
				) 
		";
	} else if( (int) $tipo_busqueda === 2) { // POR FECHA DE ABONO
		$where_fecha = " 
			AND tra.registro_deposito IS NOT NULL 
			AND DATE(tra.registro_deposito) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra.registro_deposito) <= '".$busqueda_fecha_fin."' 
		";
	} else { // POR FECHA DE TRANSACCIÓN
		$where_fecha = " 
			AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."'
		";
	}

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		switch ((int)$busqueda_tipo_transaccion) {
			/*case 1:
				$where_tipo_transaccion=" 
				AND (
					# (tra.tipo_id = 1 and tra.estado = 1 and IFNULL(tra_2.estado, 0) != 3 and IFNULL(tra.caja_vip,0) IN (0,2)) 
					# OR (tra.tipo_id = 1 and IFNULL(tra_2.estado, 0) = 0 and IFNULL(tra.caja_vip,0) = 1) 
					#OR (tra.tipo_id = 26 and tra.estado = 1 and IFNULL(tra.caja_vip,0) = 1)
					(tra.tipo_id = 26 and tra.estado = 1)
				)";
				break;
			case 11:
				$where_tipo_transaccion=" 
				AND (
					#(tra.tipo_id = 9 and tra.estado = 2 and IFNULL(tra.caja_vip, 0) = 0)
		            #OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip, 0) = 1)
					(tra.tipo_id = 11 )
		         )";
				break;
			case 21:
				$where_tipo_transaccion=" AND tra.tipo_id = 21 and tra.estado = 2";
				break;
			case 28:
				$where_tipo_transaccion=" 
				AND (
					(tra.tipo_id = 28 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 0) 
					OR (tra.tipo_id = 29 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 1)
				)";
				break;*/
			case 41:
				$where_tipo_transaccion="
				AND ( tt.is_bonus = 1 and tra.tipo_id = 4 )";					
				break;
			case 51:
				$where_tipo_transaccion="
				AND ( tt.is_bonus = 1  and tra.tipo_id = 5)";					
				break;
			default:
				$where_tipo_transaccion=" AND tra.tipo_id =" . $busqueda_tipo_transaccion;
				break;
		}
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){ // Si es cajero
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_bono="";
	if( (int) $bono > 0 ){ // Si es cajero
		$where_bono=" AND tra.bono_id = " . $bono;
	}

	$where_proveedor="";
	if( (int) $proveedor > 0 ){
		$where_proveedor = " AND tra.api_id = " . $proveedor;
	}


	$where_num_transaccion = "";
	if(strlen($num_transaccion)>0){
		$where_num_transaccion = " AND tra.txn_id = '" . $num_transaccion."'";
	}

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	$where_lugar="";
	if( (int) $lugar === 2 ){  
		$where_lugar=" AND tra.caja_vip = 3 ";
	}else if ( (int) $lugar === 1 ) {
		$where_lugar=" AND IFNULL(tra.caja_vip, 0) IN (0,2) ";
	}else if ( (int) $lugar === 0 ) {
		$where_lugar="";
	}

	if(isset($_POST["search"]["value"])){		
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"			
			OR ttc.contact_type LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.telefono LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.tipo_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR cuen.cuenta_descripcion LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.comision_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.bono_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.total_recarga LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR usu_val.usuario LIKE "%'.$_POST["search"]["value"].'%" ';

			$diccionario = array(1=>'DEPOSITO',2=>'RECARGA WEB',3=>'RETORNO RECARGA WEB',4=>'APUESTA REGISTRADA',5=>'APUESTA PAGADA',9=>'RETIRO');
			foreach ($diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR tra.tipo_id LIKE "%'.$key.'%"';
				}
			}

			$tc_tipo_doc_diccionario = array(0=>'DNI',8=>'DNI',1=>'CARNE EXTRANJERIA',2=>'PASAPORTE');
			foreach ($tc_tipo_doc_diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR cli.tipo_doc LIKE "%'.$key.'%"';
				}
			}

			$nombre_busqueda .= ')';
		}else{
			$nombre_busqueda = "";
		}
	}

	$where_local="";
	if( (int) $local > 0 ){
		$where_local=" AND ( loc.cc_id='".$local."' OR ce_ssql.cc_id='".$local."' ) ";
		//$where_local=" AND tra.cc_id='".$local."' ";
	}

	$where_estado_cierre="";
	if( (int) $estado_cierre > 0 ){
		if( (int) $estado_cierre === 1 ){//Cierre activo
			$where_estado_cierre=" AND caj.id > 0 ";
		}
		if( (int) $estado_cierre === 2 ){//Cierre eliminado
			$where_estado_cierre=" AND ce.id > 0 ";
		}
	}

	$where_cuenta = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '' && $_POST["cuenta"] !== null) {
		$where_cuenta = " AND tra.cuenta_id IN ( " . implode(",",$_POST["cuenta"]) . " ) " ;
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(tra.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(tra.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$where_cajavip = "";
	if ($caja_vip == 1) {
		//$where_cajavip = " AND tra.caja_vip = 1 " ;
	}else{
		$where_cajavip = " AND IFNULL(tra.caja_vip, 0) = 0 " ;
	}
	
	// Cantidades
	$query_COUNT ="
		SELECT
			IFNULL(SUM(IF(tra.tipo_id IN (26), 1, 0)), 0) AS num_deposito, #cantidad de depósitos aprobados
			IFNULL(SUM(IF(tra.bono_id > 0 and tra.bono_monto > 0, 1, 0)), 0) AS num_bono,
			IFNULL(SUM(IF(tra.tipo_id=2, 1, 0)), 0) AS num_recarga,
			IFNULL(SUM(IF(tra.tipo_id=4, 1, 0)), 0) AS num_apuesta_generada,
			IFNULL(SUM(IF(tra.tipo_id=5, 1, 0)), 0) AS num_apuesta_pagada,
            IFNULL(SUM(IF(tra.tipo_id=26 and tra.estado = 1, tra.monto, 0)), 0) AS total_deposito,
			IFNULL(SUM(IF(tra.tipo_id=2, tra.monto, 0)), 0) AS total_recarga,
			IFNULL(SUM(IF(tra.tipo_id in (2,10) and tra.bono_id > 0, tra.bono_monto, 0)), 0) AS total_bono,
			IFNULL(SUM(IF(tra.tipo_id=4, tra.monto, 0)), 0) AS total_apuesta_generada,
			IFNULL(SUM(IF(tra.tipo_id=5, tra.monto, 0)), 0) AS total_apuesta_pagada,
			IFNULL(SUM(IF(tra.bono_id=1 and tra.tipo_id in (2, 10), tra.bono_monto, 0)), 0) AS total_bono_5pct,
			IFNULL(SUM(IF((tra.bono_id IN (126196,140219,140221) OR rb.nombre LIKE '%Bono Apuestas Deportivas%') and tra.tipo_id = 2, tra_b.bono_monto, 0)), 0) AS total_bono_apuesta_deportiva,
            IFNULL(SUM(IF((tra.bono_id IN (126197,140218,140220) OR rb.nombre LIKE '%Bono Casino%') and tra.tipo_id = 2, tra_b.bono_monto, 0)), 0) AS total_bono_casino,
            IFNULL(SUM(IF(tra.tipo_id = 11, tra.monto, 0)), 0) AS total_retiro,
            IFNULL(SUM(IF(tra.tipo_id = 14, tra.monto, 0)), 0) AS total_terminal_deposit,
            IFNULL(SUM(IF(tra.tipo_id = 4 AND tra.api_id = 4, tra.monto, 0)), 0) AS total_venta_bingo,
            IFNULL(SUM(IF(tra.tipo_id = 5 AND tra.api_id = 4, tra.monto, 0)), 0) AS total_pago_bingo,
            IFNULL(SUM(IF(tra.tipo_id = 17, tra.monto, 0)), 0) AS total_subir_balance,
            IFNULL(SUM(IF(tra.tipo_id = 18, tra.monto, 0)), 0) AS total_bajar_balance,
			IFNULL(SUM(IF(tra.tipo_id = 4 and tra.api_id IN (3,8), 1, 0)), 0) AS num_juegos_virtuales,
            IFNULL(SUM(IF(tra.tipo_id = 4 and tra.api_id IN (3,8), tra.monto, 0)), 0) AS total_juegos_virtuales,
			IFNULL(SUM(IF(tra.tipo_id = 5 and tra.api_id IN (3,8), 1, 0)), 0) AS num_juegos_virtuales_pagadas,
            IFNULL(SUM(IF(tra.tipo_id = 5 and tra.api_id IN (3,8), tra.monto, 0)), 0) AS total_juegos_virtuales_pagadas,
			IFNULL(SUM(IF(tra.tipo_id = 15, 1, 0)), 0) AS num_donacion_cancer,
            IFNULL(SUM(IF(tra.tipo_id = 15, tra.monto, 0)), 0) AS total_donacion_cancer,
			IFNULL(SUM(IF(tra.tipo_id = 32, 1, 0)), 0) AS num_pago_sorteo_mundial,
			IFNULL(SUM(IF(tra.tipo_id = 32, tra.monto, 0)), 0) AS total_sorteo_mundial,
			IFNULL(SUM(IF(tra.tipo_id = 33, 1, 0)), 0) AS num_tambo,
			IFNULL(SUM(IF(tra.tipo_id = 33, tra.monto, 0)), 0) AS total_tambo,
			IFNULL(SUM(IF(tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL, 1, 0)), 0) AS num_saldo_real,
			IFNULL(SUM(IF(tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL, tra.monto, 0)), 0) AS total_saldo_real,
			IFNULL(SUM(IF(tra.id_tipo_balance = 6, 1, 0)), 0) AS num_saldo_promocional,
			IFNULL(SUM(IF(tra.id_tipo_balance = 6, tra.monto, 0)), 0) AS total_saldo_promocional,
			IFNULL(SUM(IF(tra.tipo_id = 4 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket', 1, 0)), 0) AS num_v_torito_g,
			IFNULL(SUM(IF(tra.tipo_id = 4 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket', tra.monto, 0)), 0) AS total_v_torito_g,
            IFNULL(SUM(IF(tra.tipo_id = 4 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket_mm', 1, 0)), 0) AS num_v_torito_mm,
			IFNULL(SUM(IF(tra.tipo_id = 4 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket_mm', tra.monto, 0)), 0) AS total_v_torito_mm,
            IFNULL(SUM(IF(tra.tipo_id = 5 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket', 1, 0)), 0) AS num_p_torito_g,
			IFNULL(SUM(IF(tra.tipo_id = 5 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket', tra.monto, 0)), 0) AS total_p_torito_g,
            IFNULL(SUM(IF(tra.tipo_id = 5 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket_mm', 1, 0)), 0) AS num_p_torito_mm,
			IFNULL(SUM(IF(tra.tipo_id = 5 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket_mm', tra.monto, 0)), 0) AS total_p_torito_mm
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			LEFT JOIN tbl_televentas_recargas_bono rb ON tra.bono_id = rb.id
            LEFT JOIN tbl_televentas_clientes_transaccion tra_b ON tra.transaccion_id = tra_b.id
		WHERE
		".$tipos_and_estados."
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_local ." 
		".$where_estado_cierre ." 
		".$where_cuenta."
		".$where_cliente."
		".$where_bono."
		".$where_proveedor."
		".$where_num_transaccion."
		".$where_tipo_saldo."
		".$where_lugar."
		".$where_cajavip."
		";
	//echo $query_1;
	$result["QUERY_listar_transacciones_resumen_v2"] = $query_COUNT;
	$list_query_COUNT=$mysqli->query($query_COUNT);
	$list_transaccion_COUNT=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_COUNT->fetch_assoc()) {
			$list_transaccion_COUNT[]=$li;
		}
	}


	$query_cliente_unico ="
		SELECT
			COUNT(t.id) cant
		FROM (SELECT
				tra.cliente_id as id
			FROM
				tbl_televentas_clientes_transaccion tra
				LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id 
                LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			WHERE
			".$tipos_and_estados."
			AND tra.cliente_id > 0
			".$where_users_test ." 
			".$where_fecha ." 
			".$where_tipo_transaccion ." 
			".$where_cajero ." 
			".$where_local ." 
			".$where_estado_cierre ." 
			".$where_cuenta."
			".$where_cliente."
			".$where_bono."
			".$where_proveedor."
			".$where_num_transaccion."
			".$where_tipo_saldo."
			".$where_lugar."
			".$where_cajavip."
			GROUP BY tra.cliente_id) AS t
		";
	$result["query_cliente_unico"] = $query_cliente_unico;
	$list_query_client=$mysqli->query($query_cliente_unico);
	$list_transaccion_cliente_unico=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_client->fetch_assoc()) {
			$list_transaccion_cliente_unico[]=$li;
		}
	}

	$query_clientes_nuevos ="
		SELECT 
			COUNT(*) cant, t_2.is_new
		FROM 
			(SELECT 
				CASE WHEN t.count_cli = 0 THEN 1 ELSE 0 END is_new
		 	FROM 
		 		(SELECT 
					tra.created_at, 
					min(tra.id) min,
					(	SELECT COUNT(*) FROM tbl_televentas_clientes_transaccion tt 
						WHERE tt.cliente_id = c.id AND DATE(tt.created_at) != '" . $busqueda_fecha_inicio . "'
		    		) AS count_cli, 
		    		c.id
				FROM tbl_televentas_clientes c
				INNER JOIN tbl_televentas_clientes_transaccion tra ON tra.cliente_id = c.id
				LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id 
                LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
				WHERE
				".$tipos_and_estados."
				AND tra.cliente_id > 0
				AND LENGTH(IFNULL(c.num_doc,0)) > 4 
				".$where_users_test ." 
				".$where_fecha ." 
				".$where_tipo_transaccion ." 
				".$where_cajero ." 
				".$where_local ." 
				".$where_estado_cierre ." 
				".$where_cuenta."
				".$where_cliente."
				".$where_bono."
				".$where_proveedor."
				".$where_num_transaccion."
				".$where_tipo_saldo."
				".$where_lugar."
				".$where_cajavip."
				GROUP BY c.id
				ORDER BY count_cli asc) AS t
			) t_2
		GROUP BY t_2.is_new
		";
	$result["query_clientes_nuevos"] = $query_clientes_nuevos;
	$list_query_client=$mysqli->query($query_clientes_nuevos);
	$list_transaccion_cliente_nuevo=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_client->fetch_assoc()) {
			$list_transaccion_cliente_nuevo[]=$li;
		}
	}

	$query_clientes_nuevos_rango ="
		SELECT 
			count(*) cant
		FROM 
			(
				SELECT 
					MIN(tra.id),
					tra.cliente_id, 
					cli.nombre,
					tra.created_at AS dia
				FROM tbl_televentas_clientes_transaccion tra
				INNER JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
				WHERE
				".$tipos_and_estados."
				AND tra.cliente_id > 0
				AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
				".$where_users_test ." 
				".$where_tipo_transaccion ." 
				".$where_cajero ." 
				".$where_local ." 
				".$where_estado_cierre ." 
				".$where_cuenta."
				".$where_cliente."
				".$where_bono."
				".$where_proveedor."
				".$where_num_transaccion."
				".$where_tipo_saldo."
				".$where_lugar."
				".$where_cajavip."
				group by tra.cliente_id
				HAVING DATE(dia) BETWEEN '" . $busqueda_fecha_inicio . "' AND '" . $busqueda_fecha_fin . "'
			) AS t
		";
	$result["query_clientes_nuevos_rango"] = $query_clientes_nuevos_rango;
	$list_query_client_rango=$mysqli->query($query_clientes_nuevos_rango);
	$list_transaccion_cliente_nuevo_rango=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_client_rango->fetch_assoc()) {
			$list_transaccion_cliente_nuevo_rango[]=$li;
		}
	}


	if(count($list_transaccion_COUNT)===0){
		$result["http_code"] = 204;
		$result["status"] ="No hay transacciones.";
		$result["resumen"] =$list_transaccion_COUNT;
	} elseif(count($list_transaccion_COUNT)>0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["resumen"] = $list_transaccion_COUNT;
		$result["client_resumen"] = $list_transaccion_cliente_unico;
		$result["client_resumen_nuevo"] = $list_transaccion_cliente_nuevo;
		$result["num_clientes_nuevos"] = $list_transaccion_cliente_nuevo_rango[0]["cant"];
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
		$result["resumen"] = $list_transaccion_COUNT;
	}
}



if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_resumen_por_cierre_v2") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;


	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
	$busqueda_tipo_transaccion=$_POST["tipo_transaccion"];
	$local=$_POST["local"];
	$busqueda_cajero=$_POST["cajero"];
	$estado_cierre=$_POST["estado_cierre"];
	$tipo_saldo	= $_POST["tipo_saldo"];
	$lugar = $_POST["lugar"];

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	$where_lugar="";
	if( (int) $lugar === 2 ){  
		$where_lugar=" AND tra.caja_vip = 3 ";
	}else if ( (int) $lugar === 1 ) {
		$where_lugar=" AND IFNULL(tra.caja_vip, 0) IN (0,2) ";
	}else if ( (int) $lugar === 0 ) {
		$where_lugar="";
	}

	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="	
		AND tra.web_id not in ('3333200', '71938219') 
		AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
		if((int)$busqueda_tipo_transaccion===1){
			$where_users_test="	
				AND tra.user_id not in (1, 249, 250, 2572, 3028) 
			";
		}
	}


	$where_fecha_inicio=" AND DATE(tra.created_at)>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND DATE(tra.created_at)<= '".$busqueda_fecha_fin."'";
	$where_fecha = "
		AND ( 
				(DATE ( caj.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
				OR 
				(DATE ( ce.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
			)
	";

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tra.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}
	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])){		
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = 'AND (usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			)';
		}else{
			$nombre_busqueda = "";
		}
	}
	$limit = "";
	if(isset($_POST["length"]))
	{
		if($_POST["length"] != -1){
			$limit = 'LIMIT ' . (int)$_POST['start'] . ',' . (int)$_POST['length'];
		}	
	}

	$where_local="";
	if( (int) $local > 0 ){
		$where_local=" AND ( loc.cc_id='".$local."' OR ce_ssql.cc_id='".$local."' ) ";
		//$where_local=" AND tra.cc_id='".$local."' ";
	}

	$where_estado_cierre="";
	if( (int) $estado_cierre > 0 ){
		if( (int) $estado_cierre === 1 ){//Cierre activo
			$where_estado_cierre=" AND caj.id > 0 ";
		}
		if( (int) $estado_cierre === 2 ){//Cierre eliminado
			$where_estado_cierre=" AND ce.id > 0 ";
		}
	}

	$query_2="
		SELECT
		A.cajero,
		A.id_turno_cierre,
		A.turno_local,
		A.turno_cierre,
		A.turno_validacion,
		A.turno_fecha,
		A.fecha_registro,
		SUM( IF ( A.cod_tipo_transaccion = 1, 1, 0 ) ) cant_deposito,
		SUM( IF ( A.cod_tipo_transaccion = 1, A.monto, 0 ) ) total_deposito,
		SUM( A.comision_monto ) total_comision,
		SUM( IF ( A.cod_tipo_transaccion = 2, 1, 0 ) ) cant_recarga,
		SUM( IF ( A.bono_monto > 0 and A.bono_id > 0, 1, 0 ) ) cant_bono,
		SUM( IF ( A.cod_tipo_transaccion = 2, A.total_recarga, 0 ) ) total_recarga,
		SUM( IF ( A.cod_tipo_transaccion = 2 and A.bono_id = 1, A.bono_monto, 0 ) ) total_bono,
		SUM( IF ( A.cod_tipo_transaccion = 10 and A.bono_id = 126197, A.bono_monto, 0 ) ) total_bono_casino,
		SUM( IF ( A.cod_tipo_transaccion = 10 and A.bono_id = 126196, A.bono_monto, 0 ) ) total_bono_apuesta_deportiva,
		SUM( IF ( A.cod_tipo_transaccion = 4, 1, 0 ) ) cant_apuesta,
		SUM( IF ( A.cod_tipo_transaccion = 4, A.monto, 0 ) ) total_apuesta,
		SUM( IF ( A.cod_tipo_transaccion = 5, 1, 0 ) ) cant_apuesta_pagada,
		SUM( IF ( A.cod_tipo_transaccion = 5, A.monto, 0 ) ) total_apuesta_pagada 
		FROM
			(
			SELECT
				tra.tipo_id AS cod_tipo_transaccion,
				DATE( tra.created_at ) AS fecha_registro,
				UPPER( usu.usuario ) AS cajero,
				tra.monto AS monto,
				IFNULL( tra.comision_monto, 0 ) AS comision_monto,
				IFNULL( tra.bono_monto, 0 ) AS bono_monto,
				IFNULL( tra.total_recarga, 0 ) AS total_recarga,
				IFNULL( tra.bono_id, 0 ) AS bono_id,
				IFNULL( caj.id, ce.id ) AS id_turno_cierre,
				UPPER( IFNULL( loc.nombre, ce_ssql.nombre ) ) AS turno_local,
				IFNULL( caj.turno_id, ce.turno_id ) AS turno_cierre,
				IFNULL( caj.fecha_operacion, ce.fecha_operacion ) AS turno_fecha,
			IF
				( caj.turno_id > 0, 1, 2 ) turno_validacion 
			FROM
				tbl_televentas_clientes_transaccion tra
				LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
				LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
				LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
				LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
				LEFT JOIN tbl_caja_eliminados ce ON ce.id = tra.turno_id
				LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id
				LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			WHERE
		(
			(tra.tipo_id IN (1,2,4,5,14,15,17,18,19,20,36,37) and tra.estado=1) 
			OR (tra.tipo_id = 11 and tra.estado = 2)
			OR (tra.tipo_id = 21 and tra.estado = 2)
			OR (tra.tipo_id = 14 and tra.estado <> 3)
		) 
				".$where_users_test ." 
				".$where_fecha ." 
				".$where_tipo_transaccion ." 
				".$where_cajero ." 
				".$where_local ." 
				".$where_estado_cierre ." 
				".$nombre_busqueda ."
				".$where_tipo_saldo."
				".$where_lugar."
			) A 
		GROUP BY
			A.cajero,
			A.fecha_registro,
			A.id_turno_cierre,
			A.turno_local,
			A.turno_cierre,
			A.turno_fecha,
			A.turno_validacion 
		ORDER BY
			A.turno_fecha,
			A.turno_cierre,
			A.turno_local,
			A.fecha_registro
			".$limit;
		$list_query_2=$mysqli->query($query_2);
		$list_totales=array();
		while ($li_2=$list_query_2->fetch_assoc()) {
			$list_totales[]=$li_2;
		}
		if($mysqli->error){
			$result["consulta_error_2"] = $mysqli->error;
		}

		if(count($list_totales)===0){
			$result["http_code"] = 204;
			$result["status"] ="No hay transacciones.";
			$result["data"] =$list_totales;
		} elseif(count($list_totales)>0){
			$total_registros = count_all_data_listar_transacciones_por_cierre_v2(
				$where_users_test,
				$where_fecha_inicio,
				$where_fecha_fin,
				$where_tipo_transaccion,
				$where_cajero,
				$where_local,
				$where_estado_cierre,
				$nombre_busqueda);
		//	$result["draw"] =intval($_POST["draw"]);
			$result["recordsTotal"] =$total_registros;
			$result["recordsFiltered"] =$total_registros;
			$result["http_code"] = 200;
			$result["status"] ="ok";
			$result["data"] =$list_totales;
		} else {
			$result["http_code"] = 400;
			$result["status"] ="Ocurrió un error al consultar transacciones.";
		}

}




//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_tipo_transaccion = $_POST["tipo_transaccion"];
	$local                     = $_POST["local"];
	$busqueda_cajero           = $_POST["cajero"];
	$estado_cierre             = $_POST["estado_cierre"];

	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$bono             		   = $_POST["bono"];

	$proveedor             	   = $_POST["proveedor"];
	$num_transaccion           = $_POST["num_transaccion"];

	$tipo_saldo          	   = $_POST["tipo_saldo"];

	$caja_vip				   = $_POST["caja_vip"];
	$lugar				       = $_POST["lugar"];


	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	$where_fecha = "";
	if( (int) $tipo_busqueda === 1) { // POR CAJA
		$where_fecha = " 
			AND ( 
					(DATE ( caj.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
					OR 
					(DATE ( ce.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
				) 
		";
	} else if( (int) $tipo_busqueda === 2) { // POR FECHA DE ABONO
		$where_fecha = " 
			AND tra.registro_deposito IS NOT NULL 
			AND DATE(tra.registro_deposito) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra.registro_deposito) <= '".$busqueda_fecha_fin."' 
		";
	} else { // POR FECHA DE TRANSACCIÓN
		$where_fecha = " 
			AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."'
		";
	}

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		switch ((int)$busqueda_tipo_transaccion) {
			/*case 1:
				$where_tipo_transaccion=" 
				AND (
					#(tra.tipo_id = 1 and tra.estado = 1 and IFNULL(tra_2.estado, 0) != 3 and IFNULL(tra.caja_vip,0) IN (0,2)) 
					#OR (tra.tipo_id = 1 and ifnull(tra_2.estado,0) = 0 and ifnull(tra.caja_vip, 0) = 1) 
					#OR (tra.tipo_id = 26 and tra.estado = 1 and IFNULL(tra.caja_vip,0) = 1)
					(tra.tipo_id = 26 and tra.estado = 1)
				)";
				break;
			case 11:
				$where_tipo_transaccion=" 
				AND (
					#(tra.tipo_id = 9 and tra.estado = 2 and IFNULL(tra.caja_vip, 0) = 0)
		            #OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip, 0) = 1)
					(tra.tipo_id = 11 )
		         )";
				break;
			case 21:
				$where_tipo_transaccion=" AND tra.tipo_id = 21 and tra.estado = 2";
				break;
			case 28:
				$where_tipo_transaccion=" 
				AND (
					(tra.tipo_id = 28 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 0) 
					OR (tra.tipo_id = 29 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 1)
				)";
				break;*/
			case 41:
				$where_tipo_transaccion="
				AND ( tt.is_bonus = 1 and tra.tipo_id = 4 )";					
				break;
			case 51:
				$where_tipo_transaccion="
				AND ( tt.is_bonus = 1  and tra.tipo_id = 5)";					
				break;
			default:
				$where_tipo_transaccion=" AND tra.tipo_id =" . $busqueda_tipo_transaccion;
				break;
		}
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){ // Si es cajero
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_bono="";
	if( (int) $bono > 0 ){
		$where_bono = " AND tra.bono_id = " . $bono;
	}

	$where_proveedor="";
	if( (int) $proveedor > 0 ){
		$where_proveedor = " AND tra.api_id = " . $proveedor;
	}


	$where_num_transaccion = "";
	if(strlen($num_transaccion)>0){
		$where_num_transaccion = " AND tra.txn_id = '" . $num_transaccion."'";
	}

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	$where_local="";
	if( (int) $local > 0 ){
		$where_local=" AND ( loc.cc_id='".$local."' OR ce_ssql.cc_id='".$local."' ) ";
		//$where_local=" AND tra.cc_id='".$local."' ";
	}

	$where_lugar="";
	if( (int) $lugar === 2 ){  
		$where_lugar=" AND tra.caja_vip = 3 ";
	}else if ( (int) $lugar === 1 ) {
		$where_lugar=" AND IFNULL(tra.caja_vip, 0) IN (0,2) ";
	}else if ( (int) $lugar === 0 ) {
		$where_lugar="";
	}

	$where_estado_cierre="";
	if( (int) $estado_cierre > 0 ){
		if( (int) $estado_cierre === 1 ){//Cierre activo
			$where_estado_cierre=" AND caj.id > 0 ";
		}
		if( (int) $estado_cierre === 2 ){//Cierre eliminado
			$where_estado_cierre=" AND ce.id > 0 ";
		}
	}

	$where_cuenta = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '' && $_POST["cuenta"] !== null) {
		$where_cuenta = " AND tra.cuenta_id IN ( " . implode(",",$_POST["cuenta"]) . " ) " ;
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(tra.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(tra.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$where_cajavip = "";
	if ($caja_vip == 1) {
		//$where_cajavip = " AND tra.caja_vip = 1 " ;
	}else{
		$where_cajavip = " AND IFNULL(tra.caja_vip, 0) IN (0,2) " ;
	}


	// Lista
	$query_1 ="
		SELECT
			tra.cliente_id,
			UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) local_cierre,
			IFNULL(caj.turno_id, ce.turno_id) turno_cierre,
			tra.created_at fecha_hora_registro,
			IFNULL(tra.registro_deposito, '') registro_deposito,
			(CASE 
				WHEN tt.is_bonus in (1) THEN CONCAT(ttt.nombre, ' Gratis') 
				WHEN ttt.id = 1 THEN 'Depósito'
				WHEN ttt.id = 11 THEN 'Retiro'
				ELSE ttt.nombre 
			END) AS tipo_transaccion,
			IF(tra.id_tipo_balance = 6, 'Promocional', 'Real') tipo_saldo,
			IFNULL(ta.name, '') proveedor_nombre,
			IFNULL(ttc.contact_type, '') AS tipo_contacto,
			IFNULL(cli.telefono, '') telefono,
			(CASE 
				WHEN tra.tipo_id in (4,5,15,19,33,34) THEN IFNULL(tra.txn_id, '') 
				WHEN tra.tipo_id in (2) THEN IFNULL(tra.id, '') 
				ELSE '' 
			END) AS txn_id,
			CASE WHEN tra.tipo_id in (2) THEN  IFNULL(tra.txn_id, '') ELSE '' END AS operation_id,
			(CASE cli.tipo_doc WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE 'NO DEFINIDO' END) AS tipo_doc,
			IFNULL(cli.num_doc, '') num_doc,
			IFNULL( tra.web_id, '' ) web_id,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			IFNULL(tra.monto_deposito, 0) AS deposito,
			IFNULL(tra.comision_monto, 0) AS comision_monto,
			IFNULL(tra.monto, 0) AS monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(tra.total_recarga, 0) AS total_recarga,
			usu.usuario AS cajero,
			IFNULL(usu_val.usuario, '') AS validador,
			IFNULL(IF( tra.tipo_id = 10 and tra.bono_id = 126196 , tra.bono_monto, 0 ),0) AS bono_apuesta_deportiva,
			IFNULL(IF( tra.tipo_id = 10 and tra.bono_id = 126197, tra.bono_monto, 0 ),0) AS bono_casino,
			IFNULL(tra.observacion_cajero, '') observacion_cajero,
			IFNULL(usu_val_sup.usuario, '') AS validado_por
		FROM 
			tbl_televentas_clientes_transaccion tra 
			LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttt ON ttt.id = tra.tipo_id
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id 
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id 
			LEFT JOIN tbl_usuarios usu_val_sup ON usu_val_sup.id = tra.user_valid_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id 
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto 
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id 
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id 
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			LEFT JOIN tbl_televentas_recargas_bono rec_bon ON tra.bono_id = rec_bon.id 
			LEFT JOIN tbl_televentas_proveedor ta ON tra.api_id = ta.id 
			LEFT JOIN tbl_televentas_tickets tt ON tra.txn_id = tt.ticket_id AND tra.api_id = tt.proveedor_id
		WHERE
		".$tipos_and_estados."
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_bono." 
		".$where_proveedor." 
		".$where_num_transaccion." 
		".$where_tipo_saldo."
		".$where_local ." 
		".$where_lugar ."
		".$where_estado_cierre ." 
		".$where_cuenta." 
		".$where_cliente." 
		".$where_cajavip." 
		";
	//echo $query_1;
	//$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$result_data = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_query"] = $query_1;
		$result["error"] = 'Export error: ' . $mysqli->error;
		echo json_encode($result);
		exit;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$result_data[]=$li;
		}

		$headers = [
			"cliente_id" => "Id",
			"local_cierre" => "Local",
			"turno_cierre" => "Turno",
			"fecha_hora_registro" => "Registro",
			"registro_deposito" => "Fecha Depósito",
			"tipo_transaccion" => "Tipo",
			"tipo_saldo" => "Saldo",
			"proveedor_nombre" => "Proveedor",
			"tipo_contacto" => "Contacto",
			"telefono" => "Teléfono",
			"txn_id" => "ID-TX",
			"operation_id" => "OPERATION-ID",
			"tipo_doc" => "Tipo Documento",
			"num_doc" => "Número Documento",
			"web_id" => "WEB-ID",
			"cliente" => "Cliente",
			"cuenta" => "Cuenta",
			"deposito" => "Depósito S/",
			"comision_monto" => "Comisión S/",
			"monto" => "Real S/",
			"bono_monto" => "Bono 5% S/",
			"total_recarga" => "Recarga S/",
			"cajero" => "Promotor",
			"validador" => "Validador",
			"bono_apuesta_deportiva" => "Bono Apuesta Deportiva",
			"bono_casino" => "Bono Casino",
			"observacion_cajero" => "Observación Cajero",
			"validado_por" => "Validado Por"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_" . $date->getTimestamp();

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













//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CLIENTE
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_resumen_x_cliente") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];

	$where_fecha_inicio = " AND DATE(c.created_at) >= '".$busqueda_fecha_inicio."' ";
	$where_fecha_fin = " AND DATE(c.created_at) <= '".$busqueda_fecha_fin."' ";

	$where_users_test="";
	if((int)$area_id !== 6){
		$where_users_test="	
		AND IFNULL(c.web_id, '') not in ('3333200', '71938219') 
		AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	if(isset($_POST["search"]["value"])){		
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			c.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR c.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR l.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR c.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR DATE(c.created_at) LIKE "%'.$_POST["search"]["value"].'%"
			OR c.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR c.telefono LIKE "%'.$_POST["search"]["value"].'%" ';

			$tc_diccionario = array(3900=>'TELEVENTAS');
			foreach ($tc_diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR c.cc_id LIKE "%'.$key.'%"';
				}
			}
			$tc_tipo_doc_diccionario = array(0=>'DNI',8=>'DNI',1=>'CARNE EXTRANJERIA',2=>'PASAPORTE');
			foreach ($tc_tipo_doc_diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR c.tipo_doc LIKE "%'.$key.'%"';
				}
			}
			$nombre_busqueda .= ')';
		}else{
			$nombre_busqueda = "";
		}
	}

	$limit = "";
	if(isset($_POST["length"]))
	{
		if($_POST["length"] != -1){
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}
	$order = "";
	$column = array(
		1=>"c.created_at",
		2=>"l.nombre",
		3=>"c.web_id",
		4=>"c.telefono",
		5=>"c.tipo_doc",
		6=>"c.num_doc",
		5=>"c.nombre"
		);
	if(isset($_POST["order"]))
	{
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = 'ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order = 'ORDER BY 
			c.created_at ASC,
			c.nombre ASC,
			c.apellido_paterno ASC,
			c.apellido_materno ASC ';
		}
	}
	else
	{
		$order = 'ORDER BY 
			c.created_at ASC,
			c.nombre ASC,
			c.apellido_paterno ASC,
			c.apellido_materno ASC ';
	}

	// Obtener clientes
	$cmd_list_clients ="
		SELECT 
			c.id,
			CONCAT(c.id,'|',c.tipo_doc,'-',c.num_doc) cliente
		FROM tbl_televentas_clientes c
		INNER JOIN tbl_usuarios u ON c.created_user_id = u.id
		INNER JOIN tbl_televentas_clientes_transaccion tra ON c.id = tra.cliente_id
		INNER JOIN tbl_televentas_clientes_balance b ON c.id = b.cliente_id AND b.tipo_balance_id = 1
		LEFT JOIN tbl_locales l ON c.cc_id = l.cc_id
		WHERE
			(tra.tipo_id IN (1,2,10,26) and tra.estado=1)
			AND DATE(c.created_at) > '2021-08-01' 
			AND LENGTH(IFNULL(c.num_doc,0)) > 4 
			".$where_users_test ." 
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
		GROUP BY c.id";

	$result["consulta_query"] = $cmd_list_clients;
	$list_cmd_clients = $mysqli->query($cmd_list_clients);
	$list_registers = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_cmd_clients->fetch_assoc()) {
			$list_registers[] = $li["cliente"];
		}
	}

	//Exoneración de ludopatas
	$con_sic_host = env('DB_SIC_AT_HOST');
	$con_sic_db   = env('DB_SIC_AT_DATABASE');
	$con_sic_user = env('DB_SIC_AT_USERNAME');
	$con_sic_pass = env('DB_SIC_AT_PASSWORD');
	$mysqli_sic   = new mysqli($con_sic_host, $con_sic_user, $con_sic_pass, $con_sic_db, 3306);
	if (mysqli_connect_errno()) {
		printf("Conexion fallida SIC: %s\n", mysqli_connect_error());
		exit();
	}
	$mysqli_sic->query("SET CHARACTER SET utf8");

	$cmd_ludopatas = "
		SELECT 
			CONCAT(CASE WHEN desTipoDocu = 'DNI' THEN 0 WHEN desTipoDocu = 'CE' THEN 1 ELSE 2 END, '-', numDocu) cliente
		FROM tbl_mincetur_ludopatia";
	$list_cmd_ludopatas = $mysqli_sic->query($cmd_ludopatas);
	$list_ludopatas = array();
	if (!$mysqli->error) {
		while ($li = $list_cmd_ludopatas->fetch_assoc()) {
			$list_ludopatas[] = $li["cliente"];
		}
	}
	
	//INI COINCIDENCIAS
	$coincidencias = [];
	foreach ($list_registers as $item) {
		list($prefix, $value) = explode('|', $item);
		if (in_array($value, $list_ludopatas)) {
			$coincidencias[] = $prefix;
		}
	}

	//FIN COINCIDENCIAS
	
	$ids_ludopatas = implode(",", $coincidencias);
	//FIN LUDOPATAS

	$cant_clientes = count($list_registers);
	$cant_ludopatas = count($coincidencias);
	$cant_registros_show = $cant_clientes - $cant_ludopatas;
	$where_ludopatas = "";
	if(count($coincidencias) > 1){
		$where_ludopatas = " AND c.id NOT IN (" . $ids_ludopatas . ") ";
	}
	$query_1 ="
		SELECT 
			IFNULL(u.usuario, '') usuario_created,
			c.created_at fecha_hora_registro,
			DATE(MAX(tra.created_at)) fecha_ultimo_movimiento,
			TIME(MAX(tra.created_at)) hora_ultimo_movimiento,
			IFNULL( IF (c.cc_id = 3900, 'TELEVENTAS', l.nombre ), '' ) local_nombre,
			IFNULL(c.web_id, '') web_id,
			IFNULL(c.telefono, '' ) telefono,
			CASE 
				WHEN c.tipo_doc = 0 THEN 'DNI'
				WHEN c.tipo_doc = 1 THEN 'CE/PTP' 
				ELSE 'PASAPORTE' 
			END tipo_doc,
			c.tipo_doc AS tipo_doc_id,
			IFNULL(c.num_doc, '') num_doc,
			IFNULL(CONCAT( c.nombre, ' ', IFNULL( c.apellido_paterno, '' ), ' ', IFNULL( c.apellido_materno, '' ) ), '') cliente,
			IFNULL(c.fec_nac, '') fecha_nacimiento,
			SUM(IF(tra.tipo_id = 26 AND tra.estado = 1, tra.monto, 0)) total_deposito,
			SUM(IF(tra.tipo_id = 10, tra.monto, 0)) total_bono,
			SUM(IF(tra.tipo_id = 2 and tra.estado = 1, tra.monto, 0)) total_recarga,
			SUM(IF(tra.tipo_id = 26 AND tra.estado = 1, 1, 0)) cont_deposito,
			SUM(IF(tra.tipo_id = 10, 1, 0)) cont_bono,
			SUM(IF(tra.tipo_id = 2 and tra.estado = 1, 1, 0)) cont_recarga,
			IFNULL(b.balance, 0) balance
		FROM tbl_televentas_clientes c
		INNER JOIN tbl_usuarios u ON c.created_user_id = u.id
		INNER JOIN tbl_televentas_clientes_transaccion tra ON c.id = tra.cliente_id
		INNER JOIN tbl_televentas_clientes_balance b ON c.id = b.cliente_id AND b.tipo_balance_id = 1
		LEFT JOIN tbl_locales l ON c.cc_id = l.cc_id
		WHERE
			(tra.tipo_id IN (1,2,10,26) and tra.estado=1)
			AND DATE( c.created_at ) > '2021-08-01' 
			AND LENGTH(IFNULL(c.num_doc,0)) > 4 
			".$where_ludopatas . "
			".$where_users_test ." 
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
		GROUP BY c.id 
		".$order
		.$limit;

	$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}

	if (count($list_transaccion) == 0) {
		$result["http_code"] = 204;
		$result["status"] = "No hay transacciones.";
		$result["data"] = $list_transaccion;
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["data"] = $list_transaccion;
		$result["draw"]            = isset($_POST["draw"]) == true ?intval($_POST["draw"]):'';
		$result["recordsTotal"]    = $cant_registros_show;
		$result["recordsFiltered"] = $cant_registros_show;
		$result["data"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
		$result["data"] = $list_transaccion;
	}

	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_resumen_x_cliente_totales") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="
		AND IFNULL(c.web_id, '') not in ('3333200', '71938219') 
		AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}
	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];

	$where_fecha_inicio = " AND DATE(c.created_at) >= '".$busqueda_fecha_inicio."' ";
	$where_fecha_fin = " AND DATE(c.created_at) <= '".$busqueda_fecha_fin."' ";


	// Obtener clientes
	$cmd_list_clients ="
		SELECT 
			c.id,
			CONCAT(c.id,'|',c.tipo_doc,'-',c.num_doc) cliente
		FROM tbl_televentas_clientes c
		INNER JOIN tbl_usuarios u ON c.created_user_id = u.id
		INNER JOIN tbl_televentas_clientes_transaccion tra ON c.id = tra.cliente_id
		INNER JOIN tbl_televentas_clientes_balance b ON c.id = b.cliente_id AND b.tipo_balance_id = 1
		LEFT JOIN tbl_locales l ON c.cc_id = l.cc_id
		WHERE
			(tra.tipo_id IN (1,2,10,26) and tra.estado=1)
			AND DATE(c.created_at) > '2021-08-01' 
			AND LENGTH(IFNULL(c.num_doc,0)) > 4 
			".$where_users_test ." 
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
		GROUP BY c.id";

	$result["consulta_query"] = $cmd_list_clients;
	$list_cmd_clients = $mysqli->query($cmd_list_clients);
	$list_registers = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_cmd_clients->fetch_assoc()) {
			$list_registers[] = $li["cliente"];
		}
	}
	
	//Exoneración de ludopatas
	$con_sic_host = env('DB_SIC_AT_HOST');
	$con_sic_db   = env('DB_SIC_AT_DATABASE');
	$con_sic_user = env('DB_SIC_AT_USERNAME');
	$con_sic_pass = env('DB_SIC_AT_PASSWORD');
	$mysqli_sic   = new mysqli($con_sic_host, $con_sic_user, $con_sic_pass, $con_sic_db, 3306);
	if (mysqli_connect_errno()) {
		printf("Conexion fallida SIC: %s\n", mysqli_connect_error());
		exit();
	}
	$mysqli_sic->query("SET CHARACTER SET utf8");

	$cmd_ludopatas = "
		SELECT 
			CONCAT(CASE WHEN desTipoDocu = 'DNI' THEN 0 WHEN desTipoDocu = 'CE' THEN 1 ELSE 2 END, '-', numDocu) cliente
		FROM tbl_mincetur_ludopatia";
	$list_cmd_ludopatas = $mysqli_sic->query($cmd_ludopatas);
	$list_ludopatas = array();
	if (!$mysqli->error) {
		while ($li = $list_cmd_ludopatas->fetch_assoc()) {
			$list_ludopatas[] = $li["cliente"];
		}
	}
	
	//INI COINCIDENCIAS
	$coincidencias = [];
	foreach ($list_registers as $item) {
		list($prefix, $value) = explode('|', $item);
		if (in_array($value, $list_ludopatas)) {
			$coincidencias[] = $prefix;
		}
	}

	//FIN COINCIDENCIAS
	
	$ids_ludopatas = implode(",", $coincidencias);
	$where_ludopatas = "";
	if(count($coincidencias) > 1){
		$where_ludopatas = " AND c.id NOT IN (" . $ids_ludopatas . ") ";
	}

	$query_1 ="
		SELECT	
			SUM(IF(tra.tipo_id = 26 AND tra.estado = 1, tra.monto, 0)) total_deposito,
			SUM(IF(tra.tipo_id = 10, tra.monto, 0)) total_bono,
			SUM(IF(tra.tipo_id = 2 and tra.estado = 1, tra.monto, 0)) total_recarga,
			SUM(IF(tra.tipo_id = 26 AND tra.estado = 1, 1, 0)) cont_deposito,
			SUM(IF(tra.tipo_id = 10, 1, 0)) cont_bono,
			SUM(IF(tra.tipo_id = 2 and tra.estado = 1, 1, 0)) cont_recarga
		FROM tbl_televentas_clientes c
			INNER JOIN tbl_usuarios u ON c.created_user_id = u.id
			INNER JOIN tbl_televentas_clientes_transaccion tra ON c.id = tra.cliente_id
			INNER JOIN tbl_televentas_clientes_balance b ON c.id = b.cliente_id AND b.tipo_balance_id = 1
			LEFT JOIN tbl_locales l ON c.cc_id = l.cc_id
		WHERE
			(tra.tipo_id IN (1,2,10,26) and tra.estado=1)
			AND DATE( c.created_at ) > '2021-08-01' 
			AND LENGTH(IFNULL(c.num_doc,0)) > 4 
			".$where_ludopatas . "
			".$where_users_test ." 
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
		GROUP BY c.id";
	$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$list_transaccion=array();
	while ($li=$list_query->fetch_assoc()) {
		$list_transaccion[]=$li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	if(count($list_transaccion)==0){
		$result["http_code"] = 400;
		$result["status"] ="No hay transacciones.";
	} elseif(count($list_transaccion)>0){
		$result["http_code"] = 200;
		$result["status"] ="ok";
		$result["result"] =$list_transaccion;
	} else{
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CLIENTE --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_resumen_x_cliente_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];

	$where_fecha_inicio = " AND DATE(c.created_at) >= '".$busqueda_fecha_inicio."' ";
	$where_fecha_fin = " AND DATE(c.created_at) <= '".$busqueda_fecha_fin."' ";

	$where_users_test="";
	if((int)$area_id !== 6){
		$where_users_test="	
		AND IFNULL(c.web_id, '') not in ('3333200', '71938219') 
		AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	// Obtener clientes
	$cmd_list_clients ="
		SELECT 
			c.id,
			CONCAT(c.id,'|',c.tipo_doc,'-',c.num_doc) cliente
		FROM tbl_televentas_clientes c
		INNER JOIN tbl_usuarios u ON c.created_user_id = u.id
		INNER JOIN tbl_televentas_clientes_transaccion tra ON c.id = tra.cliente_id
		INNER JOIN tbl_televentas_clientes_balance b ON c.id = b.cliente_id AND b.tipo_balance_id = 1
		LEFT JOIN tbl_locales l ON c.cc_id = l.cc_id
		WHERE
			(tra.tipo_id IN (1,2,10,26) and tra.estado=1)
			AND DATE(c.created_at) > '2021-08-01' 
			AND LENGTH(IFNULL(c.num_doc,0)) > 4 
			".$where_users_test ." 
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
		GROUP BY c.id";

	$result["consulta_query"] = $cmd_list_clients;
	$list_cmd_clients = $mysqli->query($cmd_list_clients);
	$list_registers = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_cmd_clients->fetch_assoc()) {
			$list_registers[] = $li["cliente"];
		}
	}

	//Exoneración de ludopatas
	$con_sic_host = env('DB_SIC_AT_HOST');
	$con_sic_db   = env('DB_SIC_AT_DATABASE');
	$con_sic_user = env('DB_SIC_AT_USERNAME');
	$con_sic_pass = env('DB_SIC_AT_PASSWORD');
	$mysqli_sic   = new mysqli($con_sic_host, $con_sic_user, $con_sic_pass, $con_sic_db, 3306);
	if (mysqli_connect_errno()) {
		printf("Conexion fallida SIC: %s\n", mysqli_connect_error());
		exit();
	}
	$mysqli_sic->query("SET CHARACTER SET utf8");

	$cmd_ludopatas = "
		SELECT 
			CONCAT(CASE WHEN desTipoDocu = 'DNI' THEN 0 WHEN desTipoDocu = 'CE' THEN 1 ELSE 2 END, '-', numDocu) cliente
		FROM tbl_mincetur_ludopatia";
	$list_cmd_ludopatas = $mysqli_sic->query($cmd_ludopatas);
	$list_ludopatas = array();
	if (!$mysqli->error) {
		while ($li = $list_cmd_ludopatas->fetch_assoc()) {
			$list_ludopatas[] = $li["cliente"];
		}
	}
	
	//INI COINCIDENCIAS
	$coincidencias = [];
	foreach ($list_registers as $item) {
		list($prefix, $value) = explode('|', $item);
		if (in_array($value, $list_ludopatas)) {
			$coincidencias[] = $prefix;
		}
	}

	//FIN COINCIDENCIAS
	
	$ids_ludopatas = implode(",", $coincidencias);
	//FIN LUDOPATAS

	$cant_clientes = count($list_registers);
	$cant_ludopatas = count($coincidencias);
	$cant_registros_show = $cant_clientes - $cant_ludopatas;
	$where_ludopatas = "";
	if(count($coincidencias) > 1){
		$where_ludopatas = " AND c.id NOT IN (" . $ids_ludopatas . ") ";
	}

	$query_1 ="
		SELECT 
			c.created_at fecha_hora_registro,
			IFNULL(u.usuario, '') usuario_created,
			DATE(MAX(tra.created_at)) fecha_ultimo_movimiento,
			TIME(MAX(tra.created_at)) hora_ultimo_movimiento,
			IFNULL( IF (c.cc_id = 3900, 'TELEVENTAS', l.nombre ), '' ) local_nombre,
			IFNULL(c.web_id, '') web_id,
			IFNULL(c.telefono, '' ) telefono,
			CASE 
				WHEN c.tipo_doc = 0 THEN 'DNI'
				WHEN c.tipo_doc = 1 THEN 'CE/PTP' 
				ELSE 'PASAPORTE' 
			END tipo_doc,
			IFNULL(c.num_doc, '') num_doc,
			IFNULL(CONCAT( c.nombre, ' ', IFNULL( c.apellido_paterno, '' ), ' ', IFNULL( c.apellido_materno, '' ) ), '') cliente,
			IFNULL(c.fec_nac, '') fecha_nacimiento,
			SUM(IF(tra.tipo_id = 26 AND tra.estado = 1, tra.monto, 0)) total_deposito,
			SUM(IF(tra.tipo_id = 10, tra.monto, 0)) total_bono,
			SUM(IF(tra.tipo_id = 2 and tra.estado = 1, tra.monto, 0)) total_recarga,
			SUM(IF(tra.tipo_id = 26 AND tra.estado = 1, 1, 0)) cont_deposito,
			SUM(IF(tra.tipo_id = 10, 1, 0)) cont_bono,
			SUM(IF(tra.tipo_id = 2 and tra.estado = 1, 1, 0)) cont_recarga,
			IFNULL(b.balance, 0) balance
		FROM tbl_televentas_clientes c
		INNER JOIN tbl_usuarios u ON c.created_user_id = u.id
		INNER JOIN tbl_televentas_clientes_transaccion tra ON c.id = tra.cliente_id
		INNER JOIN tbl_televentas_clientes_balance b ON c.id = b.cliente_id AND b.tipo_balance_id = 1
		LEFT JOIN tbl_locales l ON c.cc_id = l.cc_id
		WHERE
			(tra.tipo_id IN (1,2,10,26) and tra.estado=1)
			AND DATE( c.created_at ) > '2021-08-01' 
			AND LENGTH(IFNULL(c.num_doc,0)) > 4 
			".$where_ludopatas . "
			".$where_users_test ." 
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
		GROUP BY c.id";

	$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$result_data = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}
	
		$headers = [
			"fecha_hora_registro" => "Registro",
			"usuario_created" => "Promotor",
			"fecha_ultimo_movimiento" => "Movimiento",
			"hora_ultimo_movimiento" => "Movimiento",
			"local_nombre" => "Local",
			"web_id" => "ID",
			"telefono" => "Telefono",
			"tipo_doc" => "Documento",
			"num_doc" => "Documento",
			"cliente" => "Cliente",
			"fecha_nacimiento" => "Nacimiento",
			"total_deposito" => "Depósito",
			"total_bono" => "Bono",
			"total_recarga" => "Recarga",
			"cont_deposito" => "Depositos",
			"cont_bono" => "Bonos",
			"cont_recarga" => "Recargas",
			"balance" => "Balance"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_clientes_" . $date->getTimestamp();

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

function count_all_data($where_users_test,$where_fecha_inicio,$where_fecha_fin,$nombre_busqueda)
{
	global $mysqli;
	$query_1 ="
	SELECT count(cnt.id) as cantidad FROM (
		SELECT
			tc.id
		FROM
			tbl_televentas_clientes tc
			LEFT JOIN tbl_locales l ON l.cc_id = tc.cc_id
			JOIN tbl_televentas_clientes_balance tcb ON tcb.cliente_id=tc.id
			LEFT JOIN (
			SELECT
				tct.cliente_id
			FROM
				tbl_televentas_clientes_transaccion tct 
			WHERE
			tct.estado = 1 
			AND LENGTH(IFNULL(tc.num_doc,0)) > 4 
			".$where_users_test ." 
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
			) A ON A.cliente_id = tc.id 
			WHERE
		DATE( tc.created_at ) > '2021-08-01'
		".$nombre_busqueda ."  
		GROUP BY
		tc.id,
		tc.created_at,
		tc.web_id,
		tc.telefono,
		tc.tipo_doc,
		tc.num_doc,
		tc.nombre,
		tc.apellido_paterno,
		tc.apellido_materno 
 	) AS cnt
		";

		// $query_1="
		// SELECT count(tc.id) AS cantidad FROM tbl_televentas_clientes tc
		// JOIN tbl_televentas_clientes_balance tcb ON tcb.cliente_id=tc.id
		// WHERE DATE( tc.created_at ) > '2021-08-01' 
		// ".$nombre_busqueda;

		$list_query=$mysqli->query($query_1);
		$count=0;
		while ($li=$list_query->fetch_assoc()) {
			$count=$li['cantidad'];
		}
	return (int)$count;
}


function count_all_data_listar_transacciones_por_cierre_v2(
	$where_users_test,
	$where_fecha_inicio,
	$where_fecha_fin,
	$where_tipo_transaccion,
	$where_cajero,
	$where_local,
	$where_estado_cierre,
	$nombre_busqueda)
	{
		global $mysqli;
		
		$query_1="
		select count(*) as cantidad from (SELECT
				count(*)
		FROM
			(
			SELECT
				tra.tipo_id AS cod_tipo_transaccion,
				DATE( tra.created_at ) AS fecha_registro,
				UPPER( usu.usuario ) AS cajero,
				tra.monto AS monto,
				IFNULL( tra.bono_monto, 0 ) AS bono_monto,
				IFNULL( tra.total_recarga, 0 ) AS total_recarga,
				IFNULL( caj.id, ce.id ) AS id_turno_cierre,
				UPPER( IFNULL( loc.nombre, ce_ssql.nombre ) ) AS turno_local,
				IFNULL( caj.turno_id, ce.turno_id ) AS turno_cierre,
				IFNULL( caj.fecha_operacion, ce.fecha_operacion ) AS turno_fecha,
			IF
				( caj.turno_id > 0, 1, 2 ) turno_validacion 
			FROM
				tbl_televentas_clientes_transaccion tra
				LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
				LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
				LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
				LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
				LEFT JOIN tbl_caja_eliminados ce ON ce.id = tra.turno_id
				LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id
				LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			WHERE
				tra.estado = 1 
				".$where_users_test ." 
				".$where_fecha_inicio ." 
				".$where_fecha_fin ." 
				".$where_tipo_transaccion ." 
				".$where_cajero ." 
				".$where_local ." 
				".$where_estado_cierre ." 
				".$nombre_busqueda ."
			) A 
		GROUP BY
			A.cajero,
			A.fecha_registro,
			A.id_turno_cierre,
			A.turno_local,
			A.turno_cierre,
			A.turno_fecha,
			A.turno_validacion 
		ORDER BY
			A.turno_fecha,
			A.turno_cierre,
			A.turno_local,
			A.fecha_registro ) AS temp";
			$list_query=$mysqli->query($query_1);
			$count=0;
			while ($li=$list_query->fetch_assoc()) {
				$count=$li['cantidad'];
			}
		return (int)$count;
	}
	
function count_all_data_listar_transacciones(
$where_users_test,
$where_fecha_inicio,
$where_fecha_fin,
$where_tipo_transaccion,
$where_cajero,
$where_local,
$where_estado_cierre,
$nombre_busqueda)
{
	global $mysqli;
	$query_1 ="
		SELECT
		count(*) AS cantidad
		FROM
		tbl_televentas_clientes_transaccion tra
		LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
		LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
		LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
		LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
		LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
		LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
		LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
		LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
		LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
		LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
		LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		WHERE
		tra.estado = 1 
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
		".$where_users_test ." 
		".$where_fecha_inicio ." 
		".$where_fecha_fin ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_local ." 
		".$where_estado_cierre ." 
		".$nombre_busqueda ;

		// $query_1="
		// SELECT count(tc.id) AS cantidad FROM tbl_televentas_clientes tc
		// JOIN tbl_televentas_clientes_balance tcb ON tcb.cliente_id=tc.id
		// WHERE DATE( tc.created_at ) > '2021-08-01' 
		// ".$nombre_busqueda;

		$list_query=$mysqli->query($query_1);
		$count=0;
		while ($li=$list_query->fetch_assoc()) {
			$count=$li['cantidad'];
		}
	return (int)$count;
}



//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CLIENTE --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_resumen_x_cliente_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
   

	$where_fecha_inicio=" AND tc.created_at >= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND tc.created_at <= date_add('".$busqueda_fecha_fin."', interval 1 day)";


	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="	
		AND IFNULL(tct.web_id, '') not in ('3333200', '71938219') 
		AND IFNULL(tct.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

 

	$query_1 ="
	SELECT
		tc.created_at fecha_hora_registro,
		IFNULL(u.usuario, '') usuario_created,
		IFNULL(DATE(B.created_at), '') fecha_ultimo_movimiento,
		IFNULL(TIME(B.created_at), '') hora_ultimo_movimiento,
		IFNULL( IF ( tc.cc_id = 3900, 'TELEVENTAS', l.nombre ), '' ) local_nombre,
		IFNULL( tc.web_id, '' ) web_id,
		IFNULL( tc.telefono, '' ) telefono,
		CASE 
			WHEN tc.tipo_doc = 0 THEN 'DNI'
			WHEN tc.tipo_doc = 1 THEN 'CE/PTP' 
			ELSE 'PASAPORTE' 
		END tipo_doc,
		IFNULL( tc.num_doc, '' ) num_doc,
		UPPER(
			IFNULL(
				CONCAT( tc.nombre, ' ', IFNULL( tc.apellido_paterno, '' ), ' ', IFNULL( tc.apellido_materno, '' ) ),
				'' 
			) 
		) AS cliente,
		IFNULL(DATE(tc.fec_nac), '') fecha_nacimiento,
		IFNULL( SUM( A.monto_deposito ), 0 ) total_deposito,
		IFNULL( SUM( A.monto_bono ), 0 ) total_bono,
		IFNULL( SUM( A.monto_recarga ), 0 ) total_recarga,
		IFNULL( SUM( A.cont_deposito ), 0 ) cont_deposito,
		IFNULL( SUM( A.cont_recarga ), 0 ) cont_recarga,
		IFNULL( SUM( A.cont_bono ), 0 ) cont_bono ,
		tcb.balance
	FROM
		tbl_televentas_clientes tc
		LEFT JOIN tbl_locales l ON l.cc_id = tc.cc_id
		LEFT JOIN tbl_usuarios u ON tc.created_user_id = u.id
		JOIN tbl_televentas_clientes_balance tcb ON tcb.cliente_id=tc.id
		LEFT JOIN (
		SELECT
			tct.cliente_id,
			tct.monto monto_deposito,
		IF
			( tct.bono_id > 0, tct.bono_monto, 0 ) monto_bono,
		IF
			( tct.tipo_id = 2, tct.total_recarga, 0 ) monto_recarga,
		IF
			( tct.tipo_id = 1, 1, 0 ) cont_deposito,
		IF
			( tct.tipo_id = 2, 1, 0 ) cont_recarga,
		IF
			( tct.bono_id > 0, 1, 0 ) cont_bono 
		FROM
			tbl_televentas_clientes_transaccion tct 
		WHERE
			tct.estado = 1 
			
		) A ON A.cliente_id = tc.id 
		LEFT JOIN (
			SELECT 
				MAX(tctra.created_at) created_at,
				tctra.cliente_id
			FROM tbl_televentas_clientes_transaccion tctra
			GROUP BY tctra.cliente_id
		) B ON tc.id = B.cliente_id
	WHERE
		DATE( tc.created_at ) > '2021-08-01' 
		".$where_users_test ." 
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
	GROUP BY
		tc.id,
		tc.created_at,
		tc.web_id,
		tc.telefono,
		tc.tipo_doc,
		tc.num_doc,
		tc.nombre,
		tc.apellido_paterno,
		tc.apellido_materno
	ORDER BY 
		tc.created_at ASC,
		tc.nombre ASC,
		tc.apellido_paterno ASC,
		tc.apellido_materno ASC
	";

	$list_query_cabecera = $mysqli->query($query_1);
	$row_count_cabecera = $list_query_cabecera->num_rows;




	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/descargas/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/descargas/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción



	$titulosColumnas_tres = array('Registro', 'Fecha Ultimo Movimiento', 'Hora Ultimo Movimiento', 'Local', 'WEB-ID', 'Telefono', 'Tipo de Documento', 'Numero de Documento', 'Nombre de Cliente', 'Fecha de Nacimiento', 'Total Monto', 'Total Bono', 'Total Recarga', 'Cantidad Depositos', 'Cantidad Bonos', 'Cantidad Recargas', 'Balance');
		// Se agregan los titulos del reporte
		$objPHPExcel->setActiveSheetIndex(0)
			   
			  
		->setCellValue('A1', $titulosColumnas_tres[0])  //Titulo de las columnas
		->setCellValue('B1', $titulosColumnas_tres[1])
		->setCellValue('C1', $titulosColumnas_tres[2])
		->setCellValue('D1', $titulosColumnas_tres[3])
		->setCellValue('E1', $titulosColumnas_tres[4])
		->setCellValue('F1', $titulosColumnas_tres[5])
		->setCellValue('G1', $titulosColumnas_tres[6])
		->setCellValue('H1', $titulosColumnas_tres[7])
		->setCellValue('I1', $titulosColumnas_tres[8])
		->setCellValue('J1', $titulosColumnas_tres[9])
		->setCellValue('K1', $titulosColumnas_tres[10])
		->setCellValue('L1', $titulosColumnas_tres[11])
		->setCellValue('M1', $titulosColumnas_tres[12])
		->setCellValue('N1', $titulosColumnas_tres[13])
		->setCellValue('O1', $titulosColumnas_tres[14])
		->setCellValue('P1', $titulosColumnas_tres[15])
		->setCellValue('Q1', $titulosColumnas_tres[16]);

			//Se agregan los datos a la lista del reporte
			$i = 2; //Numero de fila donde se va a comenzar a rellenar

 

			// INICIO DETALLE MOVILIDAD - SI EXISTE MOVILIDAD
			if ($row_count_cabecera > 0)
			{
				while ($reg = $list_query_cabecera->fetch_array()) 
				{
		
					 
				$fecha_hora_registro = $reg["fecha_hora_registro"];
				$fecha_ultimo_movimiento = $reg["fecha_ultimo_movimiento"];
				$hora_ultimo_movimiento = $reg["hora_ultimo_movimiento"];
				$local_nombre = $reg["local_nombre"];
				$web_id = $reg["web_id"];
				$telefono = $reg["telefono"];
				$tipo_doc = $reg["tipo_doc"];
				$num_doc = $reg["num_doc"];
				$cliente = $reg["cliente"];
				$fecha_nacimiento = $reg["fecha_nacimiento"];
				$total_deposito = $reg["total_deposito"];
				$total_bono = $reg["total_bono"]; 
				$total_recarga = $reg["total_recarga"]; 
				$cont_deposito = $reg["cont_deposito"]; 
				$cont_bono = $reg["cont_bono"]; 
				$cont_recarga = $reg["cont_recarga"]; 
				$balance = $reg["balance"]; 
					
				 
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$i, $fecha_hora_registro)
				->setCellValue('B'.$i, $fecha_ultimo_movimiento)
				->setCellValue('C'.$i, $hora_ultimo_movimiento)
				->setCellValue('D'.$i, $local_nombre)
				->setCellValue('E'.$i, $web_id)
				->setCellValue('F'.$i, $telefono)
				->setCellValue('G'.$i, $tipo_doc)
				->setCellValue('H'.$i, $num_doc)
				->setCellValue('I'.$i, $cliente)
				->setCellValue('J'.$i, $fecha_nacimiento)
				->setCellValue('K'.$i, $total_deposito)
				->setCellValue('L'.$i, $total_bono)
				->setCellValue('M'.$i, $total_recarga)
				->setCellValue('N'.$i, $cont_deposito)
				->setCellValue('O'.$i, $cont_bono)
				->setCellValue('P'.$i, $cont_recarga)
				->setCellValue('Q'.$i, $balance)
				;
		$i++;
			 
				}
			}

 
 

			$estiloNombresFilas = array(
				'font' => array(
					'name'      => 'Arial',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
					'color'     => array(
						'rgb' => '000000'
					)
				),
				'fill' => array(
					  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					  'color' => array(
							'rgb' => 'ffff00')
					),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				)
			);
		
		 
		
			 
		
			$estiloColoFondoAmarilloOscuro = array(
				'fill' => array(
				  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				  'color' => array(
						'rgb' => '08c9b6')
			  )
			);
			  
			$estiloTituloColumnas = array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => true,
					'size' => 10,
					'color' => array(
						'rgb' => '000000'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				)
			);
		
			$estiloCuerpoColumnas = array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => false,
					'size' => 10,
					'color' => array(
						'rgb' => '000000'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				)
			);
			  
			$estiloInformacion = new PHPExcel_Style();
			$estiloInformacion->applyFromArray( array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => false,
					'size' => 10,
					'color' => array(
						'rgb' => '000000'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				)
			));
		
		
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
	
			$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->applyFromArray($estiloTituloColumnas);	 

			$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->applyFromArray($estiloColoFondoAmarilloOscuro);
		  
		
			$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:Q".($i));
		
			 
		
			// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
			for($i = 'B'; $i <= 'Z'; $i++)
			{
				$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE); 
			}
		
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('CLIENTES TLV');
			  
			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			
			// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(0);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="Reporte de clientes tlv.xls');
			header('Cache-Control: max-age=0');
		
			$date = new DateTime();
			$file_title = "reporte_clientes_tlv_" . $date->getTimestamp() . "_" . $usuario_id;
		
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$excel_path = '/var/www/html/files_bucket/contratos/descargas/'.$file_title.'.xls';
			$excel_path_download = '/files_bucket/contratos/descargas/'.$file_title.'.xls';
		
			try 
			{
				$objWriter->save($excel_path);
			} 
			catch (Exception $e)
			{
				echo json_encode(["error" => $e]);
				exit;
			}
		
			echo json_encode(array(
				"ruta_archivo" => $excel_path_download,
				"estado_archivo" => 1
			));
			exit;
		
}



//*******************************************************************************************************************
//*******************************************************************************************************************
// tipo balance
//*******************************************************************************************************************
//*******************************************************************************************************************


if (isset($_POST["accion"]) && $_POST["accion"]==="listar_tbl_reporte_tipo_balance") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_tipo_transaccion = $_POST["tipo_transaccion"];
	$local                     = $_POST["local"];
	$busqueda_cajero           = $_POST["cajero"]; 
	$tipo_balance				   = $_POST["tipo_balance"];
	$motivo_balance				   = $_POST["motivo_balance"];
	$juego_balance				   = $_POST["juego_balance"];
	$tipo_saldo				   = $_POST["tipo_saldo"];
 
	$where_fecha_inicio = "";
	$where_fecha_fin = "";
	$where_tipo_transaccion="";
	$where_tipo_balance="";
	$where_motivo_balance="";
	$where_juego_balance="";
	$where_local_balance="";
	$where_cajero_balance="";
	$where_cajero_balance="";

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	if (!Empty($busqueda_fecha_inicio)) {
		$where_fecha_inicio .= "  AND tra.created_at >= '".$busqueda_fecha_inicio."'";
	}
	
	if (!Empty($busqueda_fecha_fin)) {
		$where_fecha_fin .= "  AND tra.created_at <= date_add('".$busqueda_fecha_fin."', interval 1 day)";
	}

	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tra.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	if (!Empty($tipo_balance) && $tipo_balance != "0") {
		$where_tipo_balance .= "  AND tra.id_tipo_balance = '".$tipo_balance."'";
	}

	if (!Empty($motivo_balance) && $motivo_balance != "0") {
		$where_motivo_balance .= "  AND tra.id_motivo_balance = '".$motivo_balance."'";
	}

	if (!Empty($juego_balance) && $juego_balance != "0") {
		$where_juego_balance .= "  AND tra.id_juego_balance = '".$juego_balance."'";
	}

	if (!Empty($local) && $local != "0") {	 
		$where_local_balance=" AND ( loc.cc_id='".$local."' OR ce_ssql.cc_id='".$local."' ) ";
	}

	if (!Empty($busqueda_cajero) && $busqueda_cajero != "0") {
		$where_cajero_balance .= "  AND tra.id_cajero_balance = '".$busqueda_cajero."'";
	}
  
	$query = "SELECT
		tra.id, 
		tra.tipo_id cod_tipo_transaccion,
		ttt.nombre tipo_transaccion,
		tra.created_at fecha_hora_registro, 
		IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente, 
		IFNULL(cli.num_doc, '') num_doc,
		IFNULL(cli.web_id, '') web_id,
		IFNULL(tra.monto, 0) AS monto,	 
		UPPER(IFNULL(loc.nombre, IFNULL(ce_ssql.nombre, ''))) local_cierre,
		IFNULL(caj.id, IFNULL(ce.id, '')) id_turno_cierre,
		IFNULL(caj.turno_id, IFNULL(ce.turno_id, '')) turno_cierre, 
		IFNULL(tra.observacion_cajero, '') observacion_cajero,
		IFNULL(mtv.motivo, '') as motivo,
		IFNULL(jg.nombre, '') as juego_balance,
		IFNULL(sp_bl.nombre, '') as supervisor_balance,
		tra.id_tipo_balance,
		IFNULL(tra.id_tj_balance, '') as id_trans_juego,
		IFNULL(tra.observacion_cajero, '') as observacion_cajero,
		IFNULL(CONCAT(
		IF
			( LENGTH( pl_bl.apellido_paterno ) > 0, CONCAT( UPPER( pl_bl.apellido_paterno ), ' ' ), '' ),
		IF
			( LENGTH( pl_bl.apellido_materno ) > 0, CONCAT( UPPER( pl_bl.apellido_materno ), ' ' ), '' ),
		IF
			( LENGTH( pl_bl.nombre ) > 0, UPPER( pl_bl.nombre ), '' ) 
		), '') AS cajero_balance  
	FROM
		tbl_televentas_clientes_transaccion tra
		LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttt ON ttt.id = tra.tipo_id
		LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id  
		LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id 
		LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
		LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
		LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id  
		LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
		LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 	 
		LEFT JOIN tbl_televentas_motivo_balances mtv ON mtv.id = tra.id_motivo_balance
		LEFT JOIN tbl_televentas_tipo_juego jg ON jg.id = tra.id_juego_balance
		LEFT JOIN tbl_personal_apt sp_bl ON sp_bl.id = tra.id_supervisor_balance
		LEFT JOIN tbl_usuarios usu_bl ON usu_bl.id = tra.id_cajero_balance
		LEFT JOIN tbl_personal_apt pl_bl ON pl_bl.id = usu_bl.personal_id
	WHERE
	(
		(tra.tipo_id IN (1,2,4,5,14,15,17,18,19,20,36,37) and tra.estado=1) 
		OR (tra.tipo_id = 11 and tra.estado = 2)
		OR (tra.tipo_id = 21 and tra.estado = 2)
		OR (tra.tipo_id = 14 and tra.estado <> 3)
		OR (tra.tipo_id = 32)
		OR (tra.tipo_id = 33)
	) AND tra.tipo_id in (17, 18)
	AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
	 
	".$where_fecha_inicio ." 
	".$where_fecha_fin ." 
	".$where_tipo_transaccion ." 
	".$where_tipo_balance ." 
	".$where_motivo_balance ." 
	".$where_juego_balance ." 
	".$where_local_balance ." 
	".$where_cajero_balance ." 
	".$where_tipo_saldo."

	 ORDER BY tra.id asc 
	";
	$list_query = $mysqli->query($query);
	$nombre_tipo_balance = '';
	$html = '
	<label style="font-size: 18px;color: navy;">TIPO BALANCE</label>
	<br>
	<table id="SecRepTel_rep_tbl_tipo_balance" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">#</th> 
				<th class="text-center">Fecha Hora Registro</th>
				<th class="text-center">Tipo Balance</th>
				<th class="text-center">Modo de Balance</th>
				<th class="text-center">Motivo</th>
				<th class="text-center">Juego</th>
				<th class="text-center">ID Transacción</th>
				<th class="text-center">Cliente</th>
				<th class="text-center">Num Documento</th>
				<th class="text-center">Id Cliente</th>
				<th class="text-center">Local</th>
				<th class="text-center">Supervisor</th>
				<th class="text-center">Promotor</th>
				<th class="text-center">Monto</th> 	
				<th class="text-center">Observación</th>				 
			</tr>
		</thead>
		<tbody>';
		$i =0;
		
	while($li=$list_query->fetch_assoc()){
	 $i = $i +1;
	 
	 if($li['id_tipo_balance'] == 4){
		$nombre_tipo_balance = 'Balance NO RETIRABLE';
	 }elseif($li['id_tipo_balance'] == 5){
		$nombre_tipo_balance = 'Balance RETIRABLE';
	 }elseif($li['id_tipo_balance'] == 6){
		$nombre_tipo_balance = 'Bono AT';
	 }else {
		$nombre_tipo_balance = '';
	 }
		$html .='
			<tr>
				<td class="text-center">'.$i.'</td>
				<td class="text-center">'.$li['fecha_hora_registro'].'</td>
				<td class="text-center">'.$nombre_tipo_balance.'</td>			
				<td class="text-center">'.$li['tipo_transaccion'].'</td>
				<td class="text-center">'.$li['motivo'].'</td>
				<td class="text-center">'.$li['juego_balance'].'</td>
				<td class="text-center">'.$li['id_trans_juego'].'</td>
				<td class="text-center">'.$li['cliente'].'</td>
				<td class="text-center">'.$li['num_doc'].'</td>
				<td class="text-center">'.$li['web_id'].'</td>
				<td class="text-center">'.$li['local_cierre'].'</td>
				<td class="text-center">'.$li['supervisor_balance'].'</td>
				<td class="text-center">'.$li['cajero_balance'].'</td>
				<td class="text-center">'.$li['monto'].'</td> 
				<td class="text-center">'.$li['observacion_cajero'].'</td> 
			</tr>
			';
	}
	$html .='
		</tbody>
	</table>';
	
	$result["status"] = 200;
	$result["result"] = $html;
	echo json_encode($result);
	exit();
	 
}





//*******************************************************************************************************************
//*******************************************************************************************************************
// CUADRE DE CAJA
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_tbl_reporte_venta") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_tipo_transaccion = $_POST["tipo_transaccion"];
	$local                     = $_POST["local"];
	$busqueda_cajero           = $_POST["cajero"];
	$estado_cierre             = $_POST["estado_cierre"];

	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$bono                      = $_POST["bono"];

	$proveedor             	   = $_POST["proveedor"];
	$num_transaccion           = $_POST["num_transaccion"];
	$tipo_saldo          	   = $_POST["tipo_saldo"];
	$caja_vip				   = $_POST["caja_vip"];


	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219')			 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
		";
		if((int)$busqueda_tipo_transaccion === 1) {// si es deposito
			$where_users_test="			  
				AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
			";
		}
	}

	$where_fecha = "";
	if( (int) $tipo_busqueda === 1) { // POR CAJA
		$where_fecha = " 
			AND ( 
					(DATE ( caj.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
					OR 
					(DATE ( ce.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
				) 
		";
	} else if( (int) $tipo_busqueda === 2) { // POR FECHA DE ABONO
		$where_fecha = " 
			AND tra.registro_deposito IS NOT NULL 
			AND DATE(tra.registro_deposito) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra.registro_deposito) <= '".$busqueda_fecha_fin."' 
		";
	} else { // POR FECHA DE TRANSACCIÓN
		$where_fecha = " 
			AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."'
		";
	}

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tra.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){ // Si es cajero
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_bono="";
	if( (int) $bono > 0 ){ // Si es cajero
		$where_bono=" AND tra.bono_id = " . $bono;
	}

	$where_proveedor="";
	if( (int) $proveedor > 0 ){
		$where_proveedor = " AND tra.api_id = " . $proveedor;
	}


	$where_num_transaccion = "";
	if(strlen($num_transaccion)>0){
		$where_num_transaccion = " AND tra.txn_id = '" . $num_transaccion."'";
	}

	if(isset($_POST["search"]["value"])){		
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"			
			OR ttc.contact_type LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.telefono LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.tipo_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR cuen.cuenta_descripcion LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.comision_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.bono_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.total_recarga LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR usu_val.usuario LIKE "%'.$_POST["search"]["value"].'%" ';

			$diccionario = array(1=>'DEPOSITO',2=>'RECARGA WEB',3=>'RETORNO RECARGA WEB',4=>'APUESTA REGISTRADA',5=>'APUESTA PAGADA',9=>'RETIRO');
			foreach ($diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR tra.tipo_id LIKE "%'.$key.'%"';
				}
			}

			$tc_tipo_doc_diccionario = array(0=>'DNI',8=>'DNI',1=>'CARNE EXTRANJERIA',2=>'PASAPORTE');
			foreach ($tc_tipo_doc_diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR cli.tipo_doc LIKE "%'.$key.'%"';
				}
			}

			$nombre_busqueda .= ')';
		}else{
			$nombre_busqueda = "";
		}
	}

	$where_local="";
	if( (int) $local > 0 ){
		$where_local=" AND ( loc.cc_id='".$local."' OR ce_ssql.cc_id='".$local."' ) ";
		//$where_local=" AND tra.cc_id='".$local."' ";
	}

	$where_estado_cierre="";
	if( (int) $estado_cierre > 0 ){
		if( (int) $estado_cierre === 1 ){//Cierre activo
			$where_estado_cierre=" AND caj.id > 0 ";
		}
		if( (int) $estado_cierre === 2 ){//Cierre eliminado
			$where_estado_cierre=" AND ce.id > 0 ";
		}
	}

	$where_cuenta = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '' && $_POST["cuenta"] !== null) {
		$where_cuenta = " AND tra.cuenta_id IN ( " . implode(",",$_POST["cuenta"]) . " ) " ;
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(tra.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(tra.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$where_cajavip = "";
	if ($caja_vip == 1) {
		//$where_cajavip = " AND tra.caja_vip = 1 " ;
	}else{
		$where_cajavip = " AND IFNULL(tra.caja_vip, 0) = 0 " ;
	}
	
	// Cantidades
	$query_venta ="
		SELECT
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (1,2,5) and tra.estado = 1), tra.monto, 0 ) ), 0 ) AS apuesta_dep_total,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (1,2,5) and tra.estado = 3), tra.monto, 0 ) ), 0 ) AS apuesta_dep_anulada_total,
			IFNULL( SUM( IF ( (tra.tipo_id = 5 and tra.api_id in (1,2,5) and tra.estado = 1), tra.monto, 0 ) ), 0 ) AS apuesta_dep_pagada_total,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (4) and tra.estado = 1), tra.monto, 0 ) ), 0 ) AS apuesta_bingo_total,
			IFNULL( SUM( IF ( (tra.tipo_id = 5 and tra.api_id in (4) and tra.estado = 1), tra.monto, 0 ) ), 0 ) AS apuesta_bingo_pagada_total,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (3) and tra.estado = 1), tra.monto, 0 ) ), 0 ) AS apuesta_jv_total,
			IFNULL( SUM( IF ( (tra.tipo_id = 5 and tra.api_id in (3) and tra.estado = 1), tra.monto, 0 ) ), 0 ) AS apuesta_jv_pagada_total,
			IFNULL( SUM( IF ( tra.tipo_id = 2, tra.monto, 0 ) ), 0 ) AS recarga_total,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket'), tra.monto, 0 ) ), 0 ) AS apuesta_torito_g_total,
            IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket_mm'), tra.monto, 0 ) ), 0 ) AS apuesta_torito_mm_total,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (1,2,5) and tra.estado = 1), 1, 0 ) ), 0 ) AS apuesta_dep_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (1,2,5) and tra.estado = 3), 1, 0 ) ), 0 ) AS apuesta_dep_anulada_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 5 and tra.api_id in (1,2,5) and tra.estado = 1), 1, 0 ) ), 0 ) AS apuesta_dep_pagada_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (4) and tra.estado = 1), 1, 0 ) ), 0 ) AS apuesta_bingo_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 5 and tra.api_id in (4) and tra.estado = 1), 1, 0 ) ), 0 ) AS apuesta_bingo_pagada_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (3,8) and tra.estado = 1), 1, 0 ) ), 0 ) AS apuesta_jv_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 5 and tra.api_id in (3,8) and tra.estado = 1), 1, 0 ) ), 0 ) AS apuesta_jv_pagada_cant,
			IFNULL( SUM( IF ( tra.tipo_id = 2, 1, 0 ) ), 0 ) AS recarga_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket'), 1, 0 ) ), 0 ) AS apuesta_torito_g_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 4 and tra.api_id in (9) and tra.estado = 1 and tra.num_operacion='purchaseticket_mm'), 1, 0 ) ), 0 ) AS apuesta_torito_mm_cant
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		WHERE
		(
			(tra.tipo_id IN (1,2,4,5,14,17,18,19,20,36,37) and tra.estado=1) 
			OR (tra.tipo_id = 11 and tra.estado = 2)
			OR (tra.tipo_id = 21 and tra.estado = 2)
			OR (tra.tipo_id = 14 and tra.estado <> 3)
		) 
		".$where_users_test ." 
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
		".$where_fecha ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_local ." 
		".$where_estado_cierre ." 
		".$where_cuenta."
		".$where_cliente."
		".$where_bono."
		".$where_proveedor."
		".$where_num_transaccion."
		".$where_tipo_saldo."
		".$where_cajavip."
		";
	//echo $query_1;
	//$result["QUERY_listar_transacciones_resumen_v2"] = $query_venta;
	$list_query_venta=$mysqli->query($query_venta);
	$list_res_venta=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_venta->fetch_assoc()) {
			$list_res_venta[]=$li;
		}
	}


	// Ingresos y salidas
	$query_ingresos_salidas ="
		SELECT
			IFNULL( SUM( IF ( (tra.tipo_id = 1), 1, 0 ) ), 0 ) AS deposito_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 2), 1, 0 ) ), 0 ) AS recarga_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 4), 1, 0 ) ), 0 ) AS terminal_cant,
			IFNULL( SUM( IF ( tra.tipo_id = 1, tra.monto, 0 ) ), 0 ) AS deposito_total,
			IFNULL( SUM( IF ( tra.tipo_id = 2, tra.monto, 0 ) ), 0 ) AS recarga_total,
			IFNULL( SUM( IF ( tra.tipo_id = 14, tra.monto, 0 ) ), 0 ) AS terminal_total
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		WHERE
		(
			(tra.tipo_id = 1) or 
			(tra.tipo_id = 2 and tra.estado = 1) or 
			(tra.tipo_id = 14 and tra.estado = 1)
		) 
		".$where_users_test ." 
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
		".$where_fecha ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_local ." 
		".$where_estado_cierre ." 
		".$where_cuenta."
		".$where_cliente."
		".$where_bono."
		".$where_proveedor."
		".$where_num_transaccion."
		".$where_tipo_saldo."
		".$where_cajavip."
		";
	//$result["QUERY_listar_transacciones_resumen_v2"] = $query_COUNT;
	$list_query_ingresos_salidas=$mysqli->query($query_ingresos_salidas);
	$list_res_ingresos_salidas=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_ingresos_salidas->fetch_assoc()) {
			$list_res_ingresos_salidas[]=$li;
		}
	}

	if(count($list_res_venta)>0 || count($list_res_ingresos_salidas)>0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$list_res_venta_cant = (int)$list_res_venta[0]["apuesta_dep_cant"] + (int)$list_res_venta[0]["apuesta_dep_pagada_cant"] + 
								(int)$list_res_venta[0]["apuesta_dep_anulada_cant"] + (int)$list_res_venta[0]["apuesta_bingo_cant"] + 
								(int)$list_res_venta[0]["apuesta_bingo_pagada_cant"] + (int)$list_res_venta[0]["apuesta_jv_cant"] + 
								(int)$list_res_venta[0]["apuesta_jv_pagada_cant"] + (int)$list_res_venta[0]["recarga_cant"] + 
								(int)$list_res_venta[0]["apuesta_torito_g_cant"] + 
								(int)$list_res_venta[0]["apuesta_torito_mm_cant"];
		$list_res_venta_total = (double)$list_res_venta[0]["apuesta_dep_total"] - (double)$list_res_venta[0]["apuesta_dep_pagada_total"] - 
								(double)$list_res_venta[0]["apuesta_dep_anulada_total"] + (double)$list_res_venta[0]["apuesta_bingo_total"] - 
								(double)$list_res_venta[0]["apuesta_bingo_pagada_total"] + (double)$list_res_venta[0]["apuesta_jv_total"] - 
								(double)$list_res_venta[0]["apuesta_jv_pagada_total"] + (double)$list_res_venta[0]["recarga_total"]  + (double)$list_res_venta[0]["apuesta_torito_g_total"]  + (double)$list_res_venta[0]["apuesta_torito_mm_total"];
		$result["res_reporte_venta"] = '
			<thead>
				<tr style="background-color: #548235;">
					<th colspan="3" class="text-center" style="color: white;font-size: 16px;">Reporte de venta</th>
				</tr>
				<tr style="background-color: #c6efce;">
					<th class="text-center" class="text-center" style="color: #548235;">DESCRIPCIÓN</th>
					<th class="text-center" class="text-center" style="color: #548235;">#</th>
					<th class="text-center" class="text-center" style="color: #548235;">MONTO</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Apuestas deportivas</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_dep_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_dep_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Pago de apuestas Deportivas</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_dep_pagada_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_dep_pagada_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Apuestas anuladas</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_dep_anulada_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_dep_anulada_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Venta Bingo</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_bingo_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_bingo_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Pago Bingo</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_bingo_pagada_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_bingo_pagada_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Venta Juegos Virtuales</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_jv_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_jv_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Pago Juegos Virtuales</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_jv_pagada_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_jv_pagada_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Recarga Web</td>
					<td class="text-right">'.$list_res_venta[0]["recarga_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["recarga_total"], 2, ".", ",").'</td>
				</tr>

				<tr>
					<td>Torito Ganadazo</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_torito_g_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_torito_g_total"], 2, ".", ",").'</td>
				</tr>

				<tr>
					<td>Torito Megamillions</td>
					<td class="text-right">'.$list_res_venta[0]["apuesta_torito_mm_cant"].'</td>
					<td class="text-right">'.number_format($list_res_venta[0]["apuesta_torito_mm_total"], 2, ".", ",").'</td>
				</tr>

				<tr style="font-weight: bold;">
					<td>Total Transacciones</td>
					<td class="text-right">'.$list_res_venta_cant.'</td>
					<td class="text-right">'.number_format($list_res_venta_total, 2, ".", ",").'</td>
				</tr>
			</tbody>
			';
		$result["res_ingresos_salidas"] = '
			<thead>
				<tr style="background-color: #bf9000;">
					<th colspan="3" class="text-center" style="color: white;font-size: 16px;">Reporte de Ingresos y salidas</th>
				</tr>
				<tr style="background-color: #ffd966;">
					<th class="text-center" class="text-center" style="color: #bf9000;">DESCRIPCIÓN</th>
					<th class="text-center" class="text-center" style="color: #bf9000;">#</th>
					<th class="text-center" class="text-center" style="color: #bf9000;">MONTO</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Total de Depósitos (transferencias)</td>
					<td class="text-right">'.$list_res_ingresos_salidas[0]["deposito_cant"].'</td>
					<td class="text-right">'.number_format($list_res_ingresos_salidas[0]["deposito_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Total de Retiro</td>
					<td class="text-right">'.$list_res_ingresos_salidas[0]["recarga_cant"].'</td>
					<td class="text-right">'.number_format($list_res_ingresos_salidas[0]["recarga_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Terminal Deposit In</td>
					<td class="text-right">'.$list_res_ingresos_salidas[0]["terminal_cant"].'</td>
					<td class="text-right">'.number_format($list_res_ingresos_salidas[0]["terminal_total"], 2, ".", ",").'</td>
				</tr>
			</tbody>
			';
	} else {
		$result["http_code"] = 400;
		$result["status"] ="No hay transacciones.";
		$result["res_reporte_venta"] = '';
		$result["res_ingresos_salidas"] = '';
	}
}






//*******************************************************************************************************************
//*******************************************************************************************************************
// CUADRE DE CAJA
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_res_prevencion") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_tipo_transaccion = $_POST["tipo_transaccion"];
	$local                     = $_POST["local"];
	$busqueda_cajero           = $_POST["cajero"];
	$estado_cierre             = $_POST["estado_cierre"];

	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$bono                      = $_POST["bono"];

	$proveedor             	   = $_POST["proveedor"];
	$num_transaccion           = $_POST["num_transaccion"];
	$tipo_saldo          	   = $_POST["tipo_saldo"];

	$caja_vip				   = $_POST["caja_vip"];


	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
		if((int)$busqueda_tipo_transaccion === 1) {// si es deposito
			$where_users_test="	
				AND tra.user_id not in (1, 249, 250, 2572, 3028) 
			";
		}
	}

	$where_fecha = "";
	if( (int) $tipo_busqueda === 1) { // POR CAJA
		$where_fecha = " 
			AND ( 
					(DATE ( caj.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
					OR 
					(DATE ( ce.fecha_operacion ) >= '".$busqueda_fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$busqueda_fecha_fin."' ) 
				) 
		";
	} else if( (int) $tipo_busqueda === 2) { // POR FECHA DE ABONO
		$where_fecha = " 
			AND tra.registro_deposito IS NOT NULL 
			AND DATE(tra.registro_deposito) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra.registro_deposito) <= '".$busqueda_fecha_fin."' 
		";
	} else { // POR FECHA DE TRANSACCIÓN
		$where_fecha = " 
			AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."'
		";
	}

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tra.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){ // Si es cajero
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_bono="";
	if( (int) $bono > 0 ){ // Si es cajero
		$where_bono=" AND tra.bono_id = " . $bono;
	}

	$where_proveedor="";
	if( (int) $proveedor > 0 ){
		$where_proveedor = " AND tra.api_id = " . $proveedor;
	}


	$where_num_transaccion = "";
	if(strlen($num_transaccion)>0){
		$where_num_transaccion = " AND tra.txn_id = '" . $num_transaccion."'";
	}

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	if(isset($_POST["search"]["value"])){		
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"			
			OR ttc.contact_type LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.telefono LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.tipo_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR cuen.cuenta_descripcion LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.comision_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.bono_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.total_recarga LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR usu_val.usuario LIKE "%'.$_POST["search"]["value"].'%" ';

			$diccionario = array(1=>'DEPOSITO',2=>'RECARGA WEB',3=>'RETORNO RECARGA WEB',4=>'APUESTA REGISTRADA',5=>'APUESTA PAGADA',9=>'RETIRO');
			foreach ($diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR tra.tipo_id LIKE "%'.$key.'%"';
				}
			}

			$tc_tipo_doc_diccionario = array(0=>'DNI',8=>'DNI',1=>'CARNE EXTRANJERIA',2=>'PASAPORTE');
			foreach ($tc_tipo_doc_diccionario as $key => $value) {
				$tipo = fnc_like_match($value,$_POST["search"]["value"]);
				if ($tipo) {
					$nombre_busqueda .=' OR cli.tipo_doc LIKE "%'.$key.'%"';
				}
			}

			$nombre_busqueda .= ')';
		}else{
			$nombre_busqueda = "";
		}
	}

	$where_local="";
	if( (int) $local > 0 ){
		$where_local=" AND ( loc.cc_id='".$local."' OR ce_ssql.cc_id='".$local."' ) ";
		//$where_local=" AND tra.cc_id='".$local."' ";
	}

	$where_estado_cierre="";
	if( (int) $estado_cierre > 0 ){
		if( (int) $estado_cierre === 1 ){//Cierre activo
			$where_estado_cierre=" AND caj.id > 0 ";
		}
		if( (int) $estado_cierre === 2 ){//Cierre eliminado
			$where_estado_cierre=" AND ce.id > 0 ";
		}
	}

	$where_cuenta = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '' && $_POST["cuenta"] !== null) {
		$where_cuenta = " AND tra.cuenta_id IN ( " . implode(",",$_POST["cuenta"]) . " ) " ;
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(tra.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(tra.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$where_cajavip = "";
	if ($caja_vip == 1) {
		//$where_cajavip = " AND tra.caja_vip = 1 " ;
	}else{
		$where_cajavip = " AND IFNULL(tra.caja_vip, 0) = 0 " ;
	}
	
	// Cantidade
	$query_ingresos_salidas ="
		SELECT
			IFNULL( SUM( IF ( (tra.tipo_id = 1), 1, 0 ) ), 0 ) AS deposito_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 2), 1, 0 ) ), 0 ) AS recarga_cant,
			IFNULL( SUM( IF ( (tra.tipo_id = 4), 1, 0 ) ), 0 ) AS terminal_cant,
			IFNULL( SUM( IF ( tra.tipo_id = 1, tra.monto, 0 ) ), 0 ) AS deposito_total,
			IFNULL( SUM( IF ( tra.tipo_id = 2, tra.monto, 0 ) ), 0 ) AS recarga_total,
			IFNULL( SUM( IF ( tra.tipo_id = 14, tra.monto, 0 ) ), 0 ) AS terminal_total
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		WHERE
		(
			(tra.tipo_id = 1 ) or 
			(tra.tipo_id = 2 and tra.estado = 1) or 
			(tra.tipo_id = 14 and tra.estado = 1)
		) 
		".$where_users_test ." 
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
		".$where_fecha ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_local ." 
		".$where_estado_cierre ." 
		".$where_cuenta."
		".$where_cliente."
		".$where_bono."
		".$where_proveedor."
		".$where_num_transaccion."
		".$where_tipo_saldo."
		".$where_cajavip."
		";
	//$result["QUERY_listar_transacciones_resumen_v2"] = $query_COUNT;
	$list_query_ingresos_salidas=$mysqli->query($query_ingresos_salidas);
	$list_res_ingresos_salidas=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_ingresos_salidas->fetch_assoc()) {
			$list_res_ingresos_salidas[]=$li;
		}
	}


	// Total de Depositos (transferencias)
	$query_depositos ="
		SELECT
			IFNULL( cuen.cuenta_descripcion, '' ) AS cuenta_descripcion,
			IFNULL( SUM( IF ( tra.monto>0, 1, 0 ) ), 0 ) AS cuenta_cant,
			IFNULL( SUM( IF ( tra.monto>0, tra.monto, 0 ) ), 0 ) AS cuenta_total
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		WHERE
		tra.tipo_id = 1 
		AND tra.cuenta_id is not null 
		AND tra.cuenta_id>0 
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4 
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_local ." 
		".$where_estado_cierre ." 
		".$where_cuenta."
		".$where_cliente."
		".$where_bono."
		".$where_proveedor."
		".$where_num_transaccion."
		".$where_cajavip."
		GROUP BY 
			cuen.cuenta_descripcion
		";
	$list_query_depositos=$mysqli->query($query_depositos);
	$list_res_depositos=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_depositos->fetch_assoc()) {
			$list_res_depositos[]=$li;
		}
	}


	// Total de Retiros (transferencias a clientes)
	$query_retiros ="
		SELECT
			IFNULL( cuen.nombre, '' ) AS cuenta_descripcion,
			IFNULL( SUM( IF ( tra.monto>0, 1, 0 ) ), 0 ) AS cuenta_cant,
			IFNULL( SUM( IF ( tra.monto>0, tra.monto, 0 ) ), 0 ) AS cuenta_total
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios usu_val ON usu_val.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_televentas_cuentas_pago_retiro cuen ON cuen.id = tra.cuenta_pago_id
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		WHERE
		tra.tipo_id = 11 and tra.estado = 2 
		AND tra.cuenta_pago_id is not null 
		AND tra.cuenta_pago_id>0 
		AND tra.cliente_id > 0
		AND LENGTH(IFNULL(cli.num_doc,0)) > 4
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_local ." 
		".$where_estado_cierre ." 
		".$where_cuenta."
		".$where_cliente."
		".$where_bono."
		".$where_proveedor."
		".$where_num_transaccion."
		".$where_cajavip."
		GROUP BY 
			cuen.nombre
		";
	//$result["query_retiros"] = $query_retiros;
	$list_query_retiros=$mysqli->query($query_retiros);
	$list_res_retiros=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_retiros->fetch_assoc()) {
			$list_res_retiros[]=$li;
		}
	}

	if(count($list_res_ingresos_salidas)>0 || count($list_res_depositos)>0 || count($list_res_retiros)>0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["res_ingresos_salidas"] = '
			<thead>
				<tr style="background-color: #bf9000;">
					<th colspan="3" class="text-center" style="color: white;font-size: 16px;">Reporte de Ingresos y salidas</th>
				</tr>
				<tr style="background-color: #ffd966;">
					<th class="text-center" class="text-center" style="color: #bf9000;">DESCRIPCIÓN</th>
					<th class="text-center" class="text-center" style="color: #bf9000;">#</th>
					<th class="text-center" class="text-center" style="color: #bf9000;">MONTO</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Total de Depósitos (transferencias)</td>
					<td class="text-right">'.$list_res_ingresos_salidas[0]["deposito_cant"].'</td>
					<td class="text-right">'.number_format($list_res_ingresos_salidas[0]["deposito_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Total de Retiro</td>
					<td class="text-right">'.$list_res_ingresos_salidas[0]["recarga_cant"].'</td>
					<td class="text-right">'.number_format($list_res_ingresos_salidas[0]["recarga_total"], 2, ".", ",").'</td>
				</tr>
				<tr>
					<td>Terminal Deposit In</td>
					<td class="text-right">'.$list_res_ingresos_salidas[0]["terminal_cant"].'</td>
					<td class="text-right">'.number_format($list_res_ingresos_salidas[0]["terminal_total"], 2, ".", ",").'</td>
				</tr>
			</tbody>
			';
		// DEPOSITOS
		$result["res_depositos"] = '
			<thead>
				<tr style="background-color: #bf9000;">
					<th colspan="3" class="text-center" style="color: white;font-size: 16px;">Total de Depositos (transferencias)</th>
				</tr>
				<tr style="background-color: #ffd966;">
					<th class="text-center" style="color: #bf9000;">Depositos</th>
					<th class="text-center" style="color: #bf9000;">#</th>
					<th class="text-center" style="color: #bf9000;">Monto</th>
				</tr>
			</thead>
			<tbody>
			';
		$res_depositos_cant = 0;
		$res_depositos_total = 0;
		foreach($list_res_depositos as $l_r_d){
			$res_depositos_cant = (int)$res_depositos_cant+(int)$l_r_d["cuenta_cant"];
			$res_depositos_total = (double)$res_depositos_total+(double)$l_r_d["cuenta_total"];
			$result["res_depositos"] .= '
					<tr>
						<td>'.$l_r_d["cuenta_descripcion"].'</td>
						<td class="text-right">'.$l_r_d["cuenta_cant"].'</td>
						<td class="text-right">'.number_format($l_r_d["cuenta_total"], 2, ".", ",").'</td>
					</tr>
				';
		}
		$result["res_depositos"] .= '
				<tr style="font-weight: bold;background-color: #e7e6e6;">
					<td class="text-center">TOTAL</td>
					<td class="text-right">'.$res_depositos_cant.'</td>
					<td class="text-right">'.number_format($res_depositos_total, 2, ".", ",").'</td>
				</tr>
			</tbody>
			';
		// RETIROS
		$result["res_retiros"] = '
			<thead>
				<tr style="background-color: #bf9000;">
					<th colspan="3" class="text-center" style="color: white;font-size: 16px;">Total de Retiros (transferencias a clientes)</th>
				</tr>
				<tr style="background-color: #ffd966;">
					<th class="text-center" style="color: #bf9000;">Retiros</th>
					<th class="text-center" style="color: #bf9000;">#</th>
					<th class="text-center" style="color: #bf9000;">Monto</th>
				</tr>
			</thead>
			<tbody>
			';
		$res_retiros_cant = 0;
		$res_retiros_total = 0;
		foreach($list_res_retiros as $l_r_r){
			$res_retiros_cant = (int)$res_retiros_cant+(int)$l_r_r["cuenta_cant"];
			$res_retiros_total = (double)$res_retiros_total+(double)$l_r_r["cuenta_total"];
			$result["res_retiros"] .= '
					<tr>
						<td>'.$l_r_r["cuenta_descripcion"].'</td>
						<td class="text-right">'.$l_r_r["cuenta_cant"].'</td>
						<td class="text-right">'.number_format($l_r_r["cuenta_total"], 2, ".", ",").'</td>
					</tr>
				';
		}
		$result["res_retiros"] .= '
				<tr style="font-weight: bold;background-color: #e7e6e6;">
					<td class="text-center">TOTAL</td>
					<td class="text-right">'.$res_retiros_cant.'</td>
					<td class="text-right">'.number_format($res_retiros_total, 2, ".", ",").'</td>
				</tr>
			</tbody>
			';
		// RETIROS TERMINAL
		$result["res_retiros_terminal"] = '
			<thead>
				<tr style="background-color: #bf9000;">
					<th colspan="3" class="text-center" style="color: white;font-size: 16px;">Total de Retiros (transferencias a clientes)</th>
				</tr>
				<tr style="background-color: #ffd966;">
					<th class="text-center" style="color: #bf9000;">Terminal deposit In</th>
					<th class="text-center" style="color: #bf9000;">#</th>
					<th class="text-center" style="color: #bf9000;">Monto</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Total Terminal Deposit In</td>
					<td class="text-right">'.$list_res_ingresos_salidas[0]["terminal_cant"].'</td>
					<td class="text-right">'.number_format($list_res_ingresos_salidas[0]["terminal_total"], 2, ".", ",").'</td>
				</tr>
				<tr style="font-weight: bold;background-color: #e7e6e6;">
					<td class="text-center">TOTAL</td>
					<td class="text-right">'.$list_res_ingresos_salidas[0]["terminal_cant"].'</td>
					<td class="text-right">'.number_format($list_res_ingresos_salidas[0]["terminal_total"], 2, ".", ",").'</td>
				</tr>
			</tbody>
			';
	} else {
		$result["http_code"] = 400;
		$result["status"] ="No hay transacciones.";
		$result["res_ingresos_salidas"] = '';
	}
}

if (isset($_GET["accion"]) && $_GET["accion"]==="SecRepTel_listar_cajeros") {
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$query ="
		SELECT
			u.id cod_cajero,
			u.usuario,
			CONCAT(
			IF
				( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
			IF
				( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
			IF
				( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
			) nombre_cajero 
		FROM
			tbl_usuarios u #ON u.id = A.user_id
			JOIN tbl_personal_apt pl ON pl.id = u.personal_id
		HAVING 
			nombre_cajero LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
			OR u.usuario LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
		";
	//$result["consulta_query"] = $query;
	$list_query=$mysqli->query($query);
	$list_registros=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registros[]=$li;
			$temp_array['codigo'] = $li['cod_cajero'];
            $temp_array['value'] = strtoupper('[' . $li['usuario'] . '] ' . $li['nombre_cajero']);
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





//*******************************************************************************************************************
//*******************************************************************************************************************
// LISTAR APUESTAS ALTENAR - REPORTE TLS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_apuestas_altenar") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$estado                = $_POST["estado"];

	$where_fecha_inicio = " AND DATE(FechaHora) >= '".$busqueda_fecha_inicio."' ";
	$where_fecha_fin = " AND DATE(FechaHora) <= '".$busqueda_fecha_fin."' ";

	$where_estado = "";
	if ((int)$estado === 1){
		$where_estado = "AND Status = 'Lost' ";
	}else if ((int)$estado === 2){
		$where_estado = "AND Status = 'Win' ";
	}

	$query_1 ="
		SELECT 
			IFNULL(LocalName, '') caja,
			IFNULL(FechaHora, '') fecha,
			IFNULL(TicketID, '') id_ticket,
			CASE
				WHEN Status = 'Lost' THEN 'Perdido'
				WHEN Status = 'Win' THEN 'Ganado'
				ELSE ''
			END estado,
			CONCAT(Nombre,' ',Apellido_paterno,' ',Apellido_materno) cliente,
			IFNULL(Num_doc, '') dni,
			IFNULL(Apostado, '') MontoApostado,
    		IFNULL(Ganado, '') MontoGanado
		FROM at_altenar.Daily_summary_alt_bet
		WHERE
			TicketID != 0
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
			".$where_estado."
		ORDER BY FechaHora DESC;"
	;

	$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}

	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
		$result["result"] = $list_transaccion;
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
		$result["result"] = $list_transaccion;
	}
}

//*******************************************************************************************************************
// EXPORTAR LISTAR APUESTAS ALTENAR - REPORTE TLS
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_apuestas_altenar_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$estado                = $_POST["estado"];
	
	$where_fecha_inicio = " AND DATE(FechaHora) >= '".$busqueda_fecha_inicio."' ";
	$where_fecha_fin    = " AND DATE(FechaHora) <= '".$busqueda_fecha_fin."' ";

	$where_estado = "";
	if ((int)$estado === 1){
		$where_estado = "AND Status = 'Lost' ";
	}else if ((int)$estado === 2){
		$where_estado = "AND Status = 'Win' ";
	}

	// Lista
	$query_1 = "
		SELECT 
			IFNULL(LocalName, '') caja,
			IFNULL(FechaHora, '') fecha,
			IFNULL(TicketID, '') id_ticket,
			CASE
				WHEN Status = 'Lost' THEN 'Perdido'
				WHEN Status = 'Win' THEN 'Ganado'
				ELSE ''
			END estado,
			CONCAT(Nombre,' ',Apellido_paterno,' ',Apellido_materno) cliente,
			IFNULL(Num_doc, '') dni,
			IFNULL(Apostado, '') MontoApostado,
			IFNULL(Ganado, '') MontoGanado
		FROM at_altenar.Daily_summary_alt_bet
		WHERE
			TicketID != 0
			".$where_fecha_inicio ." 
			".$where_fecha_fin ." 
			".$where_estado."
		ORDER BY FechaHora DESC
	";
	$list_query = $mysqli->query($query_1);
	$result_data = array();
	if ($mysqli->error) {
		//$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$headers = [
			"CAJA" => "caja",
			"FECHA" => "fecha",
			"ID TICKET" => "id_ticket",
			"ESTADO" => "estado",
			"CLIENTE" => "cliente",
			"DNI" => "dni",
			"MONTO APOSTADO" => "MontoApostado",
			"MONTO GANADO" => "MontoGanado"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_apuestas_altenar" . $date->getTimestamp();

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


echo json_encode($result);