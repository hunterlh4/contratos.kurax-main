<?php

include("../sys/db_connect.php");
include("../sys/sys_login.php");
date_default_timezone_set("America/Lima");
$post = array("sec_caja_get_validados"=>array(
	"local_id" => $_POST["local_id"],
	"fecha_inicio" => $_POST["fecha_inicio"],
	"fecha_fin" => $_POST["fecha_fin"],
	"caja_validados" => $_POST["caja_validados"]
));

if(isset($post["sec_caja_get_validados"])){
	$get_data = $post["sec_caja_get_validados"];

	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));

	$where_id = $get_data["local_id"] == "all" ? "WHERE l.id != 1": "WHERE l.id = '".$get_data["local_id"]."'";
	$local_query = $mysqli->query("SELECT 
		l.id, 
		l.nombre,
		GROUP_CONCAT(DISTINCT CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL('',p.apellido_paterno))) as analistas 
		FROM tbl_locales l
		LEFT JOIN tbl_usuarios_locales ul ON(ul.local_id = l.id AND ul.estado = 1)
		LEFT JOIN tbl_usuarios u ON(u.id = ul.usuario_id AND u.estado = 1)
		LEFT JOIN tbl_personal_apt p ON(u.personal_id = p.id AND p.cargo_id=17 AND p.area_id = 22)
		".$where_id."
		GROUP BY ul.local_id
		ORDER BY l.nombre ASC ");
	$locals = array();
	while($loc = $local_query->fetch_assoc()){
		$locals[] = $loc;

	}
	$table = array();
	$cajas = array();

	$table[] = array(
		"local_nombre" => "Local",
		"ano" => "Año",
		"mes" => "Mes",
		"dia" => "Dia",
		"turno_id" => "Turno",
		"analistas" => "Analistas",
		"estado" => "Estado",
		"validar" => "Validar"
	);

	$where_caja = $get_data["caja_validados"] == "all" ? "" : "AND COALESCE(c.validar, 0) = ".$get_data["caja_validados"];
	foreach($locals as $local){
		$caja_command = "SELECT 
		c.fecha_operacion,
		c.turno_id,
		c.estado,
		c.validar
		FROM tbl_caja c
		LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
		LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
		WHERE c.id != 1
		AND l.id = '".$local["id"]."'
		AND c.fecha_operacion >= '".$get_data["fecha_inicio"]."'
		AND c.fecha_operacion < '".$fecha_fin."' ".$where_caja."
		ORDER BY c.fecha_operacion ASC, c.turno_id ASC";
		$caja_query = $mysqli->query($caja_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$caja_data = array();
		while($c=$caja_query->fetch_assoc()){
			$caja_data[]=$c;
		}
		if(count($caja_data)){
			
			foreach ($caja_data as $data_id => $data) {
				// $tr_in
				$tr = array();
				$tr["local_nombre"] = $local["nombre"];
				$tr["ano"] = substr($data["fecha_operacion"], 0,4);
				$tr["mes"] = substr($data["fecha_operacion"], 5,2);
				$tr["dia"] = substr($data["fecha_operacion"], 8,2);
				$tr["turno_id"] = $data["turno_id"];
				$tr["analistas"] = $local["analistas"];
				$tr["estado"]=($data["estado"]==1 ? "Cerrado" : "Abierto");
				$tr["validar"]=$data["validar"] == 1 ? "Validado": "No Validado";

				$table[]=$tr;
			}
		}
	}
	if (count($table) > 1) {				
		require_once('../phpexcel/classes/PHPExcel.php');

		$doc = new PHPExcel();
		$doc->setActiveSheetIndex(0);
		$doc->getActiveSheet()->fromArray($table);

		$filename = "reporte_validados".date("d-m-Y",strtotime($get_data["fecha_inicio"]))."_al_".date("d-m-Y",strtotime($get_data["fecha_fin"]))."_".date("Ymdhis").".xls";
		$excel_path = '/var/www/html/export/files_exported/caja_validados/'.$filename;

		$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
		$objWriter->save($excel_path);

		echo json_encode(array(
			"path" => '/export/files_exported/caja_validados/'.$filename,
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
}	                  

?>