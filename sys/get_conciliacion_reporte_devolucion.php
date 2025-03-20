<?php
include("db_connect.php");
include("sys_login.php");
require('/var/www/html/sys/globalFunctions/generalInfo/local.php');

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_reporte_devolucion_Datatable") {
    $fecha_inicio = '2024-01-01';
    $meses = [];
    $date1 = $fecha_inicio;
    $date2 = date("Y-m-d");
    $time = strtotime($date1);
    $last = date('Y-m', strtotime($date2));
    do {
        $month = date('Y-m', $time);
        $meses[] = $month;
        $time = strtotime('+1 month', $time);
    } while ($month != $last);

    $data = [];
    $cabecera_where = "AND c.id IS NOT NULL";
    $locales_command_where = "AND l.id IS NOT NULL";

    $concepto = "GASTOS";

    $column_titles = [
        "monto_devolucion" => "Monto de Devolución",
        "comision_devolucion" => "Comisión de Devolución",
        "devolucion_count" => "Conteo Devolución"
    ];

    $gastos_columns = array_keys($column_titles);
    $filtered_columns = $gastos_columns;

    if (array_key_exists("filtro", $_POST)) {
        $filtro = $_POST["filtro"];

        if (array_key_exists("locales", $filtro) && $filtro["locales"] && !in_array("all", $filtro["locales"])) {
            $cabecera_where .= " AND c.proveedor_id IN ('" . implode("','", $filtro["locales"]) . "')";
            $locales_command_where .= " AND l.id IN ('" . implode("','", $filtro["locales"]) . "')";
        }

        if (array_key_exists("canales_de_venta", $filtro)) {
            if ($filtro["canales_de_venta"]) {
                if (!in_array("all", $filtro["canales_de_venta"])) {
                    $filtered_columns = array_filter($gastos_columns, function($col) use ($filtro) {
                        return in_array($col, $filtro["canales_de_venta"]);
                    });
                }
            }
        }
    }
    $cabecera_where .= " AND c.status = 1";

    $locales = [];

    // Mostrar locales
    $locales_command = "SELECT
            l.id as local_id,
            l.nombre AS 'PROVEEDOR'
        FROM tbl_conci_proveedor l
        WHERE 1 = 1 AND l.status=1 ";
    $locales_query = $mysqli->query($locales_command . $locales_command_where);
    if ($mysqli->error) {
        $return["ERROR_MYSQL"] = $mysqli->error;
        print_r($mysqli->error);
        echo $locales_command;
    }

    $locales2 = [];
    while ($lcl = $locales_query->fetch_assoc()) {
        $locales2[] = $lcl;
        foreach ($filtered_columns as $gasto_column) {
            $lcl["CANAL DE VENTA"] = $column_titles[$gasto_column];
            foreach ($meses as $mes) {
                $lcl[$mes] = 0;
            }
            $locales[] = $lcl;
        }
    }

    $column_list = implode(", ", $filtered_columns);
    $trans_command = "
                SELECT 
                    c.proveedor_id,
                    l.nombre as local_nombre,
                    DATE_FORMAT(c.periodo, '%Y-%m') AS mes,
                    $column_list
                FROM tbl_conci_periodo c
                LEFT JOIN tbl_conci_proveedor l ON l.id = c.proveedor_id
                WHERE 
                c.status = 1 
                AND l.status = 1
                AND c.id IS NOT NULL
                AND c.periodo >= '{$fecha_inicio}' AND c.periodo <= now()
                $locales_command_where
                $cabecera_where
                ORDER BY c.periodo ASC, local_nombre";
    $transacciones_query = $mysqli->query($trans_command);
    if ($mysqli->error) {
        $return["ERROR_MYSQL"] = $mysqli->error;
        print_r($mysqli->error);
    }
    $locales_transacciones = [];
    while ($lcl = $transacciones_query->fetch_assoc()) {
        foreach ($filtered_columns as $gasto_column) {
            $locales_transacciones[$lcl["proveedor_id"]][$lcl["mes"]][$gasto_column] = $lcl[$gasto_column];
        }
    }

    foreach ($locales2 as $id => $data) {
        $locales2[$id]["liquidaciones"] = isset($locales_transacciones[$data["local_id"]]) ? $locales_transacciones[$data["local_id"]] : [];
    }

    // Calcula los totales por mes
    $totales2 = [];
    foreach ($meses as $mes) {
        $totales2[$mes][$concepto] = 0;
    }
    foreach ($locales2 as $local_id => $local_data) {
        foreach ($local_data["liquidaciones"] as $key_mes => $canales) {
            foreach ($canales as $key_canal => $value) {
                $totales2[$key_mes][$concepto] += $value;
            }
        }
    }
    $array_datatable = [];
    foreach ($locales2 as $id => $data_local) {
        foreach ($filtered_columns as $gasto_column) {
            $objeto = [
                "PROVEEDOR" => $data_local["PROVEEDOR"],
                "CANAL DE VENTA" => $column_titles[$gasto_column],
            ];
            foreach ($meses as $mes) { // add months columns
                $valor_mes = 0;
                if (isset($data_local["liquidaciones"])) {
                    if (isset($data_local["liquidaciones"][$mes])) {
                        if (isset($data_local["liquidaciones"][$mes][$gasto_column])) {
                            $valor_mes = $data_local["liquidaciones"][$mes][$gasto_column];
                        }
                    }
                }
                $objeto[$mes] = $valor_mes;
            }
            $array_datatable[] = $objeto;
        }
    }
    
    // No incluir los totales en la salida
    $data_return["datatable_data"] = $array_datatable;
    $data_return["meses"] = $meses;
    $data_return["totales"] = []; // Asegúrate de que 'totales' esté vacío
    $return["data"] = $data_return;
    print_r(json_encode($return));
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consolidado_gastos_obtener_conceptos") {
    try {
        $query = "SELECT id, codigo, nombre FROM tbl_gastos_conceptos WHERE estado = '1' ORDER BY nombre ASC";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        $stmt->execute();

        $list_query = $stmt->get_result();
        $list = $list_query->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        if (count($list) === 0) {
            $result["http_code"] = 400;
            $result["result"] = "El concepto no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $list;
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consolidado_gastos_obtener_proveedor") {
    try {
		
        $query = "SELECT 
                    ul.id,
                    ul.nombre
                FROM tbl_conci_proveedor ul             
                WHERE
					ul.status = 1
                ORDER BY ul.nombre ASC";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        //$stmt->bind_param("i", $supervisor_id);
        $stmt->execute();

        $list_query = $stmt->get_result();
        $list = $list_query->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        if (count($list) === 0) {
            $result["http_code"] = 400;
            $result["result"] = "El local no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $list;
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
}
?>