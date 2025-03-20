<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include("/var/www/html/phpexcel/classes/PHPExcel.php");
require_once '/var/www/html/sys/helpers.php';

function datatables_servidor( $query_excel = false ){
	global $mysqli, $login;
	//$ID_LOGIN = $login["id"];
	$data = $_POST["action"];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
    $columns = $_POST['columns'];
	$columnName = 'ses.id'; // Column name
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value
	$searchValue = $mysqli->real_escape_string($searchValue);

	$where_equipos = "";

    ## Search
	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " and (
			ses.id LIKE '%".$searchValue."%' or
			sez.zona LIKE '%".$searchValue."%' or
			sez.provincia LIKE '%".$searchValue."%' or
			ses.equipo LIKE '%".$searchValue."%' or
			sez.razon_social LIKE '%".$searchValue."%'
			) ";
	}
	//col 2  zona
	/*if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND z.nombre in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}*/
	if($_POST["columns"][2]["search"]["value"] != "" && $_POST["columns"][2]["search"]["value"] != "null"){
		$searchQuery.=	" AND sez.zona_id in ('".str_replace(",", "','", $_POST["columns"][2]["search"]["value"])."')";
	}
	//col departamento
	if($_POST["columns"][3]["search"]["value"] != "" && $_POST["columns"][3]["search"]["value"] != "null"){
		$searchQuery.=	" AND sez.provincia in ('".str_replace(",", "','", $_POST["columns"][3]["search"]["value"])."')";
	}
	//col equipo
	if($_POST["columns"][4]["search"]["value"] != "" && $_POST["columns"][4]["search"]["value"] != "null"){
		$searchQuery.=	" AND ses.equipo_id in ('".str_replace(",", "','", $_POST["columns"][4]["search"]["value"])."')";
	}
	//col estado
	if($_POST["columns"][10]["search"]["value"] != "" && $_POST["columns"][10]["search"]["value"] != "null"){
		$searchQuery.=	" AND sez.estado in ('".str_replace(",", "','", $_POST["columns"][10]["search"]["value"])."')";
	}

    $query_all = "SELECT 
					ses.id,
					ses.equipo_id,
					ses.equipo,
					ses.t_revision,
					ses.t_coordinacion,
					sez.valor_v,
					sez.valor_e,
                    sez.zona_id,
					sez.zona,
					sez.provincia,
					sez.tipo,
					sez.razon_social,
					IF(sez.estado = 1, 'Activo', 'Inactivo') as estado
				FROM tbl_solicitud_estimacion_servicio_tecnico ses
				LEFT JOIN tbl_solicitud_estimacion_zona sez ON sez.id = ses.solicitud_estimacion_zona_id";

	

	// if($query_excel){
	// 	$query_excel = $query_all  . " ORDER BY ".$columnName." ".$columnSortOrder;
	// 	$response = array(
	// 		  "query_excel" => $query_excel
	// 	);
	// 	return $response;
	// }

	$sel = $mysqli->query("SELECT count(*) AS allcount FROM wwwapuestatotal_gestion.tbl_solicitud_estimacion_servicio_tecnico");
	$records = $sel->fetch_assoc();
	$totalRecords = $records['allcount'];

	$query_f =   $query_all."	WHERE 1 ". $searchQuery;
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$query_f.") AS subquery");
	$records = $sel->fetch_assoc();
	$totalRecordwithFilter = $records['allcount'];

	$limit = " LIMIT ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}

	$query_f = $query_all . " WHERE 1 " . $searchQuery . " ORDER BY ".$columnName." ".$columnSortOrder.$limit;
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

if(isset($_POST["action"]) && $_POST["action"]=="sec_solicitud_estimacion_list"){
	$response = datatables_servidor();
	echo json_encode($response);
	return;
}

if(isset($_POST["action"]) && $_POST["action"]=="sec_solicitud_estimacion_mantenimiento_list"){
	$usuario_id=$login['id'];
	$cargo_id=$login['cargo_id'];

	$busqueda_sistema = $_POST["sistema_select"];
	$busqueda_zona = $_POST["zona_select"];
	$busqueda_provincia = $_POST["departamento_select"];
	$busqueda_estado = $_POST["estado_select"];

	$where_sistema = "";
	$where_zona = "";
	$where_provincia = "";
	$where_estado = ""; 

	if (!Empty($busqueda_sistema)) {
		$where_sistema=" AND sem.sistema_id in ('".str_replace(",", "','", $busqueda_sistema)."')";
	}
	if (!Empty($busqueda_zona)) {
		$where_zona=" AND sez.zona_id in ('".str_replace(",", "','", $busqueda_zona)."')";
	}
	if (!Empty($busqueda_provincia)) {
		$where_provincia=" AND sez.provincia in ('".str_replace(",", "','", $busqueda_provincia)."')";
	}
	if (!Empty($busqueda_estado) || $busqueda_estado == "0") {
		$where_estado=" AND sez.estado in ('".str_replace(",", "','", $busqueda_estado)."')";
	}
 
	$query_1 =" 
	SELECT 
		sem.id,
		sem.sistema,
		sem.t_revision,
		sem.t_coordinacion,
		sez.valor_v,
		sez.valor_e,
		sez.zona,
		sez.provincia,
		sez.tipo,
		sez.razon_social,
		IF(sez.estado = 1, 'Activo', 'Inactivo') as estado
	FROM tbl_solicitud_estimacion_mantenimiento sem
	LEFT JOIN tbl_solicitud_estimacion_zona sez ON sez.id = sem.solicitud_estimacion_zona_id
	WHERE 1 

	".$where_sistema ."
	".$where_zona ." 
	".$where_provincia ."
	".$where_estado ."

	order by sem.id DESC
	";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$list_transaccion=array();
	while ($li=$list_query->fetch_assoc()) {
		$list_transaccion[]=$li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	if(count($list_transaccion)==0){
		$result["http_code"] = 400;
		$result["status"] ="No hay transacciones.";
	} elseif(count($list_transaccion)>0){
		$result["http_code"] = 200;
		$result["status"] ="ok";
		$result["result"] =$list_transaccion;
		//$result["login"]=$login;
	} else{
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
	}
	echo json_encode($result);
	return;
}

// SAVE MODAL
if(isset($_POST["set_solicitud_estimacion_save"])){
	$user_id = $login["id"];
	extract($_POST);
	if(!isset($razon_social_id) || $razon_social_id == "" || $razon_social_id == "0"){
		$return["error"] = "razon_social_id";
		$return["error_msg"] = "Debe seleccionar la razón social.";
		$return["error_focus"] = "razon_social_id";
		die(json_encode($return));
	}
    if(!isset($zona_id) || $zona_id == "" || $zona_id == "0"){
		$return["error"] = "zona_id";
		$return["error_msg"] = "Debe seleccionar la zona.";
		$return["error_focus"] = "zona_id";
		die(json_encode($return));
	}
	if(!isset($departamento_id) || $departamento_id == "" || $departamento_id == "0"){
		$return["error"] = "departamento_id";
		$return["error_msg"] = "Debe seleccionar el departamento.";
		$return["error_focus"] = "departamento_id";
		die(json_encode($return));
	}
	if($tipo == "servicio tecnico"){
		if(!isset($equipo_id) || $equipo_id == "" || $equipo_id == "0"){
			$return["error"] = "equipo_id";
			$return["error_msg"] = "Debe seleccionar el equipo.";
			$return["error_focus"] = "equipo_id";
			die(json_encode($return));
		}

		if($equipo_id != 0){
			$command_equipo = "SELECT nombre FROM tbl_servicio_tecnico_equipo 
			WHERE estado = 1 AND id = $equipo_id";
			$list_equipo = $mysqli->query($command_equipo)->fetch_assoc();
			$equipo = $list_equipo["nombre"];
		}
	}elseif($tipo == "mantenimiento"){
		if(!isset($sistema_id) || $sistema_id == "" || $sistema_id == "0"){
			$return["error"] = "sistema_id";
			$return["error_msg"] = "Debe seleccionar el sistema.";
			$return["error_focus"] = "sistema_id";
			die(json_encode($return));
		}

		if($sistema_id != 0){
			$command_sistema = "SELECT nombre FROM tbl_solicitud_mantenimiento_sistema 
			WHERE estado = 1 AND id = $sistema_id";
			$list_sistema = $mysqli->query($command_sistema)->fetch_assoc();
			$sistema = $list_sistema["nombre"];
		}
	}
	if(!isset($t_revision) || $t_revision == ""){
		$return["error"] = "t_revision";
		$return["error_msg"] = "Debe ingresar el t. revisión.";
		$return["error_focus"] = "t_revision";
		die(json_encode($return));
	}
    if(!isset($t_coordinacion) || $t_coordinacion == ""){
		$return["error"] = "t_coordinacion";
		$return["error_msg"] = "Debe ingresar el t. coordinación.";
		$return["error_focus"] = "t_coordinacion";
		die(json_encode($return));
	}
    if(!isset($valor_v) || $valor_v == ""){
		$return["error"] = "valor_v";
		$return["error_msg"] = "Debe ingresar el valor v.";
		$return["error_focus"] = "valor_v";
		die(json_encode($return));
	}
    if(!isset($valor_e) || $valor_e == ""){
		$return["error"] = "valor_e";
		$return["error_msg"] = "Debe ingresar el valor e.";
		$return["error_focus"] = "valor_e";
		die(json_encode($return));
	}

    $command_zona = "SELECT nombre FROM tbl_zonas WHERE id = $zona_id";
    $list_zona = $mysqli->query($command_zona)->fetch_assoc();
    $zona = $list_zona["nombre"];

    $t_revision = (float) $t_revision;
    $t_coordinacion = (float) $t_coordinacion;
    $valor_v = (float) $valor_v;
    $valor_e = (float) $valor_e;

	if($t_revision <= 0 || $t_coordinacion <= 0 || $valor_v <= 0 || $valor_e <= 0){
		$return["error"] = "valores_negativos";
		$return["error_msg"] = "Los valores ingresados no pueden ser 0 o negativos.";
		$return["error_focus"] = "valores_negativos";
		die(json_encode($return));
	}

	$command_razon_social = "SELECT id, nombre FROM tbl_razon_social WHERE id = {$razon_social_id} AND status = 1";
	$list_razon_social = $mysqli->query($command_razon_social)->fetch_assoc();
	$razon_social = $list_razon_social["nombre"];

    if($departamento_id == "0"){
        $departamento = "NULL";
    }else{
        $command_departamento = "SELECT nombre FROM tbl_ubigeo 
            WHERE cod_prov = '00' 
            AND cod_dist = '00' 
            AND estado = 1
            AND id = $departamento_id";
        $list_departamento = $mysqli->query($command_departamento)->fetch_assoc();
        $departamento = $list_departamento["nombre"];
        $departamento = "'$departamento'";
    }

	if($tipo == "servicio tecnico"){
		$command_repetidos = "SELECT 
						count(*) as total
					FROM tbl_solicitud_estimacion_servicio_tecnico se
					LEFT JOIN tbl_solicitud_estimacion_zona sez ON sez.id = se.solicitud_estimacion_zona_id
					where sez.zona_id = {$zona_id} AND se.equipo_id = {$equipo_id} AND sez.razon_social_id = {$razon_social_id}";
		
		if($departamento_id == "0"){
			$command_repetidos.= " AND sez.provincia is null";
		}else{
			$command_repetidos.= " AND sez.provincia = $departamento";
		}

		$list_repetidos = $mysqli->query($command_repetidos)->fetch_assoc();
		$total = $list_repetidos["total"];
		if($total > 0){
			$return["error"] = "servicio tecnico repetidos";
			$return["error_msg"] = "No se pueden repetir los tiempo de solicitud estimado.";
			$return["error_focus"] = "servicio tecnico repetido";
			die(json_encode($return));
		}
	}elseif($tipo == "mantenimiento"){
		$command_repetidos = "SELECT 
						count(*) as total
					FROM tbl_solicitud_estimacion_mantenimiento sem
					LEFT JOIN tbl_solicitud_estimacion_zona sez ON sez.id = sem.solicitud_estimacion_zona_id
					where sez.zona_id = {$zona_id} AND sem.sistema_id = {$sistema_id}  AND sez.razon_social_id = {$razon_social_id}";

		if($departamento_id == "0"){
			$command_repetidos.= " AND sez.provincia is null";
		}else{
			$command_repetidos.= " AND sez.provincia = $departamento";
		}

		$list_repetidos = $mysqli->query($command_repetidos)->fetch_assoc();
		$total = $list_repetidos["total"];
		if($total > 0){
			$return["error"] = "mantenimiento repetidos";
			$return["error_msg"] = "No se pueden repetir los tiempo de solicitud estimado.";
			$return["error_focus"] = "mantenimiento repetido";
			die(json_encode($return));
		}
	}

    if($tipo == "servicio tecnico"){
        // ZONA
        $insert_command_zona = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_zona`
		(
			razon_social_id
			,razon_social
			,zona_id
			,zona
			,provincia
			,valor_v
			,valor_e
			,tipo
			,estado
		)
			VALUES
		(
			$razon_social_id
			,'".$razon_social."'
            ,$zona_id
			,'".$zona."'
			,$departamento
			,$valor_v
			,$valor_e
			,'".$tipo."'
			,1
		)";
        $return["insert_command_zona"] = $insert_command_zona;
		$mysqli->query($insert_command_zona);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command_zona;
			exit();
		}
        $ultimo_id_zona = $mysqli->insert_id;

        // SERVICIO TECNICO
        $insert_command_servicio_tecnico = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_servicio_tecnico`
		(
			equipo_id
			,equipo
			,t_revision
			,t_coordinacion
			,solicitud_estimacion_zona_id
		)
			VALUES
		(
            $equipo_id
			,'".$equipo."'
			,$t_revision
			,$t_coordinacion
            ,$ultimo_id_zona
		)";
        $return["insert_command_servicio_tecnico"] = $insert_command_servicio_tecnico;
        // $return["table_reload"] = 'servicio tecnico';
		$mysqli->query($insert_command_servicio_tecnico);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command_servicio_tecnico;
			exit();
		}
    }elseif($tipo == "mantenimiento"){
        // ZONA
        $insert_command_zona = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_zona`
		(
			razon_social_id
			,razon_social
			,zona_id
			,zona
			,provincia
			,valor_v
			,valor_e
			,tipo
			,estado
		)
			VALUES
		(
			$razon_social_id
			,'".$razon_social."'
            ,$zona_id
			,'".$zona."'
			,$departamento
			,$valor_v
			,$valor_e
			,'".$tipo."'
			,1
		)";
        $return["insert_command_zona"] = $insert_command_zona;
		$mysqli->query($insert_command_zona);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command_zona;
			exit();
		}
        $ultimo_id_zona = $mysqli->insert_id;

        // MANTENIMIENTO
        $insert_command_mantenimiento = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_mantenimiento`
		(
			sistema_id
			,sistema
			,t_revision
			,t_coordinacion
			,solicitud_estimacion_zona_id
		)
			VALUES
		(
            $sistema_id
			,'".$sistema."'
			,$t_revision
			,$t_coordinacion
            ,$ultimo_id_zona
		)";
        $return["insert_command_mantenimiento"] = $insert_command_mantenimiento;
        // $return["table_reload"] = 'mantenimiento';
		$mysqli->query($insert_command_mantenimiento);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command_mantenimiento;
			exit();
		}
    }

	// $id_servicio_tecnico_historial = $mysqli->insert_id;
	$return["mensaje"] = "Solicitud Estimación Guardada Correctamente";
	$return["curr_login"] = $login;
	// $return["id_servicio_tecnico_historial"] = $id_servicio_tecnico_historial;
}

if(isset($_POST["sec_solicitud_estimacion_cargar_solicitud"])){
	$solicitud_id = $_POST["solicitud_id"];
	
    $command ="SELECT
        se.id,
        se.equipo_id,
        se.equipo,
        se.t_revision,
        se.t_coordinacion,
        sez.zona_id,
        sez.zona,
        sez.provincia,
        sez.valor_v,
        sez.valor_e,
        sez.tipo,
		sez.estado,
		sez.razon_social_id
    FROM tbl_solicitud_estimacion_servicio_tecnico se
    LEFT JOIN tbl_solicitud_estimacion_zona sez on sez.id = se.solicitud_estimacion_zona_id
    WHERE se.id = {$solicitud_id}";

    $list_query = $mysqli->query($command);
    $list = $list_query->fetch_assoc();
	
	$return["solicitud"] = $list;
}

if(isset($_POST["sec_solicitud_estimacion_mantenimiento_cargar_solicitud"])){
	$solicitud_id = $_POST["solicitud_id"];
	
    $command ="SELECT
        sem.id,
        sem.sistema_id,
        sem.sistema,
        sem.t_revision,
        sem.t_coordinacion,
        sez.zona_id,
        sez.zona,
        sez.provincia,
        sez.valor_v,
        sez.valor_e,
        sez.tipo,
		sez.estado,
		sez.razon_social_id
    FROM tbl_solicitud_estimacion_mantenimiento sem
    LEFT JOIN tbl_solicitud_estimacion_zona sez on sez.id = sem.solicitud_estimacion_zona_id
    WHERE sem.id = {$solicitud_id}";

    $list_query = $mysqli->query($command);
    $list = $list_query->fetch_assoc();
	
	$return["solicitud"] = $list;
}

// UPDATE MODAL SOLICITUD ESTIMACION SERVICIO TECNICO
if(isset($_POST["sec_solicitud_estimacion_servicio_tecnico_update"])){
	$user_id = $login["id"];
	extract($_POST);

	if(!isset($razon_social_id_modal) || $razon_social_id_modal == "" || $razon_social_id_modal == "0"){
		$return["error"] = "razon_social_id";
		$return["error_msg"] = "Debe seleccionar la razón social.";
		$return["error_focus"] = "razon_social_id";
		die(json_encode($return));
	}

	if(!isset($zona_id_modal) || $zona_id_modal == "" || $zona_id_modal == 0){
		$return["error"] = "zona_id";
		$return["error_msg"] = "Debe seleccionar la zona.";
		$return["error_focus"] = "zona_id";
		die(json_encode($return));
	}

	if(!isset($provincia) || $provincia == "" || $provincia == "0"){
		$return["error"] = "departamento_id";
		$return["error_msg"] = "Debe seleccionar el departamento.";
		$return["error_focus"] = "departamento_id";
		die(json_encode($return));
	}

	$zona_id = $zona_id_modal;
	$razon_social_id = $razon_social_id_modal;

	if($tipo == "Servicio Técnico"){
		if(!isset($equipo_id) || $equipo_id == "" || $equipo_id == 0){
			$return["error"] = "equipo_id";
			$return["error_msg"] = "Debe seleccionar el equipo.";
			$return["error_focus"] = "equipo_id";
			die(json_encode($return));
		}
	}elseif($tipo == "Mantenimiento"){
		if(!isset($sistema_id) || $sistema_id == "" || $sistema_id == 0){
			$return["error"] = "sistema_id";
			$return["error_msg"] = "Debe seleccionar el sistema.";
			$return["error_focus"] = "sistema_id";
			die(json_encode($return));
		}
	}
	if(!isset($t_revision) || $t_revision == ""){
		$return["error"] = "t_revision";
		$return["error_msg"] = "Debe ingresar el t. revisión.";
		$return["error_focus"] = "t_revision";
		die(json_encode($return));
	}
    if(!isset($t_coordinacion) || $t_coordinacion == ""){
		$return["error"] = "t_coordinacion";
		$return["error_msg"] = "Debe ingresar el t. coordinación.";
		$return["error_focus"] = "t_coordinacion";
		die(json_encode($return));
	}
    if(!isset($valor_v) || $valor_v == ""){
		$return["error"] = "valor_v";
		$return["error_msg"] = "Debe ingresar el valor v.";
		$return["error_focus"] = "valor_v";
		die(json_encode($return));
	}
    if(!isset($valor_e) || $valor_e == ""){
		$return["error"] = "valor_e";
		$return["error_msg"] = "Debe ingresar el valor e.";
		$return["error_focus"] = "valor_e";
		die(json_encode($return));
	}

    $command_zona = "SELECT nombre FROM tbl_zonas WHERE id = $zona_id";
    $list_zona = $mysqli->query($command_zona)->fetch_assoc();
    $zona = $list_zona["nombre"];

    if($equipo_id != 0){
        $command_equipo = "SELECT nombre FROM tbl_servicio_tecnico_equipo 
        WHERE estado = 1 AND id = $equipo_id";
        $list_equipo = $mysqli->query($command_equipo)->fetch_assoc();
        $equipo = $list_equipo["nombre"];
    }

    if($sistema_id != 0){
        $command_sistema = "SELECT nombre FROM tbl_solicitud_mantenimiento_sistema 
        WHERE estado = 1 AND id = $sistema_id";
        $list_sistema = $mysqli->query($command_sistema)->fetch_assoc();
        $sistema = $list_sistema["nombre"];
    }

    $t_revision = (float) $t_revision;
    $t_coordinacion = (float) $t_coordinacion;
    $valor_v = (float) $valor_v;
    $valor_e = (float) $valor_e;
	$estado = $estado;

	if($t_revision <= 0 || $t_coordinacion <= 0 || $valor_v <= 0 || $valor_e <= 0){
		$return["error"] = "valores_negativos";
		$return["error_msg"] = "Los valores ingresados no pueden ser 0 o negativos.";
		$return["error_focus"] = "valores_negativos";
		die(json_encode($return));
	}

	//ACA ARREGLAR
	$command_razon_social = "SELECT id, nombre FROM tbl_razon_social WHERE id = {$razon_social_id} AND status = 1";
	$list_razon_social = $mysqli->query($command_razon_social)->fetch_assoc();
	$razon_social = $list_razon_social["nombre"];

    if($provincia == "0" || $provincia == ""){
        $departamento = "NULL";
    }else{
        $departamento = "'$provincia'";
    }

	// VALIDAR QUE NO SE REPITA LA SOLICITUD ESTIMACION
	// if($tipo == "Servicio Técnico"){
	// 	$command_repetidos = "SELECT 
	// 					count(*) as total
	// 				FROM tbl_solicitud_estimacion_servicio_tecnico se
	// 				LEFT JOIN tbl_solicitud_estimacion_zona sez ON sez.id = se.solicitud_estimacion_zona_id
	// 				where sez.zona_id = {$zona_id} AND se.equipo_id = {$equipo_id} AND sez.razon_social_id = {$razon_social_id}";
		
	// 	if($departamento == "0"){
	// 		$command_repetidos.= " AND sez.provincia is null";
	// 	}else{
	// 		$command_repetidos.= " AND sez.provincia = $departamento";
	// 	}

	// 	$list_repetidos = $mysqli->query($command_repetidos)->fetch_assoc();
	// 	$total = $list_repetidos["total"];
	// 	if($total > 0){
	// 		$return["error"] = "servicio tecnico repetidos";
	// 		$return["error_msg"] = "No se pueden repetir los tiempo de solicitud estimado.";
	// 		$return["error_focus"] = "servicio tecnico repetido";
	// 		die(json_encode($return));
	// 	}
	// }elseif($tipo == "Mantenimiento"){
	// 	$command_repetidos = "SELECT 
	// 					count(*) as total
	// 				FROM tbl_solicitud_estimacion_mantenimiento sem
	// 				LEFT JOIN tbl_solicitud_estimacion_zona sez ON sez.id = sem.solicitud_estimacion_zona_id
	// 				where sez.zona_id = {$zona_id} AND sem.sistema_id = {$sistema_id}  AND sez.razon_social_id = {$razon_social_id}";

	// 	if($departamento == "0"){
	// 		$command_repetidos.= " AND sez.provincia is null";
	// 	}else{
	// 		$command_repetidos.= " AND sez.provincia = $departamento";
	// 	}

	// 	$list_repetidos = $mysqli->query($command_repetidos)->fetch_assoc();
	// 	$total = $list_repetidos["total"];
	// 	if($total > 0){
	// 		$return["error"] = "mantenimiento repetidos";
	// 		$return["error_msg"] = "No se pueden repetir los tiempo de solicitud estimado.";
	// 		$return["error_focus"] = "mantenimiento repetido";
	// 		die(json_encode($return));
	// 	}
	// }

    if($tipo == "Servicio Técnico"){
        $command_select = "SELECT solicitud_estimacion_zona_id FROM tbl_solicitud_estimacion_servicio_tecnico
        WHERE id = {$id}";
        $list_select = $mysqli->query($command_select)->fetch_assoc();
        $solicitud_estimacion_zona_id = $list_select["solicitud_estimacion_zona_id"];

        $command_update_servicio_tecnico = "UPDATE tbl_solicitud_estimacion_servicio_tecnico SET
                                                equipo_id = $equipo_id
                                                ,equipo = '".$equipo."'
                                                ,t_revision = $t_revision
                                                ,t_coordinacion = $t_coordinacion
                                            WHERE id = ".$id;
        $mysqli->query($command_update_servicio_tecnico);
        if($mysqli->error){
            print_r($mysqli->error);
            echo "\n";
            echo $command_update_zona;
            exit();
        }

        $command_update_zona = "UPDATE tbl_solicitud_estimacion_zona SET
									razon_social_id = $razon_social_id
									,razon_social = '".$razon_social."'
                                    ,zona_id = $zona_id
                                    ,zona = '".$zona."'
                                    ,provincia = $departamento
                                    ,valor_v = $valor_v
                                    ,valor_e = $valor_e
                                    ,tipo = 'servicio tecnico'
									,estado = $estado
                                WHERE id = ".$solicitud_estimacion_zona_id;
        $mysqli->query($command_update_zona);
        if($mysqli->error){
            print_r($mysqli->error);
            echo "\n";
            echo $command_update_zona;
            exit();
        }

    }elseif($tipo == "Mantenimiento"){
        $command_select = "SELECT solicitud_estimacion_zona_id FROM tbl_solicitud_estimacion_mantenimiento
        WHERE id = {$id}";
        $list_select = $mysqli->query($command_select)->fetch_assoc();
        $solicitud_estimacion_zona_id = $list_select["solicitud_estimacion_zona_id"];

        $command_update_mantenimiento = "UPDATE tbl_solicitud_estimacion_mantenimiento SET
                        sistema_id = $sistema_id
                        ,sistema = '".$sistema."'
                        ,t_revision = $t_revision
                        ,t_coordinacion = $t_coordinacion
                    WHERE id = ".$id;
        $mysqli->query($command_update_mantenimiento);
        if($mysqli->error){
            print_r($mysqli->error);
            echo "\n";
            echo $command_update_mantenimiento;
            exit();
        }

        $command_update_zona = "UPDATE tbl_solicitud_estimacion_zona SET
									razon_social_id = $razon_social_id
									,razon_social = '".$razon_social."'
                                    ,zona_id = $zona_id
                                    ,zona = '".$zona."'
                                    ,provincia = $departamento
                                    ,valor_v = $valor_v
                                    ,valor_e = $valor_e
                                    ,tipo = 'mantenimiento'
									,estado = $estado
                                WHERE id = ".$solicitud_estimacion_zona_id;
        $mysqli->query($command_update_zona);
        if($mysqli->error){
            print_r($mysqli->error);
            echo "\n";
            echo $command_update_zona;
            exit();
        }
    }

	$return["mensaje"] = "Solicitud de Estimación ".$id." Actualizada";
	$return["curr_login"]=$login;
}

// UPDATE MODAL SOLICITUD ESTIMACION MANTENIMIENTO
// if(isset($_POST["sec_solicitud_estimacion_mantenimiento_update"])){
// 	$user_id = $login["id"];
// 	extract($_POST);
// 	if(!isset($zona_id) || $zona_id == ""){
// 		$return["error"] = "zona_id";
// 		$return["error_msg"] = "Debe seleccionar la zona.";
// 		$return["error_focus"] = "zona_id";
// 		die(json_encode($return));
// 	}
//     if(!isset($equipo_id) || $equipo_id == ""){
// 		$return["error"] = "equipo_id";
// 		$return["error_msg"] = "Debe seleccionar el equipo.";
// 		$return["error_focus"] = "equipo_id";
// 		die(json_encode($return));
// 	}
// 	if(!isset($t_revision) || $t_revision == ""){
// 		$return["error"] = "t_revision";
// 		$return["error_msg"] = "Debe ingresar el t. revisión.";
// 		$return["error_focus"] = "t_revision";
// 		die(json_encode($return));
// 	}
//     if(!isset($t_coordinacion) || $t_coordinacion == ""){
// 		$return["error"] = "t_coordinacion";
// 		$return["error_msg"] = "Debe ingresar el t. coordinación.";
// 		$return["error_focus"] = "t_coordinacion";
// 		die(json_encode($return));
// 	}
//     if(!isset($valor_v) || $valor_v == ""){
// 		$return["error"] = "valor_v";
// 		$return["error_msg"] = "Debe ingresar el valor v.";
// 		$return["error_focus"] = "valor_v";
// 		die(json_encode($return));
// 	}
//     if(!isset($valor_e) || $valor_e == ""){
// 		$return["error"] = "valor_e";
// 		$return["error_msg"] = "Debe ingresar el valor e.";
// 		$return["error_focus"] = "valor_e";
// 		die(json_encode($return));
// 	}

//     $command_zona = "SELECT nombre FROM tbl_zonas WHERE id = $zona_id";
//     $list_zona = $mysqli->query($command_zona)->fetch_assoc();
//     $zona = $list_zona["nombre"];

//     if($equipo_id != 0){
//         $command_equipo = "SELECT nombre FROM tbl_servicio_tecnico_equipo 
//         WHERE estado = 1 AND id = $equipo_id";
//         $list_equipo = $mysqli->query($command_equipo)->fetch_assoc();
//         $equipo = $list_equipo["nombre"];
//     }

//     if($sistema_id != 0){
//         $command_sistema = "SELECT nombre FROM tbl_solicitud_mantenimiento_sistema 
//         WHERE estado = 1 AND id = $sistema_id";
//         $list_sistema = $mysqli->query($command_sistema)->fetch_assoc();
//         $sistema = $list_sistema["nombre"];
//     }

//     $t_revision = (float) $t_revision;
//     $t_coordinacion = (float) $t_coordinacion;
//     $valor_v = (float) $valor_v;
//     $valor_e = (float) $valor_e;

//     if($provincia == "0" || $provincia == ""){
//         $departamento = "NULL";
//     }else{
//         $departamento = "'$provincia'";
//     }

//     $command_select = "SELECT solicitud_estimacion_zona_id FROM tbl_solicitud_estimacion_mantenimiento
//     WHERE id = {$id}";
//     $list_select = $mysqli->query($command_select)->fetch_assoc();
//     $solicitud_estimacion_zona_id = $list_select["solicitud_estimacion_zona_id"];

//     $command_update_mantenimiento = "UPDATE tbl_solicitud_estimacion_mantenimiento SET
//                     sistema_id = $sistema_id
//                     ,sistema = '".$sistema."'
//                     ,t_revision = $t_revision
//                     ,t_coordinacion = $t_coordinacion
//                 WHERE id = ".$id;
//     $mysqli->query($command_update_mantenimiento);
//     if($mysqli->error){
//         print_r($mysqli->error);
//         echo "\n";
//         echo $command_update_mantenimiento;
//         exit();
//     }

//     $command_update_zona = "UPDATE tbl_solicitud_estimacion_zona SET
//                                 zona_id = $zona_id
//                                 ,zona = '".$zona."'
//                                 ,provincia = $departamento
//                                 ,valor_v = $valor_v
//                                 ,valor_e = $valor_e
//                                 ,tipo = 'mantenimiento'
//                             WHERE id = ".$solicitud_estimacion_zona_id;
//     $mysqli->query($command_update_zona);
//     if($mysqli->error){
//         print_r($mysqli->error);
//         echo "\n";
//         echo $command_update_zona;
//         exit();
//     }

// 	$return["mensaje"] = "Solicitud de Estimación ".$id." Actualizada";
// 	$return["curr_login"]=$login;
// }

if (isset($_POST['razon_social_id'])) {
    $razon_social_id = $_POST['razon_social_id'];

	$command_zonas = "SELECT id, nombre FROM tbl_zonas
	where razon_social_id = {$razon_social_id}";

	$registros = $mysqli->query($command_zonas);
	$data = array();

	while ($row = $registros->fetch_assoc()) {
		$data[] = $row;
	}

	echo json_encode($data);
	return;
}

if(isset($_POST['sec_solicitud_estimacion_servicio_tecnico_upload'])){
	if ($_FILES['archivo_solicitud_servicio_tecnico']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['archivo_solicitud_servicio_tecnico']['tmp_name'])) {
		$objPHPExcel = PHPExcel_IOFactory::load($_FILES['archivo_solicitud_servicio_tecnico']['tmp_name']);
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

		$command_delete_servicio_tecnico = "DELETE FROM `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_servicio_tecnico`";
		$return["command_delete_servicio_tecnico"] = $command_delete_servicio_tecnico;
		$mysqli->query($command_delete_servicio_tecnico);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command_delete_servicio_tecnico;
			exit();
		}

		$command_delete_mantenimiento = "DELETE FROM `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_mantenimiento`";
		$return["command_delete_mantenimiento"] = $command_delete_mantenimiento;
		$mysqli->query($command_delete_mantenimiento);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command_delete_mantenimiento;
			exit();
		}

		$command_delete_zona = "DELETE FROM `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_zona`";
		$return["command_delete_zona"] = $command_delete_zona;
		$mysqli->query($command_delete_zona);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command_delete_zona;
			exit();
		}

		$excel_servicio_tecnico_zonas_free_games = array();
		$excel_servicio_tecnico_zonas_igh = array();
		$excel_mantenimiento_zonas_free_games = array();
		$excel_mantenimiento_zonas_igh = array();

		$excel_servicio_tecnico_valores_free_games = array();
		$excel_servicio_tecnico_valores_igh = array();
		$excel_mantenimiento_valores_free_games = array();
		$excel_mantenimiento_valores_igh = array();

		foreach($sheetData as $filas){
			if($filas['A'] == 'FREE GAMES' && $filas['B'] == 'SERVICIO TECNICO VALORES'){
				// array_shift($filas, 2);
				array_splice($filas, 1, 1);
				$excel_servicio_tecnico_valores_free_games[] = array_values($filas);
			}else if($filas['A'] == 'INVERSIONES GAMING HOUSE S.A.C.' && $filas['B'] == 'SERVICIO TECNICO VALORES'){
				array_splice($filas, 1, 1);
				$excel_servicio_tecnico_valores_igh[] = array_values($filas);
			}
			if($filas['A'] == 'FREE GAMES' && $filas['B'] == 'MANTENIMIENTO VALORES'){
				// array_shift($filas, 2);
				array_splice($filas, 1, 1);
				$excel_mantenimiento_valores_free_games[] = array_values($filas);
			}else if($filas['A'] == 'INVERSIONES GAMING HOUSE S.A.C.' && $filas['B'] == 'MANTENIMIENTO VALORES'){
				array_splice($filas, 1, 1);
				$excel_mantenimiento_valores_igh[] = array_values($filas);
			}
			foreach($filas as $data){
				if($filas['A'] == 'FREE GAMES' && $filas['B'] == 'SERVICIO TECNICO ZONAS'){
					if($data != null && $data != 'SERVICIO TECNICO ZONAS' && $data != 'FREE GAMES' && $data != 'INVERSIONES GAMING HOUSE S.A.C.'){
						$array_data = explode(",", $data);
						array_push($excel_servicio_tecnico_zonas_free_games, $array_data);
					}
				}else if($filas['A'] == 'INVERSIONES GAMING HOUSE S.A.C.' && $filas['B'] == 'SERVICIO TECNICO ZONAS'){
					if($data != null && $data != 'SERVICIO TECNICO ZONAS' && $data != 'FREE GAMES' && $data != 'INVERSIONES GAMING HOUSE S.A.C.'){
						$array_data = explode(",", $data);
						array_push($excel_servicio_tecnico_zonas_igh, $array_data);
					}
				}
				if($filas['A'] == 'FREE GAMES' && $filas['B'] == 'MANTENIMIENTO ZONAS'){
					if($data != null && $data != 'MANTENIMIENTO ZONAS' && $data != 'FREE GAMES' && $data != 'INVERSIONES GAMING HOUSE S.A.C.'){
						$array_data = explode(",", $data);
						array_push($excel_mantenimiento_zonas_free_games, $array_data);
					}
				}else if($filas['A'] == 'INVERSIONES GAMING HOUSE S.A.C.' && $filas['B'] == 'MANTENIMIENTO ZONAS'){
					if($data != null && $data != 'MANTENIMIENTO ZONAS' && $data != 'FREE GAMES' && $data != 'INVERSIONES GAMING HOUSE S.A.C.'){
						$array_data = explode(",", $data);
						array_push($excel_mantenimiento_zonas_igh, $array_data);
					}
				}
			}
		}

		$cantidad_zonas_st_fg = count($excel_servicio_tecnico_zonas_free_games);
		$cantidad_zonas_st_igh = count($excel_servicio_tecnico_zonas_igh);
		$cantidad_zonas_m_fg = count($excel_mantenimiento_zonas_free_games);
		$cantidad_zonas_m_igh = count($excel_mantenimiento_zonas_igh);

		$registro_servicio_tecnico_fg = array();
		$registro_servicio_tecnico_igh = array();
		$registro_mantenimiento_fg = array();
		$registro_mantenimiento_igh = array();

		foreach($excel_servicio_tecnico_valores_free_games as $servicio_tecnico_valores_fg){
			$servicio_tecnico_revision_coordinacion_fg = array_chunk($servicio_tecnico_valores_fg, 4);
			array_push($registro_servicio_tecnico_fg, $servicio_tecnico_revision_coordinacion_fg[0]);
		}
		foreach($excel_servicio_tecnico_valores_igh as $servicio_tecnico_valores_igh){
			$servicio_tecnico_revision_coordinacion_igh = array_chunk($servicio_tecnico_valores_igh, 4);
			array_push($registro_servicio_tecnico_igh, $servicio_tecnico_revision_coordinacion_igh[0]);
		}

		foreach($excel_mantenimiento_valores_free_games as $mantenimiento_valores_fg){
			$mantenimiento_revision_coordinacion_fg = array_chunk($mantenimiento_valores_fg, 4);
			array_push($registro_mantenimiento_fg, $mantenimiento_revision_coordinacion_fg[0]);
		}
		foreach($excel_mantenimiento_valores_igh as $mantenimiento_valores_igh){
			$mantenimiento_revision_coordinacion_igh = array_chunk($mantenimiento_valores_igh, 4);
			array_push($registro_mantenimiento_igh, $mantenimiento_revision_coordinacion_igh[0]);
		}

		$servicio_tecnico_v_e_fg = array();
		$servicio_tecnico_v_e_igh = array();
		$mantenimiento_v_e_fg = array();
		$mantenimiento_v_e_igh = array();

		foreach($excel_servicio_tecnico_valores_free_games as $data){
			$servicio_tecnico_v_e_fg[] = array_slice($data, 4);
		}
		foreach($excel_servicio_tecnico_valores_igh as $data){
			$servicio_tecnico_v_e_igh[] = array_slice($data, 4);
		}

		foreach($excel_mantenimiento_valores_free_games as $data){
			$mantenimiento_v_e_fg[] = array_slice($data, 4);
		}
		foreach($excel_mantenimiento_valores_igh as $data){
			$mantenimiento_v_e_igh[] = array_slice($data, 4);
		}

		$servicio_tecnico_registro_final_fg = array();
		$servicio_tecnico_registro_final_igh = array();
		$mantenimiento_registro_final_fg = array();
		$mantenimiento_registro_final_igh = array();

		foreach($excel_servicio_tecnico_zonas_free_games as $zonas){
			foreach($registro_servicio_tecnico_fg as $registro){
				$servicio_tecnico_registro_final_fg[] = array_merge($zonas, $registro);
			}
		}
		foreach($excel_servicio_tecnico_zonas_igh as $zonas){
			foreach($registro_servicio_tecnico_igh as $registro){
				$servicio_tecnico_registro_final_igh[] = array_merge($zonas, $registro);
			}
		}

		foreach($excel_mantenimiento_zonas_free_games as $zonas){
			foreach($registro_mantenimiento_fg as $registro){
				$mantenimiento_registro_final_fg[] = array_merge($zonas, $registro);
			}
		}
		foreach($excel_mantenimiento_zonas_igh as $zonas){
			foreach($registro_mantenimiento_igh as $registro){
				$mantenimiento_registro_final_igh[] = array_merge($zonas, $registro);
			}
		}

		$servicio_tecnico_v_e_final_fg = array();
		$servicio_tecnico_v_e_final_igh = array();
		$mantenimiento_v_e_final_fg = array();
		$mantenimiento_v_e_final_igh = array();

		foreach($servicio_tecnico_v_e_fg as $st_ve){	
			$servicio_tecnico_v_e_final_fg[] = array_chunk($st_ve, 2);
		}
		foreach($servicio_tecnico_v_e_igh as $m_ve){	
			$servicio_tecnico_v_e_final_igh[] = array_chunk($m_ve, 2);
		}

		foreach($mantenimiento_v_e_fg as $st_ve){	
			$mantenimiento_v_e_final_fg[] = array_chunk($st_ve, 2);
		}
		foreach($mantenimiento_v_e_igh as $m_ve){	
			$mantenimiento_v_e_final_igh[] = array_chunk($m_ve, 2);
		}

		$servicio_tecnico_v_e_final_ordenado_fg = array();
		$servicio_tecnico_v_e_final_ordenado_igh = array();
		$mantenimiento_v_e_final_ordenado_fg = array();
		$mantenimiento_v_e_final_ordenado_igh = array();

		$contador_st_fg = 0;
		$cantidad_servicio_tecnico_registro_final_fg = count($servicio_tecnico_registro_final_fg);
		$valores_st_fg = $cantidad_servicio_tecnico_registro_final_fg / $cantidad_zonas_st_fg;
		while($contador_st_fg < $cantidad_zonas_st_fg){
			for($i = 0; $i < $valores_st_fg; $i++){
				$servicio_tecnico_v_e_final_ordenado_fg[] = $servicio_tecnico_v_e_final_fg[$i][$contador_st_fg];
			}
			$contador_st_fg++;
		}
		$contador_st_igh = 0;
		$cantidad_servicio_tecnico_registro_final_igh = count($servicio_tecnico_registro_final_igh);
		$valores_st_igh = $cantidad_servicio_tecnico_registro_final_igh / $cantidad_zonas_st_igh;
		while($contador_st_igh < $cantidad_zonas_st_igh){
			for($i = 0; $i < $valores_st_igh; $i++){
				$servicio_tecnico_v_e_final_ordenado_igh[] = $servicio_tecnico_v_e_final_igh[$i][$contador_st_igh];
			}
			$contador_st_igh++;
		}

		$contador_m_fg = 0;
		$cantidad_mantenimiento_registro_final_fg = count($mantenimiento_registro_final_fg);
		$valores_m_fg = $cantidad_mantenimiento_registro_final_fg / $cantidad_zonas_m_fg;
		while($contador_m_fg < $cantidad_zonas_m_fg){
			for($i = 0; $i < $valores_m_fg; $i++){
				$mantenimiento_v_e_final_ordenado_fg[] = $mantenimiento_v_e_final_fg[$i][$contador_m_fg];
			}
			$contador_m_fg++;
		}
		$contador_m_igh = 0;
		$cantidad_mantenimiento_registro_final_igh = count($mantenimiento_registro_final_igh);
		$valores_m_igh = $cantidad_mantenimiento_registro_final_igh / $cantidad_zonas_m_igh;
		while($contador_m_igh < $cantidad_zonas_m_igh){
			for($i = 0; $i < $valores_m_igh; $i++){
				$mantenimiento_v_e_final_ordenado_igh[] = $mantenimiento_v_e_final_igh[$i][$contador_m_igh];
			}
			$contador_m_igh++;
		}

		$array_combinado_st_fg = array();
		$array_combinado_st_igh = array();
		$array_combinado_m_fg = array();
		$array_combinado_m_igh = array();

		foreach ($servicio_tecnico_registro_final_fg as $clave => $valor) {
			if (isset($servicio_tecnico_v_e_final_ordenado_fg[$clave])) {
				$array_combinado_st_fg[] = array($valor, $servicio_tecnico_v_e_final_ordenado_fg[$clave]);
			}
		}
		foreach ($servicio_tecnico_registro_final_igh as $clave => $valor) {
			if (isset($servicio_tecnico_v_e_final_ordenado_igh[$clave])) {
				$array_combinado_st_igh[] = array($valor, $servicio_tecnico_v_e_final_ordenado_igh[$clave]);
			}
		}

		foreach ($mantenimiento_registro_final_fg as $clave => $valor) {
			if (isset($mantenimiento_v_e_final_ordenado_fg[$clave])) {
				$array_combinado_m_fg[] = array($valor, $mantenimiento_v_e_final_ordenado_fg[$clave]);
			}
		}
		foreach ($mantenimiento_registro_final_igh as $clave => $valor) {
			if (isset($mantenimiento_v_e_final_ordenado_igh[$clave])) {
				$array_combinado_m_igh[] = array($valor, $mantenimiento_v_e_final_ordenado_igh[$clave]);
			}
		}

		function combinar_arrays_secundarios($array){
			$resultado = array();

			foreach ($array as $sub_array) {
				$resultado = array_merge($resultado, $sub_array);
			}

			return $resultado;
		}

		$resultado_final_st_fg = array_map('combinar_arrays_secundarios', $array_combinado_st_fg);
		$resultado_final_st_igh = array_map('combinar_arrays_secundarios', $array_combinado_st_igh);
		$resultado_final_m_fg = array_map('combinar_arrays_secundarios', $array_combinado_m_fg);
		$resultado_final_m_igh = array_map('combinar_arrays_secundarios', $array_combinado_m_igh);

		foreach($resultado_final_st_fg as $rf_st){
			$command_zona = "SELECT id FROM tbl_zonas WHERE nombre = '".$rf_st[0]."'";
			$result_zona = $mysqli->query($command_zona);
			if ($result_zona) {
				if ($result_zona->num_rows > 0) {
					$list_zona = $result_zona->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de las zonas en FREE GAMES - Servicio Técnico.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}

			$command_razon_social = "SELECT id FROM tbl_razon_social WHERE nombre = '".$rf_st[2]."'";
			$result_razon_social = $mysqli->query($command_razon_social);
			if ($result_razon_social) {
				if ($result_razon_social->num_rows > 0) {
					$list_razon_social = $result_razon_social->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de las razones sociales en FREE GAMES - Servicio Técnico.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}

			$zona_id = $list_zona["id"];
			$zona = $rf_st[0];
			$provincia = $rf_st[1];
			$valor_v = $rf_st[6];
			$valor_e = $rf_st[7];
			$razon_social_id = $list_razon_social["id"];
			$razon_social = $rf_st[2];
			$tipo = "servicio tecnico";
			$estado = 1;

			$command_equipo = "SELECT id FROM tbl_servicio_tecnico_equipo WHERE nombre = '".$rf_st[3]."'";
			$result_equipo = $mysqli->query($command_equipo);
			if ($result_equipo) {
				if ($result_equipo->num_rows > 0) {
					$list_equipo = $result_equipo->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de los equipos en FREE GAMES - Servicio Técnico.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}
			
			$equipo_id = $list_equipo["id"];
			$equipo = $rf_st[3];
			$t_revision = $rf_st[4];
			$t_coordinacion = $rf_st[5];

			$command_st_zona = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_zona`
			(
				razon_social_id
				,razon_social
				,zona_id
				,zona
				,provincia
				,valor_v
				,valor_e
				,tipo
				,estado
			)
				VALUES
			(
				$razon_social_id
				,'".$razon_social."'
				,$zona_id
				,'".$zona."'
				,'".$provincia."'
				,$valor_v
				,$valor_e
				,'".$tipo."'
				,$estado
			)";

			$return["command_st_zona_fg"] = $command_st_zona;
			$mysqli->query($command_st_zona);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command_st_zona;
				exit();
			}

			$solicitud_estimacion_zona_id = $mysqli->insert_id;

			$command_st = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_servicio_tecnico`
			(
				equipo_id
				,equipo
				,t_revision
				,t_coordinacion
				,solicitud_estimacion_zona_id
			)
				VALUES
			(
				$equipo_id
				,'".$equipo."'
				,$t_revision
				,$t_coordinacion
				,$solicitud_estimacion_zona_id
			)";

			$return["command_st_fg"] = $command_st;
			$mysqli->query($command_st);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command_st;
				exit();
			}
		}

		foreach($resultado_final_st_igh as $rf_st){
			$command_zona = "SELECT id FROM tbl_zonas WHERE nombre = '".$rf_st[0]."'";
			$result_zona = $mysqli->query($command_zona);
			if ($result_zona) {
				if ($result_zona->num_rows > 0) {
					$list_zona = $result_zona->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de las zonas en IGH - Servicio Técnico.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}

			$command_razon_social = "SELECT id FROM tbl_razon_social WHERE nombre = '".$rf_st[2]."'";
			$result_razon_social = $mysqli->query($command_razon_social);
			if ($result_razon_social) {
				if ($result_razon_social->num_rows > 0) {
					$list_razon_social = $result_razon_social->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de las razones sociales en IGH - Servicio Técnico.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}

			$zona_id = $list_zona["id"];
			$zona = $rf_st[0];
			$provincia = $rf_st[1];
			$valor_v = $rf_st[6];
			$valor_e = $rf_st[7];
			$razon_social_id = $list_razon_social["id"];
			$razon_social = $rf_st[2];
			$tipo = "servicio tecnico";
			$estado = 1;

			$command_equipo = "SELECT id FROM tbl_servicio_tecnico_equipo WHERE nombre = '".$rf_st[3]."'";
			$result_equipo = $mysqli->query($command_equipo);
			if ($result_equipo) {
				if ($result_equipo->num_rows > 0) {
					$list_equipo = $result_equipo->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de los equipos en IGH - Servicio Técnico.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}
			
			$equipo_id = $list_equipo["id"];
			$equipo = $rf_st[3];
			$t_revision = $rf_st[4];
			$t_coordinacion = $rf_st[5];

			$command_st_zona = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_zona`
			(
				razon_social_id
				,razon_social
				,zona_id
				,zona
				,provincia
				,valor_v
				,valor_e
				,tipo
				,estado
			)
				VALUES
			(
				$razon_social_id
				,'".$razon_social."'
				,$zona_id
				,'".$zona."'
				,'".$provincia."'
				,$valor_v
				,$valor_e
				,'".$tipo."'
				,$estado
			)";

			$return["command_st_zona_igh"] = $command_st_zona;
			$mysqli->query($command_st_zona);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command_st_zona;
				exit();
			}

			$solicitud_estimacion_zona_id = $mysqli->insert_id;

			$command_st = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_servicio_tecnico`
			(
				equipo_id
				,equipo
				,t_revision
				,t_coordinacion
				,solicitud_estimacion_zona_id
			)
				VALUES
			(
				$equipo_id
				,'".$equipo."'
				,$t_revision
				,$t_coordinacion
				,$solicitud_estimacion_zona_id
			)";

			$return["command_st_igh"] = $command_st;
			$mysqli->query($command_st);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command_st;
				exit();
			}
		}

		foreach($resultado_final_m_fg as $rf_m){
			$command_zona = "SELECT id FROM tbl_zonas WHERE nombre = '".$rf_m[0]."'";
			$list_zona = $mysqli->query($command_zona)->fetch_assoc();

			$command_razon_social = "SELECT id FROM tbl_razon_social WHERE nombre = '".$rf_m[2]."'";
			$list_razon_social = $mysqli->query($command_razon_social)->fetch_assoc();

			$zona_id = $list_zona["id"];
			$zona = $rf_m[0];
			$provincia = $rf_m[1];
			$valor_v = $rf_m[6];
			$valor_e = $rf_m[7];
			$razon_social_id = $list_razon_social["id"];
			$razon_social = $rf_m[2];
			$tipo = "mantenimiento";
			$estado = 1;

			$command_sistema = "SELECT id FROM tbl_solicitud_mantenimiento_sistema WHERE nombre = '".$rf_m[3]."'";
			$result_sistema = $mysqli->query($command_sistema);
			if ($result_sistema) {
				if ($result_sistema->num_rows > 0) {
					$list_sistema = $result_sistema->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de los sistemas en FREE GAMES - Mantenimiento.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}
			
			$sistema_id = $list_sistema["id"];
			$sistema = $rf_m[3];
			$t_revision = $rf_m[4];
			$t_coordinacion = $rf_m[5];

			$command_m_zona = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_zona`
			(
				razon_social_id
				,razon_social
				,zona_id
				,zona
				,provincia
				,valor_v
				,valor_e
				,tipo
				,estado
			)
				VALUES
			(
				$razon_social_id
				,'".$razon_social."'
				,$zona_id
				,'".$zona."'
				,'".$provincia."'
				,$valor_v
				,$valor_e
				,'".$tipo."'
				,$estado
			)";

			$return["command_m_zona_fg"] = $command_m_zona;
			$mysqli->query($command_m_zona);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command_m_zona;
				exit();
			}

			$solicitud_estimacion_zona_id = $mysqli->insert_id;

			$command_m = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_mantenimiento`
			(
				sistema_id
				,sistema
				,t_revision
				,t_coordinacion
				,solicitud_estimacion_zona_id
			)
				VALUES
			(
				$sistema_id
				,'".$sistema."'
				,$t_revision
				,$t_coordinacion
				,$solicitud_estimacion_zona_id
			)";

			$return["command_m_fg"] = $command_m;
			$mysqli->query($command_m);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command_m;
				exit();
			}
		}

		foreach($resultado_final_m_igh as $rf_m){
			$command_zona = "SELECT id FROM tbl_zonas WHERE nombre = '".$rf_m[0]."'";
			$result_zona = $mysqli->query($command_zona);
			if ($result_zona) {
				if ($result_zona->num_rows > 0) {
					$list_zona = $result_zona->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de las zonas en IGH - Mantenimiento.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}

			$command_razon_social = "SELECT id FROM tbl_razon_social WHERE nombre = '".$rf_m[2]."'";
			$result_razon_social = $mysqli->query($command_razon_social);
			if ($result_razon_social) {
				if ($result_razon_social->num_rows > 0) {
					$list_razon_social = $result_razon_social->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de las razones sociales en IGH - Mantenimiento.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}

			$zona_id = $list_zona["id"];
			$zona = $rf_m[0];
			$provincia = $rf_m[1];
			$valor_v = $rf_m[6];
			$valor_e = $rf_m[7];
			$razon_social_id = $list_razon_social["id"];
			$razon_social = $rf_m[2];
			$tipo = "mantenimiento";
			$estado = 1;

			$command_sistema = "SELECT id FROM tbl_solicitud_mantenimiento_sistema WHERE nombre = '".$rf_m[3]."'";
			$result_sistema = $mysqli->query($command_sistema);
			if ($result_sistema) {
				if ($result_sistema->num_rows > 0) {
					$list_sistema = $result_sistema->fetch_assoc();
				} else {
					$return["error_formato"] = "Revisa bien el nombre de los sistemas en IGH - Mantenimiento.";
					die(json_encode($return));
				}
			} else {
				$return["error_query"] = $mysqli->error;
				die(json_encode($return));
			}
			
			$sistema_id = $list_sistema["id"];
			$sistema = $rf_m[3];
			$t_revision = $rf_m[4];
			$t_coordinacion = $rf_m[5];

			$command_m_zona = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_zona`
			(
				razon_social_id
				,razon_social
				,zona_id
				,zona
				,provincia
				,valor_v
				,valor_e
				,tipo
				,estado
			)
				VALUES
			(
				$razon_social_id
				,'".$razon_social."'
				,$zona_id
				,'".$zona."'
				,'".$provincia."'
				,$valor_v
				,$valor_e
				,'".$tipo."'
				,$estado
			)";

			$return["command_m_zona_igh"] = $command_m_zona;
			$mysqli->query($command_m_zona);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command_m_zona;
				exit();
			}

			$solicitud_estimacion_zona_id = $mysqli->insert_id;

			$command_m = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_solicitud_estimacion_mantenimiento`
			(
				sistema_id
				,sistema
				,t_revision
				,t_coordinacion
				,solicitud_estimacion_zona_id
			)
				VALUES
			(
				$sistema_id
				,'".$sistema."'
				,$t_revision
				,$t_coordinacion
				,$solicitud_estimacion_zona_id
			)";

			$return["command_m_igh"] = $command_m;
			$mysqli->query($command_m);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command_m;
				exit();
			}
		}
		$return["success"] = "exito";
	} else {
		$return["error"] = "error archivo excel";
		$return["error_msg"] = "Hubo un problema con el archivo excel.";
		// echo "Error al cargar el archivo.";
	}
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));

?>