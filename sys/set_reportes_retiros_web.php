<?php

//include("global_config.php");
global $mysqli;
include("db_connect.php");
include("sys_login.php");
//include '/var/www/html/phpexcel/classes/PHPExcel.php';
include '../phpexcel/classes/PHPExcel.php';
require_once '/var/www/html/sys/helpers.php';

function getReporteRetirosWeb($query_excel=false){
	global $mysqli, $login;
    $draw = $_POST['draw'];
    $fechaInicio = $_POST['fecha_inicio'] ?? null;
    $fechaFin = $_POST['fecha_fin'] ?? null;
    $zonaId = $_POST['zona_id'];
    $localId = $_POST['local_id'];
    $searchValue = $_POST['search']['value']; // Search value
    $searchValue = $mysqli->real_escape_string($searchValue);
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $tipo_ingreso_doc = $_POST['tipo_ingreso_doc'] ?? '0';
    $estado = $_POST['estado'] ?? '100';
    ## Search
    $searchQuery = " ";
    if($searchValue != ''){
        $searchQuery = " and (
	        l.nombre LIKE '%".$searchValue."%' or 
	        lc.nombre LIKE '%".$searchValue."%' or 
	        u.usuario LIKE '%".$searchValue."%' or 
	        t.client_num_doc LIKE '%".$searchValue."%' or 
	        z.nombre LIKE '%".$searchValue."%'
			) ";
    }
	$SELECT="SELECT
                t.id cod_transaccion,
                t.created_at registro,
                t.client_id,
                t.client_num_doc,
                IFNULL(t.txn_id, '') txn_id,
                IFNULL(t.monto, 0) monto,
                ( CASE t.status WHEN 0 THEN 'Fallido' WHEN 1 THEN 'Completado' WHEN 2 THEN 'Anulado' ELSE '' END ) status,
                u.usuario,
                ( CASE t.scan_doc WHEN 1 THEN 'ESCANEADO' WHEN 2 THEN 'DIGITADO' ELSE 'DIGITADO' END ) scan_doc,
                l.cc_id,
                z.nombre zona_nombre,
                l.nombre as local_nombre,
                t.observacion_scan_doc
            FROM
                tbl_saldo_web_transaccion t
                LEFT JOIN tbl_usuarios u ON u.id = t.user_id
                LEFT JOIN tbl_local_cajas lc ON lc.id = t.local_caja_id
                LEFT JOIN tbl_locales l ON l.id = t.local_id
                LEFT JOIN tbl_zonas z ON z.id = l.zona_id
            WHERE
                t.tipo_id = 2 AND
                t.status IN (0,1,2) AND
                t.scan_doc IS NOT NULL 
            ";
    $ORDERBY = "  ORDER BY
                t.id DESC ";
    if ($zonaId && $zonaId>0){
        $searchQuery =$searchQuery.' AND l.zona_id='.$zonaId;
    }
    if ($localId && $localId>0){
        $searchQuery =$searchQuery.' AND l.id='.$localId;
    }
    if ($fechaInicio && $fechaFin) {
        $searchQuery =$searchQuery.' AND DATE(t.created_at) BETWEEN '."'".$fechaInicio."'".' AND '."'". $fechaFin."'";
    }

    if ($tipo_ingreso_doc=="2"){ //documentos que fueron digiitados
        $searchQuery =$searchQuery.' AND (t.scan_doc=2 OR t.scan_doc is null) ';
    }
    if ($tipo_ingreso_doc=="1"){ //documentos que fueron escaneados
        $searchQuery =$searchQuery.' AND t.scan_doc=1 ';
    }
    if ($estado=="0"){ //retiros fallidos
        $searchQuery =$searchQuery.' AND t.status=0 ';
    }
    if ($estado=="1"){ //retiros completados
        $searchQuery =$searchQuery.' AND t.status=1 ';
    }
    if ($estado=="2"){ //retiros anulados
        $searchQuery =$searchQuery.' AND t.status=2 ';
    }

    if($query_excel){
        $query_excel = $SELECT." ".$searchQuery. " ".$ORDERBY;
        $response = array(
            "query_excel" => $query_excel
        );
        return $response;
    }

    $limit = " LIMIT ".$row.",".$rowperpage.';';
    if($rowperpage==-1){
        $limit="";
    }
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM tbl_saldo_web_transaccion WHERE tipo_id=2 AND status IN (0,1,2) AND scan_doc IS NOT NULL");
	$records = $sel->fetch_assoc();
	$totalRecords = $records['allcount'];

	$query_f =   $SELECT." ". $searchQuery;
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$query_f.") AS subquery");
	$records = $sel->fetch_assoc();
	$totalRecordwithFilter = $records['allcount'];


	$query_f = $SELECT." ".$searchQuery. " ".$ORDERBY." ".$limit;
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

if(isset($_POST["action"]) && $_POST["action"]=="reporte_retiros_web"){

	$response = getReporteRetirosWeb();
	echo json_encode($response);
	return;
}

if(isset($_POST["action"]) && $_POST["action"]=="sec_reporte_retiros_web_excel"){
    $query_excel = getReporteRetirosWeb(true)["query_excel"];
    $registros = $mysqli->query($query_excel);
    $data = array();
    while ($row = $registros->fetch_assoc()) {
        //unset($row["id"]);
        $data[] = $row;
    }
    $file_version = date('YmdHis');
    $filename ="reporte_retiros_web" ."_at_" . $file_version . ".xls";
    $filesPath = '/var/www/html/export/reportes_retiros_web/';

    $cabeceras=["ID","REGISTRO","ID CLIENTE","DNI","TRANSACCIÓN","MONTO","ESTADO","USUARIO","DNI INGRESO","CECO","ZONA","LOCAL","OBSERVACIÓN"];
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
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'CCE5FF')
        )
    );
    $estiloData = array(
        'font' => array(
            'name'      => 'Calibri',
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
    $estiloBorder = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );

    $doc->getActiveSheet()->getStyle('A1:M1')->applyFromArray($estiloNombresFilas);
    $doc->getActiveSheet()->getColumnDimension('A')->setWidth(15);
    $doc->getActiveSheet()->getColumnDimension('B')->setWidth(22);
    $doc->getActiveSheet()->getColumnDimension('C')->setWidth(22);
    $doc->getActiveSheet()->getColumnDimension('D')->setWidth(18);
    $doc->getActiveSheet()->getColumnDimension('E')->setWidth(18);
    $doc->getActiveSheet()->getColumnDimension('F')->setWidth(13);
    $doc->getActiveSheet()->getColumnDimension('G')->setWidth(25);
    $doc->getActiveSheet()->getColumnDimension('H')->setWidth(18);
    $doc->getActiveSheet()->getColumnDimension('I')->setWidth(12);
    $doc->getActiveSheet()->getColumnDimension('J')->setWidth(12);
    $doc->getActiveSheet()->getColumnDimension('K')->setWidth(18);
    $doc->getActiveSheet()->getColumnDimension('L')->setWidth(24);;
    $doc->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);

    $totalRows = count($data ?? []);
    $apliCells = 'A1:M'.$totalRows;
    $apliCellsData = 'A2:M'.$totalRows;
    $doc->getActiveSheet()->getStyle($apliCells)->applyFromArray($estiloBorder);
    $doc->getActiveSheet()->getStyle($apliCellsData)->applyFromArray($estiloData);

    $doc->getActiveSheet()->fromArray($data, null, 'A1', true);
    $attach = $filesPath . $filename;
    $objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
    $objWriter->save($attach);

    $response = array(
        "http_code" => 200,
        "path" => '/export/reportes_retiros_web/'.$filename
    );
    echo json_encode($response);
    return;
    //header('Location: /export/solicitud_mantenimiento/'.$filename);
}

?>