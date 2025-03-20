<?php
include("../sys/db_connect.php");
include("../sys/sys_login.php");

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'caja' AND sub_sec_id = 'auditoria2' LIMIT 1");
while($r = $result->fetch_assoc()) $menu_id = $r["id"];
if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("export", $usuario_permisos[$menu_id])){
	echo "No tienes permisos para acceder a este recurso";
	die;
}

date_default_timezone_set("America/Lima");

$table=[];
$table[] = [
    // "#ID",
    "Local",
    "Fecha",

    "Sistema Resultado",
    "Sistema Cajero",
    "Sistema Diferencia",

    "Pagos Manuales Sistema",
    "Pagos Manuales Cajero",
    "Pagos Manuales Diferencia",

    "Apuestas Deportivas Sistema",
    "Apuestas Deportivas Cajero",
    "Apuestas Deportivas Diferencia",

    "Billeteros Sistema",
    "Billeteros Cajero",
    "Billeteros Diferencia",

    "Golden Race Sistema",
    "Golden Race Cajero",
    "Golden Race Diferencia",

    "Carrera de Caballos Sistema",
    "Carrera de Caballos Cajero",
    "Carrera de Caballos Diferencia",

    "DS Virtual Gaming Sistema",
    "DS Virtual Gaming Cajero",
    "DS Virtual Gaming Diferencia",

    "Bingo Sistema",
    "Bingo Cajero",
    "Bingo Diferencia",

    "Web Sistema",
    "Web Cajero",
    "Web Diferencia",

    "Web Televentas Sistema",
    "Web Televentas Cajero",
    "Web Televentas Diferencia",

    "Cash In Out Sistema",
    "Cash In Out Cajero",
    "Cash In Out Diferencia",

    "Apuestas Deportiva Altenar Sistema",
    "Apuestas Deportiva Altenar Cajero",
    "Apuestas Deportiva Altenar Diferencia",

    "Kasnet Sistema",
    "Kasnet Cajero",
    "Kasnet Diferencia",

    "Disashop Sistema",
    "Disashop Cajero",
    "Disashop Diferencia",

    "ATSnacks Sistema",
    "ATSnacks Cajero",
    "ATSnacks Diferencia",

    "Devolucion Sistema",
    "Devolucion Cajero",
    "Devolucion Diferencia",

    "Devolucion Carrera de Caballos Sistema",
    "Devolucion Carrera de Caballos Cajero",
    "Devolucion Carrera de Caballos Diferencia",

    "Torito Sistema",
    "Torito Cajero",
    "Torito Diferencia",

    "Nsoft Sistema",
    "Nsoft Cajero",
    "Nsoft Diferencia",

    "Kiron Sistema",
    "Kiron Cajero",
    "Kiron Diferencia",

    "Sistema Resultado",
    "Resultado Voucher",
    "Devolucion Sistema",
    "Devolucion Carrera de Caballos",
    "Pagos Manuales Sistema",
    "Diferencia"
];

$datos = json_decode($_POST['datos'], true);
$data_export = json_decode($_POST['data_export'], true);

foreach ($datos as $key => $value) {
    $tr=[
        // $value["local_id"],
        $value["local_nombre"],
        $value["fecha_operacion"],

        $value['sistema_resultado'],
        $value['cajero_resultado'],
        $value['diferencia_resultado'],

        $value['sistema_pagos_manuales'],
        $value['cajero_pagos_manuales'],
        $value['diferencia_pagos_manuales'],

        $value['sistema_apuestas_deportivas'],
        $value['cajero_apuestas_deportivas'],
        $value['diferencia_apuestas_deportivas'],

        $value['sistema_billeteros'],
        $value['cajero_billeteros'],
        $value['diferencia_billeteros'],

        $value['sistema_goldenrace'],
        $value['cajero_goldenrace'],
        $value['diferencia_goldenrace'],

        $value['sistema_carreradecaballos'],
        $value['cajero_carreradecaballos'],
        $value['diferencia_carreradecaballos'],

        $value['sistema_dsvirtualgaming'],
        $value['cajero_dsvirtualgaming'],
        $value['diferencia_dsvirtualgaming'],

        $value['sistema_bingo'],
        $value['cajero_bingo'],
        $value['diferencia_bingo'],

        $value['sistema_web'],
        $value['cajero_web'],
        $value['diferencia_web'],

        $value['sistema_web_televentas'],
        $value['cajero_web_televentas'],
        $value['diferencia_web_televentas'],

        $value['sistema_cash'],
        $value['cajero_cash'],
        $value['diferencia_cash'],

        $value['sistema_apuestas_deportivas_altenar'],
        $value['cajero_apuestas_deportivas_altenar'],
        $value['diferencia_apuestas_deportivas_altenar'],

        $value['sistema_kasnet'],
        $value['cajero_kasnet'],
        $value['diferencia_kasnet'],

        $value['sistema_disashop'],
        $value['cajero_disashop'],
        $value['diferencia_disashop'],

        $value['sistema_atsnacks'],
        $value['cajero_atsnacks'],
        $value['diferencia_atsnacks'],

        $value['sistema_devoluciones'],
        $value['cajero_devoluciones'],
        $value['diferencia_devoluciones'],

        $value['sistema_devoluciones_carrera_caballos'],
        $value['cajero_devoluciones_carrera_caballos'],
        $value['diferencia_devoluciones_carrera_caballos'],

        $value['sistema_torito'],
        $value['cajero_torito'],
        $value['diferencia_torito'],

        $value['sistema_nsoft'],
        $value['cajero_nsoft'],
        $value['diferencia_nsoft'],

        $value['sistema_kiron'],
        $value['cajero_kiron'],
        $value['diferencia_kiron'],

        $value['resultado_sistema'],
        $value['resultado_voucher'],
        $value['sistema_devoluciones'],
        $value['sistema_devoluciones_carrera_caballos'],
        $value['sistema_pagos_manuales'],
        $value['diferencia']
    ];

    $table[]=$tr;
}

if (!empty($datos)) {				
    $fecha_inicio = $data_export["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($data_export["fecha_inicio"]));

	$fecha_fin = date("Y-m-d",strtotime($data_export["fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($data_export["fecha_fin"]));

    require_once('../phpexcel/classes/PHPExcel.php');

    $doc = new PHPExcel();
    $doc->setActiveSheetIndex(0);
    $doc->getActiveSheet()->fromArray($table, null, 'A1', true);
        
    $filename = "reporte_auditoria".date("d-m-Y",strtotime($data_export["fecha_inicio"]))."_al_".date("d-m-Y",strtotime($data_export["fecha_fin"]))."_".date("Ymdhis").".xls";
    $excel_path = '/var/www/html/export/files_export/caja_auditoriav2/'.$filename;

    $objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
    $objWriter->save($excel_path);

    echo json_encode(array(
        "path" => '/export/files_export/caja_auditoriav2/'.$filename,
        "tipo" => "excel",
        "ext" => "xls",
        "size" => filesize($excel_path),
        "fecha_registro" => date("d-m-Y h:i:s"),
    ));

    exit; 

}else{
    echo json_encode(array(
        "error" => 'No hay resultados para mostrar'
    ));
        
}

?>