<?php

include("../sys/db_connect.php");
include("../sys/sys_login.php");
require("/var/www/html/cron/cron_pdo_connect.php");

if(isset($_POST["opt"])){
	$opt=$_POST["opt"];  
	if($opt=="sec_locales_get_solicitudes_export"){
		$get_data = $_POST["data"];		

		$local_solicitud_query = $mysqli->query("
		SELECT		
		sol.id,sol.motivo,sol.estado,sol.tipo_solicitud,sol.subtipo_solicitud,sol.local,sol.bet_id,sol.fecha_creacion,
		p.nombre, p.apellido_paterno,
		lc.nombre AS nombre_local,
		u.usuario,
		(SELECT a.nombre FROM tbl_areas a WHERE a.id = p.area_id) AS area,
		(SELECT c.nombre FROM tbl_cargos c WHERE c.id = p.cargo_id) AS cargo,
		(SELECT ts.descripcion FROM tbl_tipo_solicitud ts WHERE ts.id = sol.tipo_solicitud) AS tipo_solicitud_desc,
		(SELECT ss.descripcion FROM tbl_subtipo_solicitud ss WHERE ss.id = sol.subtipo_solicitud) AS subtipo_solicitud_desc
		FROM tbl_solicitud_prestamo  sol
		LEFT JOIN tbl_usuarios u ON (u.id = sol.usuario)
		LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id) 
		LEFT JOIN tbl_locales lc ON (lc.id = sol.local) 
		WHERE sol.local ='".$get_data."' 
		ORDER BY sol.fecha_creacion DESC
		");
		$solicitudes = array();
		while($sol = $local_solicitud_query->fetch_assoc()){
			$solicitudes[] = $sol;	
		}
		$table = array();
		$table[] = array("","","","","","","","","");
		$table[] = array("","","","","Reporte Solicitudes","","","","");
		$table[] = array("","","","","","","","","");
		$table[] = array(
			"fecha_creacion" => "Fecha Creacion",
			"local" => "Local",
			"tipo" => "Tipo",
			"subtipo" => "SubTipo",
			"usuario" => "Usuario",
			"area" => "Area",
			"cargo" => "Cargo",
			"estado" => "Estado",
			"motivo" => "Motivo",
		);
			
		foreach($solicitudes as $solicitud){
			$estado_string="";
			if($solicitud['estado']==0){$estado_string="Pendiente";}
			if($solicitud['estado']==1){$estado_string="Aprobado";}
			if($solicitud['estado']==2){$estado_string="Abonado";}
			if($solicitud['estado']==3){$estado_string="Cancelado";}
			if($solicitud['estado']==4){$estado_string="Expirado";}
			if($solicitud['estado']==5){$estado_string="Recibido";}
			if($solicitud['estado']==6){$estado_string="Abonado - Eliminacion - Turno";}
			$table[]=array(
				$solicitud['fecha_creacion'],
				$solicitud['nombre_local'],
				$solicitud['tipo_solicitud_desc'],
				$solicitud['subtipo_solicitud_desc'],
				$solicitud['usuario'],
				$solicitud['area'],
				$solicitud['cargo'],
				$estado_string,
				$solicitud['motivo']
			);
		}
		if (count($table) > 1) {				
			require_once('../phpexcel/classes/PHPExcel.php');
	
			$doc = new PHPExcel();
			$doc->setActiveSheetIndex(0);
			$doc->getActiveSheet()->fromArray($table);
			 
			$filename = "reporte_validados".$get_data.".xls";
			$excel_path = '/var/www/html/export/files_exported/local_solicitudes/'.$filename;
	
			$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
			$objWriter->save($excel_path);
	
			echo json_encode(array(
				"path" => '/export/files_exported/local_solicitudes/'.$filename,
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
	if($opt=="sec_locales_get_solicitud_detalle_export"){
		$get_data = $_POST["data"];		
		$monto_ticket = $_POST["monto_ticket"];		

		$solicitud_get_data_query = "
		SELECT
		sol.id,sol.motivo,sol.monto,sol.estado,sol.tipo_solicitud,sol.subtipo_solicitud,sol.local,sol.bet_id,sol.fecha_creacion,
		p.nombre, p.apellido_paterno,p.correo,
		lc.nombre AS nombre_local,
		u.usuario,
		ts.descripcion as descripcion_tipo_solicitud,
		sts.descripcion as descripcion_subtipo_solicitud,
		(SELECT a.nombre FROM tbl_areas a WHERE a.id = p.area_id) AS area,
		(SELECT c.nombre FROM tbl_cargos c WHERE c.id = p.cargo_id) AS cargo,
		(SELECT ts.descripcion FROM tbl_tipo_solicitud ts WHERE ts.id = sol.tipo_solicitud) AS tipo_solicitud_desc,
		(SELECT ss.descripcion FROM tbl_subtipo_solicitud ss WHERE ss.id = sol.subtipo_solicitud) AS subtipo_solicitud_desc
		FROM tbl_solicitud_prestamo  sol
		LEFT JOIN tbl_usuarios u ON (u.id = sol.usuario)
		LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id) 
		LEFT JOIN tbl_locales lc ON (lc.id = sol.local) 
		LEFT JOIN tbl_tipo_solicitud ts ON (ts.id = sol.tipo_solicitud) 
		LEFT JOIN tbl_subtipo_solicitud sts ON (sts.id = sol.subtipo_solicitud) 
		WHERE 
		sol.id='".$get_data."' LIMIT 1";					

		$result = $mysqli->query($solicitud_get_data_query);
		$sol = array();	
		while($item=$result->fetch_assoc()){
			$sol[]=$item;
		}

		$estado_string="";
		if($sol[0]['estado']==0){$estado_string="Pendiente";}
		if($sol[0]['estado']==1){$estado_string="Aprobado";}
		if($sol[0]['estado']==2){$estado_string="Abonado";}
		if($sol[0]['estado']==3){$estado_string="Cancelado";}
		if($sol[0]['estado']==4){$estado_string="Expirado";}
		if($sol[0]['estado']==5){$estado_string="Recibido";}
		if($sol[0]['estado']==6){$estado_string="Abonado - Eliminacion - Turno";}

		$table = array();
		$table[] = array("","");
		$table[] = array("Detalle Solicitudes","");
		$table[] = array("","");	
		$table[] = array("Tipo",$sol[0]['tipo_solicitud_desc']);
		$table[] = array("Fecha Creacion",$sol[0]['subtipo_solicitud_desc']);
		$table[] = array("Estado",$estado_string);
		$table[] = array("Usuario",$sol[0]['usuario']);
		$table[] = array("Area Cargo",$sol[0]['area']." / ".$sol[0]['cargo']);
		if($sol[0]['tipo_solicitud']==1){
			$table[] = array("Monto","S/ ".$sol[0]['monto']);
			$table[] = array("Motivo",$sol[0]['motivo']);
			if($sol[0]['subtipo_solicitud']==2){
				$table[] = array("Ticket",$sol[0]['bet_id']);
				$table[] = array("Monto Ticket",$monto_ticket);
				if($sol[0]['estado']==4){
					$transacciones_array = array();	
					$result = pdoStatement("
					select top 1 * from bet where id='".$sol[0]['bet_id']."'");
					foreach($result as $sel) {
						$transacciones_array[]=array(
							"ticket_id"=>$sel["Id"],
							"paid_day"=>$sel["PaidDate"],
							"CashDeskId"=>$sel["CashDeskId"],
							"ganado"=>$sel["WinningAmount"],
							"pagado"=>$sel["WinningAmount"]
						);
					}	
					$nombre_local = $mysqli->query("
					SELECT tbl_local_proveedor_id.*,tbl_locales.nombre as nombreLocal FROM tbl_local_proveedor_id 
					LEFT JOIN tbl_locales ON tbl_locales.id= tbl_local_proveedor_id.local_id
					WHERE tbl_local_proveedor_id.proveedor_id='".$transacciones_array[0]["CashDeskId"]."' LIMIT 1
					");
					$local = array();	
					while($loc=$nombre_local->fetch_assoc()){
						$local[]=$loc;
					}
					$server_date = date_create_from_format('Y-m-d H:i:s', $transacciones_array[0]['paid_day']->format('Y-m-d H:i:s'));
					$server_date->sub(new DateInterval("PT9H"));
					$real_date = $server_date->format('Y-m-d H:i:s');

					$table[] = array("","");
					$table[] = array("","");
					$table[] = array("","");
					$table[] = array("Solicitud Expirada","");
					$table[] = array("","");	
					$table[] = array("Ticket","Fecha Pago","Local Pago","Monto Pago");	
					$table[] = array($transacciones_array[0]["ticket_id"],$real_date,$local[0]['nombreLocal'],$transacciones_array[0]["pagado"]);						
					
				}
			}
		}
						
		if (count($table) > 1) {				
			require_once('../phpexcel/classes/PHPExcel.php');
	
			$doc = new PHPExcel();
			$doc->setActiveSheetIndex(0);
			$doc->getActiveSheet()->fromArray($table);
			 
			$filename = "reporte_validados".$get_data.".xls";
			$excel_path = '/var/www/html/export/files_exported/local_solicitudes/'.$filename;
	
			$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
			$objWriter->save($excel_path);
	
			echo json_encode(array(
				"path" => '/export/files_exported/local_solicitudes/'.$filename,
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

	if($opt=="sec_locales_reporte_get_solicitudes_export"){
		$get_data = $_POST["data"];		

		$whereId = ($get_data['local_id'] != 'all') ? "AND sol.local =".$get_data['local_id'] : "";
		$whereEstados = ($get_data['estados'] != 'all') ? " AND sol.estado in (".$get_data['estados'].")" : "";
		$query =$mysqli->query( "
		SELECT
		sol.id,sol.motivo,sol.estado,sol.tipo_solicitud,sol.subtipo_solicitud,sol.local,sol.bet_id,sol.fecha_creacion,
		p.nombre, p.apellido_paterno,
		lc.nombre AS nombre_local,
		u.usuario,
		(SELECT a.nombre FROM tbl_areas a WHERE a.id = p.area_id) AS area,
		(SELECT c.nombre FROM tbl_cargos c WHERE c.id = p.cargo_id) AS cargo,
		(SELECT ts.descripcion FROM tbl_tipo_solicitud ts WHERE ts.id = sol.tipo_solicitud) AS tipo_solicitud_desc,
		(SELECT ss.descripcion FROM tbl_subtipo_solicitud ss WHERE ss.id = sol.subtipo_solicitud) AS subtipo_solicitud_desc
		FROM tbl_solicitud_prestamo  sol
		LEFT JOIN tbl_usuarios u ON (u.id = sol.usuario)
		LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id) 
		LEFT JOIN tbl_locales lc ON (lc.id = sol.local) 
		WHERE 
		sol.fecha_creacion >= '".$get_data['fecha_inicio']." 00:00:00' AND 
		sol.fecha_creacion <= '".$get_data['fecha_fin']." 23:59:59'
		AND sol.id IS NOT NULL ".$whereId.$whereEstados." ORDER BY sol.fecha_creacion DESC");
		$solicitudes = array();
		while($sol = $query->fetch_assoc()){
			$solicitudes[] = $sol;	
		}
		$table = array();
		$table[] = array("","","","","","","","","");
		$table[] = array("","","","","Reporte Solicitudes","","","","");
		$table[] = array("","","","","","","","","");
		$table[] = array(
			"fecha_creacion" => "Fecha Creacion",
			"local" => "Local",
			"tipo" => "Tipo",
			"subtipo" => "SubTipo",
			"usuario" => "Usuario",
			"area" => "Area",
			"cargo" => "Cargo",
			"estado" => "Estado",
			"motivo" => "Motivo",
		);
			
		foreach($solicitudes as $solicitud){
			$estado_string="";
			if($solicitud['estado']==0){$estado_string="Pendiente";}
			if($solicitud['estado']==1){$estado_string="Aprobado";}
			if($solicitud['estado']==2){$estado_string="Abonado";}
			if($solicitud['estado']==3){$estado_string="Cancelado";}
			if($solicitud['estado']==4){$estado_string="Expirado";}
			if($solicitud['estado']==5){$estado_string="Recibido";}
			if($solicitud['estado']==6){$estado_string="Abonado - Eliminacion - Turno";}
			$table[]=array(
				$solicitud['fecha_creacion'],
				$solicitud['nombre_local'],
				$solicitud['tipo_solicitud_desc'],
				$solicitud['subtipo_solicitud_desc'],
				$solicitud['usuario'],
				$solicitud['area'],
				$solicitud['cargo'],
				$estado_string,
				$solicitud['motivo']
			);
		}
		if (count($table) > 1) {				
			require_once('../phpexcel/classes/PHPExcel.php');
	
			$doc = new PHPExcel();
			$doc->setActiveSheetIndex(0);
			$doc->getActiveSheet()->fromArray($table);
			 
			$filename = "reporte_validados".$get_data['local_id'].".xls";
			$excel_path = '/var/www/html/export/files_exported/local_solicitudes/'.$filename;
	
			$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
			$objWriter->save($excel_path);
	
			echo json_encode(array(
				"path" => '/export/files_exported/local_solicitudes/'.$filename,
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
}                  

?>