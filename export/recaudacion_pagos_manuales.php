<?php 

include("../sys/db_connect.php");
include("../sys/sys_login.php");

$data = $_POST;

$where = "WHERE pm.id <> 0 AND pm.estado = 1";

// if($login["usuario_locales"]) $where.= " AND l.id IN (".implode(",", $login["usuario_locales"]).")";

if($data['start_date'] != ""){
	$where .= " AND pm.fecha_pago >= '".$data["start_date"]."'";
}
if($data['end_date'] != ""){
	$where .= " AND pm.fecha_pago < '".date('Y-m-d', strtotime("+1 Day", strtotime($data["end_date"])))."'";
}

$table = array();

$pm_command = "SELECT
pm.id,
fecha_proceso,
pm.tipo_id,
(SELECT t.nombre FROM tbl_pago_manual_tipos t WHERE t.id = pm.tipo_id) AS tipo,
pm.motivo_id,
(SELECT m.nombre FROM tbl_pago_manual_motivos m WHERE m.id = pm.motivo_id) AS motivo,
SUBSTRING(pm.referencia,1,10) AS referencia,
pm.descripcion,
pm.estado,
DATE_FORMAT(pm.fecha_pago,'%Y-%m-%d')  AS fecha_pago,
pm.monto,
(SELECT cdv.codigo FROM tbl_canales_venta cdv WHERE cdv.id = pm.canal_de_venta_id) AS cdv,
(SELECT l.nombre FROM tbl_locales l WHERE l.id = pm.local_id) AS local,
(select IF(trs.id=16,'IGH','FG')  from tbl_locales tl left join tbl_locales_redes trs on trs.id  = tl.red_id  where tl.id = pm.local_id) AS empresa,
(SELECT u.usuario FROM tbl_usuarios u WHERE u.id = pm.usuario_id) AS usuario,
(SELECT a.nombre FROM tbl_personal_apt a WHERE a.id = pm.autorizacion_id) AS autoriza,
(SELECT zon.nombre FROM tbl_zonas zon INNER JOIN tbl_locales loc ON loc.zona_id = zon.id WHERE loc.id = pm.local_id LIMIT 1) AS zona_comercial
FROM tbl_pago_manual pm
$where
ORDER BY pm.fecha_proceso DESC";
$pm_query = $mysqli->query($pm_command);

$table[] = array(
	"id" => "id",
	"fecha_proceso" => "fecha_proceso",
	"tipo_id" => "tipo_id",
	"tipo" => "tipo",
	"motivo_id" => "motivo_id",
	"motivo" => "motivo",
	"referencia" => "referencia",
	"descripcion" => "descripcion",
	"estado" => "estado",
	"fecha_pago" => "fecha_pago",
	"monto" => "monto",
	"cdv" => "cdv",
	"local" => "local",
	"empresa" => "Ref. Empresa",
	"usuario" => "usuario",
	"autoriza" => "autoriza",
    "zona_comercial" => "zona_comercial"
);

while($tr = $pm_query->fetch_assoc()){
	$table[] = $tr;
}

require_once('../phpexcel/classes/PHPExcel.php');

$doc = new PHPExcel();
$doc->setActiveSheetIndex(0);
$doc->getActiveSheet()->fromArray($table);

$filename = "recaudacion_pagos_manuales_".date("d-m-Y_H-i-s").".xls";
$excel_path = '/var/www/html/export/files_exported/'.$filename;
$excel_path_download = '/export/files_exported/'.$filename;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename);
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');

$objWriter->save($excel_path);

echo json_encode(array(
	"path" => $excel_path_download,
	"tipo" => "excel",
	"ext" => "xls",
	"size" => filesize($excel_path)
));

exit;

?>