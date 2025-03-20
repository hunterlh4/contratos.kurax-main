<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/phpexcel/classes/PHPExcel.php';
require_once '/var/www/html/sys/helpers.php';

function datatables_servidor($query_excel=false){
	global $mysqli, $login;
	$TABLA = "tbl_solicitud_mantenimiento";
	$ID_LOGIN = $login["id"];
	$data = $_POST["action"];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value
	$searchValue = $mysqli->real_escape_string($searchValue);

	$fecha_inicio = isset($_POST["fecha_inicio"])?$_POST["fecha_inicio"]:"";
	$fecha_fin = isset($_POST["fecha_fin"])?$_POST["fecha_fin"]:"";

	$fecha_fin = date("Y-m-d", strtotime($fecha_fin." +1 days"));
	$query_fecha = "";
	if($fecha_inicio != "" && $fecha_fin != ""){
		$query_fecha = "AND sm.created_at > '$fecha_inicio' AND sm.created_at < '$fecha_fin'";
	}
	if($fecha_inicio == "" && $fecha_fin != ""){
		$query_fecha = " AND sm.created_at < '$fecha_fin'";
	}
	if($fecha_fin == "" && $fecha_inicio != ""){
		$query_fecha = " AND sm.created_at > '$fecha_inicio'";
	}
    ## Search
	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " and (
	        l.nombre LIKE '%".$searchValue."%' or 
	        z.nombre LIKE '%".$searchValue."%' or 
	        sis.nombre LIKE '%".$searchValue."%' or 
	        sm.reporte LIKE '%".$searchValue."%' or 
	        sm.tipo_mantenimiento LIKE '%".$searchValue."%' or 
	        sm.estado LIKE '%".$searchValue."%' or 
	        sm.created_at LIKE '%".$searchValue."%' or 
	        sm.fecha_cierre LIKE '%".$searchValue."%' 
			) ";
	}
	$SELECT="SELECT
				sm.created_at,
				sm.id AS id,
				z.nombre AS zona,
				l.nombre AS local,
				sis.nombre AS sistema ,
				sm.reporte ,
				sm.tipo_mantenimiento,
				sm.estado,
				sm.criticidad,
				sm.fecha_cierre,
				sm.latitud,
				sm.longitud,
                (SELECT concat(nombre,' ',apellido_paterno,' ',apellido_materno) FROM tbl_personal_apt WHERE id =(SELECT personal_id FROM tbl_usuarios  WHERE id = sm.tecnico_id)) AS proveedor,
				sm.comentario
				FROM wwwapuestatotal_gestion.tbl_solicitud_mantenimiento sm
				LEFT JOIN tbl_locales l ON  l.id = sm.local_id
				LEFT JOIN tbl_zonas z ON  z.id = sm.zona_id
				LEFT JOIN tbl_solicitud_mantenimiento_sistema sis ON  sis.id = sm.sistema_id";

	//col 2  zona
	if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.nombre in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}
	//col 3  local
	if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" AND l.nombre in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."')";
	}
	//col 4  sistena
	if($_POST["columns"][4]["search"]["value"] != "" && $_POST["columns"][4]["search"]["value"] != "null"){
		$searchQuery.=	" AND sis.nombre in ('".str_replace(",", "','", $_POST["columns"][4]["search"]["value"])."')";
	}
	//col 7  estado
	if($_POST["columns"][7]["search"]["value"] != "" && $_POST["columns"][7]["search"]["value"] != "null"){
		$searchQuery.=	" AND sm.estado in ('".str_replace(",", "','", $_POST["columns"][7]["search"]["value"])."')";
	}
	if ($login["usuario_locales"]) {
		$searchQuery .= " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	}
	if($query_excel){
		$query_excel = $SELECT." WHERE 1 ".$searchQuery . $query_fecha . " ORDER BY ".$columnName." ".$columnSortOrder;
		$response = array(
			  "query_excel" => $query_excel
		);
		return $response;
	}

	$sel = $mysqli->query("SELECT count(*) AS allcount FROM $TABLA");
	$records = $sel->fetch_assoc();
	$totalRecords = $records['allcount'];

	$query_f =   $SELECT."	WHERE 1 ". $searchQuery . $query_fecha;
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$query_f.") AS subquery");
	$records = $sel->fetch_assoc();
	$totalRecordwithFilter = $records['allcount'];

	$limit = " LIMIT ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}
	$query_f = $SELECT." WHERE 1 ".$searchQuery . $query_fecha . " ORDER BY ".$columnName." ".$columnSortOrder.$limit;
	$registros = $mysqli->query($query_f);
	$data = array();

	while ($row = $registros->fetch_assoc()) {
	   $data[] = $row;
	}

	$response = array(
	  "draw" => $draw,
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);
	return $response;

}

// REPORTE SOLICITUD MANTENIMIENTO
function datatables_servidor_dias($query_excel=false){
	global $mysqli, $login;

	// COMPARAR ARRAY DE LOCALES DEL USUARIO
	$locales_usuario = [];

	$select_locales_usuarios = "SELECT id,red_id FROM tbl_locales WHERE id IN (".implode(",", $login["usuario_locales"]).") ";
	$result_locales_usuarios = mysqli_query($mysqli,$select_locales_usuarios);
	while ($row_locales_usuarios = mysqli_fetch_assoc($result_locales_usuarios)) {
		// if($row_locales_usuarios['red_id'] == 1 || $row_locales_usuarios['red_id'] == 16){
			array_push($locales_usuario, $row_locales_usuarios['id']);
		// }
	}
	if(empty($locales_usuario)){
		array_push($locales_usuario, 0); 
	}

	$TABLA = "tbl_solicitud_mantenimiento";
	$ID_LOGIN = $login["id"];
	$data = $_POST["action"];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value
	$searchValue = $mysqli->real_escape_string($searchValue);

	$fecha_inicio = isset($_POST["fecha_inicio"])?$_POST["fecha_inicio"]:"";
	$fecha_fin = isset($_POST["fecha_fin"])?$_POST["fecha_fin"]:"";

	$fecha_fin = date("Y-m-d", strtotime($fecha_fin." +1 days"));
	$query_fecha = "";
	if($fecha_inicio != "" && $fecha_fin != ""){
		$query_fecha = "AND sm.created_at > '$fecha_inicio' AND sm.created_at < '$fecha_fin'";
	}
	if($fecha_inicio == "" && $fecha_fin != ""){
		$query_fecha = " AND sm.created_at < '$fecha_fin'";
	}
	if($fecha_fin == "" && $fecha_inicio != ""){
		$query_fecha = " AND sm.created_at > '$fecha_inicio'";
	}
    ## Search
	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " and (
	        l.nombre LIKE '%".$searchValue."%' or 
	        z.nombre LIKE '%".$searchValue."%' or 
	        sis.nombre LIKE '%".$searchValue."%' or 
	        sm.reporte LIKE '%".$searchValue."%' or 
	        sm.tipo_mantenimiento LIKE '%".$searchValue."%' or 
	        sm.estado LIKE '%".$searchValue."%' or 
	        sm.created_at LIKE '%".$searchValue."%' or 
	        sm.fecha_cierre LIKE '%".$searchValue."%' or
			rs.nombre LIKE '%".$searchValue."%' or
			udep.nombre LIKE '%".$searchValue."%' or
			(SELECT concat(nombre,' ',apellido_paterno,' ',apellido_materno) FROM tbl_personal_apt WHERE id =(SELECT personal_id FROM tbl_usuarios  WHERE id = sm.tecnico_id)) LIKE '%".$searchValue."%'
			) ";
	}
	$SELECT="SELECT
			sm.created_at,
			sm.id AS id,
			l.cc_id,
			z.razon_social_id,
			rs.nombre as razon_social,
			z.nombre AS zona,
			max(udep.nombre) as departamento,
			l.nombre AS local,
			sis.nombre AS sistema ,
			sm.reporte ,
			sm.tipo_mantenimiento,
			sm.estado,
			sm.fecha_cierre,
			sm.latitud,
			sm.longitud,
			(SELECT concat(nombre,' ',apellido_paterno,' ',apellido_materno) FROM tbl_personal_apt WHERE id =(SELECT personal_id FROM tbl_usuarios  WHERE id = sm.tecnico_id)) AS proveedor,
			sm.comentario,
			(select t_revision from tbl_solicitud_estimacion_mantenimiento sem
				left join tbl_solicitud_estimacion_zona sezz on sezz.id = sem.solicitud_estimacion_zona_id
				where sem.sistema_id = sis.id and sezz.zona_id = z.id and sezz.estado = 1 and sezz.razon_social_id = z.razon_social_id
				AND IF(max(udep.nombre) is null, provincia is null, provincia = max(udep.nombre))	
			) as t_revision,
			(select t_coordinacion from tbl_solicitud_estimacion_mantenimiento sem
				left join tbl_solicitud_estimacion_zona sezz on sezz.id = sem.solicitud_estimacion_zona_id
				where sem.sistema_id = sis.id and sezz.zona_id = z.id and sezz.estado = 1 and sezz.razon_social_id = z.razon_social_id
				AND IF(max(udep.nombre) is null, provincia is null, provincia = max(udep.nombre))	
			) as t_coordinacion,
			(select valor_v from tbl_solicitud_estimacion_mantenimiento sem
				left join tbl_solicitud_estimacion_zona sezz on sezz.id = sem.solicitud_estimacion_zona_id
				where sem.sistema_id = sis.id and sezz.zona_id = z.id and sezz.estado = 1 and sezz.razon_social_id = z.razon_social_id
				AND IF(max(udep.nombre) is null, provincia is null, provincia = max(udep.nombre))	
			) as valor_v,
			(select valor_e from tbl_solicitud_estimacion_mantenimiento sem
				left join tbl_solicitud_estimacion_zona sezz on sezz.id = sem.solicitud_estimacion_zona_id
				where sem.sistema_id = sis.id and sezz.zona_id = z.id and sezz.estado = 1 and sezz.razon_social_id = z.razon_social_id
				AND IF(max(udep.nombre) is null, provincia is null, provincia = max(udep.nombre))	
			) as valor_e,
			(0) as total_dias,
			(0) as cantidad_sabados_domingos,
			(0) as total_formula
			FROM wwwapuestatotal_gestion.tbl_solicitud_mantenimiento sm
			LEFT JOIN tbl_locales l ON  l.id = sm.local_id
			LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
			LEFT JOIN tbl_solicitud_mantenimiento_sistema sis ON  sis.id = sm.sistema_id
			LEFT JOIN tbl_ubigeo udep ON (
				udep.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
				udep.cod_prov = '00' AND
				udep.cod_dist = '00'
			)
			LEFT JOIN tbl_razon_social rs on rs.id = z.razon_social_id";

	//col 2  zona
	if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.nombre in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}
	//col 3  local
	if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" AND l.nombre in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."')";
	}
	//col 4  sistena
	if($_POST["columns"][4]["search"]["value"] != "" && $_POST["columns"][4]["search"]["value"] != "null"){
		$searchQuery.=	" AND sis.nombre in ('".str_replace(",", "','", $_POST["columns"][4]["search"]["value"])."')";
	}
	//col 7  estado
	if($_POST["columns"][7]["search"]["value"] != "" && $_POST["columns"][7]["search"]["value"] != "null"){
		$searchQuery.=	" AND sm.estado in ('".str_replace(",", "','", $_POST["columns"][7]["search"]["value"])."')";
	}
	//col 10 razon_social
	if($_POST["columns"][10]["search"]["value"] != "" && $_POST["columns"][10]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.razon_social_id in ('".str_replace(",", "','", $_POST["columns"][10]["search"]["value"])."')";
	}

	if ($login["usuario_locales"]) {
		$searchQuery .= " AND l.id IN (".implode(",", $locales_usuario).") ";
	}
	if($query_excel){
		$query_excel = $SELECT." WHERE 1 ".$searchQuery . $query_fecha . "GROUP BY sm.id ORDER BY ".$columnName." ".$columnSortOrder;
		$response = array(
			  "query_excel" => $query_excel
		);
		return $response;
	}

	$SELECT_1="SELECT
			sm.id
			FROM wwwapuestatotal_gestion.tbl_solicitud_mantenimiento sm
			LEFT JOIN tbl_locales l ON  l.id = sm.local_id
			LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
			LEFT JOIN tbl_solicitud_mantenimiento_sistema sis ON  sis.id = sm.sistema_id
			LEFT JOIN tbl_ubigeo udep ON (
				udep.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
				udep.cod_prov = '00' AND
				udep.cod_dist = '00'
			)
			LEFT JOIN tbl_razon_social rs on rs.id = z.razon_social_id";

	$query_total_filtered = "SELECT COUNT(*) AS total FROM (" . $SELECT_1 . " WHERE 1 " . $searchQuery . $query_fecha . "GROUP BY sm.id ".") AS subquery";
	$result_total_filtered = $mysqli->query($query_total_filtered);
	$row_total_filtered = $result_total_filtered->fetch_assoc();
	$totalRecordwithFilter = $row_total_filtered['total'];

	$totalRecords = $row_total_filtered['total'];

	$limit = " LIMIT ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}
	$query_f = $SELECT." WHERE 1 ".$searchQuery . $query_fecha . "GROUP BY sm.id ORDER BY ".$columnName." ".$columnSortOrder.$limit;
	$registros = $mysqli->query($query_f);
	$data = array();

	while ($row = $registros->fetch_assoc()) {
		$data[] = $row;
	}

	$response = array(
	  "draw" => $draw,
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);
	return $response;

}

function resizeImage($resourceType, $image_width, $image_height)
{
    $imagelayer = [];
    if ($image_width < 1920 && $image_height < 1080) {
        //mini
        $resizewidth_mini = 100;
        $resizeheight_mini = 100;
        $imagelayer[0] = imagecreatetruecolor($image_width, $image_height);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $image_width, $image_height, $image_width, $image_height);
        //mini
        $imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
        imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
    } else {
        $ratio = $image_width / $image_height;
        $escalaW = 1920 / $image_width;
        $escalaH = 1080 / $image_height;

        if ($ratio > 1) {
            $resizewidth = $image_width * $escalaW;
            $resizeheight = $image_height * $escalaW;
        } else {
            $resizeheight = $image_height * $escalaH;
            $resizewidth = $image_width * $escalaH;
        }
        $resizewidth_mini = 100;
        $resizeheight_mini = 100;

        $imagelayer[0] = imagecreatetruecolor($resizewidth, $resizeheight);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $resizewidth, $resizeheight, $image_width, $image_height);
        //mini
        $imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
        imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
    }
    return $imagelayer;
}


if(isset($_POST["sec_solicitud_mantenimiento_cargar_solicitud_vt"])){
	$solicitud_id = $_POST["solicitud_id"];
	$command ="SELECT inci.id AS id,
	l.nombre AS local,
	z.nombre AS zona,	 
	inci.incidencia_txt  as reporte,	 
	inci.estado_vt as estado,
	inci.created_at,
	inci.updated_at,				 
	inci.comentario_vt as comentario,
	inci.foto_terminado_vt as foto_terminado
	FROM .tbl_soporte_incidencias inci
	LEFT JOIN tbl_locales l ON  l.id = inci.local_id
	LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
	WHERE inci.id = {$solicitud_id}";
	$list_query=$mysqli->query($command);
	$list = array();
	while ($li=$list_query->fetch_assoc()) {
		$list[]=$li;
	}

	$command = "SELECT 
					sma.id,
					sma.solicitud_mantenimiento_id,
					sma.archivo
				FROM tbl_solicitud_mantenimiento_archivos sma
				WHERE sma.solicitud_mantenimiento_id = {$solicitud_id}";
	$list_query=$mysqli->query($command);
	$imagenes = array();
	while ($li=$list_query->fetch_assoc()) {
		$imagenes[]=$li;
	}

	$return["local"] = $list[0];
	$return["imagenes"] = $imagenes;
}

if(isset($_POST["sec_solicitud_mantenimiento_cargar_solicitud"])){
	$solicitud_id = $_POST["solicitud_id"];
	$command ="SELECT sm.id AS id,
				l.nombre AS local,
				z.nombre AS zona,
				sis.nombre AS sistema ,
				sm.reporte ,
				sm.tipo_mantenimiento,
				sm.estado,
				sm.created_at,
				sm.updated_at,
				sm.latitud,
				sm.longitud,
				sm.comentario,
				IF(sm.comentario_terminado IS NULL , '---', sm.comentario_terminado ) AS comentario_terminado,
				sm.foto_terminado,
				sm.criticidad,
				sm.tecnico_id,
                sm.ok_comercial,
				IF(sm.id_garantia IS NULL , '', sm.id_garantia ) AS id_garantia,
                CASE
					WHEN sm.ok_comercial = 1 THEN 1
					WHEN (sm.ok_comercial IS NULL OR sm.ok_comercial =0) AND TIMESTAMPDIFF(HOUR,sm.fecha_cierre,NOW())>=48 THEN 1
                    WHEN (sm.ok_comercial IS NULL OR sm.ok_comercial =0) AND TIMESTAMPDIFF(HOUR,sm.fecha_cierre,NOW())<48 THEN 0
				END	as comercial_visto
				FROM .tbl_solicitud_mantenimiento sm
				LEFT JOIN tbl_locales l ON  l.id = sm.local_id
				LEFT JOIN tbl_zonas z ON  z.id = sm.zona_id
				LEFT JOIN tbl_solicitud_mantenimiento_sistema sis ON  sis.id = sm.sistema_id
				WHERE sm.id = {$solicitud_id}";
	$list_query=$mysqli->query($command);
	$list = array();
	while ($li=$list_query->fetch_assoc()) {
		$list[]=$li;
	}

	if (empty($list[0]['id_garantia'])) {
		$command = "SELECT 
					sma.id,
					sma.solicitud_mantenimiento_id,
					sma.archivo
				FROM tbl_solicitud_mantenimiento_archivos sma
				WHERE sma.solicitud_mantenimiento_id = {$solicitud_id}";
		$list_query=$mysqli->query($command);
	} else {
		$command = "
            SELECT id, id_garantia_solicitud, archivo
            FROM tbl_garantia_solicitudes_archivos
            WHERE id_garantia_solicitud = {$list[0]['id_garantia']}
        ";
        $list_query=$mysqli->query($command);
	}
	
	$imagenes = array();
	while ($li=$list_query->fetch_assoc()) {
		$imagenes[]=$li;
	}

	$return["local"] = $list[0];
	$return['command'] = $command;
	$return["imagenes"] = $imagenes;
}
if(isset($_POST["sec_solicitud_mantenimiento_cargar_locales"])){

	$where_locales = "";
	if ($login["usuario_locales"]) {
		$where_locales = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	}
	$zona_id = $_POST["zona_id"];
	$command ="SELECT 
		l.id,
		l.nombre
		FROM tbl_locales l
		WHERE 1 = 1 
		".$where_locales."
		AND l.zona_id = {$zona_id}";
	$list_query=$mysqli->query($command);
	$list = array();
	while ($li=$list_query->fetch_assoc()) {
		$list[]=$li;
	}
	$return["locales"] = $list;
}
if(isset($_POST["sec_solicitud_mantenimiento_save"])){
	$data = $_POST;
	if($data["reporte"]==""){
		$return["error"]="reporte";
		$return["error_msg"]="Debe ingresar Reporte";
		$return["error_focus"]="reporte";	
	}
	elseif($data["local_id_sm"]=="" || $data["local_id_sm"]==0 || $data["local_id_sm"]=="_all_" ){
		$return["error"]="local_id_sm";
		$return["error_msg"]="Debe Seleccionar Tienda";
		$return["error_focus"]="local_id_sm";
	}		
	elseif($_FILES["imagen"]['tmp_name'][0] == "" ){
		$return["error"]="imagen";
		$return["error_msg"]="Debe Ingresar imagen";
		$return["error_focus"]="imagen";
	}
	elseif($data["sistema_id"]==""){
		$return["error"]="sistema_id";
		$return["error_msg"]="Debe Ingresar Sistema";
		$return["error_focus"]="sistema_id";
	}	
	else{
		$reporte = $mysqli->real_escape_string($data["reporte"]);
		$longitude = $data["longitude"]!=""?$data["longitude"]:'null';
		$latitude =  $data["latitude"] !=""?$data["latitude"]:'null';
		$insert_command = "
		INSERT INTO tbl_solicitud_mantenimiento 
			(created_at
			,user_id
			,zona_id
			,local_id
			,sistema_id
			,reporte
			,longitud
			,latitud
			,estado
			)
			VALUES(
			now()
			,".$login["id"]."
			,".$data["zona_id"]."
			,".$data["local_id_sm"]."
			,".$data["sistema_id"]."
			,'".$reporte."'
			,".$longitude."
			,".$latitude."
			,'Solicitud'
			)
		";
		$mysqli->query($insert_command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command;
			exit();
		}
		$id_solicitud_mantenimiento = $mysqli->insert_id;

		$path = "/var/www/html/files_bucket/solicitud_mantenimiento/";
		$file = [];
		$imageLayer = [];
		if (!is_dir($path)) mkdir($path, 0777, true);

		for ($i=0; $i < count($_FILES['imagen']["name"]); $i++) { 
			$archivo = $_FILES['imagen']["name"][$i];
			$filename = $_FILES["imagen"]['tmp_name'][$i];
			$filenametem = $_FILES["imagen"]['name'][$i];

			$size = $_FILES["imagen"]['size'][$i];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			$valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

			if($filename==""){
				$return["error"]="imagen";
				$return["error_msg"] = "Debe Ingresar un archivo de imagen";
				$return["error_focus"]="imagen";
				print_r(json_encode($return));
				die();
			}
			if(!in_array($ext, $valid_extensions)) {
				$return["error"]="ext";
				$return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
				print_r(json_encode($return));
				die();
			}
			if($size > 10485760){//10 mb
				$return["error"]="size";
				$return["error_msg"] = "Archivo supera la cantidad máxima permitida (10 MB)";
				print_r(json_encode($return));
				die();
			}
			$nombre_archivo = "";
			if($filename != ""){
			    $fileExt = pathinfo($_FILES["imagen"]['name'][$i], PATHINFO_EXTENSION);

			    $resizeFileName =   date('YmdHis');
			    $nombre_archivo = $id_solicitud_mantenimiento."_".$filenametem."_".$resizeFileName . "." . $fileExt;
			          
		        $sourceProperties = getimagesize($filename);
		        $uploadImageType = $sourceProperties[2];
		        $sourceImageWith = $sourceProperties[0];
		        $sourceImageHeight = $sourceProperties[1];

		        switch ($uploadImageType) {
		            case IMAGETYPE_JPEG:
		                $resourceType = imagecreatefromjpeg($filename);
		                break;
		            case IMAGETYPE_PNG:
		                $resourceType = imagecreatefrompng($filename);
		                break;
		            case IMAGETYPE_GIF:
		                $resourceType = imagecreatefromgif($filename);
		                break;
		            default:
		                break;
		        }
		        $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		        $file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
		        move_uploaded_file($file[0], $path . $nombre_archivo);
			}

			$insert_command = "
			INSERT INTO tbl_solicitud_mantenimiento_archivos 
				(solicitud_mantenimiento_id
				,archivo
				,estado
				)
				VALUES(
				 $id_solicitud_mantenimiento
				,'".$nombre_archivo."'
				,1
				)
			";
			$mysqli->query($insert_command);
		}
		$return["id"] = $id_solicitud_mantenimiento;
		$return["curr_login"]=$login;
		$return["mensaje"] = "Solicitud de Mantenimiento Registrada";
	}
}



if(isset($_POST["set_solicitud_mantenimiento_update_vt"])){
	$user_id = $login["id"];
	extract($_POST);
	 if(!isset($estado) || $estado == ""){
		$return["error"]="estado";
		$return["error_msg"]="Debe Seleccionar Estado";
		$return["error_focus"]="estado";	
	}
	else{
		$fecha_cierre = "null";
		$foto_terminado = "";
		if($estado == "Terminado"){
			$fecha_cierre = "now()";

			$path = "/var/www/html/files_bucket/solicitud_mantenimiento/";
			$file = [];
			$imageLayer = [];
			if (!is_dir($path)) mkdir($path, 0777, true);

			$archivo = $_FILES['foto_terminado_update']["name"];
			$filename = $_FILES["foto_terminado_update"]['tmp_name'];
			$filenametem = $_FILES["foto_terminado_update"]['name'];

			$size = $_FILES["foto_terminado_update"]['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			$valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

			if($filename == ""){
				$return["error"]="imagen";
				$return["error_msg"] = "Debe Ingresar un archivo de imagen";
				$return["error_focus"]="foto_terminado_update";
				print_r(json_encode($return));
				die();
			}
			if(!in_array($ext, $valid_extensions)) {
				$return["error"]="ext";
				$return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
				print_r(json_encode($return));
				die();
			}
			if($size > 10485760){//10 mb
				$return["error"]="size";
				$return["error_msg"] ="Archivo supera la cantidad máxima permitida (10 MB)";
				print_r(json_encode($return));
				die();
			}
			$nombre_archivo = "";
			if($filename != ""){
			    $fileExt = pathinfo($_FILES["foto_terminado_update"]['name'], PATHINFO_EXTENSION);

			    $resizeFileName =   date('YmdHis');
			    $nombre_archivo = $id."_".$filenametem."_".$resizeFileName . "." . $fileExt;
			    
		        $sourceProperties = getimagesize($filename);
		        $uploadImageType = $sourceProperties[2];
		        $sourceImageWith = $sourceProperties[0];
		        $sourceImageHeight = $sourceProperties[1];

		        switch ($uploadImageType) {
		            case IMAGETYPE_JPEG:
		                $resourceType = imagecreatefromjpeg($filename);
		                break;
		            case IMAGETYPE_PNG:
		                $resourceType = imagecreatefrompng($filename);
		                break;
		            case IMAGETYPE_GIF:
		                $resourceType = imagecreatefromgif($filename);
		                break;
		            default:
		                break;
		        }
		        $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		        $file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
		        move_uploaded_file($file[0], $path . $nombre_archivo);
			}
			$foto_terminado = ",foto_terminado_vt = '$nombre_archivo'";

		}
		$comentario = "";
		if($estado == "Programado"){
			$comentario = ",comentario_vt = '".$_POST["comentario"]."'";
		}
		$command = "
			UPDATE tbl_soporte_incidencias SET 
				 estado_vt = '$estado'			 
				,updated_at = now()
				,update_user_id = $user_id
				,fecha_cierre_vt = $fecha_cierre
				 $comentario
				 $foto_terminado
			WHERE id = ".$id;
		$mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command;
			exit();
		}
		$return["mensaje"] = "Solicitud de Mantenimiento ".$id." Actualizada";
		$return["curr_login"]=$login;
	}
}

if(isset($_POST["set_solicitud_mantenimiento_update"])){
	$user_id = $login["id"];
	extract($_POST);
	if(!isset($tipo_mantenimiento) || $tipo_mantenimiento == ""){
		$return["error"]="tipo_mantenimiento";
		$return["error_msg"]="Debe Seleccionar Tipo Mantenimiento";
		$return["error_focus"]="tipo_mantenimiento";
		die(json_encode($return));
	}
	if(!isset($estado) || $estado == ""){
		$return["error"]="estado";
		$return["error_msg"]="Debe Seleccionar Estado";
		$return["error_focus"]="estado";
		die(json_encode($return));
	}
	if( $estado == "Programado" ){
		if( !isset($tecnico_id) || $tecnico_id == "" ){
			$return["error"] = "tecnico_id";
			$return["error_msg"] = "Debe Seleccionar Técnico";
			$return["error_focus"] = "tecnico_id";
			die(json_encode($return));
		}
		//$tecnico_id = ",tecnico_id = '" . $tecnico_id . "'";
	}
	$fecha_cierre = "null";
	$foto_terminado = "";
	if($estado == "Terminado"){
		$fecha_cierre = "now()";

		$path = "/var/www/html/files_bucket/solicitud_mantenimiento/";
		// SI FUE DERIVADO DESDE GARANTIA
		$sql_id_garantia = "
			SELECT id_garantia
			FROM tbl_solicitud_mantenimiento
			WHERE id = $id
			LIMIT 1
		";
		
		$result = $mysqli->query($sql_id_garantia);

		$data = array();
        while ($row = $result->fetch_assoc()) {
            $data = $row;
        }

		if (!empty($data['id_garantia'])) {
			$path = "/var/www/html/files_bucket/solicitud_garantia/";
		}

		$file = [];
		$imageLayer = [];
		if (!is_dir($path)) mkdir($path, 0777, true);

		$archivo = $_FILES['foto_terminado_update']["name"];
		$filename = $_FILES["foto_terminado_update"]['tmp_name'];
		$filenametem = $_FILES["foto_terminado_update"]['name'];

		$size = $_FILES["foto_terminado_update"]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		$valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

		if($filename == ""){
			$return["error"]="imagen";
			$return["error_msg"] = "Debe Ingresar un archivo de imagen";
			$return["error_focus"]="foto_terminado_update";
			print_r(json_encode($return));
			die();
		}
		if(!in_array($ext, $valid_extensions)) {
			$return["error"]="ext";
			$return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
			print_r(json_encode($return));
			die();
		}
		if($size > 10485760){//10 mb
			$return["error"]="size";
			$return["error_msg"] ="Archivo supera la cantidad máxima permitida (10 MB)";
			print_r(json_encode($return));
			die();
		}
		$nombre_archivo = "";
		if($filename != ""){
			$fileExt = pathinfo($_FILES["foto_terminado_update"]['name'], PATHINFO_EXTENSION);

			$resizeFileName =   date('YmdHis');
			$nombre_archivo = $id."_".$filenametem."_".$resizeFileName . "." . $fileExt;
			
			$sourceProperties = getimagesize($filename);
			$uploadImageType = $sourceProperties[2];
			$sourceImageWith = $sourceProperties[0];
			$sourceImageHeight = $sourceProperties[1];

			switch ($uploadImageType) {
				case IMAGETYPE_JPEG:
					$resourceType = imagecreatefromjpeg($filename);
					break;
				case IMAGETYPE_PNG:
					$resourceType = imagecreatefrompng($filename);
					break;
				case IMAGETYPE_GIF:
					$resourceType = imagecreatefromgif($filename);
					break;
				default:
					break;
			}
			$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
			$file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
			move_uploaded_file($file[0], $path . $nombre_archivo);
		}
		$foto_terminado = ",foto_terminado = '$nombre_archivo'";

		if (!empty($data['id_garantia'])) {
			$command = "
				UPDATE tbl_garantia_solicitudes
				SET
					foto_terminado = '$nombre_archivo',
					estado = 1
				WHERE id = {$data['id_garantia']}
			";
			$mysqli->query($command);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command;
				exit();
			}
		}
	}
	$comentario = "";
	if($estado == "Programado" || strtoupper($estado) == "PROGRAMADO CON PROVEEDOR" ){
		$comentario = ",comentario = '".$_POST["comentario"]."'";
	}
	$tecnico_id = $tecnico_id != null ? $tecnico_id : "null";
    $check_comercial = $check_comercial ?? "";
    $check_comercial = $check_comercial == "on" ? 1 : 0;

	$query_sql_mante = "SELECT 
			created_at, user_id, local_id, reporte
		FROM tbl_solicitud_mantenimiento
		WHERE id = '".$id."' 
		LIMIT 1";

	$list_query_mante = $mysqli->query($query_sql_mante);

	if($mysqli->error)
	{
		$error = $mysqli->error;

		$result["http_code"] = 400;
		$result["donde"] = 1;
		$result["status"] = $error;
		
		echo json_encode($result);
		exit();
	}

	$row_mante = $list_query_mante->fetch_assoc();
    $created_at = $row_mante["created_at"];
    // $user_id = $row_mante["user_id"];
    $local_id = isset($row_mante["local_id"]) ? $row_mante["local_id"] : "";
    $reporte = isset($row_mante["reporte"]) ? $row_mante["reporte"] : "";

	if($estado == "Derivado a Servicio Técnico"){
		$command = "INSERT INTO tbl_servicio_tecnico
						(
							user_id,
							local_id,
							incidencia_txt,
							estado,
							update_user_id,
							agente_1_id,
							fecha_asignada,
							update_user_at,
							created_at,
							updated_at,
							recomendacion
						)
						VALUES
						(
							'".$user_id."',
							'".$local_id."',
							'".$reporte."',
							'1',
							'".$user_id."',
							'".$login["id"]."',
							now(),
							now(),
							'".$created_at."',
							now(),
							'Visita Técnica'
						)";

		$mysqli->query($command);

		// FIN: INSERTAMOS EN LA TABLA tbl_soporte_incidencias
		if($mysqli->error)
		{
			print_r($mysqli->error);
			echo "\n";
			echo $command;
			exit();
		}
	}else{
		$command = "UPDATE tbl_solicitud_mantenimiento SET
			   ok_comercial = $check_comercial,
				estado = '$estado'
				,tipo_mantenimiento = '$tipo_mantenimiento'
				,updated_at = now()
				,update_user_id = $user_id
				,criticidad = '$criticidad'
				,tecnico_id = $tecnico_id
				,fecha_cierre = $fecha_cierre
					$comentario
					$foto_terminado
			WHERE id = ".$id;
		$mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command;
			exit();
		}
	}

	$return["mensaje"] = "Solicitud de Mantenimiento ".$id." Actualizada";
	$return["curr_login"]=$login;
}

if(isset($_POST["accion"]) && $_POST["accion"] == "sec_solicitud_mantenimiento_modal_detalle_derivar_a_servicio_tecnico")
{
	$id_servicio_mantenimiento = $_POST["id_servicio_mantenimiento"];

	$error = '';

	$query_sql = 
	"
	    SELECT 
			created_at, user_id, local_id, reporte
		FROM tbl_solicitud_mantenimiento
		WHERE id = '".$id_servicio_mantenimiento."' 
		LIMIT 1
	";

	$list_query = $mysqli->query($query_sql);

	if($mysqli->error)
	{
		$error = $mysqli->error;

		$result["http_code"] = 400;
		$result["donde"] = 1;
		$result["status"] = $error;
		
		echo json_encode($result);
		exit();
	}

	$row = $list_query->fetch_assoc();
    $created_at = $row["created_at"];
    $user_id = $row["user_id"];
    $local_id = $row["local_id"];
    $reporte = $row["reporte"];

    // INICIO: INSERTAMOS EN LA TABLA tbl_soporte_incidencias

	$query_insert = "INSERT INTO tbl_servicio_tecnico
					(
						user_id,
						local_id,
						incidencia_txt,
						estado,
						update_user_id,
						agente_1_id,
						fecha_asignada,
						update_user_at,
						created_at,
						updated_at,
						recomendacion
					)
					VALUES
					(
						'".$user_id."',
						'".$local_id."',
						'".$reporte."',
						'1',
						'".$user_id."',
						'".$login["id"]."',
						now(),
						now(),
						'".$created_at."',
						now(),
						'Visita Técnica'
					)";

	$mysqli->query($query_insert);

	// FIN: INSERTAMOS EN LA TABLA tbl_soporte_incidencias

	if($mysqli->error)
	{
		$error = $mysqli->error;

		$result["http_code"] = 400;
		$result["donde"] = 2;
		$result["status"] = $error;
		
		echo json_encode($result);
		exit();
	}

	$query_update = 
	"
		UPDATE tbl_solicitud_mantenimiento 
			SET estado = 'Derivado a Servicio Técnico'
		WHERE id = '".$id_servicio_mantenimiento."' 
	";

	$mysqli->query($query_update);

	$result["http_code"] = 200;
	$result["status"] = "Derivación exitosa";
	echo json_encode($result);
	exit();
}
if(isset($_POST["action"]) && $_POST["action"]=="sec_solicitud_mantenimiento_list_excel"){
	$query_excel = datatables_servidor(true)["query_excel"];
	$registros = $mysqli->query($query_excel);
	$data = array();
	while ($row = $registros->fetch_assoc()) {
		unset($row["id"]);
		$data[] = $row;
	}
	$file_version = date('YmdHis');
	$filename ="solicitud_mantenimiento" ."_at_" . $file_version . ".xls";
	$filesPath = '/var/www/html/export/solicitud_mantenimiento/';
    
    $cabeceras=["FECHA DE INGRESO","ZONA","TIENDA","SISTEMAS","REPORTE","TIPO DE MANTENIMIENTO","ESTADO","FECHA DE CIERRE","LONGITUD","LATITUD"];
    array_unshift($data , $cabeceras);

    $doc = new PHPExcel();
    $doc->setActiveSheetIndex(0);

    $estiloNombresFilas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>11,
	        'color'     => array(
	            'rgb' => '000000'
	        )
	    ),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);
	$doc->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloNombresFilas);
	$doc->getActiveSheet()->getColumnDimension('A')->setWidth(18);
	$doc->getActiveSheet()->getColumnDimension('B')->setWidth(12);
	$doc->getActiveSheet()->getColumnDimension('C')->setWidth(13);
	$doc->getActiveSheet()->getColumnDimension('D')->setWidth(13);
	$doc->getActiveSheet()->getColumnDimension('F')->setWidth(21);
	$doc->getActiveSheet()->getColumnDimension('H')->setWidth(18);

    $doc->getActiveSheet()->fromArray($data, null, 'A1', true);
    $attach = $filesPath . $filename;
    $objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
    $objWriter->save($attach);

	$response = array(
		"curr_login" => $login,
		"path" => '/export/solicitud_mantenimiento/'.$filename
	);
	echo json_encode($response);
	return;
    //header('Location: /export/solicitud_mantenimiento/'.$filename);
}

function calcular_sabados_domingos($fechaInicio, $fechaFinal) {
    if ($fechaInicio > $fechaFinal) {
        return $fechaFinal;
    }
    
    $diaActual = date('w', $fechaInicio); // Obtener el día de la semana (0 para domingo, 6 para sábado)
    
    if ($diaActual == 0) { // Si es domingo
        $fechaFinal = strtotime('+1 day', $fechaFinal);
    }
    
    $fechaInicio = strtotime('+1 day', $fechaInicio);
    
    if ($diaActual == 6) { // Si es sábado
        $fechaFinal = strtotime('+1 day', $fechaFinal);
    }
    
    return calcular_sabados_domingos($fechaInicio, $fechaFinal);
}

// REPORTE SOLICITUD
if(isset($_POST["action"]) && $_POST["action"]=="sec_solicitud_mantenimiento_list_dias_excel"){
	$query_excel = datatables_servidor_dias(true)["query_excel"];
	$registros = $mysqli->query($query_excel);
	$data = array();
	while ($row = $registros->fetch_assoc()) {

		if($row["t_revision"] != null && $row["t_coordinacion"] != null && $row["valor_v"] != null && $row["valor_e"] != null){
            date_default_timezone_set('America/Lima');
			$fecha_actual = date("Y-m-d");	
			
			$total_dias = $row["t_revision"] + $row["t_coordinacion"] + $row["valor_v"] + $row["valor_e"];
            
            $created_at = $row["created_at"];
            $fecha_inicio = date('Y-m-d', strtotime($created_at));
            $fecha_fin = date('Y-m-d', strtotime($fecha_inicio . ' + ' . floor($total_dias) . ' days'));
            
            // $date_fecha_inicio = strtotime($fecha_inicio);
            // $date_fecha_final = strtotime($fecha_fin);
            
            // $fecha_limite = calcular_sabados_domingos($date_fecha_inicio, $date_fecha_final);
            
            // $diferencia = strtotime(date('Y-m-d',$fecha_limite)) - strtotime($fecha_inicio);

			$diferencia = strtotime($fecha_fin) - strtotime($fecha_actual);
            $diferencia_dias = $diferencia / (60 * 60 * 24); 
            
            $dias = " días";
            if($diferencia_dias == 1 || $total_dias == 1){
                $dias = " día";
            }
            
            // $fecha_actual = date("Y-m-d");
            $fecha_cierre = $row["fecha_cierre"];
            $date_fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
            if($row["estado"] != "Terminado"){
                $row["dias_atencion"] = $diferencia_dias . $dias;
            }else{
                $diferencia = strtotime($date_fecha_cierre) - strtotime($fecha_inicio);
                $diferencia_dias = $diferencia / (60 * 60 * 24);
                $row["dias_atencion"] = $diferencia_dias . $dias;
            }
            
            $row["tiempo_objetivo"] = $total_dias; // . $dias
            $row["diferencia"] = $total_dias - $diferencia_dias;
        }

		// unset($row["id"]);
        unset($row["t_revision"]);
        unset($row["t_coordinacion"]);
        unset($row["valor_v"]);
        unset($row["valor_e"]);
        unset($row["cantidad_sabados_domingos"]);
        unset($row["total_dias"]);
		unset($row["total_formula"]);
		unset($row["razon_social_id"]);
		$data[] = $row;
	}
	$file_version = date('YmdHis');
	$filename ="solicitud_estimacion_mantenimiento" ."_at_" . $file_version . ".xls";
	$filesPath = '/var/www/html/export/solicitud_mantenimiento/';
    
    $cabeceras=["FECHA DE INGRESO","ID","CC_ID","RAZON SOCIAL","ZONA","DEPARTAMENTO","TIENDA","SISTEMAS","REPORTE","TIPO DE MANTENIMIENTO","ESTADO","FECHA DE CIERRE","LATITUD","LONGITUD","PROVEEDOR","COMENTARIO","DÍAS DE ATENCIÓN","TIEMPO OBJETIVO","DIFERENCIA"];
    array_unshift($data , $cabeceras);

    $doc = new PHPExcel();
    $doc->setActiveSheetIndex(0);

    $estiloNombresFilas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>11,
	        'color'     => array(
	            'rgb' => '000000'
	        )
	    ),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);
	$doc->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloNombresFilas);
	$doc->getActiveSheet()->getColumnDimension('A')->setWidth(18);
	$doc->getActiveSheet()->getColumnDimension('B')->setWidth(10);
	$doc->getActiveSheet()->getColumnDimension('C')->setWidth(10);
	$doc->getActiveSheet()->getColumnDimension('D')->setWidth(16);
	$doc->getActiveSheet()->getColumnDimension('E')->setWidth(13);
	$doc->getActiveSheet()->getColumnDimension('F')->setWidth(18);
	$doc->getActiveSheet()->getColumnDimension('G')->setWidth(14);
	$doc->getActiveSheet()->getColumnDimension('H')->setWidth(16);
	$doc->getActiveSheet()->getColumnDimension('I')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('J')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('K')->setWidth(18);
	$doc->getActiveSheet()->getColumnDimension('L')->setWidth(20);
	$doc->getActiveSheet()->getColumnDimension('M')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('N')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('O')->setWidth(18);
	$doc->getActiveSheet()->getColumnDimension('P')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('Q')->setWidth(16);
	$doc->getActiveSheet()->getColumnDimension('R')->setWidth(14);
	$doc->getActiveSheet()->getColumnDimension('S')->setWidth(14);

    $doc->getActiveSheet()->fromArray($data, null, 'A1', true);
    $attach = $filesPath . $filename;
    $objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
    $objWriter->save($attach);

	$response = array(
		"curr_login" => $login,
		"path" => '/export/solicitud_mantenimiento/'.$filename
	);
	echo json_encode($response);
	return;
    //header('Location: /export/solicitud_mantenimiento/'.$filename);
}

if(isset($_POST["action"]) && $_POST["action"]=="sec_solicitud_mantenimiento_list"){

	$response = datatables_servidor();
	echo json_encode($response);
	return;
}

if(isset($_POST["action"]) && $_POST["action"]=="sec_solicitud_mantenimiento_dias_list"){

	$response = datatables_servidor_dias();
	echo json_encode($response);
	return;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="listar_derivaciones_vt") {
	$usuario_id=$login['id'];
	$cargo_id=$login['cargo_id'];
	
	$busqueda_estado = $_POST["estado"];
	$busqueda_zona = $_POST["zona"];
	$busqueda_tienda = $_POST["tienda"];	
	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin = $_POST["fecha_fin"];

	$where_estado = "";
	$where_zona = "";
	$where_tienda = ""; 
	$where_fecha_inicio = "";
	$where_fecha_fin = "";

	if (!Empty($busqueda_estado)) {
		$where_estado=" AND inci.estado_vt  in ('".str_replace(",", "','", $busqueda_estado)."')";
	}

	if (!Empty($busqueda_zona)) {
		$where_zona=" AND z.nombre in ('".str_replace(",", "','", $busqueda_zona)."')";
	}

	if (!Empty($busqueda_tienda)) {
		$where_tienda=" AND  l.nombre in ('".str_replace(",", "','", $busqueda_tienda)."')";
	}

	if (!Empty($busqueda_fecha_inicio)) {
		$where_fecha_inicio=" AND inci.created_at >= '".$busqueda_fecha_inicio."'";
	}

	if (!Empty($busqueda_fecha_fin)) {
		$where_fecha_fin=" AND inci.created_at <= date_add('".$busqueda_fecha_fin."', interval 1 day)";
	}

 
	$query_1 =" 
	SELECT 
	inci.created_at,
	inci.id AS id,
	z.nombre AS zona,
	l.nombre AS local,
    inci.incidencia_txt, 
	inci.equipo,
	inci.nota_tecnico,
	inci.recomendacion,
	inci.estado_vt,
	inci.fecha_cierre_vt    
	FROM wwwapuestatotal_gestion.tbl_soporte_incidencias inci     
	LEFT JOIN tbl_locales l ON  l.id = inci.local_id    
	LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
	where inci.recomendacion = 'Visita Técnica' and inci.estado=1

	".$where_estado ."
	".$where_zona ." 
	".$where_tienda ."
	".$where_fecha_inicio ."
	".$where_fecha_fin ." 

	order by inci.created_at DESC
	";
	//$result["consulta_query"] = $query_1;
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
		//$result["login"]=$login;
	} else{
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
	}
	echo json_encode($result);
	return;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>