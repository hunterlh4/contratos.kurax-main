<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/phpexcel/classes/PHPExcel.php';
require_once '/var/www/html/sys/helpers.php';
require_once '/var/www/html/class/clsServicioTecnico.php';

function datatables_servidor( $query_excel = false ){
	global $mysqli, $login;

	//$ID_LOGIN = $login["id"];
	$data = $_POST["action"];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
    $columns = $_POST['columns'];
	$columnName = 'f.id'; // Column name
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value
	$searchValue = $mysqli->real_escape_string($searchValue);

	$fecha_inicio = isset($_POST["fecha_inicio"])?$_POST["fecha_inicio"]:"";
	$fecha_fin = isset($_POST["fecha_fin"])?$_POST["fecha_fin"]:"";

	$fecha_fin = date("Y-m-d", strtotime($fecha_fin." +1 days"));
	$query_fecha = "";

	// $col_fecha = "IF(inci.updated_at  IS NULL, inci.created_at, inci.updated_at )";
	// if($fecha_inicio != "" && $fecha_fin != ""){
	// 	$query_fecha = "AND $col_fecha > '$fecha_inicio' AND $col_fecha < '$fecha_fin'";
	// }
	// if($fecha_inicio == "" && $fecha_fin != ""){
	// 	$query_fecha = " AND $col_fecha < '$fecha_fin'";
	// }
	// if($fecha_fin == "" && $fecha_inicio != ""){
	// 	$query_fecha = " AND $col_fecha > '$fecha_inicio'";
	// }

    ## Search
	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " and (
            f.id LIKE '%".$searchValue."%' or
            f.dni LIKE '%".$searchValue."%' or
            r.nombre LIKE '%".$searchValue."%' or
	        IF(f.estado = 1,'Activo','Inactivo') LIKE '%".$searchValue."%'
			) ";
	}

    // col 5 locales redes
	if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND r.id in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}

	// col 5 estado
	if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" AND f.estado in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."')";
	}

    $query_all = "SELECT
        f.id,
        f.dni,
        r.id as red_id,
        r.nombre as red,
        if(f.estado = 1,'Activo','Inactivo') as estado
    from tbl_2_factores f
    left join tbl_locales_redes r on r.id = f.red_id
    where 1 ";

	$query_total_filtered = "SELECT COUNT(*) AS total FROM (" . $query_all . $searchQuery . $query_fecha . ") AS subquery";
	$result_total_filtered = $mysqli->query($query_total_filtered);
	$row_total_filtered = $result_total_filtered->fetch_assoc();
	$totalRecordwithFilter = $row_total_filtered['total'];

	$totalRecords = $row_total_filtered['total'];

	$limit = " LIMIT ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}

	$query_f = $query_all . $searchQuery . $query_fecha . " ORDER BY ".$columnName." ".$columnSortOrder.$limit;
	$registros = $mysqli->query($query_f);
	$data = array();

	while ($row = $registros->fetch_assoc()) {
	    $data[] = $row;
	}

	$response = array(
	  "draw" => $draw,
	  "searchValue" => $searchValue,
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);

	return $response;

}

if(isset($_POST["action"]) && $_POST["action"]=="sec_dni_2_factores_list"){
	$response = datatables_servidor();
	echo json_encode($response);
	return;
}

// SAVE MODAL
if(isset($_POST["set_dni_2_factores_save"])){
    $user_id = $login["id"];
	extract($_POST);
    if(strlen($dni) !== 8){
        $return["error"] = "dni";
		$return["error_msg"] = "El dni debe tener 8 dígitos.";
		$return["error_focus"] = "dni";
		die(json_encode($return));
    }
    if(!isset($locales_redes) || $locales_redes == "" || $locales_redes == "0"){
		$return["error"] = "locales_redes";
		$return["error_msg"] = "Debe seleccionar la red.";
		$return["error_focus"] = "locales_redes";
		die(json_encode($return));
	}
	if(!isset($estado) || $estado == "" || $estado == "0"){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe seleccionar el estado.";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}

    $estado = (int)$estado;
    $locales_redes = (int)$locales_redes;

    $insert_command_2_factores = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_2_factores`
		(
			dni
			,estado
			,red_id
		)
			VALUES
		(
			'".$dni."'
			,$estado
            ,$locales_redes
		)";
    $return["insert_command_2_factores"] = $insert_command_2_factores;
    $mysqli->query($insert_command_2_factores);
    if($mysqli->error){
        print_r($mysqli->error);
        echo "\n";
        echo $insert_command_2_factores;
        exit();
    }
	$return["mensaje"] = "Registro Guardado Correctamente";
	$return["curr_login"] = $login;
}

// UPDATE MODAL
if(isset($_POST["set_dni_2_factores_update"])){
    $user_id = $login["id"];
	extract($_POST);
    if(strlen($dni) !== 8){
        $return["error"] = "dni";
		$return["error_msg"] = "El dni debe tener 8 dígitos.";
		$return["error_focus"] = "dni";
		die(json_encode($return));
    }
    if(!isset($locales_redes) || $locales_redes == "" || $locales_redes == "0"){
		$return["error"] = "locales_redes";
		$return["error_msg"] = "Debe seleccionar la red.";
		$return["error_focus"] = "locales_redes";
		die(json_encode($return));
	}
	if(!isset($estado) || $estado == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe seleccionar el estado.";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}

    $id = (int)$id;
    $estado = (int)$estado;
    $locales_redes = (int)$locales_redes;

    $update_command = "UPDATE  `wwwapuestatotal_gestion`.`tbl_2_factores` SET
			dni = '".$dni."'
			,estado = $estado
			,red_id = $locales_redes
		WHERE id = $id";

    $return["update_command"] = $update_command;
    $mysqli->query($update_command);
    if($mysqli->error){
        print_r($mysqli->error);
        echo "\n";
        echo $update_command;
        exit();
    }

	$return["mensaje"] = "Solicitud ".$id." Actualizada";
	$return["curr_login"] = $login;
}

if(isset($_POST["sec_dni_2_factores_cargar_solicitud"])){
	$solicitud_id = $_POST["solicitud_id"];
	
		$command ="SELECT
            f.id,
            f.dni,
            r.id as red_id,
            r.nombre as red,
            f.estado
        from tbl_2_factores f
        left join tbl_locales_redes r on r.id = f.red_id
        where f.id = {$solicitud_id}";
		$list_query = $mysqli->query($command);
		$list = $list_query->fetch_assoc();

	
	$return["local"] = $list;
}

// SAVE 104
if(isset($_POST["set_dni_2_factores_save_104"])){
    $user_id = $login["id"];
	extract($_POST);
    if(!isset($valor_104) || $valor_104 == "" || $valor_104 == "0"){
		$return["error"] = "valor";
		$return["error_msg"] = "Debe seleccionar el valor.";
		$return["error_focus"] = "valor";
		die(json_encode($return));
	}
	if(!isset($estado_104) || $estado_104 == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe seleccionar el estado.";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}

    $estado_104 = (int)$estado_104;

    $update_command = "UPDATE  `wwwapuestatotal_gestion`.`tbl_parametros_generales` SET
			valor = '".$valor_104."'
			,estado = $estado_104
		WHERE codigo = '".$time_cod_verif."'";

    $return["update_command"] = $update_command;
    $mysqli->query($update_command);
    if($mysqli->error){
        print_r($mysqli->error);
        echo "\n";
        echo $update_command;
        exit();
    }

	$return["mensaje"] = "Actualizado Correctamente";
	$return["curr_login"] = $login;
}

// SAVE 105
if(isset($_POST["set_dni_2_factores_save_105"])){
    $user_id = $login["id"];
	extract($_POST);
    if(!isset($valor_105) || $valor_105 == "" || $valor_105 == "0"){
		$return["error"] = "valor";
		$return["error_msg"] = "Debe seleccionar el valor.";
		$return["error_focus"] = "valor";
		die(json_encode($return));
	}
	if(!isset($estado_105) || $estado_105 == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe seleccionar el estado.";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}

    $estado_105 = (int)$estado_105;

    $update_command = "UPDATE  `wwwapuestatotal_gestion`.`tbl_parametros_generales` SET
			valor = '".$valor_105."'
			,estado = $estado_105
        WHERE codigo = '".$max_intentos_sms."'";

    $return["update_command"] = $update_command;
    $mysqli->query($update_command);
    if($mysqli->error){
        print_r($mysqli->error);
        echo "\n";
        echo $update_command;
        exit();
    }

	$return["mensaje"] = "Actualizado Correctamente";
	$return["curr_login"] = $login;
}

// SAVE 106
if(isset($_POST["set_dni_2_factores_save_106"])){
    $user_id = $login["id"];
	extract($_POST);
    if(!isset($valor_106) || $valor_106 == "" || $valor_106 == "0"){
		$return["error"] = "valor";
		$return["error_msg"] = "Debe seleccionar el valor.";
		$return["error_focus"] = "valor";
		die(json_encode($return));
	}
	if(!isset($estado_106) || $estado_106 == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe seleccionar el estado.";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}

    $estado_106 = (int)$estado_106;

    $update_command = "UPDATE  `wwwapuestatotal_gestion`.`tbl_parametros_generales` SET
			valor = '".$valor_106."'
			,estado = $estado_106
        WHERE codigo = '".$tiempo_intentos_sms."'";

    $return["update_command"] = $update_command;
    $mysqli->query($update_command);
    if($mysqli->error){
        print_r($mysqli->error);
        echo "\n";
        echo $update_command;
        exit();
    }

	$return["mensaje"] = "Actualizado Correctamente";
	$return["curr_login"] = $login;
}

// SAVE 107
if(isset($_POST["set_dni_2_factores_save_107"])){
    $user_id = $login["id"];
	extract($_POST);
    if(!isset($valor_107) || $valor_107 == "" || $valor_107 == "0"){
		$return["error"] = "valor";
		$return["error_msg"] = "Debe seleccionar el valor.";
		$return["error_focus"] = "valor";
		die(json_encode($return));
	}
	if(!isset($estado_107) || $estado_107 == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe seleccionar el estado.";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}

    $estado_107 = (int)$estado_107;

    $update_command = "UPDATE  `wwwapuestatotal_gestion`.`tbl_parametros_generales` SET
			valor = '".$valor_107."'
			,estado = $estado_107
        WHERE codigo = '".$a2doFactor_autenticacion."'";

    $return["update_command"] = $update_command;
    $mysqli->query($update_command);
    if($mysqli->error){
        print_r($mysqli->error);
        echo "\n";
        echo $update_command;
        exit();
    }

	$return["mensaje"] = "Actualizado Correctamente";
	$return["curr_login"] = $login;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>