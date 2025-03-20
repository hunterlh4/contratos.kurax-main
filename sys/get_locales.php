<?php
include("db_connect.php");
include("sys_login.php");
//require("/var/www/html/cron/cron_pdo_connect.php");

require "/var/www/html/sys/zoneMinder/ZoneMinder.php";

// function normaliza ($cadena){
// $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
// $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
// $cadena = utf8_decode($cadena);
// $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
// $cadena = strtolower($cadena);
// return utf8_encode($cadena);
// }

if(isset($_POST["get_locales"])){
	$data = $_POST["get_locales"];
	$data['offset'] = $data['limit']*$data['page'];
	$where = "WHERE l.id IS NOT NULL";


	//	Filtrado por permisos de locales

	if($login && $login["usuario_locales"]){
		$where.= " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
	}
	if($data['filter'] != ""){

		$data['filter'] = normaliza($data['filter']);

		$where .= " AND (
				l.id 				LIKE '%{$data['filter']}%' OR
				l.cc_id 			LIKE '%{$data['filter']}%' OR
				l.nombre 			LIKE '%{$data['filter']}%' OR
				l.direccion 		LIKE '%{$data['filter']}%' OR
				z.nombre 			LIKE '%{$data['filter']}%' OR
				udep.nombre 		LIKE '%{$data['filter']}%' OR
				up.nombre 			LIKE '%{$data['filter']}%' OR
				ud.nombre 			LIKE '%{$data['filter']}%' OR
				COALESCE(tlr.nombre,'') LIKE '%{$data['filter']}%'
			)
		";
	}

	$locales = [];

	$result = $mysqli->query("
		SELECT
			l.id,
			l.cc_id,
			l.nombre,
			l.ubigeo_id,
			l.direccion,
		    l.red_id,
			COALESCE(tlr.nombre,'') as red,
            rz.nombre as razon_social,
			IF(z.nombre is null, 'Sin Zona', z.nombre) as zona,
			max(udep.nombre) as department,
			max(up.nombre) as province,
			max(ud.nombre) as district,
			IF(TRIM(l.latitud) = '' OR l.latitud IS NULL OR TRIM(l.longitud) = '' OR l.longitud IS NULL, NULL, CONCAT(TRIM(l.latitud), ',', TRIM(l.longitud))) AS coordenadas,
			(SELECT COUNT(p.id) FROM tbl_local_proveedor_id p WHERE p.local_id = l.id) AS proveedor_count
		FROM tbl_locales l
        LEFT JOIN tbl_razon_social rz ON rz.id = l.razon_social_id
		LEFT JOIN tbl_zonas z on l.zona_id=z.id
		LEFT JOIN tbl_ubigeo ud ON (
			ud.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			ud.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
			ud.cod_dist = SUBSTRING(l.ubigeo_id, 5, 2)
		)
		LEFT JOIN tbl_ubigeo up ON (
			up.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			up.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
			up.cod_dist = '00'
		)
		LEFT JOIN tbl_ubigeo udep ON (
			udep.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			udep.cod_prov = '00' AND
			udep.cod_dist = '00'
		)
		LEFT JOIN tbl_locales_redes tlr on l.red_id = tlr.id
		$where
		GROUP BY l.id
		ORDER BY l.id DESC
	");

	//LIMIT {$data['limit']} OFFSET {$data['offset']}

	while($r = $result->fetch_assoc()) $locales[] = $r;

	$num_rows = $mysqli->query("SELECT
			l.id
		FROM tbl_locales l
		LEFT JOIN tbl_zonas z on l.zona_id=z.id
		LEFT JOIN tbl_ubigeo ud ON (
			ud.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			ud.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
			ud.cod_dist = SUBSTRING(l.ubigeo_id, 5, 2)
		)
		LEFT JOIN tbl_ubigeo up ON (
			up.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			up.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
			up.cod_dist = '00'
		)
		LEFT JOIN tbl_ubigeo udep ON (
			udep.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			udep.cod_prov = '00' AND
			udep.cod_dist = '00'
		)
		$where
		GROUP BY l.id")->num_rows;

	$body = "";
	foreach ($locales as $local) {
		$body .= '<tr>';
		$body .= '<td class="text-right">'.$local["id"].'</td>';
		$body .= '<td class="text-center">'.$local["cc_id"].'</td>';
		$body .= '<td>'.$local["nombre"].'</td>';
		$body .= '<td>'.$local["direccion"].'</td>';
        $body .= '<td>'.$local["razon_social"].'</td>';
		$body .= '<td class="text-center">'.$local["zona"].'</td>';
		$body .= '<td>'.$local["department"].'</td>';
		$body .= '<td>'.$local["province"].'</td>';
		$body .= '<td>'.$local["district"].'</td>';
		$body .= '<td>'.$local["coordenadas"].'</td>';
        $body .= '<td>'.$local["red"].'</td>';
		$body .= '<td class="text-center">';
		$body .= '<a class="btn btn-rounded btn-'.($local["proveedor_count"] ? 'primary' : 'danger').' btn-xs" title="Editar" href="./?sec_id=locales&amp;item_id='.$local["id"].'">';
		$body .= '<i class="glyphicon glyphicon-edit"></i>												';
		$body .= '</a>';
		$body .= '</td>';
		$body .= '</tr>';
	}

	echo json_encode(['body' => $body, 'num_rows' => $num_rows]);


}

//	Funciones para creación de usuarios de locales

if(isset($_POST["usuario_locales"])){
	$data=$_POST["usuario_locales"];
	$usuario_local_exists = $mysqli->query("SELECT ul.local_id, l.nombre FROM tbl_usuarios_locales ul LEFT JOIN tbl_locales l ON (l.id = ul.local_id) WHERE ul.usuario_id = '".$data."' AND ul.estado = '1'");
	?>
	<?php
	while($ul=$usuario_local_exists->fetch_assoc()){
		?>d
		<tr>
			<td><?php echo $ul["nombre"];?></td>
			<td><a class="btn btn-default btn-xs" href="/?sec_id=locales&amp;item_id=<?php echo $ul["local_id"];?>#tab=tab_users" target="_blank"><i class="glyphicon glyphicon-new-window"></i> Ir al local</a></td>
		</tr>
		<?php
	}
	?>
	<?php
}

if (isset($_POST["accion"]) && $_POST["accion"] === "locales_usuarios_obtener_por_dni") {
    $dni = isset($_POST['dni']) ? $_POST['dni'] : null;

    if ($dni != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    p.id, 
                    p.nombre,
					u.id as usuario_id,
                    u.usuario,
                    p.apellido_paterno,
					p.estado
                FROM tbl_personal_apt p
				LEFT JOIN tbl_usuarios u 
                ON p.id= u.personal_id
                WHERE p.estado IN (0,1) AND p.dni=?
                LIMIT 1
            ");

            $stmt->bind_param("s", $dni);
            $stmt->execute();
            $stmt->bind_result(
                                $id, 
                                $nombre,
								$usuario_id, 
                                $usuario,
                                $apellido_paterno,
                                $estado
							);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $id,
                        'nombre' => $nombre,
						'usuario_id' => $usuario_id,
                        'usuario' => $usuario,
                        'apellido_paterno' => $apellido_paterno,
                        'estado' => $estado
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 404,
                    'message' => 'No se encontraron datos para el ID proporcionado.',
                ]);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                'status' => 500,
                'message' => 'Error en la consulta SQL: ' . $e->getMessage(),
            ]);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'ID no válido']);
    }
}
//--------------

if(isset($_POST["add_streaming"])){
	$data = (object)$_POST["add_streaming"];
	$zm = new ZoneMinder("35.185.3.100");

	$monitor = (object)[
		'name'		=> 'temp_'.strtotime("now"),
		'path'		=> $data->url."/Streaming/tracks/{$data->trackId}/?starttime=".date("Ymd", strtotime($data->startDateTime))."T".date("His", strtotime($data->startDateTime))."Z&endtime=".date("Ymd", strtotime($data->endDateTime))."T".date("His", strtotime($data->endDateTime))."Z"
	];
	$zm->addMonitor($monitor);

	echo json_encode($zm->getLastMonitor());
}

if(isset($_POST["remove_streaming"])){
	$data = (object)$_POST["remove_streaming"];
	$zm = new ZoneMinder("35.185.3.100");

	echo json_encode($zm->deleteMonitor($data->id));
}

if(isset($_POST["add_configuracion"])){
	$data = (object)$_POST["add_configuracion"];

	$result = $mysqli->query("INSERT INTO tbl_local_relacion(local_id, producto_id, servicio_id, canal_id, proveedor_id, nombre, created_at, updated_at) VALUES
		({$data->local_id}, {$data->producto_id}, {$data->servicio_id}, {$data->canal_id}, '{$data->proveedor_id}', '{$data->nombre}', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')");

	echo $mysqli->insert_id;
}

if(isset($_POST["populate_configv2_table"])){
	$data = (object)$_POST["populate_configv2_table"];

	$config = [];
	if($data->local_id != "new"){
		$result = $mysqli->query("SELECT l.id, r.nombre as producto, s.nombre as proveedor, c.nombre as canal, l.proveedor_id, l.nombre, l.estado
			FROM tbl_local_relacion l
			INNER JOIN productos r ON r.id = l.producto_id
			INNER JOIN tbl_servicios s ON s.id = l.servicio_id
			INNER JOIN tbl_canales_venta c ON c.id = l.canal_id
			WHERE l.local_id =".$data->local_id);
		while($r = $result->fetch_assoc()) $config[] = $r;
	}

	$body = "";
	foreach($config as $row){
		$body .='<tr>';
		$body .='<td>'.$row["producto"].'</td>';
		$body .='<td>'.$row["proveedor"].'</td>';
		$body .='<td>'.$row["canal"].'</td>';
		$body .='<td>';
		$body .='<div class="form-group">';
		$body .='<p style="display:none;">'.$row["proveedor_id"].'</p>';
		$body .='<input type="text" id="txtCajaId" name="txtCajaId" class="form-control" value="'.$row["proveedor_id"].'">';
		$body .='</div>		';
		$body .='</td>';
		$body .='<td>';
		$body .='<div class="form-group">';
		$body .='<p style="display:none;">'.$row["nombre"].'</p>';
		$body .='<input type="text" id="txtCajaDesc" name="txtCajaDesc" class="form-control" value="'.$row["nombre"].'">';
		$body .='</div>';
		$body .='</td>';
		$body .='<td class="text-right">';
		$body .='<input type="hidden" id="config_id" value="'.$row["id"].'">';
		$body .='<button class="btn btn-danger btnRemoveConfigV2" id="btnRemoveConfigV2"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		$body .='</td>';
		$body .='</tr>';
	}

	echo $body;
}

if(isset($_POST["remove_configuracion"])){
	$data = (object)$_POST["remove_configuracion"];

	$mysqli->query("DELETE FROM tbl_local_relacion WHERE id = {$data->id}");
}

if(isset($_POST["subtipo_solicitud"])){

	$data = $_POST["subtipo_solicitud"];
	$current_date = new DateTime();
	$current_date->add(new DateInterval("PT9H"));
	$current_date_plus_9 = $current_date->format('Y-m-d H:i:s');
	$proveedoresArr=array();
	$ret=array();
	$str_arr="";
	$provedores = $mysqli->query("
	SELECT proveedor_id FROM tbl_local_proveedor_id
	WHERE servicio_id =1 AND canal_de_venta_id in (16,17,19) AND proveedor_id REGEXP '^[0-9]+$' AND local_id='".$data."'");
	while($prov=$provedores->fetch_assoc()){
		$proveedoresArr[]=$prov['proveedor_id'];
	}
	$str_arr = implode (",", $proveedoresArr);
	if($str_arr!=""){
		$result = pdoStatement("
		SELECT sum(WinningAmount) as suma
		FROM [ApuestaTotal].[dbo].[Bet]
		WHERE CalcDate >= dateadd(day, -30, '".$current_date_plus_9."')
		AND State=4
		AND PaidDate IS NULL
		AND CashDeskId in(".$str_arr.")");
		foreach($result as $mont) {
			$ret[]=array(
				"suma"=>$mont["suma"]
			);
		}
	}
	else{
		$ret[]=array(
			"suma"=>0
		);
	}
	print_r(json_encode($ret));
}

/*
if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_address_mac_devices_x_local"){
	$id_local = $_POST["id_local"];
	$query = "SELECT d.id, d.macAddress 
			FROM at_softwareapp.device d
			WHERE d.status = 1 and d.id_local = " . $id_local;

	$list_query = $mysqli->query($query);
	$list_result = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_result[] = $li;
	}
	$result=array();
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if (count($list_result) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_result) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_result;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="validar_address_mac_existe"){
	$address = $_POST["address"];
	$query = "SELECT id
				FROM at_softwareapp.device 
				where macAddress = '" . $address . "' limit 1";

	$list_query = $mysqli->query($query);
	$list_existe_mac = array();
	$id_mac = 0;
	while ($li = $list_query->fetch_assoc()) {
		$id_mac = $li["id"];
		$list_existe_mac[] = $li;
	}
	$result=array();
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if (count($list_existe_mac) > 0){
		
		$query2 = "SELECT d.id, l.nombre as local, d.macAddress
					FROM at_softwareapp.device d
					INNER JOIN wwwapuestatotal_gestion.tbl_locales l on d.id_local = l.id
					where d.id = " . $id_mac . " and ifnull(d.id_local,0) > 0";

		$list_query2 = $mysqli->query($query2);
		$list_use_local = array();
		while ($li = $list_query2->fetch_assoc()) {
			$list_use_local[] = $li;
		}
		
		if ($mysqli->error) {
			$result["error"] = $mysqli->error;
		}

		if (count($list_use_local) == 0){
			$result["http_code"] = 200;
			$result["status"] = "OK";
			$result["code"] = 1;
		}else{
			$result["http_code"] = 400;
			$result["status"] = "La dirección MAC está siendo utilizada";
			$result["code"] = 2;
			$result["list_use_local"] = $list_use_local;
		}
	}else{
		$result["http_code"] = 400;
		$result["status"] = "La dirección MAC no existe.";
			$result["code"] = 3;
	}
	$result["list_existe_mac"] = $list_existe_mac;
	
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_address_mac_device_local"){
	$id_mac = $_POST["id_mac"];
	$id_local = $_POST["id_local"];
	$error = '';
	$query = "UPDATE at_softwareapp.device SET id_local = " . $id_local . ", updated_at = now() WHERE id = " . $id_mac;

	$mysqli->query($query);
	if($mysqli->error){
		$error .= 'Error al agregar la dirección MAC al local: ' . $mysqli->error . $query;
	}
	if ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	}
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_device_local"){
	$id_mac = $_POST["id_mac"];
	$id_local = $_POST["id_local"];
	$error = '';
	$query = "UPDATE at_softwareapp.device SET id_local = 0, updated_at = now() WHERE id_local = " . $id_local . " AND id = " . $id_mac;

	$mysqli->query($query);
	if($mysqli->error){
		$error .= 'Error al quitar la dirección MAC al local: ' . $mysqli->error . $query;
	}
	if ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	}
	echo json_encode($result);
}	

if (isset($_POST["accion"]) && $_POST["accion"]==="agregar_address_mac_new"){
	$address = $_POST["address"];
	$id_local = $_POST["id_local"];
	$error = '';
	$query = "INSERT INTO at_softwareapp.device (macAddress, id_local, status, created_at) 
				VALUES ('" . $address . "'," . $id_local . ",1,now())";

	$mysqli->query($query);
	if($mysqli->error){
		$error .= 'Error al guardar la dirección MAC: ' . $mysqli->error . $query;
	}
	if ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	}
	echo json_encode($result);
}	
*/

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_locales_servicio_publico_obtener_por_id")
{
	$param_id = $_POST["param_id"];
	
	$query = 
		"
		SELECT 
			sp.id, sp.id_local, l.nombre as local, l.nombre local_nombre,
            sf.id AS file_id,
			IFNULL(sp.fecha_emision, '') fecha_emision, 
			IFNULL(sp.fecha_vencimiento, '') fecha_vencimiento,
			CONCAT(YEAR(sp.periodo_consumo),'-',MONTH(sp.periodo_consumo)) AS mes_facturado,
			sp.id_tipo_servicio_publico, 
			IFNULL(sp.monto_total, 0) monto_total,
			IFNULL(sp.total_pagar, 0) total_pagar,
			sp.periodo_consumo, 
			IFNULL(sf.download, '') ruta_download_file,
			IFNULL(sf.file, '') nombre_file,
			IFNULL(sf.extension, '') extension,
			IFNULL(sfc.download, '') ruta_download_file_contometro,
			IFNULL(sfc.file, '') nombre_file_contometro,
			IFNULL(sfc.extension, '') extension_contometro,
			s.nro_suministro AS numero_suministro,
			s.tipo_compromiso_pago_id AS tipo_compromiso,
			sp.tipo_documento,
			s.id AS inmueble_suministro_id,
			s.nro_suministro AS codigo_suministro,
			ctp.nombre AS tipo_compromiso_nombre,
			s.monto_o_porcentaje AS monto_pct,
			IFNULL(sp.estado, 1) estado_recibo,
			IFNULL(sp.numero_recibo, '') numero_recibo,
			IFNULL(sp.aplica_caja_chica, 0) aplica_caja_chica,
			IFNULL(sp.nombre_paga_caja_chica, '') nombre_paga_caja_chica,
			sp.comentario,
			l.id AS local_id
		FROM cont_local_servicio_publico sp
			INNER JOIN tbl_locales l on sp.id_local = l.id
			LEFT JOIN cont_contrato c on l.contrato_id = c.contrato_id
			LEFT JOIN cont_inmueble i on i.contrato_id = c.contrato_id
			INNER JOIN cont_inmueble_suministros s
			ON s.id = sp.inmueble_suministros_id
			INNER JOIN cont_local_servicio_publico_files sf
			ON sf.cont_local_servicio_publico_id = sp.id AND sf.cont_tipo_servicio_publico_id = sp.id_tipo_servicio_publico
			LEFT JOIN cont_local_servicio_publico_files sfc
			ON sfc.cont_local_servicio_publico_id = sp.id AND sfc.cont_tipo_servicio_publico_id = 3
			INNER JOIN cont_tipo_pago_servicio ctp 
			ON s.tipo_compromiso_pago_id = ctp.id
		where sp.id = {$param_id}
		ORDER BY sp.id DESC 
		LIMIT 1
		";

	$list_query = $mysqli->query($query);
	
	$lista_datos = array();

	while ($li = $list_query->fetch_assoc())
	{
		$lista_datos[] = $li;
	}
	
	if ($mysqli->error)
	{
		$result["error"] = $mysqli->error;

	}
	if (count($lista_datos) == 0)
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	elseif (count($lista_datos) > 0)
	{
		$result["http_code"] = 200;
		$result["titulo"] = "OK";
		$result["descripcion"] = $lista_datos;
	}
	else
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Error";
		$result["descripcion"] = "Ocurrió un error al obtener el registro";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_zonas_por_empresa"){
	if ($_POST["razon_social_id"] == "30") { //si es IGH
		$query_zona = "SELECT z.id, z.nombre FROM tbl_zonas z WHERE z.razon_social_id IN (30) ORDER BY z.ord";	
	}else{
		$query_zona = "SELECT z.id, z.nombre FROM tbl_zonas z WHERE z.razon_social_id NOT IN (30) ORDER BY z.ord";	
	}
	$zonas = [];
	$sel_query = $mysqli->query($query_zona);
	while($sel=$sel_query->fetch_assoc()){
		$zonas[] = $sel;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $zonas;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_ultimo_turno_caja"){

	$caja_id = $_POST["caja_id"];

	$selectQuery = "SELECT 
							c.id, 
							IFNULL(c.turno_id,'-') AS turno,
							c.fecha_operacion,
							IFNULL(IF(cdf.tipo_id = 11, cdf.valor, NULL), 0) AS cierre_efectivo
					FROM tbl_caja c
					LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
					LEFT JOIN tbl_caja_datos_fisicos cdf ON (cdf.caja_id = c.id) AND cdf.tipo_id =11
					WHERE lc.id = ? 
					ORDER BY c.fecha_operacion DESC, c.fecha_apertura DESC LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("i", $caja_id);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($caja, $turno, $fecha_operacion, $cierre_efectivo);
		$selectStmt->fetch();
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["caja"] = $caja;
		$result["turno"] = $turno;
		$result["fecha_operacion"] = $fecha_operacion;
		$result["cierre_efectivo"] = $cierre_efectivo;

	}else{
		$result["http_code"] = 400;
		$result["error"] = "No existe la caja";
	}
	echo json_encode($result);
    exit();
}
?>
