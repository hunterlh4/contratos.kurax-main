<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/phpexcel/classes/PHPExcel.php';
require_once '/var/www/html/sys/helpers.php';

if(isset($_POST["set_cobranzas_estados_de_cuenta_diferencia"])){
    global $login;
	extract($_POST);

    $periodo_year = $_POST['periodo_year'];
    $periodo_mes = $_POST['periodo_mes'];
    $periodo_rango = $_POST['periodo_rango'];
    $periodo_inicio = $_POST['periodo_inicio'];
    $periodo_fin = $_POST['periodo_fin'];
    $periodo_rango_int = $_POST['periodo_rango_int'];
    $local_id = $_POST['local_id'];
    $periodo_id = $_POST['periodo_id'];
    $monto = $_POST['ajuste'];
    $descripcion = $_POST['descripcion'];

    if(!isset($_POST['ajuste']) || $_POST['ajuste'] == ""){
		$return["error"] = "ajuste";
		$return["error_msg"] = "El campo ajuste es obligatorio.";
		die(json_encode($return));
	}
    if(!isset($_POST['descripcion']) || $_POST['descripcion'] == ""){
		$return["error"] = "descripcion";
		$return["error_msg"] = "El campo descripción es obligatorio.";
		die(json_encode($return));
	}

    $insert_command = "INSERT INTO tbl_deudas 
    (at_unique_id,fecha_ingreso,periodo_year,periodo_mes,periodo_rango,periodo_inicio,periodo_fin,periodo_rango_int,canal_de_venta_id,local_id,tipo_id,monto,saldo,descripcion,estado,periodo_liquidacion_id,estado_liquidacion)
    VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    try{
        $prepare_query = mysqli_prepare($mysqli, $insert_command);
        if(!$prepare_query){
            $return['error'] = 'error';
            $return['error_msg'] = "Error en inserción a estados de cuenta ajuste.";
            $return['error_query'] = mysqli_error($mysqli);
            echo json_encode($return);
            die();
        }

        date_default_timezone_set('America/Lima');
        $fecha_actual = new DateTime();
        $formato = "Y-m-d H:i:s";
        $fecha_ingreso = $fecha_actual->format($formato);
        $estado = 1;
        $estado_liquidacion = 1;
        $canal_de_venta_id = 0;
        $tipo_id = 5;
        $saldo = 0.00;
        
        $at_unique_id = md5($fecha_ingreso.$local_id.$canal_de_venta_id.$tipo_id);

        mysqli_stmt_bind_param($prepare_query, 'ssissssiiiiddsiii', 
            $at_unique_id,
            $fecha_ingreso,
            $periodo_year,
            $periodo_mes,
            $periodo_rango,
            $periodo_inicio,
            $periodo_fin,
            $periodo_rango_int,
            $canal_de_venta_id,
            $local_id,
            $tipo_id,
            $monto,
            $saldo,
            $descripcion,
            $estado,
            $periodo_id,
            $estado_liquidacion
        );

        $result = mysqli_stmt_execute($prepare_query);

        if (!$result) {
            $return['error'] = 'error';
            $return['error_query'] = mysqli_error($mysqli);
            echo json_encode($return);
            die();
        }

        mysqli_stmt_close($prepare_query);
        
    }catch (Exception $e) {
        $error_message = "Error en la inserción: " . $e->getMessage();
        $return['error'] = 'error';
        $return['error_query'] = $error_message;
        echo json_encode($return);
        die();
    }

    $return['msg'] = 'Ajuste correcto en estados de cuenta';

    // ACTUALIZAR TABLA ESTADOS DE CUENTA
    $fecha_inicio = '2021-01-01';
    $hoy = new DateTime();
    $hoy->modify('+1 day');
    $fecha_fin = $hoy->format('Y-m-d');
    $process = process_actualizar_estados_cuenta($fecha_inicio, $fecha_fin, $local_id);
    $return['process'] = ($process == '') ? 'success' : 'error';
    $return["curr_login"] = $login;
	
}

function process_actualizar_estados_cuenta($fecha_inicio, $fecha_fin, $local_id) {
    global $mysqli;
    $result = "";

    $fecha_inicio_deuda = $fecha_inicio;
    $deuda_anterior = "0";

    $sql_deudas = "
    SELECT
        (
            SELECT IF(SUM(d.monto),SUM(d.monto),0) AS monto_deuda
            FROM tbl_deudas d
            WHERE
                d.local_id = {$local_id}
                AND d.estado = 1
                AND d.fecha_ingreso > '$fecha_inicio_deuda'
                AND d.fecha_ingreso < '$fecha_fin'
        )AS monto_deuda
        ,NOW() as fecha_busqueda
    ";
    $deudas_q = $mysqli->query($sql_deudas);

    $montos = array();
    while ($row_montos = $deudas_q->fetch_assoc()) {
        $montos = $row_montos;
    }

    $sql_insert = "
        INSERT INTO tbl_estados_cuenta
        (
            id_local
            ,deuda
            ,update_fecha_deuda
        )
        VALUES 
        (
            {$local_id}
            ,{$montos['monto_deuda']}
            ,'{$montos['fecha_busqueda']}'
        )   
        ON DUPLICATE KEY UPDATE
            deuda = $deuda_anterior + {$montos['monto_deuda']}
            ,update_fecha_deuda = '{$montos['fecha_busqueda']}'
    ";
    $mysqli->query($sql_insert);

    $sql_update_deudas = "
        UPDATE tbl_deudas
        SET estado_liquidacion = 1
        WHERE
            local_id IN ($local_id)
            AND estado = 1
            AND fecha_ingreso > '$fecha_inicio_deuda'
            AND fecha_ingreso < '$fecha_fin'
            AND (estado_liquidacion IS NULL OR estado_liquidacion != 1)
    ";
    // $result.= "<br>$sql_update_deudas</br>";
    $mysqli->query($sql_update_deudas);
    if($mysqli_error = $mysqli->error){
        $result.= "error update tbl_deudas: ".$mysqli_error;
        exit();
    }

    return $result;
}

if(isset($_POST["sec_cobranzas_diferencia_detalle"])){
	extract($_POST);
    $local_id = $_POST['local_id'];
    $periodo_id = $_POST['periodo_id'];

    $command_periodo_fechas = "SELECT
        fecha_inicio,
        fecha_fin
    from 
    tbl_periodo_liquidacion
    where id = {$periodo_id}";
    $result_periodo_fechas = $mysqli->query($command_periodo_fechas)->fetch_assoc();
    if($mysqli->error){
        print_r($mysqli->error);
        exit();
    }
    $fecha_inicio = $result_periodo_fechas["fecha_inicio"];
    $fecha_fin = $result_periodo_fechas["fecha_fin"];

    $command_liquidaciones_fg = "SELECT
        SUM(cab.total_freegames + cab.total_caja_web) AS part_fg
    FROM
        tbl_transacciones_cabecera cab
    WHERE
        cab.fecha >= '$fecha_inicio' AND cab.fecha <= '$fecha_fin'
        AND cab.estado = 1
        AND cab.servicio_id IN (1,3,9,13,15)
        AND cab.local_id = {$local_id}
    GROUP BY
        cab.local_id";
    $result_liquidaciones_fg= $mysqli->query($command_liquidaciones_fg);
    if($mysqli->error){
        print_r($mysqli->error);
        exit();
    }
    while ($row = $result_liquidaciones_fg->fetch_assoc()) {
        $liquidaciones_fg = $row['part_fg'];
    }

    $command_total_deuda = "SELECT sum(monto) as total_deuda from tbl_deudas 
    where local_id = {$local_id}
    and estado = 1 
    and periodo_liquidacion_id = {$periodo_id}
    group by local_id";
    $result_total_deuda= $mysqli->query($command_total_deuda);
    if($mysqli->error){
        print_r($mysqli->error);
        exit();
    }
    while ($row = $result_total_deuda->fetch_assoc()) {
        $total_deuda = $row['total_deuda'];
    }

    $command_periodo_rango = "SELECT fecha_inicio, fecha_fin from tbl_periodo_liquidacion where id = {$periodo_id}";
    $result_periodo_rango = $mysqli->query($command_periodo_rango)->fetch_assoc();
    $list_fecha_inicio = $result_periodo_rango["fecha_inicio"];
    $list_fecha_fin = $result_periodo_rango["fecha_fin"];
    list($year, $mes, $dia) = explode('-', $list_fecha_inicio);
    
    $list_fecha_inicio_dia = explode('-', $list_fecha_inicio)[2];
    $list_fecha_fin_dia = explode('-', $list_fecha_fin)[2];

    $periodo_rango = "$list_fecha_inicio_dia-$list_fecha_fin_dia";
    $periodo_rango_int = $list_fecha_inicio_dia . $list_fecha_fin_dia;

    $return['liquidaciones_fg'] = $liquidaciones_fg;
    $return['total_deuda'] = $total_deuda;
    $return['periodo_year'] = $year;
    $return['periodo_mes'] = $mes;
    $return['periodo_rango'] = $periodo_rango;
    $return['periodo_inicio'] = $list_fecha_inicio;
    $return['periodo_fin'] = $list_fecha_fin;
    $return['periodo_rango_int'] = $periodo_rango_int;
    $return['local_id'] = $local_id;
    $return['periodo_id'] = $periodo_id;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>