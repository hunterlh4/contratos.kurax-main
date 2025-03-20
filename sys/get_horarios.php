<?php

include("db_connect.php");
include("sys_login.php");

$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'horarios' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

if(isset($_POST["get_horarios"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		echo json_encode(['body' => "No tienes permisos para ver este recurso", 'num_rows' => 0]);
		die;
	}

	$data = $_POST["get_horarios"];
	$data['offset'] = $data['limit']*$data['page'];
	$where = "WHERE id <> 0";

	if($data['filter'] != ""){
		$where .= " AND (
				id		LIKE '%{$data['filter']}%' OR
				name 	LIKE '%{$data['filter']}%'
			)
		";
	}

	$horarios = [];
	$result = $mysqli->query("
		SELECT
			id,
			name,
			color,
			status,
			created_at,
			updated_at
		FROM tbl_horarios
		$where
		order by id desc
		LIMIT {$data['limit']} OFFSET {$data['offset']}
	");
	while($r = $result->fetch_assoc()) $horarios[] = $r;

	$num_rows = $mysqli->query("
		SELECT
			id
		FROM tbl_horarios
		$where
	")->num_rows;

	$body = "";

	if(!empty($horarios)){
		foreach ($horarios as $horario) {
			$body .= '<tr style="background-color:'.$horario["color"].' !important">';
			$body .= '<td id="cellHorarioId">'.$horario["id"].'</td>';
			$body .= '<td id="cellHorarioName">';
			$body .= '<span id="spanHorarioName" style="mix-blend-mode:difference; color:#fff">'.$horario["name"].'</span>';
			if(array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
				$body .= '<form id="formHorarioName" method="POST">';
				$body .= '<input type="text" id="txtHorarioName" name="txtHorarioName" class="form-control" value="'.$horario["name"].'" style="display:none">';
				$body .= '</form>';
			}
			$body .= '</td>';
			$body .= '<td id="cellHorarioColor">';
			$body .= '<span id="spanHorarioColor" style="mix-blend-mode:difference; color:#fff">'.$horario["color"].'</span>';
			if(array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
				$body .= '<form id="formHorarioColor" method="POST">';
				$body .= '<input type="text" id="txtHorarioColor" name="txtHorarioColor" class="form-control" value="'.$horario["color"].'" style="display:none">';
				$body .= '</form>';
			}
			$body .= '</td>';
			$body .= '<td style="mix-blend-mode:difference; color:#fff">'.$horario["created_at"].'</td>';
			$body .= '<td style="mix-blend-mode:difference; color:#fff">'.$horario["updated_at"].'</td>';
			$body .= '<td>';
			if(array_key_exists($menu_id,$usuario_permisos) && in_array("state", $usuario_permisos[$menu_id])){
				$body .= '<input class="switch" type="checkbox" data-id="'.$horario["id"].'" '.($horario["status"] ? 'checked="checked"' : "").'> ';
			}
			if($horario["status"]){
				$body .= ' <button id="btnHorariosView" data-id="'.$horario["id"].'" data-name="'.$horario["name"].'" class="btn btn-'.($horario["status"] == 1 ? "primary" : "warning").' btn-sm">';
				$body .= '<i class="fa fa-eye"></i>';
				$body .= '</button>';
			}
			if($horario["id"] != 1 && array_key_exists($menu_id,$usuario_permisos) && in_array("delete", $usuario_permisos[$menu_id])){
				$body .= ' <button id="btnHorariosDelete" data-id="'.$horario["id"].'" class="btn btn-danger btn-sm pull-right"><i class="fa fa-trash"></i></button>';
			}
			$body .= '</td>';
			$body .= '</tr>';
		}
	}

	echo json_encode(['body' => $body, 'num_rows' => $num_rows]); die;
}

if(isset($_POST["toggle_horarios"])){
	if(array_key_exists($menu_id,$usuario_permisos) && in_array("state", $usuario_permisos[$menu_id])){
		$data = $_POST["toggle_horarios"];
		if($data["status"] == "true"){
			$num_rows = $mysqli->query("SELECT id FROM tbl_horarios_dias WHERE horario_id = ".$data["id"])->num_rows;
			$mysqli->query("UPDATE tbl_horarios set status = ".(($num_rows >= 7) ? 1 : 2)." WHERE id = ".$data["id"]);
		}
		else {
			$mysqli->query("UPDATE tbl_horarios set status = 0 WHERE id =".$data["id"]);	
		}
	}
}

if(isset($_POST["new_horario"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("new", $usuario_permisos[$menu_id])){
		echo json_encode(['body' => "No tienes permisos para crear este recurso", 'num_rows' => 0]);
		die;
	}
	$data = $_POST["new_horario"];
	$num_rows = $mysqli->query("SELECT id FROM tbl_horarios WHERE name = '".$data["name"]."'")->num_rows;

	if ($num_rows > 0) {
		echo json_encode(['body' => "Ya existe un perfil con este nombre."]);
		die;
	}
	$mysqli->query("INSERT INTO tbl_horarios(name, status, created_at, updated_at) VALUES ('".$data["name"]."', 2, now(), now())");
	echo json_encode(['body' => ""]);
}

if(isset($_POST["delete_horarios"])){
	if(array_key_exists($menu_id,$usuario_permisos) && in_array("delete", $usuario_permisos[$menu_id])){
		$data = $_POST["delete_horarios"];

		$mysqli->query("DELETE FROM tbl_horarios WHERE id =".$data["id"]);
		$mysqli->query("DELETE FROM tbl_horarios_dias WHERE horario_id =".$data["id"]);
		$mysqli->query("DELETE FROM tbl_locales_horarios WHERE horario_id =".$data["id"]);
	}
}

if(isset($_POST["update_horario_name"])){
	if(array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
		$data = $_POST["update_horario_name"];
		$mysqli->query("UPDATE tbl_horarios set name ='".$data["name"]."' WHERE id =".$data["id"]);
	}
}

if(isset($_POST["update_horario_color"])){
	if(array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
		$data = $_POST["update_horario_color"];
		$mysqli->query("UPDATE tbl_horarios set color ='".$data["color"]."' WHERE id =".$data["id"]);
	}
}

if(isset($_POST["attach_horarios_dias"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("edit", $usuario_permisos[$menu_id])){
		echo json_encode(['body' => "No tienes permisos para editar este recurso", 'num_rows' => 0]);
		die;
	}
	$data = $_POST["attach_horarios_dias"];

	if(!(boolean)$data["closed"]){
		if($data["start_shift"] == "" || $data["end_shift"] == ""){
			echo json_encode(['body' => "Campos de Apertura y Cierre de Tienda son obligatorios"]);
			die;
		}
		else if(($data["start_break"] == "") ^ ($data["end_break"] == "")){ //XNOR Operand (ambos falsos o verdaderos)
			echo json_encode(['body' => "Ambos Campos de Break(Inicio y Fin) deben estar llenados o vacios."]);
			die;
		}
	}

	$weekdays = [];
	if((boolean)$data["massive"]){ 
		$weekdays = [0,1,2,3,4,5,6];
	}
	else{
		$weekdays[] = $data["weekday_id"];	
	}

	foreach ($weekdays as $weekday) {
		$duplicate_id = false;
		$result = $mysqli->query("
			SELECT
				id
			FROM tbl_horarios_dias
			WHERE
				horario_id = ".$data["horario_id"]."
				AND weekday_id = ".$weekday."
		");
		while($r = $result->fetch_assoc()) $duplicate_id = $r["id"];

		if((boolean)$data["closed"]){
			$start_shift = 'null';
			$end_shift = 'null';
			$start_break = 'null';
			$end_break = 'null';
		}
		else {
			$start_shift = "'".$data["start_shift"]."'";
			$end_shift = "'".$data["end_shift"]."'";
			$start_break = ($data["start_break"] != "") ? "'".$data["start_break"]."'" : "null";
			$end_break = ($data["end_break"] != "") ? "'".$data["end_break"]."'" : "null";
		}

		if($duplicate_id){
			$mysqli->query("
				UPDATE 
					tbl_horarios_dias 
				SET 
					start_shift = {$start_shift},
					end_shift = {$end_shift},
					start_break = {$start_break},
					end_break = {$end_break}
				WHERE
					horario_id = {$data["horario_id"]}
					AND weekday_id = ".$weekday."
			");
		}
		else{
			$mysqli->query("
				INSERT INTO tbl_horarios_dias(
					horario_id,
					weekday_id,
					start_shift,
					end_shift,
					start_break,
					end_break
				) VALUES (
					".$data["horario_id"].",
					".$weekday.",
					{$start_shift},
					{$end_shift},
					{$start_break},
					{$end_break}
				)
			");
		}
	}


	$num_rows = $mysqli->query("SELECT id FROM tbl_horarios_dias WHERE horario_id = ".$data["horario_id"])->num_rows;
	if($num_rows >= 7){
		$mysqli->query("UPDATE tbl_horarios set status = 1 WHERE id = ".$data["horario_id"]);
	}

	echo json_encode(['body' => "Horario diario del perfil actualizado."]); die;
}

if(isset($_POST["get_horario_dias_modal"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		echo json_encode(['body' => "No tienes permisos para ver este recurso", 'num_rows' => 0]);
		die;
	}
	$data = $_POST["get_horario_dias_modal"];

	$selected_weekday = -1;
	if(isset($data["date"])){
		$selected_weekday = date('w', strtotime($data["date"]));
	}

	$horas_dia = [];
	$result = $mysqli->query("
		SELECT
			hd.id,
			hd.weekday_id,
			hd.start_shift,
			hd.start_break,
			hd.end_break,
			hd.end_shift,
			h.color
		FROM tbl_horarios_dias hd
		INNER JOIN tbl_horarios h ON h.id = hd.horario_id
		WHERE hd.horario_id = ".$data["id"]."
	");
	while($r = $result->fetch_assoc()) $horas_dia[$r["weekday_id"]] = $r;

	$weekdays = [];
	$result = $mysqli->query("SELECT * FROM tbl_weekdays");
	while($r = $result->fetch_assoc()) $weekdays[] = $r;

	$body="";

	$body .= '<tbody>';
	$body .= '<tr>';
	foreach($weekdays as $weekday){ 
		$body .= '<td class="text-center calendar-cell" style="background-color:'.(($weekday["id"] == $selected_weekday) ? '#fffbb2' : '#fff').' !important;">';
		$body .= '<h5 class="text-bold">'.mb_strtoupper($weekday["name_esp"]).'</h5>';
		if(!isset($data["show"]) && array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
			$body .= '<div id="wrapForm_'.$weekday["id"].'" style="display:none">';
			$body .= '<form method="POST" id="formHorariosDias">';
			$body .= '<input type="hidden" id ="horarios_id" value="'.$data["id"].'">';
			$body .= '<input type="hidden" id ="weekday_id" value="'.$weekday["id"].'">';
			$body .= '<div class="form-group text-left">';
			$body .= '<label for="txtHorariosDiasStartShift">Apertura Tienda</label>';
			$body .= '<input ';
			$body .= 'type="text"';
			$body .= 'required';
			$body .= 'data-pre=""';
			$body .= 'id="txtHorariosDiasStartShift_'.$weekday["id"].'"';
			$body .= 'class="form-control timepicker" ';
			$body .= 'value="'.(isset($horas_dia[$weekday["id"]]) ? $horas_dia[$weekday["id"]]["start_shift"] : "").'" ';
			$body .= (isset($horas_dia[$weekday["id"]]) && !$horas_dia[$weekday["id"]]["start_shift"]) ? 'disabled' : '';
			$body .= '>';
			$body .= '</div>';
			$body .= '<div class="form-group text-left">';
			$body .= '<label for="txtHorariosDiasStartShift">Inicio Break</label>';
			$body .= '<input ';
			$body .= 'type="text"';
			$body .= 'data-pre=""';
			$body .= 'id="txtHorariosDiasStartBreak_'.$weekday["id"].'"';
			$body .= 'class="form-control timepicker" ';
			$body .= 'value="'.(isset($horas_dia[$weekday["id"]]) ? $horas_dia[$weekday["id"]]["start_break"] : "").'" ';
			$body .= (isset($horas_dia[$weekday["id"]]) && !$horas_dia[$weekday["id"]]["start_shift"]) ? 'disabled' : '';
			$body .= '>';
			$body .= '</div>';
			$body .= '<div class="form-group text-left">';
			$body .= '<label for="txtHorariosDiasStartShift">Fin Break</label>';
			$body .= '<input ';
			$body .= 'type="text"';
			$body .= 'data-pre=""';
			$body .= 'id="txtHorariosDiasEndBreak_'.$weekday["id"].'"';
			$body .= 'class="form-control timepicker" ';
			$body .= 'value="'.(isset($horas_dia[$weekday["id"]]) ? $horas_dia[$weekday["id"]]["end_break"] : "").'" ';
			$body .= (isset($horas_dia[$weekday["id"]]) && !$horas_dia[$weekday["id"]]["start_shift"]) ? 'disabled' : '';
			$body .= '>';
			$body .= '</div>';
			$body .= '<div class="form-group text-left">';
			$body .= '<label for="txtHorariosDiasStartShift">Cierre Tienda</label>';
			$body .= '<input ';
			$body .= 'type="text"';
			$body .= 'required';
			$body .= 'data-pre=""';
			$body .= 'id="txtHorariosDiasEndShift_'.$weekday["id"].'"';
			$body .= 'class="form-control timepicker" ';
			$body .= 'value="'.(isset($horas_dia[$weekday["id"]]) ? $horas_dia[$weekday["id"]]["end_shift"] : "").'" ';
			$body .= (isset($horas_dia[$weekday["id"]]) && !$horas_dia[$weekday["id"]]["start_shift"]) ? 'disabled' : '';
			$body .= '>';
			$body .= '</div>';
			$body .= '<div class="form-check-inline my-2">';
			$body .= '<input type="checkbox" ';
			$body .= 'id="chkClosedDay_'.$weekday["id"].'" ';
			$body .= 'name="chkClosedDay" ';
			$body .= 'data-id="'.$weekday["id"].'" ';
			$body .= 'class="form-check-input" ';
			$body .= (isset($horas_dia[$weekday["id"]]) && !$horas_dia[$weekday["id"]]["start_shift"]) ? 'checked' : '';
			$body .= '>';
			$body .= '<label class="form-check-label ml-2" for="chkClosedDay">Tienda Cerrada</label>';
			$body .= '</div>';
			$body .= '<table class="w-100">';
			$body .= '<tr>';
			$body .= '<td style="width: 35%">';
			$body .= '<button id="btnHorarioDiasCancel" data-id="'.$weekday["id"].'" class="btn btn-secondary btn-sm btn-block">';
			$body .= '<i class="fa fa-ban"></i>';
			$body .= '</button>';
			$body .= '</td>';
			$body .= '<td style="width: 65%">';
			$body .= '<button ';
			$body .= 'type="submit" ';
			$body .= 'id="btnHorarioDiasSend"';
			$body .= 'data-horario-id="'.$data["id"].'"';
			$body .= 'data-weekday-id="'.$weekday["id"].'" ';
			$body .= 'data-massive="0" ';
			$body .= 'class="btn btn-success btn-sm btn-block"';
			$body .= '>';
			$body .= '<i class="fa fa-check"></i> OK';
			$body .= '</button>';
			$body .= '</td>';
			$body .= '</tr>';
			$body .= '</table>';
			$body .= '<button ';
			$body .= 'type="submit" ';
			$body .= 'id="btnHorarioDiasSend"';
			$body .= 'data-horario-id="'.$data["id"].'"';
			$body .= 'data-weekday-id="'.$weekday["id"].'" ';
			$body .= 'data-massive="1" ';
			$body .= 'class="mt-1 btn btn-warning btn-sm btn-block"';
			$body .= '>';
			$body .= '<i class="fa fa-exchange"></i> Aplicar a Todos';
			$body .= '</button>';
			$body .= '</form>';
			$body .= '</div>';
		}
		$body .= '<div id="wrapInfo_'.$weekday["id"].'">';
		if(isset($horas_dia[$weekday["id"]])){
			$body .= '<div class="alert calendar-alert" style="background-color:'.$horas_dia[$weekday["id"]]["color"].' !important">';
			if(!$horas_dia[$weekday["id"]]["start_shift"]){
				$body .= '<p class="text-bold">Tienda Cerrada</p>';
			}
			elseif($horas_dia[$weekday["id"]]["start_break"]){
				$body .= '<p class="text-bold">Apertura-Break</p>';
				$body .= $horas_dia[$weekday["id"]]["start_shift"].' - '.$horas_dia[$weekday["id"]]["start_break"].'<br>';
				$body .= '<p class="text-bold">Break-Cierre</p>';
				$body .= ''.$horas_dia[$weekday["id"]]["end_break"].' - '.$horas_dia[$weekday["id"]]["end_shift"];
			}
			else{
				$body .= '<p class="text-bold">Apertura-Cierre</p>';
				$body .= $horas_dia[$weekday["id"]]["start_shift"].' - '.$horas_dia[$weekday["id"]]["end_shift"].'<br>';
			}
			$body .= '</div>';
		}
		else{
			$body .= '<p class="alert alert-danger calendar-alert">';
			$body .= 'No hay horarios definidos.';
			$body .= '</p>';
		}
		if(!isset($data["show"]) && array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
			$body .= '<button ';
			$body .= 'id="btnHorarioDiasEdit" ';
			$body .= 'data-id="'.$weekday["id"].'" ';
			$body .= 'class="btn btn-warning btn-sm btn-block"';
			$body .= '>';
			$body .= '<i class="fa fa-edit"></i> Editar';
			$body .= '</button>';
			$body .= '</div>';
			$body .= '</td>';
		}
	}
	$body .= '</tr>';
	$body .= '</tbody>';

	echo json_encode(['body' => $body]);
}

if(isset($_POST["get_local_horarios"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		echo json_encode(['body' => "No tienes permisos para ver este recurso", 'num_rows' => 0]);
		die;
	}

	$data = $_POST["get_local_horarios"];

	$weekdays = [];
	$result = $mysqli->query("SELECT * FROM tbl_weekdays");
	while($r = $result->fetch_assoc()) $weekdays[$r["id"]] = $r["name_esp"];

	$local_horarios = [];
	$result = $mysqli->query("
		SELECT 
			horario_id,
			started_at
		FROM tbl_locales_horarios
		WHERE local_id = ".$data["local_id"]."
		ORDER BY started_at ASC
	");
	while($r = $result->fetch_assoc()) $local_horarios[$r["started_at"]] = $r["horario_id"];

	$horarios = [];
	$result = $mysqli->query("
		SELECT
			h.id,
			h.name,
			h.color,
			hd.weekday_id,
			hd.start_shift,
			hd.start_break,
			hd.end_break,
			hd.end_shift
		FROM tbl_horarios h
		INNER JOIN tbl_horarios_dias hd ON hd.horario_id = h.id
		WHERE h.status = 1
	");
	while($r = $result->fetch_assoc()) 	$horarios[$r["id"]][$r["weekday_id"]] = $r;

	$body = "";
	$body .= '<tbody>';

	$iterator_date = $data["startdate"];
	while (strtotime($iterator_date) <= strtotime($data["enddate"])){

		$horario_id = 0;
		if(!empty($local_horarios)){
			foreach ($local_horarios as $datetime => $horario) {
				if($iterator_date >= $datetime){
					$horario_id = $horario;
				}
			}
		}

		$weekday_id = date("w", strtotime($iterator_date));
		if($iterator_date == $data["startdate"]){
			$body .= '<tr>'; 
			for ($i=0; $i < $weekday_id; $i++){
				$body .= '<td class="calendar-border" width="14.28%"></td>';
			}
		}
		elseif($weekday_id == 0){
			$body .= '<tr>';
		}

		$bgcolor= "#fff";
		if($iterator_date == date('Y-m-d')) $bgcolor = "#fffbb2";
		if(in_array($weekday_id, [0,6])) $bgcolor = "#e1e1e1";
		$body .= '<td class="text-center calendar-border p-3" style="background-color:'.$bgcolor.'" width="14.28%">';
		$body .= '<div id="cellCalendar" data-date="'.$iterator_date.'" data-horario-id="'.$horario_id.'">';
		$body .= '<h5 class="text-bold">'.mb_strtoupper($weekdays[$weekday_id]).'</h5>';
		$body .= '<p class="mt--4">'.date("Y-m-d", strtotime($iterator_date)).'</p>';
		if($horario_id){
			$body .= '<div class="alert small calendar-alert" style="background-color:'.$horarios[$horario_id][$weekday_id]["color"].' !important">';
			$body .= '<h5>'.$horarios[$horario_id][$weekday_id]["name"].'</h5>';
			if(!$horarios[$horario_id][$weekday_id]["start_shift"]){
				$body .= '<p class="text-bold">Tienda Cerrada</p>';
			}
			elseif($horarios[$horario_id][$weekday_id]["start_break"] != ""){
				$body .= '<p class="text-bold">Apertura-Break</p>';
				$body .= $horarios[$horario_id][$weekday_id]["start_shift"].' - '.$horarios[$horario_id][$weekday_id]["start_break"].'<br>';
				$body .= '<p class="text-bold">Break-Cierre</p>';
				$body .= $horarios[$horario_id][$weekday_id]["end_break"].' - '.$horarios[$horario_id][$weekday_id]["end_shift"].'';
			}
			else{
				$body .= '<p class="text-bold">Apertura-Cierre</p>';
				$body .= $horarios[$horario_id][$weekday_id]["start_shift"].' - '.$horarios[$horario_id][$weekday_id]["end_shift"].'<br>';
			}
		}
		else{
			$body .= '<div class="alert alert-danger small calendar-alert">';
			$body .= '<h5>Sin Perfil</h5>';
			$body .= '<p class="text-bold">Esta tienda no contiene perfiles asignados.</p>';
		}
		$body .= '</div>';
		$body .= '</div>';
		$body .= '</td>';
		if($iterator_date == $data["enddate"]){
			for ($i=$weekday_id; $i < 6; $i++){
				$body .= '<td width="14.28%"></td>';
			}
			$body .= '</tr>';
		}
		elseif(date ("w", strtotime($iterator_date)) == 6){
			$body .= '</tr>';
		}
		$iterator_date = date ("Y-m-d", strtotime("+1 day", strtotime($iterator_date)));
	}
	$body .= '</tbody>';

	echo json_encode(['body' => $body]);
}

if(isset($_POST["save_local_horario"])){
	if(array_key_exists($menu_id,$usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])){
		$data = $_POST["save_local_horario"];

		if($data["horario_id"] == 0) die;

		if((boolean)$data["daily"]){
			$after_profile = false;
			$result = $mysqli->query("
				SELECT
					horario_id
				FROM tbl_locales_horarios
				WHERE
					local_id = ".$data["local_id"]."
					AND started_at = '".date('Y-m-d', strtotime("+1 day", strtotime($data["started_at"])))."'
				ORDER BY started_at ASC
				LIMIT 1
			");
			while($r = $result->fetch_assoc()) $after_profile = $r["horario_id"];

			if(!$after_profile){
				$before_profile = false;
				$result = $mysqli->query("
					SELECT
						horario_id
					FROM tbl_locales_horarios
					WHERE
						local_id = ".$data["local_id"]."
						AND started_at < '".$data["started_at"]."'
					ORDER BY started_at DESC
					LIMIT 1
				");
				while($r = $result->fetch_assoc()) $before_profile = $r["horario_id"];

				$mysqli->query("
					INSERT INTO tbl_locales_horarios (
						local_id, 
						horario_id,
						started_at
					)
					VALUES (
						".$data["local_id"].",
						".($before_profile ?: 1).",
						'".date('Y-m-d', strtotime("+1 day", strtotime($data["started_at"])))."'
					)
				");
			}
		}

		$num_rows = $mysqli->query("
			SELECT 
				id
			FROM tbl_locales_horarios 
			WHERE 
				local_id = ".$data["local_id"]." 
				AND started_at ='".$data["started_at"]."'
		")->num_rows;


		if($num_rows){
			$mysqli->query("
				UPDATE tbl_locales_horarios 
				SET 
					horario_id = ".$data["horario_id"]."
				WHERE 
					local_id = ".$data["local_id"]." 
					AND started_at ='".$data["started_at"]."'
			");
		}
		else{
			$mysqli->query("
				INSERT INTO tbl_locales_horarios (
					local_id, 
					horario_id,
					started_at
				)
				VALUES (
					".$data["local_id"].",
					".$data["horario_id"].",
					'".$data["started_at"]."'
				)
			");
		}
	}
}

if(isset($_POST["get_batch_horarios"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("edit", $usuario_permisos[$menu_id])){
		echo json_encode(['body' => "No tienes permisos para ver este recurso"]);
		die;
	}
	$data = $_POST["get_batch_horarios"];
	$iterator_date = $data["start_date"];

	$where = "WHERE red_id=1 AND operativo=1 AND estado=1";
	if($login["usuario_locales"]) $where .= " AND id IN (".implode(",", $login["usuario_locales"]).")";

	if($login["zona_id"]) $where .= ' AND zona_id='.$login["zona_id"];
	else if((boolean)$data["zona"]) $where .= ' AND zona_id='.$data["zona"];

	$locales = [];
	$result = $mysqli->query("
		SELECT 
			id,
			cc_id,
			nombre
		FROM tbl_locales
		$where
	");
	while($r = $result->fetch_assoc()) $locales[$r["id"]] = $r;

	$local_horarios = [];
	$result = $mysqli->query("
		SELECT 
			local_id,
			horario_id,
			started_at
		FROM tbl_locales_horarios
		WHERE local_id IN (".implode(",", array_keys($locales)).")
		ORDER BY started_at ASC
	");
	while($r = $result->fetch_assoc()) $local_horarios[$r["local_id"]][$r["started_at"]] = $r["horario_id"];

	$horarios = [];
	$result = $mysqli->query("
		SELECT
			id,
			name,
			color
		FROM tbl_horarios
		WHERE 
			status = 1
	");
	while($r = $result->fetch_assoc()) 	$horarios[$r["id"]] = $r;

	$body = "";

	$body .= '<thead>';
	$body .= '<tr class="bg-primary text-light">';
	$body .= '<th style="width: 20px;">';
	$body .= '<input type="checkbox" id="chkBatchHorarioAll" class="form-check-input">';
	$body .= '</th>';
	$body .= '<th style="width: 50px;">ID</th>';
	$body .= '<th style="width: 50px;">CC ID</th>';
	$body .= '<th style="width: 200px">Local</th>';
	
	while (strtotime($iterator_date) <= strtotime($data["end_date"])){
		$body .= '<th style="width: 150px;">'.$iterator_date.'</th>';
		$iterator_date = date ("Y-m-d", strtotime("+1 day", strtotime($iterator_date)));
	}

	$body .= '</tr>';
	$body .= '</thead>';
	$body .= '<tbody>';
	foreach($locales as $local){
		$body .= '<tr>';
		$body .= '<td>';
		$body .= '<input type="checkbox" id="chkBatchHorario" data-id="'.$local["id"].'" class="form-check-input">';
		$body .= '</td>';
		$body .= '<td>'.$local["id"].'</td>';
		$body .= '<td>'.$local["cc_id"].'</td>';
		$body .= '<td>'.$local["nombre"].'</td>';
		
		$iterator_date = $data["start_date"];
		while (strtotime($iterator_date) <= strtotime($data["end_date"])){
			$horario_id = 1;
			if(!empty($local_horarios[$local["id"]])){
				foreach ($local_horarios[$local["id"]] as $datetime => $horario) {
					if($iterator_date >= $datetime){
						$horario_id = $horario;
					}
				}
			}
			$body .= '<td style="background-color:'.$horarios[$horario_id]["color"].'">'.$horarios[$horario_id]["name"].'</td>';
			$iterator_date = date ("Y-m-d", strtotime("+1 day", strtotime($iterator_date)));
		}

		$body .= '</tr>';
		$body .= '</tbody>';
	}
	
	echo json_encode(['body' => $body]);
}

if(isset($_POST["get_horarios_option"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		echo json_encode(['body' => "No tienes permisos para ver este recurso"]);
		die;
	}

	$data = $_POST["get_horarios_option"];

	$horarios = [];
	$result = $mysqli->query("
		SELECT
			id,
			name,
			color
		FROM tbl_horarios
		WHERE 
			status = ".$data["status"]."
	");
	while($r = $result->fetch_assoc()) $horarios[] = $r;

	echo json_encode(['options' => $horarios]);
}

if(isset($_POST["set_batch_horarios"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("edit", $usuario_permisos[$menu_id])){
		echo json_encode(['body' => "No tienes permisos para editar este recurso"]);
		die;
	}

	$data = $_POST["set_batch_horarios"];

	if(empty($data["locales"])){
		echo json_encode(["body" => "Por favor definir al menos un local"]);
	}

	$local_horarios = [];
	$result = $mysqli->query("
		SELECT 
			local_id,
			horario_id,
			started_at
		FROM tbl_locales_horarios
		WHERE local_id IN (".implode(',', $data["locales"]).")
		ORDER BY started_at ASC
	");

	while($r = $result->fetch_assoc()) $local_horarios[$r["local_id"]][$r["started_at"]] = $r["horario_id"];

	foreach($data["locales"] as $local){
		$current_horario = false;
		$result = $mysqli->query("
			SELECT 
				id 
			FROM tbl_locales_horarios 
			WHERE 
				local_id = ".$local."
				AND started_at = '".$data["start_date"]."'
		");
		$after_horario = false;
		$result = $mysqli->query("
			SELECT 
				id 
			FROM tbl_locales_horarios 
			WHERE 
				local_id = ".$local."
				AND started_at = '".date('Y-m-d', strtotime("+1 Day", strtotime($data["start_date"])))."'
		");
		while($r = $result->fetch_assoc()) $after_horario = $r["id"];

		$mysqli->query("
			INSERT INTO tbl_locales_horarios(
				local_id,
				horario_id,
				started_at
			)
			VALUES(
				".$local.",
				".$data["horario_id"].",
				'".$data["start_date"]."'
			)
		");

		if($data["daily"] == "true"){
			if(!$after_horario){
				if(!$current_horario){
					$before_horario = 1;

					foreach($local_horarios[$local] as $datetime => $horario){
						if(strtotime($data["start_date"]) < strtotime($datetime)){
							break;
						}
						$before_horario = $horario;
					}
				}
				else{
					$before_horario = $current_horario;
				}

				$mysqli->query("
					INSERT INTO tbl_locales_horarios(
						local_id,
						horario_id,
						started_at
					)
					VALUES(
						".$local.",
						".$before_horario.",
						'".date('Y-m-d', strtotime("+1 Day", strtotime($data["start_date"])))."'
					)
				");
			}
		}
		else{
			$mysqli->query("
				DELETE FROM tbl_locales_horarios 
				WHERE 
					local_id = ".$local."
					AND started_at > '".$data["start_date"]."'
			");
		}
	}

	echo json_encode(["body" => "Horarios asignados correctamente."]);

}

?>
