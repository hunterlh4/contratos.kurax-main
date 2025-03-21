<?php
include("db_connect.php");
include("sys_login.php");

if($_POST["opt"] == "get_agentes"){
    $array = [];
	$command = "SELECT cli.nombre,local_id
				FROM tbl_contratos c
				LEFT JOIN tbl_clientes cli ON cli.id = c.cliente_id
                LEFT JOIN tbl_locales l ON l.id = c.local_id
				WHERE c.estado = 1 
                AND l.red_id = 5
                AND l.estado = 1
                GROUP BY cli.id";
	$cm_query = $mysqli->query($command);
	while($row = $cm_query->fetch_assoc()){
		$array[] = $row;
	}
	$return["listado"] = $array;
	print_r(json_encode($return));
}
if($_POST["opt"] == "consolidado_agentes"){
	$fecha_inicio = '2021-01-01';

	$meses = [];

	$date1  = $fecha_inicio;
	$date2  = date("Y-m-d");
	$time   = strtotime($date1);
	$last   = date('Y-m', strtotime($date2));

	do {
		$month = date('Y-m', $time);
		$total = date('t', $time);
		$meses[] = $month;
		$time = strtotime('+1 month', $time);
	} while ($month != $last);

	$data = [];
	$cabecera_where = "AND c.id IS NOT NULL";
	$cabecera_where = "AND c.canal_de_venta_id in (16,17,21,30,34,42,43)";
	$locales_command_where = "";
	$locales_command_where = "AND l.red_id = 5 ";
	$locales_command_where .= "AND  l.id IS NOT NULL";
	
	$concepto = "APOSTADO";
	$where_agente = "";
	if(array_key_exists("filtro", $_POST)){
		$filtro = $_POST["filtro"];
		if(array_key_exists("concepto", $filtro)){
			if($filtro["concepto"]){
				$concepto = $filtro["concepto"];
			}
		}
		if(array_key_exists("agente", $filtro)){
			if($filtro["agente"]){
				if(!in_array("all", $filtro["agente"])){
					$where_agente .= " AND (SELECT cli.nombre
							FROM tbl_contratos c
							LEFT JOIN tbl_clientes cli ON cli.id = c.cliente_id
							WHERE c.estado = 1 AND c.local_id = l.id LIMIT 1
)							IN ('".implode("','", $filtro["agente"])."')";
				}
			}
		}
		if(array_key_exists("locales", $filtro)){
			if($filtro["locales"]){
				if(in_array("all", $filtro["locales"])){
					if($login["usuario_locales"]){
						$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
						$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
					}
				}else{
					$cabecera_where .= " AND local_id IN ('".implode("','", $filtro["locales"])."')";
					$locales_command_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
				}
			}else{
				if($login["usuario_locales"]){
					$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
					$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
				}
			}
		}else{
			if($login["usuario_locales"]){
				$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
				$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
			}			
		}
		if(array_key_exists("estado_locales", $filtro)){
			if($filtro["estado_locales"] == "activos" ){
				$locales_command_where .= " AND l.operativo = 1";
			} else {
				$locales_command_where .= " AND l.operativo = 2";
			}
		}

		if(array_key_exists("canales_de_venta", $filtro)){
			if($filtro["canales_de_venta"]){
				if( !in_array( "all" ,$filtro["canales_de_venta"]) ){
					$cabecera_where .= " AND c.canal_de_venta_id IN ('".implode("','", $filtro["canales_de_venta"])."')";
				}
				if( in_array( "all" ,$filtro["canales_de_venta"]) ){
					$cabecera_where .= " AND c.canal_de_venta_id IN (16,17,21,30,34,42,43)";
				}
			}
		}
	}
	$cabecera_where .= " AND c.estado = '1'";
	
	$locales = []; //LOCALES

    // MOOOOOSSSTTRRRAAAARRRR_RRREEEEEPPPOOORRRTTTEEEEEEEEE
	$locales_command = "SELECT
			l.id as local_id
			,l.nombre AS 'NOMBRE TIENDA'
			,(SELECT cli.nombre
				FROM tbl_contratos c
				LEFT JOIN tbl_clientes cli ON cli.id = c.cliente_id
				WHERE c.estado = 1 AND c.local_id = l.id LIMIT 1
			) AS 'NOMBRE AGENTE'
			,udep.nombre as DEPARTAMENTO
			,up.nombre as PROVINCIA
			,ud.nombre as DISTRITO
			,zdep.nombre as ZONA_NOMBRE
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
		LEFT JOIN tbl_ubigeo_departamentos udep2 ON udep2.nombre = udep.nombre
        LEFT JOIN tbl_zonas_departamento zdep ON zdep.id = udep2.zonas_departamento_id
		WHERE 1 = 1 "
	;
    $locales_query = $mysqli->query($locales_command .$locales_command_where .$where_agente);
    if($mysqli->error){
        $return["ERROR_MYSQL"] = $mysqli->error;
        print_r($mysqli->error);
        echo $locales_command;
    }    

	$canales_where = " AND id IN (16,17,21,30,34,42,43)";
	if(array_key_exists("canales_de_venta", $filtro)){
		if($filtro["canales_de_venta"]){
			if( !in_array( "all" ,$filtro["canales_de_venta"]) ){
				$canales_where .= " AND id IN ('".implode("','", $filtro["canales_de_venta"])."')";
			}
			if( in_array( "all" ,$filtro["canales_de_venta"]) ){
					$canales_where .= " AND id IN (16,17,21,30,34,42,43)";
			}
		}
	}
    $cdv_arr = []; // CANALES DE VENTA
	$cdv_command = "SELECT id, nombre, codigo FROM tbl_canales_venta WHERE estado = '1' $canales_where ORDER BY id ASC";
	$cdv_query = $mysqli->query($cdv_command);
	while($cdv = $cdv_query->fetch_assoc()){
		$cdv_arr[$cdv["id"]] = $cdv;
	}
	
	$locales2 = [];
    while($lcl = $locales_query->fetch_assoc()){
    	$locales2[] = $lcl;
        foreach($cdv_arr as $cdv_id => $cdv)
        {
        	$lcl["CANAL DE VENTA"] = $cdv["codigo"];
        	$lcl["canal_de_venta_id"] = $cdv["id"];
        	foreach ($meses as  $mes)
	    	{
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
					,sum(c.total_produccion) AS 'PRODUCCIÓN'
					,sum(c.total_freegames) AS 'PARTICIPACIÓN OAT'
					,sum(c.total_cliente) AS 'PARTICIPACIÓN AGENTE'
				FROM tbl_transacciones_cabecera c
				LEFT JOIN tbl_locales l ON l.id = c.local_id
				WHERE 
				c.estado = 1 
				AND l.estado = 1
				AND c.id IS NOT NULL
				AND c.fecha >= '{$fecha_inicio}'  AND c.fecha <= now()
				AND l.reportes_mostrar = 1
				AND c.canal_de_venta_id in (16,17,21,30,34,42,43)
				$locales_command_where
				$cabecera_where
				GROUP BY DATE_FORMAT(c.fecha,'%Y-%m') ,c.local_id ,c.canal_de_venta_id
				ORDER BY c.fecha ASC ,local_nombre , c.canal_de_venta_id";
    $transacciones_query = $mysqli->query($trans_command);
    if($mysqli->error){
        $return["ERROR_MYSQL"] = $mysqli->error;
        print_r($mysqli->error);
    }
    $locales_transacciones = [];
    while($lcl = $transacciones_query->fetch_assoc()){    	
        $locales_transacciones[$lcl["local_id"]][$lcl["mes"]][$lcl["canal_de_venta_id"]] = $lcl;
    }

	foreach ($locales2 as $id => $data)
	{
		$locales2[$id]["liquidaciones"] = isset($locales_transacciones[$data["local_id"]]) ? $locales_transacciones[$data["local_id"]] : [];
	}	
	/*sum TOTAL local*/
	foreach ($locales2 as $id => $data)
	{
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
	foreach ($meses as  $mes)
	{
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
	foreach ($locales2 as $id => $data_local)
	{
		foreach($cdv_arr as $cdv_id => $cdv) /*fill local with cdvs*/
        {
     	   	$objeto =
     	   	[
     	   		"NOMBRE TIENDA" => $data_local["NOMBRE TIENDA"] ,
     	   		"NOMBRE AGENTE" => $data_local["NOMBRE AGENTE"],
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
				if( isset($data_local["liquidaciones"]) )
				{
					if( isset($data_local["liquidaciones"][$mes]) )
					{
						if( isset($data_local["liquidaciones"][$mes][$cdv["id"]]) )
						{
							if( isset($data_local["liquidaciones"][$mes][$cdv["id"]][$concepto]) )
							{
								$valor_mes = $data_local["liquidaciones"][$mes][$cdv["id"]][$concepto];
							}
						}
					}
				}
				$objeto[$mes]  = $valor_mes;
			}
			$array_datatable[] = $objeto;
        }

        $objeto =
     	   	[
     	   		"NOMBRE TIENDA" => $data_local["NOMBRE TIENDA"] ,
     	   		"NOMBRE AGENTE" => $data_local["NOMBRE AGENTE"],
     	   		"DEPARTAMENTO" => $data_local["DEPARTAMENTO"],
     	   		"PROVINCIA" => $data_local["PROVINCIA"],
     	   		"DISTRITO" => $data_local["DISTRITO"],
				"ZONA" => $data_local["ZONA_NOMBRE"],
     	   		"CANAL DE VENTA" => "TOTAL",
     	   	];
		foreach ($meses as  $mes)
		{//add month  value 
			$valor_mes = 0;
				if( isset($data_local["liquidaciones"]) )
				{
					if( isset($data_local["liquidaciones"][$mes]) )
					{
						if( isset($data_local["liquidaciones"][$mes]["TOTAL"]) )
						{
							if( isset($data_local["liquidaciones"][$mes]["TOTAL"][$concepto]) )
							{
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
?>