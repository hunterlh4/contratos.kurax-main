<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
require("globalFunctions/generalInfo/menu.php");

function data_to_db($d){
	global $mysqli;
	$tmp=array();
	$nulls=array("null","",false);
	foreach ($d as $k => $v) {
		if($v===0){
			$tmp[$k]=$v;
		}elseif(in_array($v, $nulls)){
			$tmp[$k]="NULL";
		}else{
			if(is_float($v)){
				$tmp[$k]="'".$v."'";
			}elseif(is_int($v)){
				$tmp[$k]=$v;
			}else{
				$v=str_replace(",", ".", $v);
				$tmp[$k]="'".trim($mysqli->real_escape_string($v))."'";
			}
		}
	}
	return $tmp;
}

function value_to_db($v){
	global $mysqli;
	$tmp="";
	$nulls=["null",""];
	if(in_array($v, $nulls)){
		$tmp="NULL";
	}else{
		if(is_float($v)){
			$tmp="'".$v."'";
		}elseif(is_int($v)){
			$tmp=$v;
		}else{
			$v=str_replace(",", ".", $v);
			$tmp="'".trim($mysqli->real_escape_string($v))."'";
		}
	}
	return $tmp;
}

function NombreMes($fecha){
	$anio = date("Y", strtotime($fecha));
	$mes = date("m", strtotime($fecha));
	$nombre_mes = "";
	switch ($mes) {
		case '01': $nombre_mes = "Enero"; break;
		case '02': $nombre_mes = "Febrero"; break;
		case '03': $nombre_mes = "Marzo"; break;
		case '04': $nombre_mes = "Abril"; break;
		case '05': $nombre_mes = "Mayo"; break;
		case '06': $nombre_mes = "Junio"; break;
		case '07': $nombre_mes = "Julio"; break;
		case '08': $nombre_mes = "Agosto"; break;
		case '09': $nombre_mes = "Septiembre"; break;
		case '10': $nombre_mes = "Octubre"; break;
		case '11': $nombre_mes = "Noviembre"; break;
		case '12': $nombre_mes = "Diciembre"; break;
	}
	return $nombre_mes." del ".$anio;
}

$return["num_update"]=0;
$return["num_insert"]=0;
$return["num_nothing"]=0;
if(isset($_POST["opt"])){
	$opt=$_POST["opt"];
	if($opt=="add_caja"){
		$data=$_POST["data"];

		if($data["numero_caja_nombre"] == 5)
		{
			// OPCION PERSONALIZADO
			if(trim($data["nombre"]) == "")
			{
				$return["error"] = true;
				$return["msg"] = "Ingrese Nombre";
				print_r(json_encode($return));
				die();
			}

			//INICIO: VALIDAR Nº CAJA EXISTENTE EN LOCAL
			$query_existe_caja = 
			$mysqli->query("SELECT 
							id 
							FROM tbl_local_cajas
							WHERE local_id = ".$data["local_id"]."
								AND nombre = '".$data["nombre"]."'
								AND caja_tipo_id = ".$data["caja_tipo_id"]);

			if($query_existe_caja->num_rows)
			{
				$return["error"] = true;
				$return["msg"] = "Ya existe una caja con ese nombre";
				print_r(json_encode($return));
				die();
			}
			//FIN: VALIDAR Nº CAJA EXISTENTE EN LOCAL

			$sql_insert = 
			"
				INSERT INTO tbl_local_cajas
				(
					local_id,
					caja_tipo_id,
					nombre,
					descripcion,
					estado
				)
				VALUES
				(
					'".$data["local_id"]."',
					'".$data["caja_tipo_id"]."',
					'".$data["nombre"]."',
					'".$data["descripcion"]."',
					'".$data["estado"]."'
				)
			";
		}
		else
		{
			// OPCION DIFERENTE A PERSONALIZADO
			// INICIO: VALIDAR Nº CAJA EXISTENTE EN LOCAL
			$query_existe_caja = 
			$mysqli->query("SELECT 
							id 
							FROM tbl_local_cajas
							WHERE local_id = ".$data["local_id"]." 
								AND nombre = '".$data["param_nombre_texto"]."'
								AND caja_tipo_id = ".$data["caja_tipo_id"]);

			if($query_existe_caja->num_rows)
			{
				$return["error"] = true;
				$return["msg"] = "Ya existe una caja con ese nombre";
				print_r(json_encode($return));
				die();
			}
			// FIN: VALIDAR Nº CAJA EXISTENTE EN LOCAL

			$sql_insert = 
			"
				INSERT INTO tbl_local_cajas
				(
					local_id,
					caja_tipo_id,
					nombre_caja_id,
					nombre,
					descripcion,
					estado
				)
				VALUES
				(
					'".$data["local_id"]."',
					'".$data["caja_tipo_id"]."',
					'".$data["numero_caja_nombre"]."',
					'".$data["param_nombre_texto"]."',
					'".$data["descripcion"]."',
					'".$data["estado"]."'
				)
			";
		}

		$mysqli->query($sql_insert);
		if($mysqli->error){
			echo $mysqli->error;
			echo $trans_command;
			exit();
		}
	}
	if($opt == "add_caja_cdv"){
		if(isset($_POST["data"]['id'])){
			unset($_POST["data"]['id']);	
		}
		$data=$_POST["data"];
		$data_to_db=array();

		if($data["detalle_tipos_id"] != 4 && $data["detalle_tipos_id"] != 28){//4:terminal billetero, 28:Kurax billeteros
			$exists = $mysqli->query("SELECT id FROM tbl_local_caja_detalle_tipos WHERE local_id =".$data["local_id"]." AND detalle_tipos_id = ".$data["detalle_tipos_id"]);
			if($exists->num_rows > 0){
				$return["error"]=true;
				$return["mensaje"]="Ya existe ese tipo de canal de venta";
				print_r(json_encode($return));
				die();
			}
		}
		if($data["detalle_tipos_id"] ==13){
			$num_rows = $mysqli->query("
				SELECT id 
				FROM tbl_local_caja_config 
				WHERE 
					local_id =".$data["local_id"]." AND
					campo='saldo_kasnet' AND
					estado=1
			")->num_rows;
			if(!$num_rows){
				$mysqli->query("
					INSERT INTO tbl_local_caja_config (
						local_id,
						campo,
						valor,
						session_cookie,
						fecha,
						estado
					) VALUES (
						".$data["local_id"].",
						'saldo_kasnet',
						0,
						'".($login["sesion_cookie"] ?: '')."',
						'".date('Y-m-d H:i:s')."',
						1
					)"
				);
			}
		}
		if($data["detalle_tipos_id"] == 21){/*disashop*/
			$num_rows = $mysqli->query("
				SELECT id
				FROM tbl_local_caja_config
				WHERE
					local_id =".$data["local_id"]." AND
					campo='saldo_disashop' AND
					estado=1
			")->num_rows;
			if(!$num_rows){
				$mysqli->query("
					INSERT INTO tbl_local_caja_config (
						local_id,
						campo,
						valor,
						session_cookie,
						fecha,
						estado
					) VALUES (
						".$data["local_id"].",
						'saldo_disashop',
						0,
						'".($login["sesion_cookie"] ?: '')."',
						'".date('Y-m-d H:i:s')."',
						1
					)"
				);
			}
		}
		foreach ($data as $k => $v) {
			$data_to_db[$k]="'".trim($mysqli->real_escape_string($v))."'";
		}

		$data_to_db['created_at'] = 'now()';
		$data_to_db['created_by_user'] = $login['id'];

		$command = "INSERT INTO tbl_local_caja_detalle_tipos";
		$command.="(";
		$command.=implode(",", array_keys($data_to_db));
		$command.=")";
		$command.=" VALUES ";
		$command.="(";
		$command.=implode(",", $data_to_db);
		$command.=")";
		$mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command;
			exit();
		}
	} else if ($opt == "editar_local_caja_detalle_tipo" ) {
		
		$data = $_POST["data"];

		$id = $data['id'];
		$nombre = $data['nombre'];
		$descripcion = $data['descripcion'];
		$userId = $login['id'];

		$selectQuery = "SELECT nombre, descripcion FROM tbl_local_caja_detalle_tipos WHERE id = ?";
		$stmt = $mysqli->prepare($selectQuery);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$currentData = $result->fetch_assoc();
		$stmt->close();

		$oldNombre = $currentData['nombre'];
        $oldDescripcion = $currentData['descripcion'];
	
		$query = "  UPDATE tbl_local_caja_detalle_tipos
					SET     
						nombre = '" . $nombre . "',
						descripcion = '" . $descripcion . "',
						updated_by_user = " . $login['id'] . ",
						updated_at = now()
					WHERE id = " . $id;

		$updated = $mysqli->query($query);
		if($updated){

			 $insertHistQuery = "INSERT INTO tbl_local_caja_detalle_tipos_historial_cambios
			 (tbl_local_caja_detalle_tipos_id, valor_nombre_anterior, valor_nombre_nuevo, valor_descripcion_anterior, valor_descripcion_nuevo, status, id_usuario, created_at)
			 VALUES (?, ?, ?, ?, ?, 1, ?, NOW())";
			$stmt = $mysqli->prepare($insertHistQuery);
			$stmt->bind_param("issssi", $id, $oldNombre, $nombre, $oldDescripcion, $descripcion, $userId);
			$stmt->execute();
			$stmt->close();

			$return["updated"] = true;
			$return["mensaje"] = "Canal de venta editado";
		} else {
			$return["error"] = true;
			$return["mensaje"] = "Error: ". $mysqli->error;
			print_r(json_encode($return));
			die();
		}
	
	} else
	if($opt=="locales_guardar_caja_config"){
		$data=$_POST["data"];
		if(isset($data["config"]["monto_anterior"])){
			if(trim($data["config"]["monto_anterior"]) == ""){
				$return["error"] = true;
				$return["msg"] = "Los campos no pueden estar vacios.";
				print_r(json_encode($return));
				die();
			}
		}
		if(isset($data["config"]["monto_inicial"])){
			if(trim($data["config"]["monto_inicial"]) == ""){
				$return["error"] = true;
				$return["msg"] = "Los campos no pueden estar vacios.";
				print_r(json_encode($return));
				die();
			}
		}
		if(isset($data["config"]["valla_deposito"])){
			if(trim($data["config"]["valla_deposito"]) == ""){
				$return["error"] = true;
				$return["msg"] = "Los campos no pueden estar vacios.";
				print_r(json_encode($return));
				die();
			}
		}
		if(isset($data["config"]["saldo_disashop"])){
			if(trim($data["config"]["saldo_disashop"]) == ""){
				$return["error"] = true;
				$return["msg"] = "Los campos no pueden estar vacios.";
				print_r(json_encode($return));
				die();
			}
		}
		// print_r($data);
		// if(trim($data["config"]["monto_anterior"]) == "" || trim($data["config"]["monto_inicial"]) == "" || trim($data["config"]["valla_deposito"]) == "" || trim($data["config"]["saldo_disashop"]) == ""){
		// 	$return["error"] = true;
		// 	$return["msg"] = "Los campos no pueden estar vacios.";
		// 	print_r(json_encode($return));
		// 	die();
		// }
		if($data["config"]["monto_inicial"] < 0 || $data["config"]["valla_deposito"] < 0){
			$return["error"] = true;
			$return["msg"] = "Monto inicial y Valla depósito no pueden ser negativos.";
			print_r(json_encode($return));
			die();
		}

		if(array_key_exists("config", $data)){
			foreach ($data["config"] as $campo => $valor) {
				$update_command = "UPDATE tbl_local_caja_config SET estado = '0' WHERE local_id = '".$data["item_id"]."' AND campo = '".$campo."'";
				$mysqli->query($update_command);

				$config = array();
				$config["local_id"]=$data["item_id"];
				$config["campo"]=$campo;
				$config["valor"]=str_replace(",", "", $valor);
				$config["session_cookie"]=$login["sesion_cookie"] ?: '';
				$config["fecha"]=date("Y-m-d H:i:s");
				$config["estado"]=1;

				$data_to_db = array();
				foreach ($config as $k => $v) {
					$data_to_db[$k]="'".trim($mysqli->real_escape_string($v))."'";
				}
				$command = "INSERT INTO tbl_local_caja_config";
				$command.="(";
				$command.=implode(",", array_keys($data_to_db));
				$command.=")";
				$command.=" VALUES ";
				$command.="(";
				$command.=implode(",", $data_to_db);
				$command.=")";
				$mysqli->query($command);
				if($mysqli->error){
					print_r($mysqli->error);
					echo "\n";
					echo $command;
					exit();
				}
			}
		}
	}
	if($opt=="locales_incrementar_saldo_kasnet"){
		$data=$_POST["data"];
		$saldo_anterior = 0;
		$result = $mysqli->query("SELECT saldo_final FROM tbl_saldo_kasnet WHERE local_id = ".$data["item_id"]." AND estado=1 ORDER BY id DESC LIMIT 1");
		while($r = $result->fetch_assoc()) $saldo_anterior = $r["saldo_final"];

		$mysqli->query("INSERT INTO tbl_saldo_kasnet (local_id, saldo_anterior, saldo_incremento, saldo_final, session_cookie, created_at, updated_at) VALUES(
			".$data["item_id"].",
			".$saldo_anterior.",
			".$data["saldo"].",
			".(double)($saldo_anterior+$data["saldo"]).",
			'".$login["sesion_cookie"]."',
			'".date("Y-m-d H:i:s")."',
			'".date("Y-m-d H:i:s")."'
		)");

	}
}
if(isset($_POST["locales_add_usuario"])){
	$usuario_id = $login?$login['id']:null;
	$fecha_hora = date("Y-m-d H:i:s");
	$data=$_POST["locales_add_usuario"];
	$locales_personal_id=$_POST["locales_personal_id"];
	$locales_usuarios_id=$_POST["locales_usuarios_id"];

	if($locales_personal_id==0){
		$dni = $mysqli->query("SELECT id, estado FROM tbl_personal_apt	 WHERE dni = '".$data["dni"]."'")->fetch_assoc();
	}else{
		$dni = $mysqli->query("SELECT id, estado FROM tbl_personal_apt	 WHERE dni = '".$data["dni"]."' AND id != '".$locales_personal_id."'")->fetch_assoc();
	}
	
	if($locales_usuarios_id==0){
		$exists = $mysqli->query("SELECT id FROM tbl_usuarios WHERE usuario = '".$data["usuario"]."'")->fetch_assoc();
	}else{
		$exists = $mysqli->query("SELECT id FROM tbl_usuarios WHERE usuario = '".$data["usuario"]."' AND id != '".$locales_usuarios_id."'")->fetch_assoc();
	}
	$add_permisos = false;
	if($data["usuario_id"]=="new"){
		$save  = true;
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
			//if($data["id"]!=0){

			//}else{
				if(!empty($exists)){
					$return["error"]="exists";
					$return["error_msg"]="El usuario ya existe!";
					$return["error_focus"]="usuario";
					$return["usuario_id"]=$exists["id"];
				}
				else if(!empty($dni)){
					$msg_estado_personal = '';
					if($dni['estado'] == 0){
						$msg_estado_personal = 'El personal está inactivo.';
					} else if($dni['estado'] == 1){
						$msg_estado_personal = 'El personal está activo.';
					}  

					$return["error"]="exists";
					$return["error_msg"]="DNI ya registrado! " . $msg_estado_personal;
					$return["error_focus"]="dni";
					$return["personal_id"]=$dni["id"];
				} else if (!is_numeric($data["dni"])) {
					$return["error"]="no_numeric";
					$return["error_msg"]="¡DNI no es numérico!";
					$return["error_focus"]="dni";
				} else if ( strlen($data["dni"])<8 || strlen($data["dni"])>8){
					$return["error"]="lenght";
					$return["error_msg"]="¡DNI no tiene la cantidad de dígitos correctos!";
					$return["error_focus"]="dni";
				} else{

			//-------- Verificar si el personal ya existe y actualizar
				if($locales_personal_id==0 ){
					$mysqli->query("START TRANSACTION");
					$insert_personal_command = "INSERT INTO tbl_personal_apt (area_id, nombre, apellido_paterno, dni, cargo_id, estado,created_at,user_created_id) ";
					$insert_personal_command.= " VALUES ('".$data["area_id"]."','".$data["nombre"]."','".$data["apellido_paterno"]."','".$data["dni"]."','".$data["cargo_id"]."','1','".$fecha_hora."','".$usuario_id."')";
					$mysqli->query($insert_personal_command);
					if($mysqli->error){
						print_r($mysqli->error);
						echo "\n";
						echo $insert_command;
						exit();
					}
					// $return["insert_personal_command"]=$insert_personal_command;
					$return["personal_id"] = $mysqli->insert_id;
					$personal_id = $return["personal_id"];
				}else{
					$mysqli->query("START TRANSACTION");
					$update_personal_command = "UPDATE tbl_personal_apt 
												SET 
													area_id = '".$data["area_id"]."',
													nombre = '".$data["nombre"]."',
													apellido_paterno = '".$data["apellido_paterno"]."',
													dni = '".$data["dni"]."',
													cargo_id = '".$data["cargo_id"]."',
													estado = 1,
													updated_at = '".$fecha_hora."',
													user_updated_id = '".$usuario_id."'
												WHERE 
													id = '".$locales_personal_id."'";
					$mysqli->query($update_personal_command);
					if($mysqli->error){
						print_r($mysqli->error);
						echo "\n";
						echo $insert_command;
						exit();
					}
					$personal_id = $locales_personal_id;
				}
	
					$grupo_id = false;
					if($data["area_id"] == 21 && $data["cargo_id"] == 5){
						$grupo_id = 11;
					}
	
					if($data["area_id"] == 31 && $data["cargo_id"] == 5){//televentas-cajero
						$grupo_id = 26;
					}
	
					if($data["area_id"] == 31 && $data["cargo_id"] == 21){//televentas-validador
						$grupo_id = 29;
					}
					if($data["area_id"] == 31 && $data["cargo_id"] == 23){//televentas-pagador
						$grupo_id = 30;
					}
					if($data["area_id"] == 31 && $data["cargo_id"] == 24){//televentas-digitador
						$grupo_id = 32;
					}
	
					if($data["area_id"] == 28 && $data["cargo_id"] == 5){//Agentes - cajero
						$grupo_id = 27;
					}
	
			if($locales_usuarios_id==0){

				$insert_usuario_command = "
						INSERT INTO tbl_usuarios(
							usuario,
							personal_id,
							sistema_id,
							estado,
							grupo_id,
							created_at,
							user_created_id
						) VALUES (
							'".$data["usuario"]."',
							'".$personal_id."',
							'7',
							'1',
							'".$grupo_id."',
							'".$fecha_hora."',
							'".$usuario_id."'
						)
				";

				$mysqli->query($insert_usuario_command);
				if($mysqli->error){
					print_r($mysqli->error);
					echo "\n";
					echo $insert_usuario_command;
					exit();
				}
				$return["usuario_id"] = $mysqli->insert_id;
				
	
				if($grupo_id){
					$result = $mysqli->query("
							INSERT INTO tbl_permisos
								(grupo_id,usuario_id,menu_id,boton_id,boton_nombre,estado,manual)
							SELECT 
								grupo_id,".$return["usuario_id"].",menu_id,boton_id,boton_nombre,estado,manual
							FROM tbl_permisos 
							WHERE 
								usuario_id = 0 
								AND grupo_id = ".$grupo_id."
						");
					if($mysqli->error){
						print_r($mysqli->error);
						echo "\n";
						echo $insert_usuario_command;
						exit();
						}

				
					$insert_usuario_local_command = "INSERT INTO tbl_usuarios_locales (usuario_id,local_id,estado) ";
					$insert_usuario_local_command.= "VALUES('".$return["usuario_id"]."','".$data["id"]."','1')";
					$mysqli->query($insert_usuario_local_command);
					if($mysqli->error){
						print_r($mysqli->error);
						echo "\n";
						echo $insert_usuario_local_command;
						exit();
					}
					// $return["insert_usuario_local_command"]=$insert_usuario_local_command;
					$mysqli->query("COMMIT");
					$add_permisos=true;
				}
			}else{

				$update_usuario_command = "
						UPDATE tbl_usuarios
						SET
							usuario = '".$data["usuario"]."',
							personal_id ='".$personal_id."',
							sistema_id = '7',
							estado = '1',
							grupo_id = '".$grupo_id."',
							updated_at = '".$fecha_hora."',
							user_updated_id = '".$usuario_id."'
						WHERE
							id= '".$locales_usuarios_id."'
					";
				$mysqli->query($update_usuario_command);
				if($mysqli->error){
					print_r($mysqli->error);
					echo "\n";
					echo $update_usuario_command;
					exit();
				}
				
				if($grupo_id){

					if($grupo_id != 0){

						$menu_manual_usuario_id = getMenuBySecId('manual_usuarios');
						
							$query = "
								SELECT 
									estado,
									menu_id,
									boton_id 
								FROM tbl_permisos WHERE
									usuario_id=0 AND
									grupo_id = ".$grupo_id;
							$groupPerms = $mysqli->query($query);
							$mysqli->query("
								UPDATE tbl_permisos SET 
									grupo_id = ".$grupo_id."
								WHERE usuario_id = ".$locales_usuarios_id);
			
							$mysqli->query("UPDATE tbl_permisos
								SET estado = 0
								WHERE manual = 0
								AND menu_id !=  ".$menu_manual_usuario_id."
								AND  usuario_id = ".$locales_usuarios_id);
			
							foreach($groupPerms as $perm){
								$getPerm = $mysqli->query("
									SELECT manual FROM tbl_permisos
									WHERE 
										menu_id=".$perm["menu_id"]." AND
										boton_id=".$perm["boton_id"]." AND
										menu_id != ".$menu_manual_usuario_id." AND
										usuario_id=".$locales_usuarios_id);
								if($getPerm->num_rows > 0){
									$update_perms = "
										UPDATE tbl_permisos SET 
											estado = ".$perm["estado"]."
										WHERE manual=0 AND
											menu_id=".$perm["menu_id"]." AND
											boton_id=".$perm["boton_id"]." AND
											usuario_id=".$locales_usuarios_id;
									$mysqli->query($update_perms);
								}
								else{
									//foreach ($getPerm as $getManual) $manual = $getManual["manual"];
									//if(isset($manual) && $manual == 0){
										$queryInsertPerm="INSERT INTO tbl_permisos (
										grupo_id,
										menu_id,
										boton_id,
										boton_nombre,
										estado,
										usuario_id)
										VALUES (
										".$grupo_id.",
										".$perm["menu_id"].",
										".$perm["boton_id"].",
										(SELECT nombre FROM tbl_menu_sistemas_botones WHERE boton=".addslashes($perm["boton_id"])." AND menu_id=".addslashes($perm["menu_id"])." LIMIT 1),
										".$perm["estado"].",
										".$locales_usuarios_id.")";
										$mysqli->query($queryInsertPerm);
									//}
								}
							}
						}
					
					}
	
				// Se verifica si el usuario tiene un permiso de ese local

				$selectQuery = "SELECT id, estado
						FROM tbl_usuarios_locales
						WHERE usuario_id = ? AND local_id = ?";

				$selectStmt = $mysqli->prepare($selectQuery);
				$selectStmt->bind_param("ii", $locales_usuarios_id,$data["id"] );
				$selectStmt->execute();
				$selectStmt->store_result();

				if ($selectStmt->num_rows > 0) {
					$selectStmt->bind_result($usuarios_locales_id, $usuarios_locales_estado);
					$selectStmt->fetch();

					//	Si tiene un permiso existente pero deshabilitado se procede a habilitarlo
					if($usuarios_locales_estado == 0){
						$update_usuario_local_command = "
								UPDATE tbl_usuarios_locales 
								SET									
									estado = 1
								WHERE
									id= '".$usuarios_locales_id."'
							";
							$mysqli->query($update_usuario_local_command);
						if($mysqli->error){
							print_r($mysqli->error);
							echo "\n";
							echo $update_usuario_local_command;
							exit();
						}
					}
				}else{
						// Si no tiene un permiso de ese local se crea uno nuevo
					$insert_usuario_local_command = "INSERT INTO tbl_usuarios_locales (usuario_id,local_id,estado) ";
					$insert_usuario_local_command.= "VALUES('".$locales_usuarios_id."','".$data["id"]."','1')";
					$mysqli->query($insert_usuario_local_command);
					if($mysqli->error){
						print_r($mysqli->error);
						echo "\n";
						echo $insert_usuario_local_command;
						exit();
					}

				}
					// $return["insert_usuario_local_command"]=$insert_usuario_local_command;
					$mysqli->query("COMMIT");
					$add_permisos=false;
				}
			}

			//}
		}
	}else{
		// Se busca la red del local
		$local_red_id = 0;
		$area_requerida_2 = "";
		$cargo_where = "";

		$result_local_red_id = $mysqli->query("SELECT red_id
		FROM tbl_locales
		WHERE id = ".$data["id"]);
		while($r = $result_local_red_id->fetch_assoc()) $local_red_id = $r["red_id"];

		// En caso que sea RED AT o RED TAMBO
		if ($local_red_id == 1 || $local_red_id == 7) {
			$cargo_where .= "4,5,13,17,28";
			$area_requerida = "21";
		}
		// En caso que sea RED AGENTES AT
		else if ($local_red_id == 5) {
			$cargo_where .= "4,5,13,17,28";
			$area_requerida = "28";
		}
		// En caso que sea RED TELEVENTAS
		else if ($local_red_id == 8) {
			$cargo_where .= "4,5,17,21";
			$area_requerida = "31";
		}
		// En caso el local no pertenezca a las redes anteriores
		else {
			$cargo_where .= "4,5";
			$area_requerida = "21 OR (papt.area_id = ('22') AND papt.cargo_id != 3) OR us.grupo_id in (26,27,30)";
			$area_requerida_2 = "AND (
				IF(
					(
						(
							SELECT count(*)
							FROM   tbl_usuarios_locales ul
							LEFT JOIN tbl_usuarios u ON  (u.id = ul.usuario_id)
							LEFT JOIN tbl_personal_apt p ON  (p.id = u.personal_id)
							WHERE  ul.local_id = ".$data["id"]."
								AND ul.estado = '1'
								AND (
									p.area_id = ('21')
									AND p.cargo_id IN (4, 5)
									OR (p.area_id = ('22') AND p.cargo_id != 3)
									OR (p.area_id = '31' AND p.cargo_id = 5)
									OR (p.area_id = '28' AND p.cargo_id = 5)
									)
								AND p.cargo_id = 4
						) > 0
					)
					,papt.cargo_id NOT IN (4)
					,papt.cargo_id NOT IN('')
				)
			)";
		}

		// Query para comprobar si el usuario comple con las condiciones
		$user_permitido = "";
		$query_users = "
		SELECT
			papt.id
		FROM tbl_personal_apt papt
		INNER JOIN tbl_usuarios us ON (us.personal_id = papt.id)
		WHERE
			us.id = ".$data["usuario_id"]."
			AND papt.cargo_id IN (".$cargo_where.")
			AND (papt.area_id = ".$area_requerida.")
			$area_requerida_2
		";
		$result_qusers = $mysqli->query($query_users);
		while($r = $result_qusers->fetch_assoc()) $user_permitido = $r["id"];

		if ($user_permitido == "") {
			$return["error"] = true;
			// $return["query_users"] = $query_users;
			$return["error_msg"] = "El usuario no puede ser agregado a este local, por favor notificar a su superior’";
			print_r(json_encode($return));
			die();
		} else {
			$local_users = [];
			$result = $mysqli->query("
				SELECT 
					ul.usuario_id 
				FROM tbl_usuarios_locales ul
				INNER JOIN tbl_usuarios u ON u.id = ul.usuario_id
				INNER JOIN tbl_personal_apt p ON p.id = u.personal_id
				WHERE 
					ul.usuario_id = '".$data["usuario_id"]."' 
					AND ul.local_id = '".$data["id"]."'
					AND p.area_id = 21 
					AND p.cargo_id = 4
			");
			while($r = $result->fetch_assoc()) $local_users[] = $r["id"];
			$return["usuario_id"] = $data["usuario_id"];
			$usuario_local_exists = $mysqli->query("SELECT id FROM tbl_usuarios_locales WHERE usuario_id = '".$data["usuario_id"]."' AND local_id = '".$data["id"]."'")->fetch_assoc();
			$mysqli->query("START TRANSACTION");
			if($usuario_local_exists){
				$update_usuario_local = "UPDATE tbl_usuarios_locales SET estado = '1' WHERE id = '".$usuario_local_exists["id"]."'";
				$mysqli->query($update_usuario_local);
				if($mysqli->error){
					print_r($mysqli->error);
					echo "\n";
					echo $update_usuario_local;
					exit();
				}
				// $return["update_usuario_local"]=$update_usuario_local;
			}else{
				$insert_usuario_local_command = "INSERT INTO tbl_usuarios_locales (usuario_id,local_id,estado) ";
				$insert_usuario_local_command.= "VALUES('".$data["usuario_id"]."','".$data["id"]."','1')";
				$mysqli->query($insert_usuario_local_command);
				if($mysqli->error){
					print_r($mysqli->error);
					echo "\n";
					echo $insert_usuario_local_command;
					exit();
				}
				// $return["insert_usuario_local_command"]=$insert_usuario_local_command;
			}
			$mysqli->query("COMMIT");
			$add_permisos=true;
		}
	}
	if($add_permisos){
		$permiso_exists = $mysqli->query("SELECT id FROM tbl_permisos WHERE usuario_id = '".$return["usuario_id"]."' AND menu_id = '75' AND boton_id = '1'")->fetch_assoc();
		if($permiso_exists){
			$permiso_command = "UPDATE tbl_permisos SET estado = '1' WHERE id = '".$permiso_exists["id"]."'";
		}else{
			$permiso_command = "INSERT INTO tbl_permisos (usuario_id,menu_id,boton_id,estado) VALUES ('".$return["usuario_id"]."','75','1','1')";
		}
		$mysqli->query($permiso_command);
		// $return["permiso_command"]=$permiso_command;
		try {
			require_once("/var/www/html/app/Controllers/UsuarioLog/UsuarioLogController.php");
				$usuarioLogController = new UsuarioLogController();
				$result = $usuarioLogController->store(array_merge($data,["action"=>"add","user_id"=>$login["id"]]));
			} catch (\Throwable $th) {
				//throw $th;
			}
	}
}

if(isset($_POST["locales_remove_usuario"])){
	$data=$_POST["locales_remove_usuario"];
	$locales_remove_usuario_command = "DELETE FROM tbl_usuarios_locales WHERE usuario_id = '".$data["usuario_id"]."' AND local_id = '".$data["id"]."'";
	// echo $locales_remove_usuario_command;
	$mysqli->query($locales_remove_usuario_command);
	try {
		require_once("/var/www/html/app/Controllers/UsuarioLog/UsuarioLogController.php");
			$usuarioLogController = new UsuarioLogController();
			$result = $usuarioLogController->store(array_merge($data,["action"=>"remove","user_id"=>$login["id"]]));
		} catch (\Throwable $th) {
			//throw $th;
		}
}

//En caso se active un usuario directamente en Locales
if(isset($_POST["locales_activate_usuario"])){
	$data=$_POST["locales_activate_usuario"];
	$action="";
	if($data['check']=="true"){
		$action = "activar";
		$vsql= "update tbl_usuarios set estado=1 where id=".$data["usuario_id"];
		$vsql_2= "UPDATE tbl_personal_apt apt SET apt.estado=1 WHERE apt.id = (SELECT u.personal_id FROM tbl_usuarios u WHERE u.id = {$data["usuario_id"]} LIMIT 1)";
		$mysqli->query($vsql_2);
	}else{
		$action = "desactivar";
		$vsql= "update tbl_usuarios set estado=0 where id=".$data["usuario_id"];	
	}
	$mysqli->query($vsql);
	try {
		require_once("/var/www/html/app/Controllers/UsuarioLog/UsuarioLogController.php");
			$usuarioLogController = new UsuarioLogController();
			$result = $usuarioLogController->store(array_merge($data,["action"=>$action,"user_id"=>$login["id"]]));
		} catch (\Throwable $th) {
			//throw $th;
		}
}

if(isset($_POST["sec_local_credencial_archivo_guardar"])){	
	$id_campo =$_POST["id_campo"];
	$tabla =$_POST["tabla"];
	
	$string_campos=$_POST["campos"];
	$array_campos=[];
	$array_campos= json_decode($string_campos,true);		
	foreach ($array_campos as $key => $extra) {
		if(trim($extra["id"])==""){				
			$sql_insert_cctv_id = "
			INSERT INTO tbl_local_credencial_detalle_valor (local_id,campo_tipo_credencial_id,valor) 
			VALUES (".value_to_db($extra["local_id"]).",".value_to_db($extra["campo_tipo_credencial_id"]).",".value_to_db($extra["valor"]).")";
			$return["sql_insert"][]=$sql_insert_cctv_id;
			$mysqli->query($sql_insert_cctv_id);
			$id_campo=$mysqli->insert_id;
			echo($sql_insert_cctv_id);
		}else{
			$sql_update_cctv_id = "UPDATE tbl_local_credencial_detalle_valor SET valor = ".value_to_db($extra["valor"])." WHERE id = '".$extra["id"]."'";
			$mysqli->query($sql_update_cctv_id);
			$return["sql_update"][]=$sql_update_cctv_id;
		}
	}

	//echo count($_FILES);exit();
	$i=0;
	foreach($_FILES as $tipo_id => $dato) {
		$archivo =  $_FILES['file']['name'];
		$size =  $_FILES['file']['size'];
		$ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
		$fecha = date('Y-m-d H:i:s');
		$identificador = date('YmdHis');
		if(!is_dir("/var/www/html/files_bucket/credenciales/")){
			mkdir("/var/www/html/files_bucket/credenciales/",0777,true);
		};
		$pathMain = '/var/www/html/files_bucket/credenciales/';
		$nombreidentificador = $id_campo."_".$archivo."_".$identificador;
		$nombreFoto = $pathMain.$nombreidentificador.".".$ext;

		move_uploaded_file($_FILES['file']['tmp_name'],$nombreFoto); 
		$insert_archivo = "INSERT INTO tbl_archivos (tabla,item_id,ext,size,archivo,fecha,orden,estado) 
				VALUES('".$tabla."','".$id_campo."','".$ext."','".$size."','".$nombreidentificador.".".$ext."','".$fecha."','".$i."','1')";
		$respuesta = $mysqli->query($insert_archivo);
		$i++;
		if ($respuesta === TRUE) {
			$last_id = mysqli_insert_id($mysqli);
			echo "1@".$last_id."@".$nombreidentificador.".".$ext;
		} else {
			echo "0@Error: " . $insert_archivo . "<br>";
		}
	};
	die;
}

if(isset($_POST["sec_local_credencial_archivo_eliminar"])){
	$id = $_POST["sec_local_credencial_archivo_eliminar"];
	$nombre = $_POST["nombre_archivo"];

	rename ('/var/www/html/files_bucket/credenciales/'.$nombre, '/var/www/html/files_bucket/credenciales/del_'.$nombre);
	$delete_archivo = "update tbl_archivos SET estado = '0' WHERE id='".$id ."'";
	$respuesta=$mysqli->query($delete_archivo);
	if ($respuesta === TRUE) {
		echo "ok";
	} else {
		echo "Error";
	}
	die;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_locales_tab_servicio_publico_listar_codigo_suministro") 
{

	$local_id = $_POST["local_id"];
	$tipo_servicio = $_POST["tipo_servicio"];

	$query = 
	"
		SELECT
			s.id AS inmueble_suministro_id,
			s.tipo_servicio_id,
			s.nro_suministro AS codigo_suministro,
		    ps.id AS pago_servicio_id,
		    ps.nombre
		FROM tbl_locales l
			INNER JOIN cont_contrato c
			ON c.contrato_id = l.contrato_id
			INNER JOIN cont_contrato_detalle AS cd 
			ON cd.contrato_id = c.contrato_id AND cd.status = 1
			INNER JOIN cont_inmueble AS i 
			ON i.contrato_id = c.contrato_id AND i.status = 1
			INNER JOIN cont_inmueble_suministros AS s
			ON s.inmueble_id = i.id AND s.status = 1
			INNER JOIN cont_tipo_pago_servicio AS ps 
			ON ps.id = s.tipo_compromiso_pago_id
		WHERE l.id = '".$local_id."' AND s.tipo_servicio_id = '".$tipo_servicio."'
		GROUP BY s.id
		ORDER BY s.id ASC
	";
	
	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$return["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0)
	{
		$return["http_code"] = 400;
		$return["result"] = "No se encontro resultados.";
	} 
	elseif (count($list) > 0) 
	{
		$return["http_code"] = 200;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["result"] = $list;
	} 
	else 
	{
		$return["http_code"] = 400;
		$return["result"] = "No existen registros.";
	}

	print_r(json_encode($return));
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_locales_tab_servicio_publico_mostrar_compromiso_pago") 
{

	$inmueble_suministro_id = $_POST["inmueble_suministro_id"];

	$query = 
	"
		SELECT
			s.id,
		    s.tipo_compromiso_pago_id,
			s.monto_o_porcentaje,
			ps.nombre,
		    ps.texto_mensaje
		FROM cont_inmueble_suministros s
			INNER JOIN cont_tipo_pago_servicio ps
			ON s.tipo_compromiso_pago_id = ps.id
		WHERE s.id = '".$inmueble_suministro_id."'
		LIMIT 1
	";
	
	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$return["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0)
	{
		$return["http_code"] = 400;
		$return["result"] = "No se encontro resultados.";
	} 
	elseif (count($list) > 0) 
	{
		$return["http_code"] = 200;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["result"] = $list;
	} 
	else 
	{
		$return["http_code"] = 400;
		$return["result"] = "No existen registros.";
	}

	print_r(json_encode($return));
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_locales_tab_servicio_publico_guardar_nuevo_servicio") 
{
	$usuario_id = $login?$login['id']:null;
	$error = '';

	$param_id = $_POST['sec_locales_tab_servicio_publico_form_modal_param_id'];

	if((int)$usuario_id > 0)
	{
		$param_local_id = $_POST['sec_locales_tab_servicio_publico_form_modal_param_local_id'];
		$param_tipo_servicio = $_POST['sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio'];
		$param_num_suministro_id = $_POST['sec_locales_tab_servicio_publico_form_modal_param_num_suministro'];
		$param_compromiso_pago_id = $_POST['sec_locales_tab_servicio_publico_form_modal_param_compromiso_pago_id'];
		$param_mes_facturado = $_POST["sec_locales_tab_servicio_publico_form_modal_param_mes_facturado"];
		$param_mes_facturado = date("Y-m-d", strtotime($param_mes_facturado));
		$param_tipo_documento = $_POST['sec_locales_tab_servicio_publico_form_modal_param_tipo_documento'];
		$param_importe = str_replace(",","",$_POST["sec_locales_tab_servicio_publico_form_modal_param_importe"]);
		$param_comentario = $_POST['sec_locales_tab_servicio_publico_form_modal_param_comentario'];
		$param_nombre_recibo = $_POST['param_nombre_recibo'];
		$param_nombre_recibo_contrometro = $_POST['param_nombre_archivo_contometro'];

		if((int)$param_id == 0){
			
	
			//VALIDACION: EL LOCAL DEBE CONTAR CON UN CONTRATO 
			$query = 
				"
					SELECT contrato_id 
					FROM tbl_locales 
					WHERE id = '".$param_local_id."'
				";
			
			$list_query = $mysqli->query($query);
			$validar_contrato = false;
			while ($li = $list_query->fetch_assoc())
			{
				if (!Empty($li['contrato_id']))
				{
					$validar_contrato = true;
				}
			}
	
			if (!$validar_contrato)
			{
				$return["http_code"] = 400;
				$return["error"] ="El local seleccionado no cuenta con un contrato.";
				
				print_r(json_encode($return));
				exit();
			}
			//VALIDACION: VALIDAR UN RIGISTRO POR SERVICO EN UN PERIODO
			
			//INICIO: VALIDAR SI EXISTE REGISTROS EN EL MISMO MES QUE QUEREMOS REGISTRAR
			$anio = date("Y",strtotime($param_mes_facturado));
			$mes = date("m",strtotime($param_mes_facturado));
	
			$query = 
			"
				SELECT 
					sp.id, sp.id_tipo_servicio_publico, sp.inmueble_suministros_id, sp.periodo_consumo
				FROM cont_local_servicio_publico AS sp
				WHERE sp.status = 1 
					AND sp.id_tipo_servicio_publico = ".$param_tipo_servicio."
					AND sp.inmueble_suministros_id = ".$param_num_suministro_id."
					AND sp.id_local = ".$param_local_id."
					AND month(sp.periodo_consumo) = '" . $mes . "' AND year(sp.periodo_consumo) = '" . $anio."' 
			";
	
			$list_query = $mysqli->query($query);
			$recibos = 0;
			while ($li = $list_query->fetch_assoc())
			{
				$recibos++;
			}
	
			if ($recibos > 0)
			{
				$return["http_code"] = 400;
				$return["status"] = "¡Alerta!";
				$return["error"] ="Ya se encuentra registrado el servicio en el mes facturado.";
				
				print_r(json_encode($return));
				exit();			
			}
	
			//FIN: VALIDAR SI EXISTE REGISTROS EN EL MISMO MES QUE QUEREMOS REGISTRAR
	
	
			if($param_compromiso_pago_id == 4)
			{
				// VALIDAR EL ARCHIVO CONTOMETRO
				if(empty($_FILES['sec_locales_tab_servicio_publico_form_modal_param_archivo_contometro']))
				{
					$return["http_code"] = 400;
					$return["status"] = "Ocurrio un error.";
					$return["error"] = "Por favor seleccionar el archivo contometro.";
	
					print_r(json_encode($return));
					exit();
				}
			}
	
			if(isset($_FILES['sec_locales_tab_servicio_publico_form_modal_param_archivo']))
			{
				$servicio_publico_archivo = "sec_locales_tab_servicio_publico_form_modal_param_archivo";
				$archivo_file_name = $_FILES[$servicio_publico_archivo]['name'];
				$archivo_file_tmp = $_FILES[$servicio_publico_archivo]['tmp_name'];
				$archivo_file_size = $_FILES[$servicio_publico_archivo]['size'];
				$archivo_file_extension = strtolower(pathinfo($archivo_file_name, PATHINFO_EXTENSION));
				
				if($param_tipo_servicio == 1)
				{
					$nombre_tipo_servicio = "LUZ";
					$path = "/var/www/html/files_bucket/contratos/servicios_publicos/luz/";
					$download = "/files_bucket/contratos/servicios_publicos/luz/";
				}
				else
				{
					$nombre_tipo_servicio = "AGUA";
					$path = "/var/www/html/files_bucket/contratos/servicios_publicos/agua/";
					$download = "/files_bucket/contratos/servicios_publicos/agua/";
				}
				if (!is_dir($path)) 
				{
					mkdir($path, 0777, true);
				}
	
				$nombreFileUpload = "ID_".$param_local_id."_".$nombre_tipo_servicio."_".$param_mes_facturado."_FECHA_".date('YmdHis').".".$archivo_file_extension;
	
				$nombreDownload = $download.$nombreFileUpload;
				move_uploaded_file($archivo_file_tmp, $path. $nombreFileUpload);
	
	
				$query_insert = 
				"
					INSERT INTO cont_local_servicio_publico 
					(
						id_local, 
						id_tipo_servicio_publico, 
						inmueble_suministros_id, 
						periodo_consumo, 
						tipo_documento,
						monto_total,
						comentario,
						estado,
						status,
						user_created_id,
						created_at,
						user_updated_id,
						updated_at
					)
					VALUES 
					(
						'".$param_local_id."', 
						'".$param_tipo_servicio."', 
						'".$param_num_suministro_id."',
						'".$param_mes_facturado."',
						'".$param_tipo_documento."',
						'".$param_importe."',
						'".$param_comentario."',
						1,
						1,
						'".$login["id"]."', 
						'".date('Y-m-d H:i:s')."',
						'".$login["id"]."', 
						'".date('Y-m-d H:i:s')."'
					)
				";
	
				$mysqli->query($query_insert);
	
				if($mysqli->error)
				{
					$error .= $mysqli->error . $query_insert;
	
					$return["http_code"] = 400;
					$return["status"] = "Error.";
					$return["error"] = $error;
	
					print_r(json_encode($return));
					exit();
				}
	
				$id_cabecera = $mysqli->insert_id;
	
				$query_insert_file = 
				"
					INSERT INTO cont_local_servicio_publico_files 
					(
						cont_local_servicio_publico_id, 
						cont_tipo_servicio_publico_id, 
						file,
						extension,
						size,
						ruta,
						download,
						status,
						user_created_id,
						created_at,
						user_updated_id,
						updated_at
					)
					VALUES 
					(
						'".$id_cabecera."',
						'".$param_tipo_servicio."',
						'".$nombreFileUpload."',
						'".$archivo_file_extension."',
						'".$archivo_file_size."',
						'".$path."',
						'".$nombreDownload."',
						1,
						'".$login["id"]."',
						'".date('Y-m-d H:i:s')."',
						'".$login["id"]."',
						'".date('Y-m-d H:i:s')."'
					)
				";
	
				$mysqli->query($query_insert_file);
	
				if($mysqli->error)
				{
					$error .= $mysqli->error . $query_insert_file;
	
					$return["http_code"] = 400;
					$return["status"] = "Error.";
					$return["error"] = $error;
	
					print_r(json_encode($return));
					exit();
				}
	
				if($param_compromiso_pago_id == 4)
				{
					$servicio_publico_archivo_contometro = "sec_locales_tab_servicio_publico_form_modal_param_archivo_contometro";
					$archivo_file_name = $_FILES[$servicio_publico_archivo_contometro]['name'];
					$archivo_file_tmp = $_FILES[$servicio_publico_archivo_contometro]['tmp_name'];
					$archivo_file_size = $_FILES[$servicio_publico_archivo_contometro]['size'];
					$archivo_file_extension = strtolower(pathinfo($archivo_file_name, PATHINFO_EXTENSION));
					
					if($param_tipo_servicio == 1)
					{
						$nombre_tipo_servicio = "LUZ_CONTOMETRO";
						$path = "/var/www/html/files_bucket/contratos/servicios_publicos/luz/";
						$download = "/files_bucket/contratos/servicios_publicos/luz/";
					}
					else
					{
						$nombre_tipo_servicio = "AGUA_CONTOMETRO";
						$path = "/var/www/html/files_bucket/contratos/servicios_publicos/agua/";
						$download = "/files_bucket/contratos/servicios_publicos/agua/";
					}
					if (!is_dir($path)) 
					{
						mkdir($path, 0777, true);
					}
	
					$nombreFileUpload = "ID_".$param_local_id."_".$nombre_tipo_servicio."_".$param_mes_facturado."_FECHA_".date('YmdHis').".".$archivo_file_extension;
	
					$nombreDownload = $download.$nombreFileUpload;
					move_uploaded_file($archivo_file_tmp, $path. $nombreFileUpload);
	
					$query_insert_file_contometro = 
					"
						INSERT INTO cont_local_servicio_publico_files 
						(
							cont_local_servicio_publico_id, 
							cont_tipo_servicio_publico_id, 
							file,
							extension,
							size,
							ruta,
							download,
							status,
							user_created_id,
							created_at,
							user_updated_id,
							updated_at
						)
						VALUES 
						(
							'".$id_cabecera."',
							3,
							'".$nombreFileUpload."',
							'".$archivo_file_extension."',
							'".$archivo_file_size."',
							'".$path."',
							'".$nombreDownload."',
							1,
							'".$login["id"]."',
							'".date('Y-m-d H:i:s')."',
							'".$login["id"]."',
							'".date('Y-m-d H:i:s')."'
						)
					";
	
					$mysqli->query($query_insert_file_contometro);
	
					if($mysqli->error)
					{
						$error .= $mysqli->error . $query_insert_file_contometro;
	
						$return["http_code"] = 400;
						$return["status"] = "Error.";
						$return["error"] = $error;
	
						print_r(json_encode($return));
						exit();
					}
				}
	
				if ($error == '')
				{
					$return["http_code"] = 200;
					$return["status"] = "Datos registrados correctamente.";
					$return["error"] = $error;
	
					print_r(json_encode($return));
					exit();
				}
				else 
				{
					$return["http_code"] = 400;
					$return["status"] = "Error.";
					$return["error"] = $error;
	
					print_r(json_encode($return));
					exit();
				}
			}
			else
			{
				$return["http_code"] = 400;
				$return["status"] = "Ocurrio un error.";
				$return["error"] = "Por favor seleccionar un archivo.";
	
				print_r(json_encode($return));
				exit();
			}
		}
		else{
			//VALIDACION: EL LOCAL DEBE CONTAR CON UN CONTRATO 
			$query = 
				"
					SELECT contrato_id 
					FROM tbl_locales 
					WHERE id = '".$param_local_id."'
				";
			
			$list_query = $mysqli->query($query);
			$validar_contrato = false;
			while ($li = $list_query->fetch_assoc())
			{
				if (!Empty($li['contrato_id']))
				{
					$validar_contrato = true;
				}
			}
	
			if (!$validar_contrato)
			{
				$return["http_code"] = 400;
				$return["error"] ="El local seleccionado no cuenta con un contrato.";
				
				print_r(json_encode($return));
				exit();
			}
			//VALIDACION: VALIDAR UN RIGISTRO POR SERVICO EN UN PERIODO
			
			//INICIO: VALIDAR SI EXISTE REGISTROS EN EL MISMO MES QUE QUEREMOS REGISTRAR
			$anio = date("Y",strtotime($param_mes_facturado));
			$mes = date("m",strtotime($param_mes_facturado));
	
			$query = 
			"
				SELECT 
					sp.id, sp.id_tipo_servicio_publico, sp.inmueble_suministros_id, sp.periodo_consumo
				FROM cont_local_servicio_publico AS sp
				WHERE sp.status = 1 
					AND sp.id_tipo_servicio_publico = ".$param_tipo_servicio."
					AND sp.inmueble_suministros_id = ".$param_num_suministro_id."
					AND sp.id_local = ".$param_local_id."
					AND month(sp.periodo_consumo) = '" . $mes . "' AND year(sp.periodo_consumo) = '" . $anio."' 
					AND sp.id <> ".$param_id."
					
			";
	
			$list_query = $mysqli->query($query);
			$recibos = 0;
			while ($li = $list_query->fetch_assoc())
			{
				$recibos++;
			}
	
			if ($recibos > 0)
			{
				$return["http_code"] = 400;
				$return["status"] = "¡Alerta!";
				$return["error"] ="Ya se encuentra registrado el servicio en el mes facturado.";
				
				print_r(json_encode($return));
				exit();			
			}
	
			//FIN: VALIDAR SI EXISTE REGISTROS EN EL MISMO MES QUE QUEREMOS REGISTRAR
	
	
			if($param_compromiso_pago_id == 4)
			{
				// VALIDAR EL ARCHIVO CONTOMETRO
				if(empty($_FILES['sec_locales_tab_servicio_publico_form_modal_param_archivo_contometro']))
				{
					$return["http_code"] = 400;
					$return["status"] = "Ocurrio un error.";
					$return["error"] = "Por favor seleccionar el archivo contometro.";
	
					print_r(json_encode($return));
					exit();
				}
			}
				
				
				if($param_tipo_servicio == 1)
				{
					$nombre_tipo_servicio = "LUZ";
					$path = "/var/www/html/files_bucket/contratos/servicios_publicos/luz/";
					$download = "/files_bucket/contratos/servicios_publicos/luz/";
				}
				else
				{
					$nombre_tipo_servicio = "AGUA";
					$path = "/var/www/html/files_bucket/contratos/servicios_publicos/agua/";
					$download = "/files_bucket/contratos/servicios_publicos/agua/";
				}
				if (!is_dir($path)) 
				{
					mkdir($path, 0777, true);
				}

				// Consultar si se registrara un nuevo archivo
				$selectQuery = "SELECT sf.id, sf.file,sf.cont_tipo_servicio_publico_id
								FROM cont_local_servicio_publico AS sp
								INNER JOIN cont_local_servicio_publico_files sf
								ON sf.cont_local_servicio_publico_id = sp.id AND sf.cont_tipo_servicio_publico_id = sp.id_tipo_servicio_publico
								WHERE sp.id = ? AND sp.status = 1 ORDER BY sp.id DESC LIMIT 1";

				$selectStmt = $mysqli->prepare($selectQuery);
				$selectStmt->bind_param("i", $param_id);
				$selectStmt->execute();
				$selectStmt->store_result();

				if ($selectStmt->num_rows > 0) {
					$selectStmt->bind_result($archivo_id, $archivo_nombre, $servicio_publico_id);
					$selectStmt->fetch();
				}
				// 
				$query_update = 
				"
					UPDATE cont_local_servicio_publico
					SET
						id_local = '".$param_local_id."',
						id_tipo_servicio_publico = '".$param_tipo_servicio."',
						inmueble_suministros_id = '".$param_num_suministro_id."',
						periodo_consumo = '".$param_mes_facturado."',
						tipo_documento = '".$param_tipo_documento."',
						monto_total = '".$param_importe."',
						comentario = '".$param_comentario."',
						user_updated_id = '".$login["id"]."',
						updated_at = '".date('Y-m-d H:i:s')."'
					WHERE id= '".$param_id."'
				";
	
				$mysqli->query($query_update);
	
				if($mysqli->error)
				{
					$error .= $mysqli->error . $query_update;
	
					$return["http_code"] = 400;
					$return["status"] = "Error.";
					$return["error"] = $error;
	
					print_r(json_encode($return));
					exit();
				}
	
				//$id_cabecera = $mysqli->insert_id;
				
				if((string)$archivo_nombre != (string)$param_nombre_recibo /*|| (string)$servicio_publico_id != (string)$param_tipo_servicio*/){

					if(isset($_FILES['sec_locales_tab_servicio_publico_form_modal_param_archivo']))
					{

						$servicio_publico_archivo = "sec_locales_tab_servicio_publico_form_modal_param_archivo";
						$archivo_file_name = $_FILES[$servicio_publico_archivo]['name'];
						$archivo_file_tmp = $_FILES[$servicio_publico_archivo]['tmp_name'];
						$archivo_file_size = $_FILES[$servicio_publico_archivo]['size'];
						$archivo_file_extension = strtolower(pathinfo($archivo_file_name, PATHINFO_EXTENSION));
						
						$nombreFileUpload = "ID_".$param_local_id."_".$nombre_tipo_servicio."_".$param_mes_facturado."_FECHA_".date('YmdHis').".".$archivo_file_extension;
			
						$nombreDownload = $download.$nombreFileUpload;
						move_uploaded_file($archivo_file_tmp, $path. $nombreFileUpload);
		

						$query_update_file = "
							UPDATE cont_local_servicio_publico_files
							SET
								cont_tipo_servicio_publico_id = '".$param_tipo_servicio."',
								file = '".$nombreFileUpload."',
								extension = '".$archivo_file_extension."',
								size = '".$archivo_file_size."',
								ruta = '".$path."',
								download = '".$nombreDownload."',
								user_updated_id = '".$login["id"]."',
								updated_at = '".date('Y-m-d H:i:s')."'
							WHERE id = '".$archivo_id."'
						";

			
						$mysqli->query($query_update_file);
			
						if($mysqli->error)
						{
							$error .= $mysqli->error . $query_update_file;
			
							$return["http_code"] = 400;
							$return["status"] = "Error.";
							$return["error"] = $error;
			
							print_r(json_encode($return));
							exit();
						}
				
					}
					else
					{
						$return["http_code"] = 400;
						$return["status"] = "Ocurrio un error.";
						$return["error"] = "Por favor seleccionar un archivo.";
			
						print_r(json_encode($return));
						exit();
					}

				}
	
				if($param_compromiso_pago_id == 4)
				{
					// Consultar si se registrara un nuevo archivo contometro
					$selectQueryContometro = "SELECT sf.id, sf.file
										FROM cont_local_servicio_publico AS sp
										LEFT JOIN cont_local_servicio_publico_files sf
										ON sf.cont_local_servicio_publico_id = sp.id AND sf.cont_tipo_servicio_publico_id = 3
										WHERE sp.id = ? AND sp.status = 1 ORDER BY sp.id DESC LIMIT 1";

					$selectStmt = $mysqli->prepare($selectQueryContometro);
					$selectStmt->bind_param("i", $param_id);
					$selectStmt->execute();
					$selectStmt->store_result();

					if ($selectStmt->num_rows > 0) {
							$selectStmt->bind_result($archivo_id_contometro,$archivo_nombre_contometro);
							$selectStmt->fetch();
						}
				
					if((string)$$param_nombre_recibo_contrometro != (string)$archivo_nombre_contometro){
						$servicio_publico_archivo_contometro = "sec_locales_tab_servicio_publico_form_modal_param_archivo_contometro";
						$archivo_file_name = $_FILES[$servicio_publico_archivo_contometro]['name'];
						$archivo_file_tmp = $_FILES[$servicio_publico_archivo_contometro]['tmp_name'];
						$archivo_file_size = $_FILES[$servicio_publico_archivo_contometro]['size'];
						$archivo_file_extension = strtolower(pathinfo($archivo_file_name, PATHINFO_EXTENSION));
						
						if($param_tipo_servicio == 1)
						{
							$nombre_tipo_servicio = "LUZ_CONTOMETRO";
							$path = "/var/www/html/files_bucket/contratos/servicios_publicos/luz/";
							$download = "/files_bucket/contratos/servicios_publicos/luz/";
						}
						else
						{
							$nombre_tipo_servicio = "AGUA_CONTOMETRO";
							$path = "/var/www/html/files_bucket/contratos/servicios_publicos/agua/";
							$download = "/files_bucket/contratos/servicios_publicos/agua/";
						}
						if (!is_dir($path)) 
						{
							mkdir($path, 0777, true);
						}
		
						$nombreFileUpload = "ID_".$param_local_id."_".$nombre_tipo_servicio."_".$param_mes_facturado."_FECHA_".date('YmdHis').".".$archivo_file_extension;

						$nombreDownload = $download.$nombreFileUpload;
						move_uploaded_file($archivo_file_tmp, $path. $nombreFileUpload);
		
						$query_update_file_contometro = "
							UPDATE cont_local_servicio_publico_files
							SET
								cont_tipo_servicio_publico_id = 3,
								file = '".$nombreFileUpload."',
								extension = '".$archivo_file_extension."',
								size = '".$archivo_file_size."',
								ruta = '".$path."',
								download = '".$nombreDownload."',
								user_updated_id = '".$login["id"]."',
								updated_at = '".date('Y-m-d H:i:s')."'
							WHERE id = '".$archivo_id_contometro."'
						";
		
						$mysqli->query($query_update_file_contometro);
		
						if($mysqli->error)
						{
							$error .= $mysqli->error . $query_update_file_contometro;
		
							$return["http_code"] = 400;
							$return["status"] = "Error.";
							$return["error"] = $error;
		
							print_r(json_encode($return));
							exit();
						}
					}
				}
	
				if ($error == '')
				{
					$return["http_code"] = 200;
					$return["status"] = "Datos registrados correctamente.";
					$return["error"] = $error;
	
					print_r(json_encode($return));
					exit();
				}
				else 
				{
					$return["http_code"] = 400;
					$return["status"] = "Error.";
					$return["error"] = $error;
	
					print_r(json_encode($return));
					exit();
				}
		}
		
	}
	else
	{
		$return["http_code"] = 400;
        $return["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	print_r(json_encode($return));
	exit();
}

if (isset($_POST["post_locales_servicio_publico"])) 
{
	$message = "";
	$status = true;
	$nombre_tipo_servicio = "";
	$nombre_servicio = "";

	$txt_id_local = $_POST["txt_id_local"];
	$txt_nombre_tienda = $_POST["txt_nombre_tienda"];



	$txt_locales_tipo_servicio_publico = $_POST["txt_locales_tipo_servicio_publico"];

	if($txt_locales_tipo_servicio_publico == 1)
	{
		$nombre_tipo_servicio = "LUZ";
		$path = "/var/www/html/files_bucket/contratos/servicios_publicos/luz/";
		$nombre_servicio = "luz";
	}
	else
	{
		$nombre_tipo_servicio = "AGUA";
		$path = "/var/www/html/files_bucket/contratos/servicios_publicos/agua/";
		$nombre_servicio = "agua";
	}

	$txt_locales_servicio_publico_fecha_emision = $_POST["txt_locales_servicio_publico_fecha_emision"];
	$txt_locales_servicio_publico_fecha_emision = date("Y-m-d", strtotime($txt_locales_servicio_publico_fecha_emision));

	$txt_locales_servicio_publico_fecha_vencimiento = $_POST["txt_locales_servicio_publico_fecha_vencimiento"];
	$txt_locales_servicio_publico_fecha_vencimiento = date("Y-m-d", strtotime($txt_locales_servicio_publico_fecha_vencimiento));

	if (!Empty($_POST["txt_locales_servicio_publico_monto_total"])) {
		$txt_locales_servicio_publico_monto_total = $_POST["txt_locales_servicio_publico_monto_total"];
		$txt_locales_servicio_publico_monto_total = str_replace(",","",$_POST["txt_locales_servicio_publico_monto_total"]);
	}else{
		$txt_locales_servicio_publico_monto_total = 0;
	}



	$txt_nombre_file_mes_consumo = $_POST["txt_locales_servicio_publico_periodo_consumo"];

	$txt_locales_servicio_publico_periodo_consumo = $_POST["txt_locales_servicio_publico_periodo_consumo"];
	$txt_locales_servicio_publico_periodo_consumo = date("Y-m", strtotime($txt_locales_servicio_publico_periodo_consumo));
	$txt_locales_servicio_publico_periodo_consumo = $txt_locales_servicio_publico_periodo_consumo."-01";

	$txt_locales_servicio_publico_comentario = $_POST["txt_locales_servicio_publico_comentario"];

	//VALIDACION: EL LOCAL DEBE CONTAR CON UN CONTRATO 
	$query = "SELECT contrato_id FROM tbl_locales WHERE id = ".$txt_id_local;
	
	$list_query = $mysqli->query($query);
	$validar_contrato = false;
	while ($li = $list_query->fetch_assoc()) {
		if (!Empty($li['contrato_id'])) {
			$validar_contrato = true;
		}
	}

	if (!$validar_contrato) {
		$message = "El local seleccionado no cuenta con un contrato";
		$status = false;

		$return["status"] = $status;
		$return["message"] = $message;
		print_r(json_encode($return));
		exit();
	}

	//VALIDACION: VALIDAR UN RIGISTRO POR SERVICO EN UN PERIODO
	$anio = date("Y",strtotime($txt_locales_servicio_publico_periodo_consumo));
	$mes = date("m",strtotime($txt_locales_servicio_publico_periodo_consumo));
	$query = "SELECT sp.id, sp.id_tipo_servicio_publico, sp.periodo_consumo
		FROM cont_local_servicio_publico AS sp
		WHERE sp.status = 1
		AND sp.id_tipo_servicio_publico = ".$txt_locales_tipo_servicio_publico."
		AND sp.id_local = ".$txt_id_local."
		AND month(sp.periodo_consumo) = '" . $mes . "' and year(sp.periodo_consumo) = '" . $anio."'";

	$list_query = $mysqli->query($query);
	$recibos = 0;
	while ($li = $list_query->fetch_assoc()) {
		$recibos++;
	}

	if ($recibos > 0) {		
		$message = "Ya se encuentra registrado el servicio en el periodo seleccionado";
		$status = false;

		$return["status"] = $status;
		$return["message"] = $message;
		print_r(json_encode($return));
		exit();
	}

	if(!empty($_FILES['fileLocalServicioPublico']['name']))
	{
		$fileServicioPublico = $_FILES['fileLocalServicioPublico']['name'];
		$tmpServicioPublico = $_FILES['fileLocalServicioPublico']['tmp_name'];
		$sizeServicioPublico = $_FILES['fileLocalServicioPublico']['size'];
		$extServicioPublico = strtolower(pathinfo($fileServicioPublico, PATHINFO_EXTENSION));

		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');

		if(in_array($extServicioPublico, $valid_extensions))
		{
			//$path = "/var/www/html/contratos/servicios_publicos/luz/";
			$download = "/files_bucket/contratos/servicios_publicos/".$nombre_servicio."/";

			if (!is_dir($path)) 
			{
				mkdir($path, 0777, true);
			}

			$nombreFileUpload = $txt_nombre_tienda."_".$nombre_tipo_servicio."_".$txt_nombre_file_mes_consumo.".".$extServicioPublico;
			$nombreDownload = $download.$nombreFileUpload;
			move_uploaded_file($tmpServicioPublico, $path. $nombreFileUpload);

			$query_insert = "INSERT INTO cont_local_servicio_publico 
								(
									id_local, 
									id_tipo_servicio_publico, 
									fecha_emision, 
									fecha_vencimiento, 
									periodo_consumo, 
									comentario, 
									monto_total,
									nombre_file, 
									extension, 
									size, 
									ruta_upload, 
									ruta_download_file, 
									estado,
									status,
									user_created_id,
									created_at, 
									user_updated_id, 
									updated_at
                                 )
							VALUES 
								(
									'".$txt_id_local."', 
									'".$txt_locales_tipo_servicio_publico."', 
									'".$txt_locales_servicio_publico_fecha_emision."',
									'".$txt_locales_servicio_publico_fecha_vencimiento."',
									'".$txt_locales_servicio_publico_periodo_consumo."',
									'".$txt_locales_servicio_publico_comentario."',
									'".$txt_locales_servicio_publico_monto_total."',
									'".$nombreFileUpload."',
									'".$extServicioPublico."',
									'".$sizeServicioPublico."',
									'".$path."',
									'".$nombreDownload."',
									1,
									1,
									'".$login["id"]."', 
									'".date('Y-m-d')."', 
									'".$login["id"]."', 
									'".date('Y-m-d')."'
								)";

			$mysqli->query($query_insert);

			if($mysqli->error)
			{
				$status = false;
				$message = $mysqli->error;
			}
			else
			{
				$message = "Datos guardados correctamente";
				$status = true;
			}

		}
		else
		{
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	}
	else
	{
		$message = "Tiene que seleccionar un archivo";
		$status = false;

	}


	/*echo json_encode([
		'status' => $status,
		'message' => $message
	]);*/

	$return["status"] = $status;
	$return["message"] = $message;

}

if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_locales_contabilidad_reporte")
{
	$param_tipo_servicio = $_POST['param_tipo_servicio'];
	$param_local_id = $_POST['param_local_id'];

	$query = 
	"
		SELECT
			s.id, 
		    s.id_tipo_servicio_publico, 
		    ts.nombre AS tipo_servicio_publico,
		    cis.nro_suministro,
			s.fecha_emision, 
		    s.fecha_vencimiento, 
		    s.monto_total, 
			CONCAT(YEAR(s.periodo_consumo),'-',MONTH(s.periodo_consumo)) AS mes_facturado,
			s.comentario, 
		    sf.file, sf.extension, sf.download, 
		    s.estado,
		    e.nombre as nombre_estado,
		    s.total_pagar
			-- e.situacion as situacion_contabilidad
		FROM cont_local_servicio_publico as s
			INNER JOIN cont_inmueble_suministros AS cis
			ON s.inmueble_suministros_id = cis.id
			INNER JOIN cont_tipo_servicio_publico AS ts
		    ON ts.id = s.id_tipo_servicio_publico
		    INNER JOIN cont_tipo_estado_servicio_publico as e 
		    ON e.id = s.estado
		    INNER JOIN cont_local_servicio_publico_files AS sf
		    ON sf.cont_local_servicio_publico_id = s.id AND sf.cont_tipo_servicio_publico_id = s.id_tipo_servicio_publico
		WHERE s.status = 1 
			AND s.id_tipo_servicio_publico = '".$param_tipo_servicio."'
			AND s.id_local = '".$param_local_id."'
	";

    $list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$opciones = '<div class="text-center">';

		$opciones .= '
				<button class="btn btn-rounded text-center btn-primary btn-sm" title="Ver documento"
						onclick="verFileServicioPublicoEnVisor(\'' . $reg->extension . '\', \'' . $reg->download . '\')">
						<i class="fa fa-eye"></i>
				</button>';

		if ($reg->estado != 3) {
			$opciones .= '
				<button type="button" class="btn text-center btn-danger btn-sm" title="Alerta configurada" 
						onclick="EliminarReciboServicioPublicoLocales(' . $reg->id . ')">
						<i class="glyphicon glyphicon-trash"></i>
				</button>';
		} 
		if ($reg->estado == 6) {
			$opciones .= '
				<button type="button" class="btn text-center btn-warning btn-sm" title="Editar recibo" 
						onclick="EditarReciboServicioPublicoLocales(' . $reg->id . ')">
						<i class="glyphicon glyphicon-pencil"></i>
				</button>';
			
		}
		$opciones .= '</div>';


		$data[] = array(
			"0" => $reg->id,
			"1" => NombreMes($reg->mes_facturado),
			"2" => $reg->tipo_servicio_publico.' - '.$reg->nro_suministro,
			"3" => $reg->fecha_emision,
			"4" => $reg->fecha_vencimiento,
			"5" => 'S/ '.$reg->monto_total,
			"6" => $reg->nombre_estado,
			"7" => ($reg->estado == 3) 
					? 
					'S/ '.$reg->total_pagar
					.' <button class="btn btn-rounded text-center btn-info btn-sm" 
						title="Ver pago"
						onclick="sec_locales_servicio_publico_modal_voucher_pago('.$reg->id.')">
						<i class="fa fa-handshake-o"></i>
					</button>'
					: 
					'',
			"8" => $opciones
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	//echo json_encode($resultado);
	$return["resultado"] = $resultado;


}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_locales_servicio_publico_modal_voucher_pago")
{

	$recibo_id = $_POST["recibo_id"];
	
	$html = '';
	$tbody = '';
	

	$query = 
	"
		SELECT 
			pd.id, pd.cont_ser_pub_programacion_id,
		    pd.cont_local_servicio_publico_id,
		    sp.id AS id_recibo,
		    IFNULL(pf.download, '') ruta_download_file,
			IFNULL(pf.imagen, '') nombre_file,
			IFNULL(pf.extension, '') extension
		FROM cont_ser_pub_programacion_detalle pd
			INNER JOIN cont_local_servicio_publico sp
			ON pd.cont_local_servicio_publico_id = sp.id
			INNER JOIN cont_ser_pub_programacion_files pf
			ON pd.cont_ser_pub_programacion_id = pf.cont_ser_pub_programacion_id
			WHERE sp.id = '".$recibo_id."'
	";

	$list_query = $mysqli->query($query);

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query;
		echo json_encode($result);
		exit();
	}

	$row_count = $list_query->num_rows;

	if($row_count > 0) 
	{
		$num = 1;

		while ($row = $list_query->fetch_assoc()) 
		{
			$ruta_download_file = $row["ruta_download_file"];
			$nombre_file = $row["nombre_file"];
			$extension = $row["extension"];

			$i = 1;
			if($extension == "pdf")
			{
				$tbody .= '<div class="row" style="padding-bottom: 5px;">
                                    
                            <div class="row"
                                style="margin-bottom: 20px;" 
                                id="sec_con_serv_pub_div_imagen_recibo_'.$i.'">
                                <iframe src="'.$ruta_download_file.'" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>
                            </div>

                        </div>';
				
			}
			else
			{
				$tbody .= '<div class="row" style="padding-bottom: 3px;">
                                    
                            <div class="col-xs-4 col-md-4 col-sm-4"
                                style="margin-bottom: 20px;" 
                                id="sec_con_serv_pub_div_imagen_recibo_'.$i.'">
                                <div class="col-md-12">
									<div align="center" style="height: 100%; width: 100%;">
										<img  id="" src="'.$ruta_download_file.'" width="100%" height="100%" />
									</div>
								</div>
                            </div>
                            
                            <div class="col-xs-4 col-md-4 col-sm-4"
                                style="text-align: center; margin-bottom: 5px;" 
                                id="sec_con_serv_pub_div_VerImagenFullPantalla">
                                
                                <button 
                                    type="button" 
                                    class="btn btn-block btn-info btn-sm" 
                                    id="sec_con_serv_pub_ver_full_pantalla"
                                    onclick="verFileServicioPublicoEnVisor(\''.$extension.'\', \''.$ruta_download_file.'\')"
                                    style="margin-top: 13%">
                                    <i class="fa fa-arrows-alt"></i>  Pantalla Completa
                                </button>
                            </div>

                            <div class="col-xs-4 col-md-4 col-sm-4"
                                style="text-align: center; margin-bottom: 5px;" 
                                id="sec_con_serv_pub_btn_descargar_imagen_recibo">

                                <a id="sec_con_serv_pub_descargar_imagen_a" 
                                    class="btn btn-block btn-success btn-sm"
                                    onclick="sec_locales_btn_descargar(\''.$ruta_download_file.'\')";
                                    style="margin-top: 13%">
                                    <i class="fa fa-arrow-circle-down"></i> Descargar
                                </a>
                            </div>
                            
                        </div>';
			}

			$i++;
		}

		$html .= $tbody;
	}
	else if ($row_count == 0)
	{
		$tbody .= '<div class="row" style="background: yellow; padding-bottom: 5px;">
                                    
                        <div class="col-xs-12 col-md-12 col-sm-12"
                            style="margin-bottom: 20px; background: red;" 
                            id="">
                            NO EXISTEN REGISTROS
                        </div>
                        
                    </div>';
	}

	if ($row_count >= 0) 
	{
		$return["http_code"] = 200;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["result"] = $html;
	}
	else 
	{
		$return["http_code"] = 400;
		$return["result"] = "No hay registros.";
	}

	print_r(json_encode($return));
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="cont_eliminar_recibo_servicio_publico")
{
	$message = "";

	$txt_id_recibo_local_servicio_publico = $_POST['txt_id_recibo_local_servicio_publico'];

	$sel_query = $mysqli->query("SELECT 
									sp.id,
									sp.estado,
									sp.id_tipo_servicio_publico
								FROM cont_local_servicio_publico AS sp 
								WHERE sp.id = ".$txt_id_recibo_local_servicio_publico);
	$estado = "";
	
	while($sel = $sel_query->fetch_assoc())
	{
		$estado = $sel["estado"];
	}

	if ($estado == 2 || $estado == 3)
	{
		$status = false;
		$message = "No se puede eliminar el recibo, porque ya se encuentra atendido";
		$return["status"] = $status;
		$return["message"] = $message;
		$return["memory_end"]=memory_get_usage();
		$return["time_end"] = microtime(true);
		$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
		$return["time_total"]=($return["time_end"]-$return["time_init"]);
		print_r(json_encode($return));
		exit();
	}


	$query = "DELETE FROM cont_local_servicio_publico WHERE id = '".$txt_id_recibo_local_servicio_publico."'";

	$mysqli->query($query);

	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;

		$return["status"] = $status;
		$return["message"] = $message;

		print_r(json_encode($return));
		exit();
	}

	$query_delete_file = 
	"
		DELETE FROM cont_local_servicio_publico_files 
		WHERE cont_local_servicio_publico_id = '".$txt_id_recibo_local_servicio_publico."'";

	$mysqli->query($query_delete_file);

	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;

		$return["status"] = $status;
		$return["message"] = $message;

		print_r(json_encode($return));
		exit();
	}
	else
	{
		$message = "Recibo eliminado";
		$status = true;

		$return["status"] = $status;
		$return["message"] = $message;
	}

}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_address_mac_device_local") {
	$id_local = $_POST["id_local"];
	$macAddress = $_POST["macAddress"];
	$id_user = $login["id"];
	$error = '';

	$return=array();
	$query = "INSERT INTO tbl_televentas_local_devices 
				(local_id, macAddress, status, created_id_user, created_at) 
				VALUES (" . $id_local . ",'" . $macAddress . "', 1, " . $id_user . ", now())";

	$mysqli->query($query);

	if($mysqli->error){
		$error .= 'Error al guardar la dirección MAC: ' . $mysqli->error . $query;
	}
	if ($error == '') {
		$return["http_code"] = 200;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["error"] = $error;
	} else {
		$return["http_code"] = 400;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["error"] = $error;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="editar_address_mac_device_local") {
	$macAddress = $_POST["macAddress"];
	$idMac = $_POST["idMac"];
	$estado = $_POST["estado"];
	$id_user = $login["id"];
	$error = '';

	$return=array();
	$query = "UPDATE tbl_televentas_local_devices 
				SET macAddress = '" . $macAddress . "', 
				updated_id_user = " . $id_user
				. ", updated_at = now(), status = " . $estado 
				. " WHERE id = " . $idMac;

	$mysqli->query($query);

	if($mysqli->error){
		$error .= 'Error al editar la dirección MAC: ' . $mysqli->error . $query;
	}
	if ($error == '') {
		$return["http_code"] = 200;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["error"] = $error;
	} else {
		$return["http_code"] = 400;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["error"] = $error;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_address_mac_device_local") {
	$idMac = $_POST["idMac"];
	$id_user = $login["id"];
	$error = '';

	$return=array();
	$query = "UPDATE tbl_televentas_local_devices 
				SET updated_id_user = " . $id_user
				. ", updated_at = now(), status = 0 "
				. " WHERE id = " . $idMac;

	$mysqli->query($query);

	if($mysqli->error){
		$error .= 'Error al eliminar la dirección MAC: ' . $mysqli->error . $query;
	}
	if ($error == '') {
		$return["http_code"] = 200;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["error"] = $error;
	} else {
		$return["http_code"] = 400;
		$return["status"] = "Datos obtenidos de gestion.";
		$return["error"] = $error;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="actualizar_permiso_para_crear_locales_red_at") {
	$user_id = $login?$login['id']:null;

	if ((int) $user_id > 0) {
		$permiso_para_crear_locales_red_at = $_POST["permiso_para_crear_locales_red_at"];
		$error = '';

		$query = "
		UPDATE tbl_otros_permisos 
		SET 
			permiso = $permiso_para_crear_locales_red_at,
			user_updated_id = $user_id, 
			updated_at = now()
		WHERE 
			modulo = 'locales' 
			AND accion = 'crear_locales_red_at'
		";

		$mysqli->query($query);

		if($mysqli->error){
			$error .= 'Error al actualizar el permiso para crear locales Red AT: ' . $mysqli->error . $query;
		} else {
			$permiso_si_o_no = 'SI';

			if ($permiso_para_crear_locales_red_at == "0") {
				$permiso_si_o_no = 'NO';
			}
		}

		if ($error == '') {
			$return["http_code"] = 200;
			$return["status"] = "Datos obtenidos de gestion.";
			$return["msg"] = 'Los JC y supervisores ' . $permiso_si_o_no . ' pueden crear locales Red AT.';
		} else {
			$return["http_code"] = 400;
			$return["status"] = "Datos obtenidos de gestion.";
			$return["error"] = $error;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Clic en el boton F5, iniciar sesión y volver a repetir la operación";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="list_detalle_locales_excel")
{
	$row_count_detalle_locales = 0;
	$query_detalle_locales = 
	"
		SELECT l.id                                               
		, l.cc_id
		, IF(TRIM(l.latitud) = '' OR l.latitud IS NULL OR TRIM(l.longitud) = '' OR l.longitud IS NULL, NULL, CONCAT(TRIM(l.latitud), ',', TRIM(l.longitud))) AS coordenadas
		, l.nombre
		, l.direccion
		, l.descripcion
		, l.email
		, l.phone
		, l.ubigeo_id
		, CASE WHEN l.trastienda = 1 THEN 'Si' ELSE 'No' END as trastienda
		,l.zona_id
		,z.nombre AS zona
		,IF(
			TRIM(l.latitud) <> '' AND TRIM(l.longitud) <> '',
			CONCAT('https://www.google.com/maps?q=', TRIM(l.latitud), ',', TRIM(l.longitud)),
			NULL
			) AS enlace_mapa 
		, lpr.proveedor_id AS num_terminales_kasnet
		,IFNULL(e.num_tv_apuestas_virtuales,0) as num_tv_apuestas_virtuales
		,IFNULL(e.num_tv_apuestas_deportivas,0) as num_tv_apuestas_deportivas
		,IFNULL(c.num_cpu,0) as num_cpu
		,IFNULL(c.num_monitores,0) as num_monitores
		,IFNULL(c.num_autoservicios,0) as num_autoservicios
		,IFNULL(c.num_allinone,0) as num_allinone
		,IFNULL(c.num_terminales_hibrido,0) as num_terminales_hibrido
		,IFNULL(c.num_terminales_antiguo,0) as num_terminales_antiguo
		,s.internet_proveedor_id
		,p.nombre AS internet_proveedor_nombre
		,s.internet_tipo_id
		,t.nombre AS internet_tipo_nombre
		,IFNULL(s.num_decos_internet,0) as num_decos_internet
		,IFNULL(s.num_decos_directv,0) as num_decos_directv
		,MAX(DISTINCT CASE WHEN wd.id = 1 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS monday
        ,MAX(DISTINCT CASE WHEN wd.id = 2 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS tuesday
        ,MAX(DISTINCT CASE WHEN wd.id = 3 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS wednesday
        ,MAX(DISTINCT CASE WHEN wd.id = 4 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS thursday
        ,MAX(DISTINCT CASE WHEN wd.id = 5 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS friday
        ,MAX(DISTINCT CASE WHEN wd.id = 6 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS saturday
        ,MAX(DISTINCT CASE WHEN wd.id = 0 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS sunday
		,IFNULL(lc.conteo_cajas, 0) as conteo_cajas
		,g.nombre_subgerente
		,jc.jefe_comercial
		,jc.telefono as js_telefono
		,CASE l.estado
				WHEN 1 THEN 'Operativo'
				WHEN 0 THEN 'No operativo'
				ELSE 'Cerrado'
			END AS estado
		,l.fecha_inicio_operacion
	FROM tbl_locales l
	LEFT JOIN (
		SELECT zona_id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_subgerente FROM tbl_personal_apt WHERE cargo_id=29
		) AS g ON g.zona_id = l.zona_id 
	LEFT JOIN (
		SELECT zona_id, telefono, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS jefe_comercial FROM tbl_personal_apt WHERE cargo_id=16
		) AS jc ON jc.zona_id = l.zona_id
	LEFT JOIN (SELECT local_id, proveedor_id
		FROM tbl_local_proveedor_id
		WHERE canal_de_venta_id = 28 AND estado = 1
		GROUP BY local_id) lpr ON l.id = lpr.local_id 
	LEFT JOIN tbl_zonas z ON (z.id = l.zona_id)
	LEFT JOIN tbl_locales_equipos e ON l.id = e.local_id
	LEFT JOIN tbl_locales_equipos_computo c ON l.id = c.local_id
	LEFT JOIN tbl_locales_servicios s ON l.id = s.local_id
	LEFT JOIN tbl_internet_proveedor p ON s.internet_proveedor_id = p.id
	LEFT JOIN tbl_internet_tipo t ON s.internet_tipo_id = t.id
	LEFT JOIN (SELECT local_id, MAX(started_at) AS started_at 
		FROM tbl_locales_horarios 
		GROUP BY local_id) AS lh_max ON lh_max.local_id = l.id
	LEFT JOIN tbl_locales_horarios AS lh ON lh.local_id = l.id AND lh.started_at = lh_max.started_at
	LEFT JOIN tbl_horarios AS h ON h.id = lh.horario_id
	LEFT JOIN tbl_horarios_dias AS hd ON hd.horario_id = h.id
	LEFT JOIN tbl_weekdays AS wd ON wd.id = hd.weekday_id
	LEFT JOIN (SELECT local_id, COUNT(id) AS conteo_cajas 
		FROM tbl_local_cajas 
		GROUP BY local_id) AS lc ON lc.local_id = l.id
	WHERE l.id <> 1367 AND l.id <> 1198 AND l.id <> 1197 AND l.id <> 1368 AND l.id <> 1199 AND l.id <> 1532 AND l.id <> 1 AND l.id <> 786 AND l.id <> 781 AND l.id <> 784 AND l.id <> 785 AND l.id <> 780 AND l.id <> 782 AND l.id <> 787 AND l.id <> 783        
	GROUP BY l.id
	ORDER BY l.nombre ASC
	";
	$ubigeos_arr = array();
	$ubigeos_query = $mysqli->query("SELECT id,cod_depa,cod_prov,cod_dist,nombre FROM tbl_ubigeo");
	while($ubg=$ubigeos_query->fetch_assoc()){
		$ubg_id=$ubg["cod_depa"].$ubg["cod_prov"].$ubg["cod_dist"];
		$ubigeos_arr[$ubg_id]=$ubg["nombre"];
	}

	$list_query_locales = $mysqli->query($query_detalle_locales);

	$row_count_detalle_locales = $list_query_locales->num_rows;

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Lista Detallada de locales";

					$list_cols["zona"]="Zona";
	$titulosColumnas = array('CC', 'Nombre', 'Departamento', 'Provincia', 'Distrito', 'Dirección', 'Ubicación G. Maps', 'Proveedor de Internet', 'Tipo de Internet',  '# DECOS MOVISTAR', '# DECOS DIRECTV', '# de CPU', '# de Monitores', '# de terminal KASNET','Trastienda', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado','Domingo', '# de cajas operativas', '# de autoservicios', '# de AIO', '# de terminales híbrido', '# de terminales antiguos', '# de televisores virtuales', '# de televisores apuestas deportivas', 'Subgerencia', 'Zona', 'Jefe Comercial', 'Celular del jefe comercial', 'Celular de la tienda', 'Correo de la tienda', 'Status diario', 'Fecha de apertura');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
			    ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
			    ->setCellValue('B1', $titulosColumnas[1])
			    ->setCellValue('C1', $titulosColumnas[2])
			    ->setCellValue('D1', $titulosColumnas[3])
			    ->setCellValue('E1', $titulosColumnas[4])
			    ->setCellValue('F1', $titulosColumnas[5])
			    ->setCellValue('G1', $titulosColumnas[6])
			    ->setCellValue('H1', $titulosColumnas[7])
			    ->setCellValue('I1', $titulosColumnas[8])
			    ->setCellValue('J1', $titulosColumnas[9])
			    ->setCellValue('K1', $titulosColumnas[10])
			    ->setCellValue('L1', $titulosColumnas[11])
			    ->setCellValue('M1', $titulosColumnas[12])
			    ->setCellValue('N1', $titulosColumnas[13])
			    ->setCellValue('O1', $titulosColumnas[14])
			    ->setCellValue('P1', $titulosColumnas[15])
			    ->setCellValue('Q1', $titulosColumnas[16])
			    ->setCellValue('R1', $titulosColumnas[17])
			    ->setCellValue('S1', $titulosColumnas[18])
			    ->setCellValue('T1', $titulosColumnas[19])
			    ->setCellValue('U1', $titulosColumnas[20])
			    ->setCellValue('V1', $titulosColumnas[21])
			    ->setCellValue('W1', $titulosColumnas[22])
			    ->setCellValue('X1', $titulosColumnas[23])
			    ->setCellValue('Y1', $titulosColumnas[24])
			    ->setCellValue('Z1', $titulosColumnas[25])
			    ->setCellValue('AA1', $titulosColumnas[26])
			    ->setCellValue('AB1', $titulosColumnas[27])
			    ->setCellValue('AC1', $titulosColumnas[28])
			    ->setCellValue('AD1', $titulosColumnas[29])
			    ->setCellValue('AE1', $titulosColumnas[30])
			    ->setCellValue('AF1', $titulosColumnas[31])
			    ->setCellValue('AG1', $titulosColumnas[32])
			    ->setCellValue('AH1', $titulosColumnas[33])
			    ->setCellValue('AI1', $titulosColumnas[34])
			    ->setCellValue('AJ1', $titulosColumnas[35])
			    ->setCellValue('AK1', $titulosColumnas[36])
			    //->setCellValue('AL1', $titulosColumnas[37])
			    //->setCellValue('AM1', $titulosColumnas[38])
			    //->setCellValue('AN1', $titulosColumnas[39])
				;

	//Se agregan los datos a la lista del reporte
    $i = 2; //Numero de fila donde se va a comenzar a rellenar

	$monto_maximo_concar = 41;
	$monto_restante = 0;
	$var_indice_movilidad = 0;

	if($row_count_detalle_locales > 0)
	{
		while ($row = $list_query_locales->fetch_array())
		{
			$row["departamento"]=@$ubigeos_arr[substr($row["ubigeo_id"], 0,2)."0000"];
			$row["provincia"]=@$ubigeos_arr[substr($row["ubigeo_id"], 0,4)."00"];;
			$row["distrito"]=@$ubigeos_arr[$row["ubigeo_id"]];			
			$cc_id = $row["cc_id"];
    		$nombre = $row["nombre"];
    		$departamento = $row["departamento"];
    		$provincia = $row["provincia"];
    		$distrito = $row["distrito"];
    		$direccion = $row["direccion"];
    		$enlace_mapa = $row["enlace_mapa"];
    		$internet_proveedor_nombre = $row["internet_proveedor_nombre"];
    		$internet_tipo_nombre = $row["internet_tipo_nombre"];
    		$num_decos_internet = $row["num_decos_internet"];
			$num_decos_directv = $row["num_decos_directv"];
    		$num_cpu = $row["num_cpu"];
    		$num_monitores = $row["num_monitores"];
    		$num_terminales_kasnet = $row["num_terminales_kasnet"];
    		$trastienda = $row["trastienda"];
    		$monday = $row["monday"];
    		$tuesday = $row["tuesday"];
    		$wednesday = $row["wednesday"];
    		$thursday = $row["thursday"];
    		$friday = $row["friday"];
    		$saturday = $row["saturday"];
    		$sunday = $row["sunday"];
    		$conteo_cajas = $row["conteo_cajas"];
    		$num_autoservicios = $row["num_autoservicios"];
    		$num_allinone = $row["num_allinone"];
    		$num_terminales_hibrido = $row["num_terminales_hibrido"];
    		$num_terminales_antiguo = $row["num_terminales_antiguo"];
    		$num_tv_apuestas_virtuales = $row["num_tv_apuestas_virtuales"];
    		$num_tv_apuestas_deportivas = $row["num_tv_apuestas_deportivas"];
    		$nombre_subgerente = $row["nombre_subgerente"];
    		$zona = $row["zona"];
    		$jefe_comercial = $row["jefe_comercial"];
    		$js_telefono = $row["js_telefono"];
    		//$supervisor = $row["supervisor"];
    		//$s_telefono = $row["s_telefono"];
    		//$s_correo = $row["s_correo"];
    		$phone = $row["phone"];
    		$email = $row["email"];
    		$estado = $row["estado"];
    		$fecha_inicio_operacion = $row["fecha_inicio_operacion"];

    		$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $cc_id)
							->setCellValue('B'.$i, $nombre)
							->setCellValue('C'.$i, $departamento)
							->setCellValue('D'.$i, $provincia)
							->setCellValue('E'.$i, $distrito)
							->setCellValue('F'.$i, $direccion)
							->setCellValue('G'.$i, $enlace_mapa)
							->setCellValue('H'.$i, $internet_proveedor_nombre)
							->setCellValue('I'.$i, $internet_tipo_nombre)
							->setCellValue('J'.$i, $num_decos_internet)
							->setCellValue('K'.$i, $num_decos_directv)
							->setCellValue('L'.$i, $num_cpu)
							->setCellValue('M'.$i, $num_monitores)
							->setCellValue('N'.$i, $num_terminales_kasnet)
							->setCellValue('O'.$i, $trastienda)
                                ->setCellValue('P'.$i, $monday)
                                ->setCellValue('Q'.$i, $tuesday)
                                ->setCellValue('R'.$i, $wednesday)
                                ->setCellValue('S'.$i, $thursday)
                                ->setCellValue('T'.$i, $friday)
                                ->setCellValue('U'.$i, $saturday)
                                ->setCellValue('V'.$i, $sunday)
                                ->setCellValue('W'.$i, $conteo_cajas)
                                ->setCellValue('X'.$i, $num_autoservicios)
                                ->setCellValue('Y'.$i, $num_allinone)
                                ->setCellValue('Z'.$i, $num_terminales_hibrido)
                                ->setCellValue('AA'.$i, $num_terminales_antiguo)
                                ->setCellValue('AB'.$i, $num_tv_apuestas_virtuales)
                                ->setCellValue('AC'.$i, $num_tv_apuestas_deportivas)
                                ->setCellValue('AD'.$i, $nombre_subgerente)
                                ->setCellValue('AE'.$i, $zona)
                                ->setCellValue('AF'.$i, $jefe_comercial)
                                ->setCellValue('AG'.$i, $js_telefono)
                                //->setCellValue('AH'.$i, $supervisor)
                                //->setCellValue('AI'.$i, $s_telefono)
                                //->setCellValue('AJ'.$i, $s_correo)
                                ->setCellValue('AH'.$i, $phone)
                                ->setCellValue('AI'.$i, $email)
                                ->setCellValue('AJ'.$i, $estado)
                                ->setCellValue('AK'.$i, $fecha_inicio_operacion);
			$i++;
		}
	}

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
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => '000000')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloColoFondo = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => '900C0C')
	  )
	);
	  
	$estiloTituloColumnas = array(
	    'font' => array(
	        'name'  => 'Calibri',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => 'FFFFFF'
	        )
	    ),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);
	  
	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Calibri',
			'italic'    => false,
	        'strike'    => false,
			'size' =>10,
	        'color' => array(
	            'rgb' => '000000'
	        )
			),
		'alignment' =>  array(
			'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	));
	$estiloInformacionLeft = new PHPExcel_Style();
	$estiloInformacionLeft->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Calibri',
			'italic'    => false,
	        'strike'    => false,
			'size' =>10,
	        'color' => array(
	            'rgb' => '000000'
	        )
			),
		'alignment' =>  array(
			'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Cambiado de CENTER a LEFT
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	));

	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
		// Recorre todas las filas mayores a 1 y aplica el ajuste automático de altura
		for ($i = 2; $i <= $objPHPExcel->getActiveSheet()->getHighestRow(); $i++) {
			$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(18);
		}

	$objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray($estiloNombresFilas);

	$objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AK1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloColoFondo);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AK1')->applyFromArray($estiloColoFondo);
	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AK".($i-1));

	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "B2:B2".($i-1));
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "F2:F2".($i-1));
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "G2:G2".($i-1));
	//$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "AL2:AL2".($i-1));

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Lista de locales detallada');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="LocalesDetallado.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$filename = "LOCALES_20042023 [LOCALES_" . date("dmY") . "].xls";
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/' . $filename;
	$excel_path_download = '/files_bucket/mepa/descargas/'. $filename;

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download,
		"estado_archivo" => 1
	));
	exit;
}

function replace_invalid_caracters($cadena) {
	$cadena = str_replace("\n", " ", $cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("#", "", $cadena);
	$cadena = str_replace("$", "", $cadena);
	$cadena = str_replace("%", "", $cadena);
	$cadena = str_replace("&", "", $cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("(", "", $cadena);
	$cadena = str_replace(")", "", $cadena);
	$cadena = str_replace("*", "", $cadena);
	$cadena = str_replace("+", "", $cadena);
	$cadena = str_replace("-", "", $cadena);
	$cadena = str_replace(".", "", $cadena);
	$cadena = str_replace("/", "", $cadena);
	$cadena = str_replace("<", "", $cadena);
	$cadena = str_replace("=", "", $cadena);
	$cadena = str_replace(">", "", $cadena);
	$cadena = str_replace("?", "", $cadena);
	$cadena = str_replace("@", "", $cadena);
	$cadena = str_replace("[", "", $cadena);
	$cadena = str_replace("\\", "", $cadena);
	$cadena = str_replace("]", "", $cadena);
	$cadena = str_replace("^", "", $cadena);
	$cadena = str_replace("_", "", $cadena);
	$cadena = str_replace("`", "", $cadena);
	$cadena = str_replace("{", "", $cadena);
	$cadena = str_replace("|", "", $cadena);
	$cadena = str_replace("}", "", $cadena);
	$cadena = str_replace("~", "", $cadena);
	$cadena = str_replace("¡", "", $cadena);
	$cadena = str_replace("¢", "", $cadena);
	$cadena = str_replace("£", "", $cadena);
	$cadena = str_replace("¤", "", $cadena);
	$cadena = str_replace("¥", "", $cadena);
	$cadena = str_replace("¦", "", $cadena);
	$cadena = str_replace("§", "", $cadena);
	$cadena = str_replace("¨", "", $cadena);
	$cadena = str_replace("©", "", $cadena);
	$cadena = str_replace("ª", "", $cadena);
	$cadena = str_replace("«", "", $cadena);
	$cadena = str_replace("¬", "", $cadena);
	$cadena = str_replace("®", "", $cadena);
	$cadena = str_replace("°", "", $cadena);
	$cadena = str_replace("±", "", $cadena);
	$cadena = str_replace("²", "", $cadena);
	$cadena = str_replace("³", "", $cadena);
	$cadena = str_replace("´", "", $cadena);
	$cadena = str_replace("µ", "", $cadena);
	$cadena = str_replace("¶", "", $cadena);
	$cadena = str_replace("·", "", $cadena);
	$cadena = str_replace("¸", "", $cadena);
	$cadena = str_replace("¹", "", $cadena);
	$cadena = str_replace("º", "", $cadena);
	$cadena = str_replace("»", "", $cadena);
	$cadena = str_replace("¼", "", $cadena);
	$cadena = str_replace("½", "", $cadena);
	$cadena = str_replace("¾", "", $cadena);
	$cadena = str_replace("¿", "", $cadena);
	$cadena = str_replace("À", "A", $cadena);
	$cadena = str_replace("Á", "A", $cadena);
	$cadena = str_replace("Â", "A", $cadena);
	$cadena = str_replace("Ã", "A", $cadena);
	$cadena = str_replace("Ä", "A", $cadena);
	$cadena = str_replace("Å", "A", $cadena);
	$cadena = str_replace("Æ", "", $cadena);
	$cadena = str_replace("Ç", "", $cadena);
	$cadena = str_replace("È", "E", $cadena);
	$cadena = str_replace("É", "E", $cadena);
	$cadena = str_replace("Ê", "E", $cadena);
	$cadena = str_replace("Ë", "E", $cadena);
	$cadena = str_replace("Ì", "I", $cadena);
	$cadena = str_replace("Í", "I", $cadena);
	$cadena = str_replace("Î", "I", $cadena);
	$cadena = str_replace("Ï", "I", $cadena);
	$cadena = str_replace("Ð", "", $cadena);
	$cadena = str_replace("Ñ", "N", $cadena);
	$cadena = str_replace("Ò", "O", $cadena);
	$cadena = str_replace("Ó", "O", $cadena);
	$cadena = str_replace("Ô", "O", $cadena);
	$cadena = str_replace("Õ", "O", $cadena);
	$cadena = str_replace("Ö", "O", $cadena);
	$cadena = str_replace("×", "", $cadena);
	$cadena = str_replace("Ø", "", $cadena);
	$cadena = str_replace("Ù", "U", $cadena);
	$cadena = str_replace("Ú", "U", $cadena);
	$cadena = str_replace("Û", "U", $cadena);
	$cadena = str_replace("Ü", "U", $cadena);
	$cadena = str_replace("Ý", "Y", $cadena);
	$cadena = str_replace("Þ", "", $cadena);
	$cadena = str_replace("ß", "", $cadena);
	$cadena = str_replace("à", "a", $cadena);
	$cadena = str_replace("á", "a", $cadena);
	$cadena = str_replace("â", "a", $cadena);
	$cadena = str_replace("ã", "a", $cadena);
	$cadena = str_replace("ä", "a", $cadena);
	$cadena = str_replace("å", "a", $cadena);
	$cadena = str_replace("æ", "", $cadena);
	$cadena = str_replace("ç", "", $cadena);
	$cadena = str_replace("è", "e", $cadena);
	$cadena = str_replace("é", "e", $cadena);
	$cadena = str_replace("ê", "e", $cadena);
	$cadena = str_replace("ë", "e", $cadena);
	$cadena = str_replace("ì", "i", $cadena);
	$cadena = str_replace("í", "i", $cadena);
	$cadena = str_replace("î", "i", $cadena);
	$cadena = str_replace("ï", "i", $cadena);
	$cadena = str_replace("ð", "o", $cadena);
	$cadena = str_replace("ñ", "n", $cadena);
	$cadena = str_replace("ò", "o", $cadena);
	$cadena = str_replace("ó", "o", $cadena);
	$cadena = str_replace("ô", "o", $cadena);
	$cadena = str_replace("õ", "o", $cadena);
	$cadena = str_replace("ö", "o", $cadena);
	$cadena = str_replace("÷", "", $cadena);
	$cadena = str_replace("ø", "", $cadena);
	$cadena = str_replace("ù", "u", $cadena);
	$cadena = str_replace("ú", "u", $cadena);
	$cadena = str_replace("û", "u", $cadena);
	$cadena = str_replace("ü", "u", $cadena);
	$cadena = str_replace("ý", "y", $cadena);
	$cadena = str_replace("þ", "", $cadena);
	$cadena = str_replace("ÿ", "", $cadena);
	$cadena = str_replace("Œ", "", $cadena);
	$cadena = str_replace("œ", "", $cadena);
	$cadena = str_replace("Š", "", $cadena);
	$cadena = str_replace("š", "", $cadena);
	$cadena = str_replace("Ÿ", "", $cadena);
	$cadena = str_replace("ƒ", "", $cadena);
	$cadena = str_replace("–", "", $cadena);
	$cadena = str_replace("—", "", $cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("‚", "", $cadena);
	$cadena = str_replace('"', "", $cadena);
	$cadena = str_replace('"', "", $cadena);
	$cadena = str_replace("„", "", $cadena);
	$cadena = str_replace("†", "", $cadena);
	$cadena = str_replace("‡", "", $cadena);
	$cadena = str_replace("•", "", $cadena);
	$cadena = str_replace("…", "", $cadena);
	$cadena = str_replace("‰", "", $cadena);
	$cadena = str_replace("€", "", $cadena);
	$cadena = str_replace("™", "", $cadena);

	return $cadena;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="import_detalle_locales_excel") {
	global $mysqli;

	// Obtener los datos del archivo JSON y decodificarlos
	$data = file_get_contents("../files_bucket/migracion/datalocales.json");
    $locales = json_decode($data, true);
	print_r($locales);

	// Comprobar si la conexión a la base de datos se ha establecido correctamente
	if ($mysqli->connect_errno) {
        die("Failed to connect to MySQL: " . $mysqli->connect_error);
    }
	// Recorrer el array de datos para actualizar la tabla tbl_locales y agregar registros en las otras tablas
	foreach ($locales as $item) {
		// Sanitizar y preparar los datos para su inserción en la base de datos
		$cc_id = $item['cc_id'];
        $correo = $mysqli->real_escape_string($item['correo']);
		$phone = $mysqli->real_escape_string($item['phone']);
        $trastienda = $item['trastienda'];
		if (!empty($trastienda)) {
            $trastienda = explode("|", $trastienda)[0];
            $trastienda = $mysqli->real_escape_string($trastienda);
        }

		// Actualizar la tabla tbl_locales con los datos recibidos
        $sql = "UPDATE tbl_locales SET nombre = '" . replace_invalid_caracters(trim($correo)). "', 
										phone = '" . replace_invalid_caracters(trim($phone)). "', 
										trastienda = " . $trastienda . " 			
										WHERE cc_id = '".$cc_id."'";
        $mysqli->query($sql);

        $num_tv_apuestas_virtuales = $item['num_tv_apuestas_virtuales'];
        $num_tv_apuestas_deportivas = $item['num_tv_apuestas_deportivas'];

		// Insertar registros en la tabla tbl_locales_equipos
        $sql = "INSERT INTO tbl_locales_equipos (local_id, 
													num_tv_apuestas_virtuales, 
													num_tv_apuestas_deportivas) 
												SELECT 
													id,
													" . $num_tv_apuestas_virtuales. ",
													" . $num_tv_apuestas_deportivas. "
													FROM tbl_locales
												WHERE cc_id = '".$cc_id."'";
        $mysqli->query($sql);

        $num_cpu = $item['num_cpu'];
        $num_monitores = $item['num_monitores'];
        $num_autoservicios = $item['num_autoservicios'];
        $num_allinone = $item['num_allinone'];
        $num_terminales_hibrido = $item['num_terminales_hibrido'];
        $num_terminales_antiguo = $item['num_terminales_antiguo'];

		// Insertar registros en la tabla tbl_locales_equipos_computo
		$sql = "INSERT INTO tbl_locales_equipos_computo (
													local_id
													, num_cpu
													, num_monitores
													, num_autoservicios
													, num_allinone
													, num_terminales_hibrido
													, num_terminales_antiguo
													) 
												SELECT 
													id,
													" . $num_cpu. ",
													" . $num_monitores. ",
													" . $num_autoservicios. ",
													" . $num_allinone. ",
													" . $num_terminales_hibrido. ",
													" . $num_terminales_antiguo. "
												FROM tbl_locales
												WHERE cc_id = '".$cc_id."'";												
        $mysqli->query($sql);

        $internet_proveedor_id = $item['internet_proveedor_id'];
        $internet_tipo_id = $item['internet_tipo_id'];
        $num_decos_internet = $item['num_decos_internet'];
        $num_decos_directv = $item['num_decos_directv'];

        if (!empty($internet_proveedor_id)) {
            $internet_proveedor_id = explode("|", $internet_proveedor_id)[0];
            $internet_proveedor_id = $mysqli->real_escape_string($internet_proveedor_id);
        }
        if (!empty($internet_tipo_id)) {
            $internet_tipo_id = explode("|", $internet_tipo_id)[0];
            $internet_tipo_id = $mysqli->real_escape_string($internet_tipo_id);
        }

        // Insertar registros en la tabla tbl_locales_equipos_computo
		$sql = "INSERT INTO tbl_locales_servicios (
												local_id, 
												internet_proveedor_id, 
												internet_tipo_id, 
												num_decos_internet, 
												num_decos_directv) 
											SELECT 
												id,
												" . $internet_proveedor_id. ",
												" . $internet_tipo_id. ",
												" . $num_decos_internet. ",
												" . $num_decos_directv. "
											FROM tbl_locales
        									WHERE cc_id = '".$cc_id."'";
		$mysqli->query($sql);
	}
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "locales_caja_cambiar_estado") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $caja_id = $_POST["caja_id"];
        $estado = $_POST["estado"];

        if ((int)$caja_id > 0) {

            $query_update_subdiario = "
                UPDATE tbl_local_cajas 
                SET 
                    estado = ?,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

            $stmt = $mysqli->prepare($query_update_subdiario);
            $stmt->bind_param("iiss", $estado, $usuario_id, $fecha, $caja_id);

            if ($stmt->execute()) {
				$title = ($estado == 1) ? "Activación exitosa" : "Desactivación exitosa";
				$text = ($estado == 1) ? "La caja se activó correctamente" : "La caja se desactivó correctamente";

                $result["http_code"] = 200;
				$result["title"] = $title;
                $result["text"] = $text;

            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe la caja";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["set_status_proveedor"])) {

    if (!isset($_POST["local_proveedor_id"], $_POST["new_status"], $_POST["old_status"])) {
        echo json_encode(['status' => 400, 'message' => 'Datos incompletos']);
        exit();
    }
    $local_proveedor_id = (int)$_POST["local_proveedor_id"];
    $new_status = (int)$_POST["new_status"];
    $old_status = (int)$_POST["old_status"];
	$servicio_id = (int)$_POST["servicio_id"];
    $id_usuario = (int)$login['id'];

    $result = [];

    $result_state = $new_status ? 'habilitó' : 'deshabilitó';

    $query_update = "UPDATE tbl_local_proveedor_id SET habilitado = $new_status WHERE id = $local_proveedor_id";
    $update_historico = $mysqli->query($query_update);

    if ($update_historico) {
		
        $query_insert = "INSERT INTO tbl_local_proveedor_id_config_historial_cambios (
                            local_proveedor_id, 
							servicio_id, 
                            valor_anterior,
                            valor_nuevo,
                            status,
                            id_usuario,
                            created_at
                         ) VALUES (
                            $local_proveedor_id,
							$servicio_id,
                            $old_status,
                            $new_status,
                            1,
                            $id_usuario,
                            NOW()
                         )";

        $insert_transaccion = $mysqli->query($query_insert);

        if ($insert_transaccion) {
            $result['status'] = 200;
            $result['message'] = "Se $result_state correctamente el terminal.";
        } else {
            $result['status'] = 500;
            $result['message'] = "Ocurrió un error al registrar el historial.";
        }

    } else {
        $result['status'] = 500;
        $result['message'] = "Ocurrió un error al actualizar el terminal.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "get_historico_local_proveedor") {
    
	$local_proveedor_id = $_POST['local_proveedor_id'];
	$servicio_id = $_POST['servicio_id'];

	$query = "
		SELECT
		t2.nombre servicio,
		t1.valor_anterior,
		t1.valor_nuevo,
		DATE_FORMAT(t1.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
		t3.usuario
		FROM tbl_local_proveedor_id_config_historial_cambios t1
		INNER JOIN tbl_servicios t2 ON t2.id = t1.servicio_id
		INNER JOIN tbl_usuarios t3 ON t3.id = t1.id_usuario
		WHERE t1.local_proveedor_id = ? 
		AND t1.servicio_id = ?
		ORDER BY t1.created_at DESC;";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Error preparando la consulta: " . $mysqli->error);
        echo json_encode([
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => []
        ]);
        exit;
    }
    
    $stmt->bind_param("ii", $local_proveedor_id, $servicio_id);
    $stmt->execute();
    $list_query = $stmt->get_result();
    $stmt->close();

    $data = [];

    if ($mysqli->error) {
        error_log("Error en la consulta: " . $mysqli->error);
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => ''
        ];
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_assoc()) {
			$valor_anterior = ($reg['valor_anterior'] == 1) ? '<span class="badge bg-success">Habilitado</span>' : '<span class="badge bg-danger">Deshabilitado</span>';
			$valor_nuevo = ($reg['valor_nuevo'] == 1) ? '<span class="badge bg-success">Habilitado</span>' : '<span class="badge bg-danger">Deshabilitado</span>';
            $data[] = [
                "0" => $cont,
				"1" => $reg['servicio'],
                "2" => $valor_anterior,
                "3" => $valor_nuevo,
                "4" => $reg['created_at'],
                "5" => $reg['usuario']
            ];
            $cont++;
        }
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
    }
    echo json_encode($resultado);
    exit;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "get_historico_local_caja_detalle") {
    
	$local_caja_detalle_tipos_id = $_POST['local_caja_detalle_tipos_id'];
	
	$query = "SELECT 
	t1.valor_nombre_anterior,
	t1.valor_nombre_nuevo,
	t1.valor_descripcion_anterior,
	t1.valor_descripcion_nuevo,
	t2.usuario,
	DATE_FORMAT(t1.created_at, '%d/%m/%Y %H:%i:%s') AS created_at
	FROM tbl_local_caja_detalle_tipos_historial_cambios t1
	INNER JOIN tbl_usuarios t2 ON t2.id = t1.id_usuario
	WHERE t1.tbl_local_caja_detalle_tipos_id = ?
	ORDER BY t1.created_at DESC;";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Error preparando la consulta: " . $mysqli->error);
        echo json_encode([
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => []
        ]);
        exit;
    }
    
    $stmt->bind_param("i", $local_caja_detalle_tipos_id);
    $stmt->execute();
    $list_query = $stmt->get_result();
    $stmt->close();

    $data = [];

    if ($mysqli->error) {
        error_log("Error en la consulta: " . $mysqli->error);
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => '',
			"5" => '',
			"6" => ''
        ];
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_assoc()) {

            $data[] = [
                "0" => $cont,
				"1" => $reg['valor_nombre_anterior'],
                "2" => $reg['valor_nombre_nuevo'],
                "3" => $reg['valor_descripcion_anterior'],
                "4" => $reg['valor_descripcion_nuevo'],
                "5" => $reg['usuario'],
				"6" => $reg['created_at']
            ];
            $cont++;
        }
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
    }
    echo json_encode($resultado);
    exit;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));

?>