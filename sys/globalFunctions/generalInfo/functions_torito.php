<?php
/*
    Inserta una transacción en la tabla tbl_torito_transaccion, busca un partner token de la tabla tbl_torito_acceso
    INPUT: (array)una transaction de la api de torito
    OUPUT: boolean
 */
function insertar_transaction_torito($trx)
{
    global $mysqli;
    $result_insert = false;

    $id_cashier = $trx['id_cashier'];
    $id_store = $trx['id_store'];
    $transactionid = $trx['transactionid'];
    $amount = $trx['amount'];
    $transactiontype = $trx['transactiontype'];
    $local_id_trx = $trx['local_id_trx'];
    $status = $trx['status'];
    $numlines = $trx['numlines'];
    $prize_id = $trx['prize_id'];
    $numdraws = $trx['numdraws'];
    $date = $trx['date'];
    $time = $trx['time'];
    $timestamp = $trx['timestamp'];
    $status_name = '';

    switch ($status) {
        case 'failed':
            $status_name = '0';
            break;
        case 'completed':
            $status_name = '1';
            break;
        default:
            $status_name = '0';
            break;
    }

    $query = "  SELECT 
                    partnertoken, created_at
                FROM `tbl_torito_acceso`
                WHERE 
                    `idcashier` = " . $id_cashier . " AND 
                    `idstore` = " . $id_store . " AND 
                    `created_at` <= '" . $timestamp . "'
                ORDER BY `created_at` DESC
                LIMIT 1";
    

    $torito_acceso = $mysqli->query($query)->fetch_assoc();

    if ($torito_acceso) {
        $partnertoken = $torito_acceso['partnertoken'];
        $created_at = $torito_acceso['created_at'];
    } else {
        return $result_insert;
    }

    $type_cod = "SELECT CASE '$transactiontype'  
                            WHEN 'VENTA GN'  THEN '1'  
                            WHEN 'PAGO GN'  THEN '2' 
                            WHEN 'RECARGA'  THEN '3'  
                            WHEN 'VENTA MM'  THEN '4' 
                            WHEN 'PAGO MM'  THEN '5'         
                            WHEN 'PROMO TORITO'  THEN '6' 
                            WHEN 'PROMO TORITO MM'  THEN '7' 
                            WHEN 'CANJE TORITO'  THEN '8'         
                            WHEN 'CANJE TORITO MM'  THEN '9' END ";

    $insert = " INSERT INTO `tbl_torito_transaccion` 
                            ( `id_red`, `id_local`, `cc_id`, `user_id`, `partnertoken`, `id_torito_tipo_transaccion`, `transactionid`, `date`, `time`, `numlines`, `numdraws`, `amount`, `status`, `created_at`, `updated_at`) 
                        VALUES 
                            ( 0, $local_id_trx, '$id_store', $id_cashier,'$partnertoken', ($type_cod),'$transactionid', date('$timestamp'), time('$timestamp'), $numlines, $numdraws, $amount, $status_name, now(),now())";
    $result = $mysqli->query($insert);
    if ($result) {
        $result_insert = true;
    }

    return $result_insert;
}

/*
    Obtener las transacciones de la API TORITO, SOLO de un día para no sobrecargar la api
    INPUT: (DATETIME) fecha de inicio
    OUPUT: (json_decode) array
 */
function getTransaccionesApi($fecha_inicio)
{
    $url =  'https://api.nexlot.pe/prod/nexlot/v4/gettransactionreport?startDate=' .  $fecha_inicio . '&endDate=' . $fecha_inicio . '&=';
    $ch = curl_init($url);

    $headers = array(
        "x-api-key: hjYqIUYBid4EbCljeSRU3aVoVao1t3zE21QcwYvY",
        "Content-Type: application/json",
        "Authorization: Basic YXB1ZXN0YXRvdGFsYWZmOmM5N2UzYzIwZWU="
    );

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response);
}

/*
    Obtener info del local 
    INPUT: (int) cc_id
    OUPUT: (array) info del local
 */
function getLocalTrx($cc_id){
    global $mysqli;
    $query = "SELECT id, cc_id, nombre from tbl_locales where cc_id = $cc_id LIMIT 1 ";
    $fetch_query = $mysqli->query($query)->fetch_assoc();
    return $fetch_query;
}

/*
    Obtener info del usuario 
    INPUT: (int) usuario_id
    OUPUT: (array) info del usuario
 */
function getUsuarioTrx($usuario_id){
    global $mysqli;
    $usuario = [];
    $query = "SELECT id, usuario from tbl_usuarios where id = $usuario_id LIMIT 1 ";
    $result = $mysqli->query($query);
    if($result)
    {
        $usuario = $result->fetch_assoc();
    }
    return $usuario;
}