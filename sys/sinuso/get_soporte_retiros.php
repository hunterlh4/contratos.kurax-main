<?php

include("db_connect.php");
include("sys_login.php");
include("/var/www/html/cron/cron_bc_leech.php");

function action_response($code, $message=""){
	return json_encode(['code' => $code, 'message' => $message]);
}

$this_menu = $mysqli->query("
	SELECT id 
	FROM tbl_menu_sistemas 
	WHERE sec_id = 'soporte' 
	AND sub_sec_id = 'retiros' 
	LIMIT 1
")->fetch_assoc();

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
				dni,
				nombres,
				apellido_paterno,
				apellido_materno
			FROM tbl_consultas_dni
			WHERE dni = '$dni';
		");
		while($r = $result->fetch_assoc()) $consulta = $r;

		if(empty($consulta)){
			if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("generate", $usuario_permisos[$menu_id])){
				die(action_response('401', 'No Autorizado. Solo puedes buscar DNIs contenidos en nuestra base de datos.'));
			}

			$accessToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImRhOGIyMzllNjQ2YzRmNTM2MDAzZDFmMTNkYTZiZWUxN2FlMmUwNTIyODM1MWRlZjU2OWQyMTNlMTkwZDZmODg2ZDU1ZGNjZWUwN2FiZGRjIn0.eyJhdWQiOiIxIiwianRpIjoiZGE4YjIzOWU2NDZjNGY1MzYwMDNkMWYxM2RhNmJlZTE3YWUyZTA1MjI4MzUxZGVmNTY5ZDIxM2UxOTBkNmY4ODZkNTVkY2NlZTA3YWJkZGMiLCJpYXQiOjE1OTkyNzA2NDUsIm5iZiI6MTU5OTI3MDY0NSwiZXhwIjoxNjMwODA2NjQ1LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.IROpjLb_oSxGsVHduml2KUXKD7pTL5_DDZfNAnLhS-pL2tGgpbSYXgQayYHlwjGgbVCm8eEbpO00fFrSVWEkvmHcvt1xxhmvA8Y5vL6FI7XoJluo5T-UhXPwQIVgDfgygGQoWFATkTGF4SQ9MpjWANgav2GKgx-VjP6ucWrMn1ObSyFOtLf8C_IeI2JlAC-MzEJ3vlWqMaZ2HRU2V3msK6595L97eiUIpc7ruLOSjmDuPm_KoxtSwFEYd6UG0MNUPci6Ug0EheiuaZoHmwi--TQwHabm-vtqUzkWhwYaUEvXdeoxGYhJwUr_OX5ECEDfpo2Z5f0K7XKh6fpe5rq2NjU4U6I18kWZi1DTGfCsO41KNF6a5pxf5BOGBuxF-Zl7ixzzf_PxTZ7wGBj2tw8yBFOt-_8N7FyNaSMuFzNaWG5CCSgQtjsXktYqRx77v14RNctR7atTC09BYi0NVoSTIBeKqlMlRa8gqpG0bjofTZ1E2jhXzGyIoQkCj7UaJkMBTDwXhUZ65md8FY30aZ9AcSZaqo2HE0NgAu0oEMi4UwGNq04QsG4-DPMXZJyXBj2joOtzPdWOSkQjlpX_3fAknN28de7sSqd7pJa2fFs8cVH_rdq6_PKyzzvgpKN1RtMbJFzHO016F27-_vCeD24GCTSJFp56fXfmo21mgrq8Gb0";

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
				$mysqli->query("
					INSERT INTO tbl_consultas_dni (
						dni,
						nombres,
						apellido_paterno,
						apellido_materno,
						caracter_verificacion,
						caracter_verificacion_anterior,
						estado,
						created_at
					) VALUES(
						'".$consulta["dni"]."',
						'".$consulta["nombres"]."',
						'".$consulta["apellido_paterno"]."',
						'".$consulta["apellido_materno"]."',
						'".$consulta["caracter_verificacion"]."',
						'".$consulta["caracter_verificacion_anterior"]."',
						'1',
						'".date('Y-m-d H:i:s')."'
					)
				");
			}

			$body = "";
			$body .= '<table class="table table-striped table-hover">';
			$body .= '<thead>';
			$body .= '<tr class="bg-primary">';
			$body .= '<th style="color:white;">DNI</th>';
			$body .= '<th style="color:white;">NOMBRES</th>';
			$body .= '<th style="color:white;">APELLIDO PATERNO</th>';
			$body .= '<th style="color:white;">APELLIDO MATERNO</th>';
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
			// $body .= '<td>'.$consulta["caracter_verificacion"].'</td>';
			// $body .= '<td>'.$consulta["caracter_verificacion_anterior"].'</td>';
			$body .= '</tr>';
			$body .= '</tbody>';
			$body .= '</table>';
			echo die(action_response('200', $body));
		}
		else die(action_response('404', $consulta));
	}
	else die(action_response('400', 'DNI Inválido. Por Favor Digitar los 8 dígitos del DNI.'));
}
elseif(isset($_POST["show_attachments"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("request", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
	}

	$data = $_POST["show_attachments"];

	$query = "
		SELECT file_path
		FROM tbl_client_documents
		WHERE doc_number = ".$data["dni"]."
	";
	$attachments = [];
	$query_result = $mysqli->query($query);
	if($mysqli->error){ echo $mysqli->error; die; }
	while($r = $query_result->fetch_assoc()) $attachments[] = $r["file_path"];

	$body = '';
	$body .= '<div class="row">';
	$images = preg_grep("/.(jpg|jpeg|png|gif|bmp)$/", $attachments);

	foreach (array_diff($attachments, $images) as $doc) {
		$body .= '<div class="col-lg-12">';
		$body .= '<p><a href="'.$doc.'">'.$doc.'</a></p>';
		$body .= '</div>';
	}
	$body .= '</div>';
	$line_break = 0;
	foreach ($images as $image) {
		if(!$line_break){
			$body .= '<div class="row mt-4">';
		}
		$line_break++;

		$body .= '<div class="col-lg-4">';
		$body .= '<a href="'.$image.'" target="_blank"><img class="img img-responsive" src="'.$image.'"></a>';
		$body .= '</div>';
		if($line_break == 3){
			$body .= '</div>';
			$line_break = 0;
		}

	}
	die(action_response('200', $body));
}
elseif(isset($_POST["get_retiros_quota_status"])){
	$data = $_POST["get_retiros_quota_status"];

	$client_id = $data["client_id"];
	$request_time = $data["request_time"];
	$request_id = $data["request_id"];

	$status = get_retiros_quota_status($client_id, $request_time, $request_id);
	echo json_encode(['data' => $status]);
}

elseif(isset($_POST["get_deposits_total"])){
	$data = $_POST["get_deposits_total"];

	$client_id = $data["client_id"];
	$request_time = $data["request_time"];
	$request_id = $data["request_id"];

	$total = get_deposits_total($client_id, $request_time, $request_id);
	echo json_encode(['data' => $total]);
}

elseif(isset($_POST["get_soporte_retiros_api"])){

	if(!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Retiros.'));
	}

	// POST DATA
	$data = $_POST["get_soporte_retiros_api"];

	// DATA CLEANING
	$request_id = trim($data["id"]) === "" ? null : trim($data["id"]);

	$start_date = $data["start_date"] ?: date("d-m-y");
	$start_time = $data["start_time"] ? $data["start_time"] : date("H:i:s", strtotime("00:00:00"));
	$from_date_local = $start_date . " - " . $start_time;

	$end_date = $data["end_date"] ?: date("d-m-y", strtotime("+1 days"));
	$end_time = $data["end_time"] ? $data["end_time"] : date("H:i:s", strtotime("00:00:00"));
	$to_date_local = $end_date . " - " . $end_time;

	$payment_type_id = $data["payment_type"] ? $data["payment_type"] === "-1" ? "" : $data["payment_type"] : "";
	$state_list = $data["state_list"] ? $data["state_list"] !== "" ? $data["state_list"] : [] : [];
    $amount_greater_than = is_numeric($data["amount_greater_than"]) ? $data["amount_greater_than"] : null;
    $amount_less_than = is_numeric($data["amount_less_than"]) ? $data["amount_less_than"] : null;
    $is_verified = $data["is_verified"] ?? -1;
	//$is_bonus = $data["is_bonus"] ?? -1;
	$is_bonus_player = $data["is_bonus_player"] ?? -1;
	$channel = $data["channel"] ?? -1;
	$payment_type = $data["payment_type"] ?? -1;
	$client_gladcon_at = $data["client_gladcon_at"] ?? -1;
	$btag = $data["btag"];

    //FILTER CHECKS
	$state_list_check = true;
	$payment_type_check = true;
    $amount_greater_than_check = true;
    $amount_less_than_check = true;
    $is_verified_check = true;
	$is_bonus_check = true;
	$channel_check = true;
	$client_gladcon_at_check = true;
	$btag_check = true;

	//Btags
	$btags = ['pjlima', 'pjhuacho', 'pjtrujillo', 'pjchimbote', 'pjchiclayo', 'pjpiura', 'pjtumbes', 'pjtacna', 'kingpalace', 'moulinrouge', 'juegospj', 'clubpj', 'clubmeier'];

	// Leech authentication
	$API_Authentication = leech_authentication();

	if(leech_login()){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://backofficewebadmin.betconstruct.com/api/en/Client/GetClientWithdrawalRequestsWithTotals');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Authentication: '.$API_Authentication
			//'Authentication: '.'59a398ce338b628e7722d0d26a8b8f381a9f20e696cd543600e51a90b351b662'
		]);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
			"BetShopId" 	=> "",
			"ByAllowDate" 	=> false,
			"ClientId" 		=> "",
			"ClientLogin" 	=> "",
			"Email"			=> "",
			"FromDateLocal" => $from_date_local,
			"Id" 			=> $request_id,
			"IsTest" 		=> "",
			"PaymentTypeId" => $payment_type_id,
			"StateList" 	=> $state_list,
			"ToDateLocal" 	=> $to_date_local
		]));

		$response = json_decode(curl_exec($ch), true);

		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}

		$response_data = [];
		if (!$response["HasError"] && isset($response["Data"])){
			// SAVE THE BANK ACCOUNTS
 			save_bank_accounts($response["Data"]["ClientRequests"]);

			// KEEP RETRIEVING THE DATA
			foreach ($response["Data"]["ClientRequests"] as $client_request){
				preg_match('~Nombre del Banco:(.*?),~', $client_request["Info"], $bank);
				preg_match('~Numero de Cuenta:(.*?),~', $client_request["Info"], $bank_account);
				preg_match('~Numero CCI - 20 Digitos:(.*?),~', $client_request["Info"], $bank_cci);

				$bank = $bank ? (array_key_exists(1, $bank) ? $bank[1] : null) : null;
				$bank_account = $bank_account ? (array_key_exists(1, $bank_account) ? $bank_account[1] : null) : null;
				$bank_cci = $bank_cci ? (array_key_exists(1, $bank_cci) ? $bank_cci[1] : null) : null;

				$row = [];
				$row["id"] = $client_request["Id"];
				$row["amount"] = $client_request["Amount"];
				$row["request_time"] = $client_request["RequestTime"] ? date('Y-m-d H:i:s', strtotime($client_request["RequestTime"])) : null;
				$row["allow_time"] = $client_request["AllowTime"] ? date('Y-m-d H:i:s', strtotime($client_request["AllowTime"])) : null;
				$row["payment_created"] = $client_request["PaymentCreated"] ? date('Y-m-d H:i:s', strtotime($client_request["PaymentCreated"])) : null;
				$row["is_verified"] = $client_request["IsVerified"];
				$row["account_holder"] = $client_request["AccountHolder"];
				$row["is_bonus"] = $client_request["IsBonus"];
				$row["bank"] = $bank;
				$row["bank_account"] = $bank_account;
				$row["bank_cci"] = $bank_cci;
				$row["channel"] = is_null($client_request["BetshopId"]) ? "Digital" : "Retail";
				$row["state"] = $client_request["State"];
				$row["state_name"] = $client_request["StateName"];
				$row["client_id"] = $client_request["ClientId"];


				//$row["request_time"]
				$fecha_siete_dias_atras = date('Y-m-d H:i:s', strtotime('-7 days',strtotime($row["request_time"])));

				$query = " SELECT SUM(col_Amount) as Monto
					FROM bc_apuestatotal.at_ClientDeposits
					WHERE col_ClientId = '".$row["client_id"]."'
					 AND col_Created >= '".$fecha_siete_dias_atras."'
					";
				$query_result = $mysqli->query($query);
			    $result = $query_result->fetch_assoc();
				$depositos =  $result["Monto"] ? :0 ;

				$query = " SELECT SUM(col_Amount) as Monto
							FROM bc_apuestatotal.at_ClientWithdrawal
							WHERE col_ClientId = '".$row["client_id"]."' 
							AND col_Created >= '".$fecha_siete_dias_atras."' 
							AND col_TypeId = '2'
							";
				$query_result = $mysqli->query($query);
			    $result= $query_result->fetch_assoc();
				$retiros=  $result["Monto"] ? :0;

				$query = " SELECT col_Id, col_Amount
							FROM bc_apuestatotal.at_ClientWithdrawal
							WHERE col_ClientId = '".$row["client_id"]."' 
							AND col_Created >= '".$fecha_siete_dias_atras."' 
							AND col_TypeId = '1'
							ORDER BY `col_Created` DESC
							LIMIT 1
							";

				$query_result = $mysqli->query($query);
			    $result_retiro= $query_result->fetch_assoc();

			    $retiro_en_curso = 0;
			    $at_clientwithdrawal_col_id = 0;
			    if($result_retiro){
					$retiro_en_curso =  $result_retiro["col_Amount"];
					$at_clientwithdrawal_col_id = $result_retiro["col_Id"];
			    }

				$query = " SELECT col_AfterBalance
							FROM bc_apuestatotal.tbl_Transaction
							WHERE col_DocumentId = '".$at_clientwithdrawal_col_id."' 
							AND col_AccountId = '5211-279-8-".$row["client_id"]."-PEN'
							";
				$query_result = $mysqli->query($query);
			    $result = $query_result->fetch_assoc();

			    $end_balance = 0;
			    if($result){
					$end_balance=  $result["col_AfterBalance"];
			    }

				//Ratio Magico = Depositos / (Retiros + Retiro en curso + End balance) 
				$ratio_magico = 0;
				$divisor = ($retiros + $retiro_en_curso + $end_balance) ;
				if($divisor != 0 ){
					$ratio_magico =  $depositos / $divisor;
				}
				$row["formu"] = "ratio_magico = ".$depositos."/"."(".$retiros."+".$retiro_en_curso." +".$end_balance.")";
				$row["ratio_magico"] = $ratio_magico;

				$row["client_name"] = $client_request["ClientName"];
				$row["payment_type"] = $client_request["PaymentSystemName"];
				$row["payment_type_id"] = $client_request["PaymentSystemId"];
				$row["btag"] = null;
				$row["client_created"] = null;
				$row["bet_shop_name"] = $client_request["BetShopName"];
				$row["bank_bet_shop_name"] = ($bank ? $bank : "") . ($client_request["BetShopName"] ? " $client_request[BetShopName]" : "");
				$row["duplicated_bank_status"] = is_null($client_request["BetshopId"]) ? get_duplicated_bank_accounts_status($client_request["ClientId"], $bank_account, $bank_cci) : "";
				$row["client_sportsbook_profile_id"] = $client_request["ClientSportsbookProfileId"];

				$from_date = get_from_date_request($client_request["ClientId"], $row["request_time"], $client_request["Id"]);
				$row["from_date"] = $from_date;
				$row["deposits_total"] = get_deposits_total_v2($from_date, $client_request["ClientId"], $client_request["RequestTime"]) ?? "-.--";
				$row["quota_status"] = get_retiros_quota_status($from_date, $client_request["Id"]);
				$row["depositos_televentas_total"] = get_depositos_televentas_total($from_date, $client_request["ClientId"]) ?? "-.--";

				$row["is_bonus_player"] = get_bonus_player_status($from_date, $client_request["ClientId"]);

				$query = "SELECT col_BTag, col_Created FROM bc_apuestatotal.tbl_Client WHERE col_id = $row[client_id] LIMIT 1;";
				$query_result = $mysqli->query($query);
				if($mysqli->error){ $row["btag"] = null; }
				while($r = $query_result->fetch_assoc()) {
					$row["btag"] = $r["col_BTag"];
					$row["client_created"] = $r["col_Created"];
				}

				if ($amount_greater_than){
                    $amount_greater_than_check = $row["amount"] >= $amount_greater_than;
				}

                if ($amount_less_than){
                    $amount_less_than_check = $row["amount"] < $amount_less_than;
                }

                if (!is_null($is_verified) && $is_verified != -1){
                	$is_verified_check = $row["is_verified"] == $is_verified;
				}

				if (!is_null($is_bonus_player) && $is_bonus_player != -1){
					$is_bonus_check = $row["is_bonus_player"] == $is_bonus_player;
				}

				if (!is_null($channel) && $channel != -1){
					$channel_check = strtolower($row["channel"]) == $channel;
				}

				if (!is_null($payment_type) && $payment_type != -1){
					$payment_type_check = $row["payment_type_id"] == $payment_type;
				}

				if (!empty($state_list)){
					$state_list_check = false;
					foreach ($state_list as $state){
						if ($state == $row["state"]) $state_list_check = true;
					}
				}

				if (!is_null($client_gladcon_at) && $client_gladcon_at != -1){
					if ($client_gladcon_at == "gladcon"){
						$client_gladcon_at_check = in_array($row["btag"], $btags);
					} else if ($client_gladcon_at == "at"){
						$client_gladcon_at_check = !in_array($row["btag"], $btags);
					}
				}

				if ($btag){
					$btag_check = strtolower($row["btag"]) == strtolower($btag);
				}

                if (
                	$amount_greater_than_check &&
					$amount_less_than_check &&
					$is_verified_check &&
					$is_bonus_check &&
					$channel_check &&
					$payment_type_check &&
					$state_list_check &&
					$btag_check &&
					$client_gladcon_at_check
				) {
					$response_data[] = $row;
				}
            }
		}
		curl_close($ch);

		echo json_encode(['data' => $response_data]);
	}
}

elseif(isset($_POST["get_soporte_retiros"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Retiros.'));
	}

	$bc_states = [
		'Rejected' => -2,
		'Cancelled' => -1,
		'New' => 0,
		'Allowed' => 1,
		'Pending' => 2,
		'Paid' => 3,
		'RollBacked' => 4,
	];

	$data = $_POST["get_soporte_retiros"];
	$data['offset'] = $data['limit']*$data['page'];

	$list_where = "WHERE cr.id <> 0";
	if($data['filter'] != ""){
		$list_where .= " AND (
				cr.id 			LIKE '%{$data['filter']}%' OR
				cr.client_id 	LIKE '%{$data['filter']}%' OR
				c.col_DocNumber 	LIKE '%{$data['filter']}%' OR
				c.col_Name 		LIKE '%{$data['filter']}%'
			)
		";
	}

	if ($data["from_date"] != "") {
		$list_where .= " AND cr.created_at >= '{$data["from_date"]}'";
	}
	if ($data["to_date"] != "") {
		$list_where .= " AND cr.created_at < '{$data["to_date"]}'";
	}

	if(array_key_exists("state", $data)){
		if($data["state"] != "all"){
			$list_where .= " AND state_bc = '".$data["state"]."'";
		}
	}

	$query = "
		SELECT amount, client_id 
		FROM tbl_client_request_pending_bets
	";
	$pending_bets = [];
	$query_result = $mysqli->query($query);
	if($mysqli->error){ echo $mysqli->error; die; }
	while($r = $query_result->fetch_assoc()) {
		$pending_bets[$r["client_id"]] = $r["amount"];
	}

	$query = "
		SELECT
			cr.id,
			cr.created_at,
			(
				SELECT cri.created_at
				FROM tbl_client_requests cri
				WHERE
					cri.created_at < cr.created_at
					AND cri.client_id = cr.client_id
					AND cri.state_bc IN ('Allowed','Paid')
				ORDER BY cri.created_at DESC
				LIMIT 1
			) as last_created_at,
			cr.amount,
			cr.sum_bets,
			cr.sum_depos,
			cr.pending_bets,
			cr.sum_gamings,
			cr.percentage,
			cr.state_bc,
			cr.state,
			cr.cuota_status,
			cr.client_id,
			(cb.id IS NOT NULL) AS IsBonus,
			c.col_Name as Name,
			c.col_IsVerified as IsVerified,
			c.col_Created as CreatedLocalDate,
			c.col_DocNumber as DocNumber,
			c.col_IsLocked as IsLocked,
			cr.credit_card,
			(
				SELECT col_Balance
				FROM bc_apuestatotal.tbl_Account
				WHERE 
					col_Id = CONCAT('5211-279-8-', c.col_Id, '-PEN')
					AND col_Modified <= cr.created_at
				ORDER BY col_Modified DESC
				LIMIT 1
			) AS balance_realtime,
			(
				SELECT DATE_ADD(max(col_Created), INTERVAL -9 HOUR)
				FROM bc_apuestatotal.tbl_Document
				WHERE col_ClientId = c.col_Id
			) AS last_transaction,
			IFNULL((
				SELECT col_AfterBalance FROM bc_apuestatotal.tbl_ClientRequest icr 
				INNER JOIN bc_apuestatotal.tbl_Transaction it ON it.col_DocumentId = icr.col_RequestDocumentId 
				WHERE 
					it.col_AccountId = CONCAT('5211-279-8-', c.col_Id , '-PEN')
					AND icr.col_Id = cr.id
			), cr.pending_bets
			) as balance
		FROM tbl_client_requests cr
		INNER JOIN bc_apuestatotal.tbl_Client c ON c.col_Id = cr.client_id
		LEFT JOIN tbl_client_bonus cb ON cb.client_id = cr.client_id
		{$list_where}
		ORDER BY cr.created_at DESC
		LIMIT {$data['limit']} OFFSET {$data['offset']}
	";

	$requests = [];
	$docs = [];
	$result = $mysqli->query($query);
	if($mysqli->error){ echo $mysqli->error; die; }
	while($r = $result->fetch_assoc()){
		if(isset($pending_bets[$r["client_id"]])) {
			$r["pending_bets"] = $pending_bets[$r["client_id"]];
		}

		$requests[] = $r;
		$docs[] = $r["DocNumber"];
	}

	$query = "
		SELECT
			count(cr.id) as count_requests,
			sum(cr.amount) as sum_amount
		FROM tbl_client_requests cr
		INNER JOIN bc_apuestatotal.tbl_Client c ON c.col_Id = cr.client_id
		{$list_where}
		ORDER BY cr.created_at DESC
	";
	$final = [];
	$result = $mysqli->query($query);
	if($mysqli->error){ echo $mysqli->error; die; }
	if($r = $result->fetch_assoc()) $final = $r;

	$query = "
		SELECT cr.id
		FROM tbl_client_requests cr
		INNER JOIN bc_apuestatotal.tbl_Client c ON c.col_Id = cr.client_id
		{$list_where}
	";
	$num_rows = $mysqli->query($query)->num_rows;

	$body = '';
	if($num_rows && !empty($requests)){
		$query = "
			SELECT
				dni,
				estado
			FROM tbl_consultas_dni
			WHERE
				dni IN('".implode("','", $docs)."')
				AND estado = 1
		";

		$dnis = [];
		$query_result = $mysqli->query($query);
		while($r = $query_result->fetch_assoc()) $dnis[$r["dni"]] = $r;

		$query = "
			SELECT DISTINCT doc_number
			FROM tbl_client_documents
			WHERE
				doc_number IN('".implode("','", $docs)."')
				AND doc_type = 'dni'
		";

		$dnis_attachments = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		while($r = $query_result->fetch_assoc()) $dnis_attachments[$r["doc_number"]] = $r["doc_number"];

		$query = "
			SELECT MAX(col_Modified) AS last_modified
			FROM bc_apuestatotal.tbl_Balance			
		";
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		if($r = $query_result->fetch_assoc()) {
			$last_modified_balance = $r["last_modified"];
		}

		$body .= '<thead>';
		$body .= '<tr class="bg-primary">';
		$body .= '<th style="width: 360px" class="text-light text-center" colspan="6"><b>Solicitudes</b></th>';
		$body .= '<th style="width: 560px" class="text-light text-center" colspan="7"><b>Validaciones</b></th>';
		$body .= '<th class="text-light text-center" colspan="6">Cliente</th>';
		$body .= '<th style="width: 120px" class="text-light text-center" colspan="2">Estados</th>';
		$body .= '</tr>';
		$body .= '<tr class="bg-secondary">';
		$body .= '<th style="width: 150px">#ID Solicitud</th>';
		$body .= '<th style="width: 100px">Fecha Solicitud</th>';
		$body .= '<th style="width: 120px">Monto</th>';
		$body .= '<th style="width: 60px">Bonus</th>';
		$body .= '<th style="width: 60px">Cerrada</th>';

		$body .= '<th style="width: 120px">Fecha Último Retiro</th>';
		$body .= '<th style="width: 80px">Transacciones</th>';
		$body .= '<th style="width: 80px">Apostado</th>';
		$body .= '<th style="width: 80px">Casino</th>';
		$body .= '<th style="width: 80px">Depositado</th>';
		$body .= '<th style="width: 80px">Pendientes</th>';
		$body .= '<th style="width: 80px">Disponible</th>';
		// $body .= '<th style="width: 80px">Porcentage</th>';
		$body .= '<th style="width: 120px">Cuota > 1.2</th>';

		$body .= '<th style="width: 100px">#ID Cliente</th>';
		$body .= '<th style="width: 180px">Nombres</th>';
		$body .= '<th style="width: 100px">Fecha Registro</th>';
		$body .= '<th style="width: 100px">DNI</th>';
		$body .= '<th style="width: 100px">Verificado</th>';

		// $body .= '<th class="text-light" style="width: 40px">CC</th>';
		$body .= '<th style="width: 80px">Estado BC</th>';
		$body .= '<th style="width: 80px">Estado AT</th>';
		// if(array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
		// 	$body .= '<th class="text-light" style="width: 140px">Aprobar</th>';
		// }
		$body .= '</tr>';
		$body .= '</thead>';
		$body .= '<tbody>';

		foreach ($requests as $request) {
			$btn_final_validated = 1;
			if(in_array($request["DocNumber"], array_keys($dnis_attachments))){
				$btn_config = [
					'text' => 'Validado',
					'color' => 'btn-success'
				];
			}
			elseif(in_array($request["DocNumber"], array_keys($dnis))){
				$btn_final_validated = 0;
				$btn_config = [
					'text' => 'Anexar',
					'color' => 'btn-info'
				];
			}
			else{
				$btn_final_validated = 0;
				$btn_config = [
					'text' => 'Verificar',
					'color' => 'btn-warning'
				];
			}

			if($request["percentage"] >= 100 || $request["sum_depos"] == 0){
				$btn_percentage = 'btn-success';
			}
			else{
				$btn_final_validated = 0;
				$btn_percentage = 'btn-warning';
			}

			if($request["cuota_status"] == 1){
				$btn_cuota = 'btn-success';
			}
			elseif($request["cuota_status"] == 2){
				$btn_final_validated = 0;
				$btn_cuota = 'btn-danger';
			}
			else{
				$btn_final_validated = 0;
				$btn_cuota = 'btn-warning';
			}

			if($bc_states[$request["state_bc"]] == $request["state"]){
				$btn_state_config = "btn-success";
			}
			else{
				$btn_state_config = "btn-warning";
			}

			if($request["percentage"] >= 100 || $request["sum_depos"] == 0){
				$btn_depos_config = "btn-success";
			}
			else{
				$btn_depos_config = "btn-danger";
			}

			$btn_final_config = [
				'class' => 'btn-danger',
				'text' => 'Rechazado',
				'disabled' => 'disabled'
			];

			if($btn_final_validated){
				if($request["state"] == 0){
					$is_disabled = true;
					if($login["area_id"] == 6 || ($login["area_id"] == 9 && in_array($login["cargo_id"], [16]))){
						$is_disabled = false;
					}

					$btn_final_config = [
						'class' => 'btn-success',
						'text' => 'Aprobar',
						'disabled' => ($is_disabled  ? 'disabled' : '')
					];
				}
				elseif($request["state"] == 1){
					$is_disabled = true;
					if(
						!in_array($request["state_bc"], ['Cancelled', 'Rejected'])
						&& $login["area_id"] == 6
						|| ($login["area_id"] == 3 && in_array($login["cargo_id"], [16]))
					){
						$is_disabled = false;
					}

					$btn_final_config = [
						'class' => 'btn-success',
						'text' => 'Pagar',
						'disabled' => ($is_disabled  ? 'disabled' : '')
					];
				}
				elseif($request["state"] == 3){
					$is_disabled = true;

					$btn_final_config = [
						'class' => 'btn-success',
						'text' => 'Pagado',
						'disabled' => 'disabled'
					];
				}
			}
			elseif($request["state"] >= 0){
				$btn_final_config = [
					'class' => 'btn-danger',
					'text' => 'No Validado',
					'disabled' => 'disabled'
				];
			}

			$row_bg = "";
			if($request["pending_bets"] > ($request["balance"])) {
				$row_bg = "alert-warning";
			} elseif($request["last_transaction"] > $last_modified_balance) {
				$row_bg = "alert-danger";
			}

			$body .= '<tr class="' . $row_bg . '">';
			$body .= '<td>';
			$body .= '<button id="btnRetiroRefresh" data-id="'.$request["id"].'" class="btn btn-link btn-xs"><i class="fa fa-refresh fa-xs"></i></button>'.$request["id"];
			$body .= '</td>';
			$body .= '<td>'.date('Y-m-d H:i:s', strtotime($request["created_at"])).'</td>';
			$body .= '<td class="text-right">S/ '.number_format($request["amount"], 2, '.', ',').'</td>';
			if($request["IsBonus"]){
				$body .= '<td class="text-center text-success"><i class="fa fa-check fa-2x"></i></td>';
			}
			else{
				$body .= '<td class="text-center text-danger"><i class="fa fa-times fa-2x"></i></td>';
			}
			if($request["IsLocked"]){
				$body .= '<td class="text-center text-success"><i class="fa fa-check fa-2x"></i></td>';
			}
			else{
				$body .= '<td class="text-center text-danger"><i class="fa fa-times fa-2x"></i></td>';
			}
			$body .= '<td>'.($request["last_created_at"] ? date('Y-m-d H:i:s', strtotime($request["last_created_at"])) : "").'</td>';
			$body .= '<td>';
			$body .= '<button id="btnRetiroTransactionsModal" class="btn btn-info btn-xs btn-block"';
			$body .= 'data-monto="'.$request["amount"].'"';
			$body .= 'data-apostado="'.$request["sum_bets"].'"';
			$body .= 'data-request="'.$request["id"].'">';
			$body .= '<i class="fa fa-eye"></i> Ver';
			$body .= '</button>';
			$body .= '</td>';
			$body .= '<td>';
			$body .= '<button id="btnRetiroBetsModal" class="btn btn-secondary btn-xs btn-block"';
			$body .= 'data-monto="'.$request["amount"].'"';
			$body .= 'data-apostado="'.$request["sum_bets"].'"';
			$body .= 'data-request="'.$request["id"].'">';
			$body .= 'S/ '.number_format($request["sum_bets"], 2, '.', ',');
			$body .= '</button>';
			$body .= '</td>';
			$body .= '<td>';
			$body .= '<button id="btnRetiroGamingsModal" class="btn btn-secondary btn-xs btn-block"';
			$body .= 'data-monto="'.$request["amount"].'"';
			$body .= 'data-apostado="'.$request["sum_gamings"].'"';
			$body .= 'data-request="'.$request["id"].'">';
			$body .= 'S/ '.number_format($request["sum_gamings"], 2, '.', ',');
			$body .= '</button>';
			$body .= '</td>';
			$body .= '<td>';
			$body .= '<button id="btnRetiroDeposModal" class="btn btn-secondary btn-xs btn-block"';
			$body .= 'data-monto="'.$request["amount"].'"';
			$body .= 'data-apostado="'.$request["sum_depos"].'"';
			$body .= 'data-request="'.$request["id"].'">';
			$body .= 'S/ '.number_format($request["sum_depos"], 2, '.', ',');
			$body .= '</button>';
			$body .= '</td>';
			$body .= '<td>';
			$body .= 'S/ '.number_format($request["pending_bets"], 2, '.', ',');
			$body .= '</td>';
			$body .= '<td>';
			$body .= 'S/ '.number_format($request["amount"] + $request["balance"] - $request["pending_bets"], 2, '.', ',');
			$body .= '</td>';
			// $body .= '<td class="text-right '.$btn_depos_config.'">';
			// $body .= ($request["sum_depos"] != 0 ? $request["percentage"] : "<span style='font-size:16px'>&infin;</span>").' %';
			// $body .= '</td>';
			// $body .= '<td>';
			// $body .= '<button id="btnRetiroPercentageModal" class="btn '.$btn_percentage.' btn-xs btn-block"';
			// $body .= 'data-monto="'.$request["amount"].'"';
			// $body .= 'data-apostado="'.$request["sum_depos"].'"';
			// $body .= 'data-request="'.$request["id"].'">';
			// $body .= ($request["sum_depos"] != 0 ? $request["percentage"] : "<span style='font-size:16px'>&infin;</span>").' %<br/>';
			// $body .= "S/ " . number_format(($request["sum_bets"] + $request["sum_gamings"] - $request["sum_depos"]), 2, '.', ',');
			// $body .= '</td>';
			$body .= '<td>';
			$body .= '<button id="btnRetiroCuotaModal" class="btn '.$btn_cuota.' btn-xs btn-block"';
			$body .= 'data-monto="'.$request["amount"].'"';
			$body .= 'data-apostado="'.$request["sum_bets"].'"';
			$body .= 'data-depositado="'.$request["sum_depos"].'"';
			$body .= 'data-request="'.$request["id"].'">';
			$body .= '<i class="fa fa-money"></i> Detalle';
			$body .= '</button>';
			$body .= '</td>';
			$body .= '<td>';
			$body .= '<a id="btnClientStatistics"';
			$body .= 'data-client-id="'.$request["client_id"].'">';
			$body .= $request["client_id"];
			$body .= '</a>';
			$body .= '</td>';
			$body .= '<td>'.$request["Name"].'</td>';
			$body .= '<td>'.$request["CreatedLocalDate"].'</td>';
			$body .= '<td>';
			$body .= '<button id="btnRetiroDNIModal" ';
			$body .= 'data-dni="'.$request["DocNumber"].'" ';
			$body .= 'data-client-id="'.$request["client_id"].'" ';
			$body .= 'class="btn '.$btn_config["color"].' btn-xs btn-block">';
			$body .= '<i class="fa fa-id-card"></i> '.$btn_config["text"];
			$body .= '</button>';
			$body .= '</td>';
			if($request["IsVerified"]){
				$body .= '<td class="text-center text-success"><i class="fa fa-check fa-2x"></i></td>';
			}
			else{
				$body .= '<td class="text-center text-danger"><i class="fa fa-times fa-2x"></i></td>';
			}
			$body .= '<td class="'.$btn_state_config.'">'.$request["state_bc"].'</td>';
			$body .= '<td>';
			$body .= '<button';
			$body .= ' id="btnRetiroEstado"';
			$body .= ' class="btn btn-block btn-xs '.(isset($btn_final_config["class"]) ? $btn_final_config["class"] : "").'"';
			$body .= ' data-id="'.$request["id"].'" ';
			$body .= (isset($btn_final_config["disabled"]) ? $btn_final_config["disabled"] : "").'>';
			$body .= (isset($btn_final_config["text"]) ? $btn_final_config["text"] : "");
			$body .= '</button>';
			if(
				in_array($request["state"], [0, 1])
				&& $btn_final_validated
				&& ($login["area_id"] == 6 || ($login["area_id"] == 9 && in_array($login["cargo_id"], [16])))
			){
				$body .= '<button id="btnRetiroEstadoDeny" class="btn btn-block btn-xs btn-danger" data-id="'.$request["id"].'">Rechazar</button>';
			}
			$body .='</td>';
			// if(array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
			// $body .= '<td><button id="btnSoporteRetirosFinalAction" data-request="'.$request["id"].'" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Aprobar/Rechazar</button></td>';
			// }
			$body .= '</tr>';
		}

		$body .= '</tbody>';
		$body .= '<tfoot>';
		$body .= '<tr>';
		$body .= '<td colspan="3"><b>Solicitudes:</b> '.($data['offset']+1).' - '.($data['offset']+$data['limit'] >= $final["count_requests"] ? $final["count_requests"] : $data['offset']+$data['limit']).' de '.$final["count_requests"].'</td>';
		$body .= '<td colspan="3"><b>Monto Total:</b> S/ '.number_format($final["sum_amount"], 2, '.', ',').'</td>';
		$body .= '</tr>';
		$body .= '<tfoot>';
		$body .= '</table>';
	}

	echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
}

elseif(isset($_POST["get_values_quota"])) {
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver las Cuotas de las Apuestas.'));
	}

	$data = $_POST["get_values_quota"];
	//client_id, request_id, created_at
	$query = "
		SELECT DATE_ADD(
			IFNULL((
				SELECT iicr.col_RequestTime
				FROM bc_apuestatotal.tbl_ClientRequest iicr
				WHERE
					iicr.col_State = 3
					AND iicr.col_ClientId = '$data[client_id]'
					AND iicr.col_RequestTime < '" . date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($data['request_time']))) . "'
					AND iicr.col_Id <> $data[request_id]
				ORDER BY iicr.col_RequestTime  DESC
				LIMIT 1
			), '2000-01-01'),
			INTERVAL -1 SECOND
		) AS from_date
	";
	$query_result = $mysqli->query($query);
	$from_date = null;
	if($mysqli->error){ echo json_encode(['data' => null]); die; }
	if($r = $query_result->fetch_assoc()) {
		$from_date = $r["from_date"];
	}

	if ($from_date){
		$query = "
			SELECT
				b.col_Id AS ticket_id,
				b.col_Amount AS apostado,
				b.col_Created AS created,
				b.col_Price AS odds,
				CASE b.col_State
					WHEN '-3' THEN 'Rollbacked'
					WHEN '-2' THEN 'Waiting partner'
					WHEN '-1' THEN 'Rejected'
					WHEN '0' THEN 'Pending Detail'
					WHEN '1' THEN 'Accepted'
					WHEN '2' THEN 'Returned'
					WHEN '3' THEN 'Lost'
					WHEN '4' THEN 'Won'
					WHEN '5' THEN 'CashOut'
					ELSE 'No'
				END AS state,
				b.col_WinningAmount AS ganado
			FROM bc_apuestatotal.tbl_Bet AS b
			WHERE
				b.col_Created >= '$from_date'
				AND b.col_Created <= '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($data["request_time"])))."'
				AND b.col_ClientId = '$data[client_id]'
		";
		$bets = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo json_encode(['data' => null]); die; }
		while($r = $query_result->fetch_assoc()) $bets[] = $r;
		echo json_encode(['data' => $bets]);
	}else {
		echo json_encode(['data' => null]);
	}

}

elseif(isset($_POST["get_values_deposits"])) {
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver las Cuotas de las Apuestas.'));
	}

	$data = $_POST["get_values_deposits"];
	//client_id, request_id, created_at
	$query = "
		SELECT DATE_ADD(
			IFNULL((
				SELECT iicr.col_RequestTime
				FROM bc_apuestatotal.tbl_ClientRequest iicr
				WHERE
					iicr.col_State = 3
					AND iicr.col_ClientId = '$data[client_id]'
					AND iicr.col_RequestTime < '" . date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($data['request_time']))) . "'
					AND iicr.col_Id <> $data[request_id]
				ORDER BY iicr.col_RequestTime  DESC
				LIMIT 1
			), '2000-01-01'),
			INTERVAL -1 SECOND
		) AS from_date
	";
	$query_result = $mysqli->query($query);
	$from_date = null;
	if($mysqli->error){ echo json_encode(['data' => null]); die; }
	if($r = $query_result->fetch_assoc()) {
		$from_date = $r["from_date"];
	}

	if ($from_date){
		$query = "
			SELECT
				DATE_ADD(d.col_Created, INTERVAL -9 HOUR) AS Created,
				d.col_Id AS Id,
				d.col_Amount AS Amount,
				ps.col_Name AS PaymentSystemName,
			    cd.col_Name AS CashDeskName
			FROM bc_apuestatotal.tbl_Document as d
			LEFT JOIN bc_apuestatotal.tbl_PaymentSystem ps ON ps.col_Id = d.col_PaymentSystemId
			LEFT JOIN bc_apuestatotal.tbl_CashDesk cd ON cd.col_Id = d.col_CashDeskId
			WHERE
				d.col_Created >= '$from_date'
				AND d.col_Created < '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($data["request_time"])))."'
				AND d.col_TypeId IN(3, 5)
				AND d.col_ClientId = '$data[client_id]'
		";

		$bets = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo json_encode(['data' => null]); die; }
		while($r = $query_result->fetch_assoc()) $bets[] = $r;
		echo json_encode(['data' => $bets]);
	}else {
		echo json_encode(['data' => null]);
	}
}

elseif(isset($_POST["get_values_deposits_tv"])) {
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver esto'));
	}

	$data = $_POST["get_values_deposits_tv"];
	$client_id = $data["client_id"];
	$from_date = $data["from_date"];

	if ($from_date){
		$query = "
			SELECT
				cd.col_TransactionDate as transaction_date,
				cd.col_Id as depositos_id,
				cd.col_Amount as amount,
				tct.monto as amount_2,
				tct.bono_monto,
				tct.total_recarga,
			   	c.col_Name AS cashdesk_name
			FROM
				bc_apuestatotal.at_ClientDeposits cd
			LEFT JOIN
				tbl_televentas_clientes_transaccion tct
			ON
				cd.col_Note = tct.txn_id
			LEFT JOIN
				bc_apuestatotal.tbl_CashDesk c
			ON
				c.col_Id = cd.col_CashDeskId
			WHERE
				cd.col_ClientId = '$client_id' AND
				cd.col_PaymentSystemId = 1630 AND
				cd.col_Created >= '$from_date';
		";

		$rows = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo json_encode(['data' => null]); die; }
		while($r = $query_result->fetch_assoc()) $rows[] = $r;
		echo json_encode(['data' => $rows]);
	}else {
		echo json_encode(['data' => null]);
	}
}

elseif(isset($_POST["get_values_table"])) {
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver las Cuotas de las Apuestas.'));
	}

	$data = $_POST["get_values_table"];

	$query = "
		SELECT
			client_id,
			created_at
		FROM tbl_client_requests
		WHERE id = ".$data["request_id"]."
		LIMIT 1
	";
	$current_request = [];
	$query_result = $mysqli->query($query);
	while($r = $query_result->fetch_assoc()) $current_request = $r;

	//Getting fromDate range
	$query = "
		SELECT DATE_ADD(
			IFNULL((
				SELECT iicr.col_RequestTime
				FROM bc_apuestatotal.tbl_ClientRequest iicr
				WHERE
					iicr.col_State = 3
					AND iicr.col_ClientId = {$current_request["client_id"]}
					AND iicr.col_RequestTime < '" . date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($current_request['created_at']))) . "'
					AND iicr.col_Id <> {$data["request_id"]}
				ORDER BY iicr.col_RequestTime  DESC
				LIMIT 1
			), '2000-01-01'),
			INTERVAL -1 SECOND
		) AS from_date
	";
	$query_result = $mysqli->query($query);
	if($mysqli->error){ echo $mysqli->error; die; }
	if($r = $query_result->fetch_assoc()) {
		$from_date = $r["from_date"];
	}

	if($data["type"] == "bets"){
		$query = "
			SELECT
				DATE_ADD(b.col_Created, INTERVAL -9 HOUR) AS created,
				b.col_Id AS ticket_id,
				b.col_Amount AS apostado,
				b.col_Price AS odds,
				CASE b.col_State
					WHEN '-3' THEN 'Rollbacked'
					WHEN '-2' THEN 'Waiting partner'
					WHEN '-1' THEN 'Rejected'
					WHEN '0' THEN 'Pending Detail'
					WHEN '1' THEN 'Accepted'
					WHEN '2' THEN 'Returned'
					WHEN '3' THEN 'Lost'
					WHEN '4' THEN 'Won'
					WHEN '5' THEN 'CashOut'
					ELSE 'No'
				END AS state,
				b.col_WinningAmount as ganado
			FROM bc_apuestatotal.tbl_Bet AS b
			WHERE
				b.col_Created >= '{$from_date}'
				AND b.col_Created < '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($current_request["created_at"])))."'
				AND b.col_ClientId = " . $current_request["client_id"] . "
		";
		$bets = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		while($r = $query_result->fetch_assoc()) $bets[] = $r;
	}
	elseif($data["type"] == "depos"){
		$query = "
			SELECT
				DATE_ADD(d.col_Created, INTERVAL -9 HOUR) AS Created,
				d.col_Id AS Id,
				d.col_Amount AS Amount,
				ps.col_Name AS PaymentSystemName
			FROM bc_apuestatotal.tbl_Document as d
			LEFT JOIN bc_apuestatotal.tbl_PaymentSystem ps ON ps.col_Id = d.col_PaymentSystemId
			WHERE
				d.col_Created >= '{$from_date}'
				AND d.col_Created < '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($current_request["created_at"])))."'
				AND d.col_TypeId IN(3, 5)
				AND d.col_ClientId = ".$current_request["client_id"]."
		";

		$depos = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		while($r = $query_result->fetch_assoc()) $depos[] = $r;
	}
	elseif($data["type"] == "percentage"){
		$query = "
			SELECT
				DATE_ADD(b.col_Created, INTERVAL -9 HOUR) as created,
				b.col_Id as id,
				b.col_Amount as balance,
				'Apuesta' as type
			FROM bc_apuestatotal.tbl_Bet AS b
			WHERE
				b.col_Created >= '{$from_date}'
				AND b.col_Created < '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($current_request["created_at"])))."'
				AND b.col_ClientId = " . $current_request["client_id"] . "
		";
		$bets = [];
		$query_result = $mysqli->query($query);
		while($r = $query_result->fetch_assoc()) $bets[] = $r;

		$query = "
			SELECT
				DATE_ADD(d.col_Created, INTERVAL -9 HOUR) as created,
				d.col_Id as id,
				d.col_Amount as balance,
				'Depósito' as type
			FROM bc_apuestatotal.tbl_Document as d
			WHERE
				d.col_Created >= '{$from_date}'
				AND d.col_Created < '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($current_request["created_at"])))."'
				AND d.col_TypeId IN(3, 5)
				AND d.col_ClientId = ".$current_request["client_id"]."
		";

		$depos = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		while($r = $query_result->fetch_assoc()) $depos[] = $r;

		$gamings = [];
		$query = "
			SELECT 
				d.col_Id AS Id,
				d.col_Amount AS Amount,
				DATE_ADD(d.col_Created, INTERVAL -9 HOUR) AS Created
			FROM bc_apuestatotal.tbl_Document AS d
			INNER JOIN bc_apuestatotal.tbl_Game ig ON (ig.col_Id = d.col_GameId)
			WHERE 
				d.col_ClientId = {$current_request["client_id"]} 
				AND d.col_Created >= '{$from_date}'
				AND d.col_Created < '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($current_request["created_at"])))."'
				AND d.col_TypeId = 10
				AND d.col_GameId <> -1
		";
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		while($r = $query_result->fetch_assoc()) {
			$gamings[]  = [
				'created' => $r["Created"],
				'id' => $r["Id"],
				'balance' => $r["Amount"],
				'type' => 'Casino'
			];
		}

		$transactions = array_merge($bets, $depos, $gamings);
	}
	elseif($data["type"] == "transactions"){
		$query = "
			SELECT
				DATE_ADD(d.col_Created, INTERVAL -9 HOUR) as col_Created,
				d.col_Id,
				te.col_Text AS Type,
				d.col_Amount
			FROM bc_apuestatotal.tbl_Document d
			LEFT JOIN bc_apuestatotal.tbl_DocumentType dt ON dt.col_Id = d.col_TypeId 
			LEFT JOIN bc_apuestatotal.tbl_TranslationEntry te ON te.col_LanguageId = 'en' AND te.col_TranslationId = dt.col_nameId
			WHERE
		--		d.col_Created >= '{$from_date}'
		--		AND d.col_Created <= '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($current_request["created_at"])))."'
				d.col_ClientId = {$current_request["client_id"]}
			GROUP BY d.col_Id
			ORDER BY d.col_Created DESC
		";
		$transactions = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		while($r = $query_result->fetch_assoc()) {
			$transactions[] = $r;
		}
	}
	elseif($data["type"] == "gamings"){
		$query = "
			SELECT 
				CAST(DATE_ADD(gk.col_Date, INTERVAL -9 HOUR) AS DATE) AS FromDate,
				(SELECT col_Text FROM bc_apuestatotal.tbl_TranslationEntry WHERE col_LanguageId = 'en' AND col_TranslationId = g.col_NameId LIMIT 1) AS GameName,
				gk.col_BetAmount AS Stakes,
				gk.col_BetCount AS Bets,
				(gk.col_WinAmount + gk.col_BetAmount) AS Winnings,
				(gk.col_WinAmount) AS Profit
            FROM bc_apuestatotal.tbl_ClientGameKPI gk
            INNER JOIN bc_apuestatotal.tbl_Game g ON (g.col_Id = gk.col_GameId)
            WHERE 
				gk.col_ClientId = {$current_request["client_id"]} 
				AND gk.col_Date >= CAST('{$from_date}' AS DATE)
				AND gk.col_Date < '" . date("Y-m-d", strtotime("+1 Day +9 Hours", strtotime($current_request["created_at"]))) . "'
		";

		$gamings = [];
		$query_result = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		while($r = $query_result->fetch_assoc()) $gamings[] = $r;
	}
	elseif($data["type"] == "cuota"){
		$query = "
			SELECT
				b.col_Id AS ticket_id,
				b.col_Amount AS apostado,
				b.col_Created AS created,
				b.col_Price AS odds,
				CASE b.col_State
					WHEN '-3' THEN 'Rollbacked'
					WHEN '-2' THEN 'Waiting partner'
					WHEN '-1' THEN 'Rejected'
					WHEN '0' THEN 'Pending Detail'
					WHEN '1' THEN 'Accepted'
					WHEN '2' THEN 'Returned'
					WHEN '3' THEN 'Lost'
					WHEN '4' THEN 'Won'
					WHEN '5' THEN 'CashOut'
					ELSE 'No'
				END AS state,
				b.col_WinningAmount AS ganado
			FROM bc_apuestatotal.tbl_Bet AS b
			WHERE
				b.col_Created >= '{$from_date}'
				AND b.col_Created <= '".date('Y-m-d H:i:s', strtotime("+9 Hours", strtotime($current_request["created_at"])))."'
				AND b.col_ClientId = ".$current_request["client_id"]."
		";
		$bets = [];
		$query_result = $mysqli->query($query);
		while($r = $query_result->fetch_assoc()) $bets[] = $r;
	}
	else {
		die(action_response('400', 'Bad Request.'));
	}


	$body = '<table width="100%" id="tblSoporteRetirosValue" class="table table-hover table-striped table-condensed table-bordered">';
	if($data["type"] == "bets"){
		if(!empty($bets)){
			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="6" class="text-center text-bold bg-primary" style="color:#fff !important">LISTA DE APUESTAS</th>';
			$body .= '</tr>';
			$body .= '<tr style="background-color:#e1e1e1;">';
			$body .= '<th><b>Fecha Creación</b></th>';
			$body .= '<th><b>Ticket ID</b></th>';
			$body .= '<th><b>Apostado</b></th>';
			$body .= '<th><b>Cuota</b></th>';
			$body .= '<th><b>Estado</b></th>';
			$body .= '<th><b>Ganado</b></th>';
			$body .= '</tr>';
			$body .= '</thead>';
			$body .= '<tbody style="overflow:hidden">';
			foreach ($bets as $bet) {
				$body .= '<tr class="'.($bet["odds"] < 1.2 ? 'text-danger': '').'">';
				$body .= '<td>'.$bet["created"].'</td>';
				$body .= '<td>'.$bet["ticket_id"].'</td>';
				$body .= '<td class="text-right">S/ '.number_format($bet["apostado"], 2).'</td>';
				$body .= '<td class="text-right">'.$bet["odds"].'</td>';
				$body .= '<td>'.$bet["state"].'</td>';
				$body .= '<td class="text-right">S/ '.$bet["ganado"].'</td>';
				$body .= '</tr>';
			}
			$body .= '</tbody>';
			$body .= '</table>';
		}
		else $body .= '<p>No hay apuestas para mostrar.</p>';
	} elseif($data["type"] == "transactions"){
		if(!empty($transactions)){
			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="6" class="text-center text-bold bg-primary" style="color:#fff !important">LISTA DE APUESTAS</th>';
			$body .= '</tr>';
			$body .= '<tr style="background-color:#e1e1e1;">';
			$body .= '<th><b>Fecha Creación</b></th>';
			$body .= '<th><b>Ticket ID</b></th>';
			$body .= '<th><b>Tipo</b></th>';
			$body .= '<th><b>Valor</b></th>';
			$body .= '</tr>';
			$body .= '</thead>';
			$body .= '<tbody>';
			foreach ($transactions as $transaction) {
				$body .= '<tr>';
				$body .= '<td>'.$transaction["col_Created"].'</td>';
				$body .= '<td>'.$transaction["col_Id"].'</td>';
				$body .= '<td>'.$transaction["Type"].'</td>';
				$body .= '<td>'.$transaction["col_Amount"].'</td>';
				$body .= '</tr>';
			}
			$body .= '</tbody>';
			$body .= '</table>';
		}
		else $body .= '<p>No hay transacciones para mostrar.</p>';
	} elseif($data["type"] == "percentage"){
		if(!empty($transactions)){
			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="4" class="text-center text-bold bg-primary" style="color:#fff !important">LISTA DE APUESTAS</th>';
			$body .= '</tr>';
			$body .= '<tr style="background-color:#e1e1e1;">';
			$body .= '<th><b>Fecha Creación</b></th>';
			$body .= '<th><b>ID</b></th>';
			$body .= '<th><b>Balance</b></th>';
			$body .= '<th><b>Tipo</b></th>';
			$body .= '</tr>';
			$body .= '</thead>';
			$body .= '<tbody>';
			foreach ($transactions as $transaction) {
				switch ($transaction["type"]) {
					case 'Casino':
						$percentage_class = 'text-info';
						break;
					case 'Apuesta':
						$percentage_class = 'text-danger';
						break;
					default:
						$percentage_class = 'text-success';
						break;
				}
				$body .= '<tr class="'.$percentage_class.'">';
				$body .= '<td>'.$transaction["created"].'</td>';
				$body .= '<td>'.$transaction["id"].'</td>';
				$body .= '<td>S/ '.number_format($transaction["balance"], 2).'</td>';
				$body .= '<td>'.$transaction["type"].'</td>';
				$body .= '</tr>';
			}
			$body .= '</tbody>';
			$body .= '</table>';
		}
		else $body .= '<p>No hay información para mostrar.</p>';
	} elseif($data["type"] == "gamings"){
		if(!empty($gamings)){
			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="7" class="text-center text-bold bg-primary" style="color:#fff !important">LISTA DE APUESTAS</th>';
			$body .= '</tr>';
			$body .= '<tr style="background-color:#e1e1e1;">';
			$body .= '<th><b>Fecha</b></th>';
			$body .= '<th><b>Juego</b></th>';
			$body .= '<th><b>Stakes</b></th>';
			$body .= '<th><b>Apuestas</b></th>';
			$body .= '<th><b>Winnings</b></th>';
			$body .= '<th><b>Profits</b></th>';
			$body .= '</tr>';
			$body .= '</thead>';
			$body .= '<tbody>';
			foreach ($gamings as $gaming) {
				$body .= '<tr>';
				$body .= '<td>'.$gaming["FromDate"].'</td>';
				$body .= '<td>'.$gaming["GameName"].'</td>';
				$body .= '<td>'.$gaming["Stakes"].'</td>';
				$body .= '<td>'.$gaming["Bets"].'</td>';
				$body .= '<td>'.$gaming["Winnings"].'</td>';
				$body .= '<td>'.$gaming["Profit"].'</td>';
				$body .= '</tr>';
			}
			$body .= '</tbody>';
			$body .= '</table>';
		}
		else $body .= '<p>No hay Apuestas de Casinos para mostrar.</p>';
	} elseif($data["type"] == "cuota"){
		if(!empty($bets)){
			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="6" class="text-center text-bold bg-primary" style="color:#fff !important">LISTA DE APUESTAS</th>';
			$body .= '</tr>';
			$body .= '<tr style="background-color:#e1e1e1;">';
			$body .= '<th><b>Fecha Creación</b></th>';
			$body .= '<th><b>Ticket ID</b></th>';
			$body .= '<th><b>Apostado</b></th>';
			$body .= '<th><b>Cuota</b></th>';
			$body .= '<th><b>Estado</b></th>';
			$body .= '<th><b>Ganado</b></th>';
			$body .= '</tr>';
			$body .= '</thead>';
			$body .= '<tbody>';
			foreach ($bets as $bet) {
				$body .= '<tr>';
				$body .= '<td class="'.($bet["odds"] < 1.2 ? 'text-bold text-white bg-danger': '').'">'.$bet["created"].'</td>';
				$body .= '<td class="'.($bet["odds"] < 1.2 ? 'text-bold text-white bg-danger': '').'">'.$bet["ticket_id"].'</td>';
				$body .= '<td class="text-right '.($bet["odds"] < 1.2 ? 'text-bold text-white bg-danger': '').'">S/ '.number_format($bet["apostado"], 2).'</td>';
				$body .= '<td class="text-right '.($bet["odds"] < 1.2 ? 'text-bold text-white bg-danger': '').'">'.$bet["odds"].'</td>';
				$body .= '<td class="'.($bet["odds"] < 1.2 ? 'text-bold text-white bg-danger': '').'">'.$bet["state"].'</td>';
				$body .= '<td class="text-right '.($bet["odds"] < 1.2 ? 'text-bold text-white bg-danger': '').'">S/ '.$bet["ganado"].'</td>';
				$body .= '</tr>';
			}
			$body .= '</tbody>';
			$body .= '</table>';
		}
		else $body .= '<p>No hay apuestas para mostrar.</p>';
	} elseif($data["type"] == "depos"){
		if(!empty($depos)){
			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="4" class="text-center text-bold bg-primary" style="color:#fff !important">LISTA DE DEPÓSITOS</th>';
			$body .= '</tr>';
			$body .= '<tr style="background-color:#e1e1e1;">';
			$body .= '<th><b>Fecha Creación</b></th>';
			$body .= '<th><b>Depósito ID</b></th>';
			$body .= '<th><b>Monto</b></th>';
			$body .= '<th><b>Método de Pago</b></th>';
			$body .= '</tr>';
			$body .= '</thead>';
			$body .= '<tbody>';
			foreach ($depos as $depo) {
				$body .= '<tr>';
				$body .= '<td>'.$depo["Created"].'</td>';
				$body .= '<td class="text-left">'.$depo["Id"].'</td>';
				$body .= '<td class="text-right">S/ '.number_format($depo["Amount"], 2).'</td>';
				$body .= '<td>'.$depo["PaymentSystemName"].'</td>';
				$body .= '</tr>';
			}
			$body .= '</tbody>';
			$body .= '</table>';
		}
		else $body .= '<p>No hay depositos para mostrar.</p>';
	}


	echo die(action_response('200', $body));
}
elseif(isset($_POST["get_player_statistics"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Retiros.'));
	}

	if(!($statistics = leech_player_statistics($_POST["get_player_statistics"], true))){
		echo die(action_response('404'));
	}

	$body = '';

	$body .= '<div class="row" style=" margin: 0 auto;">';
	$body .= '<div class="col-xs-12 col-md-2 col-md-offset-1">';
	$body .= '<div class="card-box-inline mb-0p5">';
	$body .= '<i class="icon fa fa-calendar"></i>';
	$body .= '<span class="text">Sport Last Bets</span>';
	$body .= '<div class="value date">'.($statistics["Data"]["LastSportBetTimeLocal"] ? date('Y-m-d H:i:s', strtotime($statistics["Data"]["LastSportBetTimeLocal"])) : "No data to display.").'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-primary mb-0p5">';
	$body .= '<i class="icon fa  fa-trophy"></i>';
	$body .= '<span class="text">Total Sport Bonus Winnings</span>';
	$body .= '<div class="value">'.number_format($statistics["Data"]["TotalSportBonusWinings"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-success mb-0p5">';
	$body .= '<i class="icon fa fa-money"></i>';
	$body .= '<span class="text">Gaming Total Stakes</span>';
	$body .= '<div class="value ">S/ '.number_format($statistics["Data"]["TotalCasinoStakes"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-info mb-0p5">';
	$body .= '<i class="icon fa fa-rocket"></i>';
	$body .= '<span class="text">Gaming Profitability</span>';
	$body .= '<div class="value">'.number_format($statistics["Data"]["CasinoProfitness"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-warning mb-0p5">';
	$body .= '<i class="icon fa fa-calendar"></i>';
	$body .= '<span class="text">Last Deposit Date</span>';
	$body .= '<div class="value date">'.($statistics["Data"]["LastDepositTimeLocal"] ? date('Y-m-d H:i:s', strtotime($statistics["Data"]["LastDepositTimeLocal"])) : "No data to display.").'</div>';
	$body .= '</div>';
	$body .= '</div>';
	$body .= '<div class="col-xs-12 col-md-2">';
	$body .= '<div class="card-box-inline card-box-inline-primary mb-0p5">';
	$body .= '<i class="icon fa fa-money"></i>';
	$body .= '<span class="text">Sport Total Stakes</span>';
	$body .= '<div class="value">S/ '.number_format($statistics["Data"]["TotalSportStakes"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-success mb-0p5">';
	$body .= '<i class="icon fa fa-bar-chart"></i>';
	$body .= '<span class="text">Sport P/L</span>';
	$body .= '<div class="value ">'.number_format($statistics["Data"]["ProfitAndLose"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-info mb-0p5">';
	$body .= '<i class="icon fa  fa-trophy"></i>';
	$body .= '<span class="text">Gaming Total Winning</span>';
	$body .= '<div class="value">S/ '.number_format($statistics["Data"]["TotalCasinoWinnings"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-warning mb-0p5">';
	$body .= '<i class="icon fa fa-calendar"></i>';
	$body .= '<span class="text">Gaming Last Bet</span>';
	$body .= '<div class="value date">'.($statistics["Data"]["LastCasinoBetTimeLocal"] ? date('Y-m-d H:i:s', strtotime($statistics["Data"]["LastCasinoBetTimeLocal"])) : "No data to display.").'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline mb-0p5">';
	$body .= '<i class="icon fa fa-dollar"></i>';
	$body .= '<span class="text">Withdraw Amount</span>';
	$body .= '<div class="value ">S/ '.number_format($statistics["Data"]["WithdrawalAmount"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '</div>';
	$body .= '<div class="col-xs-12 col-md-2">';
	$body .= '<div class="card-box-inline card-box-inline-success mb-0p5">';
	$body .= '<i class="icon fa fa-trophy"></i>';
	$body .= '<span class="text">Sport Total Winning</span>';
	$body .= '<div class="value ">S/ '.number_format($statistics["Data"]["TotalSportWinnings"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-info mb-0p5">';
	$body .= '<i class="icon fa fa-bell"></i>';
	$body .= '<span class="text">Sport Profitability</span>';
	$body .= '<div class="value">'.number_format($statistics["Data"]["SportProfitness"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-warning mb-0p5">';
	$body .= '<i class="icon fa fa-money"></i>';
	$body .= '<span class="text">Total Casino Bonus Stakes</span>';
	$body .= '<div class="value ">'.number_format($statistics["Data"]["TotalCasinoBonusStakes"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline mb-0p5">';
	$body .= '<i class="icon fa fa-dollar"></i>';
	$body .= '<span class="text">Deposits</span>';
	$body .= '<div class="value ">S/ '.number_format($statistics["Data"]["DepositAmount"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-primary mb-0p5">';
	$body .= '<i class="icon fa fa-dollar"></i>';
	$body .= '<span class="text">WithDrawal Count</span>';
	$body .= '<div class="value">'.$statistics["Data"]["WithdrawalCount"].'</div>';
	$body .= '</div>';
	$body .= '</div>';
	$body .= '<div class="col-xs-12 col-md-2">';
	$body .= '<div class="card-box-inline card-box-inline-info mb-0p5">';
	$body .= '<i class="icon fa fa-money"></i>';
	$body .= '<span class="text">Sport Total Unsettled</span>';
	$body .= '<div class="value">'.$statistics["Data"]["TotalUnsettledBets"].'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-warning mb-0p5">';
	$body .= '<i class="icon fa fa-database"></i>';
	$body .= '<span class="text">Sport Total Bets</span>';
	$body .= '<div class="value ">'.$statistics["Data"]["TotalSportBets"].'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline mb-0p5">';
	$body .= '<i class="icon fa fa-trophy"></i>';
	$body .= '<span class="text">Total Casino Bonus Winnings</span>';
	$body .= '<div class="value ">S/ '.number_format($statistics["Data"]["TotalCasinoBonusWinings"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-primary mb-0p5">';
	$body .= '<i class="icon fa fa-dollar"></i>';
	$body .= '<span class="text">Deposit Count</span>';
	$body .= '<div class="value">'.$statistics["Data"]["DepositCount"].'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-success mb-0p5">';
	$body .= '<i class="icon fa fa-calendar"></i>';
	$body .= '<span class="text">Last WithDrawal Date</span>';
	$body .= '<div class="value date">'.($statistics["Data"]["LastWithdrawalTimeLocal"] ? date('Y-m-d H:i:s', strtotime($statistics["Data"]["LastWithdrawalTimeLocal"])) : "No data to display.").'</div>';
	$body .= '</div>';
	$body .= '</div>';
	$body .= '<div class="col-xs-12 col-md-2">';
	$body .= '<div class="card-box-inline card-box-inline-warning mb-0p5">';
	$body .= '<i class="icon fa fa-money"></i>';
	$body .= '<span class="text">Total Sport Bonus Stakes</span>';
	$body .= '<div class="value ">'.number_format($statistics["Data"]["TotalSportBonusStakes"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline mb-0p5">';
	$body .= '<i class="icon fa fa-ravelry"></i>';
	$body .= '<span class="text">Sport Total Unsettled Bets</span>';
	$body .= '<div class="value ">'.$statistics["Data"]["TotalUnsettledBets"].'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-primary mb-0p5">';
	$body .= '<i class="icon fa  fa-area-chart"></i>';
	$body .= '<span class="text">Gaming P/L</span>';
	$body .= '<div class="value">'.number_format($statistics["Data"]["GamingProfitAndLose"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-success mb-0p5">';
	$body .= '<i class="icon fa fa-calendar"></i>';
	$body .= '<span class="text">First Deposit Date</span>';
	$body .= '<div class="value date">'.($statistics["Data"]["FirstDepositTimeLocal"] ? date('Y-m-d H:i:s', strtotime($statistics["Data"]["FirstDepositTimeLocal"])) : "No data to display.").'</div>';
	$body .= '</div>';
	$body .= '<div class="card-box-inline card-box-inline-info mb-0p5">';
	$body .= '<i class="icon fa fa-dollar"></i>';
	$body .= '<span class="text">Net Revenue</span>';
	$body .= '<div class="value">'.number_format($statistics["Data"]["ProfitAndLose"]+$statistics["Data"]["GamingProfitAndLose"], 2, '.', ',').'</div>';
	$body .= '</div>';
	$body .= '</div>';
	$body .= '</div>';

	echo die(action_response('200', $body));
}

elseif(isset($_POST["get_player_bonus_data"])) {
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Retiros.'));
	}

	$data = $_POST["get_player_bonus_data"];

	$API_Authentication = leech_authentication();
	if(leech_login()){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://backofficewebadmin.betconstruct.com/api/en/Client/GetClientBonuses');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Authentication: '.$API_Authentication
		]);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
			"AcceptanceType" 	=> null,
			"BonusType" 	=> null,
			"ClientId" 	=> $data["client_id"],
			"EndDateLocal" 	=> null,
			"PartnerBonusId" 	=> "",
			"StartDateLocal" 	=> null,
		]));

		$response = json_decode(curl_exec($ch), true);

		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}

		$response_data = [];

		if (!$response["HasError"] && isset($response["Data"])){
			echo json_encode(['data' => $response["Data"]]);
			die();
		}
		echo json_encode(['data' => '']);
		die();
	}
}

elseif(isset($_POST["set_cuotas_approval"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("edit", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para Modificar Retiros.'));
	}

	$data = $_POST["set_cuotas_approval"];
	$status = ((int)$data["status"] == 1 ?: 2);

	$query = "
		UPDATE tbl_client_requests
		SET cuota_status = {$status}
		WHERE id = ".$data["request_id"]."
	";
	$mysqli->query($query);

	die(action_response(200, $status));
}
elseif(isset($_POST["set_final_status"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("edit", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para Modificar Retiros.'));
	}

	$data  = $_POST["set_final_status"];

	$query = "
		SELECT
			state,
			state_bc
		FROM tbl_client_requests
		WHERE id = ".$data["request_id"]."
	";
	$client_request = [];
	$query_result = $mysqli->query($query);
	if($mysqli->error){ echo $mysqli->error; die; }
	if($r = $query_result->fetch_assoc()) $client_request = $r;

	if(isset($data["state"]) && $data["state"] == 'deny'){
		$query = "
			UPDATE tbl_client_requests
			SET state = -2
			WHERE id = ".$data["request_id"]."
		";
		$mysqli->query($query);
	}
	else{
		$state = false;
		if(
			$client_request["state"] == 0
			&& !in_array($client_request["state_bc"], ['Cancelled', 'Rejected'])
			&& ($login["area_id"] == 6 || ($login["area_id"] == 9 && $login["cargo_id"] == 16))
		){
			$state = 1;
		}
		elseif(
			$client_request["state"] == 1
			&& !in_array($client_request["state_bc"], ['Cancelled', 'Rejected'])
			&& ($login["area_id"] == 6 || ($login["area_id"] == 9 && $login["cargo_id"] == 16))
		){
			$state = 3;
		}

		if($state){
			$query = "
				UPDATE tbl_client_requests
				SET state = {$state}
				WHERE id = ".$data["request_id"]."
			";
			$mysqli->query($query);
		}
	}

	die(action_response(200));
}
elseif(isset($_POST["set_file_upload"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("generate", $usuario_permisos[$menu_id])){
		die(action_response('401', 'No Autorizado. Solo puedes buscar DNIs contenidos en nuestra base de datos.'));
	}

	$data = $_POST;

	if(isset($_FILES['fileRetiroDNI'])) {
		$path = "";
		$valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx', 'csv');

		$file = $_FILES['fileRetiroDNI']['name'];
		$tmp = $_FILES['fileRetiroDNI']['tmp_name'];
		$size = $_FILES['fileRetiroDNI']['size'];
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		$final_file = strtolower($data["txtRetiroDNI"]."_".date('YmdHis').".".$ext);
		if(in_array($ext, $valid_extensions)) {
			$path = 'files_bucket/client_documents/'.$final_file;
			move_uploaded_file($tmp,'/var/www/html/'.$path);

			$query = "
				INSERT INTO tbl_client_documents (
					client_id,
					doc_type,
					doc_number,
					file_path,
					session_id,
					created_at,
					updated_at
				)
				VALUES(
					".$data['txtRetiroDNIClientId'].",
					'dni',
					".$data['txtRetiroDNI'].",
					'".$path."',
					'".$login["sesion_cookie"]."',
					'".date('Y-m-d H:i:s')."',
					'".date('Y-m-d H:i:s')."'
				)
			";
			$mysqli->query($query);
		}
	}
	else{
		echo "<pre>"; var_dump($_POST, $_FILES); echo "</pre>"; die;
	}
}
elseif(isset($_POST["check_database_status"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Status.'));
	}

	$query = "
	    SELECT
		(SELECT DATE_ADD(MAX(col_RequestTime), INTERVAL -9 HOUR) FROM bc_apuestatotal.tbl_ClientRequest) AS 'Última Solicitud',
		(SELECT DATE_ADD(MAX(col_Created), INTERVAL -9 HOUR) FROM bc_apuestatotal.tbl_Document) AS 'Último Documento',
		(SELECT MAX(created_at) FROM bc_apuestatotal.tbl_Transaction) AS 'Última Transacción',
		(SELECT DATE_ADD(MAX(col_Created), INTERVAL -9 HOUR) FROM bc_apuestatotal.tbl_Bet) AS 'Última Apuesta',
		(SELECT DATE_ADD(MAX(col_Created), INTERVAL -9 HOUR) FROM bc_apuestatotal.tbl_Client) AS 'Último Cliente Registrado',
		(SELECT DATE_ADD(MAX(col_Modified), INTERVAL -9 HOUR) FROM bc_apuestatotal.tbl_Balance) AS 'Último Balance Registrado'
	";
	$summary = false;
	$query_result = $mysqli->query($query);
	if($mysqli->error){ echo $mysqli->error; die; }
	if($r = $query_result->fetch_assoc()) {
		$summary = $r;
	}

	$body = '';
	$body .= '<table class="table table-striped table-hover">';
	$body .= '<thead>';
	$body .= '<tr>';
	$body .= '<th scope="col">Descripción</th>';
	$body .= '<th scope="col">Última Fecha</th>';
	$body .= '</tr>';
	$body .= '</thead>';
	$body .= '<tbody>';
	foreach($summary as $key => $table) {
		$body .= '<tr>';
		$body .= '<th scope="row">'.$key.'</th>';
		$body .= '<td>'.$table.'</td>';
		$body .= '</tr>';
	}
	$body .= '</tbody>';
	$body .= '</table>';
	$body .= '</div>';

	die(action_response(200, $body));
}
elseif(isset($_POST["check_import_status"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Retiros.'));
	}

	$query = "
		SELECT
		    ph.status,
		    ph.updated_at,
		    (
		        SELECT DATE_ADD(id.col_Created, INTERVAL -9 HOUR)
		        FROM bc_apuestatotal.tbl_Document id
		        ORDER BY id.col_Created DESC
		        LIMIT 1
		    ) AS created_at
		FROM tbl_client_requests_process_history ph
		ORDER BY ph.created_at DESC
		LIMIT 1
	";
	$query_result = $mysqli->query($query);
	if($mysqli->error){ echo $mysqli->error; die; }
	if($r = $query_result->fetch_assoc()) $status = $r;

	die(action_response(200, $status));
}
elseif(isset($_POST["accept_request"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("edit", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para Modificar Retiros.'));
	}

	$data = $_POST["accept_request"];
	return action_response(200, $data);

	process_request(1, ["Id" => $data["request_id"]]);
}
elseif(isset($_POST["decline_request"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("edit", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para modificar Retiros.'));
	}

	$data = $_POST["decline_request"];
	return action_response(200, $data);

	process_request(2, [
		"Id" => $data["request_id"],
		"ClientNotes" => $data["client_notes"],
		"RejectReason" => $data["reject_reason"]
	]);
}
elseif(isset($_POST["import_requests"])){
	exec("php /var/www/html/cron/withdrawals/client_requests.php");
}
elseif(isset($_POST["refresh_request"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("edit", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para Modificar Retiros.'));
	}

	$data = $_POST["refresh_request"];

	populate_requests([
		'Id' => $data["request_id"]
	]);

	die(action_response(200));
}

elseif(isset($_POST["get_duplicated_bank_accounts_status"])){
	if(!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Retiros.'));
	}

	// POST DATA
	$data = $_POST["get_duplicated_bank_accounts_status"];
	$client_id = $data["client_id"];
	$bank_account = $data["bank_account"];
	$cci = $data["bank_cci"];

	$status = get_duplicated_bank_accounts_status($client_id, $bank_account, $cci);
	echo json_encode(['status' => $status]);
}
elseif(isset($_POST["get_duplicated_bank_accounts"])){
	if(!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Retiros.'));
	}

	// POST DATA
	$data = $_POST["get_duplicated_bank_accounts"];
	$client_id = $data["client_id"];
	$bank_account = $data["bank_account"];
	$cci = $data["bank_cci"];

	$query = "
			SELECT c.col_Id,c.col_Name, col_Email, cba.cuenta, cba.cci
			FROM tbl_client_bank_account cba
			JOIN bc_apuestatotal.tbl_Client c
			ON c.col_Id = cba.client_id
			WHERE ((cuenta = '$bank_account' AND (cuenta != '' AND cuenta IS NOT null)) 
			   OR (cci = '$cci' AND (cci != '' AND cci IS NOT NULL)));
		";

	$query_result = $mysqli->query($query);
	if ($mysqli->error) {
		echo json_encode(['data' => null]);
		die;
	}
	$clients = array();
	while ($client = $query_result ->fetch_assoc()){
		$clients[] = $client;
	}
	echo json_encode(['data' => $clients]);
}

elseif (isset($_POST["execute_withdrawal_action"])) {
	if(!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		die(action_response('403', 'No Autorizado. No tienes permisos para ver Retiros.'));
	}

	// POST DATA
	$data = $_POST["execute_withdrawal_action"];
	$token = env('SOPORTE_V2_TOKEN');
	$rq = [
		"id" => $data["id"],
		"type" => $data["action"]
	];

	$request_headers = array();
	$request_headers[] = "Content-type: application/json";
	$request_headers[] = "Authorization: "."Bearer $token";
	$request_json = json_encode($rq);
	$curl = curl_init("https://api.apuestatotal.com/v2/betconstruct/withdrawal_requests");
	curl_setopt($curl, CURLOPT_HTTPHEADER,$request_headers);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
	$response = curl_exec($curl);
	$response_arr = json_decode($response, true);
	curl_close($curl);


	echo json_encode(['data' => $response_arr]);
}

function save_bank_accounts($client_requests){
	global $mysqli;
	foreach ($client_requests as $client_request){
		if(!is_null($client_request["BetshopId"])) continue;

		preg_match('~Numero de Cuenta:(.*?),~', $client_request["Info"], $bank_account);
		preg_match('~Numero CCI - 20 Digitos:(.*?),~', $client_request["Info"], $cci);
		$client_id = $client_request["ClientId"];

		$bank_account = $bank_account ? (array_key_exists(1, $bank_account) ? $bank_account[1] : null) : null;
		$bank_account = preg_replace('/[^0-9]/', '', $bank_account);
		$cci = $cci ? (array_key_exists(1, $cci) ? $cci[1] : null) : null;
		$cci = preg_replace('/[^0-9]/', '', $cci);

		if (!$bank_account && !$client_id) continue;

		//Save the bank account
		$query = "
			INSERT INTO tbl_client_bank_account (client_id, cuenta, cci, created_at, updated_at)
			VALUES ('$client_id', '$bank_account', '$cci', now(),now())
			ON DUPLICATE KEY UPDATE updated_at = now()
		";

		$mysqli->query($query);
	}
}

function get_duplicated_bank_accounts_status($client_id,$bank_account, $cci): string
{
	global $mysqli;

	$query = "
		SELECT
			(
				SELECT COUNT(*)
				FROM tbl_client_bank_account
				WHERE ((cuenta = '$bank_account' AND (cuenta != '' AND cuenta IS NOT null))
				OR (cci = '$cci' AND (cci != '' AND cci IS NOT NULL)))
			) as all_accounts,
			(
				SELECT COUNT(*)
				FROM tbl_client_bank_account
				WHERE ((cuenta = '$bank_account' AND (cuenta != '' AND cuenta IS NOT null))
				OR (cci = '$cci' AND (cci != '' AND cci IS NOT NULL)))
				AND client_id = '$client_id'
			) as client_accounts
		";

	$result = $mysqli->query($query);
	if ($mysqli->error) {
		return "error";
	}

	$all_accounts = 0;
	$client_accounts = 0;
	$others_accounts = 0;

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$all_accounts = $row["all_accounts"] ?? 0;
			$client_accounts = $row["client_accounts"] ?? 0;
			$others_accounts = $all_accounts - $client_accounts;
		}

		if ($all_accounts > 0){
			if ($others_accounts > 0) return "duplicated_accounts";
			else if ($client_accounts > 1) return "client_multiple_accounts";
			else if ($client_accounts == 1) return "client_unique_account";
			return "no_client_accounts";
		} else {
			return "no_accounts";
		}
	}else{
		return "error";
	}
}

function get_deposits_total($from_date, $request_id){
	global $mysqli;

	if ($from_date){
		$sumTotal = 0;

		$query = "
			SELECT
				IFNULL((
					SELECT SUM(id.col_Amount)
					FROM bc_apuestatotal.tbl_Document AS id
					WHERE
						id.col_ClientId = cr.col_ClientId
						AND id.col_Created >= '$from_date'
						AND id.col_Created < cr.col_RequestTime
						AND id.col_TypeId IN(3, 5)
				    LIMIT 1
				), 0 ) AS SumDepos							
			FROM bc_apuestatotal.tbl_ClientRequest cr
			WHERE cr.col_Id = '$request_id'
		";

		$query_result = $mysqli->query($query);
		if($mysqli->error){ return null; }
		if($r = $query_result->fetch_assoc()) {
			$sumTotal = $r["SumDepos"];
		}
		return $sumTotal;
	}
	else {
		return null;
	}
}

function get_deposits_total_v2($from_date, $client_id, $request_date){
	global $mysqli;

	if ($from_date){
		$sumTotal = 0;

		$query = "
			SELECT SUM(col_DepositAmount) as deposits_sum
			FROM bc_apuestatotal.tbl_ClientKPI
			WHERE col_ClientId = '$client_id' 
			AND col_Date >= DATE('$from_date') 
			AND col_Date <= DATE('$request_date')
		";

		$query_result = $mysqli->query($query);
		if($mysqli->error){ return null; }
		if($r = $query_result->fetch_assoc()) {
			$sumTotal = $r["deposits_sum"];
		}
		return $sumTotal;
	}
	else {
		return null;
	}
}

function get_depositos_televentas_total($from_date, $client_id){
	global $mysqli;

	if ($from_date){
		$total = -1;

		$query = "
			SELECT
				SUM(cd.col_Amount) as total
			FROM
				bc_apuestatotal.at_ClientDeposits cd
			WHERE
				cd.col_ClientId = '$client_id' AND
			  	cd.col_PaymentSystemId = 1630 AND
				cd.col_Created >= '$from_date'
			;
		";

		$query_result = $mysqli->query($query);
		if($mysqli->error){ return null; }
		if($r = $query_result->fetch_assoc()) {
			$total = $r["total"];
		}
		return $total;
	}
	else {
		return null;
	}
}

function get_retiros_quota_status($from_date, $request_id){
	global $mysqli;

	if ($from_date){
		$quota_status = -1;

		$query = "
			SELECT
				IF((
					SELECT COUNT(ib.col_Id)
					FROM bc_apuestatotal.tbl_Bet ib
					WHERE
						ib.col_ClientId = cr.col_ClientId
						AND ib.col_Created >= '$from_date'
						AND ib.col_Created < cr.col_RequestTime
						AND ib.col_Price < 1.2
				    	AND ib.col_WinningAmount >= 100
				) > 0,0,1) as cuota_status				
			FROM bc_apuestatotal.tbl_ClientRequest cr
			WHERE cr.col_Id = '$request_id'
		";

		$query_result = $mysqli->query($query);
		if($mysqli->error){ return null; }
		if($r = $query_result->fetch_assoc()) {
			$quota_status = $r["cuota_status"];
		}
		return $quota_status;
	}
	else {
		return null;
	}
}

function get_from_date_request($client_id, $request_time, $request_id) {
	global $mysqli;
	$from_date = null;
	$query = "
			SELECT DATE_ADD(IFNULL
			        (
			        	(
			        	    SELECT cr.col_RequestTime
			        	    FROM bc_apuestatotal.tbl_ClientRequest cr
			        	    WHERE cr.col_State = 3
							AND cr.col_ClientId = '$client_id'
							AND cr.col_RequestTime < '$request_time'
							AND cr.col_Id <> '$request_id'
							ORDER BY cr.col_RequestTime  DESC
							LIMIT 1
			        	), '2000-01-01'
			        ),
				INTERVAL -1 SECOND
			) AS from_date
		";

	$query_result = $mysqli->query($query);
	if($mysqli->error){ return null;}
	if($r = $query_result->fetch_assoc()) {
		$from_date = $r["from_date"];
	}

	return $from_date;
}

function get_bonus_player_status($from_date, $client_id){
	global $mysqli;

	if ($from_date){
		$bonus_player_status = false;

		$query = "
		 	SELECT * 
			FROM bc_apuestatotal.tbl_ClientBonus
			WHERE col_ClientId = '$client_id' AND
			col_ResultType = 1 AND
			col_Created >= '$from_date';
		";

		$query_result = $mysqli->query($query);
		if($mysqli->error){ return null; }
		if($query_result->num_rows > 0) {
			$bonus_player_status = true;
		}
		return $bonus_player_status;
	}
	return null;
}
?>
