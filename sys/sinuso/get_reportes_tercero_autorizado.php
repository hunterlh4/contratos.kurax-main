<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="listar_registros_tercero_autorizado") {
 
	$fec_inicio = $_POST["fec_inicio"];
	$fec_fin = $_POST["fec_fin"];
	$cajero = $_POST["cajero"];
	$cliente = $_POST["cliente"];

	$where_fecha_inicio = "";
	$where_fecha_fin = "";
	$where_cliente = "";
	$where_cajero = "";	 


	if (!Empty($fec_inicio)) {
		$where_fecha_inicio .= "  AND tit.created_at >= '".$fec_inicio."'";
	}

	if (!Empty($fec_fin)) {
		$where_fecha_fin .= "  AND tit.created_at <= date_add('".$fec_fin."', interval 1 day)";
	}
	
	if (!Empty($cajero) && $cajero != "0") {
		$where_cajero .= "  AND tit.id_cajero = '".$cajero."'";
	}

	if (!Empty($cliente) && $cliente != "0") {
		$where_cliente .= "  AND tit.id_cliente = '".$cliente."'";
	}

	$query = " SELECT  
		cli.id,
		if (cli.tipo_doc = '0', 'DNI' , if (cli.tipo_doc = '1', 'CE/PTP' , 'PASAPORTE')) as tipo_doc_nomb,
		cli.num_doc, 
		CONCAT_WS(' ', cli.nombre, cli.apellido_paterno, cli.apellido_materno) AS cliente ,
		(SELECT COUNT(*) FROM tbl_televentas_titular_abono tit WHERE cli.id = tit.id_cliente and tit.estado in (1,0)) AS total_tercero

		FROM wwwapuestatotal_gestion.tbl_televentas_titular_abono tit 
		Left Join tbl_televentas_clientes cli on  cli.id = tit.id_cliente
		Where tit.estado in (1,0)
		$where_cajero
		$where_cliente
		$where_fecha_inicio
		$where_fecha_fin
		group by tit.id_cliente 
		ORDER BY cli.nombre asc
		";
	$list_query = $mysqli->query($query);
	$html = '
	<table id="sec_reportes_tercero_autorizado" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">#</th> 
				<th class="text-center">Tipo Doc.</th>
				<th class="text-center">Num. Doc.</th>
				<th class="text-center">Cliente</th>
				<th class="text-center">Cant. 3ro Aut.</th>
				<th class="text-center">Ver detalle</th>				 
			</tr>
		</thead>
		<tbody>';
		$i =0;
	while($li=$list_query->fetch_assoc()){
	 $i = $i +1;
		$html .='
			<tr>
				<td class="text-center">'.$i.'</td>			 		
				<td class="text-center">'.$li['tipo_doc_nomb'].'</td>
				<td class="text-center">'.$li['num_doc'].'</td>
				<td class="text-center">'.$li['cliente'].'</td>
				<td class="text-center">'.$li['total_tercero'].'</td>
				<td class="text-center"><button type="button" class="btn btn-info" onclick="SecRepTerAut_ver_detalle('.$li['id'].')"><span class="fa fa-eye"></span></button></td>			 
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


if (isset($_POST["accion"]) && $_POST["accion"]==="listado_detalle_titular_abono") {
 
	$id_cliente = $_POST["id_cliente"];

	$query_1 = "SELECT 
		tit.id,
		tit.id_cliente,
		tit.dni_titular,
		tit.nombre_apellido_titular ,
		tit.estado, 
		tit.id_cajero,
		CONCAT_WS(' ', pl.nombre, pl.apellido_paterno, pl.apellido_materno) AS cajero,
		tit.created_at
	FROM tbl_televentas_titular_abono tit
	left JOIN tbl_usuarios u ON u.id = tit.id_cajero
	left JOIN tbl_personal_apt pl ON pl.id = u.personal_id
	WHERE tit.id_cliente ='".$id_cliente."'
	AND tit.estado in (0,1)
	ORDER BY tit.id ASC";

	$list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	} else {
		while ($li_1 = $list_query_1->fetch_assoc()) {
			$list_1[] = $li_1;
		}
	}
	
	if (count($list_1) > 0) {	
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_1;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros";
		$result["result"] = $list_1;
	}
}


echo json_encode($result);
