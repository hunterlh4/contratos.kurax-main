<?php
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

$return = array();
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");



if (isset($_POST['sec_mantenimientos_contrato_tipo_save'])) {
	$nombre_tabla   =   $_POST['nombre_tabla'];
	$nombre =  $_POST['nombre'];
	$estado =  $_POST['estado'];
	$nombre_tipo =  $_POST['nombre_tipo'];
	$tipo_accion_modal = $_POST['tipo_accion_modal'];
	$nombre =  replace_invalid_caracters($nombre);


	$error = '';
	try {
		if ($tipo_accion_modal == 'new') {

			if ($nombre_tabla != 'cont_tipo_archivos' && $nombre_tabla != 'cont_tipo_categoria_servicio') {
				$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
				$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre) = '$nombre_minisculas'";
				$result = $mysqli->query($select_command);
	
				if ($result->num_rows == 0) {
					// la columna no existe en la tabla
					if ($nombre_tabla == 'cont_tipo_suministro_deBaja' || $nombre_tabla == 'cont_tipo_file_noBa' || $nombre_tabla == 'cont_tipo_couta_DeBaja') {
	
						$descripcion = '';
						$insert_command = "
						insert into $nombre_tabla (
							nombre,
							status,
							descripcion,
							created_at,
							user_created_id,
							updated_at,
							user_updated_id
							)
						values( '" . $nombre . "',
								'" . $estado . "',
								'" . $descripcion . "',
								now(),
								'" . $login["id"] . "',
								now(),
								'" . $login["id"] . "'
								)";
					} else {
	
						$comando_select = "
							SELECT estado
							  FROM  $nombre_tabla
							  LIMIT 1
							";
						$result = $mysqli->query($comando_select);
	
						if ($result) {
							$insert_command = "
								insert into $nombre_tabla (
								nombre,
								estado,
								created_at,
								user_created_id,
								updated_at,
								user_updated_id
								)
								values( '" . $nombre . "',
									'" . $estado . "',
									now(),
									'" . $login["id"] . "',
									now(),
									'" . $login["id"] . "'
									)";
						} else {
							$insert_command = "
								insert into $nombre_tabla (
								nombre,
								status,
								created_at,
								user_created_id,
								updated_at,
								user_updated_id
								)
								values( '" . $nombre . "',
									'" . $estado . "',
									now(),
									'" . $login["id"] . "',
									now(),
									'" . $login["id"] . "'
									)";
						}
					}
					if (!$mysqli->query($insert_command)) {
						$error_code = $mysqli->errno;
						if($error_code==1406){
							$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre'";
							$result = $mysqli->query($query);
							$row = $result->fetch_assoc();
	
							$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];
							$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
							$return["error"] = $error;
						}else{
							$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
							$return["error"] = $error;						
						}
						

						 

						
					}
				} else {
					$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
					$return["error"] = $error;
				}
			} else if ($nombre_tabla == 'cont_tipo_categoria_servicio') {
				$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
				$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre) = '$nombre_minisculas'";
				$result = $mysqli->query($select_command);
				if ($result->num_rows == 0) {
					$insert_command = "
							insert into $nombre_tabla (
								nombre,
								status,
								categoria_servicio_id,
								created_at,
								created_id,
								updated_at,
								updated_id
								)
							values( '" . $nombre . "',
									'" . $estado . "',
									'-1',
									now(),
									'" . $login["id"] . "',
									now(),
									'" . $login["id"] . "'
									)";

					if (!$mysqli->query($insert_command)) {
						$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre'";
						$result = $mysqli->query($query);
						$row = $result->fetch_assoc();

						$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];


						if(strlen($nombre)>$max_length){
							$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
							$return["error"] = $error;
						}else{
							$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
							$return["error"] = $error;
						}
						 
					}

				}else{
					$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
					$return["error"] = $error;
				}

				
			}	else	{
				$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
				$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre_tipo_archivo) = '$nombre_minisculas'";
				$result = $mysqli->query($select_command);
				if ($result->num_rows == 0) {
					$insert_command = "
					insert into $nombre_tabla (nombre_tipo_archivo,status,tipo_contrato_id,created_at,
					user_created_id,
					updated_at,
					user_updated_id
					)
					values
					('$nombre',
					$estado,
					0,
					now(),
					'" . $login["id"] . "',
					now(),
					'" . $login["id"] . "')";
					if (!$mysqli->query($insert_command)) {
						$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre_tipo_archivo'";
						$result = $mysqli->query($query);
						$row = $result->fetch_assoc();

						$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];

						
						if(strlen($nombre)>$max_length){
							$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
							$return["error"] = $error;
						}else{
							$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
							$return["error"] = $error;
						}
						 
					}
				} else {
					$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
					$return["error"] = $error;
				}
			}
	
			if ($error == '') {
				$return["mensaje"] = " Se ha actualizado :  $nombre en tipo:  $nombre_tipo";
			}
		} else if ($tipo_accion_modal == 'edit') {
	
			$id_contrato_tipo =  $_POST['id_contrato_tipo'];
	
			if ($nombre_tabla == 'cont_tipo_archivos') {
				$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
				$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre_tipo_archivo) = '$nombre_minisculas' and tipo_archivo_id!=" . $id_contrato_tipo;
				$result = $mysqli->query($select_command);
	
				if ($result->num_rows == 0) {
					$update_query_command = "update
							$nombre_tabla
							set nombre_tipo_archivo = '$nombre',
								status = '$estado',
								 user_updated_id = '" . $login["id"] . "',
							updated_at = now()
							where tipo_archivo_id = '$id_contrato_tipo'";
					$mysqli->query($update_query_command);
					
					if (!$mysqli->query($update_query_command)) {
						$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre_tipo_archivo'";
						$result = $mysqli->query($query);
						$row = $result->fetch_assoc();

						$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];

						if(strlen($nombre)>$max_length){
							$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
							$return["error"] = $error;
						}else{
							$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
							$return["error"] = $error;
						}
						 
					}
				} else {
					$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
					$return["error"] = $error;
				}			
	
	
	
				
			} else if ($nombre_tabla == 'cont_tipo_suministro_deBaja') {
				$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
				$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre) = '$nombre_minisculas' and tipo_suministro_id!=" . $id_contrato_tipo;
				$result = $mysqli->query($select_command);
				if ($result->num_rows == 0) {
						  $update_query_command = "update
						  $nombre_tabla
						   set nombre ='$nombre',
						   status = '$estado',
						  user_updated_id = '" . $login["id"] . "',
						  updated_at = now()
						  where tipo_suministro_id='$id_contrato_tipo'";
						  $mysqli->query($update_query_command);
						if (!$mysqli->query($update_query_command)) {
							$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre'";
							$result = $mysqli->query($query);
							$row = $result->fetch_assoc();
	
							$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];
	
							if(strlen($nombre)>$max_length){
								$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
								$return["error"] = $error;
							}else{
								$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
								$return["error"] = $error;
							}
						}
					} else {
					$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
					$return["error"] = $error;
					}
			} else if ($nombre_tabla == 'cont_tipo_file_noBa') {
					$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
					$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre) = '$nombre_minisculas' and tipo_file_id!=" . $id_contrato_tipo;
					$result = $mysqli->query($select_command);
					if ($result->num_rows == 0) {
						$update_query_command = "update
						$nombre_tabla
						 set nombre ='$nombre',
						 status = '$estado',
						user_updated_id = '" . $login["id"] . "',
						updated_at = now()
						 where tipo_file_id='$id_contrato_tipo'";
						$mysqli->query($update_query_command);
						if (!$mysqli->query($update_query_command)) {
						$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre'";
						$result = $mysqli->query($query);
						$row = $result->fetch_assoc();

						$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];

						if (strlen($nombre) > $max_length) {
							$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :" . $max_length;
							$return["error"] = $error;
						} else {
							$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
							$return["error"] = $error;
						}
					}
					} else {
						$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
						$return["error"] = $error;
					}
			} else if ($nombre_tabla == 'cont_tipo_couta_DeBaja') { 
						
						$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
						$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre) = '$nombre_minisculas' and tipo_couta_id!=" . $id_contrato_tipo;
						$result = $mysqli->query($select_command);
						if ($result->num_rows == 0) {
							$update_query_command = "update
							$nombre_tabla
							set nombre ='$nombre',
							status = '$estado',
							user_updated_id = '" . $login["id"] . "',
							updated_at = now()
							where tipo_couta_id='$id_contrato_tipo'";
						$mysqli->query($update_query_command);
							if (!$mysqli->query($update_query_command)) {
								$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre'";
								$result = $mysqli->query($query);
								$row = $result->fetch_assoc();
		
								$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];
		
								if(strlen($nombre)>$max_length){
									$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
									$return["error"] = $error;
								}else{
									$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
									$return["error"] = $error;
								}
							}
						} else {
							$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
							$return["error"] = $error;
						}
			} 
			else if ($nombre_tabla == 'cont_tipo_categoria_servicio') { 
						
				$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
				$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre) = '$nombre_minisculas' and id!=" . $id_contrato_tipo;
				$result = $mysqli->query($select_command);
				if ($result->num_rows == 0) {
					$update_query_command = "update
					$nombre_tabla
					set nombre ='$nombre',
						status = '$estado',
					updated_id = '" . $login["id"] . "',
					updated_at = now()
					where id='$id_contrato_tipo'";
					$mysqli->query($update_query_command);
					if (!$mysqli->query($update_query_command)) {
						$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre'";
						$result = $mysqli->query($query);
						$row = $result->fetch_assoc();

						$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];

						if(strlen($nombre)>$max_length){
							$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
							$return["error"] = $error;
						}else{
							$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
							$return["error"] = $error;
						}
					}
				} else {
					$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
					$return["error"] = $error;
				}
			} else {
				$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre));
				$select_command = "SELECT * FROM $nombre_tabla WHERE LOWER(nombre) = '$nombre_minisculas' and id!=" . $id_contrato_tipo;
				$result = $mysqli->query($select_command);
	
				// VALIDAMOS SI EL REGISTRO CON ESE NOMBRE YA EXISTE PERO SIN CONTAR EL REGISTRO RECIVIDO
				if ($result->num_rows == 0) {
					$comando_select = "
						SELECT estado
							  FROM  $nombre_tabla
							  LIMIT 1
						";
					$result = $mysqli->query($comando_select);
	
					if ($result) {
						$update_query_command = "update
							$nombre_tabla
							 set nombre ='$nombre',
							 estado = '$estado',
							user_updated_id = '" . $login["id"] . "',
							updated_at = now()
							 where id='$id_contrato_tipo'";
						$mysqli->query($update_query_command);
						if (!$mysqli->query($update_query_command)) {
							$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre'";
							$result = $mysqli->query($query);
							$row = $result->fetch_assoc();

							$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];

							if(strlen($nombre)>$max_length){
								$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
								$return["error"] = $error;
							}else{
								$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
								$return["error"] = $error;
							}
						}
					} else {
						$update_query_command = "update
									$nombre_tabla
									 set nombre ='$nombre',
									 status = '$estado',
									 user_updated_id = '" . $login["id"] . "',
									updated_at = now()
									 where id='$id_contrato_tipo'";
						$mysqli->query($update_query_command);
						if (!$mysqli->query($update_query_command)) {
							$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$nombre_tabla' AND COLUMN_NAME = 'nombre'";
							$result = $mysqli->query($query);
							$row = $result->fetch_assoc();

							$max_length = $row['CHARACTER_MAXIMUM_LENGTH'];

							if(strlen($nombre)>$max_length){
								$error = "Error al actualizar el registro: tamaño maximo de caracteres permitidos es :".$max_length;
								$return["error"] = $error;
							}else{
								$error = "Error al actualizar el registro: " . mysqli_error($mysqli);
								$return["error"] = $error;
							}
						}
					}
				} else {
					$error = "Error al actualizar el registro: el registro con nombre $nombre ya existe en la tabla";
					$return["error"] = $error;
				}
			}
	
	
	
	
	
			$return["id"] = $id_contrato_tipo;
			if ($error == '') {
				$return["mensaje"] = " Se ha actualizado :  $nombre en tipo:  $nombre_tipo";
			}
		}
	} catch (Exception $th) {
		$return["error_ex"] = $th;
	}
	
}


if (isset($_POST["sec_mantenimientos_contrato_tipo_list"])) {
	$data = $_POST["sec_mantenimientos_contrato_tipo_list"];
	$lista = [];
	$tipo = $_POST["tipo_general"];

	try {
		if ($tipo != '0') {

			// CONDICIONALES SI LOS CAMPOS CAMBIAN ** AQUI ES DONDE SE VA A MODIFICAR MAS ADELANTE
			// $comando_select = "
			// 	SELECT estado
			// 		  FROM  $tipo
			// 		  LIMIT 1
			// 	";
			// $result = $mysqli->query($comando_select);
			// var_dump($comando_select);exit();
	
			// if ($result) {
			// 	$comando_select = "
			// 			SELECT t.id,IF(t.estado=1,'Activo','Inactivo') as status   ,user_updated_id,updated_at, t.nombre ,u.usuario,user_created_id,created_at
			// 		  FROM $tipo  t
			// 		left  join tbl_usuarios u
			// 		on u.id =  t.user_updated_id
			// 		  order BY  t.id DESC
			// 		 ";
			// 	$query = $mysqli->query($comando_select);
			// 	$lista = [];
			// 	while ($d = $query->fetch_assoc()) {
	
			// 		// $d['status'] = $s['status'];
			// 		$lista[] = $d;
			// 	}
			// } else {
				if ($tipo == 'cont_tipo_suministro_deBaja') {
					$comando_select = "SELECT t.tipo_suministro_id as id, IF(t.status=1,'Activo','Inactivo') as status , t.user_updated_id ,t.updated_at,u.usuario,u2.usuario AS usuario_actualiza, t.nombre ,t.user_created_id,t.created_at
							FROM $tipo  t
							LEFT  JOIN tbl_usuarios u
							on u.id =  t.user_created_id
							LEFT  JOIN tbl_usuarios u2
							on u2.id =  t.user_updated_id
						  order BY  id DESC
						 ";
	
					$query = $mysqli->query($comando_select);
					$lista = [];
					while ($d = $query->fetch_assoc()) {
	
						$lista[] = $d;
					}
				} else if ($tipo == 'cont_tipo_file_noBa') {
					$comando_select = "SELECT t.tipo_file_id as id, IF(t.status=1,'Activo','Inactivo') as status   ,t.user_updated_id ,t.updated_at,t.nombre ,u.usuario,u2.usuario AS usuario_actualiza,t.user_created_id,t.created_at
							FROM $tipo  t
							LEFT  JOIN tbl_usuarios u
							on u.id =  t.user_created_id
							LEFT  JOIN tbl_usuarios u2
							on u2.id =  t.user_updated_id
							order BY  id DESC
						 ";
					$query = $mysqli->query($comando_select);
					$lista = [];
					while ($d = $query->fetch_assoc()) {
	
						$lista[] = $d;
					}
				} else if ($tipo == 'cont_tipo_couta_DeBaja') {
					$comando_select = "SELECT t.tipo_couta_id as id,IF(t.status=1,'Activo','Inactivo') as status  , t.user_updated_id ,t.updated_at,t.nombre ,u.usuario,u2.usuario AS usuario_actualiza,t.user_created_id,t.created_at
							FROM $tipo  t
							left  join tbl_usuarios u
							on u.id =  t.user_created_id
							LEFT  JOIN tbl_usuarios u2
							on u2.id =  t.user_updated_id
							order BY  id DESC
						 ";
					$query = $mysqli->query($comando_select);
					$lista = [];
					while ($d = $query->fetch_assoc()) {
						$lista[] = $d;
					}
				} else if ($tipo == 'cont_tipo_archivos') {
					$comando_select = "SELECT t.tipo_archivo_id as id, IF(t.status=1,'Activo','Inactivo') as status    ,
					t.user_updated_id ,t.updated_at,t.nombre_tipo_archivo as nombre ,
					u.usuario,u2.usuario AS usuario_actualiza ,t.user_created_id,t.created_at
											FROM cont_tipo_archivos  t
											LEFT JOIN tbl_usuarios u
											ON u.id =  t.user_created_id
											LEFT  JOIN tbl_usuarios u2
											ON u2.id =  t.user_updated_id
											ORDER BY  id DESC
						 ";
					$query = $mysqli->query($comando_select);
					$lista = [];
			// var_dump($comando_select);exit();s

					while ($d = $query->fetch_assoc()) {
						$lista[] = $d;
					}
				} else if ($tipo == 'cont_tipo_categoria_servicio') {
					$comando_select = "SELECT t.id,  IF(t.status=1,'Activo','Inactivo') as status    , t.updated_id ,t.updated_at,t.nombre ,u.usuario,u2.usuario AS usuario_actualiza,t.created_id,t.created_at
								FROM $tipo  t
									LEFT  JOIN tbl_usuarios u
									ON u.id =  t.created_id
									LEFT  JOIN tbl_usuarios u2
									ON u2.id =  t.updated_id
									ORDER BY  t.id DESC";
					$query = $mysqli->query($comando_select);
					$lista = [];
					while ($d = $query->fetch_assoc()) {
						$lista[] = $d;
					}
				}
				else if ($tipo == 'cont_tipo_pago_servicio') {
					$comando_select = "SELECT t.id,  IF(t.estado=1,'Activo','Inactivo') as status    , t.user_updated_id,t.updated_at,t.nombre ,u.usuario,u2.usuario AS usuario_actualiza,t.user_created_id,t.created_at
								FROM $tipo  t
									LEFT  JOIN tbl_usuarios u
									on u.id =  t.user_created_id
									LEFT  JOIN tbl_usuarios u2
									ON u2.id =  t.user_updated_id
									order BY  t.id DESC

									";
					$query = $mysqli->query($comando_select);
					$lista = [];
					while ($d = $query->fetch_assoc()) {
						$lista[] = $d;
					}
				}else if ($tipo == 'cont_tipo_comprobante') {
								$comando_select = "SELECT t.id,  IF(t.estado=1,'Activo','Inactivo') as status    , t.user_updated_id ,t.updated_at,t.nombre ,u.usuario,u2.usuario AS usuario_actualiza,t.user_created_id,t.created_at
								FROM $tipo  t
									LEFT  JOIN tbl_usuarios u
									ON u.id =  t.user_created_id
									LEFT  JOIN tbl_usuarios u2
									ON u2.id =  t.user_updated_id
									ORDER BY  t.id DESC

									";
					// var_dump($comando_select);exit();
					$query = $mysqli->query($comando_select);
					$lista = [];
					while ($d = $query->fetch_assoc()) {
						$lista[] = $d;
					}
				}else if ($tipo == 'cont_tipo_docu_identidad') {
					$comando_select = "SELECT t.id,  IF(t.estado=1,'Activo','Inactivo') as status    , t.user_updated_id ,t.updated_at,t.nombre ,u.usuario,u2.usuario AS usuario_actualiza,t.user_created_id,t.created_at
								FROM $tipo  t
									LEFT  JOIN tbl_usuarios u
									on u.id =  t.user_created_id
									LEFT  JOIN tbl_usuarios u2
									ON u2.id =  t.user_updated_id
									ORDER BY  t.id DESC

									";
					// var_dump($comando_select);exit();
					$query = $mysqli->query($comando_select);
					$lista = [];
					while ($d = $query->fetch_assoc()) {
						$lista[] = $d;
					}
				}else {
					$comando_select = "SELECT t.id, IF(t.status=1,'Activo','Inactivo') as status , t.nombre ,t.user_updated_id ,t.updated_at ,u.usuario,u2.usuario AS usuario_actualiza,t.user_created_id,t.created_at
								FROM $tipo  t
								LEFT  JOIN tbl_usuarios u
								ON u.id =  t.user_created_id
								LEFT  JOIN tbl_usuarios u2
								ON u2.id =  t.user_updated_id
								ORDER BY  t.id DESC
								";
						// var_dump($comando_select);exit();
					$query = $mysqli->query($comando_select);
					$lista = [];
					while ($d = $query->fetch_assoc()) {
						$lista[] = $d;
					}
				}
			// }
		}
		$return["lista"] = $lista;
		$return["mensaje"] = "lista realizada correctamente";
	} catch (Exception $th) {
		$return["error_ex"] = $th;

	}
	


	
}



$return["memory_end"] = memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"] = ($return["memory_end"] - $return["memory_init"]);
$return["time_total"] = ($return["time_end"] - $return["time_init"]);
print_r(json_encode($return));
