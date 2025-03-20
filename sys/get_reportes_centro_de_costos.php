<?php 

include("db_connect.php");
include("sys_login.php");

function NombreLocal($red_id){
	$name_local = '';
	switch ($red_id) {
		case '1': $name_local = 'Tienda'; break;
		case '9': $name_local = 'Casino'; break;
		case '7': $name_local = 'Tambo'; break;
	}
	return $name_local;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_locales") {
	$query = "SELECT lc.id, lc.nombre
	FROM tbl_locales lc
	where lc.estado = 1";
	$list_query = $mysqli->query($query);
	$list = array();
	while($li=$list_query->fetch_assoc()){
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_redes") {
	// $query = "SELECT id, nombre
	// FROM tbl_locales_redes 
	// where id IN (1,9,7)";
	// $list_query = $mysqli->query($query);
	// while($li=$list_query->fetch_assoc()){
	// 	$list[] = $li;
	// }
	$list = array(
		array('id'=> 1, 'nombre' => 'Tienda'),
		array('id'=> 9, 'nombre' => 'Casino'),
		array('id'=> 7, 'nombre' => 'Tambo'),	
		array('id'=> 16, 'nombre' => 'RedIGH'),	
	);
	$result["status"] = 200;
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_arrendatario") {
	$usuario_id = $login?$login['id']:null;

	$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;
	$list_query_permisos_empresas = $mysqli->query($query);

	$query = "SELECT rs.id ,rs.nombre  from  tbl_razon_social rs  
 
	LEFT JOIN tbl_locales_redes tlr 
	ON tlr.id = rs.red_id 
	LEFT JOIN tbl_locales tl 
	ON tl.red_id = tlr.id
	LEFT JOIN tbl_usuarios_locales tul 
	on tul.local_id = tl.id
	WHERE rs.status = 1";
	if ($list_query_permisos_empresas->num_rows > 0) {
		$query.= ' AND  tul.usuario_id ='.$usuario_id.'   GROUP BY  rs.id';
	}else{
		$query.= '   GROUP BY  rs.id';

	} 
	$list_query = $mysqli->query($query);
	$list = array();
	while($li=$list_query->fetch_assoc()){
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_departamentos") {
	$query = "SELECT nombre, cod_depa AS id
	FROM tbl_ubigeo
	WHERE cod_prov = '00' AND cod_dist = '00' AND estado = '1'
	ORDER BY nombre ASC
	";
	$list_query = $mysqli->query($query);
	$list = array();
	while($li=$list_query->fetch_assoc()){
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_provincias") {
	$departamento_id = $_POST['departamento_id'];
	if (Empty($departamento_id)) {
		$result["status"] = 400;
		$result["result"] = [];
		echo json_encode($result);
		exit();
	}
	$query = "SELECT nombre,cod_prov AS id
	FROM tbl_ubigeo
	WHERE cod_depa = '" . $departamento_id . "' AND cod_dist = '00' AND cod_prov != '00' AND estado = '1'
	ORDER BY nombre ASC
	";
	$list_query = $mysqli->query($query);
	$list = array();
	while($li=$list_query->fetch_assoc()){
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_distritos") {
	$departamento_id = $_POST['departamento_id'];
	$provincia_id = $_POST["provincia_id"];

	if (Empty($departamento_id) || Empty($provincia_id) ) {
		$result["status"] = 400;
		$result["result"] = [];
		echo json_encode($result);
		exit();
	}
	$query = "SELECT nombre,cod_prov AS id
	FROM tbl_ubigeo
	WHERE cod_depa = '" . $departamento_id . "' AND cod_prov = '" . $provincia_id . "' AND cod_dist != '00' AND estado = '1'
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = array();
	while($li=$list_query->fetch_assoc()){
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="reporte_locales") {
	$local = $_POST['local'];
	$centro_costo = $_POST["centro_costo"];
	$arrendatario = $_POST["arrendatario"];
	$nombre_tienda = $_POST["nombre_tienda"];
	$departamento = $_POST["departamento"];
	$provincia = $_POST["provincia"];
	$distrito = $_POST["distrito"];
	$direccion = $_POST["direccion"];

	$where_local = " AND lc.red_id IN (1,9,7)";
	$where_centro_costo = "";
	$where_arrendatario = "";
	$where_nombre_tienda = "";
	$where_ubigeo = "";
	$where_direccion = "";

	if (!Empty($local) && $local != "0") {
		$where_local = " AND lc.red_id IN (".$local.")";
	}
	if (!Empty($centro_costo) && $centro_costo != "0") {
		$where_centro_costo = "  AND lc.cc_id LIKE '%".$centro_costo."%'";
	}
	if (!Empty($arrendatario) && $arrendatario != "0") {
		$where_arrendatario = "  AND lc.razon_social_id = '".$arrendatario."'";
	}
	if (!Empty($nombre_tienda) && $nombre_tienda != "0") {
		$where_nombre_tienda = "  AND lc.nombre LIKE '%".$nombre_tienda."%'";
	}
	if (!Empty($direccion) && $direccion != "0") {
		$where_direccion = "  AND lc.direccion LIKE '%".$direccion."%'";
	}
	if (!Empty($departamento) && $departamento != "0") {
		$where_ubigeo .= "  AND dp.cod_depa = '".$departamento."'";
	}
	if (!Empty($provincia) && $provincia != "0") {
		$where_ubigeo .= "  AND pr.cod_prov = '".$provincia."'";
	}
	if (!Empty($distrito) && $distrito != "0") {
		$where_ubigeo .= "  AND dt.cod_dist = '".$distrito."'";
	}

	// verificamos los permisos para los locales del usuario logeado
	$usuario_id = $login?$login['id']:null;
	$where_locales_permisos = '';

	$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;
	$list_query_permisos_empresas = $mysqli->query($query);
	if ($list_query_permisos_empresas->num_rows > 0) {
		$where_locales_permisos.= ' AND  tul.usuario_id ='.$usuario_id.'   GROUP BY  lc.id';
	}else{
		$where_locales_permisos.= '   GROUP BY  lc.id';

	} 

	$query = "SELECT lc.id, lc.red_id, rs.nombre AS arrendatario, lc.nombre, lc.cc_id, 
	dp.nombre AS departamento, pr.nombre AS provincia, dt.nombre AS distrito, lc.direccion
	FROM tbl_locales lc
	INNER JOIN cont_contrato AS c ON c.contrato_id = lc.contrato_id
	INNER JOIN tbl_razon_social AS rs ON rs.id = lc.razon_social_id
	INNER JOIN tbl_ubigeo AS dp ON dp.cod_depa = SUBSTRING(lc.ubigeo_id, 1, 2) AND dp.cod_prov = '00' AND dp.cod_dist = '00' 
	INNER JOIN tbl_ubigeo AS pr ON pr.cod_depa = SUBSTRING(lc.ubigeo_id, 1, 2) AND SUBSTRING(lc.ubigeo_id, 3, 2) = pr.cod_prov AND pr.cod_dist = '00'
	INNER JOIN tbl_ubigeo AS dt ON dt.cod_depa = SUBSTRING(lc.ubigeo_id, 1, 2) AND SUBSTRING(lc.ubigeo_id, 3, 2) = dt.cod_prov AND SUBSTRING(lc.ubigeo_id, 5, 2) = dt.cod_dist
	LEFT JOIN tbl_usuarios_locales tul
	ON tul.local_id = lc.id
	where lc.estado = 1 AND lc.cc_id != ''
	$where_local
	$where_centro_costo
	$where_arrendatario
	$where_nombre_tienda
	$where_ubigeo
	$where_direccion
	$where_locales_permisos
	";
	$list_query = $mysqli->query($query);
	$html = '
	<table id="sec_rep_locales_por_costos" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">Local</th>
				<th class="text-center">Empresa</th>
				<th class="text-center">C.C.</th>
				<th class="text-center">Nombre</th>
				<th class="text-center">Departamento</th>
				<th class="text-center">Provincia</th>
				<th class="text-center">Distrito</th>
				<th class="text-center">Dirección</th>
			</tr>
		</thead>
		<tbody>';
	while($li=$list_query->fetch_assoc()){
		$html .='
			<tr>
				<td class="text-center">'.NombreLocal($li['red_id']).'</td>
				<td class="text-left">'.$li['arrendatario'].'</td>
				<td class="text-left">'.$li['cc_id'].'</td>
				<td class="text-left">'.$li['nombre'].'</td>
				<td class="text-left">'.$li['departamento'].'</td>
				<td class="text-left">'.$li['provincia'].'</td>
				<td class="text-left">'.$li['distrito'].'</td>
				<td class="text-left">'.$li['direccion'].'</td>
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



























?>
