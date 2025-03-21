<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
*/


//*******************************************************************************************************************
//*******************************************************************************************************************
// GUARDAR META
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_meta") {

    $usuario_id = $login ? $login['id'] : 0;
    $zona = $_POST["zona"];
    $producto = $_POST["producto"];
    $monto = $_POST["monto"];

    $query_insert_meta = "
        INSERT INTO tbl_zonas_meta ( ano, mes, zona_id, servicio_id, meta, id_user_created, created_at ) 
        VALUES ( 
            '".date('Y')."', 
            '".date('m')."', 
            '" . $zona . "', 
            '" . $producto . "', 
            '" . $monto . "', 
            '" . $usuario_id . "', 
            now()
        );
    ";
    $mysqli->query($query_insert_meta);

    $query_1 = "
        SELECT 
            id
        FROM tbl_zonas_meta 
        WHERE ano='".date('Y')."'
        AND mes='".date('m')."'
        AND zona_id='" . $zona . "'
        AND servicio_id='" . $producto . "'
        AND meta='" . $monto . "'
        AND id_user_created='" . $usuario_id . "'
    ";
    $list_query_1 = $mysqli->query($query_1);
    //echo $query_1;
    $list_1 = array();
    while ($li_1 = $list_query_1->fetch_assoc()) {
        $list_1[] = $li_1;
    }
    if ($mysqli->error) {
        $result["query_1_error"] = $mysqli->error;
    }
    if (count($list_1) === 0) {
        $result["http_code"] = 400;
        $result["status"] = "Ocurrió un error al realizar el registro.";
        $result["result"] = $list_1;
    } else if (count($list_1) > 0) {
        $result["http_code"] = 200;
        $result["status"] = "ok.";
    } else {
        $result["http_code"] = 400;
        $result["status"] = "Ocurrió un error al realizar el registro.";
        $result["result"] = $list_1;
    }
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// GUARDAR META
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_meta_masiva") {

    $usuario_id = $login ? $login['id'] : 0;
    $ciclo_ano = $_POST["ciclo_ano"];
    $ciclo_mes = str_pad($_POST["ciclo_mes"], 2, "0", STR_PAD_LEFT);
    $detalle = $_POST["detalle"];

    if(count($detalle)>0){
        $result["http_code"] = 200;
        $result["status"] = "ok.";
    } else {
        $result["http_code"] = 400;
        $result["status"] = "Ocurrió un error al realizar el registro.";
    }

    $query_1 = "
        SELECT
            z.id cod_zona,
            IFNULL(
                (SELECT zm.meta 
                FROM tbl_zonas_meta zm 
                WHERE zm.zona_id=z.id AND zm.servicio_id=1 AND zm.ano=$ciclo_ano AND zm.mes='".$ciclo_mes."'
                ORDER BY zm.id DESC LIMIT 1)
            , 0) meta_apd,
            IFNULL(
                (SELECT zm.meta 
                FROM tbl_zonas_meta zm 
                WHERE zm.zona_id=z.id AND zm.servicio_id=3 AND zm.ano=$ciclo_ano AND zm.mes='".$ciclo_mes."'
                ORDER BY zm.id DESC LIMIT 1)
            , 0) meta_jv,
            IFNULL(
                (SELECT zm.meta 
                FROM tbl_zonas_meta zm 
                WHERE zm.zona_id=z.id AND zm.servicio_id=9 AND zm.ano=$ciclo_ano AND zm.mes='".$ciclo_mes."'
                ORDER BY zm.id DESC LIMIT 1)
            , 0) meta_bingo
        FROM
            tbl_zonas z
        ORDER BY
            z.ord
    ";
    $list_query_1 = $mysqli->query($query_1);
    $list_1 = array();
    while ($li_1 = $list_query_1->fetch_assoc()) {
        $list_1[] = $li_1;
    }
    if (count($list_1) === 0) {
        $result["http_code"] = 400;
        $result["status"] = "Ocurrió un error al realizar el registro.";
    } else if (count($list_1) > 0) {
        foreach($detalle as $det){
            foreach ($list_1 as $key => $value) {
                if((int) $value["cod_zona"] === (int) $det["cod_zona"]){
                    if((float) $value["meta_apd"] !== (float) $det["monto_apd"]){
                        $query_insert_meta = "
                            INSERT INTO tbl_zonas_meta ( ano, mes, zona_id, servicio_id, meta, id_user_created, created_at ) 
                            VALUES ( 
                                '". $ciclo_ano ."', 
                                '". $ciclo_mes ."', 
                                '" . $det["cod_zona"] . "', 
                                '1', 
                                '" . $det["monto_apd"] . "', 
                                '" . $usuario_id . "', 
                                now()
                            );
                        ";
                        $mysqli->query($query_insert_meta);
                    }
                    if((float) $value["meta_jv"] !== (float) $det["monto_jv"]){
                        $query_insert_meta = "
                            INSERT INTO tbl_zonas_meta ( ano, mes, zona_id, servicio_id, meta, id_user_created, created_at ) 
                            VALUES ( 
                                '". $ciclo_ano ."', 
                                '". $ciclo_mes ."', 
                                '" . $det["cod_zona"] . "', 
                                '3', 
                                '" . $det["monto_jv"] . "', 
                                '" . $usuario_id . "', 
                                now()
                            );
                        ";
                        $mysqli->query($query_insert_meta);
                    }
                    if((float) $value["meta_bingo"] !== (float) $det["monto_bingo"]){
                        $query_insert_meta = "
                            INSERT INTO tbl_zonas_meta ( ano, mes, zona_id, servicio_id, meta, id_user_created, created_at ) 
                            VALUES ( 
                                '". $ciclo_ano ."', 
                                '". $ciclo_mes ."', 
                                '" . $det["cod_zona"] . "', 
                                '9', 
                                '" . $det["monto_bingo"] . "', 
                                '" . $usuario_id . "', 
                                now()
                            );
                        ";
                        $mysqli->query($query_insert_meta);
                    }
                }
            }
        }
    } else {
        $result["http_code"] = 400;
        $result["status"] = "Ocurrió un error al realizar el registro.";
        $result["result"] = $list_1;
    }


}




//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_transacciones_x_zona") {

    $cod_zona = $_POST["cod_zona"];
    $ano = $_POST["ano"];
    $mes = str_pad($_POST['mes'], 2, "0", STR_PAD_LEFT);

    $query ="
    SELECT
        zm.id cod,
        zm.servicio_id cod_producto,
        ( CASE zm.servicio_id WHEN 1 THEN 'APUESTAS DEPORTIVAS' WHEN 3 THEN 'JUEGOS VIRTUALES' WHEN 9 THEN 'BINGO' ELSE '' END ) producto,
        zm.meta,
        UPPER( IFNULL( u.usuario, 'SISTEMA' ) ) usuario,
        zm.created_at registro 
    FROM
        tbl_zonas_meta zm
        LEFT JOIN tbl_usuarios u ON u.id = zm.id_user_created 
    WHERE
        zm.zona_id = $cod_zona 
        AND zm.ano = '".$ano."' 
        AND zm.mes = '".$mes."' 
    ORDER BY zm.created_at
    ";
    //echo $query;
    $list_query=$mysqli->query($query);
    $list=array();
    while ($li=$list_query->fetch_assoc()) {
        $list[]=$li;
    }
    if(count($list)==0){
        $result["http_code"] = 400;
        $result["status"] ="No tiene transacciones.";
        $result["result"] =$list;
    } elseif (count($list)>0) {
        $result["http_code"] = 200;
        $result["status"] = "ok";
        $result["result"] =$list;
    } else {
        $result["http_code"] = 400;
        $result["status"] ="Ocurrió un error al consultar las transacciones.";
        $result["result"] =$list;
    }
}



echo json_encode($result);

?>


