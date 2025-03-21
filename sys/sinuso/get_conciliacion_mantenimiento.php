<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

//////////   FUNCIONES PARA PROVEEDORES

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_listar")
{

    $estado_id = $_POST["estado_id"];

    $where_empresa = "";
    $where_estado = "";

    if($estado_id != ""){
        $where_estado = " WHERE cc.status = $estado_id ";
    }

    //-----------------------------------------------------------------------------------
	$query = "
        SELECT
            cc.id,
            cc.nombre AS nombre,
            cc.created_at,
            u.usuario,
            cc.status
        FROM tbl_conci_proveedor cc
        LEFT JOIN tbl_usuarios u ON cc.user_created_id = u.id
        $where_estado
        ORDER BY cc.created_at ASC
	";

	$list_query = $mysqli->query($query);

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{

        $estadoHTML = ($reg->status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

        $botones_accion = "";

        if($reg->status == 1){
            $botones_accion = '<a onclick="sec_conci_mant_proveedor_obtener('.$reg->id.');";
                                    class="btn btn-warning btn-sm"
                                    data-toggle="tooltip" data-placement="top" title="Editar">
                                    <span class="fa fa-pencil"></span>
                                </a>
                                <a onclick="sec_conci_mant_proveedor_eliminar('.$reg->id.');" 
                                    class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Eliminar"> 
                                    <span class="fa fa-trash"></span>
                                </a>';
        }

        $botones = '<a onclick="sec_conci_mant_proveedor_ver('.$reg->id.');";
                        class="btn btn-info btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Ver">
                        <span class="fa fa-eye"></span>
                    </a>
                   '.$botones_accion;                

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->created_at,
			"3" => $reg->usuario,
			"4" => $estadoHTML,
			"5" => $botones
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

function obtenerEstadosProveedor($mysqli, $provider_id) {

    $stmtEstados = $mysqli->prepare("
        SELECT 
            id,
            nombre,
            estado
        FROM tbl_conci_proveedor_estado
        WHERE proveedor_id = ? AND status = 1
    ");

    if (!$stmtEstados) {
        throw new Exception("Error al preparar la consulta de estados del proveedor.");
    }

    $stmtEstados->bind_param("i", $provider_id);

    if (!$stmtEstados->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmtEstados->bind_result($estado_id, $estado_nombre, $estado_status);

    $estados = [];

    while ($stmtEstados->fetch()) {
        $estados[] = [
            'id' => $estado_id,
            'nombre' => $estado_nombre,
            'status' => $estado_status
        ];
    }

    $stmtEstados->close();

    return $estados;
}

function obtenerCuentasBancariasProveedor($mysqli, $provider_id) {

    $stmt = $mysqli->prepare("
        SELECT 
            id,
            banco_id,
            moneda_id,
            cuenta_corriente,
            cuenta_interbancaria,
            status
        FROM tbl_conci_proveedor_cuenta_bancaria
        WHERE proveedor_id = ?
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de cuentas bancarias del proveedor.");
    }

    $stmt->bind_param("i", $provider_id);

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmt->bind_result($id, $banco_id,$moneda_id, $cuenta_corriente, $cuenta_interbancaria, $status);

    $cuentasBancarias = [];

    while ($stmt->fetch()) {
        $cuentasBancarias[] = [
            'id' => $id,
            'banco_id' => $banco_id,
            'moneda_id' => $moneda_id,
            'cuenta_corriente' => $cuenta_corriente,
            'cuenta_interbancaria' => $cuenta_interbancaria,
            'status' => $status
        ];
    }

    $stmt->close();

    return $cuentasBancarias;
}


function obtenerColumnasProveedor($mysqli, $provider_id, $tipo_archivo_id) {

    $stmtColumnas = $mysqli->prepare("
        SELECT 
            id,
            nombre,
            formato_id,
            orden,
            columna_id,
            prefijo,
            sufijo,
            separador_id,
            nombreColumna_json,
            status
        FROM tbl_conci_proveedor_columna
        WHERE proveedor_id = ? 
        AND status = 1
        AND tipo_archivo_id = ?
        ORDER BY orden ASC
    ");

    if (!$stmtColumnas) {
        throw new Exception("Error al preparar la consulta de estados del proveedor.");
    }

    $stmtColumnas->bind_param("ii", $provider_id, $tipo_archivo_id);

    if (!$stmtColumnas->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmtColumnas->bind_result($id, $nombre, $formato_id, $orden, $columna_id, $prefijo, $sufijo, $separador_id, $nombreColumna_json,$status);

    $columnas = [];

    while ($stmtColumnas->fetch()) {
        $columnas[] = [
            'id' => $id,
            'nombre' => $nombre,
            'formato_id' => $formato_id,
            'orden' => $orden,
            'columna_id' => $columna_id,
            'prefijo' => $prefijo,
            'sufijo' => $sufijo,
            'nombreColumna_json' => $nombreColumna_json,
            'status' => $status
        ];
    }

    $stmtColumnas->close();

    return $columnas;
}

function obtenerFormulaFijaProveedor($mysqli, $provider_id) {

    $stmtFormulas = $mysqli->prepare("
        SELECT 
            id,
            columna_id,
            operador_id,
            opcion_id,
            comision_porcentual,
            comision_fija,
            igv
        FROM tbl_conci_proveedor_formula
        WHERE proveedor_id = ? 
        AND status = 1
    ");

    if (!$stmtFormulas) {
        throw new Exception("Error al preparar la consulta de estados del proveedor.");
    }

    $stmtFormulas->bind_param("i", $provider_id);

    if (!$stmtFormulas->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmtFormulas->bind_result($id, $columna_id, $operador_id, $opcion_id, $comision_porcentual, $comision_fija, $igv);

    $formulas = [];

    while ($stmtFormulas->fetch()) {
        $formulas[] = [
            'id' => $id,
            'columna_id' => $columna_id,
            'operador_id' => $operador_id,
            'opcion_id' => $opcion_id,
            'comision_porcentual' => $comision_porcentual,
            'comision_fija' => $comision_fija,
            'igv' => $igv
        ];
    }

    $stmtFormulas->close();

    return $formulas;
}

function obtenerFormulaEscalonadaProveedor($mysqli, $provider_id) {

    $stmtFormulas = $mysqli->prepare("
        SELECT 
            id,
            desde,
            hasta,
            comision_porcentual,
            comision_fija,
            igv
        FROM tbl_conci_proveedor_formula
        WHERE proveedor_id = ? 
        AND status = 1
    ");

    if (!$stmtFormulas) {
        throw new Exception("Error al preparar la consulta de estados del proveedor.");
    }

    $stmtFormulas->bind_param("i", $provider_id);

    if (!$stmtFormulas->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmtFormulas->bind_result($id, $desde, $hasta, $comision_porcentual, $comision_fija, $igv);

    $formulas = [];

    while ($stmtFormulas->fetch()) {
        $formulas[] = [
            'id' => $id,
            'desde' => $desde,
            'hasta' => $hasta,
            'comision_porcentual' => $comision_porcentual,
            'comision_fija' => $comision_fija,
            'igv' => $igv
        ];
    }

    $stmtFormulas->close();

    return $formulas;
}

function obtenerFormulaMixtaProveedor($mysqli, $provider_id) {

    $stmtFormulas = $mysqli->prepare("
        SELECT 
            id,
            columna_id,
            operador_id,
            opcion_id,
            desde,
            hasta,
            comision_porcentual,
            comision_fija,
            igv
        FROM tbl_conci_proveedor_formula
        WHERE proveedor_id = ? 
        AND status = 1
    ");

    if (!$stmtFormulas) {
        throw new Exception("Error al preparar la consulta de estados del proveedor.");
    }

    $stmtFormulas->bind_param("i", $provider_id);

    if (!$stmtFormulas->execute()) {
        throw new Exception("Error al ejecutar la consulta de estados del proveedor. Comunicarse con soporte.");
    }
    
    $stmtFormulas->bind_result($id, $columna_id, $operador_id, $opcion_id, $desde, $hasta, $comision_porcentual, $comision_fija, $igv);

    $formulas = [];

    while ($stmtFormulas->fetch()) {
        $formulas[] = [
            'id' => $id,
            'columna_id' => $columna_id,
            'operador_id' => $operador_id,
            'opcion_id' => $opcion_id,
            'desde' => $desde,
            'hasta' => $hasta,
            'comision_porcentual' => $comision_porcentual,
            'comision_fija' => $comision_fija,
            'igv' => $igv
        ];
    }

    $stmtFormulas->close();

    return $formulas;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_obtener") {
    $provider_id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($provider_id != NULL) {
        try {

            //  1. DATOS DEL PROVEEDOR

                $stmtProveedor = $mysqli->prepare("
                    SELECT 
                        p.id, 
                        p.nombre,
                        p.nombre_corto,
                        p.tipo_importacion_id,
                        it.metodo,
                        p.tipo_calculo_id,
                        p.tipo_formula_id,
                        IFNULL(p.comision_moneda_id,0),
                        ft.metodo,
                        p.columna_conciliacion_id,
                        IFNULL(p.created_at, ''),
                        IFNULL(p.updated_at, ''),
                        u.usuario AS usuario_create,
                        ua.usuario AS usuario_update
                    FROM tbl_conci_proveedor p
                    LEFT JOIN tbl_conci_importacion_tipo it ON it.id = p.tipo_importacion_id
                    LEFT JOIN tbl_conci_formula_tipo ft ON ft.id = p.tipo_formula_id
                    LEFT JOIN tbl_usuarios u ON u.id=p.user_created_id
                    LEFT JOIN tbl_usuarios ua ON ua.id=p.user_updated_id
                    WHERE p.id=?
                    LIMIT 1
                ");

                $stmtProveedor->bind_param("i", $provider_id);
                if (!$stmtProveedor->execute()) throw new Exception("Error al ejecutar la consulta del proveedor. Comunicarse con soporte.");
                $stmtProveedor->bind_result(
                                    $id, 
                                    $nombre, 
                                    $nombre_corto,
                                    $tipo_importacion_id,
                                    $metodo_importacion,
                                    $tipo_calculo_id,
                                    $tipo_formula_id,
                                    $comision_moneda_id,
                                    $metodo_formula,
                                    $columna_conciliacion_id,
                                    $created_at,
                                    $updated_at,
                                    $usuario_create,
                                    $usuario_update);


                if (!$stmtProveedor->fetch()) throw new Exception("No se encontraron datos del proveedor seleccionado. Comunicarse con soporte.");
                $stmtProveedor->close();

                $response = [
                    'status' => 200,
                    'result' => [
                        'id' => $id,
                        'nombre' => $nombre,
                        'nombre_corto' => $nombre_corto,
                        'metodo_importacion' => $metodo_importacion,
                        'tipo_calculo_id' => $tipo_calculo_id,
                        'tipo_formula_id' => $tipo_formula_id,
                        'comision_moneda_id' => $comision_moneda_id,
                        'metodo_formula' => $metodo_formula,
                        'columna_conciliacion_id' => $columna_conciliacion_id,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'usuario_create' => $usuario_create,
                        'usuario_update' => $usuario_update
                    ]
                ];
                
            //  2.  ESTADOS

                $estadosProveedor = obtenerEstadosProveedor($mysqli, $provider_id);
                
                $response = [
                    'status' => 200,
                    'result' => [
                        'id' => $id,
                        'nombre' => $nombre,
                        'nombre_corto' => $nombre_corto,
                        'metodo_importacion' => $metodo_importacion,
                        'tipo_calculo_id' => $tipo_calculo_id,
                        'tipo_formula_id' => $tipo_formula_id,
                        'comision_moneda_id' => $comision_moneda_id,
                        'metodo_formula' => $metodo_formula,
                        'columna_conciliacion_id' => $columna_conciliacion_id,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'usuario_create' => $usuario_create,
                        'usuario_update' => $usuario_update,
                        'estados' => $estadosProveedor,
                    ]
                ];
                            
            switch ($metodo_importacion) {
                case "ColumnasArchivoCombinado":
                    
                    $tipo_archivo_id = 3;

                    //  3.  COLUMNAS

                    $columnasProveedor = obtenerColumnasProveedor($mysqli, $provider_id, $tipo_archivo_id);
    
                    //  4.  FORMATO  

                    $columna_conciliacion_name = "id";
                    list($column_provider_id_nombre,$column_provider_id_prefijo,$column_provider_id_separador,$column_provider_id_sufijo, $column_provider_id_nombreColumna_json, $column_provider_id_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $columna_conciliacion_name);

                    $calimaco_column_name_monto = "monto";
                    list($column_provider_monto_nombre, $column_provider_monto_prefijo,$column_provider_monto_simbolo,$column_provider_monto_sufijo, $column_provider_monto_nombreColumna_json, $column_provider_monto_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_monto);
                    
                    $calimaco_column_name_comision_total = "comision_total";
                    list($column_provider_comision_total_nombre, $column_provider_comision_total_prefijo,$column_provider_comision_total_simbolo,$column_provider_comision_total_sufijo, $column_provider_comision_total_nombreColumna_json, $column_provider_comision_total_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_monto);

                    $response['resultColumnaConciliacion'] = [
                            'combinado_calimaco_id' => $columna_conciliacion_id, //  Id Calimaco
                            'combinado_calimaco_tipo' => $column_provider_id_formato,
                            'combinado_nombre_json' => $column_provider_id_nombreColumna_json,
                            'combinado_prefijo' => $column_provider_id_prefijo,
                            'combinado_sufijo' => $column_provider_id_sufijo,
                            'combinado_separador_id' => $column_provider_id_separador,
                            'combinado_monto_prefijo' => $column_provider_monto_prefijo,   //  Monto
                            'combinado_monto_sufijo' => $column_provider_monto_sufijo,
                            'combinado_monto_separador_id' => $column_provider_monto_simbolo,
                            'combinado_comision_prefijo' => $column_provider_comision_total_prefijo,   //  Comisión Total
                            'combinado_comision_sufijo' => $column_provider_comision_total_sufijo,
                            'combinado_comision_separador_id' => $column_provider_comision_total_simbolo
                        ];

                    list($combinado_extension,$combinado_separador_id,$combinado_linea_inicio,$combinado_columna_inicio) = fetchArchivoFormato($mysqli, $provider_id, $tipo_archivo_id);

                    $response['resultArchivoFormato'] = [
                        'extension_id' => $combinado_extension,
                        'separador_id' => $combinado_separador_id,
                        'linea_inicio' => $combinado_linea_inicio,
                        'columna_inicio' => $combinado_columna_inicio,
                        'columnas' => $columnasProveedor
                    ];

                    break;
                case "ColumnasArchivosIndependientes":
                    $tipo_archivo_id = 1;

                    //  3.  COLUMNAS

                    $columnasProveedorVenta = obtenerColumnasProveedor($mysqli, $provider_id, $tipo_archivo_id);

                    //  4.  FORMATO  

                    $columna_conciliacion_name = "id";
                    list($column_provider_id_nombre,$column_provider_id_prefijo,$column_provider_id_separador,$column_provider_id_sufijo, $column_provider_id_nombreColumna_json, $column_provider_id_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $columna_conciliacion_name);
                 
                    
                    $calimaco_column_name_monto = "monto";
                    list($column_provider_monto_nombre, $column_provider_monto_prefijo,$column_provider_monto_simbolo,$column_provider_monto_sufijo, $column_provider_monto_nombreColumna_json, $column_provider_monto_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_monto);
                    
                    $response['resultColumnaVenta'] = [
                        'calimaco_id' => $columna_conciliacion_id, //  Id Calimaco
                        'calimaco_tipo' => $column_provider_id_formato,
                        'nombre_json' => $column_provider_id_nombreColumna_json,
                        'prefijo' => $column_provider_id_prefijo,
                        'sufijo' => $column_provider_id_sufijo,
                        'separador_id' => $column_provider_id_separador,
                        'monto_prefijo' => $column_provider_monto_prefijo,   //  Monto
                        'monto_sufijo' => $column_provider_monto_sufijo,
                        'monto_separador_id' => $column_provider_monto_simbolo,
                        'columnasVenta' => $columnasProveedorVenta
                    ];

                    $tipo_archivo_id = 2;

                    $columnasProveedorLiquidacion = obtenerColumnasProveedor($mysqli, $provider_id, $tipo_archivo_id);

                    $columna_conciliacion_name_liquidacion = "id";
                    list($column_provider_id_nombre,$column_provider_id_prefijo,$column_provider_id_separador,$column_provider_id_sufijo, $column_provider_id_nombreColumna_json, $column_provider_id_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $columna_conciliacion_name_liquidacion);

                    $calimaco_column_name_comision_total_liquidacion = "comision_total";
                    list($column_provider_comision_total_nombre, $column_provider_comision_total_prefijo,$column_provider_comision_total_simbolo,$column_provider_comision_total_sufijo, $column_provider_comision_total_nombreColumna_json, $column_provider_comision_total_formato) = fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name_comision_total_liquidacion);
                
                    $response['resultColumnaLiquidacion'] = [
                        'calimaco_id' => $columna_conciliacion_id, //  Id Calimaco
                        'calimaco_tipo' => $column_provider_id_formato,
                        'nombre_json' => $column_provider_id_nombreColumna_json,
                        'prefijo' => $column_provider_id_prefijo,
                        'sufijo' => $column_provider_id_sufijo,
                        'separador_id' => $column_provider_id_separador,            
                        'comision_prefijo' => $column_provider_comision_total_prefijo,   //  Comisión Total
                        'comision_sufijo' => $column_provider_comision_total_sufijo,
                        'comision_separador_id' => $column_provider_comision_total_simbolo,
                        'columnasLiquidacion' => $columnasProveedorLiquidacion
                    ];
                    $tipo_archivo_id = 1;

                    list($venta_extension,$venta_separador_id,$venta_linea_inicio,$venta_columna_inicio) = fetchArchivoFormato($mysqli, $provider_id, $tipo_archivo_id);

                    $response['resultArchivoFormatoVenta'] = [
                        'extension_id' => $venta_extension,
                        'separador_id' => $venta_separador_id,
                        'linea_inicio' => $venta_linea_inicio,
                        'columna_inicio' => $venta_columna_inicio
                    ];
                    $tipo_archivo_id = 2;

                    list($liquidacion_extension,$liquidacion_separador_id,$liquidacion_linea_inicio,$liquidacion_columna_inicio) = fetchArchivoFormato($mysqli, $provider_id, $tipo_archivo_id);

                    $response['resultArchivoFormatoLiquidacion'] = [
                        'extension_id' => $liquidacion_extension,
                        'separador_id' => $liquidacion_separador_id,
                        'linea_inicio' => $liquidacion_linea_inicio,
                        'columna_inicio' => $liquidacion_columna_inicio
                    ];

                    break;
                default:
                    throw new Exception("No existe el metodo de importación");
                    break;
            }

            //  3.  Formulas

                switch ($metodo_formula) {
                    case "FormulaFija":

                        $formulasProveedor = obtenerFormulaFijaProveedor($mysqli, $provider_id);

                        $response['formulas'] = [
                            'formulas' => $formulasProveedor
                        ];

                        break;
                    case "FormulaEscalonada":

                        $formulasProveedor = obtenerFormulaEscalonadaProveedor($mysqli, $provider_id);

                        $response['formulas'] = [
                            'formulas' => $formulasProveedor
                        ];

                        break;

                    case "FormulaMixta":

                        $formulasProveedor = obtenerFormulaMixtaProveedor($mysqli, $provider_id);

                        $response['formulas'] = [
                            'formulas' => $formulasProveedor
                        ];

                        break;
                    default:
                        throw new Exception("No existe el metodo de formulas");
                        break;
                }

            //  4. CUENTAS BANCARIAS

            $cuentasBancariasProveedor = obtenerCuentasBancariasProveedor($mysqli, $provider_id);
                
            $response['cuentasBancarias'] = [
                'cuentas' => $cuentasBancariasProveedor
            ];
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(['status' => 500, 'message' => 'Error en la consulta SQL: '. $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'ID no válido']);
    }
}

function fetchCalimacoColumn($mysqli, $provider_id, $tipo_archivo_id, $calimaco_column_name) {

    $queryColumn = "SELECT 
                        pc.nombre, 
                        IFNULL(pc.prefijo,''), 
                        IFNULL(pc.separador_id,0),  
                        IFNULL(pc.sufijo,''),  
                        IFNULL(pc.nombreColumna_json,''), 
                        pc.formato_id
                    FROM tbl_conci_proveedor_columna pc
                    LEFT JOIN tbl_conci_calimaco_columna cc ON pc.columna_id = cc.id
                    LEFT JOIN tbl_conci_archivo_separador ars ON pc.separador_id = ars.id
                    WHERE cc.nombre_bd = ? 
                    AND pc.tipo_archivo_id = ?
                    AND pc.proveedor_id = ?
                    ;";

    $stmtColumn = $mysqli->prepare($queryColumn);

    if (!$stmtColumn) throw new Exception("Error al preparar la consulta de las columnas del proveedor. Comunicarse con soporte.");

    $stmtColumn->bind_param("sii", $calimaco_column_name, $tipo_archivo_id, $provider_id);
    if (!$stmtColumn->execute()) throw new Exception("Error al ejecutar la consulta de las columnas del proveedor. Comunicarse con soporte.".$mysqli->error);
    $stmtColumn->store_result();

    if ($stmtColumn->num_rows > 0) {
        $stmtColumn->bind_result(
                                    $column_provider_nombre,
                                    $column_provider_prefijo, 
                                    $column_provider_separador_id,
                                    $column_provider_sufijo,
                                    $column_provider_nombreColumna_json,
                                    $column_provider_formato_id);
        if (!$stmtColumn->fetch()) throw new Exception("No se encontraron datos de las columnas del proveedor seleccionado. Comunicarse con soporte.");
            $stmtColumn->close(); 
        return [$column_provider_nombre, 
                $column_provider_prefijo, 
                $column_provider_separador_id, 
                $column_provider_sufijo, 
                $column_provider_nombreColumna_json,
                $column_provider_formato_id
            ];
    } else {
        throw new Exception("No se encontró una columna que corresponda  a '{$calimaco_column_name}' de Calimaco. Comunicarse con soporte.");
        //fn_conci_set_status_code_response(400, "No se encontró una columna que corresponda a '{$calimaco_column_name}' de Calimaco: " . $mysqli->error, "Error");
    }
}

function fetchArchivoFormato($mysqli, $provider_id, $tipo_archivo_id) {

    $queryColumn = "SELECT 
                        IFNULL(af.extension_id,0),  
                        IFNULL(af.separador_id,0),  
                        IFNULL(af.linea_inicio,0), 
                        IFNULL(af.columna_inicio,0)
                    FROM tbl_conci_archivo_formato af
                    WHERE af.tipo_archivo_id = ?
                    AND af.proveedor_id = ?
                    ;";

    $stmtColumn = $mysqli->prepare($queryColumn);

    if (!$stmtColumn) throw new Exception("Error al preparar la consulta de las columnas del proveedor. Comunicarse con soporte.");

    $stmtColumn->bind_param("ii", $tipo_archivo_id, $provider_id);
    if (!$stmtColumn->execute()) throw new Exception("Error al ejecutar la consulta de las columnas del proveedor. Comunicarse con soporte.".$mysqli->error);
    $stmtColumn->store_result();

    if ($stmtColumn->num_rows > 0) {
        $stmtColumn->bind_result(
                                    $extension_id, 
                                    $separador_id,
                                    $linea_inicio,
                                    $columna_inicio);
        if (!$stmtColumn->fetch()) throw new Exception("No se encontraron datos de las columnas del proveedor seleccionado. Comunicarse con soporte.");
            $stmtColumn->close(); 
        return [$extension_id, 
                $separador_id, 
                $linea_inicio, 
                $columna_inicio,
            ];
    } else {
        throw new Exception("No se encontró una columna que corresponda al proveedor id='{$provider_id}'. Comunicarse con soporte.");
        //fn_conci_set_status_code_response(400, "No se encontró una columna que corresponda a '{$calimaco_column_name}' de Calimaco: " . $mysqli->error, "Error");
    }
}

//  Datos Generales

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_importacion_tipo") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                metodo, 
                nombre
            FROM tbl_conci_importacion_tipo
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

//  Columna archivos

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_columna_tipo") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_columna_tipo
            WHERE status = 1
            ORDER BY nombre DESC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_columna_combinado") {
    try {

        $stmt = $mysqli->prepare("
            SELECT id, nombre
                FROM tbl_conci_calimaco_columna
                WHERE status = 1 AND sincronia_combinado = 1
                UNION
                SELECT 0 AS id, 'No aplica' AS nombre
                ORDER BY id ASC
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_columna_venta") {
    try {

        $stmt = $mysqli->prepare("
            SELECT id, nombre
                FROM tbl_conci_calimaco_columna
                WHERE status = 1 AND sincronia_venta = 1
                UNION
                SELECT 0 AS id, 'No aplica' AS nombre
                ORDER BY id ASC
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_columna_liquidacion") {
    try {

        $stmt = $mysqli->prepare("
            SELECT id, nombre
                FROM tbl_conci_calimaco_columna
                WHERE status = 1 AND sincronia_liquidacion = 1
                UNION
                SELECT 0 AS id, 'No aplica' AS nombre
                ORDER BY id ASC
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

//  Formato calimaco id
if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_calimaco_id") {
    try {

        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre
            FROM tbl_conci_calimaco_columna
            WHERE status = 1 AND identificador = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_calimaco_tipo") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_columna_tipo
            WHERE status = 1 AND habilitado_idendificador = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_monto_separador") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT(nombre,' ( ',simbolo,' )') AS nombre
            FROM tbl_conci_archivo_separador
            WHERE status = 1 AND (simbolo = '.' OR simbolo = ',')
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_calimaco_separador") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT(nombre,' ( ',simbolo,' )') AS nombre
            FROM tbl_conci_archivo_separador
            WHERE status = 1
            UNION
        SELECT 0 AS id, 'Ninguno' AS nombre
            ORDER BY id ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

//  Archivos ------------------------------------

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_formato_tipo") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_archivo_extension
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}
if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_separador_tipo") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT(nombre,' ( ',simbolo,' )') AS nombre
            FROM tbl_conci_archivo_separador
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}


//  Liquidación ------------------------------------

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_liquidacion_calculo_tipo") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_calculo_tipo
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

//     FORMULAS

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_liquidacion_formula_tipo") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                metodo AS id, 
                nombre
            FROM tbl_conci_formula_tipo
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_formula_fija_operador_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_formula_operador
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_formula_mixta_operador_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_formula_operador
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_formula_fija_opcion_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_formula_opcion
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_formula_mixta_opcion_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_conci_formula_opcion
            WHERE status = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}
//  CUENTAS BANCARIAS


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_banco_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_bancos
            WHERE estado = 1
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_mant_proveedor_moneda_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT
                id, 
                CONCAT(nombre,' (',simbolo,')') AS nombre
            FROM tbl_moneda
            WHERE estado = 1 AND nombre IS NOT NULL
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "No se encontraron registros.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}
