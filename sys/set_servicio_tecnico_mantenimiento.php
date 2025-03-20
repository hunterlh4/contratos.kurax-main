<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/phpexcel/classes/PHPExcel.php';
require_once '/var/www/html/sys/helpers.php';
require_once '/var/www/html/class/clsServicioTecnico.php';

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

function datatables_servidor( $query_excel = false ){
	global $mysqli, $login;

	// COMPARAR ARRAY DE LOCALES DEL USUARIO
	$locales_usuario = [];

	$select_locales_usuarios = "SELECT id,red_id FROM tbl_locales WHERE id IN (".implode(",", $login["usuario_locales"]).") ";
	$result_locales_usuarios = mysqli_query($mysqli,$select_locales_usuarios);
	while ($row_locales_usuarios = mysqli_fetch_assoc($result_locales_usuarios)) {
		//if($row_locales_usuarios['red_id'] == 1 || $row_locales_usuarios['red_id'] == 16){
			array_push($locales_usuario, $row_locales_usuarios['id']);
		//}
	}
	if(empty($locales_usuario)){
		array_push($locales_usuario, 0); 
	}

	//$ID_LOGIN = $login["id"];
	$data = $_POST["action"];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
    $columns = $_POST['columns'];
	$columnName = 'IF(inci.updated_at  IS NULL, inci.created_at, inci.updated_at )'; // Column name
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
	        z.nombre LIKE '%".$searchValue."%' or 
	        ste.nombre LIKE '%".$searchValue."%' or 
	        inci.incidencia_txt LIKE '%".$searchValue."%' or 
	        inci.equipo LIKE '%".$searchValue."%' or 
	        inci.nota_tecnico LIKE '%".$searchValue."%' or 
	        inci.recomendacion LIKE '%".$searchValue."%' or 
            rs.nombre LIKE '%".$searchValue."%' or 
            udep.nombre LIKE '%".$searchValue."%' or
	        IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) LIKE '%".$searchValue."%' or
			IF(inci.updated_at  IS NULL, inci.created_at, inci.updated_at ) LIKE '%".$searchValue."%' or
			l.nombre LIKE '%".$searchValue."%'
			) ";
	}
	//col 2  zona
	/*if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.nombre in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}*/
	if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND (l.zona_id in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}
    if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" || z.nombre in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."'))";
	}
	//col 3  local
	/*if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" AND l.nombre in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."')";
	}*/
	if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" AND (l.id in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."')";
	}
    if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" || l.nombre in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."'))";
	}
	//col 4  sistena
	// if($_POST["columns"][4]["search"]["value"] != "" && $_POST["columns"][4]["search"]["value"] != "null"){
	// 	$searchQuery.=	" AND inci.incidencia_txt in ('".str_replace(",", "','", $_POST["columns"][4]["search"]["value"])."')";
	// }
	//col 5 Equipo
	if($_POST["columns"][5]["search"]["value"] != "" && $_POST["columns"][5]["search"]["value"] != "null"){
		$searchQuery.=	" AND ste.id in ('".str_replace(",", "','", $_POST["columns"][5]["search"]["value"])."')";
	}
	//col 8  estado
	if($_POST["columns"][8]["search"]["value"] != "" && $_POST["columns"][8]["search"]["value"] != "null"){
		$searchQuery.=	" AND  IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) in ('".str_replace(",", "','", $_POST["columns"][8]["search"]["value"])."')";
	}
	//col 10 razon_social
	if($_POST["columns"][10]["search"]["value"] != "" && $_POST["columns"][10]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.razon_social_id in ('".str_replace(",", "','", $_POST["columns"][10]["search"]["value"])."')";
	}

	//LISTAR SOLO LOCALES DEL USUARIO
	if ($login["usuario_locales"]) {
		$searchQuery .= " AND l.id IN (".implode(",", $locales_usuario).") ";
	}

    $query_all = "SELECT 
        inci.created_at AS created_at,
        inci.id AS id,
		l.cc_id,
        z.nombre AS zona,
        max(udep.nombre) as departamento,
        l.nombre AS local,
        inci.incidencia_txt,
        /*inci.equipo,*/
        ste.nombre as equipo,
        inci.recomendacion,
        inci.nota_tecnico,
        IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) as estado_vt,
        inci.fecha_cierre_vt,
        inci.estado_servicio_tecnico_id,
        inci.estado as estado_id,
		z.razon_social_id,
        -- CONCAT(ifnull( perso_aptt1.nombre,''),' ',ifnull( perso_aptt1.apellido_paterno,''),' ',ifnull( perso_aptt1.apellido_materno,'')) as tecnico,
 		-- CONCAT(ifnull( perso_aptt2.nombre,''),' ',ifnull( perso_aptt2.apellido_paterno,''),' ',ifnull( perso_aptt2.apellido_materno,'')) as tecnico2,
		rs.nombre as razon_social,
        (select t_revision from tbl_solicitud_estimacion_servicio_tecnico se
			left join tbl_solicitud_estimacion_zona sezz on sezz.id = se.solicitud_estimacion_zona_id
			where se.equipo_id = ste.id and sezz.zona_id = z.id and sezz.estado = 1 and sezz.razon_social_id = z.razon_social_id
            AND IF(max(udep.nombre) is null, provincia is null, provincia = max(udep.nombre))
        ) as t_revision,
        (select t_coordinacion from tbl_solicitud_estimacion_servicio_tecnico se
			left join tbl_solicitud_estimacion_zona sezz on sezz.id = se.solicitud_estimacion_zona_id
			where se.equipo_id = ste.id and sezz.zona_id = z.id and sezz.estado = 1 and sezz.razon_social_id = z.razon_social_id
            AND IF(max(udep.nombre) is null, provincia is null, provincia = max(udep.nombre))
        ) as t_coordinacion,
        (select valor_v from tbl_solicitud_estimacion_servicio_tecnico se
			left join tbl_solicitud_estimacion_zona sezz on sezz.id = se.solicitud_estimacion_zona_id
			where se.equipo_id = ste.id and sezz.zona_id = z.id and sezz.estado = 1 and sezz.razon_social_id = z.razon_social_id
            AND IF(max(udep.nombre) is null, provincia is null, provincia = max(udep.nombre))
        ) as valor_v,
        (select valor_e from tbl_solicitud_estimacion_servicio_tecnico se
			left join tbl_solicitud_estimacion_zona sezz on sezz.id = se.solicitud_estimacion_zona_id
			where se.equipo_id = ste.id and sezz.zona_id = z.id and sezz.estado = 1 and sezz.razon_social_id = z.razon_social_id 
            AND IF(max(udep.nombre) is null, provincia is null, provincia = max(udep.nombre))
        ) as valor_e,
        (0) as total_dias,
        (0) as cantidad_domingos,
        (0) as total_formula
        FROM wwwapuestatotal_gestion.tbl_servicio_tecnico inci
        LEFT JOIN tbl_locales l ON  l.id = inci.local_id    
        LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
        LEFT JOIN tbl_servicio_tecnico_equipo ste ON  ste.id = inci.equipo_id
        LEFT JOIN tbl_usuarios usu_tec ON usu_tec.id = inci.user_id
        LEFT JOIN tbl_personal_apt perso_apt ON perso_apt.id = usu_tec.personal_id
        LEFT JOIN tbl_ubigeo udep ON (
			udep.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			udep.cod_prov = '00' AND
			udep.cod_dist = '00'
		)
		LEFT JOIN tbl_razon_social rs on rs.id = z.razon_social_id
        -- LEFT JOIN tbl_personal_apt perso_aptt1 -- comment
 		-- 	ON perso_aptt1.id = (
 		-- 		SELECT perso_aptt.id
 		-- 		FROM tbl_personal_apt perso_aptt
 		-- 		LEFT JOIN tbl_usuarios usu_tec2 ON perso_aptt.id = usu_tec2.personal_id 
 		-- 		LEFT JOIN tbl_servicio_tecnico_historial sthh  ON usu_tec2.id = sthh.tecnico_id
 		-- 		where sthh.servicio_tecnico_id = inci.id
 		-- 		order by sthh.updated_at asc
 		-- 		limit 1
 		-- 	)
 		-- 	LEFT JOIN tbl_personal_apt perso_aptt2 -- comment
 		-- 	ON perso_aptt2.id = (
 		-- 		SELECT perso_aptt.id
 		-- 		FROM tbl_personal_apt perso_aptt
 		-- 		LEFT JOIN tbl_usuarios usu_tec2 ON perso_aptt.id = usu_tec2.personal_id 
 		-- 		LEFT JOIN tbl_servicio_tecnico_historial sthh  ON usu_tec2.id = sthh.tecnico_id
 		-- 		where sthh.servicio_tecnico_id = inci.id
 		-- 		order by sthh.updated_at desc
 		-- 		limit 1
 		-- 	)
        WHERE inci.recomendacion = 'Visita Técnica'
		AND inci.estado = 1
        "
        ;
	
	if($query_excel){
		$query_excel = $query_all . $searchQuery . $query_fecha . " GROUP BY inci.id ORDER BY ".$columnName." ".$columnSortOrder;
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
	LEFT JOIN tbl_ubigeo udep ON (
		udep.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
		udep.cod_prov = '00' AND
		udep.cod_dist = '00'
	)
	LEFT JOIN tbl_razon_social rs on rs.id = z.razon_social_id
	WHERE inci.recomendacion = 'Visita Técnica'
	AND inci.estado = 1
	"
	;

	$query_total_filtered = "SELECT COUNT(*) AS total FROM (" . $query_all_1 . $searchQuery . $query_fecha . "GROUP BY inci.id ".") AS subquery";
	$result_total_filtered = $mysqli->query($query_total_filtered);
	$row_total_filtered = $result_total_filtered->fetch_assoc();
	$totalRecordwithFilter = $row_total_filtered['total'];

	$totalRecords = $row_total_filtered['total'];

	$limit = " LIMIT ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}

	$query_f = $query_all . $searchQuery . $query_fecha . " GROUP BY inci.id ORDER BY ".$columnName." ".$columnSortOrder.$limit;
	$registros = $mysqli->query($query_f);
	$data = array();

	while ($row = $registros->fetch_assoc()) {
        // $total_dias = null;
        // if($row["t_revision"] != null && $row["t_coordinacion"] != null && $row["valor_v"] != null && $row["valor_e"] != null){
        //     $total_dias = $row["t_revision"] + $row["t_coordinacion"] + $row["valor_v"] + $row["valor_e"];
        // }
        // $row["total_dias"] = $total_dias;
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

// function datatables_servidor_reporte_historial( $query_excel = false ){
// 	global $mysqli, $login;
// 	$ID_LOGIN = $login["id"];
// 	$data = $_POST["action"];
// 	$draw = $_POST['draw'];
// 	$row = $_POST['start'];
// 	$rowperpage = $_POST['length']; // Rows display per page
// 	$columnIndex = $_POST['order'][0]['column']; // Column index
// 	$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
// 	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
// 	$searchValue = $_POST['search']['value']; // Search value
// 	$searchValue = $mysqli->real_escape_string($searchValue);

// 	$fecha_inicio = isset($_POST["fecha_inicio"])?$_POST["fecha_inicio"]:"";
// 	$fecha_fin = isset($_POST["fecha_fin"])?$_POST["fecha_fin"]:"";
// 	$incidencia_id = isset($_POST["incidencia_id"])?$_POST["incidencia_id"]:"";

// 	$fecha_fin = date("Y-m-d", strtotime($fecha_fin." +1 days"));
// 	$query_fecha = "";
// 	/*if($fecha_inicio != "" && $fecha_fin != ""){
// 		$query_fecha = "AND inci.created_at > '$fecha_inicio' AND inci.created_at < '$fecha_fin'";
// 	}
// 	if($fecha_inicio == "" && $fecha_fin != ""){
// 		$query_fecha = " AND inci.created_at < '$fecha_fin'";
// 	}
// 	if($fecha_fin == "" && $fecha_inicio != ""){
// 		$query_fecha = " AND inci.created_at > '$fecha_inicio'";
// 	}*/
//     ## Search
// 	$searchQuery = " ";
// 	if($searchValue != ''){
// 	   $searchQuery = " and (
// 	        l.nombre LIKE '%".$searchValue."%' or 
// 	        z.nombre LIKE '%".$searchValue."%'	        
// 			) ";
// 	}


//     $query_all = "SELECT 
//         sth.updated_at as created_at,
// 		ste.nombre as estado,
//         inci.id AS id,
//         l.nombre AS local,
//         steq.nombre AS equipo,
// 		usu.usuario AS 'Usuario',
// 		usu_tec.usuario AS 'Técnico',
// 		sth.foto_terminado AS 'Imagen',
// 		sth.comentario AS 'Comentario Téc',
// 		sth.comentario_terminado AS 'Comentario Terminado'
//         FROM tbl_servicio_tecnico_historial sth
// 		LEFT JOIN tbl_soporte_incidencias inci ON inci.id = sth.servicio_tecnico_id
// 		LEFT JOIN tbl_servicio_tecnico_estado ste ON ste.id = sth.servicio_tecnico_estado_id
// 		LEFT JOIN tbl_servicio_tecnico_equipo steq ON steq.id = sth.equipo_id
// 		LEFT JOIN tbl_usuarios usu ON usu.id = sth.user_id
// 		LEFT JOIN tbl_usuarios usu_tec ON usu_tec.id = sth.tecnico_id
// 		LEFT JOIN tbl_locales l ON l.id = inci.local_id
// 		WHERE sth.servicio_tecnico_id = $incidencia_id        
//         $searchQuery
//         "
//         ;

// 	if($query_excel){
// 		$query_excel = $query_all  . " ORDER BY ".$columnName." ".$columnSortOrder;
// 		$response = array(
// 			  "query_excel" => $query_excel
// 		);
// 		return $response;
// 	}

//     $sel = $mysqli->query("SELECT count(*) AS allcount FROM ( $query_all ) as  A ");
// 	$records = $sel->fetch_assoc();
// 	$totalRecords = $records['allcount'];

// 	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$query_all.") AS subquery");	
// 	$records = $sel->fetch_assoc();
// 	$totalRecordwithFilter = $records['allcount'];

// 	$limit = " LIMIT ".$row.",".$rowperpage;
// 	if($rowperpage==-1){
// 		$limit="";
// 	}
// 	$query_f = $query_all . " ORDER BY ".$columnName." ".$columnSortOrder.$limit;
// 	$registros = $mysqli->query($query_f);
// 	$data = array();

// 	while ($row = $registros->fetch_assoc()) {
// 	   $data[] = $row;
// 	}

// 	$response = array(
// 		"draw" => $draw,
// 		"iTotalRecords" => $totalRecords,
// 		"iTotalDisplayRecords" => $totalRecordwithFilter,
// 		"aaData" => $data
// 	);
// 	return $response;

// }

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

function calcular_domingos($fechaInicio, $fechaFinal) {
    if ($fechaInicio > $fechaFinal) {
        return $fechaFinal;
    }
    
    $diaActual = date('w', $fechaInicio); // Obtener el día de la semana (0 para domingo, 6 para sábado)
    
    if ($diaActual == 0) { // Si es domingo
        $fechaFinal = strtotime('+1 day', $fechaFinal);
    }
    
    $fechaInicio = strtotime('+1 day', $fechaInicio);
    
    // if ($diaActual == 6) { // Si es sábado
    //     $fechaFinal = strtotime('+1 day', $fechaFinal);
    // }
    
    return calcular_domingos($fechaInicio, $fechaFinal);
}

if(isset($_POST["action"]) && $_POST["action"]=="sec_servicio_tecnico_list_excel"){
	$query_excel = datatables_servidor(true)["query_excel"];
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
            
            // $fecha_limite = calcular_domingos($date_fecha_inicio, $date_fecha_final);
            
            //$diferencia = strtotime(date('Y-m-d',$fecha_limite)) - strtotime($fecha_inicio);

			$diferencia = strtotime($fecha_fin) - strtotime($fecha_actual);
            $diferencia_dias = $diferencia / (60 * 60 * 24); 
            
            $dias = " días";
            if($diferencia_dias == 1 || $total_dias == 1){
                $dias = " día";
            }
            
            // $fecha_actual = date("Y-m-d");
            $fecha_cierre = $row["fecha_cierre_vt"];
            $date_fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
            if($row["estado_vt"] != "Terminado"){
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
        unset($row["estado_servicio_tecnico_id"]);
        unset($row["estado_id"]);
        unset($row["razon_social_id"]);
        unset($row["t_revision"]);
        unset($row["t_coordinacion"]);
        unset($row["valor_v"]);
        unset($row["valor_e"]);
        unset($row["cantidad_domingos"]);
        unset($row["total_dias"]);
        unset($row["total_formula"]);
		$data[] = $row;
	}

	$file_version = date('YmdHis');
	$filename ="solicitud_estimacion_servicio_tecnico" ."_at_" . $file_version . ".xls";
	$filesPath = '/var/www/html/export/servicio_tecnico_reporte/';
    
	// $cabeceras = ["FECHA DE INGRESO","ZONA","DEPARTAMENTO","TIENDA","DESCRI. INCIDENTE","EQUIPO","RECOMENDACIÓN","NOTA PARA EL TÉCNICO","ESTADO","FECHA DE CIERRE","NOMBRE DEL TECNICO 1","NOMBRE DEL TECNICO 2","RAZÓN SOCIAL","DÍAS DE ATENCIÓN","TIEMPO OBJETIVO","DIFERENCIA"];
    $cabeceras = ["FECHA DE INGRESO","ID","CC_ID","ZONA","DEPARTAMENTO","TIENDA","DESCRI. INCIDENTE","EQUIPO","RECOMENDACIÓN","NOTA PARA EL TÉCNICO","ESTADO","FECHA DE CIERRE","RAZÓN SOCIAL","DÍAS DE ATENCIÓN","TIEMPO OBJETIVO","DIFERENCIA"];
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
	$doc->getActiveSheet()->getColumnDimension('E')->setWidth(16);
	$doc->getActiveSheet()->getColumnDimension('F')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('G')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('H')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('I')->setWidth(16);
	$doc->getActiveSheet()->getColumnDimension('J')->setWidth(22);
	$doc->getActiveSheet()->getColumnDimension('K')->setWidth(16);
    $doc->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $doc->getActiveSheet()->getColumnDimension('M')->setWidth(18);
    $doc->getActiveSheet()->getColumnDimension('N')->setWidth(18);
    $doc->getActiveSheet()->getColumnDimension('O')->setWidth(14);
    $doc->getActiveSheet()->getColumnDimension('P')->setWidth(14);

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

// if(isset($_POST["action"]) && $_POST["action"]=="sec_servicio_tecnico_reporte_historial_list_excel"){
// 	$query_excel = datatables_servidor(true)["query_excel"];
// 	$registros = $mysqli->query($query_excel);
// 	$data = array();
// 	while ($row = $registros->fetch_assoc()) {
// 		unset($row["id"]);
// 		$data[] = $row;
// 	}
// 	$file_version = date('YmdHis');
// 	$filename ="servicio_tecnico_reporte" ."_at_" . $file_version . ".xls";
// 	$filesPath = '/var/www/html/export/servicio_tecnico_reporte/';
    
//     $cabeceras = ["FECHA DE INGRESO","ZONA","TIENDA","DESCRI. INCIDENTE","EQUIPO","RECOMENDACIÓN","NOTA PARA EL TÉCNICO","ESTADO","FECHA DE CIERRE","NOMBRE DEL TECNICO 1","NOMBRE DEL TECNICO 2"];
//     array_unshift($data , $cabeceras);

//     $doc = new PHPExcel();
//     $doc->setActiveSheetIndex(0);

//     $estiloNombresFilas = array(
// 		'font' => array(
// 	        'name'      => 'Calibri',
// 	        'bold'      => true,
// 	        'italic'    => false,
// 	        'strike'    => false,
// 	        'size' =>11,
// 	        'color'     => array(
// 	            'rgb' => '000000'
// 	        )
// 	    ),
// 	    'alignment' =>  array(
// 	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
// 	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
// 	        'wrap'      => false
// 	    )
// 	);
// 	$doc->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloNombresFilas);
// 	$doc->getActiveSheet()->getColumnDimension('A')->setWidth(18);
// 	$doc->getActiveSheet()->getColumnDimension('B')->setWidth(12);
// 	$doc->getActiveSheet()->getColumnDimension('C')->setWidth(13);
// 	$doc->getActiveSheet()->getColumnDimension('D')->setWidth(13);
// 	$doc->getActiveSheet()->getColumnDimension('F')->setWidth(21);
// 	$doc->getActiveSheet()->getColumnDimension('H')->setWidth(18);

//     $doc->getActiveSheet()->fromArray($data, null, 'A1', true);
//     $attach = $filesPath . $filename;
//     $objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
//     $objWriter->save($attach);

// 	$response = array(
// 		"curr_login" => $login,
// 		"path" => '/export/servicio_tecnico_reporte/'.$filename
// 	);
// 	echo json_encode($response);
// 	return;
//     //header('Location: /export/servicio_tecnico_reporte/'.$filename);
// }
// if(isset($_POST["action"]) && $_POST["action"]=="sec_servicio_tecnico_reporte_historial_list"){

// 	$response = datatables_servidor_reporte_historial();
// 	echo json_encode($response);
// 	return;
// }

if(isset($_POST["sec_servicio_tecnico_cargar_solicitud"])){
	$solicitud_id = $_POST["solicitud_id"];
	
		$command ="SELECT inci.id AS id,
			l.nombre AS local,
			IF(z.nombre IS NULL , '---', z.nombre ) AS zona,	 
			inci.id AS incidencia_id,
			inci.incidencia_txt  as reporte,	 
			inci.equipo ,
			inci.equipo_id ,
			IF(inci.estado_servicio_tecnico_id IS NULL OR inci.estado_vt = 'Derivado' , '' ,inci.estado_servicio_tecnico_id) as estado,
			inci.created_at,
			inci.updated_at,				 
			inci.comentario_vt as comentario,
			sth.foto_terminado as foto_terminado,
            inci.nota_tecnico,
            sth.comentario_terminado,
            inci.updated_at,
			sth.tecnico_id,
			rs.id as razon_social_id,
			rs.nombre as razon_social
			FROM tbl_servicio_tecnico inci
			LEFT JOIN tbl_servicio_tecnico_historial sth on sth.servicio_tecnico_id = inci.id
			LEFT JOIN tbl_locales l ON  l.id = inci.local_id
			LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
			LEFT JOIN tbl_servicio_tecnico_equipo ste ON  ste.id = inci.equipo_id
			LEFT JOIN tbl_razon_social rs ON rs.id = z.razon_social_id
			WHERE inci.id = {$solicitud_id}
			order by sth.id desc
            limit 1";
		$list_query = $mysqli->query($command);
		$list = $list_query->fetch_assoc();

	
	$return["local"] = $list;
}

// UPDATE MODAL
if(isset($_POST["set_servicio_tecnico_update"])){
	$user_id = $login["id"];
	extract($_POST);
	if(!isset($estado) || $estado == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe Seleccionar Estado";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}
	if(!isset($equipo_id)){
		$return["error"] = "equipo";
		$return["error_msg"] = "Debe Seleccionar Equipo";
		$return["error_focus"] = "equipo";
		die(json_encode($return));
	}
	if( $estado == 2 ){
		if( !isset($tecnico_id) || $tecnico_id == "" ){
			$return["error"] = "tecnico_id";
			$return["error_msg"] = "Debe Seleccionar Técnico";
			$return["error_focus"] = "tecnico_id";
			die(json_encode($return));
		}
		if( strlen($comentario) > 200)
		{
			$return["error"] = "comentario";
			$return["error_msg"] = "Comentario no mayor a 200 caracteres";
			$return["error_focus"] = "comentario";
			die(json_encode($return));
		}
		//$tecnico_id = ",tecnico_id = '" . $tecnico_id . "'";
	}
	if($estado == 4){
		if((!isset($comentario_terminado_editar) || $comentario_terminado_editar == "") && !file_exists($_FILES['foto_terminado_update']['tmp_name'])){
			$return["error"] = "comentario o foto terminado";
			$return["error_msg"] = "El campo comentario o foto es necesario";
			$return["error_focus"] = "comentario o foto terminado";
			die(json_encode($return));
		}
	}

	if( $estado == 4 ){
		$command = "SELECT inci.id AS id,
			inci.id AS incidencia_id,
			l.nombre AS local,
			IF(z.nombre IS NULL , '---', z.nombre ) AS zona,
			inci.incidencia_txt  as reporte,
			inci.equipo_id ,
			/*sth.servicio_tecnico_estado_id as estado ,*/
			/*ste.nombre as estado ,*/
			ste.id as estado ,
			inci.created_at,
			inci.updated_at,
			IF(sth.comentario_terminado IS NULL , '---', sth.comentario_terminado ) AS comentario_terminado,
			sth.foto_terminado,
			sth.tecnico_id,
			inci.nota_tecnico,
			inci.local_id,
			steq.nombre as equipo_nombre
			FROM tbl_servicio_tecnico inci
			LEFT JOIN tbl_locales l ON  l.id = inci.local_id
			LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
			LEFT JOIN tbl_servicio_tecnico_estado  ste ON ste.id = inci.id
			LEFT JOIN tbl_servicio_tecnico_equipo steq ON steq.id = inci.equipo_id
			LEFT JOIN tbl_servicio_tecnico_historial sth ON sth.servicio_tecnico_id = inci.id 
			where inci.id = {$id}
			order by id desc
			limit 1";
				
		$data_command = $mysqli->query($command)->fetch_assoc();
		$local_id = $data_command["local_id"];
		
		$command_supervisor_correos = "SELECT p.correo
		FROM tbl_usuarios_locales ul
		LEFT JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
		LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
		WHERE ul.local_id = ". $local_id ." AND ul.estado = '1' 
		AND (p.area_id = ('21') AND p.cargo_id IN (4,5) OR (p.area_id = ('22') AND p.cargo_id != 3)
			OR (p.area_id = '31' AND p.cargo_id = 5)
			OR (p.area_id = '28' AND p.cargo_id = 5)
		)
		AND (p.cargo_id = 16 OR p.cargo_id = 4)";

		$correos = [];
		$data_command_supervisor_correos = $mysqli->query($command_supervisor_correos);				
		while($supervisores_correos = $data_command_supervisor_correos->fetch_assoc()){
			if($supervisores_correos['correo'] != ""){
				array_push($correos,$supervisores_correos['correo']);
			}
		}	
		if(empty($correos)){
			$return["error"] = "correo";
			$return["error_msg"] = "El jefe comercial y supervisor no cuentan con correos asignados";
			$return["error_focus"] = "correo";
			die(json_encode($return));
		}
	}
	$fecha_cierre = "null";
	$foto_terminado = "";
	if($estado == 4 )
	{
		$fecha_cierre = "now()";
		if(file_exists($_FILES['foto_terminado_update']['tmp_name'])){
			$path = "/var/www/html/files_bucket/servicio_tecnico/";
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
				die(json_encode($return));
			}
			if(!in_array($ext, $valid_extensions)) {
				$return["error"]="ext";
				$return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
				die(json_encode($return));
			}
			if($size > 10485760){//10 mb
				$return["error"]="size";
				$return["error_msg"] ="Archivo supera la cantidad máxima permitida (10 MB)";
				die(json_encode($return));
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
			$foto_terminado = $nombre_archivo;
		}
	}

	$equipo_id = isset($equipo_id) ? "'$equipo_id'" : "null";
	$tecnico_id = isset($tecnico_id) && $tecnico_id != "" ? "'$tecnico_id'" : "null";
    $comentario = isset($comentario) && $comentario != "''" ? "'$comentario'" : "---";
	$comentario_terminado = isset($comentario_terminado) && $comentario_terminado != "" ? "'$comentario_terminado'" : "---";
	$comentario_terminado_editar = isset($comentario_terminado_editar) && $comentario_terminado_editar != "" ? "$comentario_terminado_editar" : "---";

    // $command ="SELECT estado_servicio_tecnico_id
	// 		FROM tbl_soporte_incidencias inci		
	// 		WHERE inci.id = {$incidencia_id}";

	$command ="SELECT sete.estado
			FROM tbl_servicio_tecnico sete		
			WHERE sete.id = {$incidencia_id}";
	$query_inci = $mysqli->query($command)->fetch_assoc();
	if($query_inci["estado"] !=  $estado){ //insert
		$insert_command = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_servicio_tecnico_historial`
		(
			servicio_tecnico_id
			,servicio_tecnico_estado_id
			,equipo_id
			,comentario
			,user_id
			,foto_terminado
			,comentario_terminado
			,tecnico_id
			,created_at
			,updated_at
		)
			VALUES
		(
			 $id
			,$estado
			,$equipo_id
			," . $comentario . "
			, " . $login["id"] . "
			, '" . $foto_terminado ."'
			, '" . $comentario_terminado_editar ."'
			,$tecnico_id
			,NOW()
			,NOW()
		)
		"
		;
		$return["insert_command"] = $insert_command;
		$mysqli->query($insert_command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command;
			exit();
		}
	}
	else
	{
		$insert_command = "UPDATE  `wwwapuestatotal_gestion`.`tbl_servicio_tecnico_historial` SET
			equipo_id = $equipo_id
			,tecnico_id = $tecnico_id
			,comentario = " . $comentario . "
			,comentario_terminado = '" . $comentario_terminado_editar . "'
			,user_id =  " . $login["id"] . "
			,foto_terminado =  '" . $foto_terminado ."'			
			,updated_at = NOW()
		WHERE id = $id
		"
		;
		$return["insert_command"] = $insert_command;
		$mysqli->query($insert_command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command;
			exit();
		}

	}

	// $command = "UPDATE tbl_soporte_incidencias
	// 			SET 
	// 				 estado_vt = '$estado_vt'
	// 				,estado_servicio_tecnico_id = $estado
	// 				,equipo_id = $equipo_id
	// 				,updated_at = now()
	// 				,update_user_id = $user_id
	// 				,fecha_cierre_vt = $fecha_cierre					
	// 			WHERE id = " . $id;

    $query_servicio_tecnico_estado = "select nombre from tbl_servicio_tecnico_estado
    where estado = 1
    and id = {$estado}";

    $query_estado_serv = $mysqli->query($query_servicio_tecnico_estado);	
	$estado_prob = $query_estado_serv->fetch_assoc();

	$esta_nombre = $estado_prob['nombre'];

    $command = "UPDATE tbl_servicio_tecnico
				SET 
					 estado_vt = '$esta_nombre'
                    ,estado_servicio_tecnico_id = $estado
					,equipo_id = $equipo_id
                    ,comentario_vt = $comentario
                    -- ,foto = '$foto_terminado'
					,updated_at = now()
					,update_user_id = $user_id
					-- ,fecha_solucion = $fecha_cierre
                    ,fecha_cierre_vt = $fecha_cierre					
				WHERE id = " . $incidencia_id;

	$mysqli->query($command);
	$return["update_command"] = $command;
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}

	if($estado == 4 ){
		$comentario_terminado_correo = $comentario_terminado_editar;
		$razon_social_id = $razon_social_id;
		$obj_correo = new clsServicioTecnico();
		$response_mail = $obj_correo->enviar_correo_terminado($correos, $incidencia_id, $data_command, $comentario_terminado_correo, $razon_social_id);

		if($response_mail == "ok"){
			$return["email_sent"]="ok";
		}else{
			$return["error"]=$response_mail;
			$return["error_msg"]=$response_mail;
			$return["email_error"]=$response_mail;
		}
	}

	$id_servicio_tecnico_historial = $mysqli->insert_id;
	$return["mensaje"] = "Solicitud Servicio Técnico ".$id." Actualizada";
	$return["curr_login"] = $login;
	$return["id_servicio_tecnico_historial"] = $id_servicio_tecnico_historial;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>