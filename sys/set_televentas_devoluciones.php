<?php

$result = array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Lima');

$usuario_id = $login ? $login['id'] : 0;
$usuario = $login ? $login['usuario'] : '';

if (!((int) $usuario_id > 0)) {
	$result["http_code"] = 400;
	$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	echo json_encode($result);exit();
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// VALIDAR SI CLIENTE EXISTE
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "validar_cliente_televentas_devoluciones") {
	include("function_replace_invalid_caracters.php");

    $timestamp      = $_POST["timestamp"];
    $busqueda_tipo  = $_POST["tipo"];
    $busqueda_valor = $_POST["valor"];
    $hash           = $_POST["hash"];
	$result["mensaje"] = "";
    
	if ((int) $busqueda_tipo >= 0 && (int) $busqueda_tipo <= 2) {
		$where = "tipo_doc='" . $busqueda_tipo . "' AND num_doc='" . $busqueda_valor . "'";
	}

	$query_1 = "
			SELECT 
				c.id,
				UPPER(IFNULL(c.nombre, '')) nombre,
				UPPER(IFNULL(c.apellido_paterno, '')) apellido_paterno,
				UPPER(IFNULL(c.apellido_materno, '')) apellido_materno
			FROM tbl_televentas_clientes c 
			WHERE " . $where . " 
			ORDER BY id ASC
			";
		$list_query_1 = $mysqli->query($query_1);
		$list_1 = array();
		if ($mysqli->error) {
			$result["query_1_error"] = $mysqli->error;
            echo json_encode($result);exit();
		} else {
			while ($li_1 = $list_query_1->fetch_assoc()) {
				$list_1[] = $li_1;
			}
		}

		if (count($list_1) > 0){
			$result["http_code"] = 400;
			$result["mensaje"] = "El número de documento se encuentra registrado, como cliente de Televentas, por favor consultar otro documento.";
			$result["result"] = $list_1[0];
			echo json_encode($result); exit(); 

		} else {

            //TIPO DOC
            if ((int) $busqueda_tipo >= 0 && (int) $busqueda_tipo <= 2) {
                // Si es DNI usamos la API de DNI
                if ((int) $busqueda_tipo === 0) {
                    $result_api_dni = tlv_devol_get_cliente_por_dni($busqueda_valor);
                    if((!is_array($result_api_dni)) or (empty($result_api_dni))){
                        $result["http_code"] = 400;
                        $result["mensaje"] = "El DNI no existe. Debe registrarlo manualmente";
                        $val_documento = (int)$busqueda_valor;
						echo json_encode($result); exit(); 
                    }else{
                        if(!(isset($result_api_dni["dni"]))) {
                            if(isset($result_api_dni["message"])) {	
                                $result["http_code"] = 200;
                                $result["mensaje"] = "Cuotas no disponibles para la busqueda. Por favor comunicarse con el canal digital y/o soporte.";
                                //echo json_encode($result); exit();
                            }
                            $val_documento = (int)$busqueda_valor;

                                $result["http_code"] = 200;
                                $result["mensaje"] = "No se encontro al DNI.";
                            //	echo json_encode($result); exit();
                        }

                        if ((int) $result_api_dni["dni"] === (int) $busqueda_valor) {
                            $result["http_code"] = 200;
                            $result["val_nombres"] = strtoupper(replace_invalid_caracters(trim($result_api_dni["nombres"])));
                            $result["val_apellido_paterno"] = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_paterno"])));
                            $result["val_apellido_materno"] = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_materno"])));
                            $result["val_documento"] = $result_api_dni["dni"];

                        } else {
                            $val_documento = (int)$busqueda_valor;
                        }
                    }
                } elseif ((int) $busqueda_tipo === 1 || (int) $busqueda_tipo === 2) {
                        $val_documento = $busqueda_valor;
                }
            }

        }

}



/* if ($_POST["accion"] === "obtener_televentas_cuentas_deposito_devoluciones") {
	include("function_replace_invalid_caracters.php");

	$consulta_cmd = "
		SELECT
			ca.id,
			ca.cuenta_descripcion,
			tc.bono,
			ifnull( tc.comision_monto, 0 ) comision_monto,
			IFNULL(ca.foreground, 'black') foreground,
			IFNULL(ca.background, '') background,
            IFNULL(tc.valid_cuenta_yape, 0) valid_cuenta_yape
		FROM
			tbl_televentas_cuentas tc
			JOIN tbl_cuentas_apt ca ON ca.id = tc.cuenta_apt_id 
		WHERE
			tc.status = 1 
			AND IFNULL(tc.valid_caja7, 0) = 0
			AND ca.id not in (12)
		ORDER BY ca.orden ASC
		";

	$list_query = $mysqli->query($consulta_cmd);
	$list_registers = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_registers[] = $li;
	}
	$error = '';
	if ($mysqli->error) {
		$result["insert_error"] = $mysqli->error;
		$error = $mysqli->error;
	}
	if ($error === '') {
		$result["http_code"] = 200;
		$result["result"] = $list_registers;
	}else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al obtener las cuentas.";
	}
} */


//*******************************************************************************************************************
//*******************************************************************************************************************
// BUSCAR DATOS CLIENTE VERIFICADO
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "busqueda_api_cliente_televentas") {
	include("function_replace_invalid_caracters.php");

    $busqueda_tipo  = $_POST["tipo"];
    $busqueda_valor = $_POST["valor"];
	$result["mensaje"] = "";
	$val_nombres = "";
	$val_apellido_paterno = "";
	$val_apellido_materno = "";
	$val_documento = "";


	//TIPO DOC
	if ((int) $busqueda_tipo >= 0 && (int) $busqueda_tipo <= 2) {
		// Si es DNI usamos la API de DNI
		if ((int) $busqueda_tipo === 0) {
			$result_api_dni = tlv_devol_get_cliente_por_dni($busqueda_valor);
			if((!is_array($result_api_dni)) or (empty($result_api_dni))){
				$result["mensaje"] = "El DNI no existe. Debe registrarlo manualmente";
				$val_documento = (int)$busqueda_valor;
			}else{
				if(!(isset($result_api_dni["dni"]))) {
					if(isset($result_api_dni["message"])) {	
						$result["http_code"] = 200;
						$result["status"] = "Cuotas no disponibles para la busqueda. Por favor comunicarse con el canal digital y/o soporte.";
						//echo json_encode($result); exit();
					}
					$val_documento = (int)$busqueda_valor;

						$result["http_code"] = 200;
						$result["status"] = "No se encontro al DNI.";
					//	echo json_encode($result); exit();
				}

				if ((int) $result_api_dni["dni"] === (int) $busqueda_valor) {
					$val_nombres = strtoupper(replace_invalid_caracters(trim($result_api_dni["nombres"])));
					$val_apellido_paterno = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_paterno"])));
					$val_apellido_materno = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_materno"])));
					$val_documento = $result_api_dni["dni"];
				} else {
					$val_documento = (int)$busqueda_valor;
				}
			}
		} elseif ((int) $busqueda_tipo === 1 || (int) $busqueda_tipo === 2) {
				$val_documento = $busqueda_valor;
		}
	}

	$query_2 = "SELECT 
				'0' id,
				'" . $val_nombres . "' nombre,
				'" . $val_apellido_paterno . "' apellido_paterno,
				'" . $val_apellido_materno . "' apellido_materno,
				'" . $busqueda_tipo . "' tipo_doc,
				'" . $val_documento . "' num_doc,
				'' web_id,
				'' web_full_name,
				'' player_id,
				'' calimaco_id,
				'' telefono,
				'' fec_nac,
				'' block_user_id,
				'' cc_id,
				'10000' bono_limite,
				'' updated_at,
				'' block_hash,
				1 tipo_balance_id,
				0 validate_web_id
				";
	$list_query_2 = $mysqli->query($query_2);
	$list_2 = array();
	while ($li_2 = $list_query_2->fetch_assoc()) {
		$list_2[] = $li_2;
	}

	if (count($list_2) === 0) {
		$result["http_code"] = 400;
		$result["status"] = "Datos no obtenidos.";
		$result["result"] = "No se pudo registrar al cliente.";
	} else if (count($list_2) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de la API de DNI.";

        $res_web_id = api_calimaco_get_web_id($list_2[0]["num_doc"]);
        if((int)$res_web_id['http_code']===200){
			$list_2[0]["web_id"] = $res_web_id["WebId"];
			$list_2[0]["web_full_name"] = $res_web_id["WebFullName"];
        }
		$result["result"] = $list_2[0];

	} else{
		$result["http_code"] = 400;
		$result["status"] = "Datos no obtenidos.";
		$result["result"] = "*No se pudo registrar al cliente.";
	}

}


//*******************************************************************************************************************
// GUARDAR DEPOSITO
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_transaccion_devolucion") {
	include("function_replace_invalid_caracters.php");

	$cuenta_at = $_POST["cuenta_at"];
	$banco_cliente = $_POST["banco_cliente"];
	$num_cuenta_cliente = $_POST["num_cuenta_cliente"];
	$cci_cliente = $_POST["cci_cliente"];
	$cajero = $_POST["cajero"];
	$supervisor = $_POST["supervisor"];
	$motivo = $_POST["motivo"];
	$monto = $_POST["monto"];
	// $fecha_devolucion = $_POST["fecha_devolucion"];
	$link_callbell = replace_invalid_caracters($_POST["link_callbell"]);
	$observacion = replace_invalid_caracters($_POST["observacion"]);

	$tipo_doc = $_POST["tipo_doc"];
	$num_doc = $_POST["num_doc"];
	$celular = $_POST["celular"];
	$cliente_nombre = $_POST["cliente_nombre"];
	$cliente_apepaterno = $_POST["cliente_apepaterno"];
	$cliente_apematerno = $_POST["cliente_apematerno"];


	if (!((float) $monto > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Montos incorrectos.";
		$result["monto"] = ((float) $monto > 0) ? true : false;
		echo json_encode($result);exit();
	}

	if ( strlen($celular) != 9 ) {
		$result["http_code"] = 400;
		$result["status"] = "El celular debe contener 9 dígitos.";
		echo json_encode($result);exit();
	}

	if ((int) $tipo_doc >= 0 && (int) $tipo_doc <= 2) {
		$where = "tipo_doc='" . $tipo_doc . "' AND num_doc='" . $num_doc . "'";
	}

	$query_1 = "
		SELECT 
			c.id,
			UPPER(IFNULL(c.nombre, '')) nombre,
			UPPER(IFNULL(c.apellido_paterno, '')) apellido_paterno,
			UPPER(IFNULL(c.apellido_materno, '')) apellido_materno
		FROM tbl_televentas_clientes c 
		WHERE " . $where . " 
		ORDER BY id ASC
		";
	$list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
		echo json_encode($result);exit();
	} else {
		while ($li_1 = $list_query_1->fetch_assoc()) {
			$list_1[] = $li_1;
		}
	}

	if (count($list_1) > 0){
		$result["http_code"] = 400;
		$result["status"] = "El número de documento se encuentra registrado como cliente de Televentas, favor de registrar otro documento.";
		$result["result"] = $list_1[0];
		echo json_encode($result); exit(); 
	}

    $insert_command = " 
        INSERT INTO tbl_televentas_devoluciones (
            tipo_doc,
            num_doc,
            apellidos,
            nombres,
            celular,
            cuenta_at,
            banco_cliente,
            cuenta_cliente,
            cci,
			id_cajero,
			id_supervisor,
			motivo_id,
            monto,
            link_call_bell,
            observacion,
            estado,
            id_user,
            created_at
        ) VALUES (
            '" . $tipo_doc . "',
            '" . $num_doc . "',
            '" . $cliente_apepaterno . " " . $cliente_apematerno . "',
            '" . $cliente_nombre . "',
            '" . $celular . "',
            '" . $cuenta_at . "',
            '" . $banco_cliente . "',
            '" . $num_cuenta_cliente . "',
            '" . $cci_cliente . "',
            " . $cajero . ",
            " . $supervisor . ",
            " . $motivo . ",
            " . $monto . ",
            '" . $link_callbell . "',
            '" . $observacion . "',
            1,
            '" . $usuario_id . "',
            '".date('Y-m-d H:i:s')."'
        )
        ";
    $mysqli->query($insert_command);

    if ($mysqli->error) {
        $result["insert_command"] = $insert_command;
        $result["insert_error"] = $mysqli->error;
    }

    $query_3 = "SELECT id FROM tbl_televentas_devoluciones  ";
    $query_3 .= " WHERE tipo_doc = $tipo_doc ";
    $query_3 .= " AND num_doc='" . $num_doc . "' AND apellidos='" . $cliente_apepaterno . " " . $cliente_apematerno . "' ";
    $query_3 .= " AND nombres='" . $cliente_nombre . "' AND celular='" . $celular . "' ";
    $query_3 .= " AND cuenta_at='" . $cuenta_at . "' AND banco_cliente='" . $banco_cliente . "' ";
    $query_3 .= " AND cuenta_cliente='" . $num_cuenta_cliente . "' ";
    $query_3 .= " AND cci='" . $cci_cliente . "' AND monto='" . $monto . "' ";
    $query_3 .= " AND link_call_bell='" . $link_callbell . "' AND observacion='" . $observacion . "' ";
    $query_3 .= " ORDER BY id DESC LIMIT 1 ";
    //$result["consulta_query"] = $query_3;
    $list_query = $mysqli->query($query_3);
    $list_transaccion = array();
    while ($li = $list_query->fetch_assoc()) {
        $list_transaccion[] = $li;
    }

    if ($mysqli->error) {
        //print_r($mysqli->error);
        $result["consulta_error"] = $mysqli->error;
    }

    if (count($list_transaccion) === 0) {
        $result["http_code"] = 400;
        $result["status"] = "No se guardó la transacción.";
    } elseif (count($list_transaccion) === 1) {
        $transaccion_id = $list_transaccion[0]["id"];

        $result["http_code"] = 200;
        $result["status"] = "ok";
        $result["result"] = "Solicitud de Depósito Registrada";
        $result["data"] = $list_transaccion;

        //**************************************************************************************************
        //**************************************************************************************************
        // IMAGEN
        //**************************************************************************************************
        $path = "/var/www/html/files_bucket/depositos/";
        $file = [];
        $imageLayer = [];
        if (!is_dir($path))
            mkdir($path, 0777, true);
        $imageProcess = 0;

        $filename = $_FILES['imagen_voucher']['tmp_name'];
        $filenametem = $_FILES['imagen_voucher']['name'];
        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
        if ($filename != "") {
            $fileExt = pathinfo($_FILES['imagen_voucher']['name'], PATHINFO_EXTENSION);
            $resizeFileName = $transaccion_id . "_" . date('YmdHis');
            $nombre_archivo = $resizeFileName . "." . $fileExt;
            if ($fileExt == "pdf") {
                move_uploaded_file($_FILES['imagen_voucher']['tmp_name'], $path . $nombre_archivo);
            } else {
                $sourceProperties = getimagesize($filename);
                $size = $_FILES['imagen_voucher']['size'];
                $uploadImageType = $sourceProperties[2];
                $sourceImageWith = $sourceProperties[0];
                $sourceImageHeight = $sourceProperties[1];
                switch ($uploadImageType) {
                    case IMAGETYPE_JPEG:
                        $resourceType = imagecreatefromjpeg($filename);
                        break;
                    case IMAGETYPE_PNG:
                        $resourceType = imagecreatefrompng($filename);
                        break;
                    case IMAGETYPE_GIF:
                        $resourceType = imagecreatefromgif($filename);
                        break;
                    default:
                        $imageProcess = 0;
                        break;
                }
                $imageLayer = tlv_devol_resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

                $file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
                $file[1] = imagegif($imageLayer[1], $path . "min_" . $nombre_archivo);
                move_uploaded_file($file[0], $path . $nombre_archivo);
                move_uploaded_file($file[1], $path . $nombre_archivo);
                $imageProcess = 1;
            }

            /* $comando = " INSERT INTO tbl_televentas_transaccion_archivos
                            (transaccion_id,tipo,archivo,created_at,estado)
                            VALUES(
                                '" . $transaccion_id . "',
                                1,
                                '" . $nombre_archivo . "',
                                '" . date('Y-m-d H:i:s') . "',
                                1
                                )"; */
			$comando = " 
				UPDATE
					tbl_televentas_devoluciones 
				SET
					name_file = '" . $nombre_archivo . "'
				WHERE
					id = '" . $transaccion_id . "'
			";
            $mysqli->query($comando);
            $archivo_id = mysqli_insert_id($mysqli);
            $filepath = $path . $resizeFileName . "." . $fileExt;
        }
        //**************************************************************************************************
        //**************************************************************************************************
    } else {
        $result["http_code"] = 400;
        $result["status"] = "Ocurrió un error al guardar la transacción.";
    }

}


function tlv_devol_get_cliente_por_dni($dni) {
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.apuestatotal.com/v2/dni?dni=" . $dni,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_HTTPHEADER => [
			"Accept: application/json",
			"Authorization: Bearer " . env('TELEVENTAS_API_TOKEN')
		],
	]);
	$response = json_decode(curl_exec($curl), true);
	$err = curl_error($curl);
	curl_close($curl);
	$consulta = ($response["result"] ?? []);
	return $consulta;
}


function tlv_devol_resizeImage($resourceType, $image_width, $image_height) {
	$imagelayer = [];
	if ($image_width < 1920 && $image_height < 1080) {
		//mini
		$resizewidth_mini = 100;
		$resizeheight_mini = 100;
		$imagelayer[0] = imagecreatetruecolor($image_width, $image_height);
		imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $image_width, $image_height, $image_width, $image_height);
		//mini
		$imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
		imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
	} else {
		$ratio = $image_width / $image_height;
		$escalaW = 1920 / $image_width;
		$escalaH = 1080 / $image_height;
		if ($ratio > 1) {
			$resizewidth = $image_width * $escalaW;
			$resizeheight = $image_height * $escalaW;
		} else {
			$resizeheight = $image_height * $escalaH;
			$resizewidth = $image_width * $escalaH;
		}
		//mini
		$resizewidth_mini = 100;
		$resizeheight_mini = 100;
		$imagelayer[0] = imagecreatetruecolor($resizewidth, $resizeheight);
		imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $resizewidth, $resizeheight, $image_width, $image_height);
		//mini
		$imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
		imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
	}
	return $imagelayer;
}


if ($_POST["accion"] === "listar_devoluciones") {

	$query_1 = "
		SELECT 
			td.id,
			IFNULL(td.tipo_doc, '') AS tipo_doc,
			IFNULL(td.num_doc, '') AS num_doc,
			IFNULL(td.nombres, '') AS nombres,
			IFNULL(td.apellidos, '') AS apellidos,
			IFNULL(td.celular, '') AS celular,
			IFNULL(td.banco_at, '') AS banco_at,
			cpr.nombre cuenta_at,
			IFNULL(td.banco_cliente, '') AS banco_cliente,
			IFNULL(td.cuenta_cliente, '') AS cuenta_cliente,
			IFNULL(td.cci, '') AS cci,
			td.monto monto,
			IFNULL(tm.motivo, '') motivo_id,
			CONCAT(
				IF
					( LENGTH( p2.apellido_paterno ) > 0, CONCAT( UPPER( p2.apellido_paterno ), ' ' ), '' ),
				IF
					( LENGTH( p2.apellido_materno ) > 0, CONCAT( UPPER( p2.apellido_materno ), ' ' ), '' ),
				IF
					( LENGTH( p2.nombre ) > 0, UPPER( p2.nombre ), '' ) 
				) id_cajero,
			IFNULL(p.nombre, '') supervisor,
			IFNULL(td.link_call_bell, '') AS link_call_bell,
			IFNULL(td.observacion, '') AS observacion,
			IFNULL(u.usuario, '') AS usuario,
			IFNULL(td.name_file, '') AS name_file,
			td.created_at
		FROM
            tbl_televentas_devoluciones td
			INNER JOIN tbl_usuarios u ON u.id = td.id_user
			INNER JOIN tbl_usuarios us ON us.id = td.id_supervisor
			LEFT JOIN tbl_personal_apt p ON us.personal_id = p.id
			INNER JOIN tbl_usuarios uc ON uc.id = td.id_cajero
			LEFT JOIN tbl_personal_apt p2 ON uc.personal_id = p2.id
			INNER JOIN tbl_televentas_cuentas_pago_retiro cpr ON cpr.id = td.cuenta_at
			INNER JOIN tbl_televentas_motivos tm ON td.motivo_id = tm.id
		WHERE
			td.estado = 1
		ORDER BY 
			td.created_at DESC
	";
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar transacciones.";
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay transacciones.";
		} elseif (count($list_transaccion) > 0) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list_transaccion;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar transacciones.";
		}
	}

}

	
if ($_POST["accion"]==="listar_supervisores_tlv") {
	$grupo_id = $login ? $login['grupo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;
	$where_usuario = "";
	$supervisor = 0;
	if($grupo_id == 31){
		$where_usuario = " AND u.id = " . $usuario_id;
		$supervisor = 1;
	}
	$query = "
		SELECT p.id, p.nombre 
		FROM tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		WHERE u.grupo_id = 31
		" . $where_usuario .  "
		ORDER BY p.id ASC
		";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos";
	$result["result"] = $list;
	$result["supervisor"] = $supervisor;
}

if ($_POST["accion"]==="listar_cajeros_tlv") {
	$query = "
		SELECT
			A.user_id as id,
			CONCAT(
			IF
				( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
			IF
				( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
			IF
				( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
			) nombre
		FROM
			( SELECT tr.user_id FROM tbl_televentas_clientes_transaccion tr WHERE tr.estado = 1 GROUP BY tr.user_id ) A
			JOIN tbl_usuarios u ON u.id = A.user_id
			JOIN tbl_personal_apt pl ON pl.id = u.personal_id
		";

	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_devolucion") {

	$devolucion_id = $_POST["devolucion_id"];

	$date_time = date('Y-m-d H:i:s');

	$usuario_id = $login ? $login['id'] : null;
	if ((int) $usuario_id > 0) {		
		$insert_command = " 
			UPDATE
				tbl_televentas_devoluciones 
			SET 
				estado = 0,
				updated_at = '" . $date_time . "',
				id_user_updated = '" . $usuario_id . "'
			WHERE
				id = $devolucion_id
				AND estado = 1
		";
		$mysqli->query($insert_command);
		if ($mysqli->error) {
			$result["insert_query"] = $insert_command;
			$result["insert_error"] = $mysqli->error;
		} else {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = "Devolución Eliminada";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}






echo json_encode($result);