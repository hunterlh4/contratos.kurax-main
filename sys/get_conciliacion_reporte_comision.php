<?php
include("db_connect.php");
include("sys_login.php");
require('/var/www/html/sys/globalFunctions/generalInfo/local.php');

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_reporte_comision_Datatable") {

    $fecha_inicio = '2024-01-01';

    $meses = [];

    $date1 = $fecha_inicio;
    $date2 = date("Y-m-d");
    $time = strtotime($date1);
    $last = date('Y-m', strtotime($date2));
    //$canales_de_venta = [16, 17, 21, 30, 34];

    do {
        $month = date('Y-m', $time);
        $total = date('t', $time);
        $meses[] = $month;
        $time = strtotime('+1 month', $time);
    } while ($month != $last);

    $data = [];
    $cabecera_where = "";
    //$cabecera_where = "AND c.canal_de_venta_id in (" . implode(",", $canales_de_venta) . ")";

    $concepto = "APOSTADO";

    if (isset($_POST["concepto"]) && !empty($_POST["concepto"])) {
        $concepto = $_POST["concepto"];
    }

    $locales_command_where = "";
    
    if (isset($_POST["locales"]) && !empty($_POST["locales"])) {
        $cabecera_where .= " AND p.proveedor_id =" . $_POST["locales"];
        $locales_command_where .= " AND l.id = " .$_POST["locales"];
    }

    $proveedor_id = $_POST['locales'];


    if (
        isset($_POST['canales_de_venta']) &&
        !empty($_POST['canales_de_venta']) &&
        is_array($_POST['canales_de_venta'])) {

        if (!in_array("all", $_POST['canales_de_venta'], true)) {
            $cabecera_where .= " AND f.formula_id IN ('" . implode("','", $_POST['canales_de_venta']) . "')";
        } else {
            //$cabecera_where .= " AND f.formula_id IN (" . implode(",", $canales_de_venta) . ")";
        }
    }

    $cabecera_where .= "";

    $locales = []; //LOCALES

    $locales_command = "SELECT
            l.id as proveedor_id,
            l.nombre AS 'PROVEEDOR'
        FROM tbl_conci_proveedor l
        WHERE l.status=1";

    $locales_command .= $locales_command_where;

    $locales_query = $mysqli->query($locales_command);

    if (!empty($mysqli->error)) {
        $return = [
            "error" => $mysqli->error,
            "query" => $locales_command,
        ];
        print_r($return);
        exit;
    }

    $canales_where = "";
    //$canales_where = " AND id IN (" . implode(",", $canales_de_venta) . ")";

    if (
        isset($_POST['canales_de_venta']) &&
        !empty($_POST['canales_de_venta']) &&
        is_array($_POST['canales_de_venta'])) {
        if (!in_array("all", $_POST["canales_de_venta"], true)) {
            $canales_where .= " AND f.formula_id IN ('" . implode("','", $_POST["canales_de_venta"]) . "')";
        }
    }


    //  Consultar tipo de formulas

        $selectQuery = "SELECT 
							tf.metodo
						FROM tbl_conci_proveedor p
                        LEFT JOIN tbl_conci_formula_tipo tf ON tf.id = p.tipo_formula_id
                        LEFT JOIN tbl_moneda m ON m.id = p.comision_moneda_id
						WHERE p.status = '1' AND p.id = ? 
						LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $proveedor_id);
		$selectStmt->execute();
		$selectStmt->store_result();

		if ($selectStmt->num_rows > 0) {
			$selectStmt->bind_result($metodo);
			$selectStmt->fetch();

		}else{
			$return = [
                "error" => $mysqli->error,
                "query" => "Error al obtener las formulas de proveedores",
            ];
            print_r($return);
            exit;
		}
		$selectStmt->close();

    //  Obtener formulas
    $cdv_arr = []; // CANALES DE VENTA

    switch ($metodo){
        case "FormulaFija":
            $cdv_command = "SELECT f.id, 
                                CONCAT(o.nombre, 
                               ' => ',
                               IF(f.comision_porcentual != 0, CONCAT(f.comision_porcentual, '%'), ''),
                               IF(f.comision_fija != 0, CONCAT(' + ', m.simbolo, f.comision_fija), ''), 
                               ' + IGV') AS formula
                            FROM tbl_conci_proveedor_formula f
                            LEFT JOIN tbl_conci_formula_opcion o ON o.id = f.opcion_id
                            LEFT JOIN tbl_conci_proveedor p ON p.id = f.proveedor_id
                            LEFT JOIN tbl_moneda m ON m.id = p.comision_moneda_id
                            WHERE f.status = 1 AND p.id = $proveedor_id $canales_where ORDER BY f.id";
            break;
        case "FormulaEscalonada":

            $cdv_command = "SELECT f.id, 
                                        CONCAT(f.desde, ' a ', f.hasta, ' => ',
                                            IF(f.comision_porcentual != 0, CONCAT(f.comision_porcentual, '%'), ''),
                                            IF(f.comision_fija != 0, CONCAT(' + ', m.simbolo, f.comision_fija), ''), 
                                            ' + IGV') AS formula
                            FROM tbl_conci_proveedor_formula f
                            LEFT JOIN tbl_conci_proveedor p ON p.id = f.proveedor_id
                            LEFT JOIN tbl_moneda m ON m.id = p.comision_moneda_id
                            WHERE f.status = 1 AND p.id = $proveedor_id $canales_where ORDER BY f.id";
            break;
        case "FormulaMixta":
            $cdv_command = "SELECT f.id, 
                                        CONCAT(o.nombre, ' ', f.desde, ' a ', f.hasta,
                                            ' => ',
                                            IF(f.comision_porcentual != 0, CONCAT(f.comision_porcentual, '%'), ''),
                                            IF(f.comision_fija != 0, CONCAT(' + ', m.simbolo, f.comision_fija), ''), 
                                            ' + IGV') AS formula
                            FROM tbl_conci_proveedor_formula f
                            LEFT JOIN tbl_conci_formula_opcion o ON o.id = f.opcion_id
                            LEFT JOIN tbl_conci_proveedor p ON p.id = f.proveedor_id
                            LEFT JOIN tbl_moneda m ON m.id = p.comision_moneda_id
                            WHERE f.status = 1 AND p.id = $proveedor_id $canales_where ORDER BY f.id";
            break;
        default:
            break; 
        }
    $cdv_query = $mysqli->query($cdv_command);

    if (!empty($mysqli->error)) {
        $return = [
            "error" => $mysqli->error,
            "query" => $cdv_command,
        ];
        print_r($return);
        exit;
    }

    while ($cdv = $cdv_query->fetch_assoc()) {
        $cdv_arr[$cdv["id"]] = $cdv;
    }

    $locales2 = [];
    while ($lcl = $locales_query->fetch_assoc()) {
        $locales2[] = $lcl;
        foreach ($cdv_arr as $cdv_id => $cdv) {
            $lcl["CANAL DE VENTA"] = $cdv["formula"];
            $lcl["formula_id"] = $cdv["id"];
            foreach ($meses as $mes) {
                $lcl[$mes] = 0;
            }
            $locales[] = $lcl;
        }
        $lcl["CANAL DE VENTA"] = "TOTAL";
        $locales[] = $lcl;
    }

    $trans_command = "
				SELECT 
					f.periodo_id
                    ,f.formula_id AS formula_id
                    ,p.proveedor_id
                    ,pro.nombre as local_nombre
                    ,DATE_FORMAT(p.periodo, '%Y-%m') AS mes
					,sum(f.count) AS 'Conteo'
					,sum(f.comision_total) AS 'Comision'
				FROM tbl_conci_proveedor_comision f
				LEFT JOIN tbl_conci_periodo p ON p.id = f.periodo_id
                LEFT JOIN tbl_conci_proveedor pro ON pro.id = p.proveedor_id
				WHERE 
				f.status = 1
				AND p.status = 1
				AND p.periodo >= '{$fecha_inicio}'  AND p.periodo <= now()
				{$cabecera_where} 
				GROUP BY DATE_FORMAT(p.periodo,'%Y-%m') ,p.proveedor_id ,f.formula_id
				ORDER BY p.periodo ASC ,local_nombre , f.formula_id";
    $transacciones_query = $mysqli->query($trans_command);
    if (!empty($mysqli->error)) {
        $return = [
            "error" => $mysqli->error,
            "query" => $trans_command,
        ];
        print_r($return);
        exit;
    }
    $locales_transacciones = [];
    while ($lcl = $transacciones_query->fetch_assoc()) {
        $locales_transacciones[$lcl["proveedor_id"]][$lcl["mes"]][$lcl["formula_id"]] = $lcl;
    }

    foreach ($locales2 as $id => $data) {
        $locales2[$id]["liquidaciones"] = isset($locales_transacciones[$data["proveedor_id"]]) ? $locales_transacciones[$data["proveedor_id"]] : [];
    }
    /*sum TOTAL local*/
    foreach ($locales2 as $id => $data) {
        foreach ($data["liquidaciones"] as $key_fecha => $canales) {
            $total = 0;
            $valores_fila = [];
            foreach ($canales as $key => $value) {
                $total += $value[$concepto];
                $valores_fila = $value;
            }
            $valores_fila[$concepto] = $total;
            $valores_fila["formula_id"] = "TOTAL";
            $locales2[$id]["liquidaciones"][$key_fecha]["TOTAL"] = $valores_fila;
        }
    }

    $totales2 = [];
    foreach ($meses as $mes) {
        $totales2[$mes][$concepto] = 0;
    }
    foreach ($locales2 as $proveedor_id => $local_data) {
        foreach ($data["liquidaciones"] as $key_mes => $canales) {
            foreach ($canales as $key_canal => $value) {
                $totales2[$key_mes][$concepto] += $value[$concepto];
            }
        }
    }

    $array_datatable = [];
    foreach ($locales2 as $id => $data_local) {
        foreach ($cdv_arr as $cdv_id => $cdv) /*fill local with cdvs*/ {
            $objeto =
                [
                    "PROVEEDOR" => $data_local["PROVEEDOR"],
                    "CANAL DE VENTA" => $cdv["formula"],
                ];
            foreach ($meses as $mes)//add months columns
            {
                //add month  value
                $valor_mes = 0;
                if (isset($data_local["liquidaciones"])) {
                    if (isset($data_local["liquidaciones"][$mes])) {
                        if (isset($data_local["liquidaciones"][$mes][$cdv["id"]])) {
                            if (isset($data_local["liquidaciones"][$mes][$cdv["id"]][$concepto])) {
                                $valor_mes = $data_local["liquidaciones"][$mes][$cdv["id"]][$concepto];
                            }
                        }
                    }
                }
                $objeto[$mes] = $valor_mes;
            }
            $array_datatable[] = $objeto;
        }

        $objeto =
            [
                "PROVEEDOR" => $data_local["PROVEEDOR"],
                "CANAL DE VENTA" => "TOTAL",
            ];
        foreach ($meses as $mes) {//add month  value
            $valor_mes = 0;
            if (isset($data_local["liquidaciones"])) {
                if (isset($data_local["liquidaciones"][$mes])) {
                    if (isset($data_local["liquidaciones"][$mes]["TOTAL"])) {
                        if (isset($data_local["liquidaciones"][$mes]["TOTAL"][$concepto])) {
                            $valor_mes = $data_local["liquidaciones"][$mes]["TOTAL"][$concepto];
                        }
                    }
                }
            }
            $objeto[$mes] = $valor_mes;
        }
        $array_datatable[] = $objeto;

    }

    $totales = [];
    $data_return["datatable_data"] = $array_datatable;
    $data_return["meses"] = $meses;
    $data_return["totales"] = $totales2;
    $return["data"] = $data_return;
    print_r(json_encode($return));
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_reporte_comision_formula_listar") {
    try {
		

        //  Consultar tipo de formulas

            $proveedor_id = $_POST["proveedor"];
            $selectQuery = "SELECT 
                                tf.metodo
                            FROM tbl_conci_proveedor p
                            LEFT JOIN tbl_conci_formula_tipo tf ON tf.id = p.tipo_formula_id
                            LEFT JOIN tbl_moneda m ON m.id = p.comision_moneda_id
                            WHERE p.status = '1' AND p.id = ? 
                            LIMIT 1";

            $selectStmt = $mysqli->prepare($selectQuery);
            $selectStmt->bind_param("i", $proveedor_id);
            $selectStmt->execute();
            $selectStmt->store_result();

            if ($selectStmt->num_rows > 0) {
                $selectStmt->bind_result($metodo);
                $selectStmt->fetch();

            }else{
                throw new Exception("Error al obtener las formulas de los proveedores");
            }
            $selectStmt->close();

        //  Obtener formulas

        $query = ""; 

            switch ($metodo){
                case "FormulaFija":
                    $query .= "SELECT f.id, 
                                CONCAT(o.nombre, 
                                ' => ',
                                IF(f.comision_porcentual != 0, CONCAT(f.comision_porcentual, '%'), ''),
                                IF(f.comision_fija != 0, CONCAT(' + ', m.simbolo, f.comision_fija), ''), 
                                ' + IGV') AS nombre
                                FROM tbl_conci_proveedor_formula f
                                LEFT JOIN tbl_conci_formula_opcion o ON o.id = f.opcion_id
                                LEFT JOIN tbl_conci_proveedor p ON p.id = f.proveedor_id
                                LEFT JOIN tbl_moneda m ON m.id = p.comision_moneda_id";
                    break;
                case "FormulaEscalonada":
                    $query .= "SELECT f.id, 
                                        CONCAT(f.desde, ' a ', f.hasta,  ' => ',
                                            IF(f.comision_porcentual != 0, CONCAT(f.comision_porcentual, '%'), ''),
                                            IF(f.comision_fija != 0, CONCAT(' + ', m.simbolo, f.comision_fija), ''), 
                                            ' + IGV') AS nombre
                                FROM tbl_conci_proveedor_formula f
                                LEFT JOIN tbl_conci_proveedor p ON p.id = f.proveedor_id
                                LEFT JOIN tbl_moneda m ON m.id = p.comision_moneda_id";
                    break;
                case "FormulaMixta":
                    $query .= "SELECT f.id, 
                                        CONCAT(o.nombre, ' ', f.desde, ' a ', f.hasta,
                                            ' => ',
                                            IF(f.comision_porcentual != 0, CONCAT(f.comision_porcentual, '%'), ''),
                                            IF(f.comision_fija != 0, CONCAT(' + ', m.simbolo, f.comision_fija), ''), 
                                            ' + IGV') AS nombre
                                FROM tbl_conci_proveedor_formula f
                                LEFT JOIN tbl_conci_formula_opcion o ON o.id = f.opcion_id
                                LEFT JOIN tbl_conci_proveedor p ON p.id = f.proveedor_id
                                LEFT JOIN tbl_moneda m ON m.id = p.comision_moneda_id";
                    break;
                default:
                    break; 
            }


        $query .= " WHERE f.status = 1 AND f.proveedor_id = ? ORDER BY f.id";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        $stmt->bind_param("i", $proveedor_id);
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_reporte_comision__listar") {
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