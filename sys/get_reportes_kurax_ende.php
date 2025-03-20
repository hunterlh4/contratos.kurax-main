<?php
include("db_connect.php");
include("sys_login.php");


include_once("globalFunctions/generalInfo/local.php");

if (isset($_POST["get_locales_kurax_ende"])) {
    $get_data = $_POST["get_locales_kurax_ende"];
    $redes = $get_data["redes"];
    $locales = getLocalesByRedesSinPermisos($redes);
    $return = [
        'status' => 200,
        'locales' => $locales,
    ];
    echo json_encode($return);
    exit();

} else if (isset($_POST["get_data_kurax_ende"])) {
    $get_data = $_POST["get_data_kurax_ende"];
    $locales = $get_data['locales'];
    $redes = $get_data['redes'];
    $canales_venta = $get_data['canales_venta'];
    $fecha_inicio = $get_data['fecha_inicio'];
    $fecha_fin = $get_data['fecha_fin'];

    $filtro_locales = "";
    $filtro_redes = "";
    $filtro_canales_venta = "";
    if(!empty($locales)){
        $filtro_locales = ' AND l_pago.id IN ('. implode(',', $locales) .')'; 
    }
    if(!empty($redes)){
        $filtro_redes = ' AND l_pago.red_id IN ('. implode(',', $redes) .')'; 
    }
    if(!empty($canales_venta)){
        $filtro_canales_venta = ' AND ende.canal_de_venta_id_origen IN ('. implode(',', $canales_venta) .')'; 
    }

    $permisos_locales = "";
    // if($login && $login["usuario_locales"]){
    //     $permisos_locales = " AND l_pago.id IN (".implode(",", $login["usuario_locales"]).") ";
    // }

    $query = "  SELECT 
                    ende.fecha as fecha,
                    rs_pago.nombre as rs_pago_nombre,
                    l_origen.nombre as local_origen_nombre,
                    cv_origen.nombre as canal_origen_nombre,
                    rs_origen.nombre as rs_origen_nombre,
                    l_pago.nombre as local_pago_nombre,
                    ende.monto_pagado as monto_pagado
                from
                    kx_ende ende
                    INNER JOIN tbl_locales l_pago          ON l_pago.id = ende.local_id_pago
                    INNER JOIN tbl_locales l_origen        ON l_origen.id = ende.local_id_origen
                    INNER JOIN tbl_razon_social rs_pago    ON l_pago.razon_social_id = rs_pago.id
                    INNER JOIN tbl_razon_social rs_origen  ON l_origen.razon_social_id = rs_origen.id
                    INNER JOIN tbl_canales_venta cv_origen ON cv_origen.id = ende.canal_de_venta_id_origen
                where
                    ende.estado = 1
                    and fecha >= '" . $fecha_inicio ."'".
              "   AND fecha <= '" . $fecha_fin . "'
                    $filtro_locales
                    $filtro_redes
                    $filtro_canales_venta
                    --  $permisos_locales 
                ";
    // se comentó el permiso de locales por acuerdo con Kathia Guillermo

    $data = [];
    $query_data = $mysqli->query($query);

    if ($mysqli->error) {
        error_log("Error en la consulta: " . $mysqli->error);
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => ''
        ];
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {

        while ($r = $query_data->fetch_assoc()) {
            $data[] = [
                "0" => date('d/m/Y', strtotime($r['fecha'])),
                "1" => $r['rs_pago_nombre'],
                "2" => $r['local_pago_nombre'],
                "3" => $r['canal_origen_nombre'],
                "4" => $r['rs_origen_nombre'],
                "5" => $r['local_origen_nombre'],
                "6" => number_format($r['monto_pagado'],2),
            ];
        }
    
        $result['sEcho'] = 1;
        $result['iTotalRecords'] = count($data);
        $result['iTotalDisplayRecords'] = count($data);
        $result['aaData'] = $data;
    
        
        echo json_encode($result);
        exit();
    }
    
}
