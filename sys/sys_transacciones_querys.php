<?php

// get_sumas_test(".$call_param.")
function  get_sumas_test($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
        d.local_id,	
        d.canal_de_venta_id,
        IF(ISNULL(SUM(d.apostado)),'0.00',SUM(d.apostado)) AS total_apostado,
        IF(ISNULL(SUM(d.ganado)),'0.00',SUM(d.ganado)) AS total_ganado,
        IF(ISNULL(SUM(d.income)),'0.00',SUM(d.income)) AS total_income,
        IF(ISNULL(SUM(d.withdraw)),'0.00',SUM(d.withdraw)) AS total_withdraw
        FROM tbl_transacciones_detalle d

        LEFT JOIN tbl_contratos con ON (con.local_id = d.local_id)
        WHERE d.created >= '".$fecha_inicio."'
        AND d.created < '".$fecha_fin."'
        AND d.local_id IS NOT NULL
        AND ( d.tipo = '1' OR d.tipo = '4' )
        AND (
            IF(
                (con.tipo_contrato_id = 2)
                , (d.state != 'Returned')
                , (d.state IS NULL OR d.state != 'never_use_this_state')
                )
            )
        AND d.servicio_id = '".$servicio_id."'
        AND d.local_id IN ($local_id_search)
        GROUP BY d.local_id ASC, d.canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}

//get_resumen_turnover(".$call_param.")";
function get_resumen_turnover($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
	local_id,

IF(ISNULL(SUM(income)),'0.00',SUM(income)) AS total_income,

IF(ISNULL(SUM(deposit)),'0.00',SUM(deposit)) AS total_deposit,

IF(ISNULL(SUM(terminal_withdraw)),'0.00',SUM(terminal_withdraw)) AS total_terminal_withdraw,

IF(ISNULL(SUM(withdraw)),'0.00',SUM(withdraw)) AS total_withdraw
FROM tbl_transacciones_detalle
WHERE created >= '".$fecha_inicio."'
AND created < '".$fecha_fin."'
AND local_id IS NOT NULL
AND tipo = '4'
AND servicio_id = '".$servicio_id."'
AND local_id IN ($local_id_search)
GROUP BY local_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_terminal_turnover(".$call_param.")";
function get_terminal_turnover($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
	local_id,

IF(ISNULL(SUM(income)),'0.00',SUM(income)) AS total_income,
 
IF(ISNULL(SUM(deposit)),'0.00',SUM(deposit)) AS total_deposit,
 
IF(ISNULL(SUM(terminal_withdraw)),'0.00',SUM(terminal_withdraw)) AS total_terminal_withdraw,

IF(ISNULL(SUM(withdraw)),'0.00',SUM(withdraw)) AS total_withdraw
FROM tbl_transacciones_detalle
WHERE created >= '".$fecha_inicio."'
AND created < '".$fecha_fin."'
AND local_id IS NOT NULL
AND tipo = '2'
AND servicio_id = '".$servicio_id."'
AND local_id IN ($local_id_search)
GROUP BY local_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_cashdesk_turnover(".$call_param.")";
function get_cashdesk_turnover($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
	local_id,
 
IF(ISNULL(SUM(terminal_income)),'0.00',SUM(terminal_income)) AS terminal_income,
 
IF(ISNULL(SUM(terminal_withdraw)),'0.00',SUM(terminal_withdraw)) AS terminal_withdraw,
  
IF(ISNULL(SUM(deposit)),'0.00',SUM(deposit)) AS total_deposit,
  
IF(ISNULL(SUM(withdraw)),'0.00',SUM(withdraw)) AS total_withdraw
FROM tbl_transacciones_detalle
WHERE created >= '".$fecha_inicio."'
AND created < '".$fecha_fin."'
AND local_id IS NOT NULL
AND tipo = '3'
AND servicio_id = '".$servicio_id."'
AND local_id IN ($local_id_search)
GROUP BY local_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_num_tickets_test(".$call_param.")";
function get_num_tickets_test($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT local_id, canal_de_venta_id, COUNT(id) AS num_tickets
    FROM tbl_transacciones_detalle
    WHERE created BETWEEN CAST('".$fecha_inicio."' AS DATE) AND CAST('".$fecha_fin."' AS DATE)
    AND local_id IS NOT NULL
    AND servicio_id = '".$servicio_id."'
    AND tipo = '1'
    AND local_id IN ($local_id_search)
    GROUP BY local_id ASC, canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_num_tickets_resumen(".$call_param.")";
function get_num_tickets_resumen($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT d.local_id, 
    d.canal_de_venta_id, 
    (IFNULL(SUM(d.num_tickets),0) - IFNULL(SUM(r.cancelled_tickets),0)) AS num_tickets
    FROM tbl_transacciones_detalle d
    LEFT JOIN tbl_transacciones_repositorio r ON (r.at_unique_id = d.at_unique_id)
    WHERE d.created >= '".$fecha_inicio."'
    AND d.created < '".$fecha_fin."'
    AND d.servicio_id = '".$servicio_id."'
    AND d.tipo = '4'
    AND d.local_id IN ($local_id_search)
    GROUP BY d.local_id ASC, d.canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_num_tickets_ganados(".$call_param.")
function get_num_tickets_ganados($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT local_id, canal_de_venta_id, COUNT(id) AS num_tickets
    FROM tbl_transacciones_detalle
    WHERE created >= '".$fecha_inicio."'
    AND created < '".$fecha_fin."'
    AND local_id IS NOT NULL
    AND state = 'Won'
    AND servicio_id = '".$servicio_id."'
    AND tipo = '1'
    AND local_id IN ($local_id_search)
    GROUP BY local_id ASC, canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_num_tickets_ganados_pagados(".$call_param.")";
function get_num_tickets_ganados_pagados($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT local_id, canal_de_venta_id, COUNT(id) AS num_tickets
    FROM tbl_transacciones_detalle
    WHERE created >= '".$fecha_inicio."'
    AND created < '".$fecha_fin."'
    AND local_id IS NOT NULL
    AND state = 'Won'
    AND paid_local_id IS NOT NULL
    AND servicio_id = '".$servicio_id."'
    AND tipo = '1'
    AND local_id IN ($local_id_search)
    GROUP BY local_id ASC, canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_pagados_test(".$call_param.")";
function get_pagados_test($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT local_id, 
    canal_de_venta_id, 
    
    IF(ISNULL(SUM(ganado)),'0.00',SUM(ganado)) AS total_ganado_pagado
    FROM tbl_transacciones_detalle
    WHERE paid_day >= '".$fecha_inicio."'
    AND paid_day < '".$fecha_fin."'
    AND paid_local_id IS NOT NULL
    AND servicio_id = '".$servicio_id."'
    AND (tipo = '1' OR tipo = '4')
    AND local_id IN ($local_id_search)
    GROUP BY local_id ASC, canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_ganados(".$call_param.")";
function get_ganados($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
    local_id,
    canal_de_venta_id,
IF(ISNULL(SUM(ganado)),'0.00',SUM(ganado)) AS total_ganado
FROM tbl_transacciones_detalle
WHERE calc_date >= '".$fecha_inicio."'
AND calc_date < '".$fecha_fin."'
AND state IN ('Won','CashOut','Returned')
AND servicio_id = '".$servicio_id."'
AND tipo = '1'
AND local_id IN ($local_id_search)
GROUP BY local_id ASC, canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_pbet_pagados(".$call_param.")";
function get_pbet_pagados($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
	d.local_id,    
	IF(ISNULL(SUM(d.ganado)),'0.00',SUM(d.ganado)) AS total_ganado_pagado
FROM tbl_transacciones_detalle d
LEFT JOIN tbl_contratos con ON (con.local_id = d.local_id)
WHERE d.paid_day >= '".$fecha_inicio."'
AND d.paid_day < '".$fecha_fin."'
AND d.canal_de_venta_id = '16' 
AND d.paid_local_id IS NOT NULL
AND d.servicio_id = '".$servicio_id."'
AND d.tipo = '1'

AND (
	IF(
		(con.tipo_contrato_id = 2)
		, (d.state IS NULL OR d.state != 'Returned')
		, (d.state IS NULL OR d.state != 'never_use_this_state')
		)
	)

AND (
		(d.paid_canal_de_venta_id = '16' AND d.paid_local_id = d.local_id)
		OR
		(d.paid_canal_de_venta_id = '16' AND d.paid_local_id != d.local_id)
		OR
		(d.paid_canal_de_venta_id != '16' AND d.paid_local_id != d.local_id)
	)
AND d.local_id IN ($local_id_search)
GROUP BY d.local_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_pagados_en_otra_tienda_test(".$call_param.")";
function get_pagados_en_otra_tienda_test($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT local_id, 
	canal_de_venta_id, 

	IF(ISNULL(SUM(ganado)),'0.00',SUM(ganado)) AS total_pagado
FROM tbl_transacciones_detalle
WHERE paid_day BETWEEN CAST('".$fecha_inicio."' AS DATE) AND CAST('".$fecha_fin."' AS DATE)
AND local_id != paid_local_id
AND tipo = '1'
AND servicio_id = '".$servicio_id."'
AND local_id IN ($local_id_search)

GROUP BY local_id ASC, canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_pagados_de_otra_tienda_test(".$call_param.")";
function get_pagados_de_otra_tienda_test($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT paid_local_id, paid_canal_de_venta_id, 

    IF(ISNULL(SUM(ganado)),'0.00',SUM(ganado)) AS total_pagado
    FROM tbl_transacciones_detalle
    
    WHERE paid_day >= '".$fecha_inicio."'
    AND paid_day < '".$fecha_fin."'
    AND local_id != paid_local_id
    
    AND servicio_id = '".$servicio_id."'
    AND tipo = '1'
    AND local_id IN ($local_id_search)
    GROUP BY paid_local_id ASC, paid_canal_de_venta_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_cashdesk_balance(".$call_param.")";
function get_cashdesk_balance($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
    local_id,
    
    
    IF(ISNULL(SUM(apostado)),'0.00',SUM(apostado)) AS apostado,
    
    IF(ISNULL(SUM(ganado)),'0.00',SUM(ganado)) AS ganado,
    
    IF(ISNULL(SUM(pagado)),'0.00',SUM(pagado)) AS pagado,
    ROUND(
    IF(ISNULL(SUM(apostado)),0,SUM(apostado))
        -
        IF(ISNULL(SUM(pagado)),0,SUM(pagado))
        +
        IF(ISNULL(SUM(deposit)),0,SUM(deposit))
        -
        IF(ISNULL(SUM(withdraw)),0,SUM(withdraw)),2) AS balance
    -- ROUND(SUM(apostado) - SUM(pagado) + SUM(deposit) - SUM(withdraw),2) AS balance
    
    FROM tbl_transacciones_detalle
    WHERE created >= '".$fecha_inicio."'
    AND created < '".$fecha_fin."'
    AND local_id IS NOT NULL
    AND servicio_id = '".$servicio_id."'
    AND tipo = '3'
    AND local_id IN ($local_id_search)
    GROUP BY local_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_terminal_premios_pagados(".$call_param.")";
function get_terminal_premios_pagados($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
	local_id,

    IF(ISNULL(SUM(ganado)),'0.00',SUM(ganado)) AS premios_pagados
FROM tbl_transacciones_detalle
WHERE paid_day >= '".$fecha_inicio."'
AND paid_day < '".$fecha_fin."'
AND canal_de_venta_id = '17'
AND tipo = '1'
AND servicio_id = '".$servicio_id."'
AND (
    	(paid_canal_de_venta_id != '17' AND paid_local_id = local_id)
    	OR
    	(paid_canal_de_venta_id = '17' AND paid_local_id != local_id)
    	OR
    	(paid_canal_de_venta_id != '17' AND paid_local_id != local_id)
    )
AND local_id IN ($local_id_search)
GROUP BY local_id ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}
//get_apostado_odds(".$call_param.")";
function get_apostado_odds($fecha_inicio,$fecha_fin,$servicio_id,$local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT
	d.local_id,
	
	d.canal_de_venta_id,
	d.odds,
	IF(ISNULL(SUM(d.apostado)),'0.00',SUM(d.apostado)) AS apostado
	
	
FROM tbl_transacciones_detalle d
LEFT JOIN tbl_contratos con ON (con.local_id = d.local_id)
WHERE d.created >= '".$fecha_inicio."'
AND d.created < '".$fecha_fin."'
AND d.tipo = '1'

AND (
	IF(
		(con.tipo_contrato_id = 2)
		, (d.state != 'Returned')
		, (d.state IS NULL OR d.state != 'never_use_this_state')
		)
	)
AND d.local_id != '1'
AND d.local_id IN ($local_id_search)
AND d.servicio_id = '".$servicio_id."'

GROUP BY d.local_id ASC, d.canal_de_venta_id ASC, d.odds ASC;";
    return $total_sumas_query = $mysqli->query($query_call);

}

// get_locales_formulas

function get_locales_formulas($local_id_search){
	global $mysqli;
	global $return;
	global $login;
    
    $query_call = "SELECT 
	local_id, 
	canal_de_venta_id, 
	fuente, 
	columna, 
	tipo, 
	operador_id, 
	desde, 
	hasta, 
	monto AS monto_cliente, 
	contrato_id, 
	formula_id, 
	tipo_contrato_id,
	servicio_id,
	producto_id
FROM 
	view_locales_contratos
WHERE
    local_id IN ($local_id_search)
    ;";
    return $total_sumas_query = $mysqli->query($query_call);

}
?>