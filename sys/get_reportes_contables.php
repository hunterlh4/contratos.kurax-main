<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
error_reporting(0);

function generar_cabecera(){
    $cabecera=[];
    $filaTitulo =[];
    $filaTitulo['Campo']                  = "Campo";
    $filaTitulo['sub_diario']             = "Sub Diario";
    $filaTitulo['numero_de_comprobante']  = "Número de Comprobante";
    $filaTitulo['fecha_de_comprobante']   = "Fecha de Comprobante";
    $filaTitulo['codigo_de_moneda']       = "Código de Moneda";
    $filaTitulo['glosa_principal']        = "Glosa Principal";
    $filaTitulo['tipo_de_cambio']         = "Tipo de Cambio";
    $filaTitulo['tipo_de_conversion']     = "Tipo de Conversión";
    $filaTitulo['flag_de_conversion_de_moneda'] = "Flag de Conversión de Moneda";
    $filaTitulo['fecha_tipo_de_cambio']   = "Fecha Tipo de Cambio";
    $filaTitulo['cuenta_contable']        = "Cuenta Contable";
    $filaTitulo['codigo_de_anexo']        = "Código de Anexo";
    $filaTitulo['codigo_de_centro_de_costo'] = "Código de Centro de Costo";
    $filaTitulo['debe_haber']             = "Debe / Haber";
    $filaTitulo['importe_original_prestamo_slot'] = "Importe Original";
    $filaTitulo['importe_en_dolares']     = "Importe en Dólares";
    $filaTitulo['importe_en_soles']       = "Importe en Soles";
    $filaTitulo['tipo_de_documento']      = "Tipo de Documento";
    $filaTitulo['numero_de_documento']       = "Número de Documento";
    $filaTitulo['fecha_de_documento']     = "Fecha de Documento";
    $filaTitulo['fecha_de_vencimiento']   = "Fecha de Vencimiento";
    $filaTitulo['codigo_de_area']         = "Código de Area";
    $filaTitulo['glosa_detalle']          = "Glosa Detalle";
    $filaTitulo['codigo_de_anexo_auxiliar'] = "Código de Anexo Auxiliar";
    $filaTitulo['medio_de_pago']          = "Medio de Pago";
    $filaTitulo['tipo_de_documento_de_referencia'] = "Tipo de Documento de Referencia";
    $filaTitulo['numero_de_documento_referencia'] = "Número de Documento Referencia";
    $filaTitulo['fecha_documento_referencia'] = "Fecha Documento Referencia";
    $filaTitulo['nro_maq_registradora_tipo_doc_ref'] = "Nro Máq. Registradora Tipo Doc. Ref.";
    $filaTitulo['base_imponible_documento_referencia'] = "Base Imponible Documento Referencia";
    $filaTitulo['igv_documento_provision'] = "IGV Documento Provisión";
    $filaTitulo['tipo_referencia_en_estado_mq'] = "Tipo Referencia en estado MQ";
    $filaTitulo['numero_serie_caja_registradora'] = "Número Serie Caja Registradora";
    $filaTitulo['fecha_de_operacion']     = "Fecha de Operación";
    $filaTitulo['tipo_de_tasa']           = "Tipo de Tasa";
    $filaTitulo['tasa_detraccion_percepcion'] = "Tasa Detracción/Percepción";
    $filaTitulo['importe_base_detraccion_percepcion_dolares'] = "Importe Base Detracción/Percepción Dólares";
    $filaTitulo['importe_base_detraccion_percepcion_soles'] = "Importe Base Detracción/Percepción Soles";
    $filaTitulo['tipo_cambio']            = "Tipo Cambio para 'F'";
    $filaTitulo['importe_de_igv']         = "Importe de IGV sin derecho crédito fiscal";
    array_push($cabecera,$filaTitulo);
    $filaTitulo =[];
    $filaTitulo['Campo']                  = "Restricciones";
    $filaTitulo['sub_diario']             = "Ver T.G. 02";
    $filaTitulo['numero_de_comprobante']  = "Los dos primeros dígitos son el mes y los otros 4 siguientes un correlativo";
    $filaTitulo['fecha_de_comprobante']   = "";
    $filaTitulo['codigo_de_moneda']       = "Ver T.G. 03";
    $filaTitulo['glosa_principal']        = "";
    $filaTitulo['tipo_de_cambio']         = "Llenar  solo si Tipo de Conversión es 'C'. Debe estar entre >=0 y <=9999.999999";
    $filaTitulo['tipo_de_conversion']     = "Solo: 'C'= Especial, 'M'=Compra, 'V'=Venta , 'F' De acuerdo a fecha";
    $filaTitulo['flag_de_conversion_de_moneda'] = "Solo: 'S' = Si se convierte, 'N'= No se convierte";
    $filaTitulo['fecha_tipo_de_cambio']   = "Si  Tipo de Conversión 'F'";
    $filaTitulo['cuenta_contable']        = "Debe existir en el Plan de Cuentas";
    $filaTitulo['codigo_de_anexo']        = "Si Cuenta Contable tiene seleccionado Tipo de Anexo, debe existir en la tabla de Anexos";
    $filaTitulo['codigo_de_centro_de_costo'] = "Si Cuenta Contable tiene habilitado C. Costo, Ver T.G. 05";
    $filaTitulo['debe_haber']             = " 'D' ó 'H'";
    $filaTitulo['importe_original_prestamo_slot'] = "Importe original de la cuenta contable. Obligatorio, debe estar entre >=0 y <=99999999999.99";
    $filaTitulo['importe_en_dolares']     = "Importe de la Cuenta Contable en Dólares. Obligatorio si Flag de Conversión de Moneda esta en 'N', debe estar entre >=0 y <=99999999999.99";
    $filaTitulo['importe_en_soles']       = "Importe de la Cuenta Contable en Soles. Obligatorio si Flag de Conversión de Moneda esta en 'N', debe estra entre >=0 y <=99999999999.99 ";
    $filaTitulo['tipo_de_documento']      = "Si Cuenta Contable tiene habilitado el Documento Referencia Ver T.G. 06";
    $filaTitulo['numero_de_documento']       = "Si Cuenta Contable tiene habilitado el Documento Referencia Incluye Serie y Número";
    $filaTitulo['fecha_de_documento']     = "Si Cuenta Contable tiene habilitado el Documento Referencia";
    $filaTitulo['fecha_de_vencimiento']   = "Si Cuenta Contable tiene habilitada la Fecha de Vencimiento";
    $filaTitulo['codigo_de_area']         = "Si Cuenta Contable tiene habilitada el Area. Ver T.G. 26";
    $filaTitulo['glosa_detalle']          = "";
    $filaTitulo['codigo_de_anexo_auxiliar'] = "Si Cuenta Contable tiene seleccionado Tipo de Anexo Referencia";
    $filaTitulo['medio_de_pago']          = "Si Cuenta Contable tiene habilitado Tipo Medio Pago. Ver T.G. 'S1'o";
    $filaTitulo['tipo_de_documento_de_referencia'] = "Si Tipo de Documento es 'NA' ó 'ND' Ver T.G. 06";
    $filaTitulo['numero_de_documento_referencia'] = "Si Tipo de Documento es 'NC', 'NA' ó 'ND', incluye Serie y Número";
    $filaTitulo['fecha_documento_referencia'] = "Si Tipo de Documento es 'NC', 'NA' ó 'ND'";
    $filaTitulo['nro_maq_registradora_tipo_doc_ref'] = "Si Tipo de Documento es 'NC', 'NA' ó 'ND'. Solo cuando el Tipo Documento de Referencia 'TK'.";
    $filaTitulo['base_imponible_documento_referencia'] = "Si Tipo de Documento es 'NC', 'NA' ó 'ND'";
    $filaTitulo['igv_documento_provision'] = "Si Tipo de Documento es 'NC', 'NA' ó 'ND'";
    $filaTitulo['tipo_referencia_en_estado_mq'] = "Si la Cuenta Contable tiene Habilitado Documento Referencia 2 y  Tipo de Documento es 'TK'";
    $filaTitulo['numero_serie_caja_registradora'] = "Si la Cuenta Contable teien Habilitado Documento Referencia 2 y  Tipo de Documento es 'TK'";
    $filaTitulo['fecha_de_operacion']     = "Si la Cuenta Contable tiene Habilitado Documento Referencia 2. Cuando Tipo de Documento es 'TK', consignar la fecha de emision del ticket";
    $filaTitulo['tipo_de_tasa']           = "Si la Cuenta Contable tiene configurada la Tasa:  Si es '1' ver T.G. 28 y '2' ver T.G. 29";
    $filaTitulo['tasa_detraccion_percepcion'] = "Si la Cuenta Contable tiene conf. en Tasa:  Si es '1' ver T.G. 28 y '2' ver T.G. 29. Debe estar entre >=0 y <=999.99";
    $filaTitulo['importe_base_detraccion_percepcion_dolares'] = "Si la Cuenta Contable tiene configurada la Tasa. Debe ser el importe total del documento y estar entre >=0 y <=99999999999.99";
    $filaTitulo['importe_base_detraccion_percepcion_soles'] = "Si la Cuenta Contable tiene configurada la Tasa. Debe ser el importe total del documento y estar entre >=0 y <=99999999999.99";
    $filaTitulo['tipo_cambio']            = "Especificar solo si Tipo Conversión es 'F'. Se permite 'M' Compra y 'V' Venta.";
    $filaTitulo['importe_de_igv']         = "Especificar solo para comprobantes de compras con IGV sin derecho de crédito Fiscal. Se detalle solo en la cuenta 42xxxx";
    array_push($cabecera,$filaTitulo);
    $filaTitulo =[];
    $filaTitulo['Campo']                  = "Tamaño/Formato";
    $filaTitulo['sub_diario']             = "4 Caracteres";
    $filaTitulo['numero_de_comprobante']  = "6 Caracteres";
    $filaTitulo['fecha_de_comprobante']   = "dd/mm/aaaa";
    $filaTitulo['codigo_de_moneda']       = "2 Caracteres";
    $filaTitulo['glosa_principal']        = "40 Caracteres";
    $filaTitulo['tipo_de_cambio']         = "Numérico 11, 6";
    $filaTitulo['tipo_de_conversion']     = "1 Caracteres";
    $filaTitulo['flag_de_conversion_de_moneda'] = "1 Caracteres";
    $filaTitulo['fecha_tipo_de_cambio']   = "dd/mm/aaaa";
    $filaTitulo['cuenta_contable']        = "12 Caracteres";
    $filaTitulo['codigo_de_anexo']        = "18 Caracteres";
    $filaTitulo['codigo_de_centro_de_costo'] = "";
    $filaTitulo['debe_haber']             = "1 Carácter";
    $filaTitulo['importe_original_prestamo_slot'] = "Numérico 14,2";
    $filaTitulo['importe_en_dolares']     = "Numérico 14,2";
    $filaTitulo['importe_en_soles']       = "Numérico 14,2";
    $filaTitulo['tipo_de_documento']      = "2 Caracteres";
    $filaTitulo['numero_de_documento']       = "20 Caracteres";
    $filaTitulo['fecha_de_documento']     = "dd/mm/aaaa";
    $filaTitulo['fecha_de_vencimiento']   = "dd/mm/aaaa";
    $filaTitulo['codigo_de_area']         = "3 Caracteres";
    $filaTitulo['glosa_detalle']          = "30 Caracteres";
    $filaTitulo['codigo_de_anexo_auxiliar'] = "18 Caracteres";
    $filaTitulo['medio_de_pago']          = "8 Caracteres";
    $filaTitulo['tipo_de_documento_de_referencia'] = "2 Caracteres";
    $filaTitulo['numero_de_documento_referencia'] = "20 Caracteres";
    $filaTitulo['fecha_documento_referencia'] = "dd/mm/aaaa";
    $filaTitulo['nro_maq_registradora_tipo_doc_ref'] = "20 Caracteres";
    $filaTitulo['base_imponible_documento_referencia'] = "Numérico 14,2";
    $filaTitulo['igv_documento_provision'] = "Numérico 14,2";
    $filaTitulo['tipo_referencia_en_estado_mq'] = " 'MQ'";
    $filaTitulo['numero_serie_caja_registradora'] = "15 caracteres";
    $filaTitulo['fecha_de_operacion']     = "dd/mm/aaaa";
    $filaTitulo['tipo_de_tasa']           = "5 Caracteres";
    $filaTitulo['tasa_detraccion_percepcion'] = "Numérico 14,2";
    $filaTitulo['importe_base_detraccion_percepcion_dolares'] = "Numérico 14,2";
    $filaTitulo['importe_base_detraccion_percepcion_soles'] = "Numérico 14,2";
    $filaTitulo['tipo_cambio']            = "1 Caracter";
    $filaTitulo['importe_de_igv']         = "Numérico 14,2";
    array_push($cabecera,$filaTitulo);
    return $cabecera;
}

function generar_excel($array_datos , $filename){
    global $mysqli;
    global $login;
    require_once '../phpexcel/classes/PHPExcel.php';
    $filesPath = '/var/www/html/export/files_exported/reportes_contables/';

    $doc = new PHPExcel();
    $doc->setActiveSheetIndex(0);
    $doc->getActiveSheet()->getColumnDimension('A')->setWidth(5);
    $doc->getActiveSheet()->getColumnDimension('F')->setWidth(22);
    $doc->getActiveSheet()->getColumnDimension('L')->setWidth(18);
    

    $doc->getActiveSheet()->fromArray($array_datos, null, 'A1', true);
    $doc->getActiveSheet()->setAutoFilter("A3:AN3");

    $attach = $filesPath . $filename;
    $objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel2007');
    try {
        $objWriter->save($attach);
    } catch (PHPExcel_Writer_Exception $e) {
        echo json_encode(["error" => $e]);
        exit;
    }
    $objWriter->save($attach);
    $excel_path = $filesPath . $filename;
    $excel_path_download = '/export/files_exported/reportes_contables/' . $filename;
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    $url = $filename;

    $insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
    $insert_cmd .= " VALUES ('" . $url . "','excel','xlsx','" . filesize($attach) . "','" . date("Y-m-d h:i:s") . "','" . $login["id"] . "')";
    $mysqli->query($insert_cmd);

    echo json_encode(array(
        "path" => $excel_path_download,
        "url" => $filename,
        "tipo" => "excel",
        "ext" => "xlsx",
        "size" => filesize($excel_path),
        "fecha_registro" => date("d-m-Y h:i:s"),
        "sql" => $insert_cmd
    ));
    exit;

}

function getFinalTableReportesContables($post)
{
    global $mysqli;
    global $login;

    $con_db_name = env('DB_DATABASE');
    $con_host = env('DB_HOST');
    $con_user = env('DB_USERNAME');
    $con_pass = env('DB_PASSWORD');
    $mysqli = new mysqli($con_host, $con_user, $con_pass, $con_db_name, 3306);
    $get_data = $post["sec_contable_export"];
    $local_id = $get_data["local_id"];
    $is_televentas = $get_data["is_televentas"];
    $fecha_inicio = $get_data["fecha_inicio"];
    $fecha_inicio_pretty = date("d-m-Y", strtotime($get_data["fecha_inicio"]));
    $fecha_fin = date("Y-m-d", strtotime($get_data["fecha_fin"] . " +1 day"));
    $fecha_fin_pretty = date("d-m-Y", strtotime($get_data["fecha_fin"]));

    //$where_id = $local_id == "all" ? "WHERE l.id != 1" : "WHERE l.id = '".$local_id."'";
    $where_id = $is_televentas !== "true" ? "WHERE l.id != 1" : "WHERE l.nombre like 'televentas%'";
    //$locals_query = $mysqli->query("SELECT l.id, l.nombre, l.cc_id FROM tbl_locales l " . $where_id);
    //$locals_query = $mysqli->query("SELECT l.id, l.nombre, l.cc_id FROM tbl_locales l WHERE SUBSTRING((REPLACE(upper(l.nombre),' ','')),1,5)='REDAT'");

    $query = "SELECT l.id, l.nombre, l.cc_id FROM tbl_locales l WHERE SUBSTRING((REPLACE(upper(l.nombre),' ','')),1,5) in ('REDAT','Telev')";
    if($login["usuario_locales"]){
        $query .= " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
    }
    $locals_query = $mysqli->query($query);
    
    $locals = array();

    // Retrieve queried locals and their config to $locals[$id]["caja_config"]
    while ($local = $locals_query->fetch_assoc()) {
        $local["caja_config"] = array();
        $local_caja_config_command = "SELECT campo, valor FROM tbl_local_caja_config WHERE local_id = '" . $local["id"] . "' AND estado = '1'";
        $local_caja_config_query = $mysqli->query($local_caja_config_command);
        if ($mysqli->error) {
            print_r($mysqli->error);
            exit();
        }
        while ($lcc = $local_caja_config_query->fetch_assoc()) {
            $local["caja_config"][$lcc["campo"]] = $lcc["valor"];
        }
        $locals[] = $local;
    }
    $content = array();
    $max_terminal_num = 15;
    foreach ($locals as $local) {
        $table = array();
        $table["datos_sistema"] = array();

        $caja_arr = array();
        $caja_command = "SELECT
            c.id AS caja_id,
            c.fecha_operacion,
            c.turno_id,
            c.observaciones,
            c.estado,
            u.usuario as username
            FROM tbl_caja c
            LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
            LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
            LEFT join tbl_usuarios u ON (u.id = c.usuario_id)
            WHERE c.id != 1
            AND l.id = '" . $local["id"] . "'
            AND c.fecha_operacion >= '" . $fecha_inicio . "'
            AND c.fecha_operacion < '" . $fecha_fin . "'
            ORDER BY c.fecha_operacion ASC, c.turno_id ASC
            ";
        $caja_query = $mysqli->query($caja_command);
        if ($mysqli->error) {
            print_r($mysqli->error);
            exit();
        }
        $caja_data = array();

        while ($c = $caja_query->fetch_assoc()) {
            $c["datos_sistema"] = array();
            $ds_command = "SELECT
                       cd.id,
                       cdt.nombre,
                       IFNULL(cd.ingreso,0) AS ingreso,
                       IFNULL(cd.salida,0) AS salida,
                       CAST((IFNULL(cd.ingreso,0) - IFNULL(cd.salida,0)) AS DECIMAL(20,2)) AS resultado,
                       lcdt.detalle_tipos_id
                       FROM tbl_local_caja_detalle_tipos lcdt
                       LEFT JOIN tbl_caja_detalle cd ON (cd.tipo_id = lcdt.id AND cd.caja_id = '" . $c["caja_id"] . "')
                       LEFT JOIN tbl_caja_detalle_tipos cdt ON (cdt.id = lcdt.detalle_tipos_id)
                       WHERE lcdt.local_id = '" . $local["id"] . "'
                       ORDER BY lcdt.orden, cdt.ord ASC, lcdt.nombre ASC";
            $ds_query = $mysqli->query($ds_command);
            if ($mysqli->error) {
                print_r($mysqli->error);
                exit();
            }
            $array_tipos_id = array();
            while ($ds_db = $ds_query->fetch_assoc()) {
                $c["datos_sistema"][$ds_db["detalle_tipos_id"]][] = $ds_db;
                $array_tipos_id[$ds_db["detalle_tipos_id"]] = $ds_db["detalle_tipos_id"];
            }

            $c["datos_fisicos"] = array();
            $df_command = "SELECT
                       df.tipo_id, IFNULL(df.valor,0) AS valor
                       FROM tbl_caja_datos_fisicos df
                       WHERE df.caja_id = '" . $c["caja_id"] . "'";
            $df_query = $mysqli->query($df_command);
            if ($mysqli->error) {
                print_r($mysqli->error);
                exit();
            }
            while ($df_db = $df_query->fetch_assoc()) {
                $c["datos_fisicos"][$df_db["tipo_id"]] = $df_db;
            }
            $caja_data[] = $c;
        }
        $result = array();
        if (count($caja_data)) {
            $num_terminals = 0;
            $table["datos_sistema"]["cols"] = array();
            foreach ($caja_data as $ck => $c) {
                $new_num_terminals = 0;
                foreach ($c["datos_sistema"] as $detalle_tipos_id => $ds) {
                    if ($detalle_tipos_id == 4) {
                        foreach ($ds as $key => $value) {
                            $new_num_terminals++;
                        }
                    }
                    $table["datos_sistema"]["cols"][$detalle_tipos_id] = $ds[0];
                }
                if ($new_num_terminals > $num_terminals) {
                    $num_terminals = $new_num_terminals;
                }
                if ($max_terminal_num < $num_terminals)
                    $max_terminal_num = $num_terminals;
            }
            $table["datos_sistema"]["col_num"] = count($table["datos_sistema"]["cols"]);
            $table["datos_sistema"]["num_terminals"] = $num_terminals;
            $table["datos_sistema"]["colspan"] = 1;
            foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
                if ($ds_k == 4) {
                    $table["datos_sistema"]["colspan"] += ($table["datos_sistema"]["num_terminals"] + 1);
                } else {
                    $table["datos_sistema"]["colspan"] += 3;
                }
            }
            $report = array();
            $report["apertura"] = false;
            $report["efectivo_fisico"] = 0;
            $table["tbody"] = array();
            $no_data = "-";
            foreach ($caja_data as $data_id => $data) {
                $tr = array();
                $tr["cc_id"] = $local["cc_id"];
                $tr["local_nombre"] = $local["nombre"];
                $tr["ano"] = substr($data["fecha_operacion"], 0, 4);
                $tr["mes"] = substr($data["fecha_operacion"], 5, 2);
                $tr["dia"] = substr($data["fecha_operacion"], 8, 2);
                $tr["turno_id"] = $data["turno_id"];
                $tr["apertura"] = (array_key_exists(1, $data["datos_fisicos"]) ? $data["datos_fisicos"][1]["valor"] : 0);



                foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
                    if (array_key_exists($ds_k, $data["datos_sistema"])) {
                        if ($ds_k == 4) {
                            $t_sum = 0;
                            foreach ($data["datos_sistema"][$ds_k] as $key => $value) {
                                $tr["ds_" . $ds_k . "_t_" . $key . "_" . "_in"] = $value["ingreso"];
                                $t_sum += $value["ingreso"];
                            }
                            $tr["ds_" . $ds_k . "_res"] = $t_sum;
                        } else {
                            $ds = $data["datos_sistema"][$ds_k][0];
                            $tr["ds_" . $ds_k . "_in"] = $ds["ingreso"];
                            $tr["ds_" . $ds_k . "_out"] = $ds["salida"];
                            $tr["ds_" . $ds_k . "_res"] = $ds["resultado"];
                        }
                    } else {
                        if ($ds_k == 4) {
                            for ($t = 1; $t <= $table["datos_sistema"]["num_terminals"]; $t++) {
                                $tr["ds_" . $ds_k . "_t_" . $t . "_" . "_in"] = $no_data;
                            }
                            $tr["ds_" . $ds_k . "_res"] = $no_data;
                        } else {
                            $tr["ds_" . $ds_k . "_in"] = $no_data;
                            $tr["ds_" . $ds_k . "_out"] = $no_data;
                            $tr["ds_" . $ds_k . "_res"] = $no_data;
                        }
                    }
                }

                $tr["resultado"] = "" . (array_key_exists(5, $data["datos_fisicos"]) ? $data["datos_fisicos"][5]["valor"] : "0.00");
                $tr["devoluciones"] = "" . (array_key_exists(8, $data["datos_fisicos"]) ? $data["datos_fisicos"][8]["valor"] : "0.00");
                $tr["ticket_tito_slots"] = "" . (array_key_exists(23, $data["datos_fisicos"]) ? $data["datos_fisicos"][23]["valor"] : "0.00");
                $tr["bonos_promociones"] = "" . (array_key_exists(24, $data["datos_fisicos"]) ? $data["datos_fisicos"][24]["valor"] : "0.00");
                $tr["pagos_manuales"] = "" . (array_key_exists(9, $data["datos_fisicos"]) ? $data["datos_fisicos"][9]["valor"] : "0.00");
                $tr["resultado_real"] = $tr["resultado"] - $tr["pagos_manuales"] - $tr["devoluciones"];
                $tr["visa"] = "" . (array_key_exists(6, $data["datos_fisicos"]) ? $data["datos_fisicos"][6]["valor"] : "0.00");
                $tr["mastercard"] = "" . (array_key_exists(7, $data["datos_fisicos"]) ? $data["datos_fisicos"][7]["valor"] : "0.00");

                // agregar columnas nuevas
                $tr["deposito_cliente_directo"] = "" . (array_key_exists(21, $data["datos_fisicos"]) ? $data["datos_fisicos"][21]["valor"] : "0.00");
                $tr["tarjeta_de_credito"] = "" . (array_key_exists(22, $data["datos_fisicos"]) ? $data["datos_fisicos"][22]["valor"] : "0.00");

                $tr["aumento_fondo"] = "" . (array_key_exists(18, $data["datos_fisicos"]) ? $data["datos_fisicos"][18]["valor"] : "0.00");
                $tr["reduccion_fondo"] = "" . (array_key_exists(19, $data["datos_fisicos"]) ? $data["datos_fisicos"][19]["valor"] : "0.00");

                $tr["prestamo_slot"] = "" . (array_key_exists(12, $data["datos_fisicos"]) ? $data["datos_fisicos"][12]["valor"] : "0.00");
                $tr["prestamo_boveda"] = "" . (array_key_exists(2, $data["datos_fisicos"]) ? $data["datos_fisicos"][2]["valor"] : "0.00");
                $tr["devolucion_slot"] = "" . (array_key_exists(13, $data["datos_fisicos"]) ? $data["datos_fisicos"][13]["valor"] : "0.00");
                $tr["devolucion_boveda"] = "" . (array_key_exists(3, $data["datos_fisicos"]) ? $data["datos_fisicos"][3]["valor"] : "0.00");
                $tr["deposito_venta"] = "" . (array_key_exists(4, $data["datos_fisicos"]) ? $data["datos_fisicos"][4]["valor"] : "0.00");

                $tr["deuda_slot"] = "" . (array_key_exists(16, $data["datos_fisicos"]) ? $data["datos_fisicos"][16]["valor"] : "0.00");
                $tr["deuda_boveda"] = "" . (array_key_exists(17, $data["datos_fisicos"]) ? $data["datos_fisicos"][17]["valor"] : "0.00");
                $tr["fondo_fijo"] = "" . (array_key_exists(14, $data["datos_fisicos"]) ? $data["datos_fisicos"][14]["valor"] : (array_key_exists("monto_inicial", $local["caja_config"]) ? $local["caja_config"]["monto_inicial"] : 0));
                $tr["saldo_kasnet"] = "" . (array_key_exists(20, $data["datos_fisicos"]) ? $data["datos_fisicos"][20]["valor"] : "0.00");
                $tr["valla"] = "" . (array_key_exists(15, $data["datos_fisicos"]) ? $data["datos_fisicos"][15]["valor"] : (array_key_exists("valla_deposito", $local["caja_config"]) ? $local["caja_config"]["valla_deposito"] : "0.00"));

                $efectivo_fisico = (array_key_exists(11, $data["datos_fisicos"]) ? $data["datos_fisicos"][11]["valor"] : "0.00");
                $deposito = ($efectivo_fisico > $tr["fondo_fijo"] ? $efectivo_fisico - $tr["fondo_fijo"] : 0);
                $tr["deposito"] = round($deposito, 2);
                $tr["accion"] = (($tr["apertura"] - $tr["fondo_fijo"]) > 0 ? "" : "No ") . "Depositar";
                $tr["efectivo_sistema"] = "" . (array_key_exists(10, $data["datos_fisicos"]) ? $data["datos_fisicos"][10]["valor"] : "0.00");
                $tr["efectivo_fisico"] = "" . round($efectivo_fisico, 2);
                $diff = ($tr["efectivo_fisico"] - $tr["efectivo_sistema"]);
                $tr["efectivo_sobrante"] = round($diff, 2);
                $tr["observaciones"] = $data["observaciones"];
                $tr["estado"] = ($data["estado"] == 1 ? "Cerrado" : "Abierto");
                $tr["username"] = $data["username"];



                $table["tbody"][] = $tr;

                if ($report["apertura"] === false) {
                    $report["apertura"] = $tr["apertura"];
                }
                $report["efectivo_fisico"] = $tr["efectivo_fisico"];
            }
            $table_total = array();
            $table_total_ignore = array();
            $table_total_ignore[] = "local_nombre";
            $table_total_ignore[] = "ano";
            $table_total_ignore[] = "mes";
            $table_total_ignore[] = "dia";
            $table_total_ignore[] = "turno_id";
            $table_total_ignore[] = "apertura";
            $table_total_ignore[] = "observaciones";
            $table_total_ignore[] = "accion";
            $table_total_ignore[] = "fondo_fijo";
            $table_total_ignore[] = "valla";
            $table_total_ignore[] = "deposito";
            $table_total_ignore[] = "efectivo_sistema";
            $table_total_ignore[] = "efectivo_fisico";
            $table_total_ignore[] = "deuda_slot";
            $table_total_ignore[] = "deuda_boveda";
            $table_total_ignore[] = "saldo_kasnet";
            $table_total_ignore[] = "estado";
            $table_total_ignore[] = "username";
            $table_total_ignore[] = "caja_id";
            foreach ($table["tbody"] as $tr_k => $tr_v) {
                foreach ($tr_v as $key => $value) {
                    if (in_array($key, $table_total_ignore)) {
                        if (in_array($key, array("turno_id", "dia", "mes", "ano", "apertura", "accion", "fondo_fijo", "valla", "observaciones", "fondo_fijo", "valla", "deposito", "efectivo_sistema", "efectivo_fisico", "deuda_slot", "deuda_boveda", "saldo_kasnet"))) {
                            $value = "";
                        }
                        if ($key != "caja_id") {
                            $table_total[$key] = $value;
                        }
                    } else {
                        if (array_key_exists($key, $table_total)) {
                            $table_total[$key] += $value;
                        } else {
                            $table_total[$key] = $value;
                        }
                    }
                }
            }
            $table["total"] = $table_total;
            $resumen = array();
            $resumen["apertura"] = ($report["apertura"] === false ? 0 : $report["apertura"]);
            $resumen["resultado"] = $table_total["resultado"];
            $resumen["depositos"] = ($table_total["deposito_venta"]);
            $resumen["tarjetas"] = ($table_total["visa"] + $table_total["mastercard"]);
            $resumen["devo_manuales"] = ($table_total["devoluciones"] + $table_total["pagos_manuales"]);
            $resumen["sobra_falta"] = $table_total["efectivo_sobrante"];
            $resumen["efectivo_fisico"] = ($report["efectivo_fisico"] === false ? 0 : $report["efectivo_fisico"]);
            $resumen["prestamo_slotboveda"] = ($table_total["prestamo_slot"] + $table_total["prestamo_boveda"]);
            $resumen["devolucion_slotboveda"] = ($table_total["devolucion_slot"] + $table_total["devolucion_boveda"]);
            $resumen["diff_real"] = ($resumen["apertura"]
                + $resumen["resultado"]
                - $resumen["depositos"]
                - $resumen["tarjetas"]
                - $resumen["devo_manuales"]
                + $resumen["sobra_falta"]
                - $resumen["efectivo_fisico"]
                + $resumen["prestamo_slotboveda"]
                - $resumen["devolucion_slotboveda"]
            );

            if ($get_data["group_by"] == "day") {
                $ds_id_res_total_x_dia_in = array();
                $ds_id_res_total_x_dia_out = array();
                $ds_id_res_total_x_dia_res = array();
                $visa_total_x_dia = array();
                $mastercard_total_x_dia = array();

                //agregar columnas nuevas
                $deposito_cliente_directo_x_dia = array();
                $tarjeta_de_credito_x_dia = array();
                $aumento_fondo_x_dia = array();
                $reduccion_fondo_x_dia = array();

                $devoluciones_total_x_dia = array();
                $ticket_tito_slots_total_x_dia = array();
                $bonos_promociones_total_x_dia = array();
                $pagos_manuales_total_x_dia = array();
                $resultado_real_total_x_dia = array();
                $prestamo_slot_total_x_dia = array();
                $prestamo_boveda_total_x_dia = array();
                $devolucion_slot_total_x_dia = array();
                $devolucion_boveda_total_x_dia = array();
                $deposito_venta_total_x_dia = array();
                $deuda_slot_total_x_dia = array();
                $deuda_boveda_total_x_dia = array();
                $fondo_fijo_total_x_dia = array();
                $saldo_kasnet_total_x_dia = array();
                $valla_total_x_dia = array();
                $deposito_total_x_dia = array();
                $accion_total_x_dia = array();
                $efectivo_sistema_total_x_dia = array();
                $efectivo_fisico_total_x_dia = array();
                $efectivo_sobrante_total_x_dia = array();
                $table_x_day = array();
                $apertura_x_day = array();
                $resultado_x_day = array();
                $ds_4_total_x_dia = array();

                foreach ($table["tbody"] as $key => $value) {
                    $apertura_x_day[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["apertura"];
                    $resultado_x_day[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["resultado"];

                    foreach ($array_tipos_id as $id => $val_tipos_id) {
                        if ($id == 4) {
                            for ($ds4 = 0; $ds4 < $table["datos_sistema"]["num_terminals"]; $ds4++) {
                                if (isset($value["ds_" . $id . "_t_" . $ds4 . "__in"])) {
                                    $ds_4_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]]["ds_" . $id . "_t_" . $ds4 . "__in"][] = $value["ds_" . $id . "_t_" . $ds4 . "__in"];
                                } else {
                                    $ds_4_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]]["ds_" . $id . "_t_" . $ds4 . "__in"][] = "-";
                                }
                            }
                            if (isset($value["ds_" . $id . "_res"])) {
                                $ds_4_res_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]]["ds_" . $id . "_res"][] = $value["ds_" . $id . "_res"];
                            } else {
                                $ds_4_res_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]]["ds_" . $id . "_res"][] = "-";
                            }
                        } else {

                            if (isset($value["ds_" . $id . "_in"])) {
                                $ds_id_res_total_x_dia_in[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][$id][] = $value["ds_" . $id . "_in"];
                            } else {
                                $ds_id_res_total_x_dia_in[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][$id][] = "-";
                            }
                            if (isset($value["ds_" . $id . "_out"])) {
                                $ds_id_res_total_x_dia_out[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][$id][] = $value["ds_" . $id . "_out"];
                            } else {
                                $ds_id_res_total_x_dia_out[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][$id][] = "-";
                            }
                            if (isset($value["ds_" . $id . "_res"])) {
                                $ds_id_res_total_x_dia_res[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][$id][] = $value["ds_" . $id . "_res"];
                            } else {
                                $ds_id_res_total_x_dia_res[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][$id][] = "-";
                            }
                        }
                    }

                    if (isset($value["visa"])) {
                        $visa_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["visa"];
                    } else {
                        $visa_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["mastercard"])) {
                        $mastercard_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["mastercard"];
                    } else {
                        $mastercard_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }

                    //agregar columnas nuevas
                    if (isset($value["deposito_cliente_directo"])) {
                        $deposito_cliente_directo_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["deposito_cliente_directo"];
                    } else {
                        $deposito_cliente_directo_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }

                    //agregar columnas nuevas
                    if (isset($value["tarjeta_de_credito"])) {
                        $tarjeta_de_credito_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["tarjeta_de_credito"];
                    } else {
                        $tarjeta_de_credito_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }

                    //agregar columnas nuevas
                    if (isset($value["aumento_fondo"])) {
                        $aumento_fondo_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["aumento_fondo"];
                    } else {
                        $aumento_fondo_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }

                    //agregar columnas nuevas
                    if (isset($value["reduccion_fondo"])) {
                        $reduccion_fondo_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["reduccion_fondo"];
                    } else {
                        $reduccion_fondo_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }


                    if (isset($value["devoluciones"])) {
                        $devoluciones_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["devoluciones"];
                    } else {
                        $devoluciones_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }

                    if (isset($value["ticket_tito_slots"])) {
                        $ticket_tito_slots_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["ticket_tito_slots"];
                    } else {
                        $ticket_tito_slots_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }

                    if (isset($value["bonos_promociones"])) {
                        $bonos_promociones_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["bonos_promociones"];
                    } else {
                        $bonos_promociones_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }

                    if (isset($value["pagos_manuales"])) {
                        $pagos_manuales_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["pagos_manuales"];
                    } else {
                        $pagos_manuales_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["resultado_real"])) {
                        $resultado_real_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["resultado_real"];
                    } else {
                        $resultado_real_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["prestamo_slot"])) {
                        $prestamo_slot_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["prestamo_slot"];
                    } else {
                        $prestamo_slot_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["prestamo_boveda"])) {
                        $prestamo_boveda_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["prestamo_boveda"];
                    } else {
                        $prestamo_boveda_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["devolucion_slot"])) {
                        $devolucion_slot_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["devolucion_slot"];
                    } else {
                        $devolucion_slot_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["devolucion_boveda"])) {
                        $devolucion_boveda_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["devolucion_boveda"];
                    } else {
                        $devolucion_boveda_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["deposito_venta"])) {
                        $deposito_venta_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["deposito_venta"];
                    } else {
                        $deposito_venta_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["deuda_slot"])) {
                        $deuda_slot_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["deuda_slot"];
                    } else {
                        $deuda_slot_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["deuda_boveda"])) {
                        $deuda_boveda_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["deuda_boveda"];
                    } else {
                        $deuda_boveda_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["fondo_fijo"])) {
                        $fondo_fijo_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["fondo_fijo"];
                    } else {
                        $fondo_fijo_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["saldo_kasnet"])) {
                        $saldo_kasnet_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["saldo_kasnet"];
                    } else {
                        $saldo_kasnet_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["valla"])) {
                        $valla_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["valla"];
                    } else {
                        $valla_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["deposito"])) {
                        $deposito_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["deposito"];
                    } else {
                        $deposito_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["accion"])) {
                        $accion_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["accion"];
                    } else {
                        $accion_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["efectivo_sistema"])) {
                        $efectivo_sistema_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["efectivo_sistema"];
                    } else {
                        $efectivo_sistema_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["efectivo_fisico"])) {
                        $efectivo_fisico_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["efectivo_fisico"];
                    } else {
                        $efectivo_fisico_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                    if (isset($value["efectivo_sobrante"])) {
                        $efectivo_sobrante_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = $value["efectivo_sobrante"];
                    } else {
                        $efectivo_sobrante_total_x_dia[$value["ano"] . "" . $value["mes"] . "" . $value["dia"]][] = "-";
                    }
                }
                foreach ($table["tbody"] as $num => $val) {
                    $array = array();
                    $array["cc_id"] = $local["cc_id"];
                    $array["local_nombre"] = $val["local_nombre"];
                    $array["ano"] = $val["ano"];
                    $array["mes"] = $val["mes"];
                    $array["dia"] = $val["dia"];
                    $array["turno_id"] = "";
                    if (isset($apertura_x_day[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["apertura"] = current($apertura_x_day[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["apertura"] = "-";
                    }

                    foreach ($array_tipos_id as $id => $val_tipos_id) {
                        if ($id == 4) {
                            for ($i = 0; $i < $table["datos_sistema"]["num_terminals"]; $i++) {
                                if (isset($ds_4_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]["ds_" . $id . "_t_" . $i . "__in"])) {
                                    $array["ds_" . $id . "_t_" . $i . "__in"] = array_sum($ds_4_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]["ds_" . $id . "_t_" . $i . "__in"]);
                                } else {
                                    $array["ds_" . $i . "_t_" . $i . "__in"] = "-";
                                }
                            }
                            if (isset($ds_4_res_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]["ds_" . $id . "_res"])) {
                                $array["ds_" . $id . "_res"] = array_sum($ds_4_res_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]["ds_" . $id . "_res"]);
                            } else {
                                $array["ds_" . $id . "_res"] = "-";
                            }
                        } else {

                            if (isset($ds_id_res_total_x_dia_in[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]][$id])) {
                                $array["ds_" . $id . "_in"] = array_sum($ds_id_res_total_x_dia_in[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]][$id]);
                            } else {
                                $array["ds_" . $id . "_in"] = "-";
                            }
                            if (isset($ds_id_res_total_x_dia_out[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]][$id])) {
                                $array["ds_" . $id . "_out"] = array_sum($ds_id_res_total_x_dia_out[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]][$id]);
                            } else {
                                $array["ds_" . $id . "_out"] = "-";
                            }
                            if (isset($ds_id_res_total_x_dia_res[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]][$id])) {
                                $array["ds_" . $id . "_res"] = array_sum($ds_id_res_total_x_dia_res[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]][$id]);
                            } else {
                                $array["ds_" . $id . "_res"] = "-";
                            }
                        }
                    }


                    if (isset($resultado_x_day[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["resultado"] = array_sum($resultado_x_day[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["resultado"] = "-";
                    }
                    if (isset($devoluciones_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["devoluciones"] = array_sum($devoluciones_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["devoluciones"] = "-";
                    }

                    if (isset($ticket_tito_slots_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["ticket_tito_slots"] = array_sum($ticket_tito_slots_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["ticket_tito_slots"] = "-";
                    }

                    if (isset($bonos_promociones_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["bonos_promociones"] = array_sum($bonos_promociones_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["bonos_promociones"] = "-";
                    }

                    if (isset($pagos_manuales_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["pagos_manuales"] = array_sum($pagos_manuales_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["pagos_manuales"] = "-";
                    }

                    if (isset($resultado_real_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["resultado_real"] = array_sum($resultado_real_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["resultado_real"] = "-";
                    }

                    if (isset($visa_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["visa"] = array_sum($visa_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["visa"] = "-";
                    }

                    if (isset($mastercard_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["mastercard"] = array_sum($mastercard_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["mastercard"] = "-";
                    }


                    //agregar columnas nuevas
                    if (isset($deposito_cliente_directo_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["deposito_cliente_directo"] = array_sum($deposito_cliente_directo_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["deposito_cliente_directo"] = "-";
                    }

                    //agregar columnas nuevas
                    if (isset($tarjeta_de_credito_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["tarjeta_de_credito"] = array_sum($tarjeta_de_credito_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["tarjeta_de_credito"] = "-";
                    }

                    //agregar columnas nuevas
                    if (isset($aumento_fondo_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["aumento_fondo"] = array_sum($aumento_fondo_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["aumento_fondo"] = "-";
                    }

                    //agregar columnas nuevas
                    if (isset($reduccion_fondo_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["reduccion_fondo"] = array_sum($reduccion_fondo_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["reduccion_fondo"] = "-";
                    }


                    if (isset($prestamo_slot_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["prestamo_slot"] = array_sum($prestamo_slot_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["prestamo_slot"] = "-";
                    }
                    if (isset($prestamo_boveda_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["prestamo_boveda"] = end($prestamo_boveda_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["prestamo_boveda"] = "-";
                    }
                    if (isset($devolucion_slot_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["devolucion_slot"] = array_sum($devolucion_slot_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["devolucion_slot"] = "-";
                    }
                    if (isset($devolucion_boveda_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["devolucion_boveda"] = array_sum($devolucion_boveda_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["devolucion_boveda"] = "-";
                    }
                    if (isset($deposito_venta_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["deposito_venta"] = array_sum($deposito_venta_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["deposito_venta"] = "-";
                    }
                    if (isset($deuda_slot_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["deuda_slot"] = end($deuda_slot_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["deuda_slot"] = "-";
                    }
                    if (isset($deuda_boveda_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["deuda_boveda"] = end($deuda_boveda_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["deuda_boveda"] = "-";
                    }
                    if (isset($fondo_fijo_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["fondo_fijo"] = end($fondo_fijo_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["fondo_fijo"] = "-";
                    }
                    if (isset($saldo_kasnet_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["saldo_kasnet"] = end($saldo_kasnet_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["saldo_kasnet"] = "-";
                    }
                    if (isset($valla_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["valla"] = end($valla_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["valla"] = "-";
                    }
                    if (isset($deposito_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["deposito"] = end($deposito_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["deposito"] = "-";
                    }
                    $array["accion"] = "";
                    if (isset($efectivo_sistema_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["efectivo_sistema"] = end($efectivo_sistema_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["efectivo_sistema"] = "-";
                    }
                    if (isset($efectivo_fisico_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["efectivo_fisico"] = end($efectivo_fisico_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["efectivo_fisico"] = "-";
                    }
                    if (isset($efectivo_sobrante_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]])) {
                        $array["efectivo_sobrante"] = array_sum($efectivo_sobrante_total_x_dia[$val["ano"] . "" . $val["mes"] . "" . $val["dia"]]);
                    } else {
                        $array["efectivo_sobrante"] = "-";
                    }
                    $array["observaciones"] = "";
                    $array["estado"] = "";
                    $array["username"] = "";
                    $period = $val["ano"] . "" . $val["mes"] . "" . $val["dia"];
                    $table_x_day[(int)$period] = $array;
                }
                array_multisort($table_x_day);
                if (!empty($table_x_day)) $result["table_x_day"] = $table_x_day;
            }
        }

        if (!empty($table) && !empty($table["datos_sistema"])) $result["table"] = $table;
        if (!empty($caja_data)) $result["caja_data"] = $caja_data;
        if (!empty($result)) $content[] = $result;
    }

    if (count($content)) {

        $finalTable = array();

        $tr_final = array();
        $tr_final["cc_id"] = "Centro de Costo";
        $tr_final["local_nombre"] = "Local";
        $tr_final["ano"] = "Ano";
        $tr_final["mes"] = "Mes";
        $tr_final["dia"] = "Dia";
        $tr_final["turno_id"] =  "Turno";
        $tr_final["apertura"] =  "Apertura Efectivo";
        $tr_final["ds_1_in"] = "AD Ingreso";
        $tr_final["ds_1_out"] = "AD Salida";
        $tr_final["ds_1_res"] = "AD Resultado";
        $tr_final["ds_2_in"] = "Web Ingreso";
        $tr_final["ds_2_out"] = "Web Salida";
        $tr_final["ds_2_res"] = "Web Resultado";
        $tr_final["ds_3_in"] = "GR Ingreso";
        $tr_final["ds_3_out"] = "GR Salida";
        $tr_final["ds_3_res"] = "GR Resultado";
        for ($i = 0; $i < $max_terminal_num; $i++) {
            $tr_final["ds_4_t_" . $i . "__in"] = "Billetero " . ($i + 1);
        }
        $tr_final["ds_4_res"] =  "Resultado";
        $tr_final["ds_5_in"] = "InOut Ingreso";
        $tr_final["ds_5_out"] =  "InOut Salida";
        $tr_final["ds_5_res"] =  "InOut Resultado";
        $tr_final["ds_12_in"] =  "Polla Ingreso";
        $tr_final["ds_12_out"] = "Polla Salida";
        $tr_final["ds_12_res"] = "Polla Resultado";
        $tr_final["ds_13_in"] =  "Kasnet Ingreso";
        $tr_final["ds_13_out"] = "Kasnet Salida";
        $tr_final["ds_13_res"] = "Kasnet Resultado";
        /*disashop 21  tbl_caja_detalle_tipos*/
        $tr_final["ds_21_in"] =  "Disashop Ingreso";
        $tr_final["ds_21_res"] = "Disashop Resultado";

        $tr_final["ds_15_in"] =  "Bingo Ingreso";
        $tr_final["ds_15_out"] = "Bingo Salida";
        $tr_final["ds_15_res"] = "Bingo Resultado";
        $tr_final["ds_17_in"] =  "ATSnacks Ingreso";
        $tr_final["ds_17_out"] = "ATSnacks Salida";
        $tr_final["ds_17_res"] = "ATSnacks Resultado";
        $tr_final["ds_19_in"] =  "Torito Ingreso";
        $tr_final["ds_19_out"] = "Torito Salida";
        $tr_final["ds_19_res"] = "Torito Resultado";
        $tr_final["resultado"] = "Resultado Dia";
        $tr_final["devoluciones"] =  "Devoluciones";
        $tr_final["ticket_tito_slots"] =  "Ticket TITO/Slots";
        $tr_final["bonos_promociones"] =  "Bonos y Promociones";
        $tr_final["pagos_manuales"] =  "Pagos Manuales";
        $tr_final["resultado_real"] =  "Resultado Real";
        $tr_final["visa"] = "Visa";
        $tr_final["mastercard"] =  "Mastercard";

        //agregar columnas nuevas
        $tr_final["deposito_cliente_directo"] =  "Deposito cliente directo";
        $tr_final["tarjeta_de_credito"] =  "tarjeta de credito";
        $tr_final["aumento_fondo"] =  "Aumento fondo";
        $tr_final["reduccion_fondo"] =  "Reduccion fondo";

        $tr_final["prestamo_slot"] = "Prestamo Slot";
        $tr_final["prestamo_boveda"] = "Prestamo Boveda";
        $tr_final["devolucion_slot"] = "Deposito Slot";
        $tr_final["devolucion_boveda"] = "Deposito Boveda";
        $tr_final["deposito_venta"] =  "Deposito Venta";
        $tr_final["deuda_slot"] =  "Deuda Slot";
        $tr_final["deuda_boveda"] =  "Deuda Boveda";
        $tr_final["fondo_fijo"] =  "Fondo Fijo";
        $tr_final["saldo_kasnet"] =  "Saldo Kasnet";
        $tr_final["valla"] = "Minimo Deposito";
        $tr_final["deposito"] =  "Deposito";
        $tr_final["accion"] =  "Accion";
        $tr_final["efectivo_sistema"] =  "Efectivo Sistema";
        $tr_final["efectivo_fisico"] = "Efectivo Fisico";
        $tr_final["efectivo_sobrante"] = "Efectivo Sobrante";
        $tr_final["observaciones"] = "Observaciones";
        $tr_final["estado"] =  "Estado";
        $tr_final["username"] =  "Nombre Usuario";
      //  $finalTable[] = $tr_final;
        //var_dump($content);
        //die();

        if ($get_data["group_by"] == "day") {
            foreach ($content as $result) {
                foreach ($result["table_x_day"] as $tr) {
                    $tr_final = array();
                    $tr_final["cc_id"] = isset($tr["cc_id"]) ? $tr["cc_id"] : "";
                    $tr_final["local_nombre"] = isset($tr["local_nombre"]) ? $tr["local_nombre"] : "";
                    $tr_final["ano"] = isset($tr["ano"]) ? $tr["ano"] : "";
                    $tr_final["mes"] = isset($tr["mes"]) ? $tr["mes"] : "";
                    $tr_final["dia"] = isset($tr["dia"]) ? $tr["dia"] : "";
                    $tr_final["turno_id"] = isset($tr["turno_id"]) ? $tr["turno_id"] : "";
                    $tr_final["apertura"] = isset($tr["apertura"]) ? $tr["apertura"] : "";
                    $tr_final["ds_1_in"] = isset($tr["ds_1_in"]) ? $tr["ds_1_in"] : "";
                    $tr_final["ds_1_out"] = isset($tr["ds_1_out"]) ? $tr["ds_1_out"] : "";
                    $tr_final["ds_1_res"] = isset($tr["ds_1_res"]) ? $tr["ds_1_res"] : "";
                    $tr_final["ds_2_in"] = isset($tr["ds_2_in"]) ? $tr["ds_2_in"] : "";
                    $tr_final["ds_2_out"] = isset($tr["ds_2_out"]) ? $tr["ds_2_out"] : "";
                    $tr_final["ds_2_res"] = isset($tr["ds_2_res"]) ? $tr["ds_2_res"] : "";
                    $tr_final["ds_3_in"] = isset($tr["ds_3_in"]) ? $tr["ds_3_in"] : "";
                    $tr_final["ds_3_out"] = isset($tr["ds_3_out"]) ? $tr["ds_3_out"] : "";
                    $tr_final["ds_3_res"] = isset($tr["ds_3_res"]) ? $tr["ds_3_res"] : "";
                    for ($i = 0; $i < $max_terminal_num; $i++)
                        $tr_final["ds_4_t_" . $i . "__in"] = isset($tr["ds_4_t_" . $i . "__in"]) ? $tr["ds_4_t_" . $i . "__in"] : "";
                    $tr_final["ds_4_res"] = isset($tr["ds_4_res"]) ? $tr["ds_4_res"] : "";
                    $tr_final["ds_5_in"] = isset($tr["ds_5_in"]) ? $tr["ds_5_in"] : "";
                    $tr_final["ds_5_out"] = isset($tr["ds_5_out"]) ? $tr["ds_5_out"] : "";
                    $tr_final["ds_5_res"] = isset($tr["ds_5_res"]) ? $tr["ds_5_res"] : "";
                    $tr_final["ds_12_in"] = isset($tr["ds_12_in"]) ? $tr["ds_12_in"] : "";
                    $tr_final["ds_12_out"] = isset($tr["ds_12_out"]) ? $tr["ds_12_out"] : "";
                    $tr_final["ds_12_res"] = isset($tr["ds_12_res"]) ? $tr["ds_12_res"] : "";
                    $tr_final["ds_13_in"] = isset($tr["ds_13_in"]) ? $tr["ds_13_in"] : "";
                    $tr_final["ds_13_out"] = isset($tr["ds_13_out"]) ? $tr["ds_13_out"] : "";
                    $tr_final["ds_13_res"] = isset($tr["ds_13_res"]) ? $tr["ds_13_res"] : "";
                    /*disashop 21  tbl_caja_detalle_tipos*/
                    $tr_final["ds_21_in"] = isset($tr["ds_21_in"]) ? $tr["ds_21_in"] : "";
                    $tr_final["ds_21_res"] = isset($tr["ds_21_res"]) ? $tr["ds_21_res"] : "";

                    $tr_final["ds_15_in"] = isset($tr["ds_15_in"]) ? $tr["ds_15_in"] : "";
                    $tr_final["ds_15_out"] = isset($tr["ds_15_out"]) ? $tr["ds_15_out"] : "";
                    $tr_final["ds_15_res"] = isset($tr["ds_15_res"]) ? $tr["ds_15_res"] : "";
                    $tr_final["ds_17_in"] = isset($tr["ds_17_in"]) ? $tr["ds_17_in"] : "";
                    $tr_final["ds_17_out"] = isset($tr["ds_17_out"]) ? $tr["ds_17_out"] : "";
                    $tr_final["ds_17_res"] = isset($tr["ds_17_res"]) ? $tr["ds_17_res"] : "";
                    $tr_final["ds_19_in"] = isset($tr["ds_19_in"]) ? $tr["ds_19_in"] : "";
                    $tr_final["ds_19_out"] = isset($tr["ds_19_out"]) ? $tr["ds_19_out"] : "";
                    $tr_final["ds_19_res"] = isset($tr["ds_19_res"]) ? $tr["ds_19_res"] : "";
                    $tr_final["resultado"] = isset($tr["resultado"]) ? $tr["resultado"] : "";
                    $tr_final["devoluciones"] = isset($tr["devoluciones"]) ? $tr["devoluciones"] : "";
                    $tr_final["ticket_tito_slots"] = isset($tr["ticket_tito_slots"]) ? $tr["ticket_tito_slots"] : "";
                    $tr_final["bonos_promociones"] = isset($tr["bonos_promociones"]) ? $tr["bonos_promociones"] : "";
                    $tr_final["pagos_manuales"] = isset($tr["pagos_manuales"]) ? $tr["pagos_manuales"] : "";
                    $tr_final["resultado_real"] = isset($tr["resultado_real"]) ? $tr["resultado_real"] : "";
                    $tr_final["visa"] = isset($tr["visa"]) ? $tr["visa"] : "";
                    $tr_final["mastercard"] = isset($tr["mastercard"]) ? $tr["mastercard"] : "";

                    //agregar columnas nuevas
                    $tr_final["deposito_cliente_directo"] = isset($tr["deposito_cliente_directo"]) ? $tr["deposito_cliente_directo"] : "";
                    $tr_final["tarjeta_de_credito"] = isset($tr["tarjeta_de_credito"]) ? $tr["tarjeta_de_credito"] : "";
                    $tr_final["aumento_fondo"] = isset($tr["aumento_fondo"]) ? $tr["aumento_fondo"] : "";
                    $tr_final["reduccion_fondo"] = isset($tr["reduccion_fondo"]) ? $tr["reduccion_fondo"] : "";

                    $tr_final["prestamo_slot"] = isset($tr["prestamo_slot"]) ? $tr["prestamo_slot"] : "";
                    $tr_final["prestamo_boveda"] = isset($tr["prestamo_boveda"]) ? $tr["prestamo_boveda"] : "";
                    $tr_final["devolucion_slot"] = isset($tr["devolucion_slot"]) ? $tr["devolucion_slot"] : "";
                    $tr_final["devolucion_boveda"] = isset($tr["devolucion_boveda"]) ? $tr["devolucion_boveda"] : "";
                    $tr_final["deposito_venta"] = isset($tr["deposito_venta"]) ? $tr["deposito_venta"] : "";
                    $tr_final["deuda_slot"] = isset($tr["deuda_slot"]) ? $tr["deuda_slot"] : "";
                    $tr_final["deuda_boveda"] = isset($tr["deuda_boveda"]) ? $tr["deuda_boveda"] : "";
                    $tr_final["fondo_fijo"] = isset($tr["fondo_fijo"]) ? $tr["fondo_fijo"] : "";
                    $tr_final["saldo_kasnet"] = isset($tr["saldo_kasnet"]) ? $tr["saldo_kasnet"] : "";
                    $tr_final["valla"] = isset($tr["valla"]) ? $tr["valla"] : "";
                    $tr_final["deposito"] = isset($tr["deposito"]) ? $tr["deposito"] : "";
                    $tr_final["accion"] = isset($tr["accion"]) ? $tr["accion"] : "";
                    $tr_final["efectivo_sistema"] = isset($tr["efectivo_sistema"]) ? $tr["efectivo_sistema"] : "";
                    $tr_final["efectivo_fisico"] = isset($tr["efectivo_fisico"]) ? $tr["efectivo_fisico"] : "";
                    $tr_final["efectivo_sobrante"] = isset($tr["efectivo_sobrante"]) ? $tr["efectivo_sobrante"] : "";
                    $tr_final["observaciones"] = str_replace(array(",", '"', "'", "`"), " ", isset($tr["observaciones"]) ? $tr["observaciones"] : "");
                    $tr_final["estado"] = isset($tr["estado"]) ? $tr["estado"] : "";
                    $tr_final["username"] = isset($tr["username"]) ? $tr["username"] : "";
                    $finalTable[] = $tr_final;
                }
            }
        }

        if ($get_data["group_by"] == "turno_id") {
            foreach ($content as $result) {
                foreach ($result["table"]["tbody"] as $tr) {
                    $tr_final = array();
                    $tr_final["cc_id"] = isset($tr["cc_id"]) ? $tr["cc_id"] : "";
                    $tr_final["local_nombre"] = isset($tr["local_nombre"]) ? $tr["local_nombre"] : "";
                    $tr_final["ano"] = isset($tr["ano"]) ? $tr["ano"] : "";
                    $tr_final["mes"] = isset($tr["mes"]) ? $tr["mes"] : "";
                    $tr_final["dia"] = isset($tr["dia"]) ? $tr["dia"] : "";
                    $tr_final["turno_id"] = isset($tr["turno_id"]) ? $tr["turno_id"] : "";
                    $tr_final["apertura"] = isset($tr["apertura"]) ? $tr["apertura"] : "";
                    $tr_final["ds_1_in"] = isset($tr["ds_1_in"]) ? $tr["ds_1_in"] : "";
                    $tr_final["ds_1_out"] = isset($tr["ds_1_out"]) ? $tr["ds_1_out"] : "";
                    $tr_final["ds_1_res"] = isset($tr["ds_1_res"]) ? $tr["ds_1_res"] : "";
                    $tr_final["ds_2_in"] = isset($tr["ds_2_in"]) ? $tr["ds_2_in"] : "";
                    $tr_final["ds_2_out"] = isset($tr["ds_2_out"]) ? $tr["ds_2_out"] : "";
                    $tr_final["ds_2_res"] = isset($tr["ds_2_res"]) ? $tr["ds_2_res"] : "";
                    $tr_final["ds_3_in"] = isset($tr["ds_3_in"]) ? $tr["ds_3_in"] : "";
                    $tr_final["ds_3_out"] = isset($tr["ds_3_out"]) ? $tr["ds_3_out"] : "";
                    $tr_final["ds_3_res"] = isset($tr["ds_3_res"]) ? $tr["ds_3_res"] : "";
                    for ($i = 0; $i < $max_terminal_num; $i++) {
                        $tr_final["ds_4_t_" . $i . "__in"] = isset($tr["ds_4_t_" . $i . "__in"]) ? $tr["ds_4_t_" . $i . "__in"] : "";
                    }
                    $tr_final["ds_4_res"] = isset($tr["ds_4_res"]) ? $tr["ds_4_res"] : "";
                    $tr_final["ds_5_in"] = isset($tr["ds_5_in"]) ? $tr["ds_5_in"] : "";
                    $tr_final["ds_5_out"] = isset($tr["ds_5_out"]) ? $tr["ds_5_out"] : "";
                    $tr_final["ds_5_res"] = isset($tr["ds_5_res"]) ? $tr["ds_5_res"] : "";
                    $tr_final["ds_12_in"] = isset($tr["ds_12_in"]) ? $tr["ds_12_in"] : "";
                    $tr_final["ds_12_out"] = isset($tr["ds_12_out"]) ? $tr["ds_12_out"] : "";
                    $tr_final["ds_12_res"] = isset($tr["ds_12_res"]) ? $tr["ds_12_res"] : "";
                    $tr_final["ds_13_in"] = isset($tr["ds_13_in"]) ? $tr["ds_13_in"] : "";
                    $tr_final["ds_13_out"] = isset($tr["ds_13_out"]) ? $tr["ds_13_out"] : "";
                    $tr_final["ds_13_res"] = isset($tr["ds_13_res"]) ? $tr["ds_13_res"] : "";
                    /*disashop 21  tbl_caja_detalle_tipos*/
                    $tr_final["ds_21_in"] = isset($tr["ds_21_in"]) ? $tr["ds_21_in"] : "";
                    $tr_final["ds_21_res"] = isset($tr["ds_21_res"]) ? $tr["ds_21_res"] : "";

                    $tr_final["ds_15_in"] = isset($tr["ds_15_in"]) ? $tr["ds_15_in"] : "";
                    $tr_final["ds_15_out"] = isset($tr["ds_15_out"]) ? $tr["ds_15_out"] : "";
                    $tr_final["ds_15_res"] = isset($tr["ds_15_res"]) ? $tr["ds_15_res"] : "";
                    $tr_final["ds_17_in"] = isset($tr["ds_17_in"]) ? $tr["ds_17_in"] : "";
                    $tr_final["ds_17_out"] = isset($tr["ds_17_out"]) ? $tr["ds_17_out"] : "";
                    $tr_final["ds_17_res"] = isset($tr["ds_17_res"]) ? $tr["ds_17_res"] : "";
                    $tr_final["ds_19_in"] = isset($tr["ds_19_in"]) ? $tr["ds_19_in"] : "";
                    $tr_final["ds_19_out"] = isset($tr["ds_19_out"]) ? $tr["ds_19_out"] : "";
                    $tr_final["ds_19_res"] = isset($tr["ds_19_res"]) ? $tr["ds_19_res"] : "";
                    $tr_final["resultado"] = isset($tr["resultado"]) ? $tr["resultado"] : "";
                    $tr_final["devoluciones"] = isset($tr["devoluciones"]) ? $tr["devoluciones"] : "";
                    $tr_final["ticket_tito_slots"] = isset($tr["ticket_tito_slots"]) ? $tr["ticket_tito_slots"] : "";
                    $tr_final["bonos_promociones"] = isset($tr["bonos_promociones"]) ? $tr["bonos_promociones"] : "";
                    $tr_final["pagos_manuales"] = isset($tr["pagos_manuales"]) ? $tr["pagos_manuales"] : "";
                    $tr_final["resultado_real"] = isset($tr["resultado_real"]) ? $tr["resultado_real"] : "";
                    $tr_final["visa"] = isset($tr["visa"]) ? $tr["visa"] : "";
                    $tr_final["mastercard"] = isset($tr["mastercard"]) ? $tr["mastercard"] : "";

                    //agregar columnas nuevas
                    $tr_final["deposito_cliente_directo"] = isset($tr["deposito_cliente_directo"]) ? $tr["deposito_cliente_directo"] : "";
                    $tr_final["tarjeta_de_credito"] = isset($tr["tarjeta_de_credito"]) ? $tr["tarjeta_de_credito"] : "";
                    $tr_final["aumento_fondo"] = isset($tr["aumento_fondo"]) ? $tr["aumento_fondo"] : "";
                    $tr_final["reduccion_fondo"] = isset($tr["reduccion_fondo"]) ? $tr["reduccion_fondo"] : "";

                    $tr_final["prestamo_slot"] = isset($tr["prestamo_slot"]) ? $tr["prestamo_slot"] : "";
                    $tr_final["prestamo_boveda"] = isset($tr["prestamo_boveda"]) ? $tr["prestamo_boveda"] : "";
                    $tr_final["devolucion_slot"] = isset($tr["devolucion_slot"]) ? $tr["devolucion_slot"] : "";
                    $tr_final["devolucion_boveda"] = isset($tr["devolucion_boveda"]) ? $tr["devolucion_boveda"] : "";
                    $tr_final["deposito_venta"] = isset($tr["deposito_venta"]) ? $tr["deposito_venta"] : "";
                    $tr_final["deuda_slot"] = isset($tr["deuda_slot"]) ? $tr["deuda_slot"] : "";
                    $tr_final["deuda_boveda"] = isset($tr["deuda_boveda"]) ? $tr["deuda_boveda"] : "";
                    $tr_final["fondo_fijo"] = isset($tr["fondo_fijo"]) ? $tr["fondo_fijo"] : "";
                    $tr_final["saldo_kasnet"] = isset($tr["saldo_kasnet"]) ? $tr["saldo_kasnet"] : "";
                    $tr_final["valla"] = isset($tr["valla"]) ? $tr["valla"] : "";
                    $tr_final["deposito"] = isset($tr["deposito"]) ? $tr["deposito"] : "";
                    $tr_final["accion"] = isset($tr["accion"]) ? $tr["accion"] : "";
                    $tr_final["efectivo_sistema"] = isset($tr["efectivo_sistema"]) ? $tr["efectivo_sistema"] : "";
                    $tr_final["efectivo_fisico"] = isset($tr["efectivo_fisico"]) ? $tr["efectivo_fisico"] : "";
                    $tr_final["efectivo_sobrante"] = isset($tr["efectivo_sobrante"]) ? $tr["efectivo_sobrante"] : "";
                    $tr_final["observaciones"] = str_replace(array(",", '"', "'", "`"), " ", isset($tr["observaciones"]) ? $tr["observaciones"] : "");
                    $tr_final["estado"] = isset($tr["estado"]) ? $tr["estado"] : "";
                    $tr_final["username"] = isset($tr["username"]) ? $tr["username"] : "";

                    $finalTable[] = $tr_final;
                }
            }
        }

        $tr_final = array();
        $tr_final["cc_id"] = "";
        $tr_final["local_nombre"] = "Suma Total";
        $tr_final["ano"] = "";
        $tr_final["mes"] = "";
        $tr_final["dia"] = "";
        $tr_final["turno_id"] =  "";
        $tr_final["apertura"] =  "";
        $tr_final["ds_1_in"] = 0;
        $tr_final["ds_1_out"] = 0;
        $tr_final["ds_1_res"] = 0;
        $tr_final["ds_2_in"] = 0;
        $tr_final["ds_2_out"] = 0;
        $tr_final["ds_2_res"] = 0;
        $tr_final["ds_3_in"] = 0;
        $tr_final["ds_3_out"] = 0;
        $tr_final["ds_3_res"] = 0;
        for ($i = 0; $i < $max_terminal_num; $i++) {
            $tr_final["ds_4_t_" . $i . "__in"] = 0;
        }
        $tr_final["ds_4_res"] =  0;
        $tr_final["ds_5_in"] = 0;
        $tr_final["ds_5_out"] =  0;
        $tr_final["ds_5_res"] =  0;
        $tr_final["ds_12_in"] =  0;
        $tr_final["ds_12_out"] = 0;
        $tr_final["ds_12_res"] = 0;
        $tr_final["ds_13_in"] =  0;
        $tr_final["ds_13_out"] = 0;
        $tr_final["ds_13_res"] = 0;
        /*disashop 21  tbl_caja_detalle_tipos*/
        $tr_final["ds_21_in"] =  0;
        $tr_final["ds_21_res"] = 0;

        $tr_final["ds_15_in"] =  0;
        $tr_final["ds_15_out"] = 0;
        $tr_final["ds_15_res"] = 0;
        $tr_final["ds_17_in"] =  0;
        $tr_final["ds_17_out"] = 0;
        $tr_final["ds_17_res"] = 0;
        $tr_final["ds_19_in"] =  0;
        $tr_final["ds_19_out"] = 0;
        $tr_final["ds_19_res"] = 0;
        $tr_final["resultado"] = 0;
        $tr_final["devoluciones"] =  0;
        $tr_final["ticket_tito_slots"] =  0;
        $tr_final["bonos_promociones"] =  0;
        $tr_final["pagos_manuales"] =  0;
        $tr_final["resultado_real"] =  0;
        $tr_final["visa"] = 0;
        $tr_final["mastercard"] =  0;

        //agregar columnas nuevas
        $tr_final["deposito_cliente_directo"] =  0;
        $tr_final["tarjeta_de_credito"] =  0;
        $tr_final["aumento_fondo"] =  0;
        $tr_final["reduccion_fondo"] =  0;

        $tr_final["prestamo_slot"] = 0;
        $tr_final["prestamo_boveda"] = 0;
        $tr_final["devolucion_slot"] = 0;
        $tr_final["devolucion_boveda"] = 0;
        $tr_final["deposito_venta"] =  0;
        $tr_final["deuda_slot"] =  "";
        $tr_final["deuda_boveda"] =  "";
        $tr_final["fondo_fijo"] =  "";
        $tr_final["saldo_kasnet"] =  "";
        $tr_final["valla"] = "";
        $tr_final["deposito"] =  "";
        $tr_final["accion"] =  "";
        $tr_final["efectivo_sistema"] =  "";
        $tr_final["efectivo_fisico"] = "";
        $tr_final["efectivo_sobrante"] = 0;
        $tr_final["observaciones"] = "";
        $tr_final["estado"] =  "";
        $tr_final["username"] =  "";
        foreach ($content as $result) {
            $tr_final["ds_1_in"] += isset($result["table"]["total"]["ds_1_in"]) ? $result["table"]["total"]["ds_1_in"] : 0;
            $tr_final["ds_1_out"] += isset($result["table"]["total"]["ds_1_out"]) ? $result["table"]["total"]["ds_1_out"] : 0;
            $tr_final["ds_1_res"] += isset($result["table"]["total"]["ds_1_res"]) ? $result["table"]["total"]["ds_1_res"] : 0;
            $tr_final["ds_2_in"] += isset($result["table"]["total"]["ds_2_in"]) ? $result["table"]["total"]["ds_2_in"] : 0;
            $tr_final["ds_2_out"] += isset($result["table"]["total"]["ds_2_out"]) ? $result["table"]["total"]["ds_2_out"] : 0;
            $tr_final["ds_2_res"] += isset($result["table"]["total"]["ds_2_res"]) ? $result["table"]["total"]["ds_2_res"] : 0;
            $tr_final["ds_3_in"] += isset($result["table"]["total"]["ds_3_in"]) ? $result["table"]["total"]["ds_3_in"] : 0;
            $tr_final["ds_3_out"] += isset($result["table"]["total"]["ds_3_out"]) ? $result["table"]["total"]["ds_3_out"] : 0;
            $tr_final["ds_3_res"] += isset($result["table"]["total"]["ds_3_res"]) ? $result["table"]["total"]["ds_3_res"] : 0;
            for ($i = 0; $i < $max_terminal_num; $i++) {
                $tr_final["ds_4_t_" . $i . "__in"] += isset($result["table"]["total"]["ds_4_t_" . $i . "__in"]) ? $result["table"]["total"]["ds_4_t_" . $i . "__in"] : 0;
            }
            $tr_final["ds_4_res"] +=  isset($result["table"]["total"]["ds_4_res"]) ? $result["table"]["total"]["ds_4_res"] : 0;
            $tr_final["ds_5_in"] += isset($result["table"]["total"]["ds_5_in"]) ? $result["table"]["total"]["ds_5_in"] : 0;
            $tr_final["ds_5_out"] +=  isset($result["table"]["total"]["ds_5_out"]) ? $result["table"]["total"]["ds_5_out"] : 0;
            $tr_final["ds_5_res"] +=  isset($result["table"]["total"]["ds_5_res"]) ? $result["table"]["total"]["ds_5_res"] : 0;
            $tr_final["ds_12_in"] +=  isset($result["table"]["total"]["ds_12_in"]) ? $result["table"]["total"]["ds_12_in"] : 0;
            $tr_final["ds_12_out"] += isset($result["table"]["total"]["ds_12_out"]) ? $result["table"]["total"]["ds_12_out"] : 0;
            $tr_final["ds_12_res"] += isset($result["table"]["total"]["ds_12_res"]) ? $result["table"]["total"]["ds_12_res"] : 0;
            $tr_final["ds_13_in"] +=  isset($result["table"]["total"]["ds_13_in"]) ? $result["table"]["total"]["ds_13_in"] : 0;
            $tr_final["ds_13_out"] += isset($result["table"]["total"]["ds_13_out"]) ? $result["table"]["total"]["ds_13_out"] : 0;
            $tr_final["ds_13_res"] += isset($result["table"]["total"]["ds_13_res"]) ? $result["table"]["total"]["ds_13_res"] : 0;
            /*disashop 21  tbl_caja_detalle_tipos*/
            $tr_final["ds_21_in"] +=  isset($result["table"]["total"]["ds_21_in"]) ? $result["table"]["total"]["ds_21_in"] : 0;
            $tr_final["ds_21_res"] += isset($result["table"]["total"]["ds_21_res"]) ? $result["table"]["total"]["ds_21_res"] : 0;

            $tr_final["ds_15_in"] +=  isset($result["table"]["total"]["ds_15_in"]) ? $result["table"]["total"]["ds_15_in"] : 0;
            $tr_final["ds_15_out"] += isset($result["table"]["total"]["ds_15_out"]) ? $result["table"]["total"]["ds_15_out"] : 0;
            $tr_final["ds_15_res"] += isset($result["table"]["total"]["ds_15_res"]) ? $result["table"]["total"]["ds_15_res"] : 0;
            $tr_final["ds_17_in"] +=  isset($result["table"]["total"]["ds_17_in"]) ? $result["table"]["total"]["ds_17_in"] : 0;
            $tr_final["ds_17_out"] += isset($result["table"]["total"]["ds_17_out"]) ? $result["table"]["total"]["ds_17_out"] : 0;
            $tr_final["ds_17_res"] += isset($result["table"]["total"]["ds_17_res"]) ? $result["table"]["total"]["ds_17_res"] : 0;
            $tr_final["ds_19_in"] +=  isset($result["table"]["total"]["ds_19_in"]) ? $result["table"]["total"]["ds_19_in"] : 0;
            $tr_final["ds_19_out"] += isset($result["table"]["total"]["ds_19_out"]) ? $result["table"]["total"]["ds_19_out"] : 0;
            $tr_final["ds_19_res"] += isset($result["table"]["total"]["ds_19_res"]) ? $result["table"]["total"]["ds_19_res"] : 0;
            $tr_final["resultado"] += isset($result["table"]["total"]["resultado"]) ? $result["table"]["total"]["resultado"] : 0;
            $tr_final["devoluciones"] +=  isset($result["table"]["total"]["devoluciones"]) ? $result["table"]["total"]["devoluciones"] : 0;
            $tr_final["ticket_tito_slots"] +=  isset($result["table"]["total"]["ticket_tito_slots"]) ? $result["table"]["total"]["ticket_tito_slots"] : 0;
            $tr_final["bonos_promociones"] +=  isset($result["table"]["total"]["bonos_promociones"]) ? $result["table"]["total"]["bonos_promociones"] : 0;
            $tr_final["pagos_manuales"] +=  isset($result["table"]["total"]["pagos_manuales"]) ? $result["table"]["total"]["pagos_manuales"] : 0;
            $tr_final["resultado_real"] +=  isset($result["table"]["total"]["resultado_real"]) ? $result["table"]["total"]["resultado_real"] : 0;
            $tr_final["visa"] += isset($result["table"]["total"]["visa"]) ? $result["table"]["total"]["visa"] : 0;
            $tr_final["mastercard"] +=  isset($result["table"]["total"]["mastercard"]) ? $result["table"]["total"]["mastercard"] : 0;
            //agregar columnas nuevas
            $tr_final["deposito_cliente_directo"] +=  isset($result["table"]["total"]["deposito_cliente_directo"]) ? $result["table"]["total"]["deposito_cliente_directo"] : 0;
            $tr_final["tarjeta_de_credito"] +=  isset($result["table"]["total"]["tarjeta_de_credito"]) ? $result["table"]["total"]["tarjeta_de_credito"] : 0;
            $tr_final["aumento_fondo"] +=  isset($result["table"]["total"]["aumento_fondo"]) ? $result["table"]["total"]["aumento_fondo"] : 0;
            $tr_final["reduccion_fondo"] +=  isset($result["table"]["total"]["reduccion_fondo"]) ? $result["table"]["total"]["reduccion_fondo"] : 0;

            $tr_final["prestamo_slot"] += isset($result["table"]["total"]["prestamo_slot"]) ? $result["table"]["total"]["prestamo_slot"] : 0;
            $tr_final["prestamo_boveda"] += isset($result["table"]["total"]["prestamo_boveda"]) ? $result["table"]["total"]["prestamo_boveda"] : 0;
            $tr_final["devolucion_slot"] += isset($result["table"]["total"]["devolucion_slot"]) ? $result["table"]["total"]["devolucion_slot"] : 0;
            $tr_final["devolucion_boveda"] += isset($result["table"]["total"]["devolucion_boveda"]) ? $result["table"]["total"]["devolucion_boveda"] : 0;
            $tr_final["deposito_venta"] +=  isset($result["table"]["total"]["deposito_venta"]) ? $result["table"]["total"]["deposito_venta"] : 0;
            $tr_final["efectivo_sobrante"] += isset($result["table"]["total"]["efectivo_sobrante"]) ? $result["table"]["total"]["efectivo_sobrante"] : 0;
        }
       // $finalTable[] = $tr_final;

        $suma_televentas_ingreso = 0 ;
        foreach ($finalTable as $key => $value) {
            if(strpos($value["local_nombre"], 'Televentas') !== false){
                $suma_televentas_ingreso += $value["ds_19_in"]?:0;
                unset($finalTable[$key]);
            }
         }

        $rpta = [
            "suma_televentas_ingreso" => $suma_televentas_ingreso ,
            "finalTable" => $finalTable
        ];
        return $rpta;
        //return $finalTable;
    }
}
if (isset($_POST["accion"]) && $_POST["accion"]==="reportes_contables_xls") {
    date_default_timezone_set("America/Lima");
    $post = array();
    if (isset($_POST["fecha_inicio"])) {
        $post = array("sec_contable_export" => array(
            "is_televentas" => "false",
            "fecha_inicio" => $_POST["fecha_inicio"],
            "fecha_fin" => $_POST["fecha_fin"],
            "group_by" => 'day',
            "local_id" => "",
            "tipo_reporte" => $_POST["tipo_reporte"]
        ));
    } else {
        $post = array("sec_contable_export" => array(
            "is_televentas" => "",
            "local_id" => "",
            "fecha_inicio" => "",
            "fecha_fin" => "",
            "group_by" => "",
            "tipo_reporte" => ""
        ));
    }

    $tipo_cambio = 3.75;
    $tipo_cambio_compra = 3.75;
    $cambio_query = $mysqli->query("SELECT monto_compra,monto_venta FROM tbl_tipo_cambio WHERE moneda_id = 2 AND fecha = CURDATE()")->fetch_assoc();
    if($cambio_query != null){
        $tipo_cambio = $cambio_query["monto_venta"];
        $tipo_cambio_compra = $cambio_query["monto_compra"];
    }

    $reportTable = [];
    if ($post["sec_contable_export"]["tipo_reporte"] == "prestamos") {
        $table = getFinalTableReportesContables($post);      
        $table = $table["finalTable"];
        $reportTable= generar_cabecera();
        $bodyTable=[];
        $bodyReproceso=[];

        foreach ($table as $key => $value) {

            $fecha_fin = $_POST['fecha_fin'];
            $fecha_fin = date('d/m/Y',strtotime( $fecha_fin));

            $bodyTable['sub_diario']=3920;
            $bodyTable['numero_de_comprobante']=isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";
            $bodyTable['fecha_de_comprobante']=$fecha_fin;
            $bodyTable['codigo_de_moneda']='MN';
            $bodyTable['glosa_principal']='REG PRESTAMO ENTRE TIENDAS';
            $bodyTable['tipo_de_cambio']=$tipo_cambio;
            $bodyTable['tipo_de_conversion']='V';
            $bodyTable['flag_de_conversion_de_moneda']='S';
            $bodyTable['fecha_tipo_de_cambio']=$fecha_fin;
            $bodyTable['cuenta_contable']='101121';
            $bodyTable['codigo_de_anexo']=$value['cc_id'].'-FONDO BOVEDA';
            $bodyTable['codigo_de_centro_de_costo']=$value['cc_id'];
            $bodyTable['debe_haber']='';
            $bodyTable['importe_original']='';
            $bodyTable['importe_en_dolares']=''; 
            $bodyTable['importe_en_soles']=''; 
            $bodyTable['tipo_de_documento']='ME'; 


            $fechaNroDocumento = $value['dia'].'-'.$value['mes'].'-'.$value['ano'];
            $newFechaNroDocumento = date("Y-m-d", strtotime($fechaNroDocumento));
            $newMonthFechaNroDocumento = date("m", strtotime($fechaNroDocumento));
            $lastDayMonth = date('d', strtotime($newFechaNroDocumento."last day of this month"));

            $bodyTable['numero_de_documento']='0'.$newMonthFechaNroDocumento.'-'.$value['ano']; 
            $bodyTable['fecha_de_documento']= $lastDayMonth.'/'.$value['mes'].'/'.$value['ano']; 
            $bodyTable['fecha_de_vencimiento']= $lastDayMonth.'/'.$value['mes'].'/'.$value['ano']; 
            $bodyTable['codigo_de_area']= '';
            $bodyTable['glosa_detalle']='REG PTMO ENTRE TIENDAS';
            $bodyTable['codigo_de_anexo_auxiliar']='A0003';
            $bodyTable['medio_de_pago']='';
            $bodyTable['tipo_de_documento_de_referencia']='ME';
            $bodyTable['numero_de_documento_referencia']=$bodyTable['numero_de_documento'];
            $bodyTable['fecha_documento_referencia']=$bodyTable['fecha_de_documento'];
            $bodyTable['nro_maq_registradora_tipo_doc_ref']='';
            $bodyTable['base_imponible_documento_referencia']='';
            $bodyTable['igv_documento_provision']='';
            $bodyTable['tipo_referencia_en_estado_mq']='';
            $bodyTable['numero_serie_caja_registradora']='';
            $bodyTable['fecha_de_operacion']='';
            $bodyTable['tipo_de_tasa']='';
            $bodyTable['tasa_detraccion_percepcion']='';
            $bodyTable['importe_base_detraccion_percepcion_dolares']='';
            $bodyTable['importe_base_detraccion_percepcion_soles']='';
            $bodyTable['tipo_cambio']='';
            $bodyTable['importe_de_igv']='';

            if((int)$value['prestamo_slot']>0){
                $bodyTable['debe_haber']='D';
                $bodyTable['importe_original']=$value['prestamo_slot'];
                $bodyTable['codigo_de_area']=143;
                array_push($bodyReproceso,$bodyTable);
                
            }
            if((int)$value['devolucion_slot']>0){
                $bodyTable['debe_haber']='H';
                $bodyTable['importe_original']=$value['devolucion_slot'];
                $bodyTable['codigo_de_area']=343;
                array_push($bodyReproceso,$bodyTable);
            }        
        }
        
        $centrosCostos = [];
        foreach ($bodyReproceso as $key => $value) {
            $centrosCostos[$value['codigo_de_centro_de_costo']][]=$value;
        }
        $nuevaTabla =[];
        foreach ($centrosCostos as $key => $value) {
            $calculoDebe=0;
            $calculoHaber=0;
            $fecha_de_comprobante='';
            $fecha_tipo_de_cambio='';
            $codigo_de_anexo='';
            $codigo_de_centro_de_costo='';
            $numero_de_documento='';
            $fecha_de_documento='';
            $fecha_de_vencimiento='';
            foreach ($value as $key => $registro) {            
            if($registro['debe_haber']=='D'){
                $calculoDebe+=(int)$registro['importe_original'];
            }else{
                $calculoHaber+=(int)$registro['importe_original'];
            }
            $fecha_de_comprobante=$registro['fecha_de_comprobante'];
            $fecha_tipo_de_cambio=$registro['fecha_tipo_de_cambio'];
            $codigo_de_anexo=$registro['codigo_de_anexo'];
            $codigo_de_centro_de_costo=$registro['codigo_de_centro_de_costo'];
            $numero_de_documento=$registro['numero_de_documento'];
            $fecha_de_documento=$registro['fecha_de_documento'];
            $fecha_de_vencimiento=$registro['fecha_de_vencimiento'];
            }
            $nuevaTabla['campo']='';
            $nuevaTabla['sub_diario']=3920;
            $nuevaTabla['numero_de_comprobante']=isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";
            $nuevaTabla['fecha_de_comprobante']=$fecha_de_comprobante;
            $nuevaTabla['codigo_de_moneda']='MN';
            $nuevaTabla['glosa_principal']='REG PRESTAMO ENTRE TIENDAS';
            $nuevaTabla['tipo_de_cambio']=$tipo_cambio;
            $nuevaTabla['tipo_de_conversion']='V';
            $nuevaTabla['flag_de_conversion_de_moneda']='S';
            $nuevaTabla['fecha_tipo_de_cambio']=$fecha_tipo_de_cambio;
            $nuevaTabla['cuenta_contable']='101121';
            $nuevaTabla['codigo_de_anexo']=$codigo_de_anexo;
            $nuevaTabla['codigo_de_centro_de_costo']=$codigo_de_centro_de_costo;
            $nuevaTabla['debe_haber']='';
            $nuevaTabla['importe_original']='';
            $nuevaTabla['importe_en_dolares']=''; 
            $nuevaTabla['importe_en_soles']=''; 
            $nuevaTabla['tipo_de_documento']='ME'; 
            $nuevaTabla['numero_de_documento']=$numero_de_documento; 
            $nuevaTabla['fecha_de_documento']= $fecha_de_documento; 
            $nuevaTabla['fecha_de_vencimiento']= $fecha_de_vencimiento; 
            $nuevaTabla['codigo_de_area']= '';
            $nuevaTabla['glosa_detalle']='REG PTMO ENTRE TIENDAS';
            $nuevaTabla['codigo_de_anexo_auxiliar']='A0003';
            $nuevaTabla['medio_de_pago']='';
            $nuevaTabla['tipo_de_documento_de_referencia']='ME';
            $nuevaTabla['numero_de_documento_referencia']=$nuevaTabla['numero_de_documento'];
            $nuevaTabla['fecha_documento_referencia']=$nuevaTabla['fecha_de_documento'];
            $nuevaTabla['nro_maq_registradora_tipo_doc_ref']='';
            $nuevaTabla['base_imponible_documento_referencia']='';
            $nuevaTabla['igv_documento_provision']='';
            $nuevaTabla['tipo_referencia_en_estado_mq']='';
            $nuevaTabla['numero_serie_caja_registradora']='';
            $nuevaTabla['fecha_de_operacion']='';
            $nuevaTabla['tipo_de_tasa']='';
            $nuevaTabla['tasa_detraccion_percepcion']='';
            $nuevaTabla['importe_base_detraccion_percepcion_dolares']='';
            $nuevaTabla['importe_base_detraccion_percepcion_soles']='';
            $nuevaTabla['tipo_cambio']='';
            $nuevaTabla['importe_de_igv']='';


            
            if((int)$calculoDebe>0){
                $nuevaTabla['debe_haber']='D';
                $nuevaTabla['importe_original']=$calculoDebe;
                $nuevaTabla['codigo_de_area']=143;
                array_push($reportTable,$nuevaTabla);
            }
            if((int)$calculoHaber>0){
                $nuevaTabla['debe_haber']='H';
                $nuevaTabla['importe_original']=$calculoHaber;
                $nuevaTabla['codigo_de_area']=343;
                array_push($reportTable,$nuevaTabla);
            } 

            
        }
    } elseif ($post["sec_contable_export"]["tipo_reporte"] == "premios") {
        $table = getFinalTableReportesContables($post);
        $table = $table["finalTable"];
        $reportTable = generar_cabecera();
        $bodyTable = [];
        $bodyReproceso = [];

        foreach ($table as $key => $value) {

            $fecha_fin = $_POST['fecha_fin'];
            $fecha_fin = date('d/m/Y', strtotime($fecha_fin));

            $bodyTable['sub_diario'] = 3920;
            $bodyTable['numero_de_comprobante'] = isset($_POST["numero_comprobante"]) ? $_POST["numero_comprobante"] : "";
            $bodyTable['fecha_de_comprobante'] = $fecha_fin;
            $bodyTable['codigo_de_moneda'] = 'MN';
            $bodyTable['glosa_principal'] = 'PAGO PREMIOS JV';
            $bodyTable['tipo_de_cambio'] = $tipo_cambio;
            $bodyTable['tipo_de_conversion'] = 'V';
            $bodyTable['flag_de_conversion_de_moneda'] = 'S';
            $bodyTable['fecha_tipo_de_cambio'] = $fecha_fin;
            $bodyTable['cuenta_contable'] = '101121';
            $bodyTable['codigo_de_anexo'] = $value['cc_id'] . '-FONDO BOVEDA';
            $bodyTable['codigo_de_centro_de_costo'] = $value['cc_id'];
            $bodyTable['debe_haber'] = '';
            $bodyTable['importe_original'] = '';
            $bodyTable['importe_en_dolares'] = '';
            $bodyTable['importe_en_soles'] = '';
            $bodyTable['tipo_de_documento'] = 'ME';


            $fechaNroDocumento = $value['dia'] . '-' . $value['mes'] . '-' . $value['ano'];
            $newFechaNroDocumento = date("Y-m-d", strtotime($fechaNroDocumento));
            $newMonthFechaNroDocumento = date("m", strtotime($fechaNroDocumento));
            $lastDayMonth = date('d', strtotime($newFechaNroDocumento . "last day of this month"));

            $bodyTable['numero_de_documento'] = '0' . $newMonthFechaNroDocumento . '-' . $value['ano'];
            $bodyTable['fecha_de_documento'] = $lastDayMonth . '/' . $value['mes'] . '/' . $value['ano'];
            $bodyTable['fecha_de_vencimiento'] = $lastDayMonth . '/' . $value['mes'] . '/' . $value['ano'];
            $bodyTable['codigo_de_area'] = '';
            $bodyTable['glosa_detalle'] = 'PAGO PREMIOS JV';
            $bodyTable['codigo_de_anexo_auxiliar'] = 'A0003';
            $bodyTable['medio_de_pago'] = '';
            $bodyTable['tipo_de_documento_de_referencia'] = 'ME';
            $bodyTable['numero_de_documento_referencia'] = $bodyTable['numero_de_documento'];
            $bodyTable['fecha_documento_referencia'] = $bodyTable['fecha_de_documento'];
            $bodyTable['nro_maq_registradora_tipo_doc_ref'] = '';
            $bodyTable['base_imponible_documento_referencia'] = '';
            $bodyTable['igv_documento_provision'] = '';
            $bodyTable['tipo_referencia_en_estado_mq'] = '';
            $bodyTable['numero_serie_caja_registradora'] = '';
            $bodyTable['fecha_de_operacion'] = '';
            $bodyTable['tipo_de_tasa'] = '';
            $bodyTable['tasa_detraccion_percepcion'] = '';
            $bodyTable['importe_base_detraccion_percepcion_dolares'] = '';
            $bodyTable['importe_base_detraccion_percepcion_soles'] = '';
            $bodyTable['tipo_cambio'] = '';
            $bodyTable['importe_de_igv'] = '';

            if ((int)$value['bonos_promociones'] > 0) {
                $bodyTable['debe_haber'] = 'H';
                $bodyTable['importe_original'] = $value['bonos_promociones'];
                $bodyTable['codigo_de_area'] = 343;
                array_push($bodyReproceso, $bodyTable);
            }
        }

        $centrosCostos = [];
        foreach ($bodyReproceso as $key => $value) {
            $centrosCostos[$value['codigo_de_centro_de_costo']][] = $value;
        }
        $nuevaTabla = [];
        $nuevaTablaResultado = [];
        $suma_importe_original = 0;
        foreach ($centrosCostos as $key => $value) {
            $calculoDebe = 0;
            $calculoHaber = 0;
            $fecha_de_comprobante = '';
            $fecha_tipo_de_cambio = '';
            $codigo_de_anexo = '';
            $codigo_de_centro_de_costo = '';
            $numero_de_documento = '';
            $fecha_de_documento = '';
            $fecha_de_vencimiento = '';
            foreach ($value as $key => $registro) {
                if ($registro['debe_haber'] == 'H') {
                    $calculoHaber += (int)$registro['importe_original'];
                }
                $fecha_de_comprobante = $registro['fecha_de_comprobante'];
                $fecha_tipo_de_cambio = $registro['fecha_tipo_de_cambio'];
                $codigo_de_anexo = $registro['codigo_de_anexo'];
                $codigo_de_centro_de_costo = $registro['codigo_de_centro_de_costo'];
                $numero_de_documento = $registro['numero_de_documento'];
                $fecha_de_documento = $registro['fecha_de_documento'];
                $fecha_de_vencimiento = $registro['fecha_de_vencimiento'];
            }
            $nuevaTabla['campo'] = '';
            $nuevaTabla['sub_diario'] = 3920;
            $nuevaTabla['numero_de_comprobante'] = isset($_POST["numero_comprobante"]) ? $_POST["numero_comprobante"] : "";
            $nuevaTabla['fecha_de_comprobante'] = $fecha_de_comprobante;
            $nuevaTabla['codigo_de_moneda'] = 'MN';
            $nuevaTabla['glosa_principal'] = 'PAGO PREMIOS JV';
            $nuevaTabla['tipo_de_cambio'] = $tipo_cambio;
            $nuevaTabla['tipo_de_conversion'] = 'V';
            $nuevaTabla['flag_de_conversion_de_moneda'] = 'S';
            $nuevaTabla['fecha_tipo_de_cambio'] = $fecha_tipo_de_cambio;
            $nuevaTabla['cuenta_contable'] = '101121';
            $nuevaTabla['codigo_de_anexo'] = $codigo_de_anexo;
            $nuevaTabla['codigo_de_centro_de_costo'] = $codigo_de_centro_de_costo;
            $nuevaTabla['debe_haber'] = '';
            $nuevaTabla['importe_original'] = '';
            $nuevaTabla['importe_en_dolares'] = '';
            $nuevaTabla['importe_en_soles'] = '';
            $nuevaTabla['tipo_de_documento'] = 'ME';
            $nuevaTabla['numero_de_documento'] = $numero_de_documento;
            $nuevaTabla['fecha_de_documento'] = $fecha_de_documento;
            $nuevaTabla['fecha_de_vencimiento'] = $fecha_de_vencimiento;
            $nuevaTabla['codigo_de_area'] = '';
            $nuevaTabla['glosa_detalle'] = 'PAGO PREMIOS JV';
            $nuevaTabla['codigo_de_anexo_auxiliar'] = 'A0003';
            $nuevaTabla['medio_de_pago'] = '';
            $nuevaTabla['tipo_de_documento_de_referencia'] = 'ME';
            $nuevaTabla['numero_de_documento_referencia'] = $nuevaTabla['numero_de_documento'];
            $nuevaTabla['fecha_documento_referencia'] = $nuevaTabla['fecha_de_documento'];
            $nuevaTabla['nro_maq_registradora_tipo_doc_ref'] = '';
            $nuevaTabla['base_imponible_documento_referencia'] = '';
            $nuevaTabla['igv_documento_provision'] = '';
            $nuevaTabla['tipo_referencia_en_estado_mq'] = '';
            $nuevaTabla['numero_serie_caja_registradora'] = '';
            $nuevaTabla['fecha_de_operacion'] = '';
            $nuevaTabla['tipo_de_tasa'] = '';
            $nuevaTabla['tasa_detraccion_percepcion'] = '';
            $nuevaTabla['importe_base_detraccion_percepcion_dolares'] = '';
            $nuevaTabla['importe_base_detraccion_percepcion_soles'] = '';
            $nuevaTabla['tipo_cambio'] = '';
            $nuevaTabla['importe_de_igv'] = '';
            if ((int)$calculoHaber > 0) {
                $nuevaTabla['debe_haber'] = 'H';
                $nuevaTabla['importe_original'] = $calculoHaber;
                $suma_importe_original +=$calculoHaber;
                $nuevaTabla['codigo_de_area'] = 343;
                array_push($reportTable, $nuevaTabla);
                $nuevaTablaResultado=$nuevaTabla;
            }
        } 
        
        $nuevaTablaResultado['cuenta_contable']='469901';
        $nuevaTablaResultado['codigo_de_anexo']='PREMIOS';
        $nuevaTablaResultado['codigo_de_centro_de_costo']='';
        $nuevaTablaResultado['debe_haber']='D';
        $nuevaTablaResultado['importe_original']= $suma_importe_original;
        $nuevaTablaResultado['codigo_de_area']= '';
        $nuevaTablaResultado['tipo_de_documento_de_referencia']= '';
        $nuevaTablaResultado['numero_de_documento_referencia']= '';
        $nuevaTablaResultado['fecha_documento_referencia']= '';
        array_push($reportTable, $nuevaTablaResultado);  
    } elseif ($post["sec_contable_export"]["tipo_reporte"] == "faltantes") {
        $table = getFinalTableReportesContables($post);
        $table = $table["finalTable"];
        $cabecera = [];
        $cabecera['sub_diario']             = "Sub Diario";
        $cabecera['numero_de_comprobante']  = "Número de Comprobante";
        $cabecera['fecha_de_comprobante']   = "Fecha de Comprobante";
        $cabecera['codigo_de_moneda']       = "Código de Moneda";
        $cabecera['glosa_principal']        = "Glosa Principal";
        $cabecera['tipo_de_cambio']         = "Tipo de Cambio";
        $cabecera['tipo_de_conversion']     = "Tipo de Conversión";
        $cabecera['flag_de_conversion_de_moneda'] = "Flag de Conversión de Moneda";
        $cabecera['fecha_tipo_de_cambio']   = "Fecha Tipo de Cambio";
        $cabecera['cuenta_contable']        = "Cuenta Contable";
        $cabecera['codigo_de_anexo']        = "Código de Anexo";
        $cabecera['codigo_de_centro_de_costo'] = "Código de Centro de Costo";
        $cabecera['debe_haber']             = "Debe / Haber";
        $cabecera['importe_original_prestamo_slot'] = "Importe Original";
        $cabecera['importe_en_dolares']     = "Importe en Dólares";
        $cabecera['importe_en_soles']       = "Importe en Soles";
        $cabecera['tipo_de_documento']      = "Tipo de Documento";
        $cabecera['numero_de_documento']       = "Número de Documento";
        $cabecera['fecha_de_documento']     = "Fecha de Documento";
        $cabecera['fecha_de_vencimiento']   = "Fecha de Vencimiento";
        $cabecera['codigo_de_area']         = "Código de Area";
        $cabecera['glosa_detalle']          = "Glosa Detalle";
        $cabecera['codigo_de_anexo_auxiliar'] = "Código de Anexo Auxiliar";
        $cabecera['medio_de_pago']          = "Medio de Pago";
        $cabecera['tipo_de_documento_de_referencia'] = "Tipo de Documento de Referencia";
        $cabecera['numero_de_documento_referencia'] = "Número de Documento Referencia";
        $cabecera['fecha_documento_referencia'] = "Fecha Documento Referencia";
        $cabecera['nro_maq_registradora_tipo_doc_ref'] = "Nro Máq. Registradora Tipo Doc. Ref.";
        $cabecera['base_imponible_documento_referencia'] = "Base Imponible Documento Referencia";
        $cabecera['igv_documento_provision'] = "IGV Documento Provisión";
        $cabecera['tipo_referencia_en_estado_mq'] = "Tipo Referencia en estado MQ";
        $cabecera['numero_serie_caja_registradora'] = "Número Serie Caja Registradora";
        $cabecera['fecha_de_operacion']     = "Fecha de Operación";
        $cabecera['tipo_de_tasa']           = "Tipo de Tasa";
        $cabecera['tasa_detraccion_percepcion'] = "Tasa Detracción/Percepción";
        $cabecera['importe_base_detraccion_percepcion_dolares'] = "Importe Base Detracción/Percepción Dólares";
        $cabecera['importe_base_detraccion_percepcion_soles'] = "Importe Base Detracción/Percepción Soles";
        $cabecera['tipo_cambio']            = "Tipo Cambio para 'F'";
        $cabecera['importe_de_igv']         = "Importe de IGV sin derecho crédito fiscal";
        array_push($reportTable,$cabecera);
        $bodyTable=[];
        
        foreach ($table as $key => $value) {
           
            $bodyTable['sub_diario']=3920;
            $bodyTable['numero_de_comprobante']=isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";
            $bodyTable['fecha_de_comprobante']=$value['dia'].'/'.$value['mes'].'/'.$value['ano'];
            $bodyTable['codigo_de_moneda']='MN';
            $bodyTable['glosa_principal']='REG FALTANTES';
            $bodyTable['tipo_de_cambio']='3,75';
            $bodyTable['tipo_de_conversion']='V';
            $bodyTable['flag_de_conversion_de_moneda']='S';
            $bodyTable['fecha_tipo_de_cambio']=$value['dia'].'/'.$value['mes'].'/'.$value['ano'];
            $bodyTable['cuenta_contable']='101121';
            $bodyTable['codigo_de_anexo']=$value['cc_id'].'-FONDO BOVEDA';
            $bodyTable['codigo_de_centro_de_costo']=$value['cc_id'];
            $bodyTable['debe_haber']='';
            $bodyTable['importe_original']='';
            $bodyTable['importe_en_dolares']=''; // imnporte dolares
            $bodyTable['importe_en_soles']=''; // imnporte soles
            $bodyTable['tipo_de_documento']='ME'; // Tipo de Documento

            /* fechas  */
            $fechaNroDocumento = $value['dia'].'-'.$value['mes'].'-'.$value['ano'];
            $newFechaNroDocumento = date("Y-m-d", strtotime($fechaNroDocumento));
            $newMonthFechaNroDocumento = date("m", strtotime($fechaNroDocumento));
            $lastDayMonth = date('d', strtotime($newFechaNroDocumento."last day of this month"));

            $bodyTable['numero_de_documento']='0'.$newMonthFechaNroDocumento.'-'.$value['ano']; 
            $bodyTable['fecha_de_documento']= $lastDayMonth.'/'.$value['mes'].'-'.$value['ano']; //Fecha de Documento
            $bodyTable['fecha_de_vencimiento']= $lastDayMonth.'/'.$value['mes'].'-'.$value['ano']; //Fecha de Vencimiento
            $bodyTable['codigo_de_area']= '';
            $bodyTable['glosa_detalle']='REG FALTANTES';
            $bodyTable['codigo_de_anexo_auxiliar']='A0003';
            $bodyTable['medio_de_pago']='';
            $bodyTable['tipo_de_documento_de_referencia']='ME';
            $bodyTable['numero_de_documento_referencia']=$bodyTable['numero_de_documento'];
            $bodyTable['fecha_documento_referencia']=$bodyTable['fecha_de_documento'];
            $bodyTable['nro_maq_registradora_tipo_doc_ref']='';
            $bodyTable['base_imponible_documento_referencia']='';
            $bodyTable['igv_documento_provision']='';
            $bodyTable['tipo_referencia_en_estado_mq']='';
            $bodyTable['numero_serie_caja_registradora']='';
            $bodyTable['fecha_de_operacion']='';
            $bodyTable['tipo_de_tasa']='';
            $bodyTable['tasa_detraccion_percepcion']='';
            $bodyTable['importe_base_detraccion_percepcion_dolares']='';
            $bodyTable['importe_base_detraccion_percepcion_soles']='';
            $bodyTable['tipo_cambio']='';
            $bodyTable['importe_de_igv']='';

            $bodyTable['debe_haber']='-';
            $bodyTable['importe_original']='-';
            $bodyTable['codigo_de_area']=9999;

            array_push($reportTable,$bodyTable);          
        }
    } elseif ($post["sec_contable_export"]["tipo_reporte"] == "sobrantes") {
        $table = getFinalTableReportesContables($post);
        $table = $table["finalTable"];
        $cabecera = [];
        $cabecera['sub_diario']             = "Sub Diario";
        $cabecera['numero_de_comprobante']  = "Número de Comprobante";
        $cabecera['fecha_de_comprobante']   = "Fecha de Comprobante";
        $cabecera['codigo_de_moneda']       = "Código de Moneda";
        $cabecera['glosa_principal']        = "Glosa Principal";
        $cabecera['tipo_de_cambio']         = "Tipo de Cambio";
        $cabecera['tipo_de_conversion']     = "Tipo de Conversión";
        $cabecera['flag_de_conversion_de_moneda'] = "Flag de Conversión de Moneda";
        $cabecera['fecha_tipo_de_cambio']   = "Fecha Tipo de Cambio";
        $cabecera['cuenta_contable']        = "Cuenta Contable";
        $cabecera['codigo_de_anexo']        = "Código de Anexo";
        $cabecera['codigo_de_centro_de_costo'] = "Código de Centro de Costo";
        $cabecera['debe_haber']             = "Debe / Haber";
        $cabecera['importe_original_prestamo_slot'] = "Importe Original";
        $cabecera['importe_en_dolares']     = "Importe en Dólares";
        $cabecera['importe_en_soles']       = "Importe en Soles";
        $cabecera['tipo_de_documento']      = "Tipo de Documento";
        $cabecera['numero_de_documento']       = "Número de Documento";
        $cabecera['fecha_de_documento']     = "Fecha de Documento";
        $cabecera['fecha_de_vencimiento']   = "Fecha de Vencimiento";
        $cabecera['codigo_de_area']         = "Código de Area";
        $cabecera['glosa_detalle']          = "Glosa Detalle";
        $cabecera['codigo_de_anexo_auxiliar'] = "Código de Anexo Auxiliar";
        $cabecera['medio_de_pago']          = "Medio de Pago";
        $cabecera['tipo_de_documento_de_referencia'] = "Tipo de Documento de Referencia";
        $cabecera['numero_de_documento_referencia'] = "Número de Documento Referencia";
        $cabecera['fecha_documento_referencia'] = "Fecha Documento Referencia";
        $cabecera['nro_maq_registradora_tipo_doc_ref'] = "Nro Máq. Registradora Tipo Doc. Ref.";
        $cabecera['base_imponible_documento_referencia'] = "Base Imponible Documento Referencia";
        $cabecera['igv_documento_provision'] = "IGV Documento Provisión";
        $cabecera['tipo_referencia_en_estado_mq'] = "Tipo Referencia en estado MQ";
        $cabecera['numero_serie_caja_registradora'] = "Número Serie Caja Registradora";
        $cabecera['fecha_de_operacion']     = "Fecha de Operación";
        $cabecera['tipo_de_tasa']           = "Tipo de Tasa";
        $cabecera['tasa_detraccion_percepcion'] = "Tasa Detracción/Percepción";
        $cabecera['importe_base_detraccion_percepcion_dolares'] = "Importe Base Detracción/Percepción Dólares";
        $cabecera['importe_base_detraccion_percepcion_soles'] = "Importe Base Detracción/Percepción Soles";
        $cabecera['tipo_cambio']            = "Tipo Cambio para 'F'";
        $cabecera['importe_de_igv']         = "Importe de IGV sin derecho crédito fiscal";
        array_push($reportTable,$cabecera);
        $bodyTable=[];
        
        foreach ($table as $key => $value) {
           
            $bodyTable['sub_diario']=3920;
            $bodyTable['numero_de_comprobante']=isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";
            $bodyTable['fecha_de_comprobante']=$value['dia'].'/'.$value['mes'].'/'.$value['ano'];
            $bodyTable['codigo_de_moneda']='MN';
            $bodyTable['glosa_principal']='REG SOBRANTES';
            $bodyTable['tipo_de_cambio']='3,75';
            $bodyTable['tipo_de_conversion']='V';
            $bodyTable['flag_de_conversion_de_moneda']='S';
            $bodyTable['fecha_tipo_de_cambio']=$value['dia'].'/'.$value['mes'].'/'.$value['ano'];
            $bodyTable['cuenta_contable']='101121';
            $bodyTable['codigo_de_anexo']=$value['cc_id'].'-FONDO BOVEDA';
            $bodyTable['codigo_de_centro_de_costo']=$value['cc_id'];
            $bodyTable['debe_haber']='';
            $bodyTable['importe_original']='';
            $bodyTable['importe_en_dolares']=''; // imnporte dolares
            $bodyTable['importe_en_soles']=''; // imnporte soles
            $bodyTable['tipo_de_documento']='ME'; // Tipo de Documento

            /* fechas  */
            $fechaNroDocumento = $value['dia'].'-'.$value['mes'].'-'.$value['ano'];
            $newFechaNroDocumento = date("Y-m-d", strtotime($fechaNroDocumento));
            $newMonthFechaNroDocumento = date("m", strtotime($fechaNroDocumento));
            $lastDayMonth = date('d', strtotime($newFechaNroDocumento."last day of this month"));

            $bodyTable['numero_de_documento']='0'.$newMonthFechaNroDocumento.'-'.$value['ano']; 
            $bodyTable['fecha_de_documento']= $lastDayMonth.'/'.$value['mes'].'-'.$value['ano']; //Fecha de Documento
            $bodyTable['fecha_de_vencimiento']= $lastDayMonth.'/'.$value['mes'].'-'.$value['ano']; //Fecha de Vencimiento
            $bodyTable['codigo_de_area']= '';
            $bodyTable['glosa_detalle']='REG SOBRANTES';
            $bodyTable['codigo_de_anexo_auxiliar']='A0003';
            $bodyTable['medio_de_pago']='';
            $bodyTable['tipo_de_documento_de_referencia']='ME';
            $bodyTable['numero_de_documento_referencia']=$bodyTable['numero_de_documento'];
            $bodyTable['fecha_documento_referencia']=$bodyTable['fecha_de_documento'];
            $bodyTable['nro_maq_registradora_tipo_doc_ref']='';
            $bodyTable['base_imponible_documento_referencia']='';
            $bodyTable['igv_documento_provision']='';
            $bodyTable['tipo_referencia_en_estado_mq']='';
            $bodyTable['numero_serie_caja_registradora']='';
            $bodyTable['fecha_de_operacion']='';
            $bodyTable['tipo_de_tasa']='';
            $bodyTable['tasa_detraccion_percepcion']='';
            $bodyTable['importe_base_detraccion_percepcion_dolares']='';
            $bodyTable['importe_base_detraccion_percepcion_soles']='';
            $bodyTable['tipo_cambio']='';
            $bodyTable['importe_de_igv']='';

            $bodyTable['debe_haber']='-';
            $bodyTable['importe_original']='-';
            $bodyTable['codigo_de_area']=9999;

            array_push($reportTable,$bodyTable);      
        }
    } elseif ($post["sec_contable_export"]["tipo_reporte"] == "kasnet_deposito") {
        $reportTable = generar_cabecera();
        $post["group_by"] = "day";

        $table = getFinalTableReportesContables($post);
        $table = $table["finalTable"];

        $fila_agrupado_tienda = [];
        foreach ($table as $key => $value) {
            $fila_agrupado_tienda[$value["local_nombre"]][]= $value;
        }
        $table2 = [];
        foreach ($fila_agrupado_tienda as $key => $value) {
            $debe = 0;
            $haber = 0;
            $registro_tienda = [];
            foreach ($value as $index => $obj) {
                $debe += $obj["ds_13_in"]?$obj["ds_13_in"]:0;
                $haber += $obj["ds_13_out"]?$obj["ds_13_out"]:0;
            }
            $nueva_fila = $value[0];
            $nueva_fila["ds_13_in"] = $debe;
            $nueva_fila["ds_13_out"] = $haber;
            $table2[] = $nueva_fila;
        }
        //echo "<pre>";print_r($table);echo "</pre>";//die();//echo "<pre>";print_r($table2);echo "</pre>";die();
        foreach ($table2 as $key => $value) {
            $fila = [];
            $fila['campo']='';
            $fila["Sub Diario"] = "3920";
            $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";//$value["mes"]."0066";
            $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
            $fila["Código de Moneda"] = "MN";
            $fila["Glosa Principal"] = "OP. KASNET - DEPOSITO Y PAGO";//**//
            $fila["Tipo de Cambio"] = $tipo_cambio;
            $fila["Tipo de Conversión"] = "V";
            $fila["Flag de Conversión de Moneda"] = "S";
            $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
            $fila["Cuenta Contable"] = "103141";
            $fila["Código de Anexo"] = $value["cc_id"]."-FONDO BOVEDA";
            $fila["Código de Centro de Costo"] = $value["cc_id"];
            $fila["Debe / Haber"] = "H";                                  //**//
            $fila["Importe Original"] = $value["ds_13_in"];//kasnet ingreso
            $fila["Importe en Dólares"] = "";
            $fila["Importe en Soles"] = "";
            $fila["Tipo de Documento"] = "VR";                           //**//
            $fila["Número de Documento"] = "ASIGNACION KASNET";
            $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
            $fila["Fecha de Vencimiento"] = $fila["Fecha de Comprobante"];
            $fila["Código de Area"] = "343";                             //**//
            $fila["Glosa Detalle"] = "Deposito y pago 0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];
            $fila["Código de Anexo Auxiliar"] = "A0002";
            $fila["Medio de Pago"] = "";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
            $fila["Base Imponible Documento Referencia"] = "";
            $fila["IGV Documento Provisión"] = "";
            $fila["Tipo Referencia en estado MQ"] = "";
            $fila["Número Serie Caja Registradora"] = "";
            $fila["Fecha de Operación"] = "";
            $fila["Tipo de Tasa"] = "";
            $fila["Tasa Detracción/Percepción"] = "";
            $fila["Importe Base Detracción/Percepción Dólares"] = "";
            $fila["Importe Base Detracción/Percepción Soles"] = "";
            $fila["Tipo Cambio para 'F'"] = "";
            $fila["Importe de IGV sin derecho crédito fiscal"] = "";
            if($value["ds_13_in"]!= "0"){
                $reportTable[] = $fila ;
            }

            $fila["Glosa Principal"] = "OP. KASNET - DEPOSITO Y PAGO";//**//
            $fila["Importe Original"] = $value["ds_13_in"];//kasnet ingreso
            $fila["Cuenta Contable"] = "101121";
            $fila["Debe / Haber"] = "D";
            $fila["Tipo de Documento"] = "ME";
            $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];
            $fila["Código de Area"] = "143";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Glosa Detalle"] = "Deposito y pago Kasnet";
            if($value["ds_13_in"]!= "0"){
                $reportTable[] = $fila ;
            }
        }
    }
    elseif ($post["sec_contable_export"]["tipo_reporte"] == "disashop_deposito") 
    {
        $reportTable = generar_cabecera();
        $post["group_by"] = "day";

        $table = getFinalTableReportesContables($post);
        $table = $table["finalTable"];

        $fila_agrupado_tienda = [];
        foreach ($table as $key => $value) {
            $fila_agrupado_tienda[$value["local_nombre"]][]= $value;
        }
        $table2 = [];
        foreach ($fila_agrupado_tienda as $key => $value) {
            $debe = 0;
            $haber = 0;
            $registro_tienda = [];
            foreach ($value as $index => $obj) {
                $debe += $obj["ds_21_in"]?$obj["ds_21_in"]:0;
                $haber += 0;//$obj["ds_21_out"]?$obj["ds_21_out"]:0;
            }
            $nueva_fila = $value[0];
            $nueva_fila["ds_21_in"] = $debe;
            $nueva_fila["ds_21_out"] = 0;
            $table2[] = $nueva_fila;
        }
        //echo "<pre>";print_r($table);echo "</pre>";//die();
        //echo "<pre>";print_r($table2);echo "</pre>";die();
        foreach ($table2 as $key => $value) {
            $fila = [];
            $fila['campo']='';
            $fila["Sub Diario"] = "3920";
            $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";
            $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
            $fila["Código de Moneda"] = "MN";
            $fila["Glosa Principal"] = "OP. DISASHOP - DEPOSITO Y PAGO";//**//
            $fila["Tipo de Cambio"] = $tipo_cambio;
            $fila["Tipo de Conversión"] = "V";
            $fila["Flag de Conversión de Moneda"] = "S";
            $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
            $fila["Cuenta Contable"] = "103142";
            $fila["Código de Anexo"] = $value["cc_id"]."-FONDO BOVEDA";
            $fila["Código de Centro de Costo"] = $value["cc_id"];
            $fila["Debe / Haber"] = "H";
            $fila["Importe Original"] = $value["ds_21_in"];//DISASHOP  ingreso 21  tbl_caja_detalle_tipos
            $fila["Importe en Dólares"] = "";
            $fila["Importe en Soles"] = "";
            $fila["Tipo de Documento"] = "VR";
            $fila["Número de Documento"] = "ASIGNACION DISASHOP";
            $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
            $fila["Fecha de Vencimiento"] = $fila["Fecha de Comprobante"];
            $fila["Código de Area"] = "343";
            $fila["Glosa Detalle"] = "Deposito y pago 0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];
            $fila["Código de Anexo Auxiliar"] = "A0002";
            $fila["Medio de Pago"] = "";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
            $fila["Base Imponible Documento Referencia"] = "";
            $fila["IGV Documento Provisión"] = "";
            $fila["Tipo Referencia en estado MQ"] = "";
            $fila["Número Serie Caja Registradora"] = "";
            $fila["Fecha de Operación"] = "";
            $fila["Tipo de Tasa"] = "";
            $fila["Tasa Detracción/Percepción"] = "";
            $fila["Importe Base Detracción/Percepción Dólares"] = "";
            $fila["Importe Base Detracción/Percepción Soles"] = "";
            $fila["Tipo Cambio para 'F'"] = "";
            $fila["Importe de IGV sin derecho crédito fiscal"] = "";
            if($value["ds_21_in"]!= "0"){
                $reportTable[] = $fila ;
            }
            $fila["Glosa Principal"] = "OP. DISASHOP - DEPOSITO Y PAGO";//**//
            $fila["Importe Original"] = $value["ds_21_in"];//DISASHOP ingreso 21  tbl_caja_detalle_tipos
            $fila["Cuenta Contable"] = "101121";
            $fila["Debe / Haber"] = "D";
            $fila["Tipo de Documento"] = "ME";
            $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];
            $fila["Código de Area"] = "143";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Glosa Detalle"] = "Deposito y pago Kasnet";
            if($value["ds_21_in"]!= "0"){
                $reportTable[] = $fila ;
            }
        }
    }
    elseif ($post["sec_contable_export"]["tipo_reporte"] == "kasnet_retiro") {
        $reportTable = generar_cabecera();
        $post["group_by"] = "day";
        $table = getFinalTableReportesContables($post);
        $table = $table["finalTable"];

        $fila_agrupado_tienda = [];
        foreach ($table as $key => $value) {
            $fila_agrupado_tienda[$value["local_nombre"]][]= $value;
        }
        $table2 = [];
        foreach ($fila_agrupado_tienda as $key => $value) {
            $debe = 0;
            $haber = 0;
            $registro_tienda = [];
            foreach ($value as $index => $obj) {
                $debe += $obj["ds_13_in"]?$obj["ds_13_in"]:0;
                $haber += $obj["ds_13_out"]?$obj["ds_13_out"]:0;
            }
            $nueva_fila = $value[0];
            $nueva_fila["ds_13_in"] = $debe;
            $nueva_fila["ds_13_out"] = $haber;
            $table2[] = $nueva_fila;
        }
        foreach ($table2 as $key => $value) {
            $fila = [];
            $fila['campo']='';
            $fila["Sub Diario"] = "3920";
            $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";//$value["mes"]."0066";
            $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
            $fila["Código de Moneda"] = "MN";
            $fila["Glosa Principal"] = "OP. KASNET - RETIROS";            //**//
            $fila["Tipo de Cambio"] = $tipo_cambio;
            $fila["Tipo de Conversión"] = "V";
            $fila["Flag de Conversión de Moneda"] = "S";
            $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
            $fila["Cuenta Contable"] = "101121";
            $fila["Código de Anexo"] = $value["cc_id"]."-FONDO BOVEDA";
            $fila["Código de Centro de Costo"] = $value["cc_id"];
            $fila["Debe / Haber"] = "H";                                  //**//
            $fila["Importe Original"] = $value["ds_13_out"];//kasnet salida
            $fila["Importe en Dólares"] = "";
            $fila["Importe en Soles"] = "";
            $fila["Tipo de Documento"] = "ME";                           //**//
            $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];//**//
            $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
            $fila["Fecha de Vencimiento"] = $fila["Fecha de Comprobante"];
            $fila["Código de Area"] = "343";                             //**//
            $fila["Glosa Detalle"] = "Retiros Kasnet";                   //**//
            $fila["Código de Anexo Auxiliar"] = "A0002";
            $fila["Medio de Pago"] = "";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
            $fila["Base Imponible Documento Referencia"] = "";
            $fila["IGV Documento Provisión"] = "";
            $fila["Tipo Referencia en estado MQ"] = "";
            $fila["Número Serie Caja Registradora"] = "";
            $fila["Fecha de Operación"] = "";
            $fila["Tipo de Tasa"] = "";
            $fila["Tasa Detracción/Percepción"] = "";
            $fila["Importe Base Detracción/Percepción Dólares"] = "";
            $fila["Importe Base Detracción/Percepción Soles"] = "";
            $fila["Tipo Cambio para 'F'"] = "";
            $fila["Importe de IGV sin derecho crédito fiscal"] = "";

            if($value["ds_13_out"]!= "0"){
                $reportTable[] = $fila ;
            }
            $fila["Glosa Principal"] = "OP. KASNET - RETIROS";  //* F *//
            $fila["Cuenta Contable"] = "103141";
            $fila["Debe / Haber"] = "D";                        //* N *//
            $fila["Importe Original"] = $value["ds_13_out"];    //kasnet salida
            $fila["Tipo de Documento"] = "VR";                  //* R *//
            $fila["Número de Documento"] = "ASIGNACION KASNET"; //* S *//
            $fila["Código de Area"] = "143";                    //* V *//
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Glosa Detalle"] = "Retiros 0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];//* W *//

            if($value["ds_13_out"]!= "0"){
                $reportTable[] = $fila ;
            }

        }
    }
     elseif ($post["sec_contable_export"]["tipo_reporte"] == "torito_pago") {
        $reportTable = generar_cabecera();
        $post["group_by"] = "day";
        $table = getFinalTableReportesContables($post);
        $suma_televentas_ingreso = $table["suma_televentas_ingreso"];
        $table = $table["finalTable"];

        $fila_agrupado_tienda = [];
        foreach ($table as $key => $value) {
            $fila_agrupado_tienda[$value["local_nombre"]][]= $value;
        }
        $table2 = [];
        foreach ($fila_agrupado_tienda as $key => $value) {
            $debe = 0;
            $haber = 0;
            $registro_tienda = [];
            foreach ($value as $index => $obj) {
                $debe += $obj["ds_19_in"]?$obj["ds_19_in"]:0;
                $haber += $obj["ds_19_out"]?$obj["ds_19_out"]:0;
            }
            $nueva_fila = $value[0];
            $nueva_fila["ds_19_in"] = $debe;
            $nueva_fila["ds_19_out"] = $haber;
            $table2[] = $nueva_fila;
        }
        //echo "<pre>";print_r($table);echo "</pre>";//die();//echo "<pre>";print_r($table2);echo "</pre>";die();
        foreach ($table2 as $key => $value) {
            $fila = [];
            $fila['campo']='';
            $fila["Sub Diario"] = "3920";
            $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";//$value["mes"]."0068";
            $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
            $fila["Código de Moneda"] = "MN";
            $fila["Glosa Principal"] = "TORITO - PAGO DE BOLETOS";
            $fila["Tipo de Cambio"] = $tipo_cambio;
            $fila["Tipo de Conversión"] = "V";
            $fila["Flag de Conversión de Moneda"] = "S";
            $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
            $fila["Cuenta Contable"] = "101121";
            $fila["Código de Anexo"] = $value["cc_id"]."-FONDO BOVEDA";
            $fila["Código de Centro de Costo"] = $value["cc_id"];
            $fila["Debe / Haber"] = "H";
            $fila["Importe Original"] = $value["ds_19_out"];//toro salidaS
            $fila["Importe en Dólares"] = "";
            $fila["Importe en Soles"] = "";
            $fila["Tipo de Documento"] = "ME";
            $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];
            $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
            $fila["Fecha de Vencimiento"] = $fila["Fecha de Comprobante"];
            $fila["Código de Area"] = "343";
            $fila["Glosa Detalle"] = "TORITO - PAGO DE BOLETOS";
            $fila["Código de Anexo Auxiliar"] = "A0002";
            $fila["Medio de Pago"] = "";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
            $fila["Base Imponible Documento Referencia"] = "";
            $fila["IGV Documento Provisión"] = "";
            $fila["Tipo Referencia en estado MQ"] = "";
            $fila["Número Serie Caja Registradora"] = "";
            $fila["Fecha de Operación"] = "";
            $fila["Tipo de Tasa"] = "";
            $fila["Tasa Detracción/Percepción"] = "";
            $fila["Importe Base Detracción/Percepción Dólares"] = "";
            $fila["Importe Base Detracción/Percepción Soles"] = "";
            $fila["Tipo Cambio para 'F'"] = "";
            $fila["Importe de IGV sin derecho crédito fiscal"] = "";

            if($value["ds_19_out"] != "0"){
                $reportTable[] = $fila ;
            }
        }
        $suma_h = 0;
        foreach ($reportTable as $key => $value_f) {
            if(isset($value_f["Debe / Haber"]) && $value_f["Debe / Haber"] == "H"){
                $suma_h = $suma_h + $value_f["Importe Original"];
            }
        }
        //fila D cuenta 469901
        $fila = [];
        $fila['campo'] = '';
        $fila["Sub Diario"] = "3920";
        $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";//$value["mes"]."0068";
        $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
        $fila["Código de Moneda"] = "MN";
        $fila["Glosa Principal"] = "TORITO - PAGO DE BOLETOS";
        $fila["Tipo de Cambio"] = $tipo_cambio;
        $fila["Tipo de Conversión"] = "V";
        $fila["Flag de Conversión de Moneda"] = "S";
        $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
        $fila["Cuenta Contable"] = "469901";
        $fila["Código de Anexo"] = "20602925553";
        $fila["Código de Centro de Costo"] = "";
        $fila["Debe / Haber"] = "D";
        $fila["Importe Original"] = $suma_h;
        $fila["Importe en Dólares"] = "";
        $fila["Importe en Soles"] = "";
        $fila["Tipo de Documento"] = "ME";
        $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". date("y",strtotime($_POST["fecha_fin"]));
        $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
        $fila["Fecha de Vencimiento"] = $fila["Fecha de Comprobante"];
        $fila["Código de Area"] = "";
        $fila["Glosa Detalle"] = $fila["Glosa Principal"];
        $fila["Código de Anexo Auxiliar"] = "A0002";
        $fila["Medio de Pago"] = "";
        $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
        $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
        $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
        $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
        $fila["Base Imponible Documento Referencia"] = "";
        $fila["IGV Documento Provisión"] = "";
        $fila["Tipo Referencia en estado MQ"] = "";
        $fila["Número Serie Caja Registradora"] = "";
        $fila["Fecha de Operación"] = "";
        $fila["Tipo de Tasa"] = "";
        $fila["Tasa Detracción/Percepción"] = "";
        $fila["Importe Base Detracción/Percepción Dólares"] = "";
        $fila["Importe Base Detracción/Percepción Soles"] = "";
        $fila["Tipo Cambio para 'F'"] = "";
        $fila["Importe de IGV sin derecho crédito fiscal"] = "";

        $reportTable[] = $fila ;
    }
    elseif ($post["sec_contable_export"]["tipo_reporte"] == "torito_cobro") {
        $reportTable = generar_cabecera();
        $post["group_by"] = "day";
        $table = getFinalTableReportesContables($post);
        $suma_televentas_ingreso = $table["suma_televentas_ingreso"];
        $table = $table["finalTable"];

        $fila_agrupado_tienda = [];
        foreach ($table as $key => $value) {
            $fila_agrupado_tienda[$value["local_nombre"]][]= $value;
        }
        $table2 = [];
        foreach ($fila_agrupado_tienda as $key => $value) {
            $debe = 0;
            $haber = 0;
            $registro_tienda = [];
            foreach ($value as $index => $obj) {
                $debe += $obj["ds_19_in"]?$obj["ds_19_in"]:0;
                $haber += $obj["ds_19_out"]?$obj["ds_19_out"]:0;
            }
            $nueva_fila = $value[0];
            $nueva_fila["ds_19_in"] = $debe;
            $nueva_fila["ds_19_out"] = $haber;
            $table2[] = $nueva_fila;
        }
        foreach ($table2 as $key => $value) {
            $fila = [];
            $fila['campo'] = '';
            $fila["Sub Diario"] = "3920";
            $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";//$value["mes"]."0068";
            $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
            $fila["Código de Moneda"] = "MN";
            $fila["Glosa Principal"] = "TORITO - COBRO DE BOLETOS";
            $fila["Tipo de Cambio"] = $tipo_cambio;
            $fila["Tipo de Conversión"] = "V";
            $fila["Flag de Conversión de Moneda"] = "S";
            $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
            $fila["Cuenta Contable"] = "101121";
            $fila["Código de Anexo"] = $value["cc_id"]."-FONDO BOVEDA";
            $fila["Código de Centro de Costo"] = $value["cc_id"];
            $fila["Debe / Haber"] = "D";
            $fila["Importe Original"] = $value["ds_19_in"];//toro ingreso
            $fila["Importe en Dólares"] = "";
            $fila["Importe en Soles"] = "";
            $fila["Tipo de Documento"] = "ME";
            $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];
            $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
            $fila["Fecha de Vencimiento"] = $fila["Fecha de Comprobante"];
            $fila["Código de Area"] = "143";
            $fila["Glosa Detalle"] = "TORITO - COBRO DE BOLETOS";
            $fila["Código de Anexo Auxiliar"] = "A0002";
            $fila["Medio de Pago"] = "";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
            $fila["Base Imponible Documento Referencia"] = "";
            $fila["IGV Documento Provisión"] = "";
            $fila["Tipo Referencia en estado MQ"] = "";
            $fila["Número Serie Caja Registradora"] = "";
            $fila["Fecha de Operación"] = "";
            $fila["Tipo de Tasa"] = "";
            $fila["Tasa Detracción/Percepción"] = "";
            $fila["Importe Base Detracción/Percepción Dólares"] = "";
            $fila["Importe Base Detracción/Percepción Soles"] = "";
            $fila["Tipo Cambio para 'F'"] = "";
            $fila["Importe de IGV sin derecho crédito fiscal"] = "";

            if($value["ds_19_in"] != "0"){
                $reportTable[] = $fila ;
            }
        }

        ///fila D  cuenta 169901   suma televentas
        $fila = [];
        $fila['campo'] = '';
        $fila["Sub Diario"] = "3920";
        $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";
        $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
        $fila["Código de Moneda"] = "MN";
        $fila["Glosa Principal"] = "TORITO - COBRO DE BOLETOS";
        $fila["Tipo de Cambio"] = $tipo_cambio;
        $fila["Tipo de Conversión"] = "V";
        $fila["Flag de Conversión de Moneda"] = "S";
        $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
        $fila["Cuenta Contable"] = "169901";
        $fila["Código de Anexo"] = "20606592150";
        $fila["Código de Centro de Costo"] = "";
        $fila["Debe / Haber"] = "D";
        $fila["Importe Original"] = $suma_televentas_ingreso;
        $fila["Importe en Dólares"] = "";
        $fila["Importe en Soles"] = "";
        $fila["Tipo de Documento"] = "ME";
        $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". date("y",strtotime($_POST["fecha_fin"]));
        $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
        $fila["Fecha de Vencimiento"] = $fila["Fecha de Comprobante"];
        $fila["Código de Area"] = "143";
        $fila["Glosa Detalle"] = "TORITO - COBRO DE BOLETOS";
        $fila["Código de Anexo Auxiliar"] = "A0002";
        $fila["Medio de Pago"] = "";
        $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
        $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
        $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
        $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
        $fila["Base Imponible Documento Referencia"] = "";
        $fila["IGV Documento Provisión"] = "";
        $fila["Tipo Referencia en estado MQ"] = "";
        $fila["Número Serie Caja Registradora"] = "";
        $fila["Fecha de Operación"] = "";
        $fila["Tipo de Tasa"] = "";
        $fila["Tasa Detracción/Percepción"] = "";
        $fila["Importe Base Detracción/Percepción Dólares"] = "";
        $fila["Importe Base Detracción/Percepción Soles"] = "";
        $fila["Tipo Cambio para 'F'"] = "";
        $fila["Importe de IGV sin derecho crédito fiscal"] = "";

        $reportTable[] = $fila ;

        $suma_d = 0;
        foreach ($reportTable as $key => $value) {
            if(isset($value["Debe / Haber"]) && $value["Debe / Haber"] == "D"){
                $suma_d = $suma_d + $value["Importe Original"];
            }
        }
        //fila H cuenta 469901
        $fila = [];
        $fila['campo'] = '';
        $fila["Sub Diario"] = "3920";
        $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";//$value["mes"]."0068";
        $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
        $fila["Código de Moneda"] = "MN";
        $fila["Glosa Principal"] = "TORITO - COBRO DE BOLETOS";
        $fila["Tipo de Cambio"] = $tipo_cambio;
        $fila["Tipo de Conversión"] = "V";
        $fila["Flag de Conversión de Moneda"] = "S";
        $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
        $fila["Cuenta Contable"] = "469901";
        $fila["Código de Anexo"] = "20602925553";
        $fila["Código de Centro de Costo"] = "";
        $fila["Debe / Haber"] = "H";
        $fila["Importe Original"] = $suma_d;
        $fila["Importe en Dólares"] = "";
        $fila["Importe en Soles"] = "";
        $fila["Tipo de Documento"] = "ME";
        $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". date("y",strtotime($_POST["fecha_fin"]));
        $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
        $fila["Fecha de Vencimiento"] = $fila["Fecha de Comprobante"];
        $fila["Código de Area"] = "143";
        $fila["Glosa Detalle"] = "Deposito y pago Kasnet";
        $fila["Código de Anexo Auxiliar"] = "A0002";
        $fila["Medio de Pago"] = "";
        $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
        $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
        $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
        $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
        $fila["Base Imponible Documento Referencia"] = "";
        $fila["IGV Documento Provisión"] = "";
        $fila["Tipo Referencia en estado MQ"] = "";
        $fila["Número Serie Caja Registradora"] = "";
        $fila["Fecha de Operación"] = "";
        $fila["Tipo de Tasa"] = "";
        $fila["Tasa Detracción/Percepción"] = "";
        $fila["Importe Base Detracción/Percepción Dólares"] = "";
        $fila["Importe Base Detracción/Percepción Soles"] = "";
        $fila["Tipo Cambio para 'F'"] = "";
        $fila["Importe de IGV sin derecho crédito fiscal"] = "";

        $reportTable[] = $fila ;

    }
     elseif ($post["sec_contable_export"]["tipo_reporte"] == "snack") {
        $reportTable = generar_cabecera();
        $post["group_by"] = "day";
        $table = getFinalTableReportesContables($post);
        $table = $table["finalTable"];

        $fila_agrupado_tienda = [];
        foreach ($table as $key => $value) {
            $fila_agrupado_tienda[$value["local_nombre"]][]= $value;
        }
        $table2 = [];
        foreach ($fila_agrupado_tienda as $key => $value) {
            $debe = 0;
            $haber = 0;
            $registro_tienda = [];
            foreach ($value as $index => $obj) {
                $debe += $obj["ds_17_in"]?$obj["ds_17_in"]:0;
                $haber += $obj["ds_17_out"]?$obj["ds_17_out"]:0;
            }
            $nueva_fila = $value[0];
            $nueva_fila["ds_17_in"] = $debe;
            $nueva_fila["ds_17_out"] = $haber;
            $table2[] = $nueva_fila;
        }
        foreach ($table2 as $key => $value) {
            $fila = [];
            $fila['campo']='';
            $fila["Sub Diario"] = "0320";
            $fila["Número de Comprobante"] = isset($_POST["numero_comprobante"])?$_POST["numero_comprobante"]:"";//$value["mes"]."0001";
            $fila["Fecha de Comprobante"] = date("d/m/Y",strtotime($_POST["fecha_fin"]));
            $fila["Código de Moneda"] = "MN";
            $fila["Glosa Principal"] = "COBRANZA SNACK";
            $fila["Tipo de Cambio"] = "";
            $fila["Tipo de Conversión"] = "V";
            $fila["Flag de Conversión de Moneda"] = "S";
            $fila["Fecha Tipo de Cambio"] = $fila["Fecha de Comprobante"];
            $fila["Cuenta Contable"] = "121201";
            $fila["Código de Anexo"] = "00000000002";
            $fila["Código de Centro de Costo"] = $value["cc_id"];
            $fila["Debe / Haber"] = "H";
            $fila["Importe Original"] = $value["ds_17_out"];//SNAcK salida
            $fila["Importe en Dólares"] = "";
            $fila["Importe en Soles"] = "";
            $fila["Tipo de Documento"] = "BV";
            $fila["Número de Documento"] = "";
            $fila["Fecha de Documento"] = $fila["Fecha de Comprobante"];
            $fila["Fecha de Vencimiento"] = "";
            $fila["Código de Area"] = "";
            $fila["Glosa Detalle"] = "COBRANZA SNACK";
            $fila["Código de Anexo Auxiliar"] = "C";
            $fila["Medio de Pago"] = "";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Fecha Documento Referencia"] = $fila["Fecha de Documento"];
            $fila["Nro Máq. Registradora Tipo Doc. Ref."] = "";
            $fila["Base Imponible Documento Referencia"] = "";
            $fila["IGV Documento Provisión"] = "";
            $fila["Tipo Referencia en estado MQ"] = "";
            $fila["Número Serie Caja Registradora"] = "";
            $fila["Fecha de Operación"] = "";
            $fila["Tipo de Tasa"] = "";
            $fila["Tasa Detracción/Percepción"] = "";
            $fila["Importe Base Detracción/Percepción Dólares"] = "";
            $fila["Importe Base Detracción/Percepción Soles"] = "";
            $fila["Tipo Cambio para 'F'"] = "";
            $fila["Importe de IGV sin derecho crédito fiscal"] = "";

            if($value["ds_17_out"] != "0"){
                $reportTable[] = $fila ;
            }

            $fila["Código de Anexo"] = "00000000002";//$value["cc_id"]."-";
            $fila["Debe / Haber"] = "D";
            $fila["Importe Original"] = $value["ds_17_in"];//SNAcK ingreso
            $fila["Tipo de Documento"] = "BV";
            $fila["Número de Documento"] = "0".date("m",strtotime($_POST["fecha_fin"]))."-". $value["ano"];
            $fila["Código de Area"] = "";
            $fila["Tipo de Documento de Referencia"] = $fila["Tipo de Documento"];
            $fila["Número de Documento Referencia"] = $fila["Número de Documento"];
            $fila["Código de Anexo Auxiliar"] = "C";
             
            if($value["ds_17_in"] != "0"){
                $reportTable[] = $fila ;
            }
        }
    } else {
        exit();
    }
    ///fin if  select reporte

    $filename = "reportes_contables_" . date("d-m-Y", strtotime($post["sec_contable_export"]["fecha_inicio"])) . "_al_" . date("d-m-Y", strtotime($post["sec_contable_export"]["fecha_fin"])) . "_" . date("Ymdhis") . ".xlsx";
    generar_excel($reportTable,$filename);  
}

echo json_encode($result);
