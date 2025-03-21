<?php
include_once("db_connect.php");
include_once("sys_login.php");
include_once("globalFunctions/generalInfo/local.php");
include_once("globalFunctions/generalInfo/functions_api_calimaco.php");
include_once("globalFunctions/generalInfo/functions_saldos_tls.php");

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(0);

if (isset($_POST["get_locales_config_saldos_web"])) {
    $get_data = $_POST["get_locales_config_saldos_web"];
    $red_id = $get_data["red_id"];
    $locales = getLocalesByRed($red_id);

    echo json_encode($locales);
    exit();
} else if (isset($_POST["get_limite"])) {
    $get_data = $_POST["get_limite"];
    $item_id = $get_data['item_id'];
    $tipo_limite = $get_data['tipo_limite'];

    $lim = getLimite($tipo_limite, $item_id);
    if($lim){
        $lim['limite'] = number_format($lim['limite'], 2);
    }

    $resultado = array(
        "status" => 200,
        "limite" => $lim,
    );

    echo json_encode($resultado);
    exit();
} else if (isset($_POST["get_historico_limite"])) {
    $get_data = $_POST["get_historico_limite"];
    $limite_id = $get_data['limite_id'];

    $historicos = getHistoricoLimite($limite_id);

    $table_historial =
        '<table class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th class="text-center">Usuario</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">Límite Anterior</th>
                    <th class="text-center">Límite Nuevo</th>
                    <th class="text-center">Fecha</th>
                </tr>
            </thead>
        <tbody>';
    $tipo_limite = '';

    foreach ($historicos as $row) {
        $table_historial .= '
                <tr>
                    <td class="text-center">' . $row['usuario'] . '</td>
                    <td class="text-center">' . $row['tipo_descripcion'] . '</td>
                    <td class="text-center">' . number_format($row['limite_anterior'],2) . '</td>
                    <td class="text-center">' . number_format($row['limite_nuevo'],2) . '</td>
                    <td class="text-center">' . $row['created_at'] . '</td>
                </tr>';

        $tipo_limite = $row['tipo_limite'];
    }

    $table_historial .= ' 
            </tbody>
        </table>';

    $resultado = array(
        "status" => 200,
        "tipo_limite" => $tipo_limite,
        "result" => $table_historial
    );

    echo json_encode($resultado);
    exit();
} else if (isset($_POST["update_estado_limite"])) {

    $get_data = $_POST["update_estado_limite"];
    $limite_id = $get_data['limite_id'];
    $new_state = $get_data['new_state'];

    $query = "  UPDATE tbl_saldo_teleservicios_limites
                set estado = $new_state
                where id = $limite_id";

    $update = $mysqli->query($query);

    if($update){
        $status = 200;
        $msg = "Se cambió el estado";
    } else {
        $status = 500;
        $msg = "Ocurrió un error";
    }

    $resultado = array(
        "status" => $status,
        "msg" => $msg ,
    );

    echo json_encode($resultado);
    exit();

} else if (isset($_POST["get_limites_config_saldos_tls"])) {
    $get_data = $_POST["get_limites_config_saldos_tls"];
    $tipo_limite = $get_data['tipo_limite'];
    $local_id = $get_data['item_id'];
    $estados = $get_data['estados'];

    switch ($tipo_limite) {
   
        default:
            # code...
            break;
    }

    $where_item = "";
    $inner_local = "";
    $where_tipo_limite = "";
    $limit = '';
    $select = '';

    if (!empty($local_id) && $tipo_limite == 'local') {
        $red_id = $get_data['red_id'];

        $select = "l.id, l.item_id as item_id, lo.id as local_id, lo.cc_id, lo.nombre as nombre, l.limite, l.estado";
        $inner_local = "INNER JOIN tbl_locales lo on lo.id = l.item_id";
        $where_item .= " AND lo.id IN (" . implode(",", $login["usuario_locales"]) . ")";
        
        if ($local_id != '_all_') {
            $where_item .= "AND l.item_id = $local_id ";
            $limit = "LIMIT 1";
        }
        
        if ($red_id != '_all_') {
            $where_item .= " AND lo.red_id = " . $red_id . "";
        }

    } else if ($tipo_limite == 'cliente'){
        $select = "l.id, l.item_id as item_id, l.nombre as nombre, l.limite, l.estado";
        if (!empty($idweb)) {
            $where_item = "AND l.item_id = $idweb ";
            $limit = "LIMIT 1";
        }
    }

    if (!empty($tipo_limite)) {
        $where_tipo_limite = "AND t.tipo = '$tipo_limite' ";
    }

    $query = "  SELECT 
                    $select
                FROM tbl_saldo_teleservicios_limites l
                    INNER JOIN tbl_saldo_teleservicios_limites_tipos t on l.tipo_limite = t.id
                    $inner_local
                where
                    l.estado in (" . $estados . ")
                $where_item
                $where_tipo_limite
                $limit
                ";

    $result = $mysqli->query($query);
    $limites = [];
    while ($lim = $result->fetch_assoc()) {
        if($lim['estado'] ){
            $state = 'on';
            $color = 'success';
            $title_btn_change_state = 'Presione para desactivar';
        } else {
            $state = 'off';
            $color = 'danger';
            $title_btn_change_state = 'Presione para activar';
        }

        if($lim['estado'] == 1){
            $btn_edit = '<button class="btn btn-xs btn-success btn-edit-limite-tls" data-id="' . $lim['id'] . '" data-item-id="' . $lim['item_id'] . '" data-nombre="' . $lim['nombre'] . '" data-tipo-limite="' . $tipo_limite . '" title ="Editar límite"><span class="glyphicon glyphicon-edit"></span></button>' ;
        } else { $btn_edit = ''; }
        $btn_state = '<button class="btn btn-xs text-' . $color . ' btn-activar-limite-tls" data-id="' . $lim['id'] . '" data-estado="' . $lim['estado'] . '" title="' . $title_btn_change_state . '"><i class="fa fa-toggle-' . $state . '"></i></button>';
        $btn_hist = '<button class="btn btn-xs btn-warning btn-get-historico-limite-tls " data-id="' . $lim['id'] . '" data-nombre="' . $lim['nombre'] . '" title="Historial"><span class="glyphicon glyphicon-time"></span></button>';

        $botones = '<div class="div-tbl-acciones row">'. 
                            '<div class="col-md-4 text-right">'. $btn_edit . '</div>'. 
                            '<div class="col-md-4">'. $btn_state. '</div>'. 
                            '<div class="col-md-4 text-left">'. $btn_hist . '</div>'. 
                    '</div>';

        switch ($tipo_limite) {
            case 'local':

                $limites[] =  array(
                    '0' => $lim['local_id'],
                    '1' => $lim['cc_id'],
                    '2' => $lim['nombre'],
                    '3' => number_format($lim['limite'],2),
                    '4' => $botones,
                );
                break;
    
            
            default:
                # code...
                break;
        }

        

        
    }

    if ($tipo_limite == 'local' &&  !empty($local_id) && count($limites) == 0) {
        $status = 201; // no hay limite registrado para ese local    
    } elseif ($tipo_limite == 'cliente' && !empty($idweb) && count($limites) == 0) {
        $status = 201; // no hay limite registrado para ese local    
    } else {
        $status = 200;
    }

    $resultado = array(
        "status" => $status,
        "iTotalREcords" => count($limites),
        "iTotalDisplayRecords" => count($limites),
        "aaData" => $limites,
    );

    echo json_encode($resultado);
    exit();
} else if (isset($_POST["guardar_limite_saldos_tls"])) {

    $get_data = $_POST["guardar_limite_saldos_tls"];

    $tipo_limite = $get_data['tipo_limite'];
    $item_id = $get_data['item_id'];
    $nombre = $get_data['nombre'];
    $limite = $get_data['limite'];
    $estado = $get_data['estado'];

    if( (is_numeric($limite) && ($limite) >= 0) && $limite != ""){

        $user_id = $login['id'];

        $limite_anterior = getLimite($tipo_limite, $item_id);
        $where_item_id = empty($item_id) ? ' is null ' : ' = ' . $item_id;
        $item_id = empty($item_id) ? 'null ' : $item_id;


        $query = "  UPDATE tbl_saldo_teleservicios_limites l
                    INNER JOIN tbl_saldo_teleservicios_limites_tipos t on l.tipo_limite = t.id
                    set 
                        l.limite = $limite,
                        l.updated_at = now(),
                        l.user_updated_id = $user_id,
                        l.is_modified = 1,
                        l.id = LAST_INSERT_ID(l.id),
                        l.estado = 1
                    where
                        t.tipo = '$tipo_limite'
                        and l.item_id $where_item_id
                        and l.estado in  (0,1)
                    ";

        $result = $mysqli->query($query);
        $updates = $mysqli->affected_rows;

        if ($updates == 0) {

            $query = "  INSERT INTO tbl_saldo_teleservicios_limites(tipo_limite, item_id, nombre, limite, estado, is_modified, created_at, user_created_id) 
                        VALUES((select id from tbl_saldo_teleservicios_limites_tipos where tipo = '$tipo_limite'), $item_id, '$nombre', $limite, $estado, 0, now(), $user_id)";

            $result = $mysqli->query($query);
            $limite_anterior['limite'] = 'null';

            if($tipo_limite == 'local')
            {
                $limite_anterior = getLimite($tipo_limite . '_global', null);
            }
            
        } else if ($updates == 1) {
        }

        $query = " INSERT INTO tbl_saldo_teleservicios_limites_historico(saldo_tls_limite_id, limite_anterior, limite_nuevo,user_created_id, created_at)
                    VALUES(
                            (SELECT LAST_INSERT_ID()),  " . $limite_anterior['limite'] . ", $limite,  $user_id, now()
                        )
        ";

        $insert_historico =  $mysqli->query($query);

        if($insert_historico){
            $status = 200;
            $msg = "Se guardó correctamente.";
        } else {
            $status = 400;
            $msg = "Vuelva a intentarlo. Ocurrió un error: " . $mysqli->error. '.';
        }
    } else {
        $status = 400;
        $msg = "Ingrese un límite válido mayor o igual a 0";
    }

    $resultado = array(
        "status" => $status,
        "insertado" => $result,
        "msg" => $msg
    );

    echo json_encode($resultado);
    exit();

}
