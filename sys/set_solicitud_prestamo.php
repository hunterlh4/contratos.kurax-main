<?php
include("db_connect.php");
include("sys_login.php");
require("/var/www/html/cron/cron_pdo_connect.php");

if(isset($_POST["opt"])){
	$opt=$_POST["opt"];  
	$save	=true;
	if($opt=="locales_guardar_solicitud"){
		$return=[];
		$data=$_POST["data"];
		$data=$data["values"];

		foreach ($data as $key => $value) {
			if($data["tipo_solicitud"]==1){
				if ($data["subtipo_solicitud"]==2) {
					if(empty($data[$key])){
						$return["error"]="empty";
						$return["error_msg"]="Complete todos los campos!";
						$return["error_focus"]=$key;
						$save  = false;
						break;
					}	
				} else {
					if(empty($data[$key]) && $key !="ticket"){
						$return["error"]="empty";
						$return["error_msg"]="Complete todos los campos!";
						$return["error_focus"]=$key;
						$save  = false;
						break;
					}											
				}								
			}else{
				if(empty($data[$key]) && $key!="monto" && $key!="ticket"){
					$return["error"]="empty";
					$return["error_msg"]="Complete todos los campos!";
					$return["error_focus"]=$key;
					$save  = false;
					break;
				}				
			}						
		}		
		
		if($save && $data["subtipo_solicitud"]==2){

			$solicitudes = $mysqli->query("
			SELECT tbl_locales.nombre,tbl_tipo_solicitud.descripcion as 'tipo_solicitud_desc',tbl_subtipo_solicitud.descripcion as 'subtipo_solicitud_desc',tbl_solicitud_prestamo.*,p.nombre as nombrep, p.apellido_paterno 
			FROM tbl_solicitud_prestamo 
			LEFT JOIN tbl_locales ON tbl_locales.id= tbl_solicitud_prestamo.local
			LEFT JOIN tbl_tipo_solicitud ON tbl_tipo_solicitud.id= tbl_solicitud_prestamo.tipo_solicitud
			LEFT JOIN tbl_subtipo_solicitud ON tbl_subtipo_solicitud.id= tbl_solicitud_prestamo.subtipo_solicitud
			LEFT JOIN tbl_usuarios u ON (u.id = tbl_solicitud_prestamo.usuario)
			LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
			WHERE bet_id=".$data["ticket"]." LIMIT 1");
			$sol = array();	
			while($item=$solicitudes->fetch_assoc()){
				$sol[]=array(
					"id"=>$item["id"],
					"motivo"=>$item["motivo"],
					"monto"=>$item["monto"],
					"nombrep"=>$item["nombrep"],
					"apellido_paterno"=>$item["apellido_paterno"],
					"bet_id"=>$item["bet_id"],
					"local"=>$item["nombre"],
					"tipo_solicitud_desc"=>$item["tipo_solicitud_desc"],
					"subtipo_solicitud_desc"=>$item["subtipo_solicitud_desc"],
					"fecha_creacion"=>$item["fecha_creacion"],
					"estado"=>$item["estado"]
				);
			}
			
			$current_date = new DateTime();
			$current_date->add(new DateInterval("PT9H"));
			$current_date_plus_9 = $current_date->format('Y-m-d H:i:s');

			$ret= array();
			$result = pdoStatement("
			SELECT TOP 1 CashDeskId,Amount as apostado ,WinningAmount as ganado ,Id as ticket_id,CalcDate as created,PaidDate,Source 
			FROM Bet where Id='".$data["ticket"]."' AND State = 4 AND CalcDate >= dateadd(day, -30, '".$current_date_plus_9."')");
			foreach($result as $mont) {
				$ret[]=$mont;	
			}
			
			if(count($ret)){
				if($ret[0]["CashDeskId"]){
					$nombre_local = $mysqli->query("
					SELECT tbl_local_proveedor_id.*,tbl_locales.nombre as nombreLocal FROM tbl_local_proveedor_id 
					LEFT JOIN tbl_locales ON tbl_locales.id= tbl_local_proveedor_id.local_id
					WHERE tbl_local_proveedor_id.proveedor_id='".$ret[0]["CashDeskId"]."' LIMIT 1
					");
					$local = array();	
					while($loc=$nombre_local->fetch_assoc()){
						$local[]=$loc;
					}
					$ret[0]["nombre"]=$local[0]['nombreLocal'];
				}else{
					$ret[0]["nombre"]='Ticket Web';
				}				
			}			
			
			if(count($sol)==0){				
				if(count($ret)<=0){
					$return["error"]="empty";
					$return["error_msg"]="<h3>Ticket ganado no existe</h3>";
					$save=false;
				}
				else{
					if($ret[0]['PaidDate']){
						$return["error"]="empty";
						$return["error_msg"]="<h3>El ticket ya fue pagado.</h3>";
						$save=false;
					}else{
						if($ret[0]['Source']==42){
							$return["error"]="empty";
							$return["error_msg"]="<h3>Ticket Web.</h3>";
							$save=false;
						}else{
							$margen_superior= floatval($ret[0]['ganado'])+100;
							$margen_inferior= floatval($ret[0]['ganado'])-100;
							if($margen_inferior<0){
								$margen_inferior=0;
							}else{
								$margen_inferior=$margen_inferior;
							}
							if($data["monto"] >= $margen_inferior && $data["monto"] <= $margen_superior){
								$save=true;	
							}else{
								$server_date = date_create_from_format('Y-m-d H:i:s', $ret[0]['created']->format('Y-m-d H:i:s'));
								$server_date->sub(new DateInterval("PT9H"));
								$real_date = $server_date->format('Y-m-d H:i:s');
								$str_html_err_response="
								<h3>El monto ingresado no coincide con el monto por pagar del ticket</h3>
								<br/>
								<table class='table table-striped table-user-information'> 
								<tr class='lu_item'> 
									<td>Monto Ingresado</td><td style='color:red;'>".$data["monto"]."</td> 
								</tr>
								<tr class='lu_item'> 
									<td>Por Pagar</td><td>".$ret[0]['ganado']."</td> 
								</tr>
								<tr class='lu_item'>
									<td>Apostado</td><td>".$ret[0]['apostado']."</td> 
								</tr>
								<tr class='lu_item'>
									<td>Local Generado</td><td>".$ret[0]['nombre']."</td> 
								</tr>
								<tr class='lu_item'>
									<td>Ticket</td><td>".$ret[0]['ticket_id']."</td> 
								</tr>
								<tr class='lu_item'>
									<td>Fecha Creación</td><td>".$real_date."</td> 									
								</tr>
								</table>";
								$return["error"]="empty";
								$return["error_msg"]= $str_html_err_response;
								$return["error_focus"]="ticket";
								$save=false;
							}
						}						
					}									
				}
			}
			else{
				$str_estado="";
				if($sol[0]['estado']==0){$str_estado="Pendiente";};
				if($sol[0]['estado']==1){$str_estado="Aprobado";};
				if($sol[0]['estado']==2){$str_estado="Abonado";};
				if($sol[0]['estado']==3){$str_estado="Cancelado";};
				if($sol[0]['estado']==4){$str_estado="Expirado";};
				if($sol[0]['estado']==5){$str_estado="Recibido";};
				if($sol[0]['estado']==6){$str_estado="Abonado-Eliminacion-Turno";};
				$str_html_err_response="
				<h3>El número de ticket ya se encuentra reservado en una solicitud</h3>
				<br/>
				<table class='table table-striped table-user-information'> 
					<tr class='lu_item'>
						<td>Ticket</td><td>".$sol[0]['bet_id']."</td> 
					</tr>	
					<tr class='lu_item'>
						<td>Apostado</td><td>".$ret[0]['apostado']."</td> 
					</tr>					
					<tr class='lu_item'>
						<td>Por Pagar</td><td>".$ret[0]['ganado']."</td> 
					</tr>	
					<tr class='lu_item'>
						<td>Local Creación Ticket</td><td>".$ret[0]['nombre']."</td> 
					</tr>				
					<tr class='lu_item'>
						<td>Local Creación Solicitud</td><td>".$sol[0]['local']."</td> 
					</tr>	
					<tr class='lu_item'>
						<td>Nombre Solicitante</td><td>".$sol[0]['nombrep']." ".$sol[0]['apellido_paterno']."</td> 
					</tr>										
					<tr class='lu_item'>
						<td>Fecha Creación Solicitud</td><td>".$sol[0]['fecha_creacion']."</td> 
					</tr>
					<tr class='lu_item'> 
						<td>Estado Solicitud</td><td>".$str_estado."</td> 
					</tr>
				</table>";
				$return["error"]="empty";
				$return["error_msg"]= $str_html_err_response;
				$return["error_focus"]="ticket";
				$save=false;				
			}			
		}				

		if($save){			
			$sql_insert = "
			INSERT INTO tbl_solicitud_prestamo (motivo,monto,bet_id,usuario,estado,local,tipo_solicitud,subtipo_solicitud,fecha_creacion)
			VALUES('".$data["motivo"]."','".$data["monto"]."','".$data["ticket"]."','".$login["id"]."',0,'".$data["local"]."','".$data["tipo_solicitud"]."','".$data["subtipo_solicitud"]."', NOW())";	
			$mysqli->query($sql_insert);
			$id_solicitud_insertada=$mysqli->insert_id;
			if($mysqli->error){
				echo $mysqli->error;
				echo $trans_command;
				exit();
			}else{
				$sql_insert_estado = "
				INSERT INTO tbl_historial_solicitud (fecha,usuario,estado_solicitud,solicitud_prestamo_id)
				VALUES(NOW(),'".$login["id"]."',0,".$mysqli->insert_id.")";	
				$mysqli->query($sql_insert_estado);

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
					sol.id='".$id_solicitud_insertada."' LIMIT 1";					

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
					LEFT JOIN tbl_personal_apt p ON(z.id AND p.cargo_id=16 AND p.area_id = 21)
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
					$mail->Subject    = "Solicitud Creada - ".$sol[0]['id'];
					$mail->Body ='<style type="text/css"> @media only screen and (max-width: 480px) { table { display: block !important; width: 100% !important; } td { width: 480px !important; } }</style><body style="font-family: "Malgun Gothic", Arial, sans-serif; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: none; -webkit-font-smoothing: antialiased;"> <table width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0" id="background" style="height: 100% !important; margin: 0; padding: 0; width: 100% !important;"> <tr> <td align="center" valign="top"> <table width="600" border="0" bgcolor="#34495e" cellspacing="0" cellpadding="20" id="preheader"> <tr> <td valign="top"> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tr> <td valign="top" width="600"> <div class="logo"> <h1 style="color: #FFFFFF; font-size: 18px; font-weight: bold; text-align: center; text-decoration: none;">Alerta - Gestion Apuesta Total</h1> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #preheader --> <table width="600" border="2" bordercolor="#34495e" bgcolor="#ffffff" cellspacing="0" cellpadding="20" id="body_container"> <tr> <td align="center" valign="top" class="body_content"> <table width="100%" border="0" cellspacing="0" cellpadding="5" style="border-collapse: collapse;"> <tr> <td valign="top"> <h2 style="font-size: 22px; text-align: center;color:#34495e;">Solicitud Creada</h2> </td> </tr> <tr> <td valign="top" align="center"> <table border="1" style=" border-collapse: collapse;" cellpadding="3" cellspacing="3"> <tr> <td style="font-weight: bold;" >Local</td> <td>'.$sol[0]['nombre_local'].'</td> </tr> <tr> <td style="font-weight: bold;" >Tipo</td> <td>'.$sol[0]['descripcion_tipo_solicitud'].' / '.$sol[0]['descripcion_subtipo_solicitud'].'</td> </tr> <tr> <td style="font-weight: bold;" >Fecha Creacion</td> <td>'.date('Y/m/d H:i:s', strtotime($sol[0]['fecha_creacion'])).'</td> </tr> <tr> <td style="font-weight: bold;" >Estado</td> <td>Pendiente</td> </tr> <tr> <td style="font-weight: bold;">Usuario</td> <td>'.$sol[0]['usuario'].'</td> </tr>'.$monto_str.'<tr> <td style="font-weight: bold;">Motivo</td> <td>'.$sol[0]['motivo'].'</td> </tr> </table></td> </tr> <tr> <td align="center"> <div> <a href="http://gestion.apuestatotal.com/?sec_id=locales&item_id='.$sol[0]['local'].'#tab=tab_solicitudes" style="background-color:#34495e;border-radius:3px;color:#FFFFFF;display:inline-block;font-family:Helvetica,Arial,sans-serif;font-size:13px;height:45px;line-height:45px;text-align:center;text-decoration:none;text-transform:uppercase;width:150px;-webkit-text-size-adjust:none;mso-hide:all;" onmouseover="this.style.backgroundColor="#4d6b8a"" onmouseout="this.style.backgroundColor="#34495e"">Ver</a> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #body_container --> </td> </tr> </table> <!-- // END #background --></body>';
					$mail->isHTML(true);
					if($mail->Send()) $return["email_sent"]="ok";
				}catch(phpmailerException $ex){
					$return["email_error"]=$mail->ErrorInfo;
					$insert_data["is_error"]=$mail->ErrorInfo;
				}

			}
			print_r(json_encode($return));
		}
		else{
			print_r(json_encode($return));
		}
	}
	
	if($opt=="locales_cambiar_estado_solicitud"){
		$return=[];
		$data=$_POST["data"];
		$data=$data["values"];

		foreach ($data as $key => $value) {	
				
			if(empty($data[$key])){	

				$return["error"]="empty";
				$return["error_msg"]="Complete todos los campos!";
				$return["error_focus"]=$key;
				$save  = false;
				break;
			}						
		}	
		
		if($save){
			if($data["estado_solicitud"]==1){
				$mysqli->query("UPDATE tbl_solicitud_prestamo SET abonar_a='".$data["abonar_a"]."',estado=".$data["estado_solicitud"]." WHERE id=".$data["solicitud_id"]);
			}else{
				$mysqli->query("UPDATE tbl_solicitud_prestamo SET estado=".$data["estado_solicitud"]." WHERE id=".$data["solicitud_id"]);
			}
			$affected_rows = $mysqli->affected_rows;			
			if($affected_rows==1){
				$sql_insert_estado = "
				INSERT INTO tbl_historial_solicitud (fecha,usuario,estado_solicitud,solicitud_prestamo_id)
				VALUES(NOW(),'".$login["id"]."',".$data["estado_solicitud"].",".$data["solicitud_id"].")";	
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
					sol.id='".$data["solicitud_id"]."' LIMIT 1";					

					$result = $mysqli->query($solicitud_get_data_query);
					$sol = array();	
					while($item=$result->fetch_assoc()){
						$sol[]=$item;
					}

					$historial_solicitud_get_data_query = "
					select tbl_historial_solicitud.*,tbl_usuarios.usuario, concat(tbl_personal_apt.nombre,' ',tbl_personal_apt.apellido_paterno) as nombre_completo 
					from tbl_historial_solicitud
					left join tbl_usuarios on tbl_usuarios.id= tbl_historial_solicitud.usuario
					left join tbl_personal_apt on tbl_personal_apt.id= tbl_usuarios.personal_id
					where tbl_historial_solicitud.id='".$id_historial_solicitud_insertado."' LIMIT 1";					

					$result_historial = $mysqli->query($historial_solicitud_get_data_query);
					$hist = array();	
					while($item=$result_historial->fetch_assoc()){
						$hist[]=$item;
					}
					$monto_str='';
					if($sol[0]['tipo_solicitud']==1){
						$monto_str='<tr> <td>Monto</td> <td> S/ '.$sol[0]['monto'].'</td> </tr>';
					}
					array_push($correos,$sol[0]['correo']);
					$solicitud_get_correo_jefe_operaciones_query = "
					SELECT
					l.nombre,
					CONCAT(IFNULL(p.nombre,'Sin jefe de operaciones asignado.'), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) as personal_nombre,
					p.correo 
					FROM tbl_local_proveedor_id lp
					INNER JOIN tbl_locales l ON (lp.local_id = l.id)
					LEFT JOIN tbl_personal_apt p ON(p.zona_id = l.zona_id AND p.cargo_id=16 AND p.area_id = 21)
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
					$mail->Subject    = "Solicitud ".(($data["estado_solicitud"]==1)?"Aprobada ":"Cancelada ")." - ".$sol[0]['id'];
					$mail->Body ='<style type="text/css"> @media only screen and (max-width: 480px) { table { display: block !important; width: 100% !important; } td { width: 480px !important; } }</style><body style="font-family: "Malgun Gothic", Arial, sans-serif; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: none; -webkit-font-smoothing: antialiased;"> <table width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0" id="background" style="height: 100% !important; margin: 0; padding: 0; width: 100% !important;"> <tr> <td align="center" valign="top"> <table width="600" border="0" bgcolor="#34495e" cellspacing="0" cellpadding="20" id="preheader"> <tr> <td valign="top"> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tr> <td valign="top" width="600"> <div class="logo"> <h1 style="color: #FFFFFF; font-size: 18px; font-weight: bold; text-align: center; text-decoration: none;">Alerta - Gestion Apuesta Total</h1> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #preheader --> <table width="600" border="2" bordercolor="#34495e" bgcolor="#ffffff" cellspacing="0" cellpadding="20" id="body_container"> <tr> <td align="center" valign="top" class="body_content"> <table width="100%" border="0" cellspacing="0" cellpadding="5"> <tr> <td valign="top"> <h2 style="font-size: 22px; text-align: center;color:#34495e;">'.'Solicitud '.(($data["estado_solicitud"]==1)?'Aprobada ':'Cancelada ')."por ".$hist[0]['nombre_completo']." el ".date('Y/m/d H:i:s', strtotime($hist[0]['fecha'])).'</h2> </td> </tr> <tr> <td valign="top" align="center"> <table  border="1" style=" border-collapse: collapse;" cellpadding="3" cellspacing="3"> <tr> <td <td style="font-weight: bold;">Local</td> <td>'.$sol[0]['nombre_local'].'</td> </tr> <tr> <td <td style="font-weight: bold;">Tipo</td> <td>'.$sol[0]['descripcion_tipo_solicitud'].' / '.$sol[0]['descripcion_subtipo_solicitud'].'</td> </tr> <tr> <td <td style="font-weight: bold;">Fecha Creacion</td> <td>'.date('Y/m/d H:i:s', strtotime($sol[0]['fecha_creacion'])).'</td> </tr> <tr> <td <td style="font-weight: bold;">Estado</td> <td>'.(($data["estado_solicitud"]==1)?"Aprobado ":"Cancelado ").'</td> </tr> <tr> <td style="font-weight: bold;">Usuario</td> <td>'.$sol[0]['usuario'].'</td> </tr> '.$monto_str.' <tr> <td <td style="font-weight: bold;">Motivo</td> <td>'.$sol[0]['motivo'].'</td> </tr> </table> </td> </tr> <tr> <td align="center"> <div> <a href="https://gestion.apuestatotal.com/?sec_id=reportes&sub_sec_id=solicitudes" style="background-color:#34495e;border-radius:3px;color:#FFFFFF;display:inline-block;font-family:Helvetica,Arial,sans-serif;font-size:13px;height:45px;line-height:45px;text-align:center;text-decoration:none;text-transform:uppercase;width:150px;-webkit-text-size-adjust:none;mso-hide:all;" onmouseover="this.style.backgroundColor="#4d6b8a"" onmouseout="this.style.backgroundColor="#34495e"">Ver</a> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #body_container --> </td> </tr> </table> <!-- // END #background --></body>';						
					$mail->isHTML(true);
					if($mail->Send()) $return["email_sent"]="ok";
				}catch(phpmailerException $ex){
					$return["email_error"]=$mail->ErrorInfo;
					$insert_data["is_error"]=$mail->ErrorInfo;
				}

			}else{
				$return["error"]="empty";
				$return["error_msg"]="Error al cambiar estado.";										
			}
		}	
		print_r(json_encode($return));
	}	
	
	if( $opt=="reporte_solicitudes_abono_guardar" ){
		$return=[];			
		$id_solicitud =$_POST["id_solicitud"];
		$tabla =$_POST["tabla"];
		$transaccion =$_POST["transaccion"];
		
		$sql_update_solicitud = "UPDATE tbl_solicitud_prestamo SET estado=2,numero_transaccion = '".$transaccion."' WHERE id = '".$id_solicitud."'";
		$mysqli->query($sql_update_solicitud);

		$sql_insert_estado = "
		INSERT INTO tbl_historial_solicitud (fecha,usuario,estado_solicitud,solicitud_prestamo_id)
		VALUES(NOW(),'".$login["id"]."',2,".$id_solicitud.")";	
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
			sol.id='".$id_solicitud."' LIMIT 1";					

			$result = $mysqli->query($solicitud_get_data_query);
			$sol = array();	
			while($item=$result->fetch_assoc()){
				$sol[]=$item;
			}

			$historial_solicitud_get_data_query ="
			SELECT tbl_historial_solicitud.*,tbl_usuarios.usuario, concat(tbl_personal_apt.nombre,' ',tbl_personal_apt.apellido_paterno) as nombre_completo 
			from tbl_historial_solicitud
			left join tbl_usuarios on tbl_usuarios.id= tbl_historial_solicitud.usuario
			left join tbl_personal_apt on tbl_personal_apt.id= tbl_usuarios.personal_id
			where tbl_historial_solicitud.id='".$id_historial_solicitud_insertado."' LIMIT 1";					

			$result_historial = $mysqli->query($historial_solicitud_get_data_query);
			$hist = array();	
			while($item=$result_historial->fetch_assoc()){
				$hist[]=$item;
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
			LEFT JOIN tbl_personal_apt p ON(p.zona_id = l.zona_id AND p.cargo_id=16 AND p.area_id = 21)
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
			$mail->Subject    = "Solicitud Abonada - ".$sol[0]['id'];
			$mail->Body ='<style type="text/css"> @media only screen and (max-width: 480px) { table { display: block !important; width: 100% !important; } td { width: 480px !important; } }</style><body style="font-family: "Malgun Gothic", Arial, sans-serif; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: none; -webkit-font-smoothing: antialiased;"> <table width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0" id="background" style="height: 100% !important; margin: 0; padding: 0; width: 100% !important;"> <tr> <td align="center" valign="top"> <table width="600" border="0" bgcolor="#34495e" cellspacing="0" cellpadding="20" id="preheader"> <tr> <td valign="top"> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tr> <td valign="top" width="600"> <div class="logo"> <h1 style="color: #FFFFFF; font-size: 18px; font-weight: bold; text-align: center; text-decoration: none;">Alerta - Gestion Apuesta Total</h1> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #preheader --> <table width="600" border="2" bordercolor="#34495e" bgcolor="#ffffff" cellspacing="0" cellpadding="20" id="body_container"> <tr> <td align="center" valign="top" class="body_content"> <table width="100%" border="0" cellspacing="0" cellpadding="5"> <tr> <td valign="top"> <h2 style="font-size: 22px; text-align: center;color:#34495e;">Solicitud Abonada por '.$hist[0]['nombre_completo']." el ".date('Y/m/d H:i:s', strtotime($hist[0]['fecha'])).'</h2> </td> </tr> <tr> <td valign="top" align="center"> <table  border="1" style=" border-collapse: collapse;" cellpadding="3" cellspacing="3"> <tr> <td style="font-weight: bold;">Local</td> <td>'.$sol[0]['nombre_local'].'</td> </tr> <tr> <td style="font-weight: bold;">Tipo</td> <td>'.$sol[0]['descripcion_tipo_solicitud'].' / '.$sol[0]['descripcion_subtipo_solicitud'].'</td> </tr> <tr> <td style="font-weight: bold;">Fecha Creacion</td> <td>'.date('Y/m/d H:i:s', strtotime($sol[0]['fecha_creacion'])).'</td> </tr> <tr> <td style="font-weight: bold;">Estado</td> <td>Abonado</td> </tr> <tr> <td style="font-weight: bold;">Usuario</td> <td>'.$sol[0]['usuario'].'</td> </tr> '.$monto_str.' <tr> <td style="font-weight: bold;">Motivo</td> <td>'.$sol[0]['motivo'].'</td> </tr> </table> </td> </tr> <tr> <td align="center"> <div> <a href="https://gestion.apuestatotal.com/?sec_id=locales&item_id='.$sol[0]['local'].'#tab=tab_solicitudes" style="background-color:#34495e;border-radius:3px;color:#FFFFFF;display:inline-block;font-family:Helvetica,Arial,sans-serif;font-size:13px;height:45px;line-height:45px;text-align:center;text-decoration:none;text-transform:uppercase;width:150px;-webkit-text-size-adjust:none;mso-hide:all;" onmouseover="this.style.backgroundColor="#4d6b8a"" onmouseout="this.style.backgroundColor="#34495e"">Ver</a> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #body_container --> </td> </tr> </table> <!-- // END #background --></body>';
			$mail->isHTML(true);
			if($mail->Send()) $return["email_sent"]="ok";
		}catch(phpmailerException $ex){
			$return["email_error"]=$mail->ErrorInfo;
			$insert_data["is_error"]=$mail->ErrorInfo;
		}	
			
		$i=0;
		foreach($_FILES as $tipo_id => $dato) {
			$archivo =  $dato['name'];
			$size =  $dato['size'];
			$ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
			$identificador = date('YmdHis');
			if(!is_dir("/var/www/html/files_bucket/solicitudes/")){
				 mkdir("/var/www/html/files_bucket/solicitudes/",0777,true);
			};
			$pathMain = '/var/www/html/files_bucket/solicitudes/';
			$nombreidentificador = $archivo."_".$identificador;
			$nombreFoto = $pathMain.$nombreidentificador.".".$ext;
	
			move_uploaded_file($dato['tmp_name'],$nombreFoto); 
			$insert_archivo = "INSERT INTO tbl_archivos (tabla,item_id,ext,size,archivo,fecha,orden,estado) 
					VALUES('".$tabla."','".$id_solicitud."','".$ext."','".$size."','".$nombreidentificador.".".$ext."',NOW(),'".$i."','1')";
			$respuesta = $mysqli->query($insert_archivo);
			$i++;
			if ($respuesta === TRUE) {
				$return["response"]=true;
			} else {
				$return["response"]=false;					
			}
		};
		print_r(json_encode($return));
		die;
	}	

	if( $opt=="caja_aceptar_abono_guardar" ){
		$return=[];			
		$id_caja =$_POST['data']['id_caja'];
		$id_solicitud =$_POST['data']['id_solicitud'];		
		$monto =$_POST['data']['monto'];		
		
		$solicitudes=array();
		$sql_get_solicitud =  $mysqli->query("SELECT * FROM tbl_solicitud_prestamo WHERE id = '".$id_solicitud."' LIMIT 1");		
		while($s = $sql_get_solicitud->fetch_assoc()){
			$solicitudes[]=$s;
		}

		$tipo_dato_fisico=0;
		if($solicitudes[0]['subtipo_solicitud'] ==1 || $solicitudes[0]['subtipo_solicitud'] ==2 ){
			$tipo_dato_fisico=2;
		}else{
			$tipo_dato_fisico=18;
		}
		
		$sql_update_solicitud = "UPDATE tbl_solicitud_prestamo SET estado=5,caja_id = '".$id_caja."' WHERE id = '".$id_solicitud."'";
		$mysqli->query($sql_update_solicitud);
		if($mysqli->error){
			$return["error"]="error";
			$return["error_msg"]=$mysqli->error;			
		}
				
		$datos_fisico=array();
		$datos_fisico_query = $mysqli->query("SELECT * FROM tbl_caja_datos_fisicos WHERE caja_id='".$id_caja."' AND tipo_id=".$tipo_dato_fisico." LIMIT 1");
		while($df = $datos_fisico_query->fetch_assoc()){
			$datos_fisico[]=$df;
		}
		if(count($datos_fisico)>0){			
			$monto_actual= floatval($datos_fisico[0]['valor']);
			$monto_final=$monto_actual+floatval($monto);
			$sql_update_valor = "
			UPDATE tbl_caja_datos_fisicos SET valor = REPLACE('".$monto_final."',',','.') WHERE id ='".$datos_fisico[0]['id']."'";							
			$mysqli->query($sql_update_valor);	
		}
		else{
			$caja_unique_id=md5("df_caja_id_".$id_caja."_tipo_id_".$tipo_dato_fisico);
			$sql_insert_valor = "
			INSERT INTO tbl_caja_datos_fisicos (at_unique_id,caja_id,caja_unique_id,tipo_id,valor)
			VALUES('".$caja_unique_id."','".$id_caja."',(SELECT tbl_caja.at_unique_id from tbl_caja WHERE id='".$id_caja."' LIMIT 1),".$tipo_dato_fisico.",'".$monto."')";	
			$mysqli->query($sql_insert_valor);	
		}
		
		$sql_insert_estado = "
		INSERT INTO tbl_historial_solicitud (fecha,usuario,estado_solicitud,solicitud_prestamo_id)
		VALUES(NOW(),'".$login["id"]."',5,".$id_solicitud.")";	
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
			sol.id='".$id_solicitud."' LIMIT 1";					

			$result = $mysqli->query($solicitud_get_data_query);
			$sol = array();	
			while($item=$result->fetch_assoc()){
				$sol[]=$item;
			}

			$historial_solicitud_get_data_query = "
			select tbl_historial_solicitud.*,tbl_usuarios.usuario, concat(tbl_personal_apt.nombre,' ',tbl_personal_apt.apellido_paterno) as nombre_completo 
			from tbl_historial_solicitud
			left join tbl_usuarios on tbl_usuarios.id= tbl_historial_solicitud.usuario
			left join tbl_personal_apt on tbl_personal_apt.id= tbl_usuarios.personal_id
			where tbl_historial_solicitud.id='".$id_historial_solicitud_insertado."' LIMIT 1";					

			$result_historial = $mysqli->query($historial_solicitud_get_data_query);
			$hist = array();	
			while($item=$result_historial->fetch_assoc()){
				$hist[]=$item;
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
			LEFT JOIN tbl_personal_apt p ON(p.zona_id = l.zona_id AND p.cargo_id=16 AND p.area_id = 21)
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
			$mail->Subject    = "Solicitud Recibida - ".$sol[0]['id'];
			$mail->Body ='<style type="text/css"> @media only screen and (max-width: 480px) { table { display: block !important; width: 100% !important; } td { width: 480px !important; } }</style><body style="font-family: "Malgun Gothic", Arial, sans-serif; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: none; -webkit-font-smoothing: antialiased;"> <table width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0" id="background" style="height: 100% !important; margin: 0; padding: 0; width: 100% !important;"> <tr> <td align="center" valign="top"> <table width="600" border="0" bgcolor="#34495e" cellspacing="0" cellpadding="20" id="preheader"> <tr> <td valign="top"> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tr> <td valign="top" width="600"> <div class="logo"> <h1 style="color: #FFFFFF; font-size: 18px; font-weight: bold; text-align: center; text-decoration: none;">Alerta - Gestion Apuesta Total</h1> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #preheader --> <table width="600" border="2" bordercolor="#34495e" bgcolor="#ffffff" cellspacing="0" cellpadding="20" id="body_container"> <tr> <td align="center" valign="top" class="body_content"> <table width="100%" border="0" cellspacing="0" cellpadding="5"> <tr> <td valign="top"> <h2 style="font-size: 22px; text-align: center;color:#34495e;">Solicitud Recibida por '.$hist[0]['nombre_completo']." el ".date('Y/m/d H:i:s', strtotime($hist[0]['fecha'])).'</h2> </td> </tr> <tr> <td valign="top" align="center"> <table  border="1" style=" border-collapse: collapse;" cellpadding="3" cellspacing="3"> <tr> <td style="font-weight: bold;">Local</td> <td>'.$sol[0]['nombre_local'].'</td> </tr> <tr> <td style="font-weight: bold;">Tipo</td> <td>'.$sol[0]['descripcion_tipo_solicitud'].' / '.$sol[0]['descripcion_subtipo_solicitud'].'</td> </tr> <tr> <td style="font-weight: bold;">Fecha Creacion</td> <td>'.date('Y/m/d H:i:s', strtotime($sol[0]['fecha_creacion'])).'</td> </tr> <tr> <td style="font-weight: bold;">Estado</td> <td>Recibido</td> </tr> <tr> <td style="font-weight: bold;">Usuario</td> <td>'.$sol[0]['usuario'].'</td> </tr> '.$monto_str.' <tr> <td style="font-weight: bold;">Motivo</td> <td>'.$sol[0]['motivo'].'</td> </tr> </table> </td> </tr> <tr> <td align="center"> <div> <a href="https://gestion.apuestatotal.com/?sec_id=locales&item_id='.$sol[0]['local'].'#tab=tab_solicitudes" style="background-color:#34495e;border-radius:3px;color:#FFFFFF;display:inline-block;font-family:Helvetica,Arial,sans-serif;font-size:13px;height:45px;line-height:45px;text-align:center;text-decoration:none;text-transform:uppercase;width:150px;-webkit-text-size-adjust:none;mso-hide:all;" onmouseover="this.style.backgroundColor="#4d6b8a"" onmouseout="this.style.backgroundColor="#34495e"">Ver</a> </div> </td> </tr> </table> </td> </tr> </table> <!-- // END #body_container --> </td> </tr> </table> <!-- // END #background --></body>';
			$mail->isHTML(true);
			if($mail->Send()) $return["email_sent"]="ok";
		}catch(phpmailerException $ex){
			$return["email_error"]=$mail->ErrorInfo;
			$insert_data["is_error"]=$mail->ErrorInfo;
		}		

		print_r(json_encode($return));
		die;
	}	

}
?>