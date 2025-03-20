<?php
include("db_connect.php");
include("sys_login.php");
include("globalFunctions/generalInfo/functions_torito.php");


if (isset($_POST["accion"]) && $_POST["accion"] === "get_api_transacciones") {

    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $trx_estado_bd = $_POST['trx_estado_bd'];
    $local_id = $_POST['local_id'];
    $transaction_id = $_POST['transaction_id'];

    if (empty($fecha_inicio)  || empty($fecha_fin)) {
        echo json_encode([
            'status' => '500',
            'msg' => "Las fechas no pueden ser vacías."
        ]);
        exit();
    }

    $data = getTransaccionesApi($fecha_inicio);

    if ($data->result == 'OK') {
        $transacciones = ($data->data);
        $aaData = [];
        $data_faltante = [];

        foreach ($transacciones as $key => $value) {

            $nombre_local = '';
            $local_id_trx = '';
            $cc_id = '';
           
            $local = getLocalTrx($value->id_store);
            if ($local) {
                $nombre_local = $local['nombre'];
                $cc_id = $local['cc_id'];
                $local_id_trx = $local['id'];
            }

            //FILTRO LOCAL
            if ($local_id != 0) {
                if ($local_id != $local_id_trx) {
                    continue;
                }
            }

            //FILTRO transaction_id
            if ($transaction_id != '') {
                if ($transaction_id != $value->transactionid) {
                    continue;
                }
            }

            $query = "SELECT id from tbl_torito_transaccion where transactionid = '$value->transactionid' ";

            $result = $mysqli->query($query);
            $trx = [];
            while ($r = $result->fetch_assoc()) {
                $trx[] = $r;
            }

            if (count($trx)) {
                $bd = 'EXISTE';
            } else {
                $bd = 'FALTANTE';

                $data_faltante[] = [
                    'id_cashier' => $value->id_cashier,
                    'id_store' => $value->id_store,
                    'transactionid' => $value->transactionid,
                    'amount' => $value->amount,
                    'transactiontype' => $value->transactiontype,
                    'local_id_trx' => $local_id_trx,
                    'status' => $value->status,
                    'numlines' => $value->numlines,
                    'prize_id' => $value->prize_id,
                    'numdraws' => $value->numdraws,
                    'date' => $value->date,
                    'time' => $value->time,
                    'timestamp' => $value->date . " " . $value->time,
                ];
            }

            // FILTROS
            // FILTRO TIPO
            if ($trx_estado_bd != 0) {

                if ($trx_estado_bd == 1) {
                    if ($bd != 'EXISTE') {
                        continue;
                    }
                } else if ($trx_estado_bd == 2) {
                    if ($bd != 'FALTANTE') {
                        continue;
                    }
                }
            }



            $col_local = "[" . $value->id_store . "] " . $nombre_local;

            $aaData[] = array(
                '0' => $col_local,
                '1' => $value->transactionid,
                '2' => $value->transactiontype,
                '3' => $value->date . ' ' . $value->time,
                '4' => $value->amount,
                '5' => $value->id_cashier,
                '6' => $value->status,
                '7' => $value->numlines,
                '8' => $bd,
            );
        }

        $resultado = array(
            "status" => 200,
            "iTotalREcords" => count($aaData),
            "iTotalDisplayRecords" => count($aaData),
            "aaData" => $aaData,
            "dataFaltante" => $data_faltante
        );
        echo json_encode($resultado);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "insertar_transacciones_faltantes") {
    $transacciones_faltantes = json_decode($_POST["transacciones_faltantes"]);
    $transacciones_insertadas = 0;


    foreach ($transacciones_faltantes as $key => $value) {

        $transactionid = $value->transactionid;

        $q = "SELECT id from tbl_torito_transaccion where transactionid = '$transactionid' LIMIT 1";

        $existe = $mysqli->query($q)->fetch_assoc();

        if (empty($existe)) {
            $insert = insertar_transaction_torito($value);
            if ($insert) {
                $transacciones_insertadas++;
            }
        }
    }

    if ($transacciones_insertadas) {
        $status = 200;
        $title = 'Guardado!';
        $icon = 'success';
        $msg = 'Se insertó ' . $transacciones_insertadas . " transacciones";
        if ($transacciones_insertadas < count($transacciones_faltantes)) {
            $msg .= "<br>Hubo " . (count($transacciones_faltantes) - $transacciones_insertadas) . " que no se insertaron.";
        }
    } else {
        $status = 200;
        $title = 'Algo ocurrió!';
        $icon = 'warning';
        $msg = 'No se insertó transacciones';
    }

    $resultado = array(
        "status" => $status,
        "msg" => $msg,
        "title" => $title,
        "icon" => $icon,
    );

    echo json_encode($resultado);
    exit();
}

