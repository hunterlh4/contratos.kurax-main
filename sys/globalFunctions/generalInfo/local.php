<?php

/*
Obtiene la información del jefe comercial de un local
INPUT: id del local
OUPUT: Jefe comercial, correo de jefe comercial
*/
function getJefeComercial($local_id)
{
    global $mysqli;
    $query = "
	SELECT
		l.cc_id,
		CONCAT(IFNULL(p.nombre,'Sin jefe de operaciones asignado.'), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS jefe_comercial_nombre,
		IFNULL(MAX(p.correo), '') as jefe_comercial_correo
	FROM tbl_locales l
	INNER JOIN tbl_zonas z ON l.zona_id = z.id 
	INNER JOIN tbl_personal_apt p ON (
		p.id = z.jop_id
		AND p.area_id = 21
		AND p.estado = 1
	)
	WHERE l.id = $local_id
	";

    $result_query = $mysqli->query($query);
    $data_return = [];
    while ($r = $result_query->fetch_assoc()) {
        $data_return = $r;
    }
    return $data_return;
}

/*
Obtiene la información de los supervisores local
INPUT: id del local
OUPUT: Supervisores -> nombre y correo
*/
function getSupervisores($local_id)
{
    global $mysqli;
    $query = "
	SELECT 
		u.id,
		CONCAT(IFNULL(psop.nombre,'Sin supervisor asignado.'), ' ', IFNULL(psop.apellido_paterno, ''), ' ', IFNULL(psop.apellido_materno, '')) AS supervisor_nombre,
		IFNULL(psop.correo, '') as supervisor_correo
		FROM tbl_personal_apt psop
		INNER JOIN tbl_usuarios u ON psop.id = u.personal_id AND u.estado = 1
		LEFT JOIN tbl_usuarios_locales ul ON u.id = ul.usuario_id
	WHERE	 psop.area_id = 21
	AND psop.cargo_id = 4
	AND psop.estado = 1
	AND ul.local_id =  $local_id
	GROUP BY u.id
	";

    $result_query = $mysqli->query($query);
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}

/*
Obtiene la información de los cajeros de locales
INPUT: id del local
OUPUT: (Array)Cajeros -> nombre y correo
*/
function getPersonales($locales_ids = [], $cargos = [], $permisos = true)
{
    global $mysqli;
    global $login;
    $permisos_locales = '';
    if(count($login["usuario_locales"]) && $permisos){
        $permisos_locales = " AND ul.local_id IN (" . implode(",", $login["usuario_locales"]) . ") ";
    }
    $filtro_locales   = "";
    if(!empty($locales_ids)){
        $filtro_locales   = " AND ul.local_id IN (" . implode(",", $locales_ids) . ") ";
    }
    $filtro_cargos   = "";
    if(!empty($cargos)){
        $filtro_cargos    = " AND pa.cargo_id IN (" . implode(",", $cargos) . ") ";
    }
    $query = "  SELECT 
                    u.id,
                    CONCAT(IFNULL(pa.nombre,'Sin cajero asignado.'), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS personal_nombre,
                    IFNULL(pa.correo, '') as supervisor_correo
                FROM tbl_personal_apt pa
                INNER JOIN tbl_usuarios u ON pa.id = u.personal_id AND u.estado = 1
                LEFT JOIN tbl_usuarios_locales ul ON u.id = ul.usuario_id
                WHERE	 pa.area_id = 21
                    AND pa.estado = 1
                    $filtro_cargos
                    $filtro_locales
                    $permisos_locales
                GROUP BY u.id
	";

    $result_query = $mysqli->query($query);
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}

/*
Obtiene todos los supervisores de los locales de una zona
INPUT: id de la zona
OUPUT: Supervisores -> nombre y correo
*/
function getAllSupervisoresByZona($zona_id)
{
    global $mysqli;
    global $login;
    $query = "SELECT 
			u.id as usuario_id,
			pa.id as personal_id,
			pa.area_id,
			pa.cargo_id,
			CONCAT(IFNULL(pa.nombre,'Sin supervisor asignado.'), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS supervisor_nombre,
			IFNULL(pa.correo, '') as supervisor_correo
		FROM tbl_usuarios_locales ul 
		inner join tbl_usuarios u ON ul.usuario_id = u.id
		INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
		INNER JOIN tbl_locales l ON ul.local_id = l.id
		WHERE	
		pa.area_id = 21 
		AND pa.estado = 1
		and ul.estado = 1 
		AND l.zona_id = $zona_id
		AND l.id IN ('".implode("','", $login["usuario_locales"])."')
		GROUP BY u.id
		ORDER BY supervisor_nombre
	";

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    }
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}

/*
Obtiene todos los locales activos de una zona
INPUT: id de la zona
OUPUT: Array con locales
*/
function getLocalesByZona($zona_id)
{
    global $mysqli;
    global $login;

    $permisos_locales = "";
    if($login && $login["usuario_locales"]){
        $permisos_locales = " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
    }

    $where_zona = "";
    if ($zona_id != "_all_") {
        $where_zona = " AND l.zona_id = ".$zona_id;
    }

    $query = "	SELECT l.id, CONCAT('[',IFNULL(l.cc_id,''),'] ', IFNULL(l.nombre,'')) as nombre
				FROM tbl_locales l
				INNER JOIN tbl_zonas as z ON z.id = l.zona_id
				WHERE z.status = 1 AND l.estado = 1
				$where_zona
				$permisos_locales
				ORDER BY l.nombre ASC
	";

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    }
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}

/*
Obtiene todos los locales activos de una red
INPUT: id de la red
OUPUT: Array con locales
*/
function getLocalesByRed($red_id, $permisos = true)
{
    global $mysqli;
    global $login;

    $permisos_locales = "";
    if($login && $login["usuario_locales"] && $permisos){
        $permisos_locales = " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
    }

    $where_red = "";
    if ($red_id != "_all_") {
        $where_red = " AND l.red_id = ".$red_id;
    }

    $query = "	SELECT 
                    l.id, l.nombre
				FROM tbl_locales l
				    INNER JOIN tbl_locales_redes as r ON r.id = l.red_id
				WHERE l.estado = 1
				$where_red
				$permisos_locales
				ORDER BY l.nombre ASC
	";

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    }
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}

/*
Obtiene todos los locales activos de varias redes
// se comentó el permiso de locales por acuerdo con Kathia Guillermo
INPUT: Array de las redes solicitadas
OUPUT: Array con locales
*/
function getLocalesByRedesSinPermisos($redes)
{
    global $mysqli;
    global $login;

    // $permisos_locales = "";
    // if($login && $login["usuario_locales"]){
    //     $permisos_locales = " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
    // }

    $where_red = "";
    if ((count($redes))) {
        $where_red = " AND l.red_id in (". implode(',', $redes) .")";
    }

    $query = "	SELECT 
                    l.id, l.nombre
				FROM tbl_locales l
				    INNER JOIN tbl_locales_redes as r ON r.id = l.red_id
				WHERE l.estado = 1
				$where_red
				ORDER BY l.nombre ASC
	";

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    }
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}

/*
Obtiene la Configuración Web del Local
INPUT: id del local, columna de tbl_saldo_web_config
OUPUT: (0,1)columna de tbl_saldo_web_config
*/
function getLocalWebConfig($local_id, $web_config)
{
    global $mysqli;
    $query = "SELECT $web_config FROM tbl_locales_web_config where local_id = $local_id LIMIT 1";
    $result = empty($mysqli->query($query)->fetch_assoc()[$web_config]) ? 0 : 1;
    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    }
    return $result;
}

/*
Obtiene la última activación del agente_can_deposit (tbl_locales_web_config)
INPUT: id del local
OUPUT: (datetime)fecha de la última activación del agente_can_deposit del local
*/
function getLastActivacionSaldoWeb($local_id)
{
    global $mysqli;
    $query = "SELECT created_at FROM tbl_saldo_web_activaciones where local_id = $local_id ORDER BY created_at DESC LIMIT 1";
    $result = $mysqli->query($query)->fetch_assoc()['created_at'];
    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    }
    return $result;
}

/*
Obtiene el monto del local recargado desde la hora de la ultima activación
INPUT: cc_id del local, fecha de la ultima activación
OUPUT: (float)suma del campo monto filtrado por fecha y cc_id
*/
function getMontoLocalFromLastActivacion($cc_id, $created_at)
{
    global $mysqli;
    $query = "SELECT IFNULL(sum(monto),0) AS sum_monto FROM tbl_saldo_web_transaccion t WHERE t.status = 1 AND t.cc_id = $cc_id AND t.created_at > '$created_at'";
    $list_query = $mysqli->query($query);

    $result = 0;

    if ($mysqli->error) {
        return $mysqli->error;
    } else {
        $sum_monto = array();
        while ($li = $list_query->fetch_assoc()) {
            $sum_monto[] = $li;
        }
        $result = strval($sum_monto[0]['sum_monto']);
    }

    return $result;
}


/*
BLOQUEAR PERMISO agente_can_deposit
INPUT: local_id
OUTPUT: bolean
*/
function lockAgenteCanDeposit($local_id)
{
    global $mysqli;
    $update_command = "
	UPDATE tbl_locales_web_config
	SET
		agente_can_deposit = 0
	WHERE 
		local_id = $local_id
		";

    $mysqli->query($update_command);
    if ($mysqli->error) {
        $result["error"] = "mysql";
        $result["mysqli_error"] = $mysqli->error;
        $result["query"] = $update_command;
        return $result;
    }

    return true;
}

/*
Obtener el credito disponible para recargas de un local
INPUT: local_id, cc_id
OUTPUT: (double)credito
*/
function getCreditoLocal($local_id, $cc_id)
{
    $credito = 0;
    $monto_max = getParameterGeneral('monto_max_recarga_web');
    $credito = $monto_max;

    $created_at = getLastActivacionSaldoWeb($local_id);
    $monto_local = getMontoLocalFromLastActivacion($cc_id, $created_at);

    $credito = $monto_max - $monto_local;

    return $credito;
}

/*
Obtener el id de un local
INPUT: cc_id del local
OUTPUT: id del local
*/
function getLocalIdfromCCid($cc_id)
{
    global $mysqli;
    $query = "SELECT id FROM tbl_locales where cc_id = $cc_id LIMIT 1";
    $result = false;
    if (!empty($mysqli->error)) {
        return [
            "error" => "mysql",
            "mysqli_error" => $mysqli->error,
            "query" => $query,
        ];
    } else {
        $result = $mysqli->query($query)->fetch_assoc()['id'];
    }
    return $result;
}

function getLocalNombre($local_id)
{
    global $mysqli;
    $query = " SELECT l.id, l.cc_id, l.nombre, lwc.agente_can_deposit FROM 
				tbl_locales  l
				inner join tbl_locales_web_config lwc ON lwc.local_id = l.id
				WHERE id = '" . $local_id . "'";

    $list_query = $mysqli->query($query);
    if ($mysqli->error) {
        $result = $mysqli->error;
    } else {
        $list_locales = array();
        while ($li = $list_query->fetch_assoc()) {
            $list_locales[] = $li;
        }
        if (count($list_locales) == 1) {
            $result = $list_locales[0];
        }
    }

    return $result;
}