<?php

include("db_connect.php");
include("sys_login.php");
require_once '/var/www/html/cron/cron_pdo_connect.php';
require_once '/var/www/html/env.php';

function action_response($code, $message=""){
	return json_encode(['code' => $code, 'message' => $message]);
}

$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'consultas' AND sub_sec_id = 'dni' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

if(isset($_POST["show_dni"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("request", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
	}

	$dni = $_POST["show_dni"]["dni"];
	$api = false;

	if(preg_match("/^[0-9]{8}$/", $dni)){
		$consulta = [];
		$result = $mysqli->query("
			SELECT
				id,
				id AS user_id,
				dni,
				nombres,
				apellido_paterno,
				apellido_materno
			FROM tbl_consultas_dni
			WHERE
				dni = '$dni';

		");
		while($r = $result->fetch_assoc()) $consulta = $r;

		if(empty($consulta)){
			$accessToken = env('SOPORTE_V2_TOKEN');

			$curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_URL => "https://api.apuestatotal.com/v2/dni?dni=" . $dni,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_HTTPHEADER => [
					"Accept: application/json",
					"Authorization: Bearer ".$accessToken
				],
			]);

			$response = json_decode(curl_exec($curl), true);
			$err = curl_error($curl);

			curl_close($curl);

			$consulta = ($response["result"] ?? []);
		}

		if(isset($consulta["dni"]) && $consulta["dni"] === $dni){
			if($api){
				// Construcción de la consulta con placeholders para mayor seguridad y claridad
				$queryInsert = "
				INSERT INTO tbl_consultas_dni (
					dni,
					nombres,
					apellido_paterno,
					apellido_materno,
					caracter_verificacion,
					caracter_verificacion_anterior,
					created_at,
					updated_at
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
				";

					// Preparar la consulta
					if ($stmt = $mysqli->prepare($queryInsert)) {
					// Vincular los parámetros
					$date = date('Y-m-d H:i:s');
					$stmt->bind_param(
						'ssssssss',
						$consulta["dni"],
						$consulta["nombres"],
						$consulta["apellido_paterno"],
						$consulta["apellido_materno"],
						$consulta["caracter_verificacion"],
						$consulta["caracter_verificacion_anterior"],
						$date,
						$date
					);

					if ($stmt->execute()) {
						$id_creado = $stmt->insert_id;
						$consulta["user_id"] = $id_creado;
						$consulta["id"] = $consulta["user_id"];

					} //else die(action_response('404', 'Error en la ejecución de la consulta'));			

					// Cerrar la declaración
					$stmt->close();
				} 	//else die(action_response('404', 'Error en la preparación de la consulta'));			

			}

			$query = "
				SELECT 
					col_Id as Id,
					col_Email as Email,
					col_IsLocked as IsLocked,
					col_FirstName as FirstName,
					col_LastName as LastName,
					col_DocNumber AS DocNumber
				FROM bc_apuestatotal.tbl_Client 
				WHERE col_DocNumber = '{$dni}'
				LIMIT 1
			";
			$bc_user = [];
			$query_result = $mysqli->query($query);
			if($mysqli->error){ echo $mysqli->error; die; }
			while($r = $query_result->fetch_assoc()) {
				$bc_user[$r["DocNumber"]] = $r;
				// $bc_user = $r;	
			}

			$web_user = false;
			if(array_key_exists($dni, $bc_user)){
				$web_user=$bc_user[$dni];
				$consulta["Id"]=$web_user["Id"];
				$consulta["Email"]=$web_user["Email"];
				$consulta["IsLocked"]=($web_user["IsLocked"]?"Si":"No");
				$consulta["FirstName"]=$web_user["FirstName"];
				$consulta["LastName"]=$web_user["LastName"];
			}

			// INICIO INFORMACION CALIMACO
			require("/var/www/html/cron/cron_bc_connect.php");
			$query_calimaco = "
				SELECT
					user AS calimaco_user_id,
				    email AS calimaco_email, 
				    verified AS calimaco_verificado, 
				    first_name AS calimaco_nombre, 
				    last_name AS calimaco_apellido,
				    national_id AS calimaco_national_id
				FROM data.users
				WHERE national_id = '{$dni}'
				LIMIT 1
			";

			$calimaco_user = [];
			
			$query_result_calimaco = $mysqli->query($query_calimaco);
			
			if($mysqli->error)
			{
				echo $mysqli->error;
				die;
			}
			
			while($res = $query_result_calimaco->fetch_assoc())
			{
				$calimaco_user[$res["calimaco_national_id"]] = $res;
			}

			$web_user_calimaco = false;
			
			if(array_key_exists($dni, $calimaco_user))
			{
				$web_user_calimaco = $calimaco_user[$dni];
				$consulta["calimaco_user_id"] = $web_user_calimaco["calimaco_user_id"];
				$consulta["calimaco_email"] = $web_user_calimaco["calimaco_email"];
				$consulta["calimaco_verificado"] = ($web_user_calimaco["calimaco_verificado"] == 1) ? "Si" : "No";
				$consulta["calimaco_nombre"] = $web_user_calimaco["calimaco_nombre"];
				$consulta["calimaco_apellido"] = $web_user_calimaco["calimaco_apellido"];
			}

			// FIN INFORMACION CALIMACO

			//	INICIO BOTONES DE OPCIONES
			if(in_array("edit_and_view", $usuario_permisos[105])){
				$botones = '<a onclick="sec_consultas_get('.$consulta["user_id"].')";
										class="btn btn-warning btn-sm" style="margin-right: 10px;"
										data-toggle="tooltip" data-placement="top" title="Editar">
										<span class="fa fa-pencil"></span>
									</a>
									<a onclick="sec_consultas_dni_change_log('.$consulta["id"].');";
										class="btn btn-primary btn-sm"
										data-toggle="tooltip" data-placement="top" title="Historial de cambios">
										<span class="fa fa-history"></span>
									</a>';
			}

			//	FIN BOTONES DE OPCIONES

			$body = "";

			if($web_user_calimaco)
			{
				$body .= '<div class="col-lg-offset-0 col-lg-12 col-xs-12">';
			}
			else if($web_user)
			{
				$body .= '<div class="col-lg-offset-0 col-lg-12 col-xs-12">';
			}
			else{
				$body .= '<div class="col-lg-offset-2 col-lg-8 col-xs-12">';
			}
			$body .= '<table class="table table-striped table-condensed table-hover">';
			$body .= '<thead>';
			$body .= '<tr class="bg-primary">';
			$body .= '<th style="color:white;">DNI</th>';
			$body .= '<th style="color:white;">NOMBRES</th>';
			$body .= '<th style="color:white;">APELLIDO PATERNO</th>';
			$body .= '<th style="color:white;">APELLIDO MATERNO</th>';
			if($web_user){
				$body .= '<th style="color:white;" class="bg-success">WEB_USERID</th>';
				$body .= '<th style="color:white;" class="bg-success">WEB_EMAIL</th>';
				$body .= '<th style="color:white;" class="bg-success">WEB_ISLOCKED</th>';
				$body .= '<th style="color:white;" class="bg-success">WEB_NAME</th>';
				$body .= '<th style="color:white;" class="bg-success">WEB_LASTNAME</th>';
			}
			if($web_user_calimaco)
			{
				$body .= '<th style="color:white;" class="bg-danger">USERID</th>';
				$body .= '<th style="color:white;" class="bg-danger">EMAIL</th>';
				$body .= '<th style="color:white;" class="bg-danger">VERIFIED</th>';
				$body .= '<th style="color:white;" class="bg-danger">NAME</th>';
				$body .= '<th style="color:white;" class="bg-danger">LASTNAME</th>';	
			}
			if(in_array("edit_and_view", $usuario_permisos[105])){
				$body .= '<th style="color:white;">OPCIONES</th>';
			}
			// $body .= '<th>CARACTER VERIFICACION</th>';
			// $body .= '<th>CARACTER VERIFICACION ANTERIOR</th>';
			$body .= '</tr>';
			$body .= '</thead>';
			$body .= '<tbody>';
			$body .= '<tr>';
			$body .= '<td>'.$consulta["dni"].'</td>';
			$body .= '<td>'.$consulta["nombres"].'</td>';
			$body .= '<td>'.$consulta["apellido_paterno"].'</td>';
			$body .= '<td>'.$consulta["apellido_materno"].'</td>';
			if($web_user){
				$body .= '<td>'.$consulta["Id"].'</td>';
				$body .= '<td>'.$consulta["Email"].'</td>';
				$body .= '<td>'.$consulta["IsLocked"].'</td>';
				$body .= '<td>'.$consulta["FirstName"].'</td>';
				$body .= '<td>'.$consulta["LastName"].'</td>';
			}
			if($web_user_calimaco)
			{
				$body .= '<td>'.$consulta["calimaco_user_id"].'</td>';
				$body .= '<td>'.$consulta["calimaco_email"].'</td>';
				$body .= '<td>'.$consulta["calimaco_verificado"].'</td>';
				$body .= '<td>'.$consulta["calimaco_nombre"].'</td>';
				$body .= '<td>'.$consulta["calimaco_apellido"].'</td>';
			}
			$body .= '<td>'.$botones.'</td>';
			// $body .= '<td>'.$consulta["caracter_verificacion"].'</td>';
			// $body .= '<td>'.$consulta["caracter_verificacion_anterior"].'</td>';
			$body .= '</tr>';
			$body .= '</tbody>';
			$body .= '</table>';
			$body .= '</div>';
			echo die(action_response('200', $body));
		}
		else die(action_response('404', $consulta));
	}
	else die(action_response('400', 'DNI Inválido. Por Favor Digitar los 8 dígitos del DNI.'));
}

elseif(isset($_FILES['fileDNIUpload'])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("import", $usuario_permisos[$menu_id])) die(action_response('403', 'No tienes permisos para subir archivos.'));

	$filepath = $_FILES['fileDNIUpload']['tmp_name'];
	$ext = strtolower(pathinfo($_FILES["fileDNIUpload"]["name"], PATHINFO_EXTENSION));
	if($ext != 'csv') die(action_response('422', 'Formato Inválido de archivo. Solamente archivos CSV.'));

	$csvFile = array_map('str_getcsv', preg_replace('/[\x00-\x1F\x80-\xFF]/', '', file($filepath)));
	if(empty($csvFile)) die(action_response('204', 'Archivo vacio o fuera del padrón aceptado. Revisar'));

	$body = "";
	$body .= '<table class="table table-striped table-hover">';
	$body .= '<thead>';
	$body .= '<tr class="bg-primary">';
	$body .= '<th style="color:white;">DNI</th>';
	$body .= '<th style="color:white;">NOMBRES</th>';
	$body .= '<th style="color:white;">APELLIDO PATERNO</th>';
	$body .= '<th style="color:white;">APELLIDO MATERNO</th>';
	$body .= '<th style="color:white;" class="bg-success">WEB_USERID</th>';
	$body .= '<th style="color:white;" class="bg-success">WEB_EMAIL</th>';
	$body .= '<th style="color:white;" class="bg-success">WEB_ISLOCKED</th>';
	$body .= '<th style="color:white;" class="bg-success">WEB_NAME</th>';
	$body .= '<th style="color:white;" class="bg-success">WEB_LASTNAME</th>';
	$body .= '<th style="color:white;">OPCIONES</th>';
	// $body .= '<th>CARACTER VERIFICACION</th>';
	// $body .= '<th>CARACTER VERIFICACION ANTERIOR</th>';
	$body .= '</tr>';
	$body .= '</thead>';
	$body .= '<tbody>';

	foreach ($csvFile as $line){
		$dni = explode(';', $line[0])[0];

		$body .= '<tr>';
		if(!preg_match("/^[0-9]{8}$/", $dni)){
			$body .= '<td class="bg-danger" style="color:white">'.$dni.'</td>';
			$body .= '<td class="bg-danger" style="color:white">DNI Inválido</td>';
			$body .= '<td class="bg-danger" style="color:white"></td>';
			$body .= '<td class="bg-danger" style="color:white"></td>';
			// $body .= '<td></td>';
			// $body .= '<td></td>';
		}
		else{
			$consulta = [];
			$result = $mysqli->query("
				SELECT
					dni,
					nombres,
					apellido_paterno,
					apellido_materno
				FROM tbl_consultas_dni
				WHERE
					dni = '$dni';
			");
			while($r = $result->fetch_assoc()) $consulta = $r;

			if(!empty($consulta)){
				$query = "
				    SELECT 
						col_Id as Id,
						col_Email as Email,
						col_IsLocked as IsLocked,
						col_FirstName as FirstName,
						col_LastName as LastName,
						col_DocNumber AS DocNumber
					FROM bc_apuestatotal.tbl_Client WHERE col_DocNumber = '{$dni}'
				";
				$bc_user = [];
				$query_result = $mysqli->query($query);
				if($mysqli->error){ echo $mysqli->error; die; }
				while($r = $query_result->fetch_assoc()) {
					$bc_user[$r["DocNumber"]] = $r;
				}
				
				$web_user = false;
				$consulta["Id"]="NO WEB";
				$consulta["Email"]="NO WEB";
				$consulta["IsLocked"]="NO WEB";
				$consulta["FirstName"]="NO WEB";
				$consulta["LastName"]="NO WEB";
				if(array_key_exists($dni, $bc_user)){
					$web_user=$bc_user[$dni];
					$consulta["Id"]=$web_user["Id"];
					$consulta["Email"]=$web_user["Email"];
					$consulta["IsLocked"]=($web_user["IsLocked"]?"Si":"No");
					$consulta["FirstName"]=$web_user["FirstName"];
					$consulta["LastName"]=$web_user["LastName"];
				}
				$body .= '<td>'.$consulta["dni"].'</td>';
				$body .= '<td>'.$consulta["nombres"].'</td>';
				$body .= '<td>'.$consulta["apellido_paterno"].'</td>';
				$body .= '<td>'.$consulta["apellido_materno"].'</td>';

					$body .= '<td>'.$consulta["Id"].'</td>';
					$body .= '<td>'.$consulta["Email"].'</td>';
					$body .= '<td>'.$consulta["IsLocked"].'</td>';
					$body .= '<td>'.$consulta["FirstName"].'</td>';
					$body .= '<td>'.$consulta["LastName"].'</td>';
				// $body .= '<td>'.$consulta["caracter_verificacion"].'</td>';
				// $body .= '<td>'.$consulta["caracter_verificacion_anterior"].'</td>';
			}
			else{
				if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("generate", $usuario_permisos[$menu_id])){
					$body .= '<td class="bg-danger" style="color:white">'.$dni.'</td>';
					$body .= '<td class="bg-danger" style="color:white">Acceso API no permitido.</td>';
					$body .= '<td class="bg-danger" style="color:white"></td>';
					$body .= '<td class="bg-danger" style="color:white"></td>';
					// $body .= '<td></td>';
					// $body .= '<td></td>';
				}
				else{
					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, 'https://consulta.pe/api/reniec/dni');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["dni" => $dni]));
					curl_setopt($ch, CURLOPT_POST, 1);

					$headers = array();
					$headers[] = 'Content-Type: application/json';
					$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjNkNDIzOWNkMGFlYjc2NDZjYjM2N2MwNjk0MzA3M2E4MDk2MWJlMmQ4MmYxYmM0ZmM2NmZmODU3MWUyNzZhMzExZTc5YjI5ZTYwNDU5MmRkIn0.eyJhdWQiOiIxIiwianRpIjoiM2Q0MjM5Y2QwYWViNzY0NmNiMzY3YzA2OTQzMDczYTgwOTYxYmUyZDgyZjFiYzRmYzY2ZmY4NTcxZTI3NmEzMTFlNzliMjllNjA0NTkyZGQiLCJpYXQiOjE1ODcxMzM2MzEsIm5iZiI6MTU4NzEzMzYzMSwiZXhwIjoxNjE4NjY5NjMxLCJzdWIiOiIxMDMyIiwic2NvcGVzIjpbInVzZS1yZW5pZWMiXX0.tmD_mdFV-HPyzT_fFvGAHOWRtHft8uokQUqn0kJO2sWzCbY7-oF6YIqOsbyNmrNe98q8WVIrefL7m2v8HtRaiN0ZZ_i8cyNJaEyXgj0uhOlITETNOyXOWi_7t-NHpFnIEcwjPt50BKDayg7TUWTz4NM6jHYsYtFZTeoB-N71TAa7og1iRaY_GZ6yxOAHVfa7sSxM0pjNVgN1jh0IXY1JVCvdbWSz__-t52YJdRwMAehArViaYH0kefkPd3tRIt9Pb-mtEa1lI7BjLzcJcVDIav78rAsBvV_0-to2Zw19FnEQg7kv3r30_xqDHRWGgp6NzawINKT46pExpVpZh57f_kyno9dsHcw5z0GAu7J80wFuKjk8JVh3HYWHLcSLZeFNcnz_0x4_AKsAdqy1prl2ogvHaSdDVM0v4jYtPZ_lWrOhjbyHB-vhm0sgZiyA_hKdULJ3sgdOllJfIxVw6pfu3KcTNaYdxqtKMhWc9SIQq7YCZ4NQ-vLYe8z3CQ14wQIqOl41B1W_vlAAhRqdUZJ30MtIfmoBDMtgD49YjXCgExIGoNLlICndgzwB_JB4avpXvTBH_-gRoOhdI-UAL2dL4TrAc6F60428kJSAz6IGDCiEIGjENsCCVTmCuEeSxiBIg3nlHp1iEJV_fUty_mBK-N0uG_C42sDXMylUcy6SvCw';
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$consulta = json_decode(curl_exec($ch), true);
					if (curl_errno($ch)) {
						echo 'Error:' . curl_error($ch);
					}
					curl_close($ch);

					if(isset($consulta["dni"]) && $consulta["dni"] === $dni){
						$mysqli->query("
							INSERT INTO tbl_consultas_dni (
								dni,
								nombres,
								apellido_paterno,
								apellido_materno,
								caracter_verificacion,
								caracter_verificacion_anterior,
								created_at
							) VALUES(
								'".$consulta["dni"]."',
								'".$consulta["nombres"]."',
								'".$consulta["apellido_paterno"]."',
								'".$consulta["apellido_materno"]."',
								'".$consulta["caracter_verificacion"]."',
								'".$consulta["caracter_verificacion_anterior"]."',
								'".date('Y-m-d H:i:s')."'
							)
						");

						// $bc_user_command = "SELECT TOP(1) * FROM Client WHERE DocNumber = '{$dni}'";
						// $bc_user = pdoStatement($bc_user_command,"DocNumber");

						$query = "
						    SELECT 
								col_Id as Id,
								col_Email as Email,
								col_IsLocked as IsLocked,
								col_FirstName as FirstName,
								col_LastName as LastName,
								col_DocNumber AS DocNumber
						 	FROM bc_apuestatotal.tbl_Client WHERE col_DocNumber = '{$dni}'
						";
						$bc_user = [];
						$query_result = $mysqli->query($query);
						if($mysqli->error){ echo $mysqli->error; die; }
						while($r = $query_result->fetch_assoc()) {
							$bc_user[$r["DocNumber"]] = $r;
							// $bc_user = $r;
						}
						$web_user = false;
						$consulta["Id"]="NO WEB";
						$consulta["Email"]="NO WEB";
						$consulta["IsLocked"]="NO WEB";
						$consulta["FirstName"]="NO WEB";
						$consulta["LastName"]="NO WEB";
						if(array_key_exists($dni, $bc_user)){
							$web_user=$bc_user[$dni];
							$consulta["Id"]=$web_user["Id"];
							$consulta["Email"]=$web_user["Email"];
							$consulta["IsLocked"]=($web_user["IsLocked"]?"Si":"No");
							$consulta["FirstName"]=$web_user["FirstName"];
							$consulta["LastName"]=$web_user["LastName"];
						}
						// $body = "";
						// if($web_user){
						// 	$body .= '<div class="col-lg-offset-0 col-lg-12 col-xs-12">';
						// }else{
						// 	$body .= '<div class="col-lg-offset-2 col-lg-8 col-xs-12">';
						// }

						$body .= '<td>'.$consulta["dni"].'</td>';
						$body .= '<td>'.$consulta["nombres"].'</td>';
						$body .= '<td>'.$consulta["apellido_paterno"].'</td>';
						$body .= '<td>'.$consulta["apellido_materno"].'</td>';

						$body .= '<td>'.$consulta["Id"].'</td>';
						$body .= '<td>'.$consulta["Email"].'</td>';
						$body .= '<td>'.$consulta["IsLocked"].'</td>';
						$body .= '<td>'.$consulta["FirstName"].'</td>';
						$body .= '<td>'.$consulta["LastName"].'</td>';
						// $body .= '<td>'.$consulta["caracter_verificacion"].'</td>';
						// $body .= '<td>'.$consulta["caracter_verificacion_anterior"].'</td>';
					}
					else{
						$body .= '<td class="bg-danger" style="color:white">'.$dni.'</td>';
						$body .= '<td class="bg-danger" style="color:white" colspan="8">'.$consulta.'</td>';
						// $body .= '<td class="bg-danger" style="color:white"></td>';
						// $body .= '<td class="bg-danger" style="color:white"></td>';
						// $body .= '<td class="bg-danger" style="color:white"></td>';
						// $body .= '<td class="bg-danger" style="color:white"></td>';
						// $body .= '<td class="bg-danger" style="color:white"></td>';
						// $body .= '<td class="bg-danger" style="color:white"></td>';
						// $body .= '<td class="bg-danger" style="color:white"></td>';
						// $body .= '<td></td>';
						// $body .= '<td></td>';
					}
				}
			}
		}
		$body .= '</tr>';
	}

	$body .= '</tbody>';
	$body .= '</table>';
	echo die(action_response('200', $body));
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consulta_change_log") {
    $user_id = $_POST['user_id'];

    try {

        $selectQuery = " SELECT 
                            ch.id,
                            ch.valor_anterior,
                            ch.valor_nuevo,
                            ifnull(ncc.nombre_campo,''),
                            DATE_FORMAT(ch.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
                            u.usuario
                        FROM tbl_consultas_dni_historial_cambios ch
                        LEFT JOIN tbl_usuarios u ON ch.user_created_id = u.id
                        LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
                        LEFT JOIN tbl_consultas_dni_campo ncc ON ncc.campo = ch.nombre_campo
                        WHERE ch.status =1 AND ch.dni_id = ?
                        ORDER BY ch.created_at DESC" ; 
        //echo $selectQuery;
        $stmt = $mysqli->prepare($selectQuery);

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($id, $valor_anterior, $valor_nuevo, $nombre_campo, $fecha_registro, $usuario);

        $data = [];

        while ($stmt->fetch()) {

            $data[] = [
                "0" => count($data) + 1,
                "1" => $valor_anterior,
                "2" => $valor_nuevo,
                "3" => $nombre_campo,
                "4" => $fecha_registro,
                "5" => $usuario
            ];
        }

        $stmt->close();

        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data,
        ];

        echo json_encode($resultado);
    } catch (Exception $e) {
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => [
                [
                    "0" => "error",
                    "1" => 'Comunicarse con Soporte, error: ' . $e->getMessage(),
                    "2" => '',
                    "3" => '',
                    "4" => '',
                    "5" => ''
                ],
            ],
        ];

        echo json_encode($resultado);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consulta_user_get") {
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

    if ($user_id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    c.id, 
					c.dni,
                    c.nombres,
                    c.apellido_paterno,
                    c.apellido_materno
                FROM tbl_consultas_dni c
				WHERE c.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result(
                                $id, 
								$dni,
                                $nombre, 
                                $apellido_paterno,
                                $apellido_materno
                            );
                                                 
            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $id,
						'dni' => $dni,
                        'nombre' => $nombre,
                        'apellido_paterno' => $apellido_paterno,
                        'apellido_materno' => $apellido_materno
                    ],
                ]);
            } else {
                echo json_encode([
                    'status' => 404,
                    'message' => 'No se encontraron datos para el ID proporcionado.',
                ]);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                'status' => 500,
                'message' => 'Error en la consulta SQL: ' . $e->getMessage(),
            ]);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'ID no válido']);
    }
}


function build_response(){

}

?>
