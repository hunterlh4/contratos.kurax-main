<?php
include_once("db_connect.php");
include_once("sys_login.php");
include_once("/var/www/html/cron/cron_bc_leech.php");
require_once '/var/www/html/env.php';

global $mysqli;
global $usuario_permisos;
$this_menu = $mysqli->query("
	SELECT id 
	FROM tbl_menu_sistemas 
	WHERE sec_id = 'tesoreria' 
	AND sub_sec_id = 'retiros_web' 
	LIMIT 1
")->fetch_assoc();
$menu_id = $this_menu["id"];

function tesoreria_retiros_web_authorization($menu_id, $usuario_permisos, $message = "No Autorizado."){
    if(!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
        die(json_encode(['code' => "403", 'message' => $message]));
    }
}

if(isset($_POST["get_tesoreria_retiros_api"])){
    tesoreria_retiros_web_authorization($menu_id, $usuario_permisos);

    // ENUM
    $state_at = [1 => "Allowed", 12 => "Enviado", 13 => "Observado"];

    // POST DATA
    $data = $_POST["get_tesoreria_retiros_api"];

    // DATA CLEANING
    $start_date = $data["start_date"] ?: date("d-m-y");
    $start_time = $data["start_time"] ?: date("H:i:s", strtotime("00:00:00"));
    $from_date_local = $start_date . " - " . $start_time;

    $end_date = $data["end_date"] ?: date("d-m-y", strtotime("+1 days"));
    $end_time = $data["end_time"] ?: date("H:i:s", strtotime("00:00:00"));
    $to_date_local = $end_date . " - " . $end_time;

    $state = $data["state"];

    // Leech BO Authentication
    $API_Authentication = leech_authentication();
    if(!$API_Authentication) {
        echo json_encode(['data' => "No connection, intente nuevamente."]);
        die();
    }

    // BO API Call
    $payment_type_id = 742; // BankTransferBME
    $state_list = [1]; // Allowed
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://backofficewebadmin.betconstruct.com/api/en/Client/GetClientWithdrawalRequestsWithTotals');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Authentication: '. $API_Authentication]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        json_encode([
        "BetShopId" 	=> "",
        "ByAllowDate" 	=> false,
        "ClientId" 		=> "",
        "ClientLogin" 	=> "",
        "Email"			=> "",
        "FromDateLocal" => $from_date_local,
        "Id" 			=> null,
        "IsTest" 		=> "",
        "PaymentTypeId" => $payment_type_id,
        "StateList" 	=> $state_list,
        "ToDateLocal" 	=> $to_date_local
    ]));

    $response = json_decode(curl_exec($ch), true);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        die();
    }

    $response_data = [];
    if (!(!$response["HasError"] && isset($response["Data"]))){
        curl_close($ch);
        echo json_encode(['data' => "Error de API"]);
        die();
    }

    $rows = array();
    foreach ($response["Data"]["ClientRequests"] as $client_request){
        $request_state_row = save_client_request_state($client_request["Id"], $client_request["State"]);

        preg_match('~Nombre del Banco:(.*?),~', $client_request["Info"], $bank);
        preg_match('~Numero de Cuenta:(.*?),~', $client_request["Info"], $bank_account);
        preg_match('~Numero CCI - 20 Digitos:(.*?),~', $client_request["Info"], $bank_cci);
        preg_match('~Tipo de Cuenta:(.*?),~', $client_request["Info"], $bank_account_type);

        $bank = $bank ? (array_key_exists(1, $bank) ? $bank[1] : null) : null;
        $bank_account = $bank_account ? (array_key_exists(1, $bank_account) ? $bank_account[1] : null) : null;
        $bank_cci = $bank_cci ? (array_key_exists(1, $bank_cci) ? $bank_cci[1] : null) : null;
        $bank_account_type = $bank_account_type ? (array_key_exists(1, $bank_account_type) ? $bank_account_type[1] : null) : null;

        $row = [
            "id" => $client_request["Id"],
            "client_id" => $client_request["ClientId"],
            "client_name" => $client_request["ClientName"],
            "amount" => $client_request["Amount"],
            "state_name" => $client_request["StateName"],
            "state_at" => $request_state_row["estado_at"],
            "state_at_name" => $state_at[$request_state_row["estado_at"]],
            "request_time" => $client_request["RequestTime"] ? date('Y-m-d H:i:s', strtotime($client_request["RequestTime"])) : null,
            "allow_time" => $client_request["AllowTime"] ? date('Y-m-d H:i:s', strtotime($client_request["AllowTime"])) : null,
            "state_at_update_time" => $request_state_row["updated_at"],
            "payment_type" => $client_request["PaymentSystemName"],
            "bank" => $bank,
            "account_holder" => $client_request["AccountHolder"],
            "btag" => null,
            "currency" => $client_request["CurrencyId"],
            "phone" => $client_request["Phone"],
            "doc_number" => $client_request["DocNumber"],
            "bank_account" => $bank_account,
            "bank_cci" => $bank_cci,
            "bank_account_type" => $bank_account_type,
        ];

        $query = "SELECT col_BTag, col_Created FROM bc_apuestatotal.tbl_Client WHERE col_id = $row[client_id] LIMIT 1;";
        $query_result = $mysqli->query($query);
        if($mysqli->error){ $row["btag"] = null; continue;}
        while($r = $query_result->fetch_assoc()) {
            $row["btag"] = $r["col_BTag"];
        }

        if ($state === "-1") $rows[] = $row;
        else if ($state === $request_state_row["estado_at"]) $rows[] = $row;
    }
    curl_close($ch);
    echo json_encode(['data' => $rows]);
}

if(isset($_POST["post_retiro_kashio_api"])){
    $token = env("TESORERIA_TOKEN");

    // ENUM
    $banks = ["BCP" => "psp_w13k323ed23dmd01", "BBVA" => "psp_w13k12312341md02","SCOTIABANK" => "psp_w133203223m3md03","INTERBANK" => "psp_w0328223930dmd04","Otros" => "psp_w13k323ed23dmd00"];
    $account_types = ["cuentadeahorros" => "SAVING", "cuentacorriente" => "CHECKING"];

    // POST DATA
    $data = $_POST["post_retiro_kashio_api"];

    // DATA TREATMENT
    $bank_name = strtoupper(trim($data["client"]["bank"]["name"]));
    $bank_id = array_key_exists($bank_name, $banks) ? $banks["$bank_name"] : $banks["Otros"];
    $account_type_name = strtolower(preg_replace("/\s+/", "", $data["client"]["bank"]["type"]));
    $account_type = array_key_exists($account_type_name, $account_types) ? $account_types["$account_type_name"] : $account_types["SAVING"];
    $client_data = get_client_data_bo($data["client"]["id"]);
    $client_email = $client_data["Email"] ?? "";
    $data["client"]["document_type"]=str_replace(' ','',$data["client"]["document_type"]);
    if ($data["client"]["document_type"]=='' || empty($data["client"]["document_type"])) {
        $data["client"]["document_type"] = 'DNI';
    }

    $api_post_data = [
        "customer" 	=> [
            "external_id" => $data["client"]["id"],
            "name" => $data["client"]["name"],
            "phone" => $data["client"]["phone"],
            "email" => $client_email,
            "document_type" => $data["client"]["document_type"],
            "document_id" => $data["client"]["document_number"],
            "accounts" => [
                [
                    "bank" => [
                        "id" => $bank_id,
                    ],
                    "account_number" => $data["client"]["bank"]["account_number"],
                    "type" => $account_type,
                    "cci" => $data["client"]["bank"]["cci"],
                ]
            ]
        ],
        "external_id" 	=> $data["id"],
        "total" 	=> [
            "currency" => $data["currency"],
            "value" => (double) $data["total"]
        ],
        "metadata" 	=> [
            "order_id" => $data["id"],
            "order_name" => "Payout $data[id]",
            "order_description" => "Payout $data[id]",
        ],
    ];

    $json_data = json_encode($api_post_data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.apuestatotal.com/v2/kashio/payouts');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Authorization: Bearer '. $token]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

    $response = json_decode(curl_exec($ch), true);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        die();
    }

    if (!$response["result"]){
        curl_close($ch);
        echo json_encode(['data' => "No se pudo conectar al API"]);
        die();
    }
    $external_id = $response["result"]["_id"] ?? null;
    if ($response["http_code"] === 200){
        $query = "UPDATE bc_web_client_withdrawal_request SET estado_at = '12', external_id = '$external_id' WHERE id_solicitud = '$data[id]';";
        $a = $mysqli->query($query);
    }

    curl_close($ch);
    echo json_encode(['data' => $response]);
}

function save_client_request_state($request_id, $state){
    if (!$request_id || !$state) return;

    // '1: Allowed, 12: Enviado, 13: Observado',
    global $mysqli;

    // Inserting to the database
    $query = "
        INSERT INTO bc_web_client_withdrawal_request(id_solicitud, estado_bc, estado_at, created_at, updated_at)
        VALUES ($request_id, $state, $state, now(), now())
        ON DUPLICATE KEY UPDATE estado_at = IF((estado_at IN (12, 13)), estado_at, $state), updated_at = now()";
    $mysqli->query($query);

    // Retrieving the inserted row
    $query = "SELECT id_solicitud, estado_bc, estado_at, updated_at FROM bc_web_client_withdrawal_request WHERE id_solicitud = $request_id";
    $query_result = $mysqli->query($query);
    $inserted_row = $query_result->fetch_assoc();

    return $inserted_row;
}

function get_client_data_bo($client_id) {
    $API_Authentication = leech_authentication();
    if(!$API_Authentication) {
        echo json_encode(['data' => "No connection, intente nuevamente."]);
        die();
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://backofficewebadmin.betconstruct.com/api/en/Client/GetClientById?id=$client_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Authentication: '. $API_Authentication]);
    $response = json_decode(curl_exec($ch), true);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        die();
    }

    if (!(!$response["HasError"] && isset($response["Data"]))){
        curl_close($ch);
        echo json_encode(['data' => "Error de API"]);
        die();
    }
    $response = $response["Data"];

    curl_close($ch);
    return $response;
}