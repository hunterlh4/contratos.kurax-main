<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/phpexcel/classes/PHPExcel.php';
require_once '/var/www/html/sys/helpers.php';

function datatables_servidor( $query_excel = false ){
	global $mysqli, $login;
	//$ID_LOGIN = $login["id"];
	$data = $_POST["action"];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
    $columns = $_POST['columns'];
	$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value
	$searchValue = $mysqli->real_escape_string($searchValue);

	$fecha_inicio = isset($_POST["fecha_inicio"])?$_POST["fecha_inicio"]:"";
	$fecha_fin = isset($_POST["fecha_fin"])?$_POST["fecha_fin"]:"";

	$fecha_fin = date("Y-m-d", strtotime($fecha_fin." +1 days"));
	$query_fecha = "";

	$col_fecha = "IF(inci.updated_at  IS NULL, inci.created_at, inci.updated_at )";
	if($fecha_inicio != "" && $fecha_fin != ""){
		$query_fecha = "AND $col_fecha > '$fecha_inicio' AND $col_fecha < '$fecha_fin'";
	}
	if($fecha_inicio == "" && $fecha_fin != ""){
		$query_fecha = " AND $col_fecha < '$fecha_fin'";
	}
	if($fecha_fin == "" && $fecha_inicio != ""){
		$query_fecha = " AND $col_fecha > '$fecha_inicio'";
	}

	$where_equipos = "";

    ## Search
	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " and (
			inci.id LIKE '%".$searchValue."%' or
			IF(inci.updated_at  IS NULL, inci.created_at, inci.updated_at ) LIKE '%".$searchValue."%' or
	        l.nombre LIKE '%".$searchValue."%' or 
	        z.nombre LIKE '%".$searchValue."%' or 
	        ste.nombre LIKE '%".$searchValue."%' or 
	        inci.incidencia_txt LIKE '%".$searchValue."%' or 
	        inci.equipo LIKE '%".$searchValue."%' or 
	        inci.nota_tecnico LIKE '%".$searchValue."%' or 
	        inci.recomendacion LIKE '%".$searchValue."%' or 
			rs.nombre LIKE '%".$searchValue."%' or 
	        IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) LIKE '%".$searchValue."%' 
			) ";
	}
	//col 3  zona
	/*if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.nombre in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}*/
	if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.id in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."')";
	}
	//col 4  local
	/*if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" AND l.nombre in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."')";
	}*/
	if($_POST["columns"][4]["search"]["value"] != "" && $_POST["columns"][4]["search"]["value"] != "null"){
		$searchQuery.=	" AND l.id in ('".str_replace(",", "','", $_POST["columns"][4]["search"]["value"])."')";
	}
	//col 7  incidencia
	if($_POST["columns"][7]["search"]["value"] != "" && $_POST["columns"][7]["search"]["value"] != "null"){
		$searchQuery.=	" AND inci.incidencia_txt in ('".str_replace(",", "','", $_POST["columns"][7]["search"]["value"])."')";
	}
	//col 5 Equipo
	if($_POST["columns"][5]["search"]["value"] != "" && $_POST["columns"][5]["search"]["value"] != "null"){
		$searchQuery.=	" AND ste.id in ('".str_replace(",", "','", $_POST["columns"][5]["search"]["value"])."')";
	}
	//col 8  estado
	if($_POST["columns"][8]["search"]["value"] != "" && $_POST["columns"][8]["search"]["value"] != "null"){
		$searchQuery.=	" AND  IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) in ('".str_replace(",", "','", $_POST["columns"][8]["search"]["value"])."')";
	}
	//col 2 razon_social
	if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.razon_social_id in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}

    $query_all = "SELECT 
		IF(inci.updated_at  IS NULL, inci.created_at, inci.updated_at ) AS created_at,
		inci.id AS id,
		z.nombre AS zona,
		l.nombre AS local,
		inci.incidencia_txt,
		/*inci.equipo,*/
		ste.nombre as equipo,
		inci.recomendacion,
		inci.nota_tecnico,
		IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) as estado_vt,
		inci.fecha_cierre_vt,
		-- CONCAT(ifnull( perso_aptt1.nombre,''),' ',ifnull( perso_aptt1.apellido_paterno,''),' ',ifnull( perso_aptt1.apellido_materno,'')) as tecnico,
		-- CONCAT(ifnull( perso_aptt2.nombre,''),' ',ifnull( perso_aptt2.apellido_paterno,''),' ',ifnull( perso_aptt2.apellido_materno,'')) as tecnico2,
		inci.estado_servicio_tecnico_id,
		rs.nombre as razon_social
		FROM wwwapuestatotal_gestion.tbl_servicio_tecnico inci  
		LEFT JOIN tbl_locales l ON  l.id = inci.local_id    
		LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
		LEFT JOIN tbl_servicio_tecnico_equipo ste ON  ste.id = inci.equipo_id
		LEFT JOIN tbl_usuarios usu_tec ON usu_tec.id = inci.user_id
		LEFT JOIN tbl_personal_apt perso_apt ON perso_apt.id = usu_tec.personal_id
		LEFT JOIN tbl_razon_social rs on rs.id = z.razon_social_id
			-- LEFT JOIN tbl_personal_apt perso_aptt1 -- comment
			-- ON perso_aptt1.id = (
			-- 	SELECT perso_aptt.id
			-- 	FROM tbl_personal_apt perso_aptt
			-- 	LEFT JOIN tbl_usuarios usu_tec2 ON perso_aptt.id = usu_tec2.personal_id 
			-- 	LEFT JOIN tbl_servicio_tecnico_historial sthh  ON usu_tec2.id = sthh.tecnico_id
			-- 	where sthh.servicio_tecnico_id = inci.id
			-- 	order by sthh.updated_at asc
			-- 	limit 1
			-- )
			-- LEFT JOIN tbl_personal_apt perso_aptt2 -- comment
			-- ON perso_aptt2.id = (
			-- 	SELECT perso_aptt.id
			-- 	FROM tbl_personal_apt perso_aptt
			-- 	LEFT JOIN tbl_usuarios usu_tec2 ON perso_aptt.id = usu_tec2.personal_id 
			-- 	LEFT JOIN tbl_servicio_tecnico_historial sthh  ON usu_tec2.id = sthh.tecnico_id
			-- 	where sthh.servicio_tecnico_id = inci.id
			-- 	order by sthh.updated_at desc
			-- 	limit 1
			-- )
		WHERE inci.recomendacion = 'Visita Técnica'
		AND inci.estado = 1
        "
        ;

	// $query_all_count = "SELECT 
	// 	count(*) AS allcount
	// 	FROM wwwapuestatotal_gestion.tbl_servicio_tecnico inci  
	// 	WHERE inci.recomendacion = 'Visita Técnica'
	// 	AND inci.estado = 1";

	if($query_excel){
		$query_excel = $query_all  . " ORDER BY ".$columnName." ".$columnSortOrder;
		$response = array(
			  "query_excel" => $query_excel
		);
		return $response;
	}

	$query_all_1 = "SELECT 
	inci.id AS id
	FROM wwwapuestatotal_gestion.tbl_servicio_tecnico inci  
		LEFT JOIN tbl_locales l ON  l.id = inci.local_id    
		LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
		LEFT JOIN tbl_servicio_tecnico_equipo ste ON  ste.id = inci.equipo_id
		LEFT JOIN tbl_usuarios usu_tec ON usu_tec.id = inci.user_id
		LEFT JOIN tbl_personal_apt perso_apt ON perso_apt.id = usu_tec.personal_id
		LEFT JOIN tbl_razon_social rs on rs.id = z.razon_social_id
			-- LEFT JOIN tbl_personal_apt perso_aptt1 -- comment
			-- ON perso_aptt1.id = (
			-- 	SELECT perso_aptt.id
			-- 	FROM tbl_personal_apt perso_aptt
			-- 	LEFT JOIN tbl_usuarios usu_tec2 ON perso_aptt.id = usu_tec2.personal_id 
			-- 	LEFT JOIN tbl_servicio_tecnico_historial sthh  ON usu_tec2.id = sthh.tecnico_id
			-- 	where sthh.servicio_tecnico_id = inci.id
			-- 	order by sthh.updated_at asc
			-- 	limit 1
			-- )
			-- LEFT JOIN tbl_personal_apt perso_aptt2 -- comment
			-- ON perso_aptt2.id = (
			-- 	SELECT perso_aptt.id
			-- 	FROM tbl_personal_apt perso_aptt
			-- 	LEFT JOIN tbl_usuarios usu_tec2 ON perso_aptt.id = usu_tec2.personal_id 
			-- 	LEFT JOIN tbl_servicio_tecnico_historial sthh  ON usu_tec2.id = sthh.tecnico_id
			-- 	where sthh.servicio_tecnico_id = inci.id
			-- 	order by sthh.updated_at desc
			-- 	limit 1
			-- )
		WHERE inci.recomendacion = 'Visita Técnica'
		AND inci.estado = 1
	"
	;

	$query_total_filtered = "SELECT COUNT(*) AS total FROM (" . $query_all_1 . $searchQuery . $query_fecha . ") AS subquery";
	$result_total_filtered = $mysqli->query($query_total_filtered);
	$row_total_filtered = $result_total_filtered->fetch_assoc();
	$totalRecordwithFilter = $row_total_filtered['total'];

	$totalRecords = $row_total_filtered['total'];

	$limit = " LIMIT ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}

	$query_f = $query_all . $searchQuery . $query_fecha . " ORDER BY ".$columnName." ".$columnSortOrder.$limit;
	$registros = $mysqli->query($query_f);
	$data = array();

	while ($row = $registros->fetch_assoc()) {
	   $data[] = $row;
	}

	$response = array(
	  "draw" => $draw,
	  "searchValue" => $searchValue,
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);

	return $response;

}

function datatables_servidor_reporte_historial( $query_excel = false ){
	global $mysqli, $login;
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
	$incidencia_id = isset($_POST["incidencia_id"])?$_POST["incidencia_id"]:"";

	$fecha_fin = date("Y-m-d", strtotime($fecha_fin." +1 days"));
	$query_fecha = "";
	/*if($fecha_inicio != "" && $fecha_fin != ""){
		$query_fecha = "AND inci.created_at > '$fecha_inicio' AND inci.created_at < '$fecha_fin'";
	}
	if($fecha_inicio == "" && $fecha_fin != ""){
		$query_fecha = " AND inci.created_at < '$fecha_fin'";
	}
	if($fecha_fin == "" && $fecha_inicio != ""){
		$query_fecha = " AND inci.created_at > '$fecha_inicio'";
	}*/
    ## Search
	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " and (
	        l.nombre LIKE '%".$searchValue."%' or 
	        z.nombre LIKE '%".$searchValue."%'	        
			) ";
	}


    $query_all = "SELECT 
        sth.updated_at as created_at,
		ste.nombre as estado,
        inci.id AS id,
        l.nombre AS local,
        steq.nombre AS equipo,
		usu.usuario AS 'Usuario',
		usu_tec.usuario AS 'Técnico',
		sth.foto_terminado AS 'Imagen',
		sth.comentario AS 'Comentario Téc',
		sth.comentario_terminado AS 'Comentario Terminado'
        FROM tbl_servicio_tecnico_historial sth
		LEFT JOIN tbl_servicio_tecnico inci ON inci.id = sth.servicio_tecnico_id
		LEFT JOIN tbl_servicio_tecnico_estado ste ON ste.id = sth.servicio_tecnico_estado_id
		LEFT JOIN tbl_servicio_tecnico_equipo steq ON steq.id = sth.equipo_id
		LEFT JOIN tbl_usuarios usu ON usu.id = sth.user_id
		LEFT JOIN tbl_usuarios usu_tec ON usu_tec.id = sth.tecnico_id
		LEFT JOIN tbl_locales l ON l.id = inci.local_id
		WHERE sth.servicio_tecnico_id = $incidencia_id        
        $searchQuery
        "
        ;

	if($query_excel){
		$query_excel = $query_all  . " ORDER BY ".$columnName." ".$columnSortOrder;
		$response = array(
			  "query_excel" => $query_excel
		);
		return $response;
	}

    $sel = $mysqli->query("SELECT count(*) AS allcount FROM ( $query_all ) as  A ");
	$records = $sel->fetch_assoc();
	$totalRecords = $records['allcount'];

	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$query_all.") AS subquery");	
	$records = $sel->fetch_assoc();
	$totalRecordwithFilter = $records['allcount'];

	$limit = " LIMIT ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}
	$query_f = $query_all . " ORDER BY ".$columnName." ".$columnSortOrder.$limit;
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

if(isset($_POST["sec_servicio_tecnico_cargar_locales"])){
	$zona_id = $_POST["zona_id"];
	$zona_where = $zona_id != "" ? " AND l.zona_id IN (" . implode("," , $zona_id ). ") " : "";
	$command ="SELECT 
		l.id,
		l.nombre
		FROM tbl_locales l 
		WHERE reportes_mostrar = 1
		AND estado = 1 " 
		. $zona_where;
	$list_query=$mysqli->query($command);
	$list = array();
	while ($li=$list_query->fetch_assoc()) {
		$list[]=$li;
	}
	$return["locales"] = $list;
}

if(isset($_POST["action"]) && $_POST["action"]=="sec_servicio_tecnico_reporte_list_excel"){
	$query_excel = datatables_servidor(true)["query_excel"];
	$registros = $mysqli->query($query_excel);
	$data = array();
	while ($row = $registros->fetch_assoc()) {
		unset($row["id"]);
		$data[] = $row;
	}
	$file_version = date('YmdHis');
	$filename ="servicio_tecnico_reporte" ."_at_" . $file_version . ".xls";
	$filesPath = '/var/www/html/export/servicio_tecnico_reporte/';
    
    $cabeceras = ["FECHA DE INGRESO","ZONA","TIENDA","DESCRI. INCIDENTE","EQUIPO","RECOMENDACIÓN","NOTA PARA EL TÉCNICO","ESTADO","FECHA DE CIERRE","NOMBRE DEL TECNICO 1","NOMBRE DEL TECNICO 2"];
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
	$doc->getActiveSheet()->getColumnDimension('C')->setWidth(20);
	$doc->getActiveSheet()->getColumnDimension('D')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('E')->setWidth(16);
	$doc->getActiveSheet()->getColumnDimension('F')->setWidth(21);
	$doc->getActiveSheet()->getColumnDimension('G')->setWidth(28);
	$doc->getActiveSheet()->getColumnDimension('H')->setWidth(18);
	$doc->getActiveSheet()->getColumnDimension('I')->setWidth(18);
	$doc->getActiveSheet()->getColumnDimension('J')->setWidth(26);
	$doc->getActiveSheet()->getColumnDimension('K')->setWidth(26);

    $doc->getActiveSheet()->fromArray($data, null, 'A1', true);
    $attach = $filesPath . $filename;
    $objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
    $objWriter->save($attach);

	$response = array(
		"curr_login" => $login,
		"path" => '/export/servicio_tecnico_reporte/'.$filename
	);
	echo json_encode($response);
	return;
    //header('Location: /export/servicio_tecnico_reporte/'.$filename);
}
if(isset($_POST["action"]) && $_POST["action"]=="sec_servicio_tecnico_list"){
	$response = datatables_servidor();
	echo json_encode($response);
	return;
}

if(isset($_POST["action"]) && $_POST["action"]=="sec_servicio_tecnico_reporte_historial_list_excel"){
	$query_excel = datatables_servidor(true)["query_excel"];
	$registros = $mysqli->query($query_excel);
	$data = array();
	while ($row = $registros->fetch_assoc()) {
		unset($row["id"]);
		$data[] = $row;
	}
	$file_version = date('YmdHis');
	$filename ="servicio_tecnico_reporte" ."_at_" . $file_version . ".xls";
	$filesPath = '/var/www/html/export/servicio_tecnico_reporte/';
    
    $cabeceras = ["FECHA DE INGRESO","ZONA","TIENDA","DESCRI. INCIDENTE","EQUIPO","RECOMENDACIÓN","NOTA PARA EL TÉCNICO","ESTADO","FECHA DE CIERRE","NOMBRE DEL TECNICO 1","NOMBRE DEL TECNICO 2"];
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
		"path" => '/export/servicio_tecnico_reporte/'.$filename
	);
	echo json_encode($response);
	return;
    //header('Location: /export/servicio_tecnico_reporte/'.$filename);
}
if(isset($_POST["action"]) && $_POST["action"]=="sec_servicio_tecnico_reporte_historial_list"){

	$response = datatables_servidor_reporte_historial();
	echo json_encode($response);
	return;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>