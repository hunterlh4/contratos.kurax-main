<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_del_dep") {
 
	$caja = $_POST["caja"];
	$fec_inicio = $_POST["fec_inicio"];
	$fec_fin = $_POST["fec_fin"];
	$motivo = $_POST["motivo"];
	$cajero = $_POST["cajero"];

	$where_caja = "";
	$where_fecha_inicio = "";
	$where_fecha_fin = "";
	$where_motivo = "";
	$where_cajero = "";

	if (!Empty($caja) && $caja != "0") {
		$where_caja = " AND caj.local_caja_id IN (".$caja.")";
	}
	 

	if (!Empty($fec_inicio)) {
		$where_fecha_inicio .= "  AND tct6.created_at >= '".$fec_inicio."'";
	}

	if (!Empty($fec_fin)) {
		$where_fecha_fin .= "  AND tct6.created_at <= date_add('".$fec_fin."', interval 1 day)";
	}

	if (!Empty($motivo) && $motivo != "0") {
		$where_motivo .= "  AND tct.id_motivo_dev = '".$motivo."'";
	}
	if (!Empty($cajero) && $cajero != "0") {
		$where_cajero .= "  AND tct6.user_id = '".$cajero."'";
	}

	$query = "SELECT
	tct.id,
	tct.created_at fecha_hora_registro,
	IFNULL(tct.registro_deposito, '') registro_deposito,
	tct6.created_at as fecha_eliminacion,
	tct.user_id cajero_user_id,
	IFNULL(usu_cajero.usuario, '') AS usuario_cajero,
	tct6.id,
	tct6.user_id eliminador_user_id,
	IFNULL(usu_eliminador.usuario, '') AS usuario_eliminador,
	tct26.id,
	tct26.user_id validador_user_id,
	IFNULL(usu_validador.usuario, '') AS usuario_validador,
	loc_caj.nombre as caja,
	loc.nombre as nombre_local, 
	IFNULL(tct.monto_deposito, 0) AS deposito,
	IFNULL( ttr.tipo_rechazo, '' ) tipo_rechazo,
	IFNULL( tct.web_id, '' ) web_id,
	IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente
	from tbl_televentas_clientes_transaccion tct
	LEFT JOIN tbl_televentas_clientes cli ON cli.id = tct.cliente_id
	LEFT JOIN tbl_usuarios usu_cajero ON usu_cajero.id = tct.user_id
	left join tbl_televentas_clientes_transaccion tct6 on tct6.transaccion_id=tct.id and tct6.tipo_id=6
	LEFT JOIN tbl_usuarios usu_eliminador ON usu_eliminador.id = tct6.user_id
	left join tbl_televentas_clientes_transaccion tct26 on tct26.transaccion_id=tct.id and tct26.tipo_id=26
	LEFT JOIN tbl_usuarios usu_validador ON usu_validador.id = tct26.user_id
	LEFT JOIN tbl_caja caj ON caj.id = tct.turno_id
	LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
	LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
	left join tbl_televentas_tipo_rechazo ttr on tct.id_motivo_dev = ttr.id
	WHERE tct.tipo_id=1 AND tct.estado=3 
	$where_caja
	$where_motivo
	$where_cajero
	$where_fecha_inicio
	$where_fecha_fin
	ORDER BY tct.created_at ASC
	";
	$list_query = $mysqli->query($query);
	$html = '
	<table id="sec_reportes_del_dep" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">#</th>
				<th class="text-center">Nombre local - Caja</th>
				<th class="text-center">Fecha depósito</th>
				<th class="text-center">Fecha eliminación</th>
				<th class="text-center">ID Web</th>
				<th class="text-center">Cliente</th>
				<th class="text-center">Cajero</th>
				<th class="text-center">Importe</th>
				<th class="text-center">Motivo</th>
				<th class="text-center">Eliminador</th>
				<th class="text-center">Validador</th>				 
			</tr>
		</thead>
		<tbody>';
		$i =0;
	while($li=$list_query->fetch_assoc()){
	 $i = $i +1;
		$html .='
			<tr>
				<td class="text-center">'.$i.'</td>
				<td class="text-center">'.$li['nombre_local'].' - '.$li['caja'].'</td>			
				<td class="text-center">'.$li['registro_deposito'].'</td>
				<td class="text-center">'.$li['fecha_eliminacion'].'</td>
				<td class="text-center">'.$li['web_id'].'</td>
				<td class="text-center">'.$li['cliente'].'</td>
				<td class="text-center">'.$li['usuario_cajero'].'</td>
				<td class="text-center">'.$li['deposito'].'</td>
				<td class="text-center">'.$li['tipo_rechazo'].'</td>
				<td class="text-center">'.$li['usuario_eliminador'].'</td>
				<td class="text-center">'.$li['usuario_validador'].'</td>
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

echo json_encode($result);
