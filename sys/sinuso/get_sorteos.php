<?php

include("db_connect.php");
include("sys_login.php");
include("helpers.php");

$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'sorteos' AND sub_sec_id IS NULL LIMIT 1")->fetch_assoc();
$sorteo_id = 104;

if(strtotime("now") > strtotime(date('2020-01-30'))){
	echo "Evento terminado"; die;
}

function action_response($code, $message=""){
	return json_encode(['code' => $code, 'message' => $message]);
}

if(isset($_POST["process_tickets"])){
	if(!array_key_exists($this_menu["id"],$usuario_permisos) || !in_array("view", $usuario_permisos[$this_menu["id"]])){
		die(action_response('403', 'No Autorizado. No tienes permisos para acceder a este area del sistema.'));
	}

	$zonas = [];
	$response = $mysqli->query("
		SELECT
			l.cc_id,
			(
				IF(substring(l.ubigeo_id,1,2) IN ('02','06','12','13','19','23'), '1', -- Norte
					IF(substring(l.ubigeo_id,1,2) IN ('01','15','16','21','25'), '4', -- Oriente
						IF(substring(l.ubigeo_id,1,2) IN ('24','09','11','14','18'), '2', -- Centro
							IF(substring(l.ubigeo_id,1,2) IN ('03','04','05','07','10','17','20','22'), '3', -- Sur
							NULL
							)
						)
					)
				)	
			) AS 'zona_id',
			lp.proveedor_id
		FROM tbl_locales l
		INNER JOIN tbl_local_proveedor_id lp ON (lp.local_id = l.id)
		WHERE lp.estado = 1
		AND lp.canal_de_venta_id = 21
	");
	while($r = $response->fetch_assoc()) $zonas[$r["proveedor_id"]] = $r;

	$data = $_POST["process_tickets"];

	if(empty($data["tickets"]) || !isset($data["tickets"])){
		die(action_response('403', 'No Autorizado. No hay tickets aceptados'));
	}
	$tickets = array_filter($data["tickets"]);

	$query = "
		SELECT
			ticket_id,
			stake_amount,
			ticket_status,
			unit_id
		FROM tbl_repositorio_tickets_goldenrace
		WHERE ticket_id IN (".implode($tickets, ",").");
	";
	$db_tickets = [];
	$query_result = $mysqli->query($query);
	while($r = $query_result->fetch_assoc()) $db_tickets[$r["ticket_id"]] = $r;

	$query = "
		SELECT ticket_id
		FROM tbl_sorteo_ticket
		WHERE
			sorteo_id = $sorteo_id
			AND source_id = 'ges'
			AND ticket_id IN (".implode($tickets, ",").")
	";
	$repeated_tickets = [];
	$query_result = $mysqli->query($query);
	while($r = $query_result->fetch_assoc()) $repeated_tickets[] = $r["ticket_id"];

	$validated_tickets = [];
	$count_options = 0;
	$zona_id = null;
	$local_id = null;
	foreach (array_unique(array_diff($tickets, $repeated_tickets)) as $ticket_number) {
		if(!isset($db_tickets[$ticket_number])){
			$api_ticket = get_curl("http://admin.golden-race.net/qr/ticket_stake?tickets={$ticket_number}");

			$db_tickets[$ticket_number] = [
				'ticket_id' => $ticket_number,
				'stake_amount' => (empty($api_ticket) ? null : $api_ticket[0]["stake_amount"]),
				'ticket_status' => (empty($api_ticket) ? 'Not Found' : $api_ticket[0]["ticket_status"]),
				'unit_id' => $api_ticket[0]["unit_id"]
			];
		}

		if(!$local_id){
			$local_id = (isset($zonas[$db_tickets[$ticket_number]["unit_id"]]) ? $zonas[$db_tickets[$ticket_number]["unit_id"]]["cc_id"] : null);
		}
		if(!$zona_id){
			$zona_id = (isset($zonas[$db_tickets[$ticket_number]["unit_id"]]) ? $zonas[$db_tickets[$ticket_number]["unit_id"]]["zona_id"] : null);
		}

		if(in_array($db_tickets[$ticket_number]["ticket_status"], ["Won", "Paid Out"]) && $db_tickets[$ticket_number]["stake_amount"] >= 2){
			$db_tickets[$ticket_number]["options"] = (int)floor($db_tickets[$ticket_number]["stake_amount"]);
			$count_options += $db_tickets[$ticket_number]["options"];

			$validated_tickets[] = [
				'at_unique_id' => md5($sorteo_id.$ticket_number),
				'sorteo_id' => $sorteo_id,
				'ticket_id' => $ticket_number,
				'document_number' => $data["dni"],
				'source_id' => 'ges'
			];
		}
		else{
			$db_tickets[$ticket_number]["options"] = 0;
		}
	}

	if(!empty($validated_tickets)){
		feed_database($validated_tickets, 'tbl_sorteo_ticket', 'at_unique_id', true);

		$request = [
			'num_dni' => $data["dni"],
			'sorteo_id' => $sorteo_id,
			'zona_id' => $zona_id,
			'cc_id' => $local_id,
			'num_telefono' => $data["phone"],
			'num_opciones' => $count_options
		];

		$response = post_curl("https://sorteos.apuestatotal.com/api/OpcionesAcumuladas", $request, [], false);
		$resp = json_decode($response,true);
		$total = $resp['Resultados']['num_opciones'];
		die(action_response(200, [
			[
				'dni' => $data['dni'],
				'phone' => $data['phone'],
				'OpcAcu' => $count_options,
				'total' => $total
			],
			$request
		]
));
	} else{
		die(action_response(400, 'Tickets no fueron procesados. Por favor vuelve a intentar o contacte soporte.'));
	}
}

if(isset($_POST["validate_dni"])){
	if(
		!array_key_exists($this_menu["id"],$usuario_permisos)
		|| !in_array("view", $usuario_permisos[$this_menu["id"]])
		|| !isset($_POST["validate_dni"]["dni"])
		|| preg_match('/^\d{8}$/', $_POST["validate_dni"]["dni"])
	){
		die(action_response('403', 'No Autorizado. No tienes permisos para acceder a este area del sistema.'));
	}

	$dni = get_dni($_POST["validate_dni"]["dni"]);
}

if(isset($_POST["get_ticket"])) {
	if(!array_key_exists($this_menu["id"],$usuario_permisos) || !in_array("view", $usuario_permisos[$this_menu["id"]])){
		die(action_response('403', 'No Autorizado. No tienes permisos para acceder a este area del sistema.'));
	}

	if(isset($_POST["get_ticket"]["ticket"]) && preg_match('/^\d{9,10}$/', $_POST["get_ticket"]["ticket"])){
		$query = "
			SELECT id
			FROM tbl_sorteo_ticket
			WHERE
				sorteo_id = $sorteo_id
				AND ticket_id = '".$_POST["get_ticket"]["ticket"]."'
		";
		$query_result = $mysqli->query($query);

		if(!$query_result->fetch_assoc()) {
			$db_ticket = false;
			$query = "
				SELECT
					ticket_id,
					stake_amount,
					ticket_status,
					game,
					time_played
				FROM tbl_repositorio_tickets_goldenrace
				WHERE
					ticket_status IN ('Won', 'Paid Out')
					AND ticket_id = ".$_POST["get_ticket"]["ticket"].";
			";
			$query_result = $mysqli->query($query);
			if($r = $query_result->fetch_assoc()){
				$db_ticket = $r;
			}

			if(!$db_ticket){
				$api_ticket = get_curl("http://admin.golden-race.net/qr/ticket_stake?tickets=".$_POST["get_ticket"]["ticket"]);

				$db_ticket = [
					'ticket_id' => $_POST["get_ticket"]["ticket"],
					'stake_amount' => (empty($api_ticket) ? null : $api_ticket[0]["stake_amount"]),
					'ticket_status' => (empty($api_ticket) ? 'Not Found' : $api_ticket[0]["ticket_status"]),
					'game' => (empty($api_ticket) ? null : $api_ticket[0]["game"]),
					'time_played' => (empty($api_ticket) ? null : date('Y-m-d H:i:s', $api_ticket[0]["time_played"])),
				];
			}

			$ignoreGames = [
				'Spin 2 Win',
				'Spin2Win Apuesta Total',
				'sn'
			];

			$acceptStates = [
				'Won',
				'Paid Out'
			];

			if($db_ticket["ticket_status"] == "Not Found"){
				die(action_response('403', 'Vuelve intentar en 5 minutos.'));
			}
			if(!in_array($db_ticket["ticket_status"], $acceptStates)){
				die(action_response('403', 'El ticket no es ganado.'));
			}
			if($db_ticket["stake_amount"] < 2){
				die(action_response('403', 'El apostado es inferior a 2 soles.'));
			}
			if(in_array($db_ticket["game"], $ignoreGames)){
				die(action_response('403', 'El sorteo no se aplica al Juego Spin 2 Win.'));
			}
			if(strtotime($db_ticket["time_played"]) < strtotime(date('Y-m-d H:i:s', strtotime('-3 Days')))){
				die(action_response('403', 'Ticket expirado. Plazo es de 72 Horas para registrar.'));
			}

			die(action_response('200', [
				"message" => "Aceptado.",
				"options" => (int)floor($db_ticket["stake_amount"])
			]));
		}
		die(action_response('403', 'Este ticket ya fue utilizado.'));
	}
	die(action_response('400', 'Este ticket no existe.'));
}

?>
