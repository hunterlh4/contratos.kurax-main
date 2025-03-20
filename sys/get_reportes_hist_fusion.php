<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_hist_fusion") {
 

	$fec_inicio = $_POST["fec_inicio"];
	$fec_fin = $_POST["fec_fin"];
	$cliente = $_POST["cliente"];
	$usuario = $_POST["usuario"];
 
 
	$where_fecha_inicio = "";
	$where_fecha_fin = "";
	$where_cliente = "";
	$where_usuario = "";

 

	if (!Empty($fec_inicio)) {
		$where_fecha_inicio .= "  AND hf.created_at >= '".$fec_inicio."'";
	}

	if (!Empty($fec_fin)) {
		$where_fecha_fin .= "  AND hf.created_at <= date_add('".$fec_fin."', interval 1 day)";
	}

	if (!Empty($cliente) && $cliente != "0") {
		$where_cliente .= "  AND hf.cliente_id_s = '".$cliente."'";
	}
	if (!Empty($usuario) && $usuario != "0") {
		$where_usuario .= "  AND hf.usuario_id = '".$usuario."'";
	}

	$query = "SELECT
	hf.id,
    hf.usuario_id,
    IFNULL(usu_cajero.usuario, '') AS usuario_cajero,
    hf.cliente_id_f,
    hf.tipo_doc_f as tipo_doc,
    if (hf.tipo_doc_f = '0', 'DNI' , if (hf.tipo_doc_f = '1', 'CE/PTP' , 'PASAPORTE')) as tipo_doc_nomb,
    hf.num_doc_f as num_doc,
    hf.telefono_f as telefono,   
    hf.player_id_f as player_id,
    hf.web_id_f as web_id,
    hf.web_full_name_f as web_full_name,    
    hf.cliente_id_s,
	hf.cliente_f AS cliente,
	hf.correo_f as correo,
    date_format(hf.created_at, '%Y-%m-%d %H:%i') as fecha_formatead,
	hf.created_at
	from tbl_televentas_log_fusiones hf
	LEFT JOIN tbl_usuarios usu_cajero ON usu_cajero.id = hf.usuario_id
	WHERE  hf.id <> '' and hf.result=1
	$where_cliente
	$where_usuario
	$where_fecha_inicio
	$where_fecha_fin
	ORDER BY hf.created_at ASC
	";

	
	$list_query = $mysqli->query($query);
	$html = '
	<table id="sec_reportes_hist_fusion" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>	
				<th class="text-center">#</th> 
				<th class="text-center">Fecha</th> 
				<th class="text-center">Tipo Doc</th>
				<th class="text-center">Num Doc</th>
				<th class="text-center">Cliente</th>
				<th class="text-center">Correo</th>
				<th class="text-center">Teléfono</th>
				<th class="text-center">Player ID</th>
				<th class="text-center">ID Web</th>			
				<th class="text-center">Web Full Name</th>
				<th class="text-center">Responsable</th>
				<th class="text-center">Detalles</th>	 
			</tr>
		</thead>
		<tbody>';
		$i =0;
	while($li=$list_query->fetch_assoc()){
	 $i = $i +1;
		$html .='
			<tr>
				<td class="text-center">'.$i.'</td>		 		
				<td class="text-center">'.$li['created_at'].'</td>				
				<td class="text-center">'.$li['tipo_doc_nomb'].'</td>
				<td class="text-center">'.$li['num_doc'].'</td>
				<td class="text-center">'.strtoupper($li['cliente']).'</td>
				<td class="text-center">'.$li['correo'].'</td>
				<td class="text-center">'.$li['telefono'].'</td>
				<td class="text-center">'.$li['player_id'].'</td>
				<td class="text-center">'.$li['web_id'].'</td>
				<td class="text-center">'.$li['web_full_name'].'</td>
				<td class="text-center">'.strtoupper($li['usuario_cajero']).'</td>
				<td class="text-center"> 
				<button type="button" class="btn btn-primary" onclick="sec_tlv_ver_detalle_historial_f('.$li['id'].')"><span class="fa fa-eye"></span> </button>
				</td>
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



if (isset($_POST["accion"]) && $_POST["accion"]==="listar_detalle_hist_fusion") {
 

	$id_transaccion = $_POST["id"];
 
	$query = "SELECT 
				date_format(created_at, '%Y-%m-%d %H:%i') as created_at,
				cliente_id_s
				from tbl_televentas_log_fusiones where 
				id = '".$id_transaccion."' ";

	$list = $mysqli->query($query);
	$lista = array();
	while ($li = $list->fetch_assoc()) {
		$lista[] = $li;
	}
	
	$query2 = "SELECT
				id,
				cliente_id_f,    
				tipo_doc_f,
				if (tipo_doc_f = '0', 'DNI' , if (tipo_doc_f = '1', 'CE/PTP' , 'PASAPORTE')) as tipo_doc_nomb,
				num_doc_f,
				telefono_f,
				cliente_f,
				correo_f,
				player_id_f,
				web_id_f,
				web_full_name_f,
				transac_f,
				balance_f ,
				cliente_id_s,
				date_format(created_at, '%Y-%m-%d %H:%i') as created_at
				from tbl_televentas_log_fusiones
				where created_at like '%".$lista[0]["created_at"]."%' and cliente_id_s ='".$lista[0]["cliente_id_s"]."'
				ORDER BY id ASC ";

	$list_detalle = $mysqli->query($query2);
	$listado_clientes = array();
	while ($list2 = $list_detalle->fetch_assoc()) {
	$listado_clientes[] = $list2;
	}

	if (count($listado_clientes) <> 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $listado_clientes;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar historial fusion clientes.";
		$result["result"] = $listado_clientes;
	}
}


if (isset($_GET["accion"]) && $_GET["accion"]==="SecRepTel_listar_clientes_hf") {

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
 


echo json_encode($result);