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

	$col_fecha = "e.created_at";
	if($fecha_inicio != "" && $fecha_fin != ""){
		$query_fecha = "AND $col_fecha > '$fecha_inicio' AND $col_fecha < '$fecha_fin'";
	}
	if($fecha_inicio == "" && $fecha_fin != ""){
		$query_fecha = " AND $col_fecha < '$fecha_fin'";
	}
	if($fecha_fin == "" && $fecha_inicio != ""){
		$query_fecha = " AND $col_fecha > '$fecha_inicio'";
	}
    ## Search
	$searchQuery = " ";
	if($searchValue != ''){
		$searchValue = trim($searchValue);
	   $searchQuery = " and (
	        l.nombre LIKE '%".$searchValue."%' OR 
	        z.nombre LIKE '%".$searchValue."%' OR 
	        ee.nombre LIKE '%".$searchValue."%' OR 
	        CONCAT(swt.client_id, ' - ', swt.client_name) LIKE '%".$searchValue."%' OR 
	        CONCAT(
                IF( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
                IF( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
                IF( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
            ) LIKE '%".$searchValue."%' OR
			CONCAT(
                IF( LENGTH( pl_sop.apellido_paterno ) > 0, CONCAT( UPPER( pl_sop.apellido_paterno ), ' ' ), '' ),
                IF( LENGTH( pl_sop.apellido_materno ) > 0, CONCAT( UPPER( pl_sop.apellido_materno ), ' ' ), '' ),
                IF( LENGTH( pl_sop.nombre ) > 0, UPPER( pl_sop.nombre ), '' ) 
            )  LIKE '%".$searchValue."%'
			) ";
	}
    $col = 1;
	if($_POST["columns"][$col]["search"]["value"] != "" && $_POST["columns"][$col]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.id in ('".str_replace(",", "','", $_POST["columns"][$col]["search"]["value"])."')";
	}
    $col = 2 ;
	if($_POST["columns"][$col]["search"]["value"] != "" && $_POST["columns"][$col]["search"]["value"] != "null"){
		$searchQuery.=	" AND swt.tipo_id in ('".str_replace(",", "','", $_POST["columns"][$col]["search"]["value"])."')";
	}
    $col = 4 ;
	//col 4  cliente
	if($_POST["columns"][4]["search"]["value"] != "" && $_POST["columns"][4]["search"]["value"] != "null"){
		$searchQuery.=	" AND CONCAT(swt.client_id, ' - ', swt.client_name) in ('".str_replace(",", "','", $_POST["columns"][4]["search"]["value"])."')";
	}
	//col 5  estado
    $col = 5;
	if($_POST["columns"][$col]["search"]["value"] != "" && $_POST["columns"][$col]["search"]["value"] != "null"){
		$searchQuery.=	" AND  CONCAT(
            IF( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
            IF( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
            IF( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
        ) in ('".str_replace(",", "','", $_POST["columns"][$col]["search"]["value"])."')";
	}
    $col = 6 ;
	if($_POST["columns"][$col]["search"]["value"] != "" && $_POST["columns"][$col]["search"]["value"] != "null"){
		$searchQuery.=	" AND l.id in ('".str_replace(",", "','", $_POST["columns"][$col]["search"]["value"])."')";
	}
    $col = 7 ;
	if($_POST["columns"][$col]["search"]["value"] != "" && $_POST["columns"][$col]["search"]["value"] != "null"){
		$searchQuery.=	" AND swt.txn_id in ('".str_replace(",", "','", $_POST["columns"][$col]["search"]["value"])."')";
	}
    $col = 9 ;/*estado*/ 
	if($_POST["columns"][$col]["search"]["value"] != "" && $_POST["columns"][$col]["search"]["value"] != "null"){
		$searchQuery.=	" AND ee.id in ('".str_replace(",", "','", $_POST["columns"][$col]["search"]["value"])."')";
	}

    $query_all = "SELECT 
            e.created_at AS 'Registro',
            e.id,
            z.nombre AS 'Zona',/*1-deposito / 2-retiro / 3-extorno*/
            CASE 
                WHEN swt.tipo_id = 1 THEN 'Depósito'
                WHEN swt.tipo_id  = 2 THEN 'Retiro'
                WHEN swt.tipo_id  = 3 THEN 'extorno'
                ELSE 'Atendido' 
            END AS 'Tipo',
            swt.monto AS 'Monto',
            CONCAT(swt.client_id, ' - ', swt.client_name) AS 'Cliente',
            CONCAT(
                IF( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
                IF( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
                IF( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
            ) AS 'Cajero',
            l.nombre AS 'Local',
            swt.txn_id as 'Transacción Id',
            e.motivo as 'Motivo',
            ee.nombre as 'Estado',
            ee.id AS estado_extorno_id,
            CONCAT(
                IF( LENGTH( pl_sop.apellido_paterno ) > 0, CONCAT( UPPER( pl_sop.apellido_paterno ), ' ' ), '' ),
                IF( LENGTH( pl_sop.apellido_materno ) > 0, CONCAT( UPPER( pl_sop.apellido_materno ), ' ' ), '' ),
                IF( LENGTH( pl_sop.nombre ) > 0, UPPER( pl_sop.nombre ), '' ) 
            ) AS 'Usuario Soporte',
            e.updated_at AS 'Fecha Proceso',
            e.monto_aplicado AS 'Monto Aplicado'
            FROM wwwapuestatotal_gestion.tbl_extorno e
            LEFT JOIN tbl_saldo_web_transaccion swt ON swt.id = e.id_saldo_web
            LEFT JOIN tbl_extorno_estado ee ON ee.id = e.estado_extorno_id
            LEFT JOIN tbl_locales l ON l.cc_id = swt.cc_id
            LEFT JOIN tbl_zonas z ON z.id = l.zona_id
            LEFT JOIN tbl_usuarios u ON u.id = swt.user_id
            LEFT JOIN tbl_personal_apt pl ON pl.id = u.personal_id
            LEFT JOIN tbl_usuarios u_sop ON u_sop.id = e.update_user_id
            LEFT JOIN tbl_personal_apt pl_sop ON pl_sop.id = u_sop.personal_id
			WHERE 1 = 1 
            $searchQuery
            $query_fecha
            "
	;
	if($query_excel){
		$query_excel = $query_all  . " ORDER BY ".$columnName." ".$columnSortOrder;
		$response = array(
			  "query_excel" => $query_excel
		);
		return $response;
	}
    //echo "SELECT count(*) AS allcount FROM ( $query_all ) as  A ";
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

if(isset($_POST["sec_extorno_cargar_locales"])){
	$zona_id = $_POST["sec_extorno_zona_select"];
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
	while ($li = $list_query->fetch_assoc()) {
		$list[]=$li;
	}
	$return["locales"] = $list;
}

	if(isset($_POST["sec_extorno_solicitud_detalle"])){
	$id = $_POST["id"];
    $command = "SELECT
            e.monto_aplicado as 'Monto Aplicado',
            swt.monto AS 'Monto',
            CONCAT(
                IF( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
                IF( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
                IF( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
            ) AS 'Cajero',
            l.nombre AS 'Local',
            /*z.nombre AS 'Zona',*/
            e.motivo as 'Motivo',
            CASE 
                WHEN swt.tipo_id = 1 THEN 'Depósito'
                WHEN swt.tipo_id  = 2 THEN 'Retiro'
                WHEN swt.tipo_id  = 3 THEN 'extorno'
                ELSE 'Atendido' 
            END AS 'Tipo',/*1-deposito / 2-retiro / 3-extorno*/
            CONCAT(swt.client_id, ' - ', swt.client_name) AS 'Cliente',
            swt.txn_id as 'Transacción Id',
            e.created_at AS 'Registro',
            ee.nombre as 'Estado',
            e.id as 'Id Solicitud Extorno',
            swt.id as 'saldo_web_transaccion_id',
			e.id
            FROM wwwapuestatotal_gestion.tbl_extorno e
            LEFT JOIN tbl_saldo_web_transaccion swt ON swt.id = e.id_saldo_web
            LEFT JOIN tbl_extorno_estado ee ON ee.id = e.estado_extorno_id
            LEFT JOIN tbl_locales l ON l.cc_id = swt.cc_id
            LEFT JOIN tbl_zonas z ON z.id = l.zona_id
            LEFT JOIN tbl_usuarios u ON u.id = swt.user_id
            LEFT JOIN tbl_personal_apt pl ON pl.id = u.personal_id
            LEFT JOIN tbl_usuarios u_sop ON u_sop.id = e.usuario_id
            LEFT JOIN tbl_personal_apt pl_sop ON pl_sop.id = u_sop.personal_id
            WHERE e.id = $id
            "
        ;
    $registro = $mysqli->query($command)->fetch_assoc();
	$return["registro"] = $registro;
}

if(isset($_POST["action"]) && $_POST["action"] == "sec_extorno_list_excel"){
	$query_excel = datatables_servidor(true)["query_excel"];
	$registros = $mysqli->query($query_excel);
	$data = array();
	while ($row = $registros->fetch_assoc()) {
		unset($row["id"]);
		unset($row["estado_extorno_id"]);
		$data[] = $row;
	}
	$file_version = date('YmdHis');
	$filename ="extorno_reporte" ."_at_" . $file_version . ".xls";
	$filesPath = '/var/www/html/export/extorno_reporte/';
    
    $cabeceras =[
        "Registro" ,
        "Zona" ,
        "Tipo" , 
        "Monto" ,
        "Cliente" ,
        "Cajero" ,
        "Local" ,
        "Transacción Id" ,
        "Motivo" , 
        "Estado" , 
        "Usuario Soporte" , 
        "Fecha Proceso" , 
        "Monto Aplicado"
    ];
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
		"path" => '/export/extorno_reporte/'.$filename
	);
	echo json_encode($response);
	return;
}
if(isset($_POST["action"]) && $_POST["action"]=="sec_extorno_list"){

	$response = datatables_servidor();
	echo json_encode($response);
	return;
}

if(isset($_POST["sec_extorno_solicitud_rechazar"]))
{
	$user_id = $login["id"];
	extract($_POST);
	$command = "UPDATE tbl_extorno
		SET 			
			estado_extorno_id = 3 /*rechazado*/
			,updated_at = now()
			,update_user_id = $user_id
		WHERE id = " . $id;
	$mysqli->query($command);
	$return["update_command"] = $command;
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}
	$return["mensaje"] = "Extorno Rechazado";
}
if(isset($_POST["set_extorno_update"])){
	$user_id = $login["id"];
	extract($_POST);

	$command = "SELECT
				tipo_id,
				client_id,
				client_name,
				monto,
				txn_id,
				status,
				cc_id,
				turno_id,
				user_id,
				created_at
			FROM  tbl_saldo_web_transaccion
			WHERE id = $saldo_web_transaccion_id
        "
    ;
    $registro_swt = $mysqli->query($command)->fetch_assoc();

	if(!isset($monto_aplicado) || $monto_aplicado == ""){
		$return["error"] = "monto_aplicado";
		$return["error_msg"] = "Debe Ingresar Monto Aplicado";
		$return["error_focus"] = "monto_aplicado";
		die(json_encode($return));
	}

    if( is_numeric( $monto_aplicado ) == FALSE) {
		$return["error"] = "monto_aplicado";
		$return["error_msg"] = "Monto Aplicado debe ser Numérico";
		$return["error_focus"] = "monto_aplicado";
		die(json_encode($return));
	}
    if( $monto_aplicado <= 0 ) {
		$return["error"] = "monto_aplicado";
		$return["error_msg"] = "Monto Aplicado debe ser mayor a 0";
		$return["error_focus"] = "monto_aplicado";
		die(json_encode($return));
	}
    if( $monto_aplicado > $registro_swt["monto"] ) {
		$return["error"] = "monto_aplicado";
		$return["error_msg"] = "Monto Aplicado no puede ser mayor a Monto";
		$return["error_focus"] = "monto_aplicado";
		die(json_encode($return));
	}

    $command = "UPDATE tbl_extorno
				SET 
                    monto_aplicado = '$monto_aplicado'
					,estado_extorno_id = 2 /*aprobado*/
					,updated_at = '" . date("Y-m-d H:i:s") . "'
					,update_user_id = $user_id
				WHERE id = " . $id;
	$mysqli->query($command);
	$return["update_command"] = $command;
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}

    $turno_temp2 = $registro_swt["turno_id"] ? "'".$registro_swt["turno_id"]."', " : "null,";
    $i_command = "INSERT INTO tbl_saldo_web_transaccion (
                        tipo_id,
                        client_id,
                        client_name,
                        monto,
						txn_id,
                        status,
                        cc_id,
                        turno_id,
                        user_id,
                        created_at
                    ) VALUES (
                        '3',
                        '" . $registro_swt["client_id"] . "',
                        '" . $registro_swt["client_name"] . "',
                        '" . $monto_aplicado . "',
                        '" . $registro_swt["txn_id"] . "',
                        '1',
                        '" . $registro_swt["cc_id"] . "',
                        $turno_temp2
                        '" . $registro_swt["user_id"] . "',
                        '" . date("Y-m-d H:i:s") . "'
		)";
    $return["insert_command"] = $i_command;
    $mysqli->query($i_command);
    if($mysqli->error){
        print_r($mysqli->error);
        echo "\n";
        echo $i_command;
        exit();
    }
	$return["mensaje"] = "Solicitud Extorno  ".$id." Actualizada";
	$return["curr_login"] = $login;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>