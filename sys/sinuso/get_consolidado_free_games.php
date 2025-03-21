<?php
include("db_connect.php");
include("sys_login.php");

global $login;
global $mysqli;

if ($_POST["opt"] === 'get_supervisores_by_locales') {
    $result = [];

    $local_ids = $_POST['local_ids'];
    $search_function = "";
    if (!in_array(strtolower('all'), $local_ids, true)) {
        $result = getAllSupervisoresByLocales($local_ids);
    } else {
        $zona_ids = $_POST['zona_ids'];
        if (!in_array(strtolower('all'), $zona_ids, true)) {
            $result = getAllSupervisoresByZonas($zona_ids);
        } else {
            $razon_social_id = (int)$_POST['razon_social_id'];
            $result = getAllSupervisoresByRazonSocial($razon_social_id);
        }
    }

    print_r(json_encode($result));
}

if ($_POST["opt"] === "get_zonas_by_razon_social") {
    $razon_social_id = $_POST['razon_social_id'];
    $result = getZonasByRazonSocial($razon_social_id);
    print_r(json_encode($result));
}

if ($_POST["opt"] === "get_locales_by_zonas") {
    $result = [];
    $zona_ids = $_POST['zona_ids'];
    if (!in_array(strtolower('all'), $zona_ids, true)) {
        $result = getLocalesByZonas($zona_ids);
    } else if (isset($_POST['razon_social_id'])) {
        $razon_social_id = $_POST['razon_social_id'];
        $result = getLocalesByRazonSocial($razon_social_id);
    }

    print_r(json_encode($result));
}

if ($_POST["opt"] === "consolidado_free_games") {
    $fecha_inicio = '2021-01-01';

    $meses = [];

    $date1 = $fecha_inicio;
    $date2 = date("Y-m-d");
    $time = strtotime($date1);
    $last = date('Y-m', strtotime($date2));
    $canales_de_venta = [16, 17, 21, 30, 34];

    do {
        $month = date('Y-m', $time);
        $total = date('t', $time);
        $meses[] = $month;
        $time = strtotime('+1 month', $time);
    } while ($month != $last);

    $data = [];

    $cabecera_where = "AND c.canal_de_venta_id in (" . implode(",", $canales_de_venta) . ")";

    $concepto = "APOSTADO";

    if (isset($_POST["concepto"]) && !empty($_POST["concepto"])) {
        $concepto = $_POST["concepto"];
    }

    $razon_social_id = 5;

    if (isset($_POST["razon_social_id"]) && !empty($_POST["razon_social_id"])) {
        $razon_social_id = $_POST["razon_social_id"];
    }

    $locales_command_where = " AND l.id IS NOT NULL ";

    $zonas_command_where = " AND z.razon_social_id = " . $razon_social_id;

    if (
        isset($_POST['zonas']) &&
        !empty($_POST['zonas']) &&
        is_array($_POST['zonas']) &&
        !in_array('all', $_POST['zonas'], true)) {

        $zonas_command_where .= " AND z.id IN (" . implode(",", $_POST['zonas']) . ")";
    } else {
        $zonas_command_where .= " AND (z.status = 1 OR z.id = 12)";
    }

    $supervisores_command_where = "";

    if (
        isset($_POST['supervisores']) &&
        !empty($_POST['supervisores']) &&
        is_array($_POST['supervisores']) &&
        !in_array('all', $_POST['supervisores'], true)) {

        $supervisores_command_where = " AND (SELECT pa.id as personal_id 
												FROM tbl_usuarios_locales ul 
												inner join tbl_usuarios u ON ul.usuario_id = u.id 
												INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4
												WHERE 
												pa.area_id = 21 
												AND pa.estado = 1 
												AND ul.estado = 1 
												AND ul.local_id = l.id 
												LIMIT 1) 
							IN ('" . implode("','", $_POST['supervisores']) . "') ";
    }

    if (
        isset($_POST['locales']) &&
        !empty($_POST['locales']) &&
        is_array($_POST['locales']) &&
        !in_array('all', $_POST['locales'], true)) {
        $cabecera_where .= " AND local_id IN ('" . implode("','", $_POST['locales']) . "')";
        $locales_command_where .= " AND l.id IN ('" . implode("','", $_POST['locales']) . "')";
    }

    /*if ($login["usuario_locales"]) {
        $cabecera_where .= " AND local_id IN ('" . implode("','", $login["usuario_locales"]) . "')";
        $locales_command_where .= " AND l.id IN ('" . implode("','", $login["usuario_locales"]) . "')";
    }*/

    if (isset($_POST['estado_locales']) &&
        !empty($_POST['estado_locales'])) {
        if ($_POST["estado_locales"] === "activos") {
            $locales_command_where .= " AND l.operativo = 1";
        } else {
            $locales_command_where .= " AND l.operativo = 2";
        }
    }

    if (
        isset($_POST['canales_de_venta']) &&
        !empty($_POST['canales_de_venta']) &&
        is_array($_POST['canales_de_venta'])) {

        if (!in_array("all", $_POST['canales_de_venta'], true)) {
            $cabecera_where .= " AND c.canal_de_venta_id IN ('" . implode("','", $_POST['canales_de_venta']) . "')";
        } else {
            $cabecera_where .= " AND c.canal_de_venta_id IN (" . implode(",", $canales_de_venta) . ")";
        }
    }

    $cabecera_where .= " AND c.estado = '1'";

    $locales = []; //LOCALES

    $locales_command = "SELECT
			l.id as local_id
			,l.nombre AS 'NOMBRE TIENDA'
			,(SELECT 
					CONCAT(IFNULL(pa.nombre,'Sin supervisor asignado.'), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS supervisor_nombre
				FROM tbl_usuarios_locales ul 
				inner join tbl_usuarios u ON ul.usuario_id = u.id
				INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
				WHERE	
				pa.area_id = 21 
				AND pa.estado = 1
				AND ul.estado = 1 
				AND ul.local_id = l.id  LIMIT 1
			) AS 'NOMBRE SOP'
			,udep.nombre as DEPARTAMENTO
			,up.nombre as PROVINCIA
			,ud.nombre as DISTRITO
			,zdep.nombre as ZONA_DEPARTAMENTO
			,z.nombre as ZONA_NOMBRE
		FROM tbl_locales l
		LEFT JOIN tbl_ubigeo ud ON (
			ud.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			ud.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
			ud.cod_dist = SUBSTRING(l.ubigeo_id, 5, 2)
		)
		LEFT JOIN tbl_ubigeo up ON (
			up.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			up.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
			up.cod_dist = '00'
		)
		LEFT JOIN tbl_ubigeo udep ON (
			udep.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			udep.cod_prov = '00' AND
			udep.cod_dist = '00'
		)
		LEFT JOIN tbl_ubigeo_departamentos udep2 ON udep2.nombre = udep.nombre COLLATE utf8_unicode_ci
        LEFT JOIN tbl_zonas_departamento zdep ON zdep.id = udep2.zonas_departamento_id
		INNER JOIN tbl_zonas z ON z.id = l.zona_id
		WHERE 1 = 1 ";

    $locales_command .= $locales_command_where . $supervisores_command_where .  $zonas_command_where. ' ORDER BY z.nombre';

    $locales_query = $mysqli->query($locales_command);

    if (!empty($mysqli->error)) {
        $return = [
            "error" => $mysqli->error,
            "query" => $locales_command,
        ];
        print_r($return);
        exit;
    }

    $canales_where = " AND id IN (" . implode(",", $canales_de_venta) . ")";

    if (
        isset($_POST['canales_de_venta']) &&
        !empty($_POST['canales_de_venta']) &&
        is_array($_POST['canales_de_venta'])) {
        if (!in_array("all", $_POST["canales_de_venta"], true)) {
            $canales_where .= " AND id IN ('" . implode("','", $_POST["canales_de_venta"]) . "')";
        }
    }

    $cdv_arr = []; // CANALES DE VENTA
    $cdv_command = "SELECT id, nombre, codigo FROM tbl_canales_venta WHERE estado = '1' $canales_where ORDER BY id";
    $cdv_query = $mysqli->query($cdv_command);

    if (!empty($mysqli->error)) {
        $return = [
            "error" => $mysqli->error,
            "query" => $cdv_command,
        ];
        print_r($return);
        exit;
    }

    while ($cdv = $cdv_query->fetch_assoc()) {
        $cdv_arr[$cdv["id"]] = $cdv;
    }

    $locales2 = [];
    while ($lcl = $locales_query->fetch_assoc()) {
        $locales2[] = $lcl;
        foreach ($cdv_arr as $cdv_id => $cdv) {
            $lcl["CANAL DE VENTA"] = $cdv["codigo"];
            $lcl["canal_de_venta_id"] = $cdv["id"];
            foreach ($meses as $mes) {
                $lcl[$mes] = 0;
            }
            $locales[] = $lcl;
        }
        $lcl["CANAL DE VENTA"] = "TOTAL";
        $locales[] = $lcl;
    }

    $trans_command = "
				SELECT 
					c.local_id
					,l.nombre as local_nombre
					,c.canal_de_venta_id
					,DATE_FORMAT(c.fecha,'%Y-%m') AS mes
					,sum(c.total_apostado) AS 'APOSTADO'
					,sum(c.total_ganado) AS 'GANADO'
					,sum(c.total_pagado) AS 'PAGADO'
					,sum(c.resultado_negocio) AS 'GGR'
					,sum(c.num_tickets) AS 'CANTIDAD DE TICKETS'
				FROM tbl_transacciones_cabecera c
				LEFT JOIN tbl_locales l ON l.id = c.local_id
				LEFT JOIN tbl_zonas z ON z.id = l.zona_id
				WHERE 
				c.id IS NOT NULL AND 
				c.estado = 1 
				AND l.estado = 1
				AND c.fecha >= '{$fecha_inicio}'  AND c.fecha <= now()
				AND l.reportes_mostrar = 1
				AND c.canal_de_venta_id in (" . implode(",", $canales_de_venta) . ") 
				{$locales_command_where} 
				{$zonas_command_where} 
				{$cabecera_where} 
				GROUP BY DATE_FORMAT(c.fecha,'%Y-%m') ,c.local_id ,c.canal_de_venta_id
				ORDER BY z.nombre, c.fecha ASC ,local_nombre , c.canal_de_venta_id";
    $transacciones_query = $mysqli->query($trans_command);
    if (!empty($mysqli->error)) {
        $return = [
            "error" => $mysqli->error,
            "query" => $trans_command,
        ];
        print_r($return);
        exit;
    }
    $locales_transacciones = [];
    while ($lcl = $transacciones_query->fetch_assoc()) {
        $locales_transacciones[$lcl["local_id"]][$lcl["mes"]][$lcl["canal_de_venta_id"]] = $lcl;
    }

    foreach ($locales2 as $id => $data) {
        $locales2[$id]["liquidaciones"] = isset($locales_transacciones[$data["local_id"]]) ? $locales_transacciones[$data["local_id"]] : [];
    }
    /*sum TOTAL local*/
    foreach ($locales2 as $id => $data) {
        foreach ($data["liquidaciones"] as $key_fecha => $canales) {
            $total = 0;
            $valores_fila = [];
            foreach ($canales as $key => $value) {
                $total += $value[$concepto];
                $valores_fila = $value;
            }
            $valores_fila[$concepto] = $total;
            $valores_fila["canal_de_venta_id"] = "TOTAL";
            $locales2[$id]["liquidaciones"][$key_fecha]["TOTAL"] = $valores_fila;
        }
    }

    $totales2 = [];
    foreach ($meses as $mes) {
        $totales2[$mes][$concepto] = 0;
    }
    foreach ($locales2 as $local_id => $local_data) {
        foreach ($data["liquidaciones"] as $key_mes => $canales) {
            foreach ($canales as $key_canal => $value) {
                $totales2[$key_mes][$concepto] += $value[$concepto];
            }
        }
    }
    //echo "<pre>";print_r($locales2);echo "<pre>";die();
    //echo "<pre>";print_r($totales2);echo "<pre>";die();
    $array_datatable = [];
    foreach ($locales2 as $id => $data_local) {
        foreach ($cdv_arr as $cdv_id => $cdv) /*fill local with cdvs*/ {
            $objeto =
                [
                    "NOMBRE TIENDA" => $data_local["NOMBRE TIENDA"],
                    "NOMBRE SOP" => $data_local["NOMBRE SOP"],
                    "DEPARTAMENTO" => $data_local["DEPARTAMENTO"],
                    "PROVINCIA" => $data_local["PROVINCIA"],
                    "DISTRITO" => $data_local["DISTRITO"],
                    "ZONA" => $data_local["ZONA_NOMBRE"],
                    "CANAL DE VENTA" => $cdv["codigo"],
                ];
            foreach ($meses as $mes)//add months columns
            {
                //add month  value
                $valor_mes = 0;
                if (isset($data_local["liquidaciones"])) {
                    if (isset($data_local["liquidaciones"][$mes])) {
                        if (isset($data_local["liquidaciones"][$mes][$cdv["id"]])) {
                            if (isset($data_local["liquidaciones"][$mes][$cdv["id"]][$concepto])) {
                                $valor_mes = $data_local["liquidaciones"][$mes][$cdv["id"]][$concepto];
                            }
                        }
                    }
                }
                $objeto[$mes] = $valor_mes;
            }
            $array_datatable[] = $objeto;
        }

        $objeto =
            [
                "NOMBRE TIENDA" => $data_local["NOMBRE TIENDA"],
                "NOMBRE SOP" => $data_local["NOMBRE SOP"],
                "DEPARTAMENTO" => $data_local["DEPARTAMENTO"],
                "PROVINCIA" => $data_local["PROVINCIA"],
                "DISTRITO" => $data_local["DISTRITO"],
                "ZONA" => $data_local["ZONA_NOMBRE"],
                "CANAL DE VENTA" => "TOTAL",
            ];
        foreach ($meses as $mes) {//add month  value
            $valor_mes = 0;
            if (isset($data_local["liquidaciones"])) {
                if (isset($data_local["liquidaciones"][$mes])) {
                    if (isset($data_local["liquidaciones"][$mes]["TOTAL"])) {
                        if (isset($data_local["liquidaciones"][$mes]["TOTAL"][$concepto])) {
                            $valor_mes = $data_local["liquidaciones"][$mes]["TOTAL"][$concepto];
                        }
                    }
                }
            }
            $objeto[$mes] = $valor_mes;
        }
        $array_datatable[] = $objeto;

    }

    $totales = [];
    $data_return["datatable_data"] = $array_datatable;
    $data_return["meses"] = $meses;
    $data_return["totales"] = $totales2;
    $return["data"] = $data_return;
    print_r(json_encode($return));
}

function getAllSupervisoresByLocales(array $local_ids): array
{
    global $mysqli;
    //global $login;

    $str_local_ids = implode(',', $local_ids);

    $query = "SELECT 
			u.id as usuario_id,
			pa.id as personal_id,
			pa.area_id,
			pa.cargo_id,
			CONCAT(IFNULL(pa.nombre,'Sin supervisor asignado.'), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS nombre,
			IFNULL(pa.correo, '') as correo
		FROM tbl_usuarios_locales ul 
		INNER JOIN tbl_usuarios u ON ul.usuario_id = u.id
		INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
		INNER JOIN tbl_locales l ON ul.local_id = l.id
		INNER JOIN tbl_zonas z ON z.id = l.zona_id
		WHERE	
		pa.area_id = 21 
		AND pa.estado = 1
		AND ul.estado = 1
		AND l.id IN ({$str_local_ids}) 
		GROUP BY u.id
		ORDER BY nombre;";
    /*
     -- AND l.id IN ('".implode("','", $login["usuario_locales"])."')
     * */

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => $mysqli->error,
            "query" => $query,
        ];
    }
    $data_return['data'] = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return['data'][] = $r;
    }
    return $data_return;
}

function getAllSupervisoresByZonas(array $zona_ids): array
{
    global $mysqli;
    //global $login;

    $str_zona_ids = implode(',', $zona_ids);
    $query = "SELECT 
			u.id as usuario_id,
			pa.id as personal_id,
			pa.area_id,
			pa.cargo_id,
			CONCAT(IFNULL(pa.nombre,'Sin supervisor asignado.'), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS nombre,
			IFNULL(pa.correo, '') as correo
		FROM tbl_usuarios_locales ul 
		INNER JOIN tbl_usuarios u ON ul.usuario_id = u.id
		INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
		INNER JOIN tbl_locales l ON ul.local_id = l.id
		INNER JOIN tbl_zonas z ON z.id = l.zona_id
		WHERE	
		pa.area_id = 21 
		AND pa.estado = 1
		AND ul.estado = 1
		AND z.id IN ({$str_zona_ids})
		GROUP BY u.id
		ORDER BY nombre;";
    /*
     -- AND l.id IN ('".implode("','", $login["usuario_locales"])."')
     * */

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => $mysqli->error,
            "query" => $query,
        ];
    }

    $data_return['data'] = array();

    while ($r = $result_query->fetch_assoc()) {
        $data_return['data'][] = $r;
    }

    return $data_return;
}

function getAllSupervisoresByRazonSocial(int $razon_social_id): array
{
    global $mysqli;
    //global $login;

    $query = "SELECT 
			u.id as usuario_id,
			pa.id as personal_id,
			pa.area_id,
			pa.cargo_id,
			CONCAT(IFNULL(pa.nombre,'Sin supervisor asignado.'), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS nombre,
			IFNULL(pa.correo, '') as correo
		FROM tbl_usuarios_locales ul 
		INNER JOIN tbl_usuarios u ON ul.usuario_id = u.id
		INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
		INNER JOIN tbl_locales l ON ul.local_id = l.id
		INNER JOIN tbl_zonas z ON z.id = l.zona_id
		WHERE	
		pa.area_id = 21 
		AND pa.estado = 1
		AND ul.estado = 1
		AND (z.status = 1 OR z.id = 12)   
		AND z.razon_social_id = {$razon_social_id}
		GROUP BY u.id
		ORDER BY nombre;";
    /*
     -- AND l.id IN ('".implode("','", $login["usuario_locales"])."')
     * */

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => $mysqli->error,
            "query" => $query,
        ];
    }
    $data_return['data'] = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return['data'][] = $r;
    }
    return $data_return;
}

function getZonasByRazonSocial(int $razon_social_id): array
{
    global $mysqli;
    $query = "SELECT id, nombre FROM tbl_zonas WHERE (status = 1 OR id = 12) AND razon_social_id = {$razon_social_id} ORDER BY nombre;";
    $result_query = $mysqli->query($query);
    if (!empty($mysqli->error)) {
        return [
            "error" => $mysqli->error,
            "query" => $query,
        ];
    }
    $data_return['data'] = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return['data'][] = $r;
    }
    return $data_return;
}

function getLocalesByZonas(array $zona_ids): array
{
    global $mysqli;
    //global $login;

    $str_zona_ids = implode(',', $zona_ids);
    $query = "SELECT l.id, l.nombre
				FROM tbl_locales l
				INNER JOIN tbl_zonas z on z.id = l.zona_id
				WHERE	
					z.id IN ({$str_zona_ids}) ORDER BY z.nombre";
    //. " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")";

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => $mysqli->error,
            "query" => $query,
        ];
    }

    $data_return['data'] = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return['data'][] = $r;
    }
    return $data_return;
}

function getLocalesByRazonSocial(int $razon_social_id): array
{
    global $mysqli;
    //global $login;

    $query = "SELECT l.id, l.nombre
				FROM tbl_locales l
				INNER JOIN tbl_zonas z on z.id = l.zona_id
				WHERE
				    (z.status = 1 OR z.id = 12) AND
					z.razon_social_id = {$razon_social_id} ORDER BY z.nombre";
    //. " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")";

    $result_query = $mysqli->query($query);

    if (!empty($mysqli->error)) {
        return [
            "error" => $mysqli->error,
            "query" => $query,
        ];
    }

    $data_return['data'] = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return['data'][] = $r;
    }
    return $data_return;
}

?>