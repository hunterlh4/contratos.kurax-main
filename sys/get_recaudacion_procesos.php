<?php

include("db_connect.php");
include("sys_login.php");

function action_response($code, $message=""){
    return json_encode(['code' => $code, 'message' => $message]);
}

$this_menu = $mysqli->query("
	SELECT id 
	FROM tbl_menu_sistemas 
	WHERE sec_id = 'recaudacion' 
	AND sub_sec_id = 'procesos' 
	LIMIT 1
")->fetch_assoc();

$menu_id = $this_menu["id"];

if(isset($_POST["get_procesos"])){
    /*if(!array_key_exists($menu_id, $usuario_permisos) || !in_array("request", $usuario_permisos[$menu_id])){
        die(action_response('403', 'No Autorizado.'));
    }*/
    $data = $_POST["get_procesos"];
    $page = $data["page"];

    // GET THE N° OF ROWS OF THE LAST 30 DAYS
    $last_days_count = 0;
    $procesos_last_30_days_count = "
                        SELECT COUNT(*) as total FROM
                            (SELECT tp.id
                            FROM tbl_transacciones_procesos tp
                            LEFT JOIN tbl_servicios s ON (s.id = tp.servicio_id)
                            INNER JOIN tbl_canales_venta c ON (c.servicio_id = s.id AND c.en_liquidacion=1)
                            LEFT JOIN tbl_usuarios u ON (u.id = tp.usuario_id)
                            WHERE tp.tipo = 'liquidacion'
                            AND tp.estado != '5'
                            AND tp.fecha BETWEEN NOW() - INTERVAL 30 DAY AND NOW()
                            GROUP BY tp.at_unique_id) as transacciones;";
    $procesos_query = $mysqli->query($procesos_last_30_days_count);
    while($row = $procesos_query->fetch_assoc()){
        $last_days_count = $row["total"];
    }

    $last_days_count = $last_days_count <= 15 ? 15 : $last_days_count;

    // GET THE TOTAL N° OF ROWS
    $total_count = 0;
    $query_total = "
                        SELECT COUNT(*) as total FROM
                            (SELECT tp.id
                            FROM tbl_transacciones_procesos tp
                            LEFT JOIN tbl_servicios s ON (s.id = tp.servicio_id)
                            INNER JOIN tbl_canales_venta c ON (c.servicio_id = s.id AND c.en_liquidacion=1)
                            LEFT JOIN tbl_usuarios u ON (u.id = tp.usuario_id)
                            WHERE tp.tipo = 'liquidacion'
                            AND tp.estado != '5'
                            GROUP BY tp.at_unique_id) as transacciones;";
    $procesos_query = $mysqli->query($query_total);
    while($row = $procesos_query->fetch_assoc()){
        $total_count = $row["total"];
    }

    // GET THE PROCESSES
    $offset = $page * $last_days_count;

    $procesos = array();
    $procesos_command = "SELECT tp.at_unique_id
                                ,tp.fecha
                                ,tp.fecha_inicio
                                ,tp.fecha_fin
                                ,tp.estado
                                ,tp.finalizado
                                ,tp.servicio_id
                                ,s.nombre AS servicio
                                ,u.usuario
                        FROM tbl_transacciones_procesos tp 
                        LEFT JOIN tbl_servicios s ON (s.id = tp.servicio_id)
                        INNER JOIN tbl_canales_venta c ON (c.servicio_id = s.id AND c.en_liquidacion=1)
                        LEFT JOIN tbl_usuarios u ON (u.id = tp.usuario_id)
                        WHERE tp.tipo = 'liquidacion'
                        AND tp.estado != '5'
                        GROUP BY tp.at_unique_id
                        ORDER BY tp.fecha DESC
                        LIMIT $last_days_count OFFSET $offset";
    $procesos_query = $mysqli->query($procesos_command);
    while($pro=$procesos_query->fetch_assoc()){
        $procesos[]=$pro;
    }

    $html = "";
    foreach ($procesos as $pro_k => $pro_v) {
        $html .= "<tr class='pro_bg_${pro_v["servicio_id"]}'>";
        $html .= '<td>' . (($last_days_count * $page) + $pro_k + 1) . '</td>';
        $html .= '<td>' . $pro_v["at_unique_id"] . '</td>';
        $html .= '<td>' . $pro_v["fecha"] . '</td>';
        $html .= '<td>' . $pro_v["servicio"] . '</td>';
        $html .= '<td>' . strstr($pro_v["fecha_inicio"], " " ,true) . '</td>';
        $html .= '<td>' . strstr($pro_v["fecha_fin"], " " ,true) . '</td>';
        $html .= '<td>' . $pro_v["usuario"] . '</td>';
        $html .= '<td>';
            if($pro_v['estado']==0){
                $html .= '<span class="label label-warning">Abierto</span>';
            }elseif($pro_v['estado']==1){
                if($pro_v['finalizado']==1){
                    $html .= '<span class="label label-primary">Finalizado</span>';
                }else{
                    $html .= '<span class="label label-success">Cerrado</span>';
                }
            }elseif($pro_v['estado']==2){
                $html .= '<span class="label label-primary">Finalizado</span>';
            }elseif($pro_v['estado']==5){
                $html .= '<span class="label label-dark">Eliminado</span>';
            }
        $html .= "</td>";
        $html .= "<td class='text-right'>";
            if(array_key_exists(35,$usuario_permisos) && in_array("view", $usuario_permisos[35])){
                $html .= "<a class='btn btn-xs btn-rounded btn-default' target='_blank' href='/?sec_id=recaudacion&amp;sub_sec_id=liquidaciones&amp;proceso_unique_id=${pro_v["at_unique_id"]}' id='${pro_v["at_unique_id"]}'>Ver</a>";
            }
            if($pro_v["estado"]==0){
                if(array_key_exists(35,$usuario_permisos) && in_array("delete", $usuario_permisos[35])){
                    $html .= "<button class='btn btn-xs btn-rounded btn-danger liq_pro_btn' data-opt='eliminar' data-id='${pro_v["at_unique_id"]}'>Eliminar</button>";
                }
                if(array_key_exists(35,$usuario_permisos) && in_array("close", $usuario_permisos[35])){
                    $html .= "<button class='btn btn-xs btn-rounded btn-success liq_pro_btn' data-opt='cerrar' data-id='${pro_v["at_unique_id"]}'>Cerrar</button>";
                }
            }elseif($pro_v["estado"]==1){
                if($pro_v["finalizado"]==1){
                    if(array_key_exists(35,$usuario_permisos) && in_array("open_finished", $usuario_permisos[35])){
                        $html .= "<button class='btn btn-xs btn-rounded btn-warning liq_pro_btn' data-opt='abrir' data-id='${pro_v["at_unique_id"]}'>Abrir</button>";
                    }
                }else{
                    if(array_key_exists(35,$usuario_permisos) && in_array("finish", $usuario_permisos[35])){
                        $html .= "<button class='btn btn-xs btn-rounded btn-primary liq_pro_btn' data-opt='finalizar' data-id='${pro_v["at_unique_id"]}'>Finalizar</button>";
                    }
                    if(array_key_exists(35,$usuario_permisos) && in_array("open", $usuario_permisos[35])){
                        $html .= "<button class='btn btn-xs btn-rounded btn-warning liq_pro_btn' data-opt='abrir' data-id='${pro_v["at_unique_id"]}'>Abrir</button>";
                    }
                }
            }elseif($pro_v["estado"]==2){
                if(array_key_exists(35,$usuario_permisos) && in_array("open_finished", $usuario_permisos[35])){
                    $html .= "<button class='btn btn-xs btn-rounded btn-warning liq_pro_btn' data-opt='abrir' data-id='${pro_v["at_unique_id"]}'>Abrir</button>";
                }
            }elseif($pro_v["estado"]==5){
                if(array_key_exists(35,$usuario_permisos) && in_array("open", $usuario_permisos[35])){
                    $html .= "<button class='btn btn-xs btn-rounded btn-warning liq_pro_btn' data-opt='abrir' data-id='${pro_v["at_unique_id"]}'>Abrir</button>";
                }
            }
        $html .= "</td>";
        $html .= "</tr>";
    }

    echo json_encode(['tabla' => $html, 'num_rows' => $total_count, 'last_days_count' => $last_days_count]);
}