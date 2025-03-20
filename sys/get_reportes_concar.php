<?php
date_default_timezone_set("America/Lima");
setlocale(LC_ALL, "es_ES");

$file_columns = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];

$fields_provider = [
    "direc_tv" => [
        "cuenta_principal" => "varchar",
        "fecha_creacion" => "datetime",
        "periodo_de" => "datetime",
        "periodo_a" => "datetime",
        "concepto_facturable" => "varchar",
        "monto_inc_igv" => "decimal",
        "fecha_deposito" => "datetime",
        "numero_referencia" => "varchar",
        "fecha_facturacion" => "datetime",
        "usuario_creador" => "varchar",
        "nro_transaccion" => "varchar",
        "cuenta_secundaria" => "varchar"
    ],
    "movistar" => [
        "factura" => "varchar",
        "cliente" => "varchar",
        "cuenta" => "varchar",
        "telf_agrupador" => "varchar",
        "telf_afiliado" => "varchar",
        "desc_resumen" => "varchar",
        "cofa" => "varchar",
        "desc_facturacion" => "varchar",
        "fecha_cierre" => "datetime",
        "tiempo_consumido" => "int",
        "tiempo_incluido" => "int",
        "monto_facturado" => "decimal",
        "igv" => "decimal",
        "monto_inc_igv" => "decimal",
        "nombre_cliente" => "varchar",
        "concepto_facturable" => "varchar",
    ],
    "niubiz" => [
        "periodo" => "varchar",
        "ruc" => "varchar",
        "codigo_comercio" => "varchar",
        "tipo_documento" => "varchar",
        "id_unico" => "varchar",
        "nro_lote_resumen" => "varchar",
        "descripcion_operacion" => "varchar",
        "tipo_tarjeta" => "varchar",
        "entidad_emisora" => "varchar",
        "fecha_trx" => "datetime",
        "fecha_proceso" => "datetime",
        "fecha_abono" => "datetime",
        "importe_transaccion" => "decimal",
        "comision_emisores" => "decimal",
        "comision_niubiz" => "decimal",
        "comision_total" => "decimal",
        "otros_cargos_abonos" => "decimal",
        "igv" => "decimal",
        "monto_abonado" => "decimal",
        "cuenta_bancaria_abono" => "varchar",
        "importe_ajuste" => "decimal",
        "importe_anulado" => "decimal",
        "otros_cargo_abono_anulados" => "decimal",
        "importe_descontado" => "decimal",
        "comision_visa" => "decimal"
    ],
    "prosegur" => [
        "nombre_cliente" => "varchar",
        "nro_abonado" => "varchar",
        "local" => "varchar",
        "direccion" => "varchar",
        "comercial" => "varchar",
        "fecha_instalacion" => "datetime",
        "fecha_desinstalacion" => "datetime",
        "status" => "varchar",
        "tarifa_os_igv" => "decimal",
        "medio_transmisión" => "varchar",
        "facturacion" => "varchar",
        "comentario" => "varchar"
    ],
    "common" => [
        "archivo_proveedor_id" => "int",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ]
];

if (isset($_POST["accion"])) {
    $accion = $_POST["accion"];
    switch ($accion) {
        case "procesar-archivo-proveedor":
            $data = [
                'nombre_archivo' => $_FILES['archivo-proveedor']['name'],
                'nombre_proveedor' => $_POST["nombre-proveedor"],
                'proveedor_id' => intval($_POST["proveedor-id"]),
                'extension' => pathinfo($_FILES['archivo-proveedor']['name'], PATHINFO_EXTENSION),
                'tmpfname' => $_FILES['archivo-proveedor']['tmp_name']
            ];

            $nombre_proveedor_clave = str_replace(" ", "_", strtolower($data["nombre_proveedor"]));
            $start_sheet = 0;
            if ($nombre_proveedor_clave == "movistar") {
                $start_sheet = 1;
            }
            $file_data = cargar_archivo_proveedor($fields_provider, $file_columns, $data, $start_sheet);

            //$success = guardar_archivo_proveedor_por_volcado_de_datos($data, $fields_provider);

            $centro_costos = [];
            $conceptos = [];
            switch ($nombre_proveedor_clave) {
                case "direc_tv":
                    $numeros_cuenta = get_unique_values_from_array($file_data["detalle"], "cuenta_secundaria");
                    $centro_costos = get_numeros_cuenta_pendientes($numeros_cuenta);
                    break;
                case "movistar":
                    $codigos_pago = get_unique_values_from_array($file_data["detalle"], "telf_afiliado");
                    $centro_costos = get_codigos_pago_pendientes($codigos_pago);
                    $conceptos_facturables = get_unique_values_from_array($file_data["detalle"], "concepto_facturable");
                    $conceptos = get_conceptos_facturables_pendientes($conceptos_facturables);
                    break;
                case "niubiz":
                    $codigos_comercio = get_unique_values_from_array($file_data["detalle"], "codigo_comercio");
                    $centro_costos = get_codigos_comercio_pendientes($codigos_comercio);
                    break;
                case "prosegur":
                    $numeros_abonado = get_unique_values_from_array($file_data["detalle"], "nro_abonado");
                    $centro_costos = get_numeros_abonado_pendientes($numeros_abonado);
                    break;
            }

            $archivo_proveedor_id = guardar_archivo_proveedor_por_lotes($file_data, $fields_provider);

            if ($archivo_proveedor_id > 0) {
                $response["success"] = true;
                $response["data_centros_costo"] = $centro_costos;
                $response["data_conceptos"] = $conceptos;
                $response["archivo_proveedor_id"] = $archivo_proveedor_id;
                set_status_code_response(200, "", $response);
            } else {
                set_status_code_response(500, "Un error ha ocurrido al intentar guardar los datos del archivo.", null);
            }
            break;
        case "get-concar-proveedores":
            get_concar_proveedores();
            break;
        case "get-archivos-proveedor-maestro":
            get_data_archivos_proveedor_maestro();
            break;
        case "get-archivos-proveedor-detalle":
            $archivo_proveedor_id = intval($_POST["archivo_proveedor_id"]);
            get_data_archivos_proveedor_detalle($archivo_proveedor_id, $fields_provider);
            break;
        case "eliminar-archivo-proveedor":
            $archivo_proveedor_id = $_POST["archivo_proveedor_id"];
            $result = eliminar_archivo_proveedor($archivo_proveedor_id);
            if ($result) {
                $response["success"] = true;
                set_status_code_response(200, "", $response);
            }
            break;
        case "exportar-archivo-concar":
            $tipo = $_POST["tipo"];
            $data = [
                "fecha_comprobante" => $_POST["fecha_comprobante"],
                "numero_comprobante" => $_POST["numero_comprobante"],
                "fecha_emision" => $_POST["fecha_emision"],
                "numero_documento" => $_POST["numero_documento"],
                "fecha_vencimiento" => $_POST["fecha_vencimiento"]
            ];

            $data_concar_array = [];

            if ($tipo == "por-rango-fechas") {
                $data = array_merge($data, [
                    "proveedor_id" => intval($_POST["proveedor_id"]),
                    "fecha_creacion_desde" => $_POST["fecha_creacion_desde"],
                    "fecha_creacion_hasta" => $_POST["fecha_creacion_hasta"]
                ]);
                $data_concar_array = get_data_concar_por_rango_fechas($data);
            } else if ($tipo == "por-archivo-proveedor-ids") {
                $data = array_merge($data, [
                    "archivo_proveedor_ids" => json_decode(stripslashes($_POST['archivo_proveedor_ids']))
                ]);
                $data_concar_array = get_data_concar_por_archivo_proveedor_ids($data);
            }

            if (is_array($data_concar_array) && count($data_concar_array) > 0) {
                $responses = [];
                $start_sheet = 0;
                foreach ($data_concar_array as $nombre_proveedor => $data_collection) {
                    if (count($data_collection) > 0) {
                        foreach ($data_collection as $file_name => $data_concar) {
                            if (count($data_concar) > 0) {
                                $response = exportar_archivo_concar($file_columns, $data_concar, $file_name);
                                $responses[] = $response;
                            } else {
                                set_status_code_response(400, "No se puede exportar el archivo " . strtoupper($file_name) . ".", null);
                            }
                        }
                    } else {
                        set_status_code_response(400, "No se puede exportar los archivos del proveedor " . strtoupper($nombre_proveedor) . ".", null);
                    }
                }

                $archivo_proveedor_ids_collection = $data["archivo_proveedor_ids"];

                if ($tipo == "por-rango-fechas") {
                    $proveedor_id = $data["proveedor_id"];
                    $fecha_creacion_desde = $data["fecha_creacion_desde"];
                    $fecha_creacion_hasta = $data["fecha_creacion_hasta"];
                    actualizar_estado_archivo_proveedor_por_rango_fechas($proveedor_id, $fecha_creacion_desde, $fecha_creacion_hasta, 1);
                    actualizar_numero_documento_archivo_proveedor_maestro_por_rango_fechas($proveedor_id, $fecha_creacion_desde, $fecha_creacion_hasta, $data["numero_documento"]);
                }
                if ($tipo == "por-archivo-proveedor-ids") {
                    foreach ($archivo_proveedor_ids_collection as $archivo_proveedor_id_collection) {
                        foreach ($archivo_proveedor_id_collection as $proveedor_id => $archivo_proveedor_ids) {
                            actualizar_estado_archivo_proveedor_por_archivo_proveedor_ids($archivo_proveedor_ids, 1);
                            $nombre_proveedor_clave = get_nombre_proveedor_clave($proveedor_id);
                            foreach ($archivo_proveedor_ids as $archivo_proveedor_id) {
                                $numero_documento = false;
                                if ($nombre_proveedor == "movistar") {
                                    $numero_documento = get_numero_documento_archivo_proveedor_detalle_por_archivo_proveedor_id($archivo_proveedor_id);
                                } else {
                                    $numero_documento = $data["numero_documento"];
                                }
                                if ($numero_documento) {
                                    actualizar_numero_documento_archivo_proveedor_maestro_por_id($archivo_proveedor_id, $numero_documento);
                                }
                            }
                        }
                    }
                }


                if (count($responses) > 0) {
                    echo json_encode($responses);
                }
            }
            break;
        case "get-datetime-columns":
            get_datetime_columns($fields_provider);
            break;
        case "get-centros-costo":
            $data = get_centros_costo();
            set_status_code_response(200, "", $data);
            break;
        case "get-centro-costo":
            $id = intval($_POST["id"]);
            $data = get_centro_costo($id);
            if ($data == null) {
                $data = [
                    "id" => 0,
                    "local" => null,
                    "descripcion" => null,
                    "ceco" => null,
                    "fecha_baja" => null,
                    "costo_mensual" => 0.00,
                    "observacion" => null,
                    "estado" => 1
                ];
            };
            set_status_code_response(200, "", $data);
            break;
        case "editar-centro-costo":
            $data = [
                "id" => intval($_POST["id"]),
                "local" => $_POST["local"],
                "descripcion" => $_POST["descripcion"],
                "ceco" => $_POST["ceco"],
                "fecha_baja" => $_POST["fecha_baja"],
                "costo_mensual" => $_POST["costo_mensual"],
                "observacion" => $_POST["observacion"],
                "estado" => intval($_POST["estado"])
            ];
            $exist = check_exist_duplicate_ceco_on_centros_costo($data["ceco"], $data["id"]);
            if ($exist) {
                set_status_code_response(400, "El código de centro de costo '{$data["ceco"]}' ya está asignado.", null);
            }
            editar_centro_costo($data);
            set_status_code_response(200, "", null);
            break;
        case "eliminar-centro-costo":
            $id = intval($_POST["id"]);
            eliminar_centro_costo($id);
            set_status_code_response(200, "", null);
            break;
        case "get-numeros-cuenta":
            $data = get_numeros_cuenta();
            set_status_code_response(200, "", $data);
            break;
        case "get-numeros-cuenta-pendientes":
            $archivo_proveedor_id = intval($_POST["archivo_proveedor_id"]);
            $numeros_cuenta = get_unique_values_from_db($archivo_proveedor_id, "cuenta_secundaria", "nro_cuenta");
            $data = get_numeros_cuenta_pendientes($numeros_cuenta);
            set_status_code_response(200, "", $data);
            break;
        case "get-numero-cuenta":
            $id = intval($_POST["id"]);
            $nro_cuenta = $_POST["nro_cuenta"];
            $data = get_numero_cuenta($id);
            if ($data == null) {
                $data = [
                    "id" => 0,
                    "nro_cuenta" => $nro_cuenta,
                    "ceco" => null
                ];
            }
            set_status_code_response(200, "", $data);
            break;
        case "editar-numero-cuenta":
            $data = [
                "id" => intval($_POST["id"]),
                "nro_cuenta" => $_POST["nro_cuenta"],
                "ceco" => $_POST["ceco"]
            ];
            $exist = check_exist_duplicate_nro_cuenta_and_ceco_on_numeros_cuenta($data["nro_cuenta"], $data["ceco"], $data["id"]);
            if ($exist) {
                set_status_code_response(400, "La relación entre el nro. cuenta y el centro de costos ya existe.", null);
            }
            editar_numero_cuenta($data);
            set_status_code_response(200, "", null);
            break;
        case "eliminar-numero-cuenta":
            $id = intval($_POST["id"]);
            eliminar_numero_cuenta($id);
            set_status_code_response(200, "", null);
            break;
        case "get-codigos-pago":
            $data = get_codigos_pago();
            set_status_code_response(200, "", $data);
            break;
        case "get-codigos-pago-pendientes":
            $archivo_proveedor_id = $_POST["archivo_proveedor_id"];
            $codigos_pago = get_unique_values_from_db($archivo_proveedor_id, "telf_afiliado", "cod_pago");
            $data = get_codigos_pago_pendientes($codigos_pago);
            set_status_code_response(200, "", $data);
            break;
        case "get-codigo-pago":
            $id = intval($_POST["id"]);
            $cod_pago = $_POST["cod_pago"];
            $data = get_codigo_pago($id);
            if ($data == null) {
                $data = [
                    "id" => 0,
                    "cod_pago" => $cod_pago,
                    "ceco" => null
                ];
            }
            set_status_code_response(200, "", $data);
            break;
        case "editar-codigo-pago":
            $data = [
                "id" => intval($_POST["id"]),
                "cod_pago" => $_POST["cod_pago"],
                "ceco" => $_POST["ceco"]
            ];
            $exist = check_exist_duplicate_cod_pago_and_ceco_on_codigos_pago($data["cod_pago"], $data["ceco"], $data["id"]);
            if ($exist) {
                set_status_code_response(400, "La relación entre el cód. pago y el centro de costos ya existe.", null);
            }
            editar_codigo_pago($data);
            set_status_code_response(200, "", null);
            break;
        case "eliminar-codigo-pago":
            $id = intval($_POST["id"]);
            eliminar_codigo_pago($id);
            set_status_code_response(200, "", null);
            break;
        case "get-cuentas-contables":
            $data = get_cuentas_contables();
            set_status_code_response(200, "", $data);
            break;
        case "get-cuenta-contable":
            $id = intval($_POST["id"]);
            $data = get_cuenta_contable($id);
            set_status_code_response(200, "", $data);
            break;
        case "editar-cuenta-contable":
            $data = [
                "id" => intval($_POST["id"]),
                "cta_contable" => $_POST["cta_contable"],
                "concar" => $_POST["concar"]
            ];
            $exist = check_exist_duplicate_cta_contable_and_concar_on_cuentas_contables($data["cta_contable"], $data["concar"], $data["id"]);
            if ($exist) {
                set_status_code_response(400, "La relación entre la cuenta contable y el concar ya existe.", null);
            }
            editar_cuenta_contable($data);
            set_status_code_response(200, "", $data);
            break;
        case "eliminar-cuenta-contable":
            $id = intval($_POST["id"]);
            eliminar_cuenta_contable($id);
            set_status_code_response(200, "", null);
            break;
        case "get-proveedores":
            $data = get_proveedores();
            set_status_code_response(200, "", $data);
            break;
        case "get-proveedor":
            $id = intval($_POST["id"]);
            $data = get_proveedor($id);
            set_status_code_response(200, "", $data);
            break;
        case "editar-proveedor":

            $id = intval($_POST["id"]);
            $nombre = $_POST["nombre"];
            $ruc = $_POST["ruc"];

            $exist = check_exist_duplicate_nombre_on_proveedores($nombre, $id);

            if ($exist) {
                set_status_code_response(400, "El nombre del proveedor '{$nombre}' ya existe.", null);
            }

            $exist = check_exist_duplicate_ruc_on_proveedores($ruc, $id);

            if ($exist) {
                set_status_code_response(400, "El ruc '{$ruc}' le pertenece a otro proveedor.", null);
            }

            $data = [
                "id" => $id,
                "nombre" => $nombre,
                "ruc" => $ruc
            ];

            editar_proveedor($data);
            set_status_code_response(200, "", null);
            break;
        case "eliminar-proveedor":
            $id = intval($_POST["id"]);
            eliminar_proveedor($id);
            set_status_code_response(200, "", null);
            break;
        case "get-conceptos-facturables":
            $data = get_conceptos_facturables();
            set_status_code_response(200, "", $data);
            break;
        case "get-conceptos-facturables-pendientes":
            $archivo_proveedor_id = $_POST["archivo_proveedor_id"];
            $conceptos_facturables = get_unique_values_from_db($archivo_proveedor_id, "concepto_facturable", "concepto_facturable");
            $data = get_conceptos_facturables_pendientes($conceptos_facturables);
            set_status_code_response(200, "", $data);
            break;
        case "get-concepto-facturable":
            $id = intval($_POST["id"]);
            $concepto = $_POST["concepto"];
            $data = get_concepto_facturable($id);
            if ($data == null) {
                $data = [
                    "id" => 0,
                    "concepto" => $concepto,
                    "cta_contable" => null
                ];
            }
            set_status_code_response(200, "", $data);
            break;
        case "editar-concepto-facturable":
            $data = [
                "id" => intval($_POST["id"]),
                "concepto" => $_POST["concepto"],
                "cta_contable" => $_POST["cta_contable"]
            ];
            $exist = check_exist_duplicate_concepto_and_cta_contable_on_conceptos_facturables($data["concepto"], $data["cta_contable"], $data["id"]);
            if ($exist) {
                set_status_code_response(400, "La relación entre el concepto y la cta. contable ya existe.", null);
            }
            editar_concepto_facturable($data);
            set_status_code_response(200, "", null);
            break;
        case "eliminar-concepto-facturable":
            $id = intval($_POST["id"]);
            eliminar_concepto_facturable($id);
            set_status_code_response(200, "", null);
            break;
        case "get-codigos-comercio":
            $data = get_codigos_comercio();
            set_status_code_response(200, "", $data);
            break;
        case "get-codigos-comercio-pendientes":
            $archivo_proveedor_id = $_POST["archivo_proveedor_id"];
            $codigos_comercio = get_unique_values_from_db($archivo_proveedor_id, "codigo_comercio", "cod_comercio");
            $data = get_codigos_comercio_pendientes($codigos_comercio);
            set_status_code_response(200, "", $data);
            break;
        case "get-codigo-comercio":
            $id = intval($_POST["id"]);
            $cod_comercio = $_POST["cod_comercio"];
            $data = get_codigo_comercio($id);
            if ($data == null) {
                $data = [
                    "id" => 0,
                    "cod_comercio" => $cod_comercio,
                    "ceco" => null
                ];
            }
            set_status_code_response(200, "", $data);
            break;
        case "editar-codigo-comercio":
            $data = [
                "id" => intval($_POST["id"]),
                "cod_comercio" => $_POST["cod_comercio"],
                "ceco" => $_POST["ceco"]
            ];
            $exist = check_exist_duplicate_cod_comercio_and_ceco_on_codigos_comercio($data["cod_comercio"], $data["ceco"], $data["id"]);
            if ($exist) {
                set_status_code_response(400, "La relación entre el código de comercio y el centro de costos ya existe.", null);
            }
            editar_codigo_comercio($data);
            set_status_code_response(200, "", null);
            break;
        case "eliminar-codigo-comercio":
            $id = intval($_POST["id"]);
            eliminar_codigo_comercio($id);
            set_status_code_response(200, "", null);
            break;
        case "get-bancos":
            $data = get_bancos();
            set_status_code_response(200, "", $data);
            break;
        case "get-bancos-activos":
            $data = get_bancos_activos();
            set_status_code_response(200, "", $data);
            break;
        case "get-banco":
            $id = intval($_POST["id"]);
            $data = get_banco($id);
            set_status_code_response(200, "", $data);
            break;
        case "editar-banco":
            $data = [
                "id" => intval($_POST["id"]),
                "nombre" => $_POST["nombre"],
                "razon_social" => $_POST["razon_social"],
                "ruc" => $_POST["ruc"],
                "estado" => $_POST["estado"]
            ];

            $exist = check_exist_duplicate_nombre_on_bancos($data["nombre"], $data["id"]);

            if ($exist) {
                set_status_code_response(400, "El nombre del banco '{$data["nombre"]}' ya existe.", null);
            }

            $exist = check_exist_duplicate_ruc_on_bancos($data["ruc"], $data["id"]);

            if ($exist) {
                set_status_code_response(400, "El ruc '{$data["ruc"]}' le pertenece a otro banco.", null);
            }

            editar_banco($data);
            set_status_code_response(200, "", null);
            break;
        case "eliminar-banco":
            $id = intval($_POST["id"]);
            eliminar_banco($id);
            set_status_code_response(200, "", null);
            break;
        case "get-detalle-bancos":
            $archivo_proveedor_id = intval($_POST["archivo_proveedor_id"]);
            $data = get_detalle_bancos($archivo_proveedor_id);
            set_status_code_response(200, "", $data);
            break;
        case "eliminar-detalle-banco":
            $id = intval($_POST["id"]);
            eliminar_detalle_banco($id);
            set_status_code_response(200, "", null);
            break;
        case "editar-detalle-banco":
            $data = json_decode(stripslashes($_POST['data']));
            editar_detalle_banco($data);
            set_status_code_response(200, "", null);
            break;
        case "get-numeros-abonados":
            $data = get_numeros_abonados();
            set_status_code_response(200, "", $data);
            break;
        case "eliminar-numero-abonado":
            $id = intval($_POST["id"]);
            eliminar_numero_abonado($id);
            set_status_code_response(200, "", null);
            break;
        case "get-numero-abonado":
            $id = intval($_POST["id"]);
            $nro_abonado = $_POST["nro_abonado"];
            $data = get_numero_abonado($id);
            if ($data == null) {
                $data = [
                    "id" => 0,
                    "nro_abonado" => $nro_abonado,
                    "ceco" => null
                ];
            }
            set_status_code_response(200, "", $data);
        case "editar-numero-abonado":
            $data = [
                "id" => intval($_POST["id"]),
                "nro_abonado" => $_POST["nro_abonado"],
                "ceco" => $_POST["ceco"]
            ];
            $exist = check_exist_duplicate_nro_abonado_and_ceco_on_numeros_abonado($data["nro_abonado"], $data["ceco"], $data["id"]);
            if ($exist) {
                set_status_code_response(400, "La relación entre el nro. abonado y el centro de costos ya existe.", null);
            }
            editar_numero_abonado($data);
            set_status_code_response(200, "", null);
            break;
        case "get-numeros-abonado-pendientes":
            $archivo_proveedor_id = intval($_POST["archivo_proveedor_id"]);
            $numeros_abonado = get_unique_values_from_db($archivo_proveedor_id, "nro_abonado", "nro_abonado");
            $data = get_numeros_abonado_pendientes($numeros_abonado);
            set_status_code_response(200, "", $data);
            break;
        default:
            set_status_code_response(400, "La acción no es válida.", null);
    }
} else {
    set_status_code_response(400, "Una acción es necesaria.", null);
}

function cargar_archivo_proveedor($fields_provider, $file_columns, $data, $start_sheet = 0)
{
    try {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '500');
        set_time_limit(0);

        $campos_por_validar = [
            'nombre_archivo',
            'nombre_proveedor',
            'proveedor_id',
            'extension',
            'tmpfname'
        ];

        foreach ($campos_por_validar as $value) {
            if (is_null_or_empty_string($data[$value])) {
                set_status_code_response(400, "El campo ' . $value . ' es obligatorio.", null);
            }
        }

        $file_data = array();

        $ext = $data["extension"];
        $nombre_archivo = $data['nombre_archivo'];
        $proveedor_id = $data["proveedor_id"];
        $tmpfname = $data['tmpfname'];
        $nombre_proveedor_clave = str_replace(" ", "_", strtolower($data["nombre_proveedor"]));
        if ($ext == "xls" || $ext == "xlsx") {
            //include("sys_login.php");
            include("/var/www/html/sys/helpers.php");
            require_once '../phpexcel/classes/PHPExcel.php';

            $file_data_maestro = [
                "nombre_archivo" => $nombre_archivo,
                "proveedor_id" => $proveedor_id,
            ];

            $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
            libxml_use_internal_errors(true);

            $excelObj = $excelReader->load($tmpfname);

            $worksheet = $excelObj->getSheet($start_sheet);

            $initRow = 2;
            $initColumn = 0;

            if ($nombre_proveedor_clave == "prosegur") {
                $initColumn = 3;
            }

            $file_data_detalle = get_data_detalle($worksheet, $fields_provider[$nombre_proveedor_clave], $initRow, $initColumn, $file_columns);

            if ($nombre_proveedor_clave == "direc_tv") {
                foreach ($file_data_detalle as $index => $row) {
                    if (is_null_or_empty_string($row["cuenta_secundaria"])) {
                        $row["cuenta_secundaria"] = $row["cuenta_principal"];
                        $file_data_detalle[$index] = $row;
                    }
                }
            }


            if ($nombre_proveedor_clave == "movistar") {

                foreach ($file_data_detalle as $index => $row) {
                    $value = $row["desc_facturacion"];
                    if (!is_null_or_empty_string($value)) {
                        $value = str_replace(
                            array('｣', '｡', '｢', 'Ｔ', '', '\'', '¢', '‚s'),
                            array('ú', 'í', 'ó', 'és', 'á', '', 'ó', 'es'),
                            $value
                        );

                        $row["desc_facturacion"] = $value;

                        $concepto_facturable = preg_replace("/\(([^()]*+|(?R))*\)/", "", $value);
                        $concepto_facturable = preg_replace("!\s+!", " ", $concepto_facturable);
                        $concepto_facturable = str_replace(
                            array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú"),
                            array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U"),
                            $concepto_facturable
                        );

                        $concepto_facturable = trim(strtoupper($concepto_facturable));

                        if (strpos($concepto_facturable, 'DEV.REC F') !== false) {
                            $concepto_facturable = 'DEV.REC F';
                        }

                        if (strpos($concepto_facturable, 'INT.DEV.F') !== false) {
                            $concepto_facturable = 'INT.DEV.F';
                        }

                        if (strpos($concepto_facturable, 'CUOTA') !== false) {
                            $words = explode(' ', $concepto_facturable);
                            if ($words && count($words) == 3 && $words[0] == "CUOTA" && $words[2] == "INSTALACION") {
                                $concepto_facturable = "CUOTA INSTALACION";
                            }
                        }


                        $row["concepto_facturable"] = $concepto_facturable;

                        $file_data_detalle[$index] = $row;
                    }
                }
            }

            if ($nombre_proveedor_clave == "niubiz") {
                foreach ($file_data_detalle as $index => $row) {
                    $comision_niubiz = $row["comision_niubiz"];
                    $otros_cargos_abonos = $row["otros_cargos_abonos"];
                    $igv = $row["igv"];
                    $row["comision_visa"] = $comision_niubiz + $otros_cargos_abonos + $igv;
                    $file_data_detalle[$index] = $row;
                }
            }

            $file_data["maestro"][0] = $file_data_maestro;
            $file_data["detalle"] = $file_data_detalle;

            $excelObj->disconnectWorksheets();
            $excelObj->garbageCollect();
            unset($excelObj);
        } else {
            set_status_code_response(400, "Extensión de archivo incorrecta, solo se permiten archivos xls y xlsx.", null);
        }
        return $file_data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_data_detalle($worksheet, $fields, $initRow, $initColumn, $file_columns)
{
    try {
        $data_detalle = array();
        $firstCellValue = "";
        $data_row = array();;
        $currentColumn = $initColumn;
        $currentRow = $initRow;
        do {
            $firstCellValue = trim($worksheet->getCell($file_columns[$currentColumn] . $currentRow)->getValue());
            if ($firstCellValue != "") {
                foreach ($fields as $column_name => $data_type) {
                    $cell = $worksheet->getCell($file_columns[$currentColumn] . $currentRow);
                    switch ($data_type) {
                        case "datetime":
                            $cellValue = trim($cell->getValue());
                            if (PHPExcel_Shared_Date::isDateTime($cell)) {
                                $cellValue = PHPExcel_Style_NumberFormat::toFormattedString($cellValue, 'DD/MM/YYYY hh:mm:ss');
                            } else {
                                $cellValue = excel_date_to_php($cellValue);
                            }
                            break;
                        case "int":
                            $cellValue = $cell->getCalculatedValue();
                            $cellValue = intval($cellValue);
                            break;
                        case "decimal":
                            $cellValue = $cell->getCalculatedValue();
                            //$cellValue = number_format($cellValue, 2, '.', ',');
                            $cellValue = floatval($cellValue);
                            break;
                        default:
                            $cellValue = trim($cell->getValue());
                    }
                    $data_row[$column_name] = $cellValue;
                    $currentColumn++;
                }
                array_push($data_detalle, $data_row);
            }
            $currentColumn = $initColumn;
            $currentRow++;
        } while ($firstCellValue != "");
        return $data_detalle;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function guardar_archivo_proveedor_por_lotes($data, $fields_provider)
{
    try {
        if (count($data["detalle"]) > 0) {
            include("db_connect.php");
            $archivo_proveedor_id = 0;
            $data_maestro = $data["maestro"][0];

            $proveedor_id = $data_maestro["proveedor_id"];

            $mysqli->query("START TRANSACTION");

            $sql_query = "SELECT nombre as nombre_proveedor FROM tbl_concar_proveedores WHERE id = ?";

            $stmt = $mysqli->prepare($sql_query);

            bind_param_statement($stmt, "i", [$proveedor_id]);

            $stmt_result = execute_sql_statement($stmt);

            $nombre_proveedor = $stmt_result->fetch_object()->nombre_proveedor;

            $insert_command_maestro = "INSERT INTO tbl_concar_archivo_proveedor_maestro (nombre_archivo, created_at, updated_at, proveedor_id) VALUES ('{$data_maestro["nombre_archivo"]}', NOW(), NOW(), '{$data_maestro["proveedor_id"]}')";

            $mysqli->query($insert_command_maestro);

            if ($mysqli->error) {
                set_status_code_response(500, $mysqli->error, null);
            }

            $archivo_proveedor_id = mysqli_insert_id($mysqli);

            $nombre_proveedor_clave = str_replace(" ", "_", strtolower($nombre_proveedor));

            $str_select = implode(', ', array_keys($fields_provider[$nombre_proveedor_clave]));
            $insert_command_detalle = "";
            $insert_command_detalle_base = "INSERT INTO tbl_concar_archivo_proveedor_detalle ({$str_select}, " . implode(', ', array_keys($fields_provider['common'])) . ") VALUES ";
            $insert_command_detalle_items = "";
            $fields = $fields_provider[$nombre_proveedor_clave];

            $data_detalle = $data["detalle"];

            $count = 0;
            foreach ($data_detalle as $item_detalle) {

                if ($count >= 1000) {
                    $insert_command_detalle_items = substr($insert_command_detalle_items, 0, -1);
                    $insert_command_detalle = $insert_command_detalle_base . $insert_command_detalle_items;
                    $mysqli->query($insert_command_detalle);
                    $insert_command_detalle_items = "";
                    $count = 0;
                }

                $insert_command_detalle_items .= get_insert_command_row($fields, $item_detalle, $archivo_proveedor_id);
                $count++;
            }

            if (strlen($insert_command_detalle_items)) {
                $insert_command_detalle_items = substr($insert_command_detalle_items, 0, -1);
                $insert_command_detalle = $insert_command_detalle_base . $insert_command_detalle_items;
                $mysqli->query($insert_command_detalle);
            }

            if ($mysqli->error) {
                set_status_code_response(500, $mysqli->error, null);
            }

            $mysqli->query("COMMIT");
        } else {
            set_status_code_response(400, "No se ha logrado procesar el archivo, es posible que alguna columna, fila o celda indispensable esté vacía. Verifique que el archivo corresponda al proveedor seleccionado", null);
        }

        return $archivo_proveedor_id;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_insert_command_row($fields, $item_detalle, $archivo_proveedor_id)
{
    try {
        $insert_command_detalle = "(";

        foreach ($fields as $key => $value) {
            switch ($value) {
                case "datetime":
                    $insert_command_detalle .= (!is_null_or_empty_string($item_detalle[$key]) ? "STR_TO_DATE('{$item_detalle[$key]}', '%d/%m/%Y %H:%i:%s')" : "NULL") . ", ";
                    break;
                case "decimal":
                    $insert_command_detalle .= "REPLACE('{$item_detalle[$key]}', ',', '.'), ";
                    break;
                default:
                    $insert_command_detalle .= "'{$item_detalle[$key]}', ";
            }
        }

        $insert_command_detalle .= "'{$archivo_proveedor_id}', NOW(), NOW()),";

        return $insert_command_detalle;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_concar_proveedores()
{
    try {
        include("db_connect.php");
        $proveedores_arr = array();
        $proveedores_command = "SELECT p.id, p.nombre FROM tbl_concar_proveedores p";
        $proveedores_query = $mysqli->query($proveedores_command);
        if ($mysqli->error) {
            set_status_code_response(500, $mysqli->error, null);
        }

        while ($p = $proveedores_query->fetch_assoc()) {
            $proveedores_arr[$p["id"]] = $p["nombre"];
        }

        echo json_encode($proveedores_arr);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_data_archivos_proveedor_maestro()
{
    try {
        include("db_connect.php");

        $sql_query = "SELECT m.id, m.nombre_archivo, m.created_at, m.numero_documento, m.proveedor_id, p.nombre AS 'nombre_proveedor', m.estado FROM tbl_concar_archivo_proveedor_maestro m INNER JOIN tbl_concar_proveedores p ON m.proveedor_id = p.id ORDER BY m.estado, m.created_at desc;";

        $sql_result = $mysqli->query($sql_query);

        if ($mysqli->error) {
            set_status_code_response(500, $mysqli->error, null);
        }

        $data = array();

        while ($row = $sql_result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode($data);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_data_archivos_proveedor_detalle($archivo_proveedor_id, $fields_provider)
{
    try {

        include("db_connect.php");

        $sql_query = "SELECT m.proveedor_id FROM tbl_concar_archivo_proveedor_maestro m WHERE m.id = ? LIMIT 1;";

        $stmt = $mysqli->prepare($sql_query);

        bind_param_statement($stmt, "i", [$archivo_proveedor_id]);

        $stmt_result = execute_sql_statement($stmt);

        $value = $stmt_result->fetch_object();

        $proveedor_id = $value->proveedor_id;

        $sql_query = "SELECT p.nombre as nombre_proveedor FROM tbl_concar_proveedores p WHERE p.id = ? LIMIT 1;";

        $stmt = $mysqli->prepare($sql_query);

        bind_param_statement($stmt, "i", [$proveedor_id]);

        $stmt_result = execute_sql_statement($stmt);

        $value = $stmt_result->fetch_object();

        $nombre_proveedor = str_replace(" ", "_", strtolower($value->nombre_proveedor));

        $sql_select_fields_provider = implode(", d.", array_keys($fields_provider[$nombre_proveedor]));
        $sql_select_fields_common = implode(", d.", array_keys($fields_provider["common"]));

        $sql_query = "SELECT d.id, d.{$sql_select_fields_common}, d.{$sql_select_fields_provider} FROM tbl_concar_archivo_proveedor_detalle d INNER JOIN tbl_concar_archivo_proveedor_maestro m ON d.archivo_proveedor_id = m.id INNER JOIN tbl_concar_proveedores p ON m.proveedor_id = p.id WHERE m.id = ? ORDER BY  d.created_at DESC;";

        $stmt = $mysqli->prepare($sql_query);

        bind_param_statement($stmt, "i", [$archivo_proveedor_id]);

        $stmt_result = execute_sql_statement($stmt);

        $data = [];

        while ($row_data = $stmt_result->fetch_assoc()) {
            $data[] = $row_data;
        }

        echo json_encode($data);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_archivo_proveedor($archivo_proveedor_id)
{
    try {
        set_time_limit(0);
        include("db_connect.php");
        $mysqli->query("START TRANSACTION");
        $sql_query = "DELETE FROM tbl_concar_archivo_proveedor_detalle WHERE archivo_proveedor_id = ?";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$archivo_proveedor_id]);
        execute_sql_statement($stmt);
        $sql_query = "DELETE FROM tbl_concar_archivo_proveedor_maestro WHERE id = ?";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$archivo_proveedor_id]);
        execute_sql_statement($stmt);
        $mysqli->query("COMMIT");
        $stmt->close();
        return true;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function execute_sql_statement($stmt)
{
    include("db_connect.php");
    $rc = $stmt->execute();
    if (false === $rc) {
        set_status_code_response(500, htmlspecialchars($stmt->error), null);
    }
    return $stmt->get_result();
}

function get_data_concar_por_rango_fechas($data)
{
    try {
        date_default_timezone_set("America/Lima");
        setlocale(LC_ALL, "es_ES");

        $proveedor_id = $data["proveedor_id"];
        $proveedor = get_proveedor($proveedor_id);
        $nombre_proveedor = $proveedor["nombre"];
        $ruc_proveedor = $proveedor["ruc"];
        $nombre_proveedor_clave = str_replace(" ", "_", strtolower($nombre_proveedor));

        $campos_fecha_por_validar = [
            "fecha_creacion_desde",
            "fecha_creacion_hasta",
            "fecha_comprobante",
            "fecha_emision",
            "fecha_vencimiento"
        ];

        $campos_texto_por_validar = [
            "numero_comprobante"
        ];

        if ($nombre_proveedor_clave != "movistar") {
            array_push($campos_texto_por_validar, "numero_documento");
        }

        $campos_por_validar = array_merge($campos_texto_por_validar, $campos_fecha_por_validar);

        foreach ($campos_por_validar as $value) {
            if (is_null_or_empty_string($data[$value])) {
                set_status_code_response(400, "El campo ' . $value . ' es obligatorio.", null);
            }
        }

        $numero_comprobante = $data["numero_comprobante"];
        $numero_documento = $data["numero_documento"];


        $data_fechas_por_validar = [];
        foreach ($campos_fecha_por_validar as $value) {
            $data_fechas_por_validar[$value] = $data[$value];
        }

        $fechas_validas = validar_formato_fechas($data_fechas_por_validar);

        $fecha_creacion_desde = $fechas_validas["fecha_creacion_desde"];
        $fecha_creacion_hasta = $fechas_validas["fecha_creacion_hasta"];
        $fecha_comprobante = $fechas_validas["fecha_comprobante"];
        $fecha_emision = $fechas_validas["fecha_emision"];
        $fecha_vencimiento = $fechas_validas["fecha_vencimiento"];

        $fecha_creacion_desde->setTime(0, 0, 0);
        $fecha_creacion_hasta->setTime(23, 59, 59);

        if ($fecha_creacion_desde > $fecha_creacion_hasta) {
            set_status_code_response(400, "El campo Fecha Creación Hasta debe ser mayor.", null);
        }

        $dbfrmt_fecha_creacion_desde = $fecha_creacion_desde->format('Y-m-d H:i:s');
        $dbfrmt_fecha_creacion_hasta = $fecha_creacion_hasta->format('Y-m-d H:i:s');
        $dbfrmt_fecha_comprobante = $fecha_comprobante->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $frmt_fecha_comprobante = $fecha_comprobante->format('d/m/Y');
        $frmt_fecha_emision = $fecha_emision->format('d/m/Y');
        $frmt_fecha_vencimiento = $fecha_vencimiento->format('d/m/Y');
        $numero_mes_fecha_comprobante = intval($fecha_comprobante->format('n'));
        $nombre_mes_fecha_comprobante = obtener_nombre_del_mes($numero_mes_fecha_comprobante);
        $frmt_numero_mes_fecha_comprobante = $fecha_comprobante->format("m");
        $tipo_cambio = get_tipo_cambio($dbfrmt_fecha_comprobante);
        if ($tipo_cambio == null) {
            //$tipo_cambio = 3.75;
            set_status_code_response(500, "No existe tipo de cambio para la fecha de comprobante " . $frmt_fecha_comprobante, null);
        }

        include("db_connect.php");

        $str_params = "ssi";
        $params = [$dbfrmt_fecha_creacion_desde, $dbfrmt_fecha_creacion_hasta, $proveedor_id];
        switch ($nombre_proveedor_clave) {
            case "direc_tv":
                $sql_query = "SELECT " .
                    "apd.cuenta_principal " .
                    ",apd.cuenta_secundaria " .
                    ",nc.nro_cuenta " .
                    ",cc.ceco " .
                    ",cc.local " .
                    ",SUM(apd.monto_inc_igv) monto_inc_igv " .
                    "FROM " .
                    "tbl_concar_archivo_proveedor_maestro apm " .
                    "INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON apm.id = apd.archivo_proveedor_id " .
                    "LEFT JOIN tbl_concar_numeros_cuenta nc ON apd.cuenta_secundaria = nc.nro_cuenta " .
                    "LEFT JOIN tbl_concar_centros_costo cc ON nc.ceco = cc.ceco " .
                    "WHERE apm.created_at BETWEEN ? AND ? AND apm.proveedor_id = ? AND apm.estado = 0 " .
                    "GROUP BY apd.cuenta_secundaria, cc.ceco " .
                    "ORDER BY " .
                    "ceco, nro_cuenta;";

                break;
            case "movistar":
                $sql_query = "SELECT prm.nombre_proveedor, prm.ruc, prm.telf_agrupador, prm.factura, prm.cod_pago, prm.ceco, prm.local, prm.desc_facturacion, prm.cta_contable, (prm.monto_inc_igv / count(*)) AS monto_inc_igv, IF(prm.monto_inc_igv > 0, 'D', 'H') debe_haber " .
                    "FROM (" .
                    "SELECT p.nombre as nombre_proveedor, p.ruc, apd.telf_agrupador, apd.factura, cp.cod_pago, cc.ceco, cc.local, apd.desc_facturacion, cf.cta_contable, apd.monto_inc_igv " .
                    "FROM tbl_concar_archivo_proveedor_maestro apm " .
                    "INNER JOIN tbl_concar_proveedores p ON  apm.proveedor_id = p.id " .
                    "INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON  apm.id = apd.archivo_proveedor_id " .
                    "LEFT JOIN tbl_concar_codigos_pago cp ON  cp.cod_pago = apd.telf_afiliado " .
                    "LEFT JOIN tbl_concar_centros_costo cc ON  cp.ceco = cc.ceco " .
                    "LEFT  JOIN tbl_concar_conceptos_facturables cf ON UPPER(apd.concepto_facturable) = UPPER(cf.concepto) " .
                    "WHERE (apm.created_at BETWEEN ? AND ?) AND apm.proveedor_id = ? AND apm.estado = 0 " .
                    "ORDER BY cp.cod_pago" .
                    ") AS prm " .
                    "GROUP BY prm.cod_pago, monto_inc_igv" .
                    "ORDER BY prm.cod_pago, monto_inc_igv; ";
                break;
            case "niubiz":
                $str_params = "ssissi";
                $params = [
                    $dbfrmt_fecha_creacion_desde,
                    $dbfrmt_fecha_creacion_hasta,
                    $proveedor_id,
                    $dbfrmt_fecha_creacion_desde,
                    $dbfrmt_fecha_creacion_hasta,
                    $proveedor_id
                ];
                $sql_query = "(" .
                    "SELECT " .
                    "cc.ceco, " .
                    "p.ruc, " .
                    "'639301' as cta_contable, " .
                    "SUM(apd.comision_emisores) as monto_inc_igv, " .
                    "'D' as debe_haber " .
                    "FROM " .
                    "tbl_concar_archivo_proveedor_maestro as apm " .
                    "INNER JOIN tbl_concar_proveedores p ON apm.proveedor_id = p.id " .
                    "INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON apd.archivo_proveedor_id = apm.id " .
                    "INNER JOIN tbl_concar_codigos_comercio cc ON  apd.codigo_comercio = cc.cod_comercio " .
                    "WHERE apm.created_at BETWEEN ? AND ? " .
                    "AND apm.proveedor_id = ? " .
                    "AND apm.estado = 0 " .
                    "GROUP BY " .
                    "apd.codigo_comercio, cc.ceco " .
                    ") " .
                    "UNION ALL " .
                    "( " .
                    "SELECT " .
                    "'' as ceco, " .
                    "b.ruc, " .
                    "'421201' as cta_contable, " .
                    "SUM(db.importe) as monto_inc_igv, " .
                    "'H' as debe_haber " .
                    "FROM " .
                    "tbl_concar_archivo_proveedor_maestro as apm " .
                    "INNER JOIN tbl_concar_proveedores p ON apm.proveedor_id = p.id " .
                    "INNER JOIN tbl_concar_detalle_bancos db ON db.archivo_proveedor_id = apm.id " .
                    "INNER JOIN tbl_concar_bancos b ON db.banco_id = b.id " .
                    "WHERE apm.created_at BETWEEN ? AND ? " .
                    "AND apm.proveedor_id = ? " .
                    "AND apm.estado = 0 " .
                    "GROUP BY " .
                    "db.banco_id " .
                    ");";

                break;
            case "prosegur":
                $sql_query = "SELECT " .
                    "'' ceco, " .
                    "apd.tarifa_os_igv monto_inc_igv " .
                    "FROM " .
                    "tbl_concar_archivo_proveedor_maestro apm " .
                    "INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON apm.id = apd.archivo_proveedor_id " .
                    "WHERE apm.created_at BETWEEN ? AND ? " .
                    "AND apm.proveedor_id = ? " .
                    "AND apm.estado = 0 " .
                    "ORDER BY " .
                    "fecha_creacion desc;";
                break;
        }

        $stmt = $mysqli->prepare($sql_query);

        bind_param_statement($stmt, $str_params, $params);

        $result = execute_sql_statement($stmt);

        $data = [];
        $response = [];
        $file_name = "";
        $suma_importe_original = 0;
        $suma_importe_dolares = 0;

        $telf_agrupador = "";
        while ($row = $result->fetch_assoc()) {
            $importe_original = floatval($row["monto_inc_igv"]);
            $suma_importe_original += $importe_original;
            $importe_dolares = $importe_original / $tipo_cambio;
            $suma_importe_dolares += $importe_dolares;

            switch ($nombre_proveedor_clave) {
                case "direc_tv":

                    $glosa_principal = "DIRECTV " . (!is_null_or_empty_string($row["cuenta_principal"]) ? $row["cuenta_principal"] : "(SIN CUENTA PRINCIPAL)") . " " . $nombre_mes_fecha_comprobante;
                    $glosa_detalle = "DIRECTV " . (!is_null_or_empty_string($row["cuenta_secundaria"]) ? $row["cuenta_secundaria"] : "(SIN CUENTA SECUNDARIA)") . " " . $row["local"];
                    $glosa_principal = mb_substr($glosa_principal, 0, 40, "UTF-8");
                    $glosa_detalle = mb_substr($glosa_detalle, 0, 30, "UTF-8");
                    $data[] =
                        [
                            "campo" => "",
                            "sub_diario" => "3120",
                            "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                            "fecha_comprobante" => $frmt_fecha_comprobante,
                            "codigo_moneda" => "MN",
                            "glosa_principal" => $glosa_principal,
                            "tipo_cambio" => $tipo_cambio,
                            "tipo_conversion" => "V",
                            "flag_conversion_moneda" => "S",
                            "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                            "cuenta_contable" => "636701",
                            "codigo_anexo" => $ruc_proveedor,
                            "codigo_centro_costo" => $row["ceco"],
                            "debe_haber" => "D",
                            "importe_original" => number_format($importe_original, 2, '.', ''),
                            "importe_dolares" => number_format($importe_dolares, 2, '.', ''),
                            "importe_soles" => number_format($importe_original, 2, '.', ''),
                            "tipo_documento" => "RC",
                            "numero_documento" => $numero_documento,
                            "fecha_documento" => $frmt_fecha_emision,
                            "fecha_vencimiento" => $frmt_fecha_vencimiento,
                            "codigo_area" => "",
                            "glosa_detalle" => $glosa_detalle,
                            "codigo_anexo_auxiliar" => "01",
                            "medio_pago" => "",
                            "tipo_documento_referencia" => "",
                            "numero_documento_referencia" => "",
                            "fecha_documento_referencia" => "",
                            "nro_maq_registradora_tipo_doc_ref." => "",
                            "base_imponible_documento_referencia" => "",
                            "igv_documento_provision" => "",
                            "tipo_referencia_estado_mq" => "",
                            "numero_serie_caja_registradora" => "",
                            "fecha_operacion" => "",
                            "tipo_tasa" => "",
                            "tasa_detraccion_percepcion" => "",
                            "importe_base_detraccion_percepcion_dolares" => "",
                            "importe_base_detraccion_percepcion_soles" => "",
                            "tipo_cambio_para_f" => "",
                            "importe_igv_sin_derecho_credito_fiscal" => ""
                        ];
                    break;
                case "movistar":
                    $glosa = "TRIO-DUO";
                    $glosa .= (!is_null_or_empty_string($row["cod_pago"])) ? " " . $row["cod_pago"] : " " . "(SIN CÖDIGO DE PAGO)";
                    $glosa .= (!is_null_or_empty_string($row["local"])) ? " " . $row["local"] : " " . "(SIN LOCAL)";
                    $telf_agrupador = $row["telf_agrupador"];
                    $glosa_princpal = mb_substr($glosa, 0, 40, "UTF-8");
                    $glosa_detalle = mb_substr($glosa, 0, 30, "UTF-8");
                    $data[] =
                        [
                            "campo" => "",
                            "sub_diario" => "3120",
                            "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                            "fecha_comprobante" => $frmt_fecha_comprobante,
                            "codigo_moneda" => "MN",
                            "glosa_principal" => $glosa_princpal,
                            "tipo_cambio" => $tipo_cambio,
                            "tipo_conversion" => "V",
                            "flag_conversion_moneda" => "S",
                            "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                            "cuenta_contable" => $row["cta_contable"],
                            "codigo_anexo" => $ruc_proveedor,
                            "codigo_centro_costo" => $row["ceco"],
                            "debe_haber" => $row["debe_haber"],
                            "importe_original" => number_format($importe_original, 2, '.', ''),
                            "importe_dolares" => number_format($importe_dolares, 2, '.', ''),
                            "importe_soles" => number_format($importe_original, 2, '.', ''),
                            "tipo_documento" => "RC",
                            "numero_documento" => $row["factura"],
                            "fecha_documento" => $frmt_fecha_emision,
                            "fecha_vencimiento" => $frmt_fecha_vencimiento,
                            "codigo_area" => "",
                            "glosa_detalle" => $glosa_detalle,
                            "codigo_anexo_auxiliar" => "01",
                            "medio_pago" => "",
                            "tipo_documento_referencia" => "",
                            "numero_documento_referencia" => "",
                            "fecha_documento_referencia" => "",
                            "nro_maq_registradora_tipo_doc_ref." => "",
                            "base_imponible_documento_referencia" => "",
                            "igv_documento_provision" => "",
                            "tipo_referencia_estado_mq" => "",
                            "numero_serie_caja_registradora" => "",
                            "fecha_operacion" => "",
                            "tipo_tasa" => "",
                            "tasa_detraccion_percepcion" => "",
                            "importe_base_detraccion_percepcion_dolares" => "",
                            "importe_base_detraccion_percepcion_soles" => "",
                            "tipo_cambio_para_f" => "",
                            "importe_igv_sin_derecho_credito_fiscal" => ""
                        ];
                    break;
                case "niubiz":
                    $glosa = "COMISION EMISORES " . " " . substr($nombre_mes_fecha_comprobante, 0, 3);
                    $glosa_princpal = mb_substr($glosa, 0, 40, "UTF-8");
                    $glosa_detalle = mb_substr($glosa, 0, 30, "UTF-8");
                    $data[] =
                        [
                            "campo" => "",
                            "sub_diario" => "3120",
                            "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                            "fecha_comprobante" => $frmt_fecha_comprobante,
                            "codigo_moneda" => "MN",
                            "glosa_principal" => $glosa_princpal,
                            "tipo_cambio" => $tipo_cambio,
                            "tipo_conversion" => "V",
                            "flag_conversion_moneda" => "S",
                            "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                            "cuenta_contable" => $row["cta_contable"],
                            "codigo_anexo" => $row["ruc"],
                            "codigo_centro_costo" => $row["ceco"],
                            "debe_haber" => $row["debe_haber"],
                            "importe_original" => number_format($importe_original, 2, '.', ''),
                            "importe_dolares" => "",
                            "importe_soles" => "",
                            "tipo_documento" => "DA",
                            "numero_documento" => $numero_documento,
                            "fecha_documento" => $frmt_fecha_emision,
                            "fecha_vencimiento" => $frmt_fecha_vencimiento,
                            "codigo_area" => "",
                            "glosa_detalle" => $glosa_detalle,
                            "codigo_anexo_auxiliar" => "01",
                            "medio_pago" => "",
                            "tipo_documento_referencia" => "",
                            "numero_documento_referencia" => "",
                            "fecha_documento_referencia" => "",
                            "nro_maq_registradora_tipo_doc_ref." => "",
                            "base_imponible_documento_referencia" => "",
                            "igv_documento_provision" => "",
                            "tipo_referencia_estado_mq" => "",
                            "numero_serie_caja_registradora" => "",
                            "fecha_operacion" => "",
                            "tipo_tasa" => "",
                            "tasa_detraccion_percepcion" => "",
                            "importe_base_detraccion_percepcion_dolares" => "",
                            "importe_base_detraccion_percepcion_soles" => "",
                            "tipo_cambio_para_f" => "",
                            "importe_igv_sin_derecho_credito_fiscal" => ""
                        ];
                    break;
                case "prosegur":
                    $glosa = "PROV PROSEGUR" . " " . $nombre_mes_fecha_comprobante;
                    $glosa_princpal = mb_substr($glosa, 0, 40, "UTF-8");
                    $glosa_detalle = mb_substr($glosa, 0, 30, "UTF-8");
                    $data[] =
                        [
                            "campo" => "",
                            "sub_diario" => "3120",
                            "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                            "fecha_comprobante" => $frmt_fecha_comprobante,
                            "codigo_moneda" => "MN",
                            "glosa_principal" => $glosa_princpal,
                            "tipo_cambio" => $tipo_cambio,
                            "tipo_conversion" => "V",
                            "flag_conversion_moneda" => "S",
                            "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                            "cuenta_contable" => "639501",
                            "codigo_anexo" => $ruc_proveedor,
                            "codigo_centro_costo" => $row["ceco"],
                            "debe_haber" => "D",
                            "importe_original" => number_format($importe_original, 2, '.', ''),
                            "importe_dolares" => "",
                            "importe_soles" => "",
                            "tipo_documento" => "VR",
                            "numero_documento" => $numero_documento,
                            "fecha_documento" => $frmt_fecha_emision,
                            "fecha_vencimiento" => $frmt_fecha_vencimiento,
                            "codigo_area" => "",
                            "glosa_detalle" => $glosa_detalle,
                            "codigo_anexo_auxiliar" => "01",
                            "medio_pago" => "",
                            "tipo_documento_referencia" => "",
                            "numero_documento_referencia" => "",
                            "fecha_documento_referencia" => "",
                            "nro_maq_registradora_tipo_doc_ref." => "",
                            "base_imponible_documento_referencia" => "",
                            "igv_documento_provision" => "",
                            "tipo_referencia_estado_mq" => "",
                            "numero_serie_caja_registradora" => "",
                            "fecha_operacion" => "",
                            "tipo_tasa" => "",
                            "tasa_detraccion_percepcion" => "",
                            "importe_base_detraccion_percepcion_dolares" => "",
                            "importe_base_detraccion_percepcion_soles" => "",
                            "tipo_cambio_para_f" => "",
                            "importe_igv_sin_derecho_credito_fiscal" => ""
                        ];
                    break;
            }
        }

        if (count($data) > 0) {
            $summary_data = [];
            switch ($nombre_proveedor_clave) {
                case "direc_tv":
                    $summary_data = end($data);
                    $summary_data["glosa_principal"] = "";
                    $summary_data["cuenta_contable"] = "421101";
                    $summary_data["debe_haber"] = "H";
                    $summary_data["importe_original"] = round($suma_importe_original, 2);
                    $summary_data["importe_dolares"] = round($suma_importe_dolares, 2);
                    $summary_data["importe_soles"] = round($suma_importe_original, 2);
                    $summary_data["glosa_detalle"] = "";
                    $data[] = $summary_data;
                    $file_name = "PLANTILLA_CONCAR_direc_tv";
                    break;
                case "movistar":
                    $glosa = "AGRUP" . " " . $telf_agrupador . " " . $nombre_mes_fecha_comprobante;
                    $summary_data = end($data);
                    $summary_data["glosa_principal"] = $glosa;
                    $summary_data["cuenta_contable"] = "421101";
                    $summary_data["debe_haber"] = "H";
                    $summary_data["importe_original"] = round($suma_importe_original, 2);
                    $summary_data["importe_dolares"] = round($suma_importe_dolares, 2);
                    $summary_data["importe_soles"] = round($suma_importe_original, 2);
                    $summary_data["glosa_detalle"] = $glosa;
                    $data[] = $summary_data;
                    $file_name = "PLANTILLA_CONCAR_MOVISTAR";
                    break;
                case "niubiz":
                    $file_name = "PLANTILLA_CONCAR_COMISION_EMISORES";
                    break;
                case "prosegur":
                    $summary_data = end($data);
                    $summary_data["cuenta_contable"] = "421101";
                    $summary_data["debe_haber"] = "H";
                    $summary_data["importe_original"] = round($suma_importe_original, 2);
                    $data[] = $summary_data;
                    $file_name = "PLANTILLA_CONCAR_PROSEGUR";
                    break;
            }
        }

        $stmt->close();
        $response[$nombre_proveedor_clave][$file_name] = $data;
        return $response;

        //return [$nombre_proveedor_clave => $data];
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_data_concar_por_archivo_proveedor_ids($data)
{
    try {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '500');
        set_time_limit(0);
        date_default_timezone_set("America/Lima");
        setlocale(LC_ALL, "es_ES");

        $campos_fecha_por_validar = [
            "fecha_comprobante",
            "fecha_emision",
            "fecha_vencimiento"
        ];

        $campos_texto_por_validar = [
            "numero_comprobante"
        ];

        $campos_por_validar = array_merge($campos_texto_por_validar, $campos_fecha_por_validar);

        foreach ($campos_por_validar as $value) {
            if (is_null_or_empty_string($data[$value])) {
                set_status_code_response(400, "El campo ' . $value . ' es obligatorio.", null);
            }
        }

        if (!(count($data["archivo_proveedor_ids"]) > 0)) {
            set_status_code_response(400, "El campo 'archivo_proveedor_ids' es obligatorio.", null);
        }

        $numero_comprobante = $data["numero_comprobante"];
        $numero_documento = $data["numero_documento"];
        $archivo_proveedor_ids = $data["archivo_proveedor_ids"];

        $data_fechas_por_validar = [];
        foreach ($campos_fecha_por_validar as $value) {
            $data_fechas_por_validar[$value] = $data[$value];
        }

        $fechas_validas = validar_formato_fechas($data_fechas_por_validar);

        $fecha_comprobante = $fechas_validas["fecha_comprobante"];
        $fecha_emision = $fechas_validas["fecha_emision"];
        $fecha_vencimiento = $fechas_validas["fecha_vencimiento"];
        $dbfrmt_fecha_comprobante = $fecha_comprobante->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $frmt_fecha_comprobante = $fecha_comprobante->format('d/m/Y');
        $frmt_fecha_emision = $fecha_emision->format('d/m/Y');
        $frmt_fecha_vencimiento = $fecha_vencimiento->format('d/m/Y');
        $numero_mes_fecha_comprobante = intval($fecha_comprobante->format('n'));
        $nombre_mes_fecha_comprobante = obtener_nombre_del_mes($numero_mes_fecha_comprobante);
        $frmt_numero_mes_fecha_comprobante = $fecha_comprobante->format("m");
        $tipo_cambio = get_tipo_cambio($dbfrmt_fecha_comprobante);
        if ($tipo_cambio == null) {
            set_status_code_response(500, "No existe tipo de cambio para la fecha de comprobante " . $frmt_fecha_comprobante, null);
        }
        $data = [];

        include("db_connect.php");

        foreach ($archivo_proveedor_ids as $proveedores) {
            $sql_query = "";
            foreach ($proveedores as $proveedor_id => $archivo_proveedor_ids) {

                $proveedor = get_proveedor($proveedor_id);
                $nombre_proveedor = $proveedor["nombre"];
                $ruc_proveedor = $proveedor["ruc"];
                $nombre_proveedor_clave = str_replace(" ", "_", strtolower($nombre_proveedor));
                if ($nombre_proveedor_clave != "movistar" && is_null_or_empty_string($numero_documento)) {
                    set_status_code_response(400, "El campo 'Número de Documento' es obligatorio.", null);
                }

                $archivo_proveedor_ids = implode(", ", $archivo_proveedor_ids);

                switch ($nombre_proveedor_clave) {
                    case "direc_tv":
                        $sql_query = "SELECT " .
                            "apd.cuenta_principal " .
                            ",apd.cuenta_secundaria " .
                            ",nc.nro_cuenta " .
                            ",cc.ceco " .
                            ",cc.local " .
                            ",SUM(apd.monto_inc_igv) monto_inc_igv " .
                            "FROM " .
                            "tbl_concar_archivo_proveedor_maestro apm " .
                            "INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON apm.id = apd.archivo_proveedor_id " .
                            "LEFT JOIN tbl_concar_numeros_cuenta nc ON apd.cuenta_secundaria = nc.nro_cuenta " .
                            "LEFT JOIN tbl_concar_centros_costo cc ON nc.ceco = cc.ceco " .
                            "WHERE apm.estado = 0 AND apm.id IN ({$archivo_proveedor_ids}) " .
                            "GROUP BY apd.cuenta_secundaria " .
                            "ORDER BY " .
                            "cc.ceco, nc.nro_cuenta;";

                        $stmt = $mysqli->prepare($sql_query);

                        $result = execute_sql_statement($stmt);

                        $data_direc_tv = [];

                        $suma_importe_original = 0;
                        $suma_importe_dolares = 0;

                        while ($row = $result->fetch_assoc()) {
                            $importe_original = floatval($row["monto_inc_igv"]);
                            $suma_importe_original += $importe_original;
                            $importe_dolares = $importe_original / $tipo_cambio;
                            $suma_importe_dolares += $importe_dolares;
                            $glosa_principal = "DIRECTV " . (!is_null_or_empty_string($row["cuenta_principal"]) ? $row["cuenta_principal"] : "(SIN CUENTA PRINCIPAL)") . " " . $nombre_mes_fecha_comprobante;
                            $glosa_detalle = "DIRECTV " . (!is_null_or_empty_string($row["cuenta_secundaria"]) ? $row["cuenta_secundaria"] : "(SIN CUENTA SECUNDARIA)") . " " . $row["local"];
                            $glosa_principal = mb_substr($glosa_principal, 0, 40, "UTF-8");
                            $glosa_detalle = mb_substr($glosa_detalle, 0, 30, "UTF-8");
                            $data_direc_tv[] =
                                [
                                    "campo" => "",
                                    "sub_diario" => "3120",
                                    "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                                    "fecha_comprobante" => $frmt_fecha_comprobante,
                                    "codigo_moneda" => "MN",
                                    "glosa_principal" => $glosa_principal,
                                    "tipo_cambio" => $tipo_cambio,
                                    "tipo_conversion" => "V",
                                    "flag_conversion_moneda" => "S",
                                    "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                                    "cuenta_contable" => "636701",
                                    "codigo_anexo" => $ruc_proveedor,
                                    "codigo_centro_costo" => $row["ceco"],
                                    "debe_haber" => "D",
                                    "importe_original" => number_format($importe_original, 2, '.', ''),
                                    "importe_dolares" => number_format($importe_dolares, 2, '.', ''),
                                    "importe_soles" => number_format($importe_original, 2, '.', ''),
                                    "tipo_documento" => "RC",
                                    "numero_documento" => $numero_documento,
                                    "fecha_documento" => $frmt_fecha_emision,
                                    "fecha_vencimiento" => $frmt_fecha_vencimiento,
                                    "codigo_area" => "",
                                    "glosa_detalle" => $glosa_detalle,
                                    "codigo_anexo_auxiliar" => "01",
                                    "medio_pago" => "",
                                    "tipo_documento_referencia" => "",
                                    "numero_documento_referencia" => "",
                                    "fecha_documento_referencia" => "",
                                    "nro_maq_registradora_tipo_doc_ref." => "",
                                    "base_imponible_documento_referencia" => "",
                                    "igv_documento_provision" => "",
                                    "tipo_referencia_estado_mq" => "",
                                    "numero_serie_caja_registradora" => "",
                                    "fecha_operacion" => "",
                                    "tipo_tasa" => "",
                                    "tasa_detraccion_percepcion" => "",
                                    "importe_base_detraccion_percepcion_dolares" => "",
                                    "importe_base_detraccion_percepcion_soles" => "",
                                    "tipo_cambio_para_f" => "",
                                    "importe_igv_sin_derecho_credito_fiscal" => ""
                                ];
                        }

                        if (count($data_direc_tv) > 0) {

                            $summary_data_direc_tv = end($data_direc_tv);
                            $summary_data_direc_tv["glosa_principal"] = "";
                            $summary_data_direc_tv["cuenta_contable"] = "421101";
                            $summary_data_direc_tv["debe_haber"] = "H";
                            $summary_data_direc_tv["importe_original"] = round($suma_importe_original, 2);
                            $summary_data_direc_tv["importe_dolares"] = round($suma_importe_dolares, 2);
                            $summary_data_direc_tv["importe_soles"] = round($suma_importe_original, 2);
                            $summary_data_direc_tv["glosa_detalle"] = "";
                            $data_direc_tv[] = $summary_data_direc_tv;
                            $data["direc_tv"]["PLANTILLA_CONCAR_direc_tv"] = $data_direc_tv;
                        }

                        break;
                    case "movistar":
                        $sql_query = "SELECT " .
                            "xtl.desc_facturacion, " .
                            "xtl.telf_afiliado, " .
                            "xtl.nombre_proveedor, " .
                            "xtl.ruc, " .
                            "xtl.telf_agrupador, " .
                            "xtl.factura, " .
                            "xtl.cod_pago, " .
                            "xtl.ceco, " .
                            "xtl.local, " .
                            "xtl.cta_contable, " .
                            "IF(xtl.monto_inc_igv >= 0, 'D', 'H') debe_haber, " .
                            "ROUND(xtl.monto_inc_igv / xtl.contador, 2) monto_inc_igv " .
                            "FROM (" .
                            "SELECT prm.desc_facturacion, " .
                            "prm.telf_afiliado, " .
                            "prm.nombre_proveedor, " .
                            "prm.ruc, " .
                            "prm.telf_agrupador, " .
                            "prm.factura, " .
                            "prm.cod_pago, " .
                            "prm.ceco, " .
                            "prm.local, " .
                            "prm.cta_contable, " .
                            "(prm.monto_inc_igv / count(*)) AS monto_inc_igv, " .
                            "(" .
                            "SELECT count(apd.telf_afiliado) " .
                            "FROM tbl_concar_archivo_proveedor_maestro apm " .
                            "INNER JOIN tbl_concar_proveedores p " .
                            "ON apm.proveedor_id = p.id " .
                            "INNER JOIN tbl_concar_archivo_proveedor_detalle apd " .
                            "ON apm.id = apd.archivo_proveedor_id " .
                            "LEFT JOIN tbl_concar_codigos_pago cp " .
                            "ON cp.cod_pago = apd.telf_afiliado " .
                            "LEFT JOIN tbl_concar_centros_costo cc " .
                            "ON cp.ceco = cc.ceco " .
                            "LEFT JOIN tbl_concar_conceptos_facturables cf " .
                            "ON UPPER(TRIM(apd.concepto_facturable)) = UPPER(TRIM(cf.concepto)) " .
                            "WHERE apm.estado = 0 " .
                            "AND apm.id IN ({$archivo_proveedor_ids}) " .
                            "AND apd.desc_facturacion = prm.desc_facturacion " .
                            "AND apd.telf_afiliado = prm.telf_afiliado " .
                            ") AS contador " .
                            "FROM (" .
                            "SELECT p.nombre AS nombre_proveedor, " .
                            "p.ruc, " .
                            "apd.telf_agrupador, " .
                            "apd.telf_afiliado, " .
                            "apd.factura, " .
                            "cp.cod_pago, " .
                            "cc.ceco, " .
                            "cc.local, " .
                            "apd.desc_facturacion, " .
                            "apd.concepto_facturable, " .
                            "apd.monto_inc_igv, " .
                            "cf.cta_contable, " .
                            "cf.concepto " .
                            "FROM tbl_concar_archivo_proveedor_maestro apm " .
                            "INNER JOIN tbl_concar_proveedores p " .
                            "ON apm.proveedor_id = p.id " .
                            "INNER JOIN tbl_concar_archivo_proveedor_detalle apd " .
                            "ON apm.id = apd.archivo_proveedor_id " .
                            "LEFT JOIN tbl_concar_codigos_pago cp " .
                            "ON cp.cod_pago = apd.telf_afiliado " .
                            "LEFT JOIN tbl_concar_centros_costo cc " .
                            "ON cp.ceco = cc.ceco " .
                            "LEFT JOIN tbl_concar_conceptos_facturables cf " .
                            "ON UPPER(TRIM(apd.concepto_facturable)) = UPPER(TRIM(cf.concepto)) " .
                            "WHERE apm.estado = 0 " .
                            "AND apm.id IN ({$archivo_proveedor_ids}) " .
                            "ORDER BY " .
                            "apd.desc_facturacion, " .
                            "apd.telf_afiliado " .
                            ")AS prm " .
                            "GROUP BY " .
                            "prm.desc_facturacion, " .
                            "prm.telf_afiliado, " .
                            "prm.monto_inc_igv, " .
                            "prm.cta_contable" .
                            ") xtl;";

                        $stmt = $mysqli->prepare($sql_query);

                        $result = execute_sql_statement($stmt);

                        $data_movistar = [];

                        $suma_importe_original = 0;
                        $suma_importe_dolares = 0;
                        $telf_agrupador = "";
                        $glosa = "";
                        while ($row = $result->fetch_assoc()) {
                            $telf_agrupador = $row["telf_agrupador"];
                            $importe_original = floatval($row["monto_inc_igv"]);
                            $suma_importe_original += $importe_original;
                            $importe_dolares = $importe_original / $tipo_cambio;
                            $suma_importe_dolares += $importe_dolares;
                            $glosa = "TRIO-DUO";
                            $glosa .= (!is_null_or_empty_string($row["cod_pago"])) ? " " . $row["cod_pago"] : " " . "(SIN CÖDIGO DE PAGO)";
                            $glosa .= (!is_null_or_empty_string($row["local"])) ? " " . $row["local"] : " " . "(SIN LOCAL)";
                            $glosa_princpal = mb_substr($glosa, 0, 40, "UTF-8");
                            $glosa_detalle = mb_substr($glosa, 0, 30, "UTF-8");
                            $data_movistar[] =
                                [
                                    "campo" => "",
                                    "sub_diario" => "3120",
                                    "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                                    "fecha_comprobante" => $frmt_fecha_comprobante,
                                    "codigo_moneda" => "MN",
                                    "glosa_principal" => $glosa_princpal,
                                    "tipo_cambio" => $tipo_cambio,
                                    "tipo_conversion" => "V",
                                    "flag_conversion_moneda" => "S",
                                    "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                                    "cuenta_contable" => $row["cta_contable"],
                                    "codigo_anexo" => $ruc_proveedor,
                                    "codigo_centro_costo" => $row["ceco"],
                                    "debe_haber" => $row["debe_haber"],
                                    "importe_original" => abs($importe_original),
                                    "importe_dolares" => abs($importe_dolares),
                                    "importe_soles" => abs($importe_original),
                                    "tipo_documento" => "RC",
                                    "numero_documento" => $row["factura"],
                                    "fecha_documento" => $frmt_fecha_emision,
                                    "fecha_vencimiento" => $frmt_fecha_vencimiento,
                                    "codigo_area" => "",
                                    "glosa_detalle" => $glosa_detalle,
                                    "codigo_anexo_auxiliar" => "01",
                                    "medio_pago" => "",
                                    "tipo_documento_referencia" => "",
                                    "numero_documento_referencia" => "",
                                    "fecha_documento_referencia" => "",
                                    "nro_maq_registradora_tipo_doc_ref." => "",
                                    "base_imponible_documento_referencia" => "",
                                    "igv_documento_provision" => "",
                                    "tipo_referencia_estado_mq" => "",
                                    "numero_serie_caja_registradora" => "",
                                    "fecha_operacion" => "",
                                    "tipo_tasa" => "",
                                    "tasa_detraccion_percepcion" => "",
                                    "importe_base_detraccion_percepcion_dolares" => "",
                                    "importe_base_detraccion_percepcion_soles" => "",
                                    "tipo_cambio_para_f" => "",
                                    "importe_igv_sin_derecho_credito_fiscal" => ""
                                ];
                        }

                        if (count($data_movistar) > 0) {

                            $glosa = "AGRUP" . ((!is_null_or_empty_string($telf_agrupador)) ? " " . $telf_agrupador : " " . "(SIN TELEFONO AGRUPADOR)") . " " . substr($nombre_mes_fecha_comprobante, 0, 3);
                            $summary_data_movistar = end($data_movistar);
                            $summary_data_movistar["glosa_principal"] = $glosa;
                            $summary_data_movistar["cuenta_contable"] = "421101";
                            $summary_data_movistar["debe_haber"] = "H";
                            $summary_data_movistar["importe_original"] = $suma_importe_original;
                            $summary_data_movistar["importe_dolares"] = $suma_importe_dolares;
                            $summary_data_movistar["importe_soles"] = $suma_importe_original;
                            $summary_data_movistar["glosa_detalle"] = $glosa;
                            $data_movistar[] = $summary_data_movistar;
                            $data["movistar"]["PLANTILLA_CONCAR_MOVISTAR"] = $data_movistar;
                        }

                        break;
                    case "niubiz":

                        $sql_query = "(" .
                            "SELECT " .
                            "cc.ceco, " .
                            "p.ruc, " .
                            "'639301' as cta_contable, " .
                            "SUM(apd.comision_emisores) as monto_inc_igv, " .
                            "'D' as debe_haber " .
                            "FROM " .
                            "tbl_concar_archivo_proveedor_maestro as apm " .
                            "INNER JOIN tbl_concar_proveedores p ON apm.proveedor_id = p.id " .
                            "INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON apd.archivo_proveedor_id = apm.id " .
                            "LEFT JOIN tbl_concar_codigos_comercio cc ON  apd.codigo_comercio = cc.cod_comercio " .
                            "WHERE apm.id IN ({$archivo_proveedor_ids}) " .
                            "AND apm.estado = 0 " .
                            "GROUP BY " .
                            "apd.codigo_comercio " .
                            "ORDER BY cc.ceco " .
                            ")" .
                            " UNION ALL " .
                            "(" .
                            "SELECT " .
                            "'' as ceco, " .
                            "b.ruc, " .
                            "'421201' as cta_contable, " .
                            "SUM(db.importe) as monto_inc_igv, " .
                            "'H' as debe_haber " .
                            "FROM " .
                            "tbl_concar_archivo_proveedor_maestro as apm " .
                            "INNER JOIN tbl_concar_proveedores p ON apm.proveedor_id = p.id " .
                            "INNER JOIN tbl_concar_detalle_bancos db ON db.archivo_proveedor_id = apm.id " .
                            "INNER JOIN tbl_concar_bancos b ON db.banco_id = b.id " .
                            "WHERE apm.id IN ({$archivo_proveedor_ids}) " .
                            "AND apm.estado = 0 " .
                            "GROUP BY " .
                            "db.banco_id " .
                            ");";

                        $stmt = $mysqli->prepare($sql_query);
                        $result = execute_sql_statement($stmt);
                        $data_niubiz = [];
                        while ($row = $result->fetch_assoc()) {
                            $importe_original = floatval($row["monto_inc_igv"]);
                            $mes_abbrv = substr($nombre_mes_fecha_comprobante, 0, 3);
                            $glosa = "COMISION EMISORES " . " " . $mes_abbrv;
                            $glosa_princpal = mb_substr($glosa, 0, 40, "UTF-8");
                            $glosa_detalle = mb_substr($glosa, 0, 30, "UTF-8");
                            $data_niubiz[] =
                                [
                                    "campo" => "",
                                    "sub_diario" => "3120",
                                    "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                                    "fecha_comprobante" => $frmt_fecha_comprobante,
                                    "codigo_moneda" => "MN",
                                    "glosa_principal" => $glosa_princpal,
                                    "tipo_cambio" => $tipo_cambio,
                                    "tipo_conversion" => "V",
                                    "flag_conversion_moneda" => "S",
                                    "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                                    "cuenta_contable" => $row["cta_contable"],
                                    "codigo_anexo" => $row["ruc"],
                                    "codigo_centro_costo" => $row["ceco"],
                                    "debe_haber" => $row["debe_haber"],
                                    "importe_original" => number_format($importe_original, 2, '.', ''),
                                    "importe_dolares" => "",
                                    "importe_soles" => "",
                                    "tipo_documento" => "DA",
                                    "numero_documento" => $numero_documento,
                                    "fecha_documento" => $frmt_fecha_emision,
                                    "fecha_vencimiento" => $frmt_fecha_vencimiento,
                                    "codigo_area" => "",
                                    "glosa_detalle" => $glosa_detalle,
                                    "codigo_anexo_auxiliar" => "01",
                                    "medio_pago" => "",
                                    "tipo_documento_referencia" => "",
                                    "numero_documento_referencia" => "",
                                    "fecha_documento_referencia" => "",
                                    "nro_maq_registradora_tipo_doc_ref." => "",
                                    "base_imponible_documento_referencia" => "",
                                    "igv_documento_provision" => "",
                                    "tipo_referencia_estado_mq" => "",
                                    "numero_serie_caja_registradora" => "",
                                    "fecha_operacion" => "",
                                    "tipo_tasa" => "",
                                    "tasa_detraccion_percepcion" => "",
                                    "importe_base_detraccion_percepcion_dolares" => "",
                                    "importe_base_detraccion_percepcion_soles" => "",
                                    "tipo_cambio_para_f" => "",
                                    "importe_igv_sin_derecho_credito_fiscal" => ""
                                ];
                        }

                        if (count($data_niubiz) > 0) {
                            $data["niubiz"]["PLANTILLA_OPE_AT_COMISION_EMISORES_" . $mes_abbrv] = $data_niubiz;
                        }

                        $sql_query = "SELECT " .
                            "cc.ceco, " .
                            "p.ruc, " .
                            "'639301' as cta_contable, " .
                            "SUM(apd.comision_visa) as monto_inc_igv, " .
                            "'D' as debe_haber " .
                            "FROM " .
                            "tbl_concar_archivo_proveedor_maestro as apm " .
                            "INNER JOIN tbl_concar_proveedores p ON apm.proveedor_id = p.id " .
                            "INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON apd.archivo_proveedor_id = apm.id " .
                            "LEFT JOIN tbl_concar_codigos_comercio cc ON  apd.codigo_comercio = cc.cod_comercio " .
                            "WHERE apm.id IN ({$archivo_proveedor_ids}) AND apm.estado = 0 " .
                            "GROUP BY " .
                            "apd.codigo_comercio " .
                            "ORDER BY cc.ceco;";

                        $stmt = $mysqli->prepare($sql_query);
                        $result = execute_sql_statement($stmt);
                        $data_niubiz = [];
                        $suma_importe_original = 0;
                        while ($row = $result->fetch_assoc()) {
                            $importe_original = floatval($row["monto_inc_igv"]);
                            $suma_importe_original += $importe_original;
                            $mes_abbrv = substr($nombre_mes_fecha_comprobante, 0, 3);
                            $glosa = "COMISION VISANET " . " " . $mes_abbrv;
                            $glosa_princpal = mb_substr($glosa, 0, 40, "UTF-8");
                            $glosa_detalle = mb_substr($glosa, 0, 30, "UTF-8");
                            $data_niubiz[] =
                                [
                                    "campo" => "",
                                    "sub_diario" => "3120",
                                    "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                                    "fecha_comprobante" => $frmt_fecha_comprobante,
                                    "codigo_moneda" => "MN",
                                    "glosa_principal" => $glosa_princpal,
                                    "tipo_cambio" => $tipo_cambio,
                                    "tipo_conversion" => "V",
                                    "flag_conversion_moneda" => "S",
                                    "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                                    "cuenta_contable" => $row["cta_contable"],
                                    "codigo_anexo" => $row["ruc"],
                                    "codigo_centro_costo" => $row["ceco"],
                                    "debe_haber" => $row["debe_haber"],
                                    "importe_original" => number_format($importe_original, 2, '.', ''),
                                    "importe_dolares" => "",
                                    "importe_soles" => "",
                                    "tipo_documento" => "DA",
                                    "numero_documento" => $numero_documento,
                                    "fecha_documento" => $frmt_fecha_emision,
                                    "fecha_vencimiento" => $frmt_fecha_vencimiento,
                                    "codigo_area" => "",
                                    "glosa_detalle" => $glosa_detalle,
                                    "codigo_anexo_auxiliar" => "01",
                                    "medio_pago" => "",
                                    "tipo_documento_referencia" => "",
                                    "numero_documento_referencia" => "",
                                    "fecha_documento_referencia" => "",
                                    "nro_maq_registradora_tipo_doc_ref." => "",
                                    "base_imponible_documento_referencia" => "",
                                    "igv_documento_provision" => "",
                                    "tipo_referencia_estado_mq" => "",
                                    "numero_serie_caja_registradora" => "",
                                    "fecha_operacion" => "",
                                    "tipo_tasa" => "",
                                    "tasa_detraccion_percepcion" => "",
                                    "importe_base_detraccion_percepcion_dolares" => "",
                                    "importe_base_detraccion_percepcion_soles" => "",
                                    "tipo_cambio_para_f" => "",
                                    "importe_igv_sin_derecho_credito_fiscal" => ""
                                ];
                        }

                        if (count($data_niubiz) > 0) {
                            $summary_data_niubiz = end($data_niubiz);
                            $summary_data_niubiz["cuenta_contable"] = "421101";
                            $summary_data_niubiz["debe_haber"] = "H";
                            $summary_data_niubiz["importe_original"] = $suma_importe_original;
                            $data_niubiz[] = $summary_data_niubiz;
                            $data["niubiz"]["PLANTILLA_OPE_AT_COMISION_VISANET_" . $mes_abbrv] = $data_niubiz;
                        }

                        break;
                    case "prosegur":
                        $sql_query = "SELECT " .
                            "'' ceco, " .
                            "apd.tarifa_os_igv monto_inc_igv " .
                            "FROM " .
                            "tbl_concar_archivo_proveedor_maestro apm " .
                            "INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON apm.id = apd.archivo_proveedor_id " .
                            "WHERE apm.id IN ({$archivo_proveedor_ids}) " .
                            "AND apm.estado = 0 " .
                            "ORDER BY ceco;";
                        $stmt = $mysqli->prepare($sql_query);

                        $result = execute_sql_statement($stmt);

                        $data_prosegur = [];

                        $suma_importe_original = 0;
                        $glosa = "";
                        while ($row = $result->fetch_assoc()) {
                            $importe_original = floatval($row["monto_inc_igv"]);
                            $suma_importe_original += $importe_original;
                            $glosa = "PROV PROSEGUR" . " " . $nombre_mes_fecha_comprobante;
                            $glosa_princpal = mb_substr($glosa, 0, 40, "UTF-8");
                            $glosa_detalle = mb_substr($glosa, 0, 30, "UTF-8");
                            $data_prosegur[] =
                                [
                                    "campo" => "",
                                    "sub_diario" => "3120",
                                    "numero_comprobante" => $frmt_numero_mes_fecha_comprobante . $numero_comprobante,
                                    "fecha_comprobante" => $frmt_fecha_comprobante,
                                    "codigo_moneda" => "MN",
                                    "glosa_principal" => $glosa_princpal,
                                    "tipo_cambio" => $tipo_cambio,
                                    "tipo_conversion" => "V",
                                    "flag_conversion_moneda" => "S",
                                    "fecha_tipo_cambio" => $frmt_fecha_comprobante,
                                    "cuenta_contable" => "639501",
                                    "codigo_anexo" => $ruc_proveedor,
                                    "codigo_centro_costo" => $row["ceco"],
                                    "debe_haber" => "D",
                                    "importe_original" => number_format($importe_original, 2, '.', ''),
                                    "importe_dolares" => "",
                                    "importe_soles" => "",
                                    "tipo_documento" => "DA",
                                    "numero_documento" => $numero_documento,
                                    "fecha_documento" => $frmt_fecha_emision,
                                    "fecha_vencimiento" => $frmt_fecha_vencimiento,
                                    "codigo_area" => "",
                                    "glosa_detalle" => $glosa_detalle,
                                    "codigo_anexo_auxiliar" => "01",
                                    "medio_pago" => "",
                                    "tipo_documento_referencia" => "",
                                    "numero_documento_referencia" => "",
                                    "fecha_documento_referencia" => "",
                                    "nro_maq_registradora_tipo_doc_ref." => "",
                                    "base_imponible_documento_referencia" => "",
                                    "igv_documento_provision" => "",
                                    "tipo_referencia_estado_mq" => "",
                                    "numero_serie_caja_registradora" => "",
                                    "fecha_operacion" => "",
                                    "tipo_tasa" => "",
                                    "tasa_detraccion_percepcion" => "",
                                    "importe_base_detraccion_percepcion_dolares" => "",
                                    "importe_base_detraccion_percepcion_soles" => "",
                                    "tipo_cambio_para_f" => "",
                                    "importe_igv_sin_derecho_credito_fiscal" => ""
                                ];
                        }

                        if (count($data_prosegur) > 0) {
                            $summary_data_prosegur = end($data_prosegur);
                            $summary_data_prosegur["cuenta_contable"] = "421101";
                            $summary_data_prosegur["debe_haber"] = "H";
                            $summary_data_prosegur["importe_original"] = round($suma_importe_original, 2);
                            $data_prosegur[] = $summary_data_prosegur;
                            $data["prosegur"]["PLANTILLA_CONCAR_PROSEGUR"] = $data_prosegur;
                        }

                        break;
                }
            }
        }

        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function exportar_archivo_concar($file_columns, $data, $file_name)
{
    try {
        date_default_timezone_set("America/Lima");
        setlocale(LC_ALL, "es_ES");
        require_once '../phpexcel/classes/PHPExcel.php';
        $objPHPExcel = new PHPExcel();

        $header_columns_group = array(
            [
                'Campo',
                'Sub Diario',
                'Número de Comprobante',
                'Fecha de Comprobante',
                'Código de Moneda',
                'Glosa Principal',
                'Tipo de Cambio',
                'Tipo de Conversión',
                'Flag de Conversión de Moneda',
                'Fecha Tipo de Cambio',
                'Cuenta Contable',
                'Código de Anexo',
                'Código de Centro de Costo',
                'Debe / Haber',
                'Importe Original',
                'Importe en Dólares',
                'Importe en Soles',
                'Tipo de Documento',
                'Número de Documento',
                'Fecha de Documento',
                'Fecha de Vencimiento',
                'Código de Area',
                'Glosa Detalle',
                'Código de Anexo Auxiliar',
                'Medio de Pago',
                'Tipo de Documento de Referencia',
                'Número de Documento Referencia',
                'Fecha Documento Referencia',
                'Nro Máq. Registradora Tipo Doc. Ref.',
                'Base Imponible Documento Referencia',
                'IGV Documento Provisión',
                'Tipo Referencia en estado MQ',
                'Número Serie Caja Registradora',
                'Fecha de Operación',
                'Tipo de Tasa',
                'Tasa Detracción/Percepción',
                'Importe Base Detracción/Percepción Dólares',
                'Importe Base Detracción/Percepción Soles',
                'Tipo Cambio para \'F\'',
                'Importe de IGV sin derecho credito fiscal',
                'Tasa IGV'
            ],
            [
                'Restricciones',
                'Ver T.G. 02',
                '',
                '',
                'Ver T.G. 03',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            [
                'Tamaño/Formato',
                '4 Caracteres',
                '6 Caracteres',
                'dd/mm/aaaa',
                '2 Caracteres',
                '40 Caracteres',
                'Numérico 11,6',
                '1 Caracteres',
                '1 Caracteres',
                'dd/mm/aaaa',
                '12 Caracteres',
                '18 Caracteres',
                '6 Caracteres',
                '1 Carácter',
                'Numérico 14,2',
                'Numérico 14,2',
                'Numérico 14,2',
                '2 Caracteres',
                '20 Caracteres',
                'dd/mm/aaaa',
                'dd/mm/aaaa',
                '3 Caracteres',
                '30 Caracteres',
                '18 Caracteres',
                '8 Caracteres',
                '2 Caracteres',
                '20 Caracteres',
                'dd/mm/aaaa',
                '20 Caracteres',
                'Numérico 14,2',
                'Numérico 14,2',
                'MQ',
                '15 caracteres',
                'dd/mm/aaaa',
                '5 Caracteres',
                'Numérico 14,2',
                'Numérico 14,2',
                'Numérico 14,2',
                '1 Caracter',
                'Numérico 14,2',
                'Numérico 14,2'
            ]
        );

        $active_sheet = $objPHPExcel->setActiveSheetIndex(0);

        set_title_columns_in_active_sheet($active_sheet, $header_columns_group, $file_columns);

        $index_columns = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO"];

        $index_row = 4;
        foreach ($data as $row) {
            $index_column = 0;
            foreach ($row as $value) {
                $cell = $index_columns[$index_column] . $index_row;
                $active_sheet->setCellValue($cell, $value);
                $index_column++;
            }
            $index_row++;
        }

        $estiloColorFondoAmarillo = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'ffff00'
                )
            )
        );

        $estiloColorFondoAmarilloOscuro = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'ffc000'
                )
            )
        );

        $estiloTituloColumnas = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => false
            )
        );

        $estiloBordeCeldas = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $estiloResumenColumnas = array(
            'font' => array(
                'bold' => true,
                'color' => array(
                    'rgb' => 'ff0000'
                )
            )
        );

        $last_index_row = count($data) + 3;
        $first_column = 'A';
        $last_column = 'AO';

        $active_sheet->getStyle($first_column . '1:' . $last_column . '1')->applyFromArray($estiloColorFondoAmarilloOscuro);
        $active_sheet->getStyle($first_column . '1:' . $first_column . '3')->applyFromArray($estiloColorFondoAmarillo);
        $active_sheet->getStyle($first_column . '3:' . $last_column . '3')->applyFromArray($estiloColorFondoAmarillo);
        $active_sheet->getStyle($first_column . '1:' . $last_column . '3')->applyFromArray($estiloTituloColumnas);
        $active_sheet->getStyle($first_column . '1:' . $last_column . $last_index_row)->applyFromArray($estiloBordeCeldas);

        $active_sheet->getStyle($first_column . $last_index_row . ':' . $last_column . $last_index_row)->applyFromArray($estiloResumenColumnas);

        foreach ($active_sheet->getColumnIterator() as $column) {
            $active_sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $active_sheet->getStyle($first_column . '1:' . $last_column . '3')->getAlignment()->setWrapText(true);

        $extension = ".xls";
        $file_name = is_null_or_empty_string($file_name) ? "Reporte-CONCAR-" . time() . $extension : $file_name;
        //header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=$file_name");
        header('Cache-Control: max-age=0');

        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        ob_start();
        //$temp_file = tempnam(sys_get_temp_dir(), $file_name);
        $objWriter->save("php://output");
        $xlsData = ob_get_contents();
        ob_end_clean();

        $response = array(
            "success" => true,
            "file_name" => $file_name,
            "extension" => $extension,
            "file" => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );

        $objPHPExcel->disconnectWorksheets();
        unset($objWriter, $objPHPExcel);

        return $response;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_tipo_cambio($fecha)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT monto_venta FROM tbl_tipo_cambio WHERE moneda_id = 2 AND fecha = ?";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "s", [$fecha]);
        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        if ($object) {
            return floatval($object->monto_venta);
        }
        return null;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function actualizar_estado_archivo_proveedor_por_rango_fechas($proveedor_id, $fecha_creacion_desde, $fecha_creacion_hasta, $estado = 1)
{
    try {


        $fechas_por_validar = [
            "fecha_creacion_desde" => $fecha_creacion_desde,
            "fecha_creacion_hasta" => $fecha_creacion_hasta
        ];

        $fechas_validas = validar_formato_fechas($fechas_por_validar);

        $fecha_creacion_desde = $fechas_validas["fecha_creacion_desde"];
        $fecha_creacion_hasta = $fechas_validas["fecha_creacion_hasta"];
        $fecha_creacion_desde->setTime(0, 0, 0);
        $fecha_creacion_hasta->setTime(23, 59, 59);
        $dbfrmt_fecha_creacion_desde = $fecha_creacion_desde->format('Y-m-d H:i:s');
        $dbfrmt_fecha_creacion_hasta = $fecha_creacion_hasta->format('Y-m-d H:i:s');

        include("db_connect.php");
        $sql_query = "UPDATE tbl_concar_archivo_proveedor_maestro SET estado = ? WHERE proveedor_id = ? AND created_at BETWEEN ? AND ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "iiss", [$estado, $proveedor_id, $dbfrmt_fecha_creacion_desde, $dbfrmt_fecha_creacion_hasta]);
        $query_execute = $stmt->execute();
        return $query_execute;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function actualizar_estado_archivo_proveedor_por_archivo_proveedor_ids($archivo_proveedor_ids, $estado = 1)
{
    try {
        include("db_connect.php");
        $archivo_proveedor_ids = implode(", ", $archivo_proveedor_ids);
        $sql_query = "UPDATE tbl_concar_archivo_proveedor_maestro SET estado = {$estado} WHERE id IN ({$archivo_proveedor_ids});";
        $stmt = $mysqli->prepare($sql_query);
        $stmt->execute();
        return true;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function actualizar_numero_documento_archivo_proveedor_maestro_por_rango_fechas($proveedor_id, $fecha_creacion_desde, $fecha_creacion_hasta, $numero_documento)
{
    try {
        $fechas_por_validar = [
            "fecha_creacion_desde" => $fecha_creacion_desde,
            "fecha_creacion_hasta" => $fecha_creacion_hasta
        ];

        $fechas_validas = validar_formato_fechas($fechas_por_validar);

        $fecha_creacion_desde = $fechas_validas["fecha_creacion_desde"];
        $fecha_creacion_hasta = $fechas_validas["fecha_creacion_hasta"];
        $fecha_creacion_desde->setTime(0, 0, 0);
        $fecha_creacion_hasta->setTime(23, 59, 59);
        $dbfrmt_fecha_creacion_desde = $fecha_creacion_desde->format('Y-m-d H:i:s');
        $dbfrmt_fecha_creacion_hasta = $fecha_creacion_hasta->format('Y-m-d H:i:s');
        include("db_connect.php");
        $sql_query = "";
        if (!$numero_documento) {
            $sql_query = "SELECT apd.factura AS nro_documento FROM tbl_concar_archivo_proveedor_maestro apm INNER JOIN tbl_concar_archivo_proveedor_detalle apd ON apm.id = apd.archivo_proveedor_id WHERE apm.created_at BETWEEN ? AND ? AND apm.proveedor_id = ? AND apm.estado = 0 AND apd.factura IS NOT NULL GROUP BY apd.factura LIMIT 1";
            $stmt = $mysqli->prepare($sql_query);
            bind_param_statement($stmt, "ssi", [$dbfrmt_fecha_creacion_desde, $dbfrmt_fecha_creacion_hasta, $proveedor_id]);
            $stmt_result = execute_sql_statement($stmt);
            $obj = $stmt_result->fetch_object();
            if ($obj != null) {
                $numero_document = $obj->nro_documento;
            } else {
                set_status_code_response(400, "No se puede obtener el número de documento.", null);
            }
        }

        $sql_query = "UPDATE tbl_concar_archivo_proveedor_maestro SET numero_documento = ? WHERE created_at BETWEEN ? AND ? AND proveedor_id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "sssi", [$numero_document, $dbfrmt_fecha_creacion_desde, $dbfrmt_fecha_creacion_hasta, $proveedor_id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function actualizar_numero_documento_archivo_proveedor_maestro_por_id($id, $numero_documento)
{
    try {
        include("db_connect.php");
        $sql_query = "UPDATE tbl_concar_archivo_proveedor_maestro SET numero_documento = ? WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "si", [$numero_documento, $id]);
        $stmt->execute();
        return true;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function completar_numero_comprobante($value)
{
    $count_zeros = 4;
    $length = strlen($value);

    if ($length > $count_zeros) {
        $count_zeros = $length;
    }

    return str_pad($value, $count_zeros, "0", STR_PAD_LEFT);
}

function set_title_columns_in_active_sheet($active_sheet, $header_columns_group, $file_columns)
{
    $index_row = 1;
    $index_column = 0;
    $letter_column = "";
    $letter_column_group = "";
    $index_column_group = 0;

    foreach ($header_columns_group as $header_columns) {
        foreach ($header_columns as $header_column) {
            $letter_column = $file_columns[$index_column];
            $column = $letter_column_group . $letter_column . $index_row;
            $active_sheet->setCellValue($column, $header_column);
            $index_column++;
            if ($index_column >= count($file_columns)) {
                $letter_column_group = $file_columns[$index_column_group];
                $index_column_group++;
                $index_column = 0;
            }
        }
        $index_row++;
        $letter_column_group = "";
        $index_column_group = 0;
        $index_column = 0;
    }
}

function validar_formato_fechas($data)
{
    $valid_dates = [];
    $invalid_dates = [];
    $check_date_formats = ["d/m/Y", "d/m/Y H:i:s", "d-m-Y", "d-m-Y H:i:s", "Y/m/d", "Y/m/d H:i:s", "Y-m-d", "Y-m-d H:i:s"];
    foreach ($data as $key => $value) {
        $is_valid = false;
        foreach ($check_date_formats as $check_date_format) {
            $date = DateTime::createFromFormat($check_date_format, $value);
            if ($date) {
                $valid_dates[$key] = $date;
                $is_valid = true;
                break;
            }
        }
        if (!$is_valid) {
            $invalid_dates[] = $key;
        }
    }

    foreach ($invalid_dates as $value) {
        set_status_code_response(400, "El campo " . $value . " no es una fecha valida.", null);
    }
    return $valid_dates;
}

function validar_formato_fecha($value)
{
    $check_date_formats = ["d/m/Y", "d/m/Y H:i:s", "d-m-Y", "d-m-Y H:i:s", "Y/m/d", "Y/m/d H:i:s", "Y-m-d", "Y-m-d H:i:s"];
    $is_valid = false;
    foreach ($check_date_formats as $check_date_format) {
        $date = DateTime::createFromFormat($check_date_format, $value);
        if ($date) {
            $is_valid = true;
            break;
        }
    }

    if ($is_valid) {
        return $date;
    }

    return false;
}

function set_status_code_response($code, $message, $data)
{
    if (is_null_or_empty_string($code)) {
        $code = 200;
    }

    $http = array(
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Large',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Time-out',
        505 => 'HTTP/1.1 505 HTTP Version Not Supported',
    );

    header($http[$code]);

    if (is_array($data)) {
        header('Content-type: application/json');
        echo json_encode($data);
    } else {
        header('Content-type: text/plain');
    }

    if (!is_null_or_empty_string($message)) {
        exit($message);
    }
    exit();
}

function excel_date_to_php($value)
{
    try {
        if (is_null_or_empty_string($value) || intval($value) == 0) {
            return null;
        }

        $data_formats = [
            "d/m/Y", "d/m/Y H:i:s",
            "d-m-Y", "d-m-Y H:i:s",
            "Y/m/d", "Y/m/d H:i:s",
            "Y-m-d", "Y-m-d H:i:s",
            "m/d/Y", "m/d/Y H:i:s",
            "m-d-Y", "m-d-Y H:i:s",
        ];

        foreach ($data_formats as $format) {
            if (validateDate($value, $format)) {
                return $value;
            }
        }

        if (is_numeric($value)) {
            //$unixDate = ($excelDate - 25569) * 86400;
            $unixDate = PHPExcel_Shared_Date::ExcelToPHP($value);
            $format = "d/m/Y H:i:s";
            $date = gmdate($format, $unixDate);
            if (validateDate($date, $format)) {
                return $date;
            }
        }

        set_status_code_response(400, "La Fecha {$value} no es válida.", null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function guardar_archivo_proveedor_por_volcado_de_datos($data)
{
    try {
        $tmp_file = create_temp_file_name();
        $tmp_file_maestro = sys_get_temp_dir() . "/" . "tmp-maestro-" . $tmp_file . ".csv";
        $tmp_file_detalle = sys_get_temp_dir() . "/" . "tmp-detalle-" . $tmp_file . ".csv";
        array_to_csv($data["maestro"], $tmp_file_maestro, "w");
        array_to_csv($data["detalle"], $tmp_file_detalle, "w");

        include("db_connect.php");
        $mysqli->query("START TRANSACTION");

        $data_maestro = $data["maestro"][0];

        $proveedor_id = $data_maestro["proveedor_id"];

        $sql_query = "SELECT nombre as nombre_proveedor FROM tbl_concar_proveedores WHERE id = ?";

        $stmt = $mysqli->prepare($sql_query);

        bind_param_statement($stmt, "i", [$proveedor_id]);

        $stmt_result = execute_sql_statement($stmt);

        $nombre_proveedor = $stmt_result->fetch_object()->nombre_proveedor;

        $sql1 = "LOAD DATA LOCAL INFILE '{$tmp_file_maestro}' " .
            "INTO TABLE `tbl_concar_archivo_proveedor_maestro` " .
            "FIELDS TERMINATED BY ',' " .
            "ENCLOSED BY '\"' " .
            "LINES TERMINATED BY '\\r\\n' " .
            "IGNORE 1 LINES " .
            "(`nombre_archivo`, `proveedor_id`, @created_at, @updated_at) " .
            "SET date = STR_TO_DATE(@date, '%b-%d-%Y %h:%i:%s %p'),";

        $nombre_proveedor_clave = str_replace(" ", "_", strtolower($nombre_proveedor));

        $mysqli->query("COMMIT");
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function array_to_csv($data, $file = "php://memory", $mode = "r+", $delimiter = ",", $enclosure = '"', $escape_char = "\\")
{
    try {
        $csv = false;
        if (count($data)) {
            $buffer = fopen($file, $mode);
            $first_row = $data[0];
            fputcsv($buffer, array_keys($first_row), $delimiter, $enclosure, $escape_char);
            foreach ($data as $field) {
                fputcsv($buffer, $field, $delimiter, $enclosure, $escape_char);
            }

            if ($file == "php://memory" || $file == "php://output") {
                rewind($buffer);
                $csv = stream_get_contents($buffer);
            } else {
                $csv = true;
            }
            fclose($buffer);
        }
        return $csv;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function create_temp_file_name($prefix = null, $ext = "")
{
    $path = false;
    $prefix = $prefix ?? rand();
    while (true) {
        $filename = uniqid($prefix, true) . $ext;
        $temp_dir = sys_get_temp_dir();
        $path = $temp_dir . "/" . $filename;
        if (!file_exists($path)) {
            break;
        }
    }
    return $filename;
}

function is_null_or_empty_string($str)
{
    return ($str === null || trim($str) === '');
}

function get_datetime_columns($fields_provider)
{
    $datetime_columns = array();
    foreach ($fields_provider as $field_provider) {
        foreach ($field_provider as $key => $value) {
            if ($value == "datetime") {
                $datetime_columns[] = $key;
            }
        }
    }
    echo json_encode($datetime_columns);
}

function obtener_nombre_del_mes($num_mes)
{
    $mes = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];
    return $mes[$num_mes - 1];
}

function get_unique_values_from_array($data, $field)
{
    $values = array();

    foreach ($data as $row) {
        $values[] = $row[$field];
    }

    $unique_values = array_unique($values);

    return $unique_values;
}

function get_unique_values_from_db($archivo_proveedor_id, $column, $alias)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT DISTINCT d.{$column} as {$alias} FROM tbl_concar_archivo_proveedor_detalle d WHERE d.archivo_proveedor_id = {$archivo_proveedor_id}";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = strval($row[$alias]);
        }
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_numeros_cuenta_pendientes($numeros_cuenta)
{
    try {
        $data = [];
        if (count($numeros_cuenta) > 0) {
            $array_template = [
                "id" => 0,
                "nro_cuenta" => null,
                "local" => null,
                "ceco" => null
            ];
            $sql_query = "select v.nro_cuenta from (";
            $sql_selects_numeros_cuenta = [];
            foreach ($numeros_cuenta as $numero_cuenta) {
                //TODO Check This! 
                /*if (!is_numeric($numero_cuenta)) {
                    set_status_code_response(500, "Al leer la información del archivo el número de cuenta '{$numero_cuenta}' no tiene el formato correcto.", null);
                }*/
                $sql_selects_numeros_cuenta[] = "select '{$numero_cuenta}' as nro_cuenta";
            }
            $sql_query .= implode(" union all ", $sql_selects_numeros_cuenta);
            $sql_query .= ") v where not exists (select 1 from tbl_concar_numeros_cuenta nc where nc.nro_cuenta = v.nro_cuenta);";

            include("db_connect.php");

            $stmt = $mysqli->prepare($sql_query);

            $result = execute_sql_statement($stmt);

            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["nro_cuenta"] = strval($row["nro_cuenta"]);
                $data[] = $data_item;
            }
            $str_numeros_cuenta = "'" . implode("', '", $numeros_cuenta) . "'";
            $sql_query = "SELECT nc.id, nc.nro_cuenta, cc.local, nc.ceco FROM tbl_concar_numeros_cuenta nc INNER JOIN tbl_concar_centros_costo cc ON cc.ceco = nc.ceco WHERE (cc.local = '' OR cc.local = NULL) and nc.nro_cuenta IN ({$str_numeros_cuenta});";
            $stmt = $mysqli->prepare($sql_query);
            $result = execute_sql_statement($stmt);
            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["id"] = $row["id"];
                $data_item["nro_cuenta"] = $row["nro_cuenta"];
                $data_item["local"] = $row["local"];
                $data_item["ceco"] = $row["ceco"];
                $data[] = $data_item;
            }
            $stmt->close();
        }
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_numeros_cuenta()
{
    try {
        $data = [];

        include("db_connect.php");
        $sql_query = "SELECT nc.id, nc.nro_cuenta, cc.local, nc.ceco FROM tbl_concar_numeros_cuenta nc INNER JOIN tbl_concar_centros_costo cc ON cc.ceco = nc.ceco;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "nro_cuenta" => $row["nro_cuenta"],
                "local" => $row["local"],
                "ceco" => $row["ceco"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_codigos_pago_pendientes($codigos_pago)
{
    try {
        $data = [];
        if (count($codigos_pago) > 0) {
            $array_template = [
                "id" => 0,
                "cod_pago" => null,
                "local" => null,
                "ceco" => null
            ];
            $sql_query = "select v.cod_pago from (";
            $sql_selects_codigos_pago = [];
            foreach ($codigos_pago as $codigo_pago) {
                $sql_selects_codigos_pago[] = "select '{$codigo_pago}' as cod_pago";
            }
            $sql_query .= implode(" union all ", $sql_selects_codigos_pago);
            $sql_query .= ") v where not exists (select 1 from tbl_concar_codigos_pago cp where cp.cod_pago = v.cod_pago);";

            include("db_connect.php");

            $stmt = $mysqli->prepare($sql_query);

            $result = execute_sql_statement($stmt);

            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["cod_pago"] = strval($row["cod_pago"]);
                $data[] = $data_item;
            }
            $str_codigos_pago = implode(", ", $codigos_pago);
            $sql_query = "SELECT cp.id, cp.cod_pago, cc.local, cp.ceco FROM tbl_concar_codigos_pago cp INNER JOIN tbl_concar_centros_costo cc ON cc.ceco = cp.ceco WHERE (cc.local = '' OR cc.local = NULL) and cp.cod_pago IN ({$str_codigos_pago});";
            $stmt = $mysqli->prepare($sql_query);
            $result = execute_sql_statement($stmt);
            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["id"] = $row["id"];
                $data_item["cod_pago"] = $row["cod_pago"];
                $data_item["local"] = $row["local"];
                $data_item["ceco"] = $row["ceco"];
                $data[] = $data_item;
            }
            $stmt->close();
        }
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_codigos_pago()
{
    try {
        $data = [];
        include("db_connect.php");
        $sql_query = "SELECT cp.id, cp.cod_pago, cc.ceco, cc.local FROM tbl_concar_codigos_pago cp INNER JOIN tbl_concar_centros_costo cc ON cp.ceco = cc.ceco;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "cod_pago" => $row["cod_pago"],
                "local" => $row["local"],
                "ceco" => $row["ceco"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_centros_costo()
{
    try {
        $data = [];
        include("db_connect.php");
        $sql_query = "SELECT id, local, ceco, fecha_baja, estado FROM tbl_concar_centros_costo ORDER BY estado DESC, ceco ASC, local ASC;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "local" => $row["local"],
                "ceco" => $row["ceco"],
                "estado" => $row["estado"],
                "fecha_baja" => $row["fecha_baja"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_centro_costo($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, ceco, local, descripcion, fecha_baja, costo_mensual, observacion, estado FROM tbl_concar_centros_costo WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_centro_costo($data)
{
    try {
        include("db_connect.php");

        $sql_query = "";

        $id = $data["id"];

        if ($id == 0) {
            $sql_query .= "INSERT INTO tbl_concar_centros_costo (local, descripcion, ceco, fecha_baja, costo_mensual, observacion, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
        } else {
            $sql_query .= "UPDATE tbl_concar_centros_costo SET local = ?, descripcion = ?, ceco = ?, fecha_baja = ?, costo_mensual = ?, observacion = ?, estado = ? WHERE id = ?";
        }

        $stmt = $mysqli->prepare($sql_query);
        $local = $data["local"];
        $descripcion = $data["descripcion"];
        $ceco = $data["ceco"];
        $fecha_baja = null;
        if (!is_null_or_empty_string($data["fecha_baja"])) {
            $fecha_baja_valida = validar_formato_fecha($data["fecha_baja"]);
            if ($fecha_baja_valida != false) {
                $fecha_baja = $fecha_baja_valida->setTime(0, 0, 0)->format('Y-m-d H:i:s');
            } else {
                set_status_code_response(400, "El campo 'fecha_baja' no es una fecha valida.", null);
            }
        }
        $costo_mensual = floatval($data["costo_mensual"]);
        $observacion = $data["observacion"];
        $estado = $data["estado"];

        if ($id == 0) {
            bind_param_statement($stmt, "ssssdsi", [$local, $descripcion, $ceco, $fecha_baja, $costo_mensual, $observacion, $estado]);
        } else {
            bind_param_statement($stmt, "ssssdsii", [$local, $descripcion, $ceco, $fecha_baja, $costo_mensual, $observacion, $estado, $id]);
        }

        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_centro_costo($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_centros_costo WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_nombre_proveedor_clave($proveedor_id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT nombre FROM tbl_concar_proveedores WHERE id = ?";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$proveedor_id]);
        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $nombre_proveedor = $object->nombre;
        $nombre_proveedor_clave = str_replace(" ", "_", strtolower($nombre_proveedor));
        $stmt->close();
        return $nombre_proveedor_clave;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_numero_cuenta($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, nro_cuenta, ceco FROM tbl_concar_numeros_cuenta WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_numero_cuenta($data)
{
    try {
        include("db_connect.php");

        $sql_query = "";

        if ($data["id"] == 0) {
            $sql_query = "INSERT INTO tbl_concar_numeros_cuenta (nro_cuenta, ceco) VALUES (?, ?);";
        } else {
            $sql_query = "UPDATE tbl_concar_numeros_cuenta SET nro_cuenta = ?, ceco = ? WHERE id = ?;";
        }
        $stmt = $mysqli->prepare($sql_query);

        if ($data["id"] == 0) {
            bind_param_statement($stmt, "ss", [$data["nro_cuenta"], $data["ceco"]]);
        } else {
            bind_param_statement($stmt, "ssi", [$data["nro_cuenta"], $data["ceco"], $data["id"]]);
        }

        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_numero_cuenta($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_numeros_cuenta WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_codigo_pago($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, cod_pago, ceco FROM tbl_concar_codigos_pago WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_codigo_pago($data)
{
    try {
        include("db_connect.php");

        $sql_query = "";

        if ($data["id"] == 0) {
            $sql_query = "INSERT INTO tbl_concar_codigos_pago (cod_pago, ceco) VALUES (?, ?);";
        } else {
            $sql_query = "UPDATE tbl_concar_codigos_pago SET cod_pago = ?, ceco = ? WHERE id = ?;";
        }
        $stmt = $mysqli->prepare($sql_query);

        if ($data["id"] == 0) {
            bind_param_statement($stmt, "ss", [$data["cod_pago"], $data["ceco"]]);
        } else {
            bind_param_statement($stmt, "ssi", [$data["cod_pago"], $data["ceco"], $data["id"]]);
        }

        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_codigo_pago($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_codigos_pago WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_cuentas_contables()
{
    try {
        $data = [];
        include("db_connect.php");
        $sql_query = "SELECT id, cta_contable, concar FROM tbl_concar_cuentas_contables;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "cta_contable" => $row["cta_contable"],
                "concar" => $row["concar"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_cuenta_contable($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, cta_contable, concar FROM tbl_concar_cuentas_contables WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_cuenta_contable($data)
{
    try {
        include("db_connect.php");

        $sql_query = "";

        if ($data["id"] == 0) {
            $sql_query = "INSERT INTO tbl_concar_cuentas_contables (cta_contable, concar) VALUES (?, ?);";
        } else {
            $sql_query = "UPDATE tbl_concar_cuentas_contables SET cta_contable = ?, concar = ? WHERE id = ?;";
        }
        $stmt = $mysqli->prepare($sql_query);

        if ($data["id"] == 0) {
            bind_param_statement($stmt, "ss", [$data["cta_contable"], $data["concar"]]);
        } else {
            bind_param_statement($stmt, "ssi", [$data["cta_contable"], $data["concar"], $data["id"]]);
        }

        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_cuenta_contable($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_cuentas_contables WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_proveedores()
{
    try {
        $data = [];
        include("db_connect.php");
        $sql_query = "SELECT id, nombre, ruc FROM tbl_concar_proveedores;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "nombre" => $row["nombre"],
                "ruc" => $row["ruc"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_proveedor($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, nombre, ruc FROM tbl_concar_proveedores WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt == false) {
            set_status_code_response(500, $mysqli->error, null);
        }
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_proveedor($data)
{
    try {
        include("db_connect.php");

        $sql_query = "";

        if ($data["id"] == 0) {
            $sql_query = "INSERT INTO tbl_concar_proveedores (nombre, ruc) VALUES (?, ?);";
        } else {
            $sql_query = "UPDATE tbl_concar_proveedores SET nombre = ?, ruc = ? WHERE id = ?;";
        }
        $stmt = $mysqli->prepare($sql_query);

        if ($data["id"] == 0) {
            bind_param_statement($stmt, "ss", [$data["nombre"], $data["ruc"]]);
        } else {
            bind_param_statement($stmt, "ssi", [$data["nombre"], $data["ruc"], $data["id"]]);
        }

        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_proveedor($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_proveedores WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_conceptos_facturables()
{
    try {
        $data = [];
        include("db_connect.php");
        $sql_query = "SELECT cf.id, cf.concepto, cc.cta_contable, cc.concar FROM tbl_concar_conceptos_facturables cf INNER JOIN tbl_concar_cuentas_contables cc ON cc.cta_contable = cf.cta_contable;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "concepto" => $row["concepto"],
                "cta_contable" => $row["cta_contable"],
                "concar" => $row["concar"],
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_conceptos_facturables_pendientes($conceptos_facturables)
{
    try {
        $data = [];
        if (count($conceptos_facturables) > 0) {
            $array_template = [
                "id" => 0,
                "concepto" => null,
                "cta_contable" => null,
                "concar" => null
            ];
            $sql_query = "select v.concepto from (";
            $sql_selects_conceptos_facturables = [];
            foreach ($conceptos_facturables as $concepto_facturable) {
                $sql_selects_conceptos_facturables[] = "select '{$concepto_facturable}' as concepto";
            }
            $sql_query .= implode(" union all ", $sql_selects_conceptos_facturables);
            $sql_query .= ") v where not exists (select 1 from tbl_concar_conceptos_facturables cf where cf.concepto = v.concepto);";

            include("db_connect.php");

            $stmt = $mysqli->prepare($sql_query);

            $result = execute_sql_statement($stmt);

            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["concepto"] = $row["concepto"];
                $data[] = $data_item;
            }
            $stmt->close();
        }
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_concepto_facturable($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, concepto, cta_contable FROM tbl_concar_conceptos_facturables WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt == false) {
            set_status_code_response(500, $mysqli->error, null);
        }
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_concepto_facturable($data)
{
    try {
        include("db_connect.php");

        $sql_query = "";

        if ($data["id"] == 0) {
            $sql_query = "INSERT INTO tbl_concar_conceptos_facturables (concepto, cta_contable) VALUES (?, ?);";
        } else {
            $sql_query = "UPDATE tbl_concar_conceptos_facturables SET concepto = ?, cta_contable = ? WHERE id = ?;";
        }
        $stmt = $mysqli->prepare($sql_query);

        if ($data["id"] == 0) {
            bind_param_statement($stmt, "ss", [$data["concepto"], $data["cta_contable"]]);
        } else {
            bind_param_statement($stmt, "ssi", [$data["concepto"], $data["cta_contable"], $data["id"]]);
        }

        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_concepto_facturable($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_conceptos_facturables WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_codigos_comercio()
{
    try {
        $data = [];
        include("db_connect.php");
        $sql_query = "SELECT cco.id, cco.cod_comercio, cc.ceco, cc.local FROM tbl_concar_codigos_comercio cco INNER JOIN tbl_concar_centros_costo cc ON cco.ceco = cc.ceco;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "cod_comercio" => $row["cod_comercio"],
                "local" => $row["local"],
                "ceco" => $row["ceco"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_codigos_comercio_pendientes($codigos_comercio)
{
    try {
        $data = [];
        if (count($codigos_comercio) > 0) {
            $array_template = [
                "id" => 0,
                "cod_comercio" => null,
                "local" => null,
                "ceco" => null
            ];
            $sql_query = "select v.cod_comercio from (";
            $sql_selects_codigos_comercio = [];
            foreach ($codigos_comercio as $codigo_comercio) {
                $sql_selects_codigos_comercio[] = "select '{$codigo_comercio}' as cod_comercio";
            }
            $sql_query .= implode(" union all ", $sql_selects_codigos_comercio);
            $sql_query .= ") v where not exists (select 1 from tbl_concar_codigos_comercio cco where cco.cod_comercio = v.cod_comercio);";

            include("db_connect.php");

            $stmt = $mysqli->prepare($sql_query);

            $result = execute_sql_statement($stmt);

            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["cod_comercio"] = strval($row["cod_comercio"]);
                $data[] = $data_item;
            }
            $str_codigos_comercio = implode(", ", $codigos_comercio);
            $sql_query = "SELECT cco.id, cco.cod_comercio, cc.local, cc.ceco FROM tbl_concar_codigos_comercio cco INNER JOIN tbl_concar_centros_costo cc ON cc.ceco = cco.ceco WHERE (cc.local = '' OR cc.local = NULL) and cco.cod_comercio IN ({$str_codigos_comercio});";
            $stmt = $mysqli->prepare($sql_query);
            $result = execute_sql_statement($stmt);
            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["id"] = $row["id"];
                $data_item["cod_comercio"] = $row["cod_comercio"];
                $data_item["local"] = $row["local"];
                $data_item["ceco"] = $row["ceco"];
                $data[] = $data_item;
            }
            $stmt->close();
        }
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_codigo_comercio($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, cod_comercio, ceco FROM tbl_concar_codigos_comercio WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_codigo_comercio($data)
{
    try {
        include("db_connect.php");

        $sql_query = "";

        if ($data["id"] == 0) {
            $sql_query = "INSERT INTO tbl_concar_codigos_comercio (cod_comercio, ceco) VALUES (?, ?);";
        } else {
            $sql_query = "UPDATE tbl_concar_codigos_comercio SET cod_comercio = ?, ceco = ? WHERE id = ?;";
        }
        $stmt = $mysqli->prepare($sql_query);

        if ($data["id"] == 0) {
            bind_param_statement($stmt, "ss", [$data["cod_comercio"], $data["ceco"]]);
        } else {
            bind_param_statement($stmt, "ssi", [$data["cod_comercio"], $data["ceco"], $data["id"]]);
        }

        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_codigo_comercio($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_codigos_comercio WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_bancos()
{
    try {
        $data = [];
        include("db_connect.php");
        $sql_query = "SELECT id, nombre, ruc, estado FROM tbl_concar_bancos ORDER by nombre;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "nombre" => $row["nombre"],
                "ruc" => $row["ruc"],
                "estado" => $row["estado"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_bancos_activos()
{
    try {
        $data = [];
        include("db_connect.php");
        $sql_query = "SELECT id, nombre, ruc, estado FROM tbl_concar_bancos WHERE estado = 1 ORDER BY nombre;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "nombre" => $row["nombre"],
                "ruc" => $row["ruc"],
                "estado" => $row["estado"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_banco($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, nombre, razon_social, ruc, estado FROM tbl_concar_bancos WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_banco($data)
{
    try {
        include("db_connect.php");

        $sql_query = "";

        if ($data["id"] == 0) {
            $sql_query = "INSERT INTO tbl_concar_bancos (nombre, razon_social, ruc, estado) VALUES (?, ?, ?, ?);";
        } else {
            $sql_query = "UPDATE tbl_concar_bancos SET nombre = ?, razon_social = ?, ruc = ?, estado = ? WHERE id = ?;";
        }
        $stmt = $mysqli->prepare($sql_query);

        if ($data["id"] == 0) {
            bind_param_statement($stmt, "sssi", [$data["nombre"], $data["razon_social"], $data["ruc"], $data["estado"]]);
        } else {
            bind_param_statement($stmt, "sssii", [$data["nombre"], $data["razon_social"], $data["ruc"], $data["estado"], $data["id"]]);
        }

        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_banco($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_bancos WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_detalle_bancos($archivo_proveedor_id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, ROUND(importe, 2) importe, banco_id, archivo_proveedor_id FROM tbl_concar_detalle_bancos WHERE archivo_proveedor_id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$archivo_proveedor_id]);
        $result = execute_sql_statement($stmt);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "importe" => floatVal($row["importe"]),
                "banco_id" => $row["banco_id"],
                "archivo_proveedor_id" => $row["archivo_proveedor_id"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_detalle_banco($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_detalle_bancos WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_detalle_banco($data)
{
    try {
        include("db_connect.php");
        $mysqli->query("START TRANSACTION");
        $sql_query_insert = "";
        $sql_query_updates = [];
        $sql_insert_values = [];
        $stmt = null;
        $banco_ids = array_column($data, 'banco_id');
        $exists_duplicate = count($banco_ids) !== count(array_unique(array_unique($banco_ids)));
        if ($exists_duplicate) {
            set_status_code_response(400, "Existe un banco duplicado en la lista.", null);
        }

        foreach ($data as $object) {
            $id = intval($object->id);
            if ($id == 0) {
                $sql_insert_values[] = "({$object->banco_id}, {$object->importe}, {$object->archivo_proveedor_id})";
            } else {
                $sql_query_updates[] = "UPDATE tbl_concar_detalle_bancos SET banco_id = {$object->banco_id}, importe = {$object->importe} WHERE id = {$id};";
            }
        }

        if (count($sql_insert_values) > 0) {
            $sql_query_insert = "INSERT INTO tbl_concar_detalle_bancos (banco_id, importe, archivo_proveedor_id) VALUES ";
            $sql_query_insert .= implode(", ", $sql_insert_values) . ";";
            $stmt = $mysqli->prepare($sql_query_insert);
            execute_sql_statement($stmt);
        }

        foreach ($sql_query_updates as $sql_query_update) {
            $stmt = $mysqli->prepare($sql_query_update);
            execute_sql_statement($stmt);
        }
        $mysqli->query("COMMIT");
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_numeros_abonados()
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT na.id, na.nro_abonado, cc.ceco, cc.local FROM tbl_concar_numeros_abonado na INNER JOIN tbl_concar_centros_costo cc WHERE cc.ceco = na.ceco;";
        $stmt = $mysqli->prepare($sql_query);
        $result = execute_sql_statement($stmt);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => $row["id"],
                "nro_abonado" => $row["nro_abonado"],
                "ceco" => $row["ceco"],
                "local" => $row["local"]
            ];
        }
        $stmt->close();
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function eliminar_numero_abonado($id)
{
    try {
        include("db_connect.php");
        $sql_query = "DELETE FROM tbl_concar_numeros_abonado WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_numero_abonado($id)
{
    try {
        include("db_connect.php");
        $sql_query = "SELECT id, nro_abonado, ceco FROM tbl_concar_numeros_abonado WHERE id = ?;";
        $stmt = $mysqli->prepare($sql_query);
        bind_param_statement($stmt, "i", [$id]);
        $result = execute_sql_statement($stmt);
        return $result->fetch_assoc();
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function editar_numero_abonado($data)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($data["id"] == 0) {
            $sql_query = "INSERT INTO tbl_concar_numeros_abonado (nro_abonado, ceco) VALUES (?, ?);";
        } else {
            $sql_query = "UPDATE tbl_concar_numeros_abonado SET nro_abonado = ?, ceco = ? WHERE id = ?;";
        }
        $stmt = $mysqli->prepare($sql_query);
        if ($data["id"] == 0) {
            bind_param_statement($stmt, "ss", [$data["nro_abonado"], $data["ceco"]]);
        } else {
            bind_param_statement($stmt, "ssi", [$data["nro_abonado"], $data["ceco"], $data["id"]]);
        }
        return execute_sql_statement($stmt);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_numeros_abonado_pendientes($numeros_abonado)
{
    try {
        $data = [];
        if (count($numeros_abonado) > 0) {
            $array_template = [
                "id" => 0,
                "nro_abonado" => null,
                "local" => null,
                "ceco" => null
            ];
            $sql_query = "select v.nro_abonado from (";
            $sql_selects_numeros_abonado = [];
            foreach ($numeros_abonado as $numero_abonado) {
                //TODO Check This! 
                /*if (!is_numeric($numero_abonado)) {
                    set_status_code_response(500, "Al leer la información del archivo el número de abonado '{$numero_abonado}' no tiene el formato correcto.", null);
                }*/
                $sql_selects_numeros_abonado[] = "select '{$numero_abonado}' as nro_abonado";
            }
            $sql_query .= implode(" union all ", $sql_selects_numeros_abonado);
            $sql_query .= ") v where not exists (select 1 from tbl_concar_numeros_abonado na where na.nro_abonado = v.nro_abonado);";

            include("db_connect.php");

            $stmt = $mysqli->prepare($sql_query);

            $result = execute_sql_statement($stmt);

            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["nro_abonado"] = strval($row["nro_abonado"]);
                $data[] = $data_item;
            }
            $str_numeros_abonado = "'" . implode("', '", $numeros_abonado) . "'";
            $sql_query = "SELECT na.id, na.nro_abonado, cc.local, na.ceco FROM tbl_concar_numeros_abonado na INNER JOIN tbl_concar_centros_costo cc ON cc.ceco = na.ceco WHERE (cc.local = '' OR cc.local = NULL) and na.nro_abonado IN ({$str_numeros_abonado});";
            $stmt = $mysqli->prepare($sql_query);
            $result = execute_sql_statement($stmt);
            while ($row = $result->fetch_assoc()) {
                $data_item = $array_template;
                $data_item["id"] = $row["id"];
                $data_item["nro_abonado"] = $row["nro_abonado"];
                $data_item["local"] = $row["local"];
                $data_item["ceco"] = $row["ceco"];
                $data[] = $data_item;
            }
            $stmt->close();
        }
        return $data;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function get_numero_documento_archivo_proveedor_detalle_por_archivo_proveedor_id($archivo_proveedor_id)
{
    include("db_connect.php");
    $sql_query = "SELECT apd.factura AS nro_documento FROM tbl_concar_archivo_proveedor_detalle apd WHERE apd.archivo_proveedor_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql_query);
    bind_param_statement($stmt, "i", [$archivo_proveedor_id]);
    $result = execute_sql_statement($stmt);
    $object = $result->fetch_object();
    if ($object != null) {
        return $object->nro_documento;
    }
    return false;
}

function check_exist_duplicate_nombre_on_proveedores($nombre, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_proveedores WHERE nombre = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_proveedores WHERE nombre = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "s", [$nombre]);
        } else {
            bind_param_statement($stmt, "si", [$nombre, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_ruc_on_proveedores($ruc, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_proveedores WHERE ruc = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_proveedores WHERE ruc = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "s", [$ruc]);
        } else {
            bind_param_statement($stmt, "si", [$ruc, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_ceco_on_centros_costo($ceco, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_centros_costo WHERE ceco = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_centros_costo WHERE ceco = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "s", [$ceco]);
        } else {
            bind_param_statement($stmt, "si", [$ceco, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_nro_cuenta_and_ceco_on_numeros_cuenta($nro_cuenta, $ceco, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_numeros_cuenta WHERE nro_cuenta = ? AND ceco = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_numeros_cuenta WHERE nro_cuenta = ? AND ceco = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "ss", [$nro_cuenta, $ceco]);
        } else {
            bind_param_statement($stmt, "ssi", [$nro_cuenta, $ceco, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_cod_pago_and_ceco_on_codigos_pago($cod_pago, $ceco, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_codigos_pago WHERE cod_pago = ? AND ceco = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_codigos_pago WHERE cod_pago = ? AND ceco = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "ss", [$cod_pago, $ceco]);
        } else {
            bind_param_statement($stmt, "ssi", [$cod_pago, $ceco, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_cta_contable_and_concar_on_cuentas_contables($cta_contable, $concar, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_cuentas_contables WHERE cta_contable = ? AND concar = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_cuentas_contables WHERE cta_contable = ? AND concar = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "ss", [$cta_contable, $concar]);
        } else {
            bind_param_statement($stmt, "ssi", [$cta_contable, $concar, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_concepto_and_cta_contable_on_conceptos_facturables($concepto, $cta_contable, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_conceptos_facturables WHERE concepto = ? AND cta_contable = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_conceptos_facturables WHERE concepto = ? AND cta_contable = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "ss", [$concepto, $cta_contable]);
        } else {
            bind_param_statement($stmt, "ssi", [$concepto, $cta_contable, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_cod_comercio_and_ceco_on_codigos_comercio($cod_comercio, $ceco, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_codigos_comercio WHERE cod_comercio = ? AND ceco = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_codigos_comercio WHERE cod_comercio = ? AND ceco = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "ss", [$cod_comercio, $ceco]);
        } else {
            bind_param_statement($stmt, "ssi", [$cod_comercio, $ceco, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_nombre_on_bancos($nombre, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_bancos WHERE nombre = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_bancos WHERE nombre = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "s", [$nombre]);
        } else {
            bind_param_statement($stmt, "si", [$nombre, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_ruc_on_bancos($ruc, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_bancos WHERE ruc = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_bancos WHERE ruc = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "s", [$ruc]);
        } else {
            bind_param_statement($stmt, "si", [$ruc, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function check_exist_duplicate_nro_abonado_and_ceco_on_numeros_abonado($nro_abonado, $ceco, $id)
{
    try {
        include("db_connect.php");
        $sql_query = "";
        if ($id == null || $id == 0) {
            $sql_query = "SELECT id FROM tbl_concar_numeros_abonado WHERE nro_abonado = ? AND ceco = ? LIMIT 1;";
        } else {
            $sql_query = "SELECT id FROM tbl_concar_numeros_abonado WHERE nro_abonado = ? AND ceco = ? AND id NOT IN (?) LIMIT 1;";
        }

        $stmt = $mysqli->prepare($sql_query);

        if ($id == null || $id == 0) {
            bind_param_statement($stmt, "ss", [$nro_abonado, $ceco]);
        } else {
            bind_param_statement($stmt, "ssi", [$nro_abonado, $ceco, $id]);
        }

        $result = execute_sql_statement($stmt);
        $object = $result->fetch_object();
        $stmt->close();
        return ($object != null);
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

function bind_param_statement($stmt, $str_params, $params)
{
    include("db_connect.php");
    $rc = $stmt->bind_param($str_params, ...$params);
    if ($rc === false) {
        set_status_code_response(500, htmlspecialchars($stmt->error), null);
    }
}

function prepare_statement($sql_query)
{
    include("db_connect.php");
    $stmt = $mysqli->prepare($sql_query);
    if (!$stmt) {
        set_status_code_response(500, htmlspecialchars($mysqli->error), null);
    }
    return $stmt;
}

function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}
