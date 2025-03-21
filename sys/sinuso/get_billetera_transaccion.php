<?php
include("db_connect.php");
include("sys_login.php");


include_once("globalFunctions/generalInfo/local.php");

if (isset($_POST["billetera_transaccion_get_locales"])) {
    $get_data = $_POST["billetera_transaccion_get_locales"];
    $redes = $get_data["redes"];
    $locales = getLocalesByRed($redes, false);
    $return = [
        'status' => 200,
        'locales' => $locales,
    ];
    echo json_encode($return);
    exit();
} else 
if (isset($_POST["billetera_transaccion_get_cajeros"])) {
    $get_data = $_POST["billetera_transaccion_get_cajeros"];
    $locales = $get_data["locales"];
    $cargos = $get_data["cargos"];
    $personales = getPersonales($locales, $cargos, false);
    $return = [
        'status' => 200,
        'personales' => $personales,
    ];
    echo json_encode($return);
    exit();
} else 
if (isset($_POST["billetera_transaccion_get_clientes"])) {
    $get_data = $_POST["billetera_transaccion_get_clientes"];
    $estados = $get_data["estados"];
    $clientes = getClientes($estados);
    $return = [
        'status' => 200,
        'clientes' => $clientes,
    ];
    echo json_encode($return);
    exit();
} else
if (isset($_POST["billetera_transaccion_get_estados"])) {
    $get_data = $_POST["billetera_transaccion_get_estados"];
    $estados = $get_data["estados"];
    $estados = getEstados($estados);
    $return = [
        'status' => 200,
        'estados' => $estados,
    ];
    echo json_encode($return);
    exit();
} else
if (isset($_POST["billetera_transaccion_get_cuentas"])) {
    $get_data = $_POST["billetera_transaccion_get_cuentas"];
    $cuentas = getCuentas();
    $return = [
        'status' => 200,
        'cuentas' => $cuentas,
    ];
    echo json_encode($return);
    exit();
} else
if (isset($_POST["sec_billetera_transaccion_listar_transacciones_en_revision"])) {
    $get_data = $_POST["sec_billetera_transaccion_listar_transacciones_en_revision"];

    $result_transacciones = getTransacciones($get_data);
    $result = formatDataTableTransacciones($result_transacciones);

    echo json_encode($result);
    exit();
} else
if (isset($_POST["billetera_transaccion_registrar"])) {
    $get_data = $_POST["billetera_transaccion_registrar"];

    $result = registrarTransaccion($get_data);

    echo json_encode($result);
    exit();
} else
if (isset($_POST["get_transaccion"])) {
    $get_data = $_POST["get_transaccion"];

    $result = getTransacciones($get_data);

    echo json_encode($result);
    exit();
} else
if (isset($_POST["update_transaccion"])) {
    $get_data = $_POST["update_transaccion"];

    $result = update_transaccion($get_data);

    echo json_encode($result);
    exit();
} else
if (isset($_POST["get_motivos_rechazo"])) {
    $get_data = $_POST["get_motivos_rechazo"];

    $motivos = get_motivos_rechazo();

    $return = [
        'status' => 200,
        'motivos' => $motivos,
    ];
    echo json_encode($return);
    exit();
}

function get_motivos_rechazo(){
    global $mysqli;

    $query = "  SELECT
                    *
                from
                    tbl_billetera_motivos_rechazo
                where
                    status = 1;
	";

    $result_query = $mysqli->query($query);
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}

function update_transaccion($transaccion){
    global $mysqli;
    global $login;
    $login_id = $login['id'];

    $id = $transaccion['id'];
    
    $query = "  UPDATE
                    tbl_billetera_transacciones
                set";

    if(isset($transaccion['fecha_deposito'])){
        $fecha_deposito = $transaccion['fecha_deposito'];
        $query .= " fecha_deposito = '" . $fecha_deposito."',";
    }
    if(isset($transaccion['nombre_depositante'])){
        $nombre_depositante = $transaccion['nombre_depositante'];
        $query .= " nombre_depositante = '" . $nombre_depositante . "',";
    }
    if(isset($transaccion['monto_deposito'])){
        $monto_deposito = $transaccion['monto_deposito'];
        $query .= " monto_deposito = " . $monto_deposito . ",";
    }
    if(isset($transaccion['telefono_id'])){
        $telefono_id = $transaccion['telefono_id'];
        $query .= " billetera_telefono_id = " . $telefono_id . ",";
    }
    if(isset($transaccion['numero_operacion'])){
        $numero_operacion = $transaccion['numero_operacion'];
        $query .= " numero_operacion = " . $numero_operacion . ",";
    }
    if(isset($transaccion['observacion'])){
        $observacion = $transaccion['observacion'];
        $query .= " observacion = '" . $observacion . "', ";
    }
    if(isset($transaccion['estado_transaccion_id'])){
        $estado_transaccion_id = $transaccion['estado_transaccion_id'];
        $query .= " estado_transaccion_id = " . $estado_transaccion_id . ",";
    }
    if(!empty($transaccion['motivo_rechazo_id'])){
        $motivo_rechazo_id = $transaccion['motivo_rechazo_id'];
        $query .= " motivo_rechazo_id = " . $motivo_rechazo_id . ",";
    }
    if(!empty($transaccion['otro_motivo_rechazo'])){
        $otro_motivo_rechazo = $transaccion['otro_motivo_rechazo'];
        $query .= " motivo_rechazo_id = NULL,";
        $query .= " otro_motivo_rechazo = '" . $otro_motivo_rechazo . "',";
    }
    if(isset($transaccion['revision'])){
        $usuario_revision_id = $login['id'];
        $query .= " usuario_revision_id = " . $usuario_revision_id . ",";
        $query .= " fecha_revision = now(),";
        $query .= " fecha_validacion = now(),";
    }
    
    $query .= " user_updated_id = " . $login_id . ", 
                updated_at = now()
                where
                    id = $id";

    $update = $mysqli->query($query);
    
    if ($update) {
        $result = [
            'status' => 200,
            'result' => true
        ];
    } else {
        $result = [
            'status' => 503,
            'message' => 'ERROR:' . $mysqli->error . ' QUERY: ' . $query
        ];
    }

    return $result;

}

function formatDataTableTransacciones($result_transacciones)
{

    global $mysqli;
    $result = [];
    $data = [];

    if ($result_transacciones['status'] == 503) {
        error_log("Error en la consulta: " . $mysqli->error);
        $data[] = [
            "0" => "error",
            "1" => '',
            "2" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "3" => '',
            "4" => '',
            "5" => '',
            "6" => '',
            "7" => '',
            "8" => '',
            "9" => '',
            "10" => '',
            "11" => '',
        ];

        $result = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else if($result_transacciones['status'] == 200) {

        if(!empty($result_transacciones['data'])){
            foreach ($result_transacciones['data'] as $r) {
                $row = [
                    "0" => $r['id'],
                    "1" => date('d/m/Y H:i:s', strtotime($r['fecha_deposito'])),
                    "2" => $r['nombre_depositante'],
                    "3" => $r['monto_deposito'],
                    "4" => date('d/m/Y H:i:s', strtotime($r['fecha_registro'])),
                    "5" => $r['cajero_creador'],
                    "6" => $r['numero_operacion'],
                    "7" => $r['nombre_corto'], //  . ' - ' . $r['numero_telefono'],
                    "8" => $r['local_nombre'],
                    "9" => $r['usuario_revisor'],
                    "10" => $r['estado_descripcion'],
                ];
                
                if($r['estado_id'] == 3){
                    $row["11"] = '
                                <!--button class="btn btn-xs btn-success btn-registrar-billetera-transaccion" title="Registar" data-id="' . $r['id'] . '"><span class="fa fa-check"></span></button-->
                                <button class="btn btn-xs btn-success btn-editar-billetera-transaccion" title="Revisar" data-id="' . $r['id'] . '"><span class="fa fa-check"></span></button>';
                } else {
                    $row["11"] = '';
                }

                $data[] = $row;
            }
        }

        $result['sEcho'] = 1;
        $result['iTotalRecords'] = count($data);
        $result['iTotalDisplayRecords'] = count($data);
        $result['aaData'] = $data;
    }

    return  $result;
}

function getTransacciones($filtros)
{
    global $mysqli;
    $result = [];

    $where = '';
    if (!empty($filtros['transaccion_id'])) {
        $transaccion_id = $filtros['transaccion_id'];
        $where .= " AND t.id = $transaccion_id";
    }
    if (!empty($filtros['fecha'])) {
        $fecha = $filtros['fecha'];
        $where .= " AND DATE_FORMAT(t.created_at, '%Y-%m-%d') = '$fecha'";
    }
    if (!empty($filtros['monto_desde'])) {
        $monto_desde = $filtros['monto_desde'];
        $where .= " AND t.monto_deposito >= $monto_desde";
    }
    if (!empty($filtros['monto_hasta'])) {
        $monto_hasta = $filtros['monto_hasta'];
        $where .= " AND t.monto_deposito <= $monto_hasta";
    }
    if (!empty($filtros['cliente'])) {
        $cliente = $filtros['cliente'];
        $where .= " AND t.nombre_depositante = '$cliente'";
    }
    if (!empty($filtros['cajero_id'])) {
        $cajero_id = $filtros['cajero_id'];
        $where .= " AND u_creater.id = $cajero_id";
    }
    if (!empty($filtros['local_id'])) {
        $local_id = $filtros['local_id'];
        $where .= " AND t.local_id = $local_id";
    }
    if (!empty($filtros['cuenta_id'])) {
        $cuenta_id = $filtros['cuenta_id'];
        $where .= " AND te.id = $cuenta_id";
    }
    if (!empty($filtros['estado'])) {
        $estado = $filtros['estado'];
        $where .= " AND t.estado_transaccion_id = $estado";
    }

    $query = "  SELECT
                    t.id,
                    t.fecha_deposito,
                    nombre_depositante,
                    t.monto_deposito,
                    t.numero_operacion,
                    t.created_at as fecha_registro,
                    u_creater.usuario as cajero_creador,
                    IFNULL(u_revisor.usuario, '') as usuario_revisor,
                    r.nombre,
                    e.id as estado_id,
                    e.descripcion as estado_descripcion,
                    c.nombre_corto,
                    b.nombre as banco_nombre,
                    c.numero_cuenta,
                    l.nombre as local_nombre,
                    te.numero_telefono,
                    te.id as telefono_id,
                    t.observacion as observacion
                FROM tbl_billetera_transacciones t
                    inner join tbl_billetera_telefonos te on t.billetera_telefono_id = te.id
                    inner join tbl_billetera_cuentas c on te.billetera_cuenta_id = c.id
                    inner join tbl_billetera_transacciones_estados e on t.estado_transaccion_id = e.id 
                    inner join tbl_bancos b on c.banco_id = b.id
                    inner join tbl_usuarios u_creater on t.user_created_id = u_creater.id
                    left join tbl_usuarios u_revisor on t.usuario_revision_id = u_revisor.id
                    inner join tbl_billetera_registro r on t.billetera_registro_id = r.id
                    inner join tbl_locales l on t.local_id = l.id
                where 
                    t.status = 1
                    and t.billetera_registro_id = 2
                    {$where}
                ORDER BY t.id desc
                    ";

    $data = [];
    $query_data = $mysqli->query($query);
    if ($mysqli->error) {
        $result = [
            'status' => 503,
            'message' => 'ERROR:' . $mysqli->error . ' QUERY: ' . $query
        ];
    } else {
        while ($t = $query_data->fetch_assoc()) {
            $t['fecha'] = date('Y-m-d', strtotime($t['fecha_deposito']));
            $t['fecha_format'] = date('d-m-Y', strtotime($t['fecha_deposito']));
            $t['hora'] = date('H:i', strtotime($t['fecha_deposito']));
            $transacciones[] = $t;
        }

        $result = [
            'status' => 200,
            'message' => 'OK',
            'data' => $transacciones
        ];
    }

    return $result;
}
function getEstados()
{
    global $mysqli;

    $query = "  SELECT 
                    id,
                    descripcion
                from tbl_billetera_transacciones_estados
                where 
                    status = 1
	";

    $result_query = $mysqli->query($query);
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}
function getClientes($estados)
{
    global $mysqli;
    global $login;

    $filtro_estados   = "";
    if (!empty($estados)) {
        $filtro_estados    = " AND estado_transaccion_id IN (" . implode(",", $estados) . ") ";
    }
    $query = "  SELECT 
                    DISTINCT(nombre_depositante) as cliente_nombre
                from tbl_billetera_transacciones
                where 
                    status = 1
                    $filtro_estados
                ORDER BY nombre_depositante ASC
	";

    $result_query = $mysqli->query($query);
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}

function getCuentas()
{
    global $mysqli;

    $query = "  SELECT 
                    t.id, 
                    c.numero_cuenta, 
                    c.nombre_corto, 
                    b.nombre as banco_nombre,
                    t.numero_telefono
                from tbl_billetera_telefonos t
                    inner join tbl_billetera_cuentas c on t.billetera_cuenta_id = c.id
                    inner join tbl_bancos b on b.id = c.banco_id
                where
                    c.status = 1
                    and t.status = 1;
	";

    $result_query = $mysqli->query($query);
    $data_return = array();
    while ($r = $result_query->fetch_assoc()) {
        $data_return[] = $r;
    }
    return $data_return;
}


function registrarTransaccion($data)
{
    global $mysqli;
    global $login;

    $usuario_id = $login['id'];
    $transaccion_id = $data['id'];
    $estado_transaccion_id = $data['estado_transaccion_id'];

    $query = "  UPDATE tbl_billetera_transacciones
                    set estado_transaccion_id = $estado_transaccion_id,
                    usuario_revision_id = $usuario_id,
                    fecha_revision = now()
    ";

    if(!empty($data['motivo_rechazo_id'])){
        $motivo_rechazo_id = $data['motivo_rechazo_id'];
        $query .= ", motivo_rechazo_id = $motivo_rechazo_id";   
    }

    if(!empty($data['otro_motivo'])){
        $otro_motivo = $data['otro_motivo'];
        $query .= ", otro_motivo = $otro_motivo";
    }


    $query .= " WHERE
                    id = $transaccion_id
                    and estado_transaccion_id = 3;
	";

    $update = $mysqli->query($query);
    if ($update) {
        $result = [
            'status' => 200,
            'result' => true
        ];
    } else {
        $result = [
            'status' => 503,
            'message' => 'ERROR:' . $mysqli->error . ' QUERY: ' . $query
        ];
    }

    return $result;
}
