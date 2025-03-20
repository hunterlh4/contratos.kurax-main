<?php
include("db_connect.php");
include("sys_login.php");
require("/var/www/html/cron/cron_pdo_connect.php");

if(isset($_POST["solicitud_id"])){
	$data=$_POST["solicitud_id"];
	$bet_id=$_POST["bet_id"];
	$estado=$_POST["estado"];
	$cobrado=false;

	$archivos = $mysqli->query("
	SELECT * FROM tbl_archivos
	WHERE tabla='tbl_solicitud_prestamo' AND item_id=".$data);
	$archivos_array = array();	
	while($archivo=$archivos->fetch_assoc()){
		$archivos_array[]=$archivo;
	}	
	
	$transacciones_array = array();	
	$result = pdoStatement("
	select top 1 * from bet where id='".$bet_id."'");
	foreach($result as $sel) {
		$transacciones_array[]=array(
			"ticket_id"=>$sel["Id"],
			"paid_day"=>$sel["PaidDate"],
			"CashDeskId"=>$sel["CashDeskId"],
			"ganado"=>$sel["WinningAmount"],
			"pagado"=>$sel["WinningAmount"]
		);
	}
	if(count($transacciones_array)>0){
		if($transacciones_array[0]["paid_day"]){
			if($transacciones_array[0]["CashDeskId"]){
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
				$transacciones_array[0]["paid_day"]=$real_date;
				$transacciones_array[0]["local_pago"]=$local[0]['nombreLocal'];
			}else{
				$transacciones_array[0]["local_pago"]='Ticket Web';
			}
			if($estado==0 || $estado==1){
				$mysqli->query("UPDATE tbl_solicitud_prestamo SET estado=4 WHERE id=".$data);		
				$sql_insert_estado = "
				INSERT INTO tbl_historial_solicitud (fecha,usuario,estado_solicitud,solicitud_prestamo_id)
				VALUES(NOW(),0,4,".$data.")";	
				$mysqli->query($sql_insert_estado);	
				$id_historial_solicitud_insertado=$mysqli->insert_id;
				
				try{	
					$correos = array("victor.vega@testtest.apuestatotal.net","manuel.llaguno@testtest.apuestatotal.com");
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
					sol.id='".$data."' LIMIT 1";					
	
					$result = $mysqli->query($solicitud_get_data_query);
					$sol = array();	
					while($item=$result->fetch_assoc()){
						$sol[]=$item;
					}	
					
					$monto_str='';
					if($sol[0]['tipo_solicitud']==1){
						$monto_str='<tr> <td style="font-weight: bold;">Monto</td> <td> S/ '.$sol[0]['monto'].'</td> </tr>';
					}
					array_push($correos,$sol[0]['correo']);
	
					$solicitud_get_correo_jefe_operaciones_query = "
					SELECT
					l.nombre,
					CONCAT(IFNULL(p.nombre,'Sin jefe de operaciones asignado.'), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) as personal_nombre,
					p.correo 
					FROM tbl_local_proveedor_id lp
					INNER JOIN tbl_locales l ON (lp.local_id = l.id)
					INNER JOIN tbl_zonas z ON z.id = l.zona_id
					LEFT JOIN tbl_personal_apt p ON(p.id = z.jop_id AND p.cargo_id=16 AND p.area_id = 21)
					WHERE lp.estado=1 AND lp.servicio_id = 3 AND lp.local_id ='".$sol[0]['local']."'";					
					$locales = $mysqli->query($solicitud_get_correo_jefe_operaciones_query);				
					while($item_local=$locales->fetch_assoc()){
						//array_push($correos,$item_local['correo']);
					}	
					
					include('../sys/mailer/class.phpmailer.php');					
					$mail = new PHPMailer(true);
					$mail->IsSMTP(); 
					$mail->SMTPDebug  = 1;                     
					$mail->SMTPAuth   = true;                  
					$mail->Host       = "smtp.gmail.com";     
					$mail->Port       = 465;  
					$mail->SMTPSecure = "ssl"; 
					foreach ($correos as $correo) {
						$mail->AddAddress($correo);
					}
					$mail->Username   =env('MAIL_GESTION_USER');  
					$mail->Password   =env('MAIL_GESTION_PASS');        
					$mail->FromName = "Apuesta Total";
					$mail->Subject    = "Solicitud Expirada - ".$sol[0]['id'];
					$mail->Body ='<style type="text/css"> @media only screen and (max-width: 480px) { table { display: block !important; width: 100% !important; } td { width: 480px !important; } }</style><body style="font-family: "Malgun Gothic", Arial, sans-serif; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: none; -webkit-font-smoothing: antialiased;"> <table width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0" id="background" style="height: 100% !important; margin: 0; padding: 0; width: 100% !important;"> <tr> <td align="center" valign="top"> <table width="600" border="0" bgcolor="#34495e" cellspacing="0" cellpadding="20" id="preheader"> <tr> <td valign="top"> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tr> <td valign="top" width="600"> <div class="logo"> <h1 style="color: #FFFFFF; font-size: 18px; font-weight: bold; text-align: center; text-decoration: none;">Alerta - Gestion Apuesta Total</h1> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #preheader --> <table width="600" border="2" bordercolor="#34495e" bgcolor="#ffffff" cellspacing="0" cellpadding="20" id="body_container"> <tr> <td align="center" valign="top" class="body_content"> <table width="100%" border="0" cellspacing="0" cellpadding="5"> <tr> <td valign="top"> <h2 style="font-size: 22px; text-align: center;color:#34495e;">Solicitud Expirada</h2> </td> </tr> <tr> <td valign="top" align="center"> <table  border="1" style=" border-collapse: collapse;" cellpadding="3" cellspacing="3"> <tr> <td style="font-weight: bold;">Local</td> <td>'.$sol[0]['nombre_local'].'</td> </tr> <tr> <td style="font-weight: bold;">Tipo</td> <td>'.$sol[0]['descripcion_tipo_solicitud'].' / '.$sol[0]['descripcion_subtipo_solicitud'].'</td> </tr> <tr> <td style="font-weight: bold;">Fecha Creacion</td> <td>'.date('Y/m/d H:i:s', strtotime($sol[0]['fecha_creacion'])).'</td> </tr> <tr> <td style="font-weight: bold;">Estado</td> <td>Pendiente</td> </tr> <tr> <td style="font-weight: bold;">Usuario</td> <td>'.$sol[0]['usuario'].'</td> </tr> '.$monto_str.' <tr> <td style="font-weight: bold;">Motivo</td> <td>'.$sol[0]['motivo'].'</td> </tr> </table> </td> </tr> <tr> <td align="center"> <div> <a href="https://gestion.apuestatotal.com/?sec_id=locales&item_id='.$sol[0]['local'].'#tab=tab_solicitudes" style="background-color:#34495e;border-radius:3px;color:#FFFFFF;display:inline-block;font-family:Helvetica,Arial,sans-serif;font-size:13px;height:45px;line-height:45px;text-align:center;text-decoration:none;text-transform:uppercase;width:150px;-webkit-text-size-adjust:none;mso-hide:all;" onmouseover="this.style.backgroundColor="#4d6b8a"" onmouseout="this.style.backgroundColor="#34495e"">Ver</a> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #body_container --> </td> </tr> </table> <!-- // END #background --></body>';
					$mail->isHTML(true);
					if($mail->Send()) $return["email_sent"]="ok";
				}catch(phpmailerException $ex){
					$return["email_error"]=$mail->ErrorInfo;
					$insert_data["is_error"]=$mail->ErrorInfo;
				}
				
			}		
			$cobrado=true;
		}
	}	
	
	$solicitudes = $mysqli->query("
	SELECT
	sol.id,sol.motivo,sol.monto,sol.estado,sol.tipo_solicitud,sol.subtipo_solicitud,sol.local,sol.tipo_solicitud,sol.bet_id,sol.fecha_creacion,sol.numero_transaccion,
	p.nombre, p.apellido_paterno,
	u.usuario,
	(SELECT a.nombre FROM tbl_areas a WHERE a.id = p.area_id) AS area,
	(SELECT c.nombre FROM tbl_cargos c WHERE c.id = p.cargo_id) AS cargo,
	(SELECT ts.descripcion FROM tbl_tipo_solicitud ts WHERE ts.id = sol.tipo_solicitud) AS tipo_solicitud_desc,
	(SELECT ss.descripcion FROM tbl_subtipo_solicitud ss WHERE ss.id = sol.subtipo_solicitud) AS subtipo_solicitud_desc
	FROM tbl_solicitud_prestamo  sol
	LEFT JOIN tbl_usuarios u ON (u.id = sol.usuario)
	LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
	WHERE sol.id=".$data);
	$ret = array();	
	while($sel=$solicitudes->fetch_assoc()){
		$ret[]=array(
			"id"=>$sel["id"],
			"motivo"=>$sel["motivo"],
			"monto"=>$sel["monto"],
			"usuario"=>$sel["usuario"],
			"nombre"=>$sel["nombre"],
			"apellido_paterno"=>$sel["apellido_paterno"],
			"area"=>$sel["area"],
			"cargo"=>$sel["cargo"],
			"tipo_solicitud_desc"=>$sel["tipo_solicitud_desc"],
			"subtipo_solicitud_desc"=>$sel["subtipo_solicitud_desc"],			
			"estado"=>$sel["estado"],
			"numero_transaccion"=>$sel["numero_transaccion"],			
			"tipo_solicitud"=>$sel["tipo_solicitud"],
			"subtipo_solicitud"=>$sel["subtipo_solicitud"],
			"bet_id"=>$sel["bet_id"],
			"fecha_creacion"=>$sel["fecha_creacion"],		
			"cobrado"=>$cobrado,
			"transaccion"=>$transacciones_array,
			"archivos"=>$archivos_array				
		);
	}	
	print_r(json_encode($ret));	
}
?>	