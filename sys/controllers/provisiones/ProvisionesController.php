<?php

class ProvisionesController
{
    //PROVISIONES 
    public function generacion_de_provisiones()
    {
        try {
            // $request = file_get_contents('php://input');
            // $request = json_decode($request, true);
            $model =  new Provision();
            $hoy = date('Y-m-d'); // FECHA ACTUAL EN FORMATO AÑOS-MES-DIA 

            // Restar un mes a la fecha actual
            $fecha_mes_pasado = date('Y-m-d', strtotime($hoy)); // strtotime('-5 month', strtotime($hoy));

            // Obtener el último día del mes pasado
            $ultimo_dia_mes_pasado = date('Y-m-t', strtotime($fecha_mes_pasado));
        
            $fecha_actual_inicio = '2023-02';
            $primer_dia_mes_siguiente = new DateTime($fecha_actual_inicio);
            $primer_dia_mes_siguiente->modify('first day of next month');
            $ultimo_dia_mes_actual = $primer_dia_mes_siguiente->modify('-1 day');
            $fecha_actual_inicio = $ultimo_dia_mes_actual->format('Y-m-d');
            // ENVIO DE CONTRATOS CON INDICE DE INLACION (IPC)
            $ids_de_contratos_con_ipc = '';
            $contratos_con_ipc = $model->obtener_contratos_con_ipc($ultimo_dia_mes_pasado);
            $contratos_con_ipc = mb_convert_encoding($contratos_con_ipc, 'UTF-8')['result'];
			$ids_de_contratos = array();
            $provisiones = '';
            $resultados_IPC = [];

            // var_dump($ultimo_dia_mes_pasado,$fecha_actual_inicio);
            if (sizeof($contratos_con_ipc) > 0) {
                $pagar_todos = 1; // 1: SI MARCA COMO PAGADO A LOS DEMAS ANTERIORES  
                for ($i =0 ; $i <sizeof($contratos_con_ipc) ; $i++) {
                    $contrato_id = $contratos_con_ipc[$i]['contrato_id'];
                    $provisiones = $this->calculo_proviciones_contables_con_ipc($contrato_id, $pagar_todos, $ultimo_dia_mes_pasado,$fecha_actual_inicio);
                     
                    
                    $ids_de_contratos[] = $contratos_con_ipc[$i]['contrato_id'];
                    if (!empty($provisiones)) {
                        $resultados_IPC[] = $provisiones;
                    }
                }
            }
            $ids_de_contratos_con_ipc = implode(",", $ids_de_contratos); // ID DE CONTRATO CON IPC 



            $contratos = $model->obtener_contratos($ultimo_dia_mes_pasado,$ids_de_contratos_con_ipc); // TRAER CONTRATOS EXCEPTO SI TIENEN INDICE DE INFLACION (IPC)
            
            // $incrementos = $model->obtener_datos_adendas_detalle($adenda_id);
            $contratos = mb_convert_encoding($contratos, 'UTF-8')['result'];
            $pagar_todos = 1; // 1: SI MARCA COMO PAGADO A LOS DEMAS ANTERIORES  
            $resultados = [];
            if (sizeof($contratos) > 0) {
                for ($i =0 ; $i <sizeof($contratos) ; $i++) {
                    $contrato_id = $contratos[$i]['contrato_id'];
                    //  $verificar = $model->verificar_provisiones_en_contato($contrato_id);
                    //  $verificar = $verificar['result'];
                    // if (sizeof($verificar) == 0) {

                         $provisiones = $this->calculo_proviciones_contables($contrato_id, $pagar_todos, $ultimo_dia_mes_pasado,$fecha_actual_inicio);
                        if (!empty($provisiones)) {
                            $resultados[] = $provisiones;
                        }
                     
                    // } else {
                    //      $povisiones = "falto";
                    //  }
                }
            }
            $fechaHora = date("Y-m-d H:i:s");
            $resultados_json = json_encode($resultados, JSON_PRETTY_PRINT);
            $resultados_json = str_replace("\\", "", $resultados_json);
            $resultados_json_ipc = json_encode($resultados_IPC, JSON_PRETTY_PRINT);
            $resultados_json_ipc = str_replace("\\", "", $resultados_json_ipc);
            file_put_contents('/var/www/html/logs/cron_provisiones_PN.log', 'Contratos ejecutados Sin IPC: '.$resultados_json. PHP_EOL, FILE_APPEND);
            file_put_contents('/var/www/html/logs/cron_provisiones_PN.log', 'Contratos ejecutados Con IPC: '.$resultados_json_ipc. PHP_EOL, FILE_APPEND);
            
            file_put_contents("/var/www/html/logs/cron_provisiones_PN.log", $fechaHora . "\n", FILE_APPEND);
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
          
            return json_encode($resultados, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
    public function generacion_de_provisiones_por_contrato($contrato_id){
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $contrato_id = $request['contrato_id'];

            $model =  new Provision();
            $hoy = date('Y-m-d'); // FECHA ACTUAL EN FORMATO AÑOS-MES-DIA 

            $fecha_mes_pasado = date('Y-m-d', strtotime('-5 month', strtotime($hoy)));
           
            $ultimo_dia_mes_pasado = date('Y-m-t'); // ULTIMO DIA DEL MES ACTUAL 
            // $fechaActual = new DateTime(); // Obtiene la fecha y hora actual
            // $fechaActual->modify('first day of last month'); // Se ajusta al primer día del mes anterior
            // $fecha_actual_inicio = $fechaActual->format('Y-m'); // Formatea la fecha en el formato deseado

            $hoy = date('Y-m-d'); // FECHA ACTUAL EN FORMATO AÑOS-MES-DIA 

            // Restar un mes a la fecha actual
            $fecha_actual_inicio = date('Y-m', strtotime('-1 month', strtotime($hoy)));

            $fecha_actual_eliminar = date('Y-m'); // Formatea la fecha en el formato deseado
            
          
                    // SI EXISTE ADENDA ENTONCES SE REALIZA EL CALCULO CON CASO ADENDAS
                    $contratos = $model->eliminar_provisiones_por_contato($contrato_id,$fecha_actual_eliminar); // EL EFECTO DE UNA ADENDA ES ELIMINAR LA PROVISION PARA HACER NUEVAMENTE EL CALCULO CON LOS NUEVOS VALORES 


                    $contratos_con_ipc = $model->obtener_contratos_con_ipc($ultimo_dia_mes_pasado,$contrato_id);
                    $contratos_con_ipc = mb_convert_encoding($contratos_con_ipc, 'UTF-8')['result'];
                    $pagar_todos = 1; // 1: SI MARCA COMO PAGADO A LOS DEMAS ANTERIORES  

                    if (sizeof($contratos_con_ipc) > 0) {
                        $provisiones = $this->calculo_proviciones_contables_con_ipc($contrato_id, $pagar_todos, $ultimo_dia_mes_pasado,$hoy);
                    }else{
                        $provisiones = $this->calculo_proviciones_contables($contrato_id, $pagar_todos, $ultimo_dia_mes_pasado,$hoy);

                    }
                
               return json_encode($provisiones, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }


    }
    public function calculo_proviciones_contables($contrato_id,$pagar_todos,$ultimo_dia_mes_pasado,$fecha_actual_inicio)
    {
        try {
            // $request = file_get_contents('php://input');
            // $request = json_decode($request, true);
            // $contrato_id = $request['contrato_id'];
            // $ultimo_dia_mes_pasado = $request['ultimo_dia_mes_pasado'];
            // $pagar_todos = 1; 

            $model_provision =  new Provision();
            // $model =  new ContratoArrendamiento();
            $bol = false;
            $array_adelantos = [];
            $adelantos_array    = [];
            $datos = $model_provision->obtener_datos_generales_para_provision($contrato_id, $ultimo_dia_mes_pasado);
            // ADELLANTOS
            $adelantos =  $model_provision->obtener_datos_adelantos($contrato_id)['result'];
            
            // INCREMENTOS
            $incrementos_data = $model_provision->obtener_datos_incrementos($contrato_id)['result'];
            $num_incrementos_data = $model_provision->obtener_datos_incrementos($contrato_id)['num_incrementos'];
            $datos = mb_convert_encoding($datos, 'UTF-8');

            $data_contratos = $datos['result'][0];
            $dia_pago = $data_contratos['dia_de_pago_id']!=null?$data_contratos['dia_de_pago_id']:0; // asignamos 0 a dia de pago 
            if ($data_contratos['nombre'] != null || $data_contratos['num_ruc'] != null) {
                $carta_de_instruccion_id = $data_contratos['carta_de_instruccion_id'];

                $tipo_moneda_id = $data_contratos['tipo_moneda_id'];
                // LLENAR GARNATIAS 
                // TIPO DE PROGRMAMACION : ANTICIPOS GARANTIAS
                $provision_unic_anticipo = array(
                    'contrato_id' => $contrato_id,
                    'empresa_suscribe_id' => 0,
                    'renta_bruta' => round($data_contratos['monto_renta'], 2),
                    'importe' => round($data_contratos['monto_renta'], 2),
                    'num_cuota' => 0,
                    'periodo_inicio' => '2000-05-01',
                    'periodo_fin_tmp' => '2000-05-01',
                    'tipo_moneda_id' => $tipo_moneda_id,
                    'dia_de_pago' => $dia_pago ,
                    'fecha_inicio' => $data_contratos['fecha_inicio'],
                    'fecha_fin' => $data_contratos['fecha_fin'],
                    'condicion_economica_id' => $data_contratos['condicion_economica_id'],
                    'tipo_moneda_id' => $data_contratos['tipo_moneda_id'],
                    'tipo_anticipo_id' => '1',
                    'num_adelanto_id' => null,
                    'monto_renta' => round($data_contratos['monto_renta'], 2),
                    'fecha_actual' => $data_contratos['fecha_inicio'],
                    'tipo_id' => 1,
                    'anio' => 0,
                    'mes' => 0,
                    'estado_pago' => 3,
                    'created_at' =>  date('Y-m-d H:i:s'),
                    'status'  => 1,
                    'valor_incremento'  => 0

                );
                // VALIDAR SI EL ANTICIPO YA ETSA REGISTRADO
                $anticipos_bool = $model_provision->validar_existen_anticipos($contrato_id,-1)['anticipos_bool'];
                if(!$anticipos_bool){
                    $provisiones[] = $provision_unic_anticipo;
                }
               
                // FIN LLENAR GARNTIAS 

                // OBTENER ADEALNTOS
                $datetime_inicio = new DateTime($data_contratos['fecha_inicio']);
                
                // INICIO LLENAR INCREMENTOS 
                $impuesto_a_la_renta_id = $data_contratos['impuesto_a_la_renta_id'];

                $renta = $data_contratos['monto_renta'];
                 // INICIO INICIALIZACION DE VARIABLES
                // $user_id = $login?$login['id']:null;

                $num_dias_excedentes = 0;
                $incrementos = 0;
                $descuento = 0;
                $tipo_orden_id = 1;

                // FIN INICIALIZACION DE VARIABLES
                $datetime_inicio2 = new DateTime($data_contratos['fecha_inicio']);
                $datetime_fin = new DateTime($data_contratos['fecha_fin']);

                $dia_de_mes__inicio = $datetime_inicio->format('d');
                if ($dia_de_mes__inicio  == '29') {
                    $bol = true;

                    $datetime_inicio->setDate($datetime_inicio->format('Y'), $datetime_inicio->format('m'), 28);
                    $datetime_inicio2->setDate($datetime_inicio2->format('Y'), $datetime_inicio2->format('m'), 28);
                }
                // var_dump($datetime_inicio->format('Y-m-d'));
                $intervalo = $datetime_fin->diff($datetime_inicio);

                // $datetime_inicio->modify('+1 day');
                $intervalo_meses = $intervalo->format("%m");
                $intervalo_anios = $intervalo->format("%y") * 12;

                $intervalo_meses_final = $intervalo_meses + $intervalo_anios;


                // VALIDAMOS SI EL DIA LA FECHA DE INICIO DE CONTRATO ES MAYOR QUE 18 
                $add_dia = 0;

                if ($dia_de_mes__inicio >= 18) {
                    //$datetime_inicio->modify('+1 month');
                    $datetime_inicio2->modify('+1 month');
                    if ($bol == false) {
                        $add_dia = 1;
                    }
                    $fecha_actual_inicio = date('Y-m', strtotime('-1 month', strtotime($fecha_actual_inicio)));

                } else {
                    $add_dia = 1;
                    $fecha_actual_inicio = date('Y-m', strtotime('-2 month', strtotime($fecha_actual_inicio)));

                }

                // CALCULO DE ADELANTOS 
                if (sizeof($adelantos) > 0) {
                    for ($i = 0; $i < sizeof($adelantos); $i++) {
                        $adelantos_array[$i] = $adelantos[$i]['num_periodo'];
                    }
                }


                // ACTUALIZAR VALORES DE ADELANTOS EN CASO SEAN X,Y ó Z
                $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'x'), $intervalo_meses_final - 2));
                $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'y'), $intervalo_meses_final - 1));
                $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'z'), $intervalo_meses_final));
                $num_cuota = 1;
                $registra_provision = 0;
                $provisiones = [];
                $valor = 0;
                while ($num_cuota <= $intervalo_meses_final + $add_dia ||  $registra_provision == 2) {
                    
                        // INICIO PERIODO INICIO Y FIN
                        $periodo_inicio = $datetime_inicio->format('Y-m-d');
                        $anio_mes_actual =  $datetime_inicio2->format('Y-m');
                        $anio_actual =  $datetime_inicio2->format('Y');  // usado para calculo con indice de inflacion
                        $mes_actual =  $datetime_inicio2->format('m');  // usado para calculo con indice de inflacion 


                        $datetime_inicio->modify('+1 month');

                        $intervalo_dias_excedente = $datetime_fin->diff($datetime_inicio);
                        $num_dias_excedentes = $intervalo_dias_excedente->format('%R%a');
                        if ($num_dias_excedentes > 0) {
                            $periodo_fin = $datetime_fin->format('Y-m-d');
                            $descuento = $renta - (($renta * $num_dias_excedentes) / 30);
                            // $datetime_inicio->modify('-1 day');
                            // $periodo_fin_tmp = $datetime_inicio->format('Y-m-d');
                            // break;
                        } else {
                            $datetime_inicio->modify('-1 day');
                            $periodo_fin_tmp = $datetime_inicio->format('Y-m-d');
                        }
                        // FIN PERIODO INICIO Y FIN


                        // INICIO INCREMENTOS
                        $contador_incremento_a_la_renta = 0;

                        // ALGORITMO PARA CALCULAR INCREMETOS 
                        if ($num_incrementos_data > 0) {
                            for ($i = 0; $i < $num_incrementos_data; $i++) {
                                $valor = $incrementos_data[$i]["valor"];
                                $tipo_valor = $incrementos_data[$i]["tipo_valor_id"];
                                $tipo_continuidad = $incrementos_data[$i]["tipo_continuidad_id"];
                                $a_partir_del_anio_en_meses = (($incrementos_data[$i]["a_partir_del_año"] - 1) * 12) + 1;
                                // var_dump($meses[$mes_actual]." ".$anio_actual." ".$valor." ajust: ".$ajuste_inflacion." mes actual ".$mes_actual." año".$anio_actual." ipc1 ".$ipc_1[0]['indice'] ." ipc2 ".$ipc_2[0] ['indice'] );

                                // if($ajuste_inflacion > $valor){  // si el indice de inflacion es mayor a el porcentaje de incremento : se toma el mayor 
                                //     $valor = $ajuste_inflacion ;
                                // } 
                                if ($tipo_continuidad == 1) { // EL
                                    if ($num_cuota == $a_partir_del_anio_en_meses) {
                                        if ($contador_incremento_a_la_renta == 0) {
                                            $renta = $renta + $incrementos;
                                            $incrementos = 0;
                                            $contador_incremento_a_la_renta++;
                                        }

                                        if ($tipo_valor == 1) {

                                            $incrementos += $valor;
                                        } else if ($tipo_valor == 2) {

                                            $incrementos += ($renta * $valor) / 100;
                                        }
                                    }
                                    if ($num_cuota == ($a_partir_del_anio_en_meses + 12)) {
                                        $renta = $renta + $incrementos;
                                        $incrementos = 0;
                                    }
                                } elseif ($tipo_continuidad == 2 || $tipo_continuidad == 3) { // ANUAL A PARTIR DEL
                                    for ($j = $a_partir_del_anio_en_meses; $j <= $num_cuota; $j += 12) {

                                        if ($tipo_valor == 1 && $num_cuota == $j && $num_cuota != 1) {
                                            // var_dump(" $num_cuota == $j && $num_cuota == $a_partir_del_anio_en_meses");

                                            if ($contador_incremento_a_la_renta == 0) {

                                                $renta = $renta + $incrementos;
                                                $incrementos = 0;
                                                $contador_incremento_a_la_renta++;
                                            }
                                            $incrementos += $valor;
                                        }
                                        // var_dump("$tipo_valor == 2 && $num_cuota == $j && $num_cuota == $a_partir_del_anio_en_meses");

                                        if ($tipo_valor == 2 && $num_cuota == $j && $num_cuota == $a_partir_del_anio_en_meses)
                                        // if ($tipo_valor == 2 && $num_cuota == $j) 
                                        {
                                            if ($contador_incremento_a_la_renta == 0) {

                                                $renta = $renta + $incrementos;
                                                $incrementos = 0;
                                                $contador_incremento_a_la_renta++;
                                            }

                                            $incrementos += ($renta * $valor) / 100;
                                        }
                                    }
                                }
                            }
                        }


                        // FIN INCREMENTOS

                        $total = ($renta + $incrementos);
                        $renta_a_pagar = 0;
                        // INICIO IMPUESTO A LA RENTA
                        $registra_provision = 0;
                        if ($impuesto_a_la_renta_id == '1' || $impuesto_a_la_renta_id == '2') {
                            $registra_provision = 0;


                            // if($carta_de_instruccion_id == '1') {
                            // ESTE ARTIFICIO ES PARA TOMAR TODAS LAS PROVISIONES ANTERIORES COMO PAGADAS(PROCESADAS)
                            $fecha_actual = date('Y-m-d');
                            // $nueva_fecha_actual = $dia_de_mes__inicio < 18 ?$fecha_actual :  date('Y-m-d', strtotime('-1 month', strtotime($fecha_actual)));
                            // POR MEJORAR 
                            if ($dia_de_mes__inicio < 18) {
                                $nueva_fecha_actual = $fecha_actual;
                            } else {
                                $nueva_fecha_actual =  date('Y-m-d', strtotime('-1 month', strtotime($fecha_actual)));
                            }
                            $anioMes1 = date('Y-m', strtotime($nueva_fecha_actual));
                            $anioMes2 = date('Y-m', strtotime($periodo_inicio));
                            $estado_pagado = 3; // esta pagado 
                            if (in_array($num_cuota, $adelantos_array)) { // SEGUN EL INDICADOR , 1: SI MARCA COMO PAGADO A LOS DEMAS ANTERIORES  
                                // NORMALES - ANTICIPOS 

                                $tipo_anticipo_id = 2;
                                $num_adelanto_id = $num_cuota;
                                $estado_pagado = 3; //$anioMes1  <= $anioMes2 ? 0 : 3;
                                $registra_provision = 1; // para que no registre anticipos si las provisiones ya fueron calculadas en otros meses(periodos) anteriores 


                            } else {  // CASO CONTRATRIO SE GENERAN CON NORMALIDAD , ES DECIR TODOS NO PAGADOS Y/O PAGADOS SEGUN ANTICIPOS 

                                if ($anioMes1  <= $anioMes2) {
                                    $estado_pagado =       0;
                                }
                                if (($anioMes2 > $fecha_actual_inicio &&  $anioMes2 <= $anioMes1)) {
                                    $registra_provision = 2;
                                }


                                // NORMALES - RENTA Y ANTICIPOS 
                                $tipo_anticipo_id = null;
                                $num_adelanto_id = null;
                            }
                            // validacion solo para provisiones mensales (comentar si se queire hacer desde más meses atras)
                            $registra_provision = date('Y-m') == $anio_mes_actual ?$registra_provision:0;

                            // PROVISIONES DE CUOTAS 
                            if ($impuesto_a_la_renta_id == 1) {
                                $impuesto_a_la_renta = round($total * 0.05); //  PEDIDO DE CONTABILIDAD SE REDONDEA A ENTEROS
                                $renta_bruta = $total;

                                if ($carta_de_instruccion_id == 1) {
                                    $renta_neta = $total - $impuesto_a_la_renta;
                                    // TIPO DE PROGRMAMACION : IMPUESTO A LA RENTA 
                                    $provision_unic = array(
                                        // 'estado_pagado'=> $estado_pagado,
                                        'contrato_id' => $contrato_id,
                                        'empresa_suscribe_id' => 0,
                                        'renta_bruta' => round($renta_bruta, 2),
                                        'importe' => round($impuesto_a_la_renta, 2),
                                        'num_cuota' => $num_cuota,
                                        'periodo_inicio' => $periodo_inicio,
                                        'periodo_fin_tmp' => $periodo_fin_tmp,
                                        'tipo_moneda_id' => $tipo_moneda_id,
                                        'dia_de_pago' =>  $dia_pago,
                                        'condicion_economica_id' => $data_contratos['condicion_economica_id'],
                                        'tipo_anticipo_id' =>  $tipo_anticipo_id,
                                        'num_adelanto_id' => $num_adelanto_id,
                                        'monto_renta' => $renta,
                                        'fecha_actual' => $anio_mes_actual,
                                        'total_calculado' => $total,
                                        'descuento_IR' => $impuesto_a_la_renta,
                                        'carta_de_instruccion_id' => $carta_de_instruccion_id == 1 ? 'SI' : 'NO',
                                        'posee_incrementos' => $incrementos,
                                        'valor_incremento'  => $valor,
                                        'tipo_id' => 3, // INDICA IMPUESTO A LA RENTA
                                        'estado_pago' => $estado_pagado,
                                        'anio' => $datetime_inicio2->format('Y'),
                                        'mes' => $datetime_inicio2->format('m'),
                                        'created_at' =>  date('Y-m-d H:i:s'),
                                        'status'  => 1,
                                        'ajuste_inflacion' => null,
                                        'centro_costos' => $data_contratos['cc_id']

                                    );
                                    if ($registra_provision == 1 || $registra_provision == 2) {
                                        $provisiones[] = $provision_unic;
                                    }
                                } elseif ($carta_de_instruccion_id == 2) {
                                    $renta_neta = $total;
                                }
                            } elseif ($impuesto_a_la_renta_id == 2) {
                                $impuesto_a_la_renta = round(($total * 1.05265) - $total); //  PEDIDO DE CONTABILIDAD SE REDONDEA A ENTEROS
                                $renta_bruta = $total + $impuesto_a_la_renta;

                                if ($carta_de_instruccion_id == 1) {
                                    $renta_neta = $total;
                                    // TIPO DE PROGRMAMACION : IMPUESTO A LA RENTA 
                                    $provision_unic = array(
                                        // 'estado_pagado'=> $estado_pagado,
                                        'contrato_id' => $contrato_id,
                                        'empresa_suscribe_id' => 0,
                                        'renta_bruta' => round($renta_bruta, 2),
                                        'importe' => round($impuesto_a_la_renta, 2),
                                        'num_cuota' => $num_cuota,
                                        'periodo_inicio' => $periodo_inicio,
                                        'periodo_fin_tmp' => $periodo_fin_tmp,
                                        'tipo_moneda_id' => $tipo_moneda_id,
                                        'dia_de_pago' =>  $dia_pago,
                                        'condicion_economica_id' => $data_contratos['condicion_economica_id'],
                                        'tipo_anticipo_id' =>  $tipo_anticipo_id,
                                        'num_adelanto_id' => $num_adelanto_id,
                                        'monto_renta' => $renta,
                                        'fecha_actual' => $anio_mes_actual,
                                        'total_calculado' => $total,
                                        'descuento_IR' => $impuesto_a_la_renta,
                                        'carta_de_instruccion_id' => $carta_de_instruccion_id == 1 ? 'SI' : 'NO',
                                        'posee_incrementos' => $incrementos,
                                        'valor_incremento'  => $valor,
                                        'tipo_id' => 3, // INDICA IMPUESTO A LA RENTA 
                                        'estado_pago' => $estado_pagado,
                                        'anio' => $datetime_inicio2->format('Y'),
                                        'mes' => $datetime_inicio2->format('m'),
                                        'created_at' =>  date('Y-m-d H:i:s'),
                                        'status'  => 1,
                                        'ajuste_inflacion' => null,
                                        'centro_costos' => $data_contratos['cc_id']

                                    );
                                    if ($registra_provision == 1 || $registra_provision == 2) {
                                        $provisiones[] = $provision_unic;
                                    }
                                } elseif ($carta_de_instruccion_id == 2) {
                                    $renta_neta = $total + $impuesto_a_la_renta;
                                }
                            }
                            // TIPO DE PROGRMAMACION : RENTA DE ALQUILER

                            $provision_unic = array(
                                // 'estado_pagado'=> $estado_pagado,
                                'contrato_id' => $contrato_id,
                                "anioMes1" => $anioMes1,
                                "anioMes2" => $anioMes2,
                                'empresa_suscribe_id' => 0,
                                'renta_bruta' => round($renta_bruta, 2),
                                'importe' => round($renta_neta, 2),
                                'num_cuota' => $num_cuota,
                                'periodo_inicio' => $periodo_inicio,
                                'periodo_fin_tmp' => $periodo_fin_tmp,
                                'tipo_moneda_id' => $tipo_moneda_id,
                                'dia_de_pago' =>  $dia_pago,
                                'condicion_economica_id' => $data_contratos['condicion_economica_id'],
                                'tipo_anticipo_id' =>  $tipo_anticipo_id,
                                'num_adelanto_id' => $num_adelanto_id,
                                'monto_renta' => $renta,
                                'fecha_actual' => $anio_mes_actual,
                                'total_calculado' => $total,
                                'descuento_IR' => $impuesto_a_la_renta,
                                'carta_de_instruccion_id' => $carta_de_instruccion_id == 1 ? 'SI' : 'NO',
                                'posee_incrementos' => $incrementos,
                                'valor_incremento'  => $valor,
                                'tipo_id' => 2,  // INDICA RENTA DE ALQUILER 
                                'estado_pago' => $estado_pagado,
                                'anio' => $datetime_inicio2->format('Y'),
                                'mes' => $datetime_inicio2->format('m'),
                                'created_at' =>  date('Y-m-d H:i:s'),
                                'status'  => 1,
                                'ajuste_inflacion' => null,
                                'centro_costos' => $data_contratos['cc_id']

                            );
                            if ($registra_provision == 1 || $registra_provision == 2) {
                                $fecha_actual_registrada = $anio_mes_actual;
                                $provisiones[] = $provision_unic;
                            }

                            // } 

                        } else {
                            $renta_a_pagar = $total;
                        }
                        // FIN IMPUESTO A LA RENTA
                        // FIN RENTA

                        $datetime_inicio->modify('+1 day');

                        if ($num_cuota > 84) {
                            break;
                        }
                        $datetime_inicio2->modify('+1 month');

                        $num_cuota++;





                }
                if(!empty($provisiones)){
                    $anticipos_bool = $model_provision->validar_existen_anticipos($contrato_id, $fecha_actual_registrada)['result'];
                    if ($anticipos_bool[0]['max_fecha'] < $fecha_actual_registrada || $anticipos_bool[0]['max_fecha']== NULL) {
                        $res = $model_provision->insertar_provision($provisiones)['status'];

                    } else{
                        $res = 0;
                    }

                }else{
                    // var_dump($registra_provision);
                }
            } else {
                // $correo['message'] = 'Enviar correo';
                $result['Message'] = "Enviar correo de falta beneficiario";
            }

            $data_centro_costos = array(
                'centro_costos' => $data_contratos['cc_id'],
                'tipo_provision' => 'Persona natural',
                'contrato_id' => $contrato_id,
                'provision' => $res==1 ?'Ejecutado...':'Ya existe provision'
            );
            

            return json_encode($data_centro_costos, JSON_UNESCAPED_UNICODE);

 

        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
    public function calculo_proviciones_contables_con_ipc ($contrato_id,$pagar_todos,$ultimo_dia_mes_pasado,$fecha_actual_inicio)
    {
        try {
            
            $model_provision =  new Provision();
            // $model =  new ContratoArrendamiento();
            $bol = false;
            $array_adelantos = [];
            $adelantos_array    = [];
            $datos = $model_provision->obtener_datos_generales_para_provision($contrato_id, $ultimo_dia_mes_pasado);
            // ADELLANTOS
            $adelantos =  $model_provision->obtener_datos_adelantos($contrato_id)['result'];
            
            // INCREMENTOS
            $incrementos_data = $model_provision->obtener_datos_incrementos($contrato_id)['result'];
            $num_incrementos_data = $model_provision->obtener_datos_incrementos($contrato_id)['num_incrementos'];
            $datos = mb_convert_encoding($datos, 'UTF-8');

            $data_contratos = $datos['result'][0];
            $dia_pago = $data_contratos['dia_de_pago_id']!=null?$data_contratos['dia_de_pago_id']:0; // asignamos 0 a dia de pago 
            if ($data_contratos['nombre'] != null || $data_contratos['num_ruc'] != null) {
                $carta_de_instruccion_id = $data_contratos['carta_de_instruccion_id'];

                $tipo_moneda_id = $data_contratos['tipo_moneda_id'];
                // LLENAR GARNATIAS 
                // TIPO DE PROGRMAMACION : ANTICIPOS GARANTIAS
                $provision_unic_anticipo = array(
                    'contrato_id' => $contrato_id,
                    'empresa_suscribe_id' => 0,
                    'renta_bruta' => round($data_contratos['monto_renta'], 2),
                    'importe' => round($data_contratos['monto_renta'], 2),
                    'num_cuota' => 0,
                    'periodo_inicio' => '2000-05-01',
                    'periodo_fin_tmp' => '2000-05-01',
                    'tipo_moneda_id' => $tipo_moneda_id,
                    'dia_de_pago' => $dia_pago ,
                    'fecha_inicio' => $data_contratos['fecha_inicio'],
                    'fecha_fin' => $data_contratos['fecha_fin'],
                    'condicion_economica_id' => $data_contratos['condicion_economica_id'],
                    'tipo_moneda_id' => $data_contratos['tipo_moneda_id'],
                    'tipo_anticipo_id' => '1',
                    'num_adelanto_id' => null,
                    'monto_renta' => round($data_contratos['monto_renta'], 2),
                    'fecha_actual' => $data_contratos['fecha_inicio'],
                    'tipo_id' => 1,
                    'anio' => 0,
                    'mes' => 0,
                    'estado_pago' => 3,
                    'created_at' =>  date('Y-m-d H:i:s'),
                    'status'  => 1,
                    'ajuste_inflacion' => 0,
                    'valor_incremento'  => 0,

                );
                  // VALIDAR SI EL ANTICIPO YA ETSA REGISTRADO
                  $anticipos_bool = $model_provision->validar_existen_anticipos($contrato_id,-1)['result'];
                  if(!$anticipos_bool){
                      $provisiones[] = $provision_unic_anticipo;
                  }
                 
                // FIN LLENAR GARNTIAS 

                // OBTENER ADEALNTOS
                $datetime_inicio = new DateTime($data_contratos['fecha_inicio']);
                
                // INICIO LLENAR INCREMENTOS 
                $impuesto_a_la_renta_id = $data_contratos['impuesto_a_la_renta_id'];

                $renta = $data_contratos['monto_renta'];
                 // INICIO INICIALIZACION DE VARIABLES
                // $user_id = $login?$login['id']:null;

                $num_dias_excedentes = 0;
                $incrementos = 0;
                $descuento = 0;
                $tipo_orden_id = 1;
                $valor_incremento_db  = 0;
                // FIN INICIALIZACION DE VARIABLES
                $datetime_inicio2 = new DateTime($data_contratos['fecha_inicio']);
                $datetime_fin = new DateTime($data_contratos['fecha_fin']);

                $dia_de_mes__inicio = $datetime_inicio->format('d');
                if ($dia_de_mes__inicio  == '29') {
                    $bol = true;

                    $datetime_inicio->setDate($datetime_inicio->format('Y'), $datetime_inicio->format('m'), 28);
                    $datetime_inicio2->setDate($datetime_inicio2->format('Y'), $datetime_inicio2->format('m'), 28);
                }
                // var_dump($datetime_inicio->format('Y-m-d'));
                $intervalo = $datetime_fin->diff($datetime_inicio);

                // $datetime_inicio->modify('+1 day');
                $intervalo_meses = $intervalo->format("%m");
                $intervalo_anios = $intervalo->format("%y") * 12;

                $intervalo_meses_final = $intervalo_meses + $intervalo_anios;



                // VALIDAMOS SI EL DIA LA FECHA DE INICIO DE CONTRATO ES MAYOR QUE 18 
                $add_dia = 0;

                if ($dia_de_mes__inicio >= 18) {
                    //$datetime_inicio->modify('+1 month');
                    $datetime_inicio2->modify('+1 month');
                    if ($bol == false) {
                        $add_dia = 1;
                    }
                    $fecha_actual_inicio = date('Y-m', strtotime('-2 month', strtotime($fecha_actual_inicio)));

                } else {
                    $add_dia = 1;
                    $fecha_actual_inicio = date('Y-m', strtotime('-1 month', strtotime($fecha_actual_inicio)));

                }

                // CALCULO DE ADELANTOS 
                if (sizeof($adelantos) > 0) {
                    for ($i = 0; $i < sizeof($adelantos); $i++) {
                        $adelantos_array[$i] = $adelantos[$i]['num_periodo'];
                    }
                }


                // ACTUALIZAR VALORES DE ADELANTOS EN CASO SEAN X,Y ó Z
                $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'x'), $intervalo_meses_final - 2));
                $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'y'), $intervalo_meses_final - 1));
                $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'z'), $intervalo_meses_final));
                $num_cuota = 1;$provisiones = [];
                $registra_provision = 0;$valor = 0;
                while ($num_cuota <= $intervalo_meses_final + $add_dia ||  $registra_provision == 2) {
                    
                        // ******* INICIO PERIODO INICIO Y FIN
                        $periodo_inicio = $datetime_inicio->format('Y-m-d');
                        $anio_mes_actual =  $datetime_inicio2->format('Y-m');
                        $anio_actual =  $datetime_inicio2->format('Y');  // usado para calculo con indice de inflacion
                        $mes_actual =  $datetime_inicio2->format('m');  // usado para calculo con indice de inflacion 
                        //VALIDAMOS SI TIENE IPC 
                        $meses = array(
                            '01' => "Enero", '02' => "Febrero", '03' => "Marzo", '04' => "Abril",
                            '05' => "Mayo", '06' => "Junio", '07' => "Julio",   '08' => "Agosto",
                            '09' => "Septiembre", '10' => "Octubre", '11' => "Noviembre", '12' => "Diciembre"
                        );
                        $anio_anterior = $anio_actual - 1;



                        $ajuste_inflacion = 0;
                        $ipc_1 = $model_provision->obtener_ipc_del_mes($meses[$mes_actual], $anio_actual)['result'];
                        $ipc_2 = $model_provision->obtener_ipc_del_mes($meses[$mes_actual], $anio_anterior)['result'];
                        if (isset($ipc_1[0]) && isset($ipc_2[0])) {
                            $ipc_1_indice = $ipc_1[0]['indice'];
                            $ipc_2_indice = $ipc_2[0]['indice'];
                            $ajuste_inflacion_ = round($ipc_1_indice / $ipc_2_indice, 3);
                            $ajuste_inflacion = ($ajuste_inflacion_ - 1) * 100;
                            // var_dump($contrato_id."  ".$meses[$mes_actual]."  ".$ajuste_inflacion_," ".$ajuste_inflacion." ");

                            // var_dump($meses[$mes_actual]." ".$anio_actual." ".$ipc_1_indice."  ".$anio_anterior." ".$ipc_2_indice." ajust: ".$ajuste_inflacion);
                        }

                        $datetime_inicio->modify('+1 month');

                        $intervalo_dias_excedente = $datetime_fin->diff($datetime_inicio);
                        $num_dias_excedentes = $intervalo_dias_excedente->format('%R%a');
                        if ($num_dias_excedentes > 0) {
                            $periodo_fin = $datetime_fin->format('Y-m-d');
                            $descuento = $renta - (($renta * $num_dias_excedentes) / 30);
                            // break;
                        } else {
                            $datetime_inicio->modify('-1 day');
                            $periodo_fin_tmp = $datetime_inicio->format('Y-m-d');
                        }
                        // FIN PERIODO INICIO Y FIN


                        // INICIO INCREMENTOS
                        $contador_incremento_a_la_renta = 0;
                        // $valor_incremento_db = 0;
                        // // ALGORITMO PARA CALCULAR INCREMETOS 
                        if ($num_incrementos_data > 0) {
                            for ($i = 0; $i < $num_incrementos_data; $i++) {
                                $valor = $incrementos_data[$i]["valor"];
                                $tipo_valor = $incrementos_data[$i]["tipo_valor_id"];
                                $tipo_continuidad = $incrementos_data[$i]["tipo_continuidad_id"];
                                $a_partir_del_anio_en_meses = (($incrementos_data[$i]["a_partir_del_año"] - 1) * 12) + 1;


                                if ($tipo_continuidad == 1) { // EL
                                    if ($num_cuota == $a_partir_del_anio_en_meses) {
                                        if ($contador_incremento_a_la_renta == 0) {
                                            $renta = $renta + $incrementos;
                                            $incrementos = 0;
                                            $contador_incremento_a_la_renta++;
                                        }

                                        if ($tipo_valor == 1) {

                                            $incrementos += $valor;
                                        } else if ($tipo_valor == 2) {

                                            if ($ajuste_inflacion > $valor) {  // si el indice de inflacion es mayor a el porcentaje de incremento : se toma el mayor 
                                                $valor  = $ajuste_inflacion;
                                            }
                                            $valor_incremento_db = $valor;

                                            // var_dump($contrato_id."   $tipo_valor   ".$anio_actual." ".$meses[$mes_actual]." ".$ajuste_inflacion." incre ".$renta." ".$valor." ".$incrementos);

                                            $incrementos += ($renta * $valor) / 100;
                                        }
                                    }
                                    if ($num_cuota == ($a_partir_del_anio_en_meses + 12)) {
                                        $renta = $renta + $incrementos;
                                        $incrementos = 0;
                                    }
                                } elseif ($tipo_continuidad == 2 || $tipo_continuidad == 3) { // ANUAL A PARTIR DEL
                                    for ($j = $a_partir_del_anio_en_meses; $j <= $num_cuota; $j += 12) {

                                        if ($tipo_valor == 1 && $num_cuota == $j) {

                                            if ($contador_incremento_a_la_renta == 0) {

                                                $renta = $renta + $incrementos;
                                                $incrementos = 0;
                                                $contador_incremento_a_la_renta++;
                                            }

                                            $incrementos += $valor;
                                        }

                                        if ($tipo_valor == 2 && $num_cuota == $j && $num_cuota == $a_partir_del_anio_en_meses) {

                                            if ($contador_incremento_a_la_renta == 0) {

                                                $renta = $renta + $incrementos;
                                                $incrementos = 0;
                                                $contador_incremento_a_la_renta++;
                                            }
                                            if ($ajuste_inflacion > $valor) {  // si el indice de inflacion es mayor a el porcentaje de incremento : se toma el mayor 
                                                $valor  = $ajuste_inflacion;
                                            }
                                            $valor_incremento_db = $valor;

                                            // var_dump($contrato_id."   $tipo_valor   ".$anio_actual." ".$meses[$mes_actual]." ".$ajuste_inflacion." incre ".$renta." ".$valor." ".$incrementos);

                                            $incrementos += ($renta * $valor) / 100;


                                            // var_dump($incrementos." ".$contrato_id);
                                            // var_dump($num_incrementos_data." $j  renta  ".$reKnta." ".$meses[$mes_actual]." ".$ajuste_inflacion." valor ".$valor." incremento ".$incrementos );

                                        }
                                    }
                                }
                            }
                        }


                        // FIN INCREMENTOS

                        $total = ($renta + $incrementos);
                        $renta_a_pagar = 0;
                        // INICIO IMPUESTO A LA RENTA
                        $registra_provision = 0;
                        if ($impuesto_a_la_renta_id == '1' || $impuesto_a_la_renta_id == '2') {
                            $registra_provision = 0;


                            // if($carta_de_instruccion_id == '1') {
                            // ESTE ARTIFICIO ES PARA TOMAR TODAS LAS PROVISIONES ANTERIORES COMO PAGADAS(PROCESADAS)
                            $fecha_actual = date('Y-m-d');

                            // POR MEJORAR 
                            if ($dia_de_mes__inicio < 18) {
                                $nueva_fecha_actual = $fecha_actual;
                            } else {
                                $nueva_fecha_actual =  date('Y-m-d', strtotime('-1 month', strtotime($fecha_actual)));
                            }

                            $anioMes1 = date('Y-m', strtotime($nueva_fecha_actual));
                            $anioMes2 = date('Y-m', strtotime($periodo_inicio));
                            $estado_pagado = 3; // esta pagado 
                            if (in_array($num_cuota, $adelantos_array)) { // SEGUN EL INDICADOR , 1: SI MARCA COMO PAGADO A LOS DEMAS ANTERIORES  
                                // NORMALES - ANTICIPOS 
                                $tipo_anticipo_id = 2;
                                $num_adelanto_id = $num_cuota;
                                $estado_pagado = 3; //$anioMes1  <= $anioMes2 ? 0 : 3;
                                $registra_provision = 1;
                            } else {  // CASO CONTRATRIO SE GENERAN CON NORMALIDAD , ES DECIR TODOS NO PAGADOS Y/O PAGADOS SEGUN ANTICIPOS 

                                if ($anioMes1  <= $anioMes2) {
                                    $estado_pagado =       0;
                                }
                                // var_dump($anioMes2."  ".$fecha_actual_inicio." ".$anioMes1."   ".$nueva_fecha_actual);

                                if (($anioMes2 > $fecha_actual_inicio &&  $anioMes2 <= $anioMes1)) {
                                    // var_dump($anioMes2." inseroooo ".$fecha_actual_inicio." ".$anioMes1);

                                    $registra_provision = 2;
                                }


                                // NORMALES - RENTA Y ANTICIPOS 
                                $tipo_anticipo_id = null;
                                $num_adelanto_id = null;
                            }

                            // validacion solo para provisiones mensales (comentar si se queire hacer desde más meses atras)
                            $registra_provision = date('Y-m') == $anio_mes_actual ?$registra_provision:0;

                            // PROVISIONES DE CUOTAS 

                            if ($impuesto_a_la_renta_id == 1) {
                                $impuesto_a_la_renta = round($total * 0.05); //  PEDIDO DE CONTABILIDAD SE REDONDEA A ENTEROS
                                $renta_bruta = $total;

                                if ($carta_de_instruccion_id == 1) {
                                    $renta_neta = $total - $impuesto_a_la_renta;
                                    // TIPO DE PROGRMAMACION : IMPUESTO A LA RENTA 
                                    $provision_unic = array(
                                        // 'estado_pagado'=> $estado_pagado,
                                        'contrato_id' => $contrato_id,
                                        'empresa_suscribe_id' => 0,
                                        'renta_bruta' => round($renta_bruta, 2),
                                        'importe' => round($impuesto_a_la_renta, 2),
                                        'num_cuota' => $num_cuota,
                                        'periodo_inicio' => $periodo_inicio,
                                        'periodo_fin_tmp' => $periodo_fin_tmp,
                                        'tipo_moneda_id' => $tipo_moneda_id,
                                        'dia_de_pago' =>  $dia_pago,
                                        'condicion_economica_id' => $data_contratos['condicion_economica_id'],
                                        'tipo_anticipo_id' =>  $tipo_anticipo_id,
                                        'num_adelanto_id' => $num_adelanto_id,
                                        'monto_renta' => $renta, //$data_contratos['monto_renta'],
                                        'fecha_actual' => $anio_mes_actual,
                                        'total_calculado' => $total,
                                        'descuento_IR' => $impuesto_a_la_renta,
                                        'carta_de_instruccion_id' => $carta_de_instruccion_id == 1 ? 'SI' : 'NO',
                                        'posee_incrementos' => $incrementos,
                                        'valor_incremento'  => $valor,
                                        'tipo_id' => 3, // INDICA IMPUESTO A LA RENTA
                                        'estado_pago' => $estado_pagado,
                                        'anio' => $datetime_inicio2->format('Y'),
                                        'mes' => $datetime_inicio2->format('m'),
                                        'created_at' =>  date('Y-m-d H:i:s'),
                                        'status'  => 1,
                                        'ajuste_inflacion' => $valor_incremento_db
                                    );
                                    if ($registra_provision == 1 || $registra_provision == 2) {
                                        $provisiones[] = $provision_unic;
                                    }
                                } elseif ($carta_de_instruccion_id == 2) {
                                    $renta_neta = $total;
                                }
                            } elseif ($impuesto_a_la_renta_id == 2) {
                                $impuesto_a_la_renta = round(($total * 1.05265) - $total); //  PEDIDO DE CONTABILIDAD SE REDONDEA A ENTEROS
                                $renta_bruta = $total + $impuesto_a_la_renta;

                                if ($carta_de_instruccion_id == 1) {
                                    $renta_neta = $total;
                                    // TIPO DE PROGRMAMACION : IMPUESTO A LA RENTA 
                                    $provision_unic = array(
                                        // 'estado_pagado'=> $estado_pagado,
                                        'contrato_id' => $contrato_id,
                                        'empresa_suscribe_id' => 0,
                                        'renta_bruta' => round($renta_bruta, 2),
                                        'importe' => round($impuesto_a_la_renta, 2),
                                        'num_cuota' => $num_cuota,
                                        'periodo_inicio' => $periodo_inicio,
                                        'periodo_fin_tmp' => $periodo_fin_tmp,
                                        'tipo_moneda_id' => $tipo_moneda_id,
                                        'dia_de_pago' =>  $dia_pago,
                                        'condicion_economica_id' => $data_contratos['condicion_economica_id'],
                                        'tipo_anticipo_id' =>  $tipo_anticipo_id,
                                        'num_adelanto_id' => $num_adelanto_id,
                                        'monto_renta' => $renta, //$data_contratos['monto_renta'],
                                        'fecha_actual' => $anio_mes_actual,
                                        'total_calculado' => $total,
                                        'descuento_IR' => $impuesto_a_la_renta,
                                        'carta_de_instruccion_id' => $carta_de_instruccion_id == 1 ? 'SI' : 'NO',
                                        'posee_incrementos' => $incrementos,
                                        'valor_incremento'  => $valor,
                                        'tipo_id' => 3, // INDICA IMPUESTO A LA RENTA 
                                        'estado_pago' => $estado_pagado,
                                        'anio' => $datetime_inicio2->format('Y'),
                                        'mes' => $datetime_inicio2->format('m'),
                                        'created_at' =>  date('Y-m-d H:i:s'),
                                        'status'  => 1,
                                        'ajuste_inflacion' => $valor_incremento_db
                                    );
                                    if ($registra_provision == 1 || $registra_provision == 2) {
                                        $provisiones[] = $provision_unic;
                                    }
                                } elseif ($carta_de_instruccion_id == 2) {
                                    $renta_neta = $total + $impuesto_a_la_renta;
                                }
                            }
                            // TIPO DE PROGRMAMACION : RENTA DE ALQUILER

                            $provision_unic = array(
                                // 'estado_pagado'=> $estado_pagado,
                                'contrato_id' => $contrato_id,
                                "anioMes1" => $anioMes1,
                                "anioMes2" => $anioMes2,
                                'empresa_suscribe_id' => 0,
                                'renta_bruta' => round($renta_bruta, 2),
                                'importe' => round($renta_neta, 2),
                                'num_cuota' => $num_cuota,
                                'periodo_inicio' => $periodo_inicio,
                                'periodo_fin_tmp' => $periodo_fin_tmp,
                                'tipo_moneda_id' => $tipo_moneda_id,
                                'dia_de_pago' =>  $dia_pago,
                                'condicion_economica_id' => $data_contratos['condicion_economica_id'],
                                'tipo_anticipo_id' =>  $tipo_anticipo_id,
                                'num_adelanto_id' => $num_adelanto_id,
                                'monto_renta' => $renta, //$data_contratos['monto_renta'],
                                'fecha_actual' => $anio_mes_actual,
                                'total_calculado' => $total,
                                'descuento_IR' => $impuesto_a_la_renta,
                                'carta_de_instruccion_id' => $carta_de_instruccion_id == 1 ? 'SI' : 'NO',
                                'posee_incrementos' => $incrementos,
                                'valor_incremento'  => $valor,
                                'tipo_id' => 2,  // INDICA RENTA DE ALQUILER 
                                'estado_pago' => $estado_pagado,
                                'anio' => $datetime_inicio2->format('Y'),
                                'mes' => $datetime_inicio2->format('m'),
                                'created_at' =>  date('Y-m-d H:i:s'),
                                'status'  => 1,
                                'ajuste_inflacion' => $valor_incremento_db
                            );
                            if ($registra_provision == 1 || $registra_provision == 2) {
                                $fecha_actual_registrada = $anio_mes_actual;
                                $provisiones[] = $provision_unic;
                            }

                            // } 

                        } else {
                            $renta_a_pagar = $total;
                        }
                        // FIN IMPUESTO A LA RENTA
                        // FIN RENTA

                        $datetime_inicio->modify('+1 day');

                        if ($num_cuota > 84) {
                            break;
                        }
                        $datetime_inicio2->modify('+1 month');

                        $num_cuota++;

                    // FIN RESET VARIALES LOCALES



                }
                if(!empty($provisiones)){
                    $anticipos_bool = $model_provision->validar_existen_anticipos($contrato_id, $anio_mes_actual)['result'];
                    if ($anticipos_bool[0]['max_fecha'] < $fecha_actual_registrada || $anticipos_bool[0]['max_fecha']== NULL) {
                        $res = $model_provision->insertar_provision($provisiones)['status'];

                    }else{
                        $res = 0;
                    }
                    // var_dump($provisiones);

                }else{
                    // var_dump($registra_provision);
                }
            } else {
                // $correo['message'] = 'Enviar correo';
                $result['Message'] = "Enviar correo de falta beneficiario";
            }
            $data_centro_costos = array(
                'centro_costos' => $data_contratos['cc_id'],
                'tipo_provision' => 'Persona natural',
                'contrato_id' => $contrato_id,
                'provision' => $res==1 ?'Ejecutado...':'Ya existe provision'

            );


          
            return json_encode($data_centro_costos, JSON_UNESCAPED_UNICODE);

 

        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
    public function obtener_provisiones_por_periodo($request)
    {
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $fecha_limite = $request["fecha_limite"];
            $tipo_provisiones = $request["tipo_provisiones"];
            
        
            $model =  new Provision();
            // AQUI ENVIAMOS UN PARAMETRO PARA FILTRO , EL CUAL SE TRAEN TODOS LOS CONTRATOS DEL PRESENTE DESDE ENERO 
            $fecha_actual_año_actual = date('Y-m-01'); // esto se puede quitar si se desea qeu solo traiga desde inicio de cada contrato
            $fecha_limite = date('Y-m', strtotime(str_replace('/', '-', $fecha_limite)));
            // var_dump($tipo_provisiones);exit();

            if($tipo_provisiones == '0'){ 
                $incrementos = $model->obtener_provisiones_por_periodo($fecha_limite, $fecha_actual_año_actual,'0'); // SIN IPC
            }else{
                $incrementos = $model->obtener_provisiones_por_periodo($fecha_limite, $fecha_actual_año_actual,'1');  // CON IPC
            }
             
            
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_plantilla_asiento_contable($request)
    {
        try {
            // $fecha_limite = "2023-06-20";
            // $ultimo_dia_del_mes = date('d/m/Y', strtotime(date('Y-m-t', strtotime($fecha_limite))));
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $fecha_inicio_a_provisionar = $request['fecha_inicio_a_provisionar']; //"2023-06-20";
            $fecha_actual_año_actual = $request['fecha_fin_a_provisionar']; //"2023-06-20";
            $tipo_provisiones = $request['tipo_provisiones']; //"2023-06-20";


            $fechaObj = date_create_from_format('d/m/Y', $fecha_inicio_a_provisionar);
            $fecha_inicio_a_provisionar = date_format($fechaObj, 'Y-01');

            $fechaObj2 = date_create_from_format('d/m/Y', $fecha_actual_año_actual);
            // $fecha_actual_año_actual = date_format($fechaObj2, 'Y-m');
            $model =  new Provision();
            // AQUI ENVIAMOS UN PARAMETRO PARA FILTRO , EL CUAL SE TRAEN TODOS LOS CONTRATOS DEL PRESENTE DESDE ENERO 
            // $fecha_actual_año_actual = date('Y-01'); // esto se puede quitar si se desea qeu solo traiga desde inicio de cada contrato

            $incrementos = $model->obtener_plantilla_asiento_contable($fecha_inicio_a_provisionar, $fecha_actual_año_actual, $tipo_provisiones);
            $row_count = sizeof($incrementos['result']);
            $incrementos = $incrementos['result'];
            // var_dump( $incrementos);exit();
            $data_return =array();
            if ($row_count > 0) {
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()
                    ->setCreator("Apuesta Total")
                    ->setDescription("Reporte");

                $fecha_actual = date('Y-m-01');
                $mes_actual = date('m', strtotime($fecha_actual));
                $año_actual = date('Y', strtotime($fecha_actual));
                $num_fila = 2;

                // CABECERA DEL EXCEL 

                $fila_1 = array(
                    '',
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
                    'Tipo Cambio para F',
                    'Importe de IGV sin derecho credito fiscal',
                    'Tasa IGV'
                );
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', $fila_1[0])
                    ->setCellValue('B1', $fila_1[1])
                    ->setCellValue('C1', $fila_1[2])
                    ->setCellValue('D1', $fila_1[3])
                    ->setCellValue('E1', $fila_1[4])
                    ->setCellValue('F1', $fila_1[5])
                    ->setCellValue('G1', $fila_1[6])
                    ->setCellValue('H1', $fila_1[7])
                    ->setCellValue('I1', $fila_1[8])
                    ->setCellValue('J1', $fila_1[9])
                    ->setCellValue('K1', $fila_1[10])
                    ->setCellValue('L1', $fila_1[11])
                    ->setCellValue('M1', $fila_1[12])
                    ->setCellValue('N1', $fila_1[13])
                    ->setCellValue('O1', $fila_1[14])
                    ->setCellValue('P1', $fila_1[15])
                    ->setCellValue('Q1', $fila_1[16])
                    ->setCellValue('R1', $fila_1[17])
                    ->setCellValue('S1', $fila_1[18])
                    ->setCellValue('T1', $fila_1[19])
                    ->setCellValue('U1', $fila_1[20])
                    ->setCellValue('V1', $fila_1[21])
                    ->setCellValue('W1', $fila_1[22])
                    ->setCellValue('X1', $fila_1[23])
                    ->setCellValue('Y1', $fila_1[24])
                    ->setCellValue('Z1', $fila_1[25])
                    ->setCellValue('AA1', $fila_1[26])
                    ->setCellValue('AB1', $fila_1[27])
                    ->setCellValue('AC1', $fila_1[28])
                    ->setCellValue('AD1', $fila_1[29])
                    ->setCellValue('AE1', $fila_1[30])
                    ->setCellValue('AF1', $fila_1[31])
                    ->setCellValue('AG1', $fila_1[32])
                    ->setCellValue('AH1', $fila_1[33])
                    ->setCellValue('AI1', $fila_1[34])
                    ->setCellValue('AJ1', $fila_1[35])
                    ->setCellValue('AK1', $fila_1[36])
                    ->setCellValue('AL1', $fila_1[37])
                    ->setCellValue('AM1', $fila_1[38])
                    ->setCellValue('AN1', $fila_1[39])
                    ->setCellValue('AO1', $fila_1[40]);


                 $i = 0;
                // $subdiario = $model->obtener_valor_subdario('subdiario')['result'][0]['valor']; // OBTENEMOS SUBDIARIO DE LA BD trim($incrementos[$i]['moneda_id']);
                $tipo_de_cambio =  $model->obtener_valor_subdario('tipo_cambio_contable')['result'][0]['valor']; // SE CAMBIA LUEGO EL TIPO DE CAMBIO
                $codigo_moneda_nacional = $model->obtener_valor_subdario('codigo_moneda_nacional')['result'][0]['valor'];
                $codigo_moneda_dolares = $model->obtener_valor_subdario('codigo_moneda_dolares')['result'][0]['valor'];
                $array_monedas_id = ['codigo_moneda_nacional'=> $codigo_moneda_nacional,'codigo_moneda_dolares' => $codigo_moneda_dolares ];
                while ($i < $row_count) {

                    $subdiario =  isset($incrementos[$i]['subdiario_contabilidad'])?trim($incrementos[$i]['subdiario_contabilidad']):'';
                    $tipo_de_conversion = 'C';
                    $flag_conversion_moneda = 'S'; // S: SE CONVIERTE , N NO SE CONVIERTE
                    $fecha_comprobante = date('02/m/Y', strtotime($fecha_actual));
                    $num_comprobante = str_pad($mes_actual, 2, "0", STR_PAD_LEFT) . str_pad($i + 1, 4, "0", STR_PAD_LEFT);
                    $moneda_id = trim($incrementos[$i]['moneda_id']);



                    $cuenta_contable = ['635206', '421201', '641909'];
                    $debe_haber = ['D', 'H'];


                    // for ($j = 0; $j < 2; $j++) {
                    $AT_paga_impuesto =  $incrementos[$i]['AT_paga_impuesto'];
                    $tipo_id =  $incrementos[$i]['tipo_id'];
                    if ($moneda_id == 1) {
                        $codigo_moneda = $array_monedas_id['codigo_moneda_nacional'];
                    } elseif ($moneda_id == 2) {
                        $codigo_moneda = $array_monedas_id['codigo_moneda_nacional']; 
                    }
                    $nombre_tienda = $incrementos[$i]['nombre_tienda'];

                    $centro_de_costos =  $incrementos[$i]['cc_id'];
                    $tipo_de_documento =  'RA'; // RECIVO DE ARRENDAMIENTO PARA PERSONA NATURAL 
                    $numero_documento =  '1683-' . $mes_actual . $año_actual; // CONSTANTE DEFINIDA POR EL USUARIO PARA PERSONA NATURAL

                    // CALCULO DE CODIGO ANEXO AUXILIAR  
                    $periodo_inicio =  $incrementos[$i]['periodo_inicio'];
                    $año_periodo_inicio = date('Y', strtotime($periodo_inicio));
                    if ($año_periodo_inicio == $año_actual) {
                        $codigo_anexo_aux = 01;
                    } else {
                        $codigo_anexo_aux = 02;
                    }
                    $codigo_anexo = $incrementos[$i]['num_docu'];
                    if ($AT_paga_impuesto == 'SI') {
                        if ($tipo_id == 3) {
                            $num_fila;
                            $importe_original =  $incrementos[$i + 1]['renta_bruta'];

                            $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue('A' . $num_fila, '')  // RESTRICCIONES de los campos
                                ->setCellValue('B' . $num_fila, substr($subdiario, 0, 4)) // SUBDIARIO
                                ->setCellValue('C' . $num_fila, $num_comprobante) // NUM COMPROBANTE 
                                ->setCellValue('D' . $num_fila, $fecha_comprobante) //FEHCA COMPROBANTE
                                ->setCellValue('E' . $num_fila, $codigo_moneda) // CODIGO MONEDA
                                ->setCellValue('F' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 40)) // GLOSA PRINCIPAL
                                ->setCellValue('G' . $num_fila, $tipo_de_cambio) // TIPO DE CAMBIO 
                                ->setCellValue('H' . $num_fila, $tipo_de_conversion) // TIPO DE CONVERSION 
                                ->setCellValue('I' . $num_fila, $flag_conversion_moneda) // FLAG CONVERSION
                                ->setCellValue('J' . $num_fila, $fecha_comprobante) // FECHA TIPO  CAMBIO 
                                ->setCellValue('K' . $num_fila, $cuenta_contable[0]) // CUENTA CONTABLE 
                                ->setCellValue('L' . $num_fila, $codigo_anexo)  // CODIGO ANEXO 
                                ->setCellValue('M' . $num_fila, $centro_de_costos) // CODIGO CENTRO DE COSTOS
                                ->setCellValue('N' . $num_fila, $debe_haber[0]) // DEBE / HABER
                                ->setCellValue('O' . $num_fila, number_format($importe_original, 2, '.', ',')) // IMPORTE ORIGINAL
                                ->setCellValue('P' . $num_fila, '') // IMPORTE EN DOLARES 
                                ->setCellValue('Q' . $num_fila, '') // IMPORTE EN SOLES
                                ->setCellValue('R' . $num_fila,  $tipo_de_documento) // TIPO DE DOCUMENTO
                                ->setCellValue('S' . $num_fila, $numero_documento) // NUMERO DE DOCUEMNTO
                                ->setCellValue('T' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE DOCUMENTO
                                ->setCellValue('U' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE VENCIMIENTO
                                ->setCellValue('V' . $num_fila, '') // CODIGO DE AREA
                                ->setCellValue('W' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 30)) // GLOSA DETALLE
                                ->setCellValue('X' . $num_fila, '0' . $codigo_anexo_aux) // CODIGO ANEXO AUXILIAR
                                ->setCellValue('Y' . $num_fila, '')
                                ->setCellValue('Z' . $num_fila, '')
                                ->setCellValue('AA' . $num_fila, '')
                                ->setCellValue('AB' . $num_fila, '')
                                ->setCellValue('AC' . $num_fila, '')
                                ->setCellValue('AD' . $num_fila, '')
                                ->setCellValue('AE' . $num_fila, '')
                                ->setCellValue('AF' . $num_fila, '')
                                ->setCellValue('AG' . $num_fila, '')
                                ->setCellValue('AH' . $num_fila, '')
                                ->setCellValue('AI' . $num_fila, '')
                                ->setCellValue('AJ' . $num_fila, '')
                                ->setCellValue('AK' . $num_fila, '')
                                ->setCellValue('AL' . $num_fila, '')
                                ->setCellValue('AM' . $num_fila, '')
                                ->setCellValue('AN' . $num_fila, '')
                                ->setCellValue('AO' . $num_fila, '');

                            $num_fila++;

                            $importe_original =  $incrementos[$i]['descuento_IR'];

                            $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue('A' . $num_fila, '')  // RESTRICCIONES de los campos
                                ->setCellValue('B' . $num_fila, substr($subdiario, 0, 4)) // SUBDIARIO
                                ->setCellValue('C' . $num_fila, $num_comprobante) // NUM COMPROBANTE 
                                ->setCellValue('D' . $num_fila, $fecha_comprobante) //FEHCA COMPROBANTE
                                ->setCellValue('E' . $num_fila, $codigo_moneda) // CODIGO MONEDA
                                ->setCellValue('F' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 40)) // GLOSA PRINCIPAL
                                ->setCellValue('G' . $num_fila, $tipo_de_cambio) // TIPO DE CAMBIO 
                                ->setCellValue('H' . $num_fila, $tipo_de_conversion) // TIPO DE CONVERSION 
                                ->setCellValue('I' . $num_fila, $flag_conversion_moneda) // FLAG CONVERSION
                                ->setCellValue('J' . $num_fila, $fecha_comprobante) // FECHA TIPO  CAMBIO 
                                ->setCellValue('K' . $num_fila, $cuenta_contable[2]) // CUENTA CONTABLE 
                                ->setCellValue('L' . $num_fila, $codigo_anexo)  // CODIGO ANEXO 
                                ->setCellValue('M' . $num_fila, $centro_de_costos) // CODIGO CENTRO DE COSTOS
                                ->setCellValue('N' . $num_fila,  $debe_haber[1]) // DEBE / HABER
                                ->setCellValue('O' . $num_fila, number_format($importe_original, 2, '.', ',')) // IMPORTE ORIGINAL
                                ->setCellValue('P' . $num_fila, '') // IMPORTE EN DOLARES 
                                ->setCellValue('Q' . $num_fila, '') // IMPORTE EN SOLES
                                ->setCellValue('R' . $num_fila,  $tipo_de_documento) // TIPO DE DOCUMENTO
                                ->setCellValue('S' . $num_fila, $numero_documento) // NUMERO DE DOCUEMNTO
                                ->setCellValue('T' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE DOCUMENTO
                                ->setCellValue('U' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE VENCIMIENTO
                                ->setCellValue('V' . $num_fila, '') // CODIGO DE AREA
                                ->setCellValue('W' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 30)) // GLOSA DETALLE
                                ->setCellValue('X' . $num_fila, '0' . $codigo_anexo_aux) // CODIGO ANEXO AUXILIAR
                                ->setCellValue('Y' . $num_fila, '')
                                ->setCellValue('Z' . $num_fila, '')
                                ->setCellValue('AA' . $num_fila, '')
                                ->setCellValue('AB' . $num_fila, '')
                                ->setCellValue('AC' . $num_fila, '')
                                ->setCellValue('AD' . $num_fila, '')
                                ->setCellValue('AE' . $num_fila, '')
                                ->setCellValue('AF' . $num_fila, '')
                                ->setCellValue('AG' . $num_fila, '')
                                ->setCellValue('AH' . $num_fila, '')
                                ->setCellValue('AI' . $num_fila, '')
                                ->setCellValue('AJ' . $num_fila, '')
                                ->setCellValue('AK' . $num_fila, '')
                                ->setCellValue('AL' . $num_fila, '')
                                ->setCellValue('AM' . $num_fila, '')
                                ->setCellValue('AN' . $num_fila, '')
                                ->setCellValue('AO' . $num_fila, '');


                            $agregar_haber = false;

                            if (isset($incrementos[$i + 2]['cc_id'])) {
                                if ($incrementos[$i]['cc_id'] != $incrementos[$i + 2]['cc_id']) {
                                    $agregar_haber = true;
                                }
                            } else {
                                $agregar_haber = true;
                            }
                            if ($agregar_haber) {
                                $num_fila++;
                                $importe_soles_haber   = '';
                                $importe_dolares_haber = '';
                                $cuenta_contable_haber = '';


                                $importe_original =  $incrementos[$i + 1]['total_pagar'];

                                $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A' . $num_fila, '')  // RESTRICCIONES de los campos
                                    ->setCellValue('B' . $num_fila, substr($subdiario, 0, 4)) // SUBDIARIO
                                    ->setCellValue('C' . $num_fila, $num_comprobante) // NUM COMPROBANTE 
                                    ->setCellValue('D' . $num_fila, $fecha_comprobante) //FEHCA COMPROBANTE
                                    ->setCellValue('E' . $num_fila, $codigo_moneda) // CODIGO MONEDA
                                    ->setCellValue('F' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 40)) // GLOSA PRINCIPAL
                                    ->setCellValue('G' . $num_fila, $tipo_de_cambio) // TIPO DE CAMBIO 
                                    ->setCellValue('H' . $num_fila, $tipo_de_conversion) // TIPO DE CONVERSION 
                                    ->setCellValue('I' . $num_fila, $flag_conversion_moneda) // FLAG CONVERSION
                                    ->setCellValue('J' . $num_fila, $fecha_comprobante) // FECHA TIPO  CAMBIO 
                                    ->setCellValue('K' . $num_fila, $cuenta_contable[1]) // CUENTA CONTABLE 
                                    ->setCellValue('L' . $num_fila, $codigo_anexo)  // CODIGO ANEXO 
                                    ->setCellValue('M' . $num_fila, $centro_de_costos) // CODIGO CENTRO DE COSTOS
                                    ->setCellValue('N' . $num_fila,  $debe_haber[1]) // DEBE / HABER
                                    ->setCellValue('O' . $num_fila, number_format($importe_original, 2, '.', ',')) // IMPORTE ORIGINAL
                                    ->setCellValue('P' . $num_fila, '') // IMPORTE EN DOLARES 
                                    ->setCellValue('Q' . $num_fila, '') // IMPORTE EN SOLES
                                    ->setCellValue('R' . $num_fila,  $tipo_de_documento) // TIPO DE DOCUMENTO
                                    ->setCellValue('S' . $num_fila, $numero_documento) // NUMERO DE DOCUEMNTO
                                    ->setCellValue('T' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE DOCUMENTO
                                    ->setCellValue('U' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE VENCIMIENTO
                                    ->setCellValue('V' . $num_fila, '') // CODIGO DE AREA
                                    ->setCellValue('W' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 30)) // GLOSA DETALLE
                                    ->setCellValue('X' . $num_fila, '0' . $codigo_anexo_aux) // CODIGO ANEXO AUXILIAR
                                    ->setCellValue('Y' . $num_fila, '')
                                    ->setCellValue('Z' . $num_fila, '')
                                    ->setCellValue('AA' . $num_fila, '')
                                    ->setCellValue('AB' . $num_fila, '')
                                    ->setCellValue('AC' . $num_fila, '')
                                    ->setCellValue('AD' . $num_fila, '')
                                    ->setCellValue('AE' . $num_fila, '')
                                    ->setCellValue('AF' . $num_fila, '')
                                    ->setCellValue('AG' . $num_fila, '')
                                    ->setCellValue('AH' . $num_fila, '')
                                    ->setCellValue('AI' . $num_fila, '')
                                    ->setCellValue('AJ' . $num_fila, '')
                                    ->setCellValue('AK' . $num_fila, '')
                                    ->setCellValue('AL' . $num_fila, '')
                                    ->setCellValue('AM' . $num_fila, '')
                                    ->setCellValue('AN' . $num_fila, '')
                                    ->setCellValue('AO' . $num_fila, '');
                            }
                        }


                        $i++;
                    } else {
                        $importe_original =  $incrementos[$i]['total_pagar'];

                        $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $num_fila, '')  // RESTRICCIONES de los campos
                            ->setCellValue('B' . $num_fila, substr($subdiario, 0, 4)) // SUBDIARIO
                            ->setCellValue('C' . $num_fila, $num_comprobante) // NUM COMPROBANTE 
                            ->setCellValue('D' . $num_fila, $fecha_comprobante) //FEHCA COMPROBANTE
                            ->setCellValue('E' . $num_fila, $codigo_moneda) // CODIGO MONEDA
                            ->setCellValue('F' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 40)) // GLOSA PRINCIPAL
                            ->setCellValue('G' . $num_fila, $tipo_de_cambio) // TIPO DE CAMBIO 
                            ->setCellValue('H' . $num_fila, $tipo_de_conversion) // TIPO DE CONVERSION 
                            ->setCellValue('I' . $num_fila, $flag_conversion_moneda) // FLAG CONVERSION
                            ->setCellValue('J' . $num_fila, $fecha_comprobante) // FECHA TIPO  CAMBIO 
                            ->setCellValue('K' . $num_fila, $cuenta_contable[0]) // CUENTA CONTABLE 
                            ->setCellValue('L' . $num_fila, $codigo_anexo)  // CODIGO ANEXO 
                            ->setCellValue('M' . $num_fila, $centro_de_costos) // CODIGO CENTRO DE COSTOS
                            ->setCellValue('N' . $num_fila, $debe_haber[0]) // DEBE / HABER
                            ->setCellValue('O' . $num_fila, number_format($importe_original, 2, '.', ',')) // IMPORTE ORIGINAL
                            ->setCellValue('P' . $num_fila, '') // IMPORTE EN DOLARES 
                            ->setCellValue('Q' . $num_fila, '') // IMPORTE EN SOLES
                            ->setCellValue('R' . $num_fila,  $tipo_de_documento) // TIPO DE DOCUMENTO
                            ->setCellValue('S' . $num_fila, $numero_documento) // NUMERO DE DOCUEMNTO
                            ->setCellValue('T' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE DOCUMENTO
                            ->setCellValue('U' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE VENCIMIENTO
                            ->setCellValue('V' . $num_fila, '') // CODIGO DE AREA
                            ->setCellValue('W' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 30)) // GLOSA DETALLE
                            ->setCellValue('X' . $num_fila, '0' . $codigo_anexo_aux) // CODIGO ANEXO AUXILIAR
                            ->setCellValue('Y' . $num_fila, '')
                            ->setCellValue('Z' . $num_fila, '')
                            ->setCellValue('AA' . $num_fila, '')
                            ->setCellValue('AB' . $num_fila, '')
                            ->setCellValue('AC' . $num_fila, '')
                            ->setCellValue('AD' . $num_fila, '')
                            ->setCellValue('AE' . $num_fila, '')
                            ->setCellValue('AF' . $num_fila, '')
                            ->setCellValue('AG' . $num_fila, '')
                            ->setCellValue('AH' . $num_fila, '')
                            ->setCellValue('AI' . $num_fila, '')
                            ->setCellValue('AJ' . $num_fila, '')
                            ->setCellValue('AK' . $num_fila, '')
                            ->setCellValue('AL' . $num_fila, '')
                            ->setCellValue('AM' . $num_fila, '')
                            ->setCellValue('AN' . $num_fila, '')
                            ->setCellValue('AO' . $num_fila, '');

                        $agregar_haber = false;

                        if (isset($incrementos[$i + 1]['cc_id'])) {
                            if ($incrementos[$i]['cc_id'] != $incrementos[$i + 1]['cc_id']) {
                                $agregar_haber = true;
                            }
                        } else {
                            $agregar_haber = true;
                        }
                        if ($agregar_haber) {
                            $num_fila++;
                            $importe_soles_haber   = '';
                            $importe_dolares_haber = '';
                            $cuenta_contable_haber = '';



                            $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue('A' . $num_fila, '')  // RESTRICCIONES de los campos
                                ->setCellValue('B' . $num_fila, substr($subdiario, 0, 4)) // SUBDIARIO
                                ->setCellValue('C' . $num_fila, $num_comprobante) // NUM COMPROBANTE 
                                ->setCellValue('D' . $num_fila, $fecha_comprobante) //FEHCA COMPROBANTE
                                ->setCellValue('E' . $num_fila, $codigo_moneda) // CODIGO MONEDA
                                ->setCellValue('F' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 40)) // GLOSA PRINCIPAL
                                ->setCellValue('G' . $num_fila, $tipo_de_cambio) // TIPO DE CAMBIO 
                                ->setCellValue('H' . $num_fila, $tipo_de_conversion) // TIPO DE CONVERSION 
                                ->setCellValue('I' . $num_fila, $flag_conversion_moneda) // FLAG CONVERSION
                                ->setCellValue('J' . $num_fila, $fecha_comprobante) // FECHA TIPO  CAMBIO 
                                ->setCellValue('K' . $num_fila, $cuenta_contable[1]) // CUENTA CONTABLE 
                                ->setCellValue('L' . $num_fila, $codigo_anexo)  // CODIGO ANEXO 
                                ->setCellValue('M' . $num_fila, $centro_de_costos) // CODIGO CENTRO DE COSTOS
                                ->setCellValue('N' . $num_fila,  $debe_haber[1]) // DEBE / HABER
                                ->setCellValue('O' . $num_fila, number_format($importe_original, 2, '.', ',')) // IMPORTE ORIGINAL
                                ->setCellValue('P' . $num_fila, '') // IMPORTE EN DOLARES 
                                ->setCellValue('Q' . $num_fila, '') // IMPORTE EN SOLES
                                ->setCellValue('R' . $num_fila,  $tipo_de_documento) // TIPO DE DOCUMENTO
                                ->setCellValue('S' . $num_fila, $numero_documento) // NUMERO DE DOCUEMNTO
                                ->setCellValue('T' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE DOCUMENTO
                                ->setCellValue('U' . $num_fila,  date('t/m/Y', strtotime($fecha_actual_año_actual))) // FECHA DE VENCIMIENTO
                                ->setCellValue('V' . $num_fila, '') // CODIGO DE AREA
                                ->setCellValue('W' . $num_fila, substr('ALQ ' . $nombre_tienda, 0, 30)) // GLOSA DETALLE
                                ->setCellValue('X' . $num_fila, '0' . $codigo_anexo_aux) // CODIGO ANEXO AUXILIAR
                                ->setCellValue('Y' . $num_fila, '')
                                ->setCellValue('Z' . $num_fila, '')
                                ->setCellValue('AA' . $num_fila, '')
                                ->setCellValue('AB' . $num_fila, '')
                                ->setCellValue('AC' . $num_fila, '')
                                ->setCellValue('AD' . $num_fila, '')
                                ->setCellValue('AE' . $num_fila, '')
                                ->setCellValue('AF' . $num_fila, '')
                                ->setCellValue('AG' . $num_fila, '')
                                ->setCellValue('AH' . $num_fila, '')
                                ->setCellValue('AI' . $num_fila, '')
                                ->setCellValue('AJ' . $num_fila, '')
                                ->setCellValue('AK' . $num_fila, '')
                                ->setCellValue('AL' . $num_fila, '')
                                ->setCellValue('AM' . $num_fila, '')
                                ->setCellValue('AN' . $num_fila, '')
                                ->setCellValue('AO' . $num_fila, '');
                        }
                    }



                    // if($incrementos[$i]['tipo_id'] == 3){

                    // }






                    $num_fila++;
                    $i++;

                    // }


                    $num_filas_totale = $i;
                }
                //  var_dump($num_filas_totale);

                $estiloTituloColumnasMesesPrincipal = array(
                    'font' => array(
                        'name'  => 'Arial',
                        'bold'  => true,
                        'size' => 10,
                        'color' => array(
                            'rgb' => '000000'
                        )
                    ),
                    'alignment' =>  array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap'      => false
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $estiloColoFondoAmarilloMostaza = array(
                    'fill' => array(
                        'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array(
                            'rgb' => 'ffd965'
                        )
                    )
                );

                $estiloTituloColumnasPrincipal = array(
                    'font' => array(
                        'name'  => 'Arial',
                        'bold'  => true,
                        'size' => 10,
                        'color' => array(
                            'rgb' => '000000'
                        )
                    ),
                    'alignment' =>  array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap'      => false
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $estiloInformacion = new PHPExcel_Style();
                $estiloInformacion->applyFromArray(array(
                    'font' => array(
                        'name'  => 'Arial',
                        'color' => array(
                            'rgb' => '000000'
                        )
                    ),
                    'alignment' =>  array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap'      => false
                    )
                ));
              
                
                $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);

                $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloTituloColumnasPrincipal);
                $objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnasPrincipal);

                $objPHPExcel->getActiveSheet()->getStyle('A1:AO1')->applyFromArray($estiloColoFondoAmarilloMostaza);

                $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloTituloColumnasMesesPrincipal);
                $objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnasMesesPrincipal);

                $formatoDerecha = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    ),
                );
                for($i = 0; $i <= $num_fila; $i++) {
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$i)->applyFromArray($formatoDerecha);
                    $objPHPExcel->getActiveSheet()->getStyle('O'.$i)->applyFromArray($formatoDerecha);

                }
                // $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AO" . ($num_filas_totale - 1));

                for($i = 'A'; $i <= 'Z'; $i++) {
                     $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
                }
                $objPHPExcel->getActiveSheet()->setTitle('Plantilla contable');

                $objPHPExcel->setActiveSheetIndex(0);
                // $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 2);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="Plantilla_Contable_EXPORT.xls"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $fechaSolicitud  =  date('YmdHis');
                $filename = "Plantilla Contable" . $fechaSolicitud . ".xls";
                $excel_path = '/var/www/html/files_bucket/provisiones/' . $filename;
    
               // $excel_path_download = '/files_bucket/kasnet/'. $filename;
                $path = "/var/www/html/files_bucket/provisiones/";
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                
                 $objWriter->save($excel_path);
                    // Obtén el nombre del archivo sin la ruta
                $filename_only = basename($excel_path);

                // Establecer encabezados para la descarga
                header('Content-Description: File Transfer');
                header('Content-Type: application/vnd.ms-excel'); // Cambia el tipo MIME según el formato de Excel que estás usando (xls, xlsx)
                header('Content-Disposition: attachment; filename="' . $filename_only . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($excel_path));

                // Leer el archivo y enviarlo al navegador
                readfile($excel_path);

                // Finalizar la ejecución para evitar cualquier salida adicional
                exit();
                $data_return = array(
                    "path" => '/files_bucket/provisiones/' . $filename,
                    "tipo" => "excel",
                    "ext" => "xls",
                    "size" => filesize($excel_path),
                    "fecha_registro" => date("Y-m-d h:i:s"),
                );

                // exit;

                //     if ($moneda_id == 1) {
                //         $codigo_moneda = "MN";
                //         // $fila_4_importe_dolares = round($fila_4_importe_original / $tipo_de_cambio, 2);
                //         // $fila_4_importe_soles = $fila_4_importe_original;
                //     } elseif($moneda_id == 2) {
                //         $codigo_moneda = "ME";
                //         // $fila_4_importe_dolares = $fila_4_importe_original;
                //         // $fila_4_importe_soles = round($fila_4_importe_original * $tipo_de_cambio, 2);
                //     }

                // }else{
                //     return "NO se encontraron datos";
            }



            // return $data_return;
            return json_encode($data_return,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function exportar_excel_calculo_provisiones($request)
    {
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $fecha_inicio_a_provisionar = $request['fecha_inicio_a_provisionar'];
            $fecha_actual_año_actual = $request['fecha_fin_a_provisionar'];
            $tipo_provisiones = $request['tipo_provisiones'];


            $fechaObj = date_create_from_format('d/m/Y', $fecha_inicio_a_provisionar);
            $fecha_inicio_a_provisionar = date_format($fechaObj, 'Y-01');

            $fechaObj2 = date_create_from_format('d/m/Y', $fecha_actual_año_actual);
            // $fecha_actual_año_actual = date_format($fechaObj2, 'Y-m');
            $model =  new Provision();
            // AQUI ENVIAMOS UN PARAMETRO PARA FILTRO , EL CUAL SE TRAEN TODOS LOS CONTRATOS DEL PRESENTE DESDE ENERO 
            // $fecha_actual_año_actual = date('Y-01'); // esto se puede quitar si se desea qeu solo traiga desde inicio de cada contrato

            $incrementos = $model->obtener_plantilla_asiento_contable($fecha_inicio_a_provisionar, $fecha_actual_año_actual,$tipo_provisiones);
            $row_count = sizeof($incrementos['result']);
            $incrementos = $incrementos['result'];
            $data_return =array();

            if ($row_count > 0) {
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()
                    ->setCreator("Apuesta Total")
                    ->setDescription("Provisiones");
                $num_fila = 2;

                // CABECERA DEL EXCEL 

                $fila_1 = array(
                    'Nro. Cuota',
                    'Centro de costos',
                    'Tienda',
                    'Renta fija',
                    'Total',
                    'Incrementos',

                    'AT paga IR',
                    'Descuento IR',
                    'Total a pagar',
                    'Periodo inicio',
                    'Periodo fin',
                    'Día de pago'

                );
                $objPHPExcel->setActiveSheetIndex(0)

                    ->setCellValue('A1', $fila_1[0])
                    ->setCellValue('B1', $fila_1[1])
                    ->setCellValue('C1', $fila_1[2])
                    ->setCellValue('D1', $fila_1[3])
                    ->setCellValue('E1', $fila_1[4])
                    ->setCellValue('F1', $fila_1[5])
                    ->setCellValue('G1', $fila_1[6])
                    ->setCellValue('H1', $fila_1[7])
                    ->setCellValue('I1', $fila_1[8])
                    ->setCellValue('J1', $fila_1[9])
                    ->setCellValue('K1', $fila_1[10])
                    ->setCellValue('L1', $fila_1[11]);



                for ($i = 0; $i < $row_count; $i++) {


                    $nombre_tienda = $incrementos[$i]['nombre_tienda'];

                    $num_cuota =  $incrementos[$i]['num_cuota'];

                    $centro_de_costos =  $incrementos[$i]['cc_id'];
                    $importe_original =  $incrementos[$i]['renta_bruta'];
                    $total_calculado =  $incrementos[$i]['total_calculado'];
                    $descuento_IR =  $incrementos[$i]['descuento_IR'];
                    $total_pagar =  $incrementos[$i]['total_pagar'];
                    $periodo_inicio =  $incrementos[$i]['periodo_inicio'];
                    $periodo_fin =  $incrementos[$i]['periodo_fin'];
                    $dia_de_pago =  $incrementos[$i]['dia_de_pago'];
                    $AT_paga_impuesto =  $incrementos[$i]['AT_paga_impuesto'];
                    $incrementos_posee =  $incrementos[$i]['incrementos'];


                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num_fila, $num_cuota)  //NUMERO DE CUOTA
                        ->setCellValue('B' . $num_fila, $centro_de_costos) // CENTOR DE COSTOS
                        ->setCellValue('C' . $num_fila, $nombre_tienda) // NUM COMPROBANTE 
                        ->setCellValue('D' . $num_fila, number_format($importe_original, 2, '.', ',')) //IMPORTE ORIGINAL
                        ->setCellValue('E' . $num_fila, number_format($total_calculado, 2, '.', ',')) // TOTAL CALCULADO
                        ->setCellValue('F' . $num_fila, number_format($incrementos_posee, 2, '.', ',')) // INCREMENTO
                        ->setCellValue('G' . $num_fila, $AT_paga_impuesto) // AT PAGA IMPUESTO A LA RENTA

                        ->setCellValue('H' . $num_fila, number_format($descuento_IR, 2, '.', ',')) // GLOSA PRINCIPAL
                        ->setCellValue('I' . $num_fila, number_format($total_pagar, 2, '.', ',')) // GLOSA PRINCIPAL
                        ->setCellValue('J' . $num_fila, date('d/m/Y', strtotime($periodo_inicio)) ) // PERIODO INICIO
                        ->setCellValue('K' . $num_fila, date('d/m/Y', strtotime($periodo_fin))) // PERIODO FIN 
                        ->setCellValue('L' . $num_fila, $dia_de_pago); // GDIA DE PAGO 


                    $num_fila++;

                    $num_filas_totale = $i;
                }
                //  var_dump($num_filas_totale);

                $estiloTituloColumnasMesesPrincipal = array(
                    'font' => array(
                        'name'  => 'Arial',
                        'bold'  => true,
                        'size' => 10,
                        'color' => array(
                            'rgb' => '000000'
                        )
                    ),
                    'alignment' =>  array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap'      => false
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $estiloColoFondoAmarilloMostaza = array(
                    'fill' => array(
                        'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array(
                            'rgb' => 'ffd965'
                        )
                    )
                );

                $estiloTituloColumnasPrincipal = array(
                    'font' => array(
                        'name'  => 'Arial',
                        'bold'  => true,
                        'size' => 10,
                        'color' => array(
                            'rgb' => '000000'
                        )
                    ),
                    'alignment' =>  array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap'      => false
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $estiloInformacion = new PHPExcel_Style();
                $estiloInformacion->applyFromArray(array(
                    'font' => array(
                        'name'  => 'Arial',
                        'color' => array(
                            'rgb' => '000000'
                        )
                    ),
                    'alignment' =>  array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap'      => false
                    )
                ));
                $formatoDerecha = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    ),
                );
                for($i = 0; $i <= $num_fila; $i++) {
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$i)->applyFromArray($formatoDerecha);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$i)->applyFromArray($formatoDerecha);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$i)->applyFromArray($formatoDerecha);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$i)->applyFromArray($formatoDerecha);
                    $objPHPExcel->getActiveSheet()->getStyle('I'.$i)->applyFromArray($formatoDerecha);

                }

                $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);

                $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloTituloColumnasPrincipal);
                $objPHPExcel->getActiveSheet()->getStyle('AA1:Z1')->applyFromArray($estiloTituloColumnasPrincipal);

                $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloColoFondoAmarilloMostaza);

                $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloTituloColumnasMesesPrincipal);
                $objPHPExcel->getActiveSheet()->getStyle('AA1:Z1')->applyFromArray($estiloTituloColumnasMesesPrincipal);

                // $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AN".($num_filas_totale-1));

                for ($i = 'A'; $i <= 'Z'; $i++) {
                    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
                }

                $objPHPExcel->setActiveSheetIndex(0);
                $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 2);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="Provisiones.xls"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                
                
                $fechaSolicitud  =  date('YmdHis');
                $filename = "Calulo de provisiones" . $fechaSolicitud . ".xls";
                $excel_path = '/var/www/html/files_bucket/provisiones/' . $filename;
    
               // $excel_path_download = '/files_bucket/kasnet/'. $filename;
                $path = "/var/www/html/files_bucket/provisiones/";
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                
                $objWriter->save($excel_path);
                 // Obtén el nombre del archivo sin la ruta
                $filename_only = basename($excel_path);

                // Establecer encabezados para la descarga
                header('Content-Description: File Transfer');
                header('Content-Type: application/vnd.ms-excel'); // Cambia el tipo MIME según el formato de Excel que estás usando (xls, xlsx)
                header('Content-Disposition: attachment; filename="' . $filename_only . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($excel_path));

                // Leer el archivo y enviarlo al navegador
                readfile($excel_path);

                // Finalizar la ejecución para evitar cualquier salida adicional
                exit();
                $data_return = array(
                    "path" => '/files_bucket/provisiones/' . $filename,
                    "tipo" => "excel",
                    "ext" => "xls",
                    "size" => filesize($excel_path),
                    "fecha_registro" => date("Y-m-d h:i:s"),
                );

                //     if ($moneda_id == 1) {
                //         $codigo_moneda = "MN";
                //         // $fila_4_importe_dolares = round($fila_4_importe_original / $tipo_de_cambio, 2);
                //         // $fila_4_importe_soles = $fila_4_importe_original;
                //     } elseif($moneda_id == 2) {
                //         $codigo_moneda = "ME";
                //         // $fila_4_importe_dolares = $fila_4_importe_original;
                //         // $fila_4_importe_soles = round($fila_4_importe_original * $tipo_de_cambio, 2);
                //     }

                // }else{
                //     return "NO se encontraron datos";
            }
            // $incrementos = mb_convert_encoding($incrementos, 'UTF-8');





            return json_encode($data_return,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
    public function enviar_tesoreria_provisiones($request)
    {
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $provision_idIs = json_decode($request["provision_idIs"]);
            $fecha_limite_envio_correo = $request["fecha_limite_envio_correo"];
            $tipo_provisiones = $request["tipo_provisiones"];
            $provision_idIs = implode(',', $provision_idIs); // SE CONVIERTE A CADENA EL ARRAY
            $model =  new Provision();
           
            $prov_no_pagadas_array = array(); // Creamos un array vacío

            if ($provision_idIs != "") {
                $solo_provisiones_no_pagadas = $model->validar_envio_tesoreria($provision_idIs); // SE ENVIA LOS LOS IDES DE LAS PROVISIONES
                $solo_provisiones_no_pagadas = mb_convert_encoding($solo_provisiones_no_pagadas, 'UTF-8');
               
                foreach($solo_provisiones_no_pagadas['result'] as $prov){
                    // DEBEMOS VALIDAR QUE SOLO LAS PROVISIONES NO PAGADAS SE ENVIENE A TESORERIA 
                    $prov_no_pagadas_array[] = $prov['id'];
                }
                $ids_coma_separados = implode(', ', $prov_no_pagadas_array);
                // var_dump($ids_coma_separados ); 
                $incrementos = $model->enviar_tesoreria_provisiones($ids_coma_separados,$tipo_provisiones); // SE ENVIA LOS LOS IDES DE LAS PROVISIONES
                $incrementos = mb_convert_encoding(2, 'UTF-8');
                $this->enviar_correo_contabilidad_provisiones('');

            }


            return json_encode($incrementos, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
    public function calculo_proviciones_contables_dev($request)
    {
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $contrato_id = $request['contrato_id'];
            $ultimo_dia_mes_pasado = $request['ultimo_dia_mes_pasado'];
            $model_provision =  new Provision();
            $model =  new ContratoArrendamiento();
            $datos = $model_provision->obtener_datos_generales_para_provision($contrato_id, $ultimo_dia_mes_pasado);
            $datos = mb_convert_encoding($datos, 'UTF-8');
            $carta_de_instruccion_id = $datos['result'][0]['carta_de_instruccion_id'];
            // INICIO VALIDAR DATOS

            if (empty($datos['result'][0]['fecha_inicio']) || empty($datos['result'][0]['fecha_fin'])) {
                $result["consulta_error"] = 'No se pudo generar la Provisión Contable porque faltan las fechas.';
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestión.";
                $result["result"] = 'ok';

                exit(json_encode($result));
            }
            // FIN VALIDAR DATOS          
            $empresa_suscribe_id = $datos['result'][0]['empresa_suscribe_id'];
            $tipo_moneda_id = $datos['result'][0]['tipo_moneda_id'];
            $array_provision = array();
            // LLENAR GARNATIAS 
            // $provision['mes_adelanto']  =   $adelantos[$i]['mes_adelanto'];
            // $provision['num_periodo']=   $adelantos[$i]['num_periodo'];
            $provision['fecha_inicio'] =   $datos['result'][0]['fecha_inicio'];
            $provision['fecha_fin'] =   $datos['result'][0]['fecha_fin'];
            $provision['importe'] =   $datos['result'][0]['garantia_monto'];
            $provision['fecha_actual'] = $datos['result'][0]['fecha_inicio'];
            $provision['num_adelanto_id'] =   '';

            $provision['num_cuota'] =   0;
            $provision['periodo_inicio'] =   '';
            $provision['periodo_fin_tmp'] =   '';
            // $provision['empresa_suscribe_id'] =   $empresa_suscribe_id;
            $provision['tipo_moneda_id'] =   $tipo_moneda_id;
            $provision['estado_pago'] =       1;
            $provision['renta_bruta'] = '';

            array_push($array_provision, $provision);

            // FIN LLENAR GARNTIAS 

            // OBTENER ADEALNTOS
            $adelantos =  $model->obtener_datos_adelantos($contrato_id)['result'];
            $array_adelantos = [];
            $adelantos_array    = [];
            if (sizeof($adelantos) > 0) {
                for ($i = 0; $i < sizeof($adelantos); $i++) {
                    // $provision['mes_adelanto']  =   $adelantos[$i]['mes_adelanto'];
                    $provision['fecha_inicio'] =   $datos['result'][0]['fecha_inicio'];
                    $provision['fecha_fin'] =   $datos['result'][0]['fecha_fin'];
                    $provision['importe'] =   $datos['result'][0]['monto_renta'];
                    $provision['fecha_actual'] = $datos['result'][0]['fecha_inicio'];

                    $provision['num_cuota'] =   0;
                    $provision['periodo_inicio'] =   '';
                    $provision['periodo_fin_tmp'] =   '';
                    $provision['num_adelanto_id'] =    $adelantos[$i]['num_periodo'];
                    // $provision['empresa_suscribe_id'] =   $empresa_suscribe_id;
                    $provision['tipo_moneda_id'] =   $tipo_moneda_id;
                    $provision['estado_pago'] =       1;
                    $provision['renta_bruta'] = $datos['result'][0]['monto_renta'];
                    $adelantos_array[$i] = $adelantos[$i]['num_periodo'];
                    array_push($array_provision, $provision);
                }
            }

            // FIN LLENAR ADELANTOS 


            // INICIO LLENAR INCREMENTOS 
            $array_incrementos = [];
            $impuesto_a_la_renta_id = $datos['result'][0]['impuesto_a_la_renta_id'];

            $renta = $datos['result'][0]['monto_renta'];

            $incrementos_data = $model->obtener_datos_incrementos($contrato_id)['result'];
            $num_incrementos_data = $model->obtener_datos_incrementos($contrato_id)['num_incrementos'];

            if ($num_incrementos_data > 0) {
                $cont_incremento = 0;

                for ($i = 0; $i < $num_incrementos_data; $i++) {
                    $array_incrementos[$cont_incremento][0] = $incrementos_data[$i]["valor"];
                    $array_incrementos[$cont_incremento][1] = $incrementos_data[$i]["tipo_valor_id"];
                    $array_incrementos[$cont_incremento][2] = $incrementos_data[$i]["tipo_continuidad_id"];
                    $array_incrementos[$cont_incremento][3] = $incrementos_data[$i]["a_partir_del_año"];
                    $cont_incremento++;
                    // array_push($array_provision,$provision);

                    // $array_provision['adelantos_prov']['dia_de_pago'] =   0;
                }
            }

            $num_incrementos = count($array_incrementos);
            // INICIO INICIALIZACION DE VARIABLES
            // $user_id = $login?$login['id']:null;
            $created_at = date('Y-m-d H:i:s');

            $num_dias_excedentes = 0;
            $incrementos = 0;
            $descuento = 0;
            $tipo_orden_id = 1;
            // FIN INICIALIZACION DE VARIABLES


            $bol = false;

            $datetime_inicio = new DateTime($datos['result'][0]['fecha_inicio']);
            $datetime_inicio2 = new DateTime($datos['result'][0]['fecha_inicio']);
            $datetime_fin = new DateTime($datos['result'][0]['fecha_fin']);
            if ($datetime_inicio->format('d') == '29') {
                $bol = true;
                $datetime_inicio->setDate($datetime_inicio->format('Y'), $datetime_inicio->format('m'), 28);
                $datetime_inicio2->setDate($datetime_inicio2->format('Y'), $datetime_inicio2->format('m'), 28);
            }
            // var_dump($datetime_inicio->format('Y-m-d'));
            $intervalo = $datetime_fin->diff($datetime_inicio);

            // $datetime_inicio->modify('+1 day');
            $intervalo_dias = $intervalo->format("%d");
            $intervalo_meses = $intervalo->format("%m");
            $intervalo_anios = $intervalo->format("%y") * 12;

            $intervalo_meses_final = $intervalo_meses + $intervalo_anios;

            // ACTUALIZAR VALORES DE ADELANTOS EN CASO SEAN X,Y ó Z
            $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'x'), $intervalo_meses_final - 2));
            $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'y'), $intervalo_meses_final - 1));
            $adelantos_array = array_replace($adelantos_array, array_fill_keys(array_keys($adelantos_array, 'z'), $intervalo_meses_final));

            $indice_incremento = 0;

            // VALIDAMOS SI EL DIA LA FECHA DE INICIO DE CONTRATO ES MAYOR QUE 18 
            $fecha_obj = DateTime::createFromFormat('Y-m-d', $datos['result'][0]['fecha_inicio']);
            $obtener_dia = $fecha_obj->format('d');


            $add = 0;
            if ($obtener_dia >= 18) {
                $datetime_inicio->modify('+1 month');
                $datetime_inicio2->modify('+1 month');
                if ($bol == false) {
                    $add = 1;
                }
            } else {
                $add = 1;
            }



            for ($num_cuota = 1; $num_cuota <= $intervalo_meses_final + $add; $num_cuota++) {
                // $fecha_actual = clone $datetime_inicio;
                // $fecha_actual->add(new DateInterval('P' . $num_cuota . 'M'));

                // OBTENEMOS EL MES ACTUAL 
                // $datetime_inicio2 = new DateTime($datos['result'][0]['fecha_inicio']);
                $anio_mes_actual =  $datetime_inicio2->format('Y-m');
                //  var_dump("num ".$num_cuota." - ".$anio_mes_actual);
                $meses[] = $anio_mes_actual;
                // var_dump( $anio_mes_actual);
                // Avanzar al siguiente mes
                // $datetime_inicio->modify('+1 month');

                // INICIO RESET VARIALES LOCALES
                $descuento = 0;
                // FIN RESET VARIALES LOCALES


                // INICIO PERIODO INICIO Y FIN
                $periodo_inicio = $datetime_inicio->format('Y-m-d');

                $datetime_inicio->modify('+1 month');

                $intervalo_dias_excedente = $datetime_fin->diff($datetime_inicio);
                $num_dias_excedentes = $intervalo_dias_excedente->format('%R%a');
                if ($num_dias_excedentes > 0) {
                    $periodo_fin = $datetime_fin->format('Y-m-d');
                    $descuento = $renta - (($renta * $num_dias_excedentes) / 30);
                    // break;
                } else {
                    $datetime_inicio->modify('-1 day');
                    $periodo_fin_tmp = $datetime_inicio->format('Y-m-d');
                }
                // FIN PERIODO INICIO Y FIN


                // INICIO INCREMENTOS
                $contador_incremento_a_la_renta = 0;
                $posee_incrementos = '';

                for ($i = 0; $i < $num_incrementos; $i++) {

                    $valor = $array_incrementos[$i][0];
                    $tipo_valor = $array_incrementos[$i][1];
                    $tipo_continuidad =  $array_incrementos[$i][2];
                    $a_partir_del_anio_en_meses = (($array_incrementos[$i][3] - 1) * 12) + 1;

                    // if($a_partir_del_anio_en_meses  == $num_cuota){

                    // }

                    if ($tipo_continuidad == 1) { // EL
                        if ($num_cuota == $a_partir_del_anio_en_meses) {
                            $posee_incrementos = 'SI';

                            if ($contador_incremento_a_la_renta == 0) {
                                $renta = $renta + $incrementos;
                                $incrementos = 0;
                                $contador_incremento_a_la_renta++;
                            }

                            if ($tipo_valor == 1) {
                                $incrementos += $valor;
                            } else if ($tipo_valor == 2) {
                                $incrementos += ($renta * $valor) / 100;
                            }
                        } else {
                            $posee_incrementos = 'NO';
                        }
                        if ($num_cuota == ($a_partir_del_anio_en_meses + 12)) {
                            $renta = $renta + $incrementos;
                            $incrementos = 0;
                        }
                    } elseif ($tipo_continuidad == 2 || $tipo_continuidad == 3) { // ANUAL A PARTIR DEL
                        for ($j = $a_partir_del_anio_en_meses; $j <= $num_cuota; $j += 12) {
                            //var_dump("valor ".$tipo_valor."  ".$num_cuota."  J : - ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos." total ".$renta);
                            //   var_dump($num_incrementos." num_cuota: ".$num_cuota."  tipo_valor: ".$tipo_valor."  a partir del año mes: ".$a_partir_del_anio_en_meses." valor: ".$valor." incrementos ".$incrementos);

                            if ($tipo_valor == 1) {
                                if ($num_cuota == $j) {
                                    // var_dump($num_cuota." valorjj: ".$valor." tipo 1- ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos);
                                    $posee_incrementos = 'SI';

                                    if ($contador_incremento_a_la_renta == 0) {

                                        $renta = $renta + $incrementos;
                                        $incrementos = 0;
                                        $contador_incremento_a_la_renta++;
                                    }

                                    if ($tipo_valor == 1) {
                                        $incrementos += $valor;
                                        //var_dump($incrementos." valor: ".$valor." tipo 1- ".$j." a partir del año: ".$a_partir_del_anio_en_meses);
                                        //  var_dump("num_cuota: ".$num_cuota." a partir del año: ".$a_partir_del_anio_en_meses." valor: ".$valor." incrementos ".$incrementos);
                                        // var_dump($num_cuota." primero valor: ".$valor." tipo 1- ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos);
                                    }
                                    // } else if ($tipo_valor == 2) {

                                    //     $incrementos += ($renta * $valor) / 100;
                                    //     // var_dump($num_cuota."  J : - ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos." total ".$renta." valor inc ");

                                    //     // var_dump($incrementos." cuota :".$num_cuota." J: - ".$j." RENTA : ".$renta." valor: ".$valor." a partir del año: ".$a_partir_del_anio_en_meses);
                                    //    // var_dump($num_cuota." valor: ".$valor." tipo 1- ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos);

                                    // }

                                } else {
                                    $posee_incrementos = 'NO';
                                }
                            }
                            if ($tipo_valor == 2) {
                                if ($num_cuota == $j && $num_cuota == $a_partir_del_anio_en_meses) {
                                    // var_dump($num_cuota." valorjj: ".$valor." tipo 1- ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos);
                                    $posee_incrementos = 'SI';
                                    if ($contador_incremento_a_la_renta == 0) {

                                        $renta = $renta + $incrementos;
                                        $incrementos = 0;
                                        $contador_incremento_a_la_renta++;
                                    }

                                    // if ($tipo_valor == 1) {
                                    //     $incrementos += $valor;
                                    //     //var_dump($incrementos." valor: ".$valor." tipo 1- ".$j." a partir del año: ".$a_partir_del_anio_en_meses);
                                    //     //  var_dump("num_cuota: ".$num_cuota." a partir del año: ".$a_partir_del_anio_en_meses." valor: ".$valor." incrementos ".$incrementos);

                                    // } else

                                    if ($tipo_valor == 2) {

                                        $incrementos += ($renta * $valor) / 100;
                                        // var_dump($num_cuota." valorjj: ".$valor." tipo 1- ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos);

                                        // var_dump($num_cuota."  J : - ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos." total ".$renta." valor inc ");

                                        // var_dump($incrementos." cuota :".$num_cuota." J: - ".$j." RENTA : ".$renta." valor: ".$valor." a partir del año: ".$a_partir_del_anio_en_meses);
                                        // var_dump($num_cuota." valor: ".$valor." tipo 1- ".$j." a partir del año: ".$a_partir_del_anio_en_meses." incre ".$incrementos);

                                    }
                                } else {
                                    $posee_incrementos = 'NO';
                                }
                            }
                        }
                    }
                    //  var_dump($incrementos." - ".$num_incrementos." contador ".$i );
                }

                // FIN INCREMENTOS

                $total = ($renta + $incrementos);
                $renta_a_pagar = 0;
                // INICIO IMPUESTO A LA RENTA
                if ($impuesto_a_la_renta_id == '1' || $impuesto_a_la_renta_id == '2') {
                    // if ($impuesto_a_la_renta_id == '1') {
                    //     $importe_impuesto_a_la_renta = $total * 0.05;
                    //     $renta_a_pagar = $total - $importe_impuesto_a_la_renta;
                    // } elseif ($impuesto_a_la_renta_id == '2') {
                    //     $importe_impuesto_a_la_renta = ( $total * 1.05265 ) - $total;
                    //     $renta_a_pagar = $total;
                    // }

                    // // verificamos si existe carta de instruccion 
                    // if($carta_de_instruccion_id == 1){
                    //     $renta_a_pagar_ = $renta_a_pagar;
                    // }else{
                    //     $renta_a_pagar_ = $total;

                    // }

                    if ($impuesto_a_la_renta_id == 1) {
                        $impuesto_a_la_renta = $total * 0.05;
                        $renta_bruta = $total;

                        if ($carta_de_instruccion_id == 1) {
                            $renta_neta = $total - $impuesto_a_la_renta;
                        } elseif ($carta_de_instruccion_id == 2) {
                            $renta_neta = $total;
                        }
                    } elseif ($impuesto_a_la_renta_id == 2) {
                        $impuesto_a_la_renta = ($total * 1.05265) - $total;
                        $renta_bruta = $total + $impuesto_a_la_renta;
                        $renta_neta = $total;

                        if ($carta_de_instruccion_id == 1) {
                            $renta_neta = $total;
                        } elseif ($carta_de_instruccion_id == 2) {
                            $renta_neta = $total + $impuesto_a_la_renta;
                        }
                    }
                    // if($carta_de_instruccion_id == '1') {
                    if (in_array($num_cuota, $adelantos_array)) {
                        $provision['estado_pago'] =       1;
                    } else {
                        $provision['estado_pago'] =       0;
                    }
                    $provision['fecha_inicio'] =   $datos['result'][0]['fecha_inicio'];
                    $provision['fecha_fin'] =   $datos['result'][0]['fecha_fin'];
                    $provision['fecha_actual'] =   $anio_mes_actual;
                    $provision['renta_bruta'] =   round($renta_bruta, 2);
                    $provision['importe_cal'] =   "renta " . $renta . " incre" . $incrementos;

                    $provision['importe'] =       round($renta_neta, 2);
                    $provision['num_cuota'] =   $num_cuota;
                    $provision['periodo_inicio'] =   $periodo_inicio;
                    $provision['periodo_fin_tmp'] =   $periodo_fin_tmp;
                    // $provision['empresa_suscribe_id'] =   $empresa_suscribe_id;
                    $provision['tipo_moneda_id'] =   $tipo_moneda_id;
                    $provision['dia_de_pago'] =   $datos['result'][0]['dia_de_pago'];
                    $provision['contrato_id'] =   $contrato_id;
                    $provision['nombre_tienda'] =   $datos['result'][0]['nombre_tienda'];
                    $provision['impuesto_a_la_renta_id'] =   $carta_de_instruccion_id;
                    //  $provision['carta_de_instruccion_id'] =   $carta_de_instruccion_id;
                    // $provision['num_adelanto_id'] =   0;
                    array_push($array_provision, $provision);
                    // } 

                } else {
                    $renta_a_pagar = $total;
                }
                // FIN IMPUESTO A LA RENTA


                // INICIO DESCUENTO (ADELANTO)
                foreach ($array_adelantos as $value) {
                    if ($num_cuota == $value) {
                        $renta_a_pagar = 0;
                    }
                }
                // FIN DESCUENTO (ADELANTO)


                // INICIO RENTA
                if ($renta_a_pagar != 0) {
                    $provision['fecha_inicio'] =   $datos['result'][0]['fecha_inicio'];
                    $provision['fecha_fin'] =   $datos['result'][0]['fecha_fin'];
                    $provision['fecha_actual'] =   $anio_mes_actual;
                    $provision['fecha_actual'] =   $anio_mes_actual;

                    $provision['importe'] =       round($renta_neta, 2);
                    $provision['num_cuota'] =   $num_cuota;
                    $provision['periodo_inicio'] =   $periodo_inicio;
                    $provision['periodo_fin_tmp'] =   $periodo_fin_tmp;
                    // $provision['empresa_suscribe_id'] =   $empresa_suscribe_id;
                    $provision['tipo_moneda_id'] =   $tipo_moneda_id;
                    //$model->insertar_provision($array_provision); // insertamos la provision 

                    array_push($array_provision, $provision);
                }

                // FIN RENTA

                $datetime_inicio->modify('+1 day');

                if ($num_cuota > 200) {
                    break;
                }
                $datetime_inicio2->modify('+1 month');
            }

            // insertamos la provision 

            // FIN LLENAR INCREMENTOS
            // var_dump(json_encode($array_incrementos,JSON_UNESCAPED_UNICODE));

            //  return $array_provision;
            return json_encode($array_provision, JSON_UNESCAPED_UNICODE);

            //  var_dump($array_provision);

        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function enviar_correo_contabilidad_provisiones($destinatario)
    {
        $body = "";
        $body .= '<html>';

        $email_user_created = '';

            $body .= '<div>';
            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Provisiones contables</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';

       
            $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
            $body .= '<td>Area de contabilidad</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Mensaje</b></td>';
            $body .= '<td>Se aprobaron las provisiones de persona natural para el periodo  2023/08, favor de ejecutar los pagos.
                         Area de Contabilidad.</td>';
            $body .= '</tr>';

            $body .= '</table>';
            $body .= '</div>';

            $email_user_created = 'jaimeronald90@testtest.gmail.com';
        

        $body .= '<div>';
        $body .= '<br>';
        $body .= '</div>';

     

        $body .= '</html>';
        $body .= "";

        $correos_ad = [];
        array_push($correos_ad, $email_user_created);
        $correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
        $lista_correos = $correos->enviar_correo_contabilidad_provisiones($correos_ad);

        $cc = $lista_correos['cc'];
        $bcc = $lista_correos['bcc'];

        $request = [
            "subject" => "Prueba - Gestion - Sistema Contratos - Envio de provisiones a tesoreia:",
            "body"    => $body,
            "cc"      => $cc,
            "bcc"     => $bcc,
            "attach"  => [
                // $filepath . $file,
            ],
        ];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;


            $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
            $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
            $mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

            if (isset($request["cc"])) {
                foreach ($request["cc"] as $cc) {
                    $mail->addAddress($cc);
                }
            }

            if (isset($request["bcc"])) {
                foreach ($request["bcc"] as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            $mail->isHTML(true);
            $mail->Subject  = $request["subject"];
            $mail->Body     = $request["body"];
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();
            //return true;

        } catch (Exception $e) {
            $resultado = $mail->ErrorInfo;
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            //return false;
            echo json_encode($resultado);
        }
    }
    public function validar_envio_tesoreria($request){
        try{
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $provision_idIs = json_decode($request["provision_idIs"]);
            $provision_idIs = implode(',', $provision_idIs); // SE CONVIERTE A CADENA EL ARRAY
    
            $model =  new Provision();
            // var_dump( $provision_idIs);exit();
            if ($provision_idIs != "") {
                $provisiones = $model->validar_envio_tesoreria($provision_idIs); // SE ENVIA LOS LOS IDES DE LAS PROVISIONES
                $provisiones = mb_convert_encoding($provisiones, 'UTF-8');
    
            }
            $verificar = $provisiones['result'];
            if(sizeof($verificar) > 0){
                $resultado = true; // las provisiones aun no se han enviado 
            }else{
                $resultado = false;// las provisiones ya se han enviado 
            }
            return json_encode($resultado, JSON_UNESCAPED_UNICODE);
        }catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
       
    }
    
    public function obtener_json_data_update($request){
        // Obtener el contenido JSON desde la petición POST
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);
       $jsonData = $request["jsonData"];
       // Ruta de la carpeta donde deseas guardar el archivo JSON
       $rutaCarpeta = '/var/www/html/files_bucket/migracion/';
       // Nombre del archivo (puedes generar un nombre único o utilizar uno fijo)
       $nombreArchivo = 'data_cuentas_banc_25072023.json';

       
       // Ruta completa del archivo a guardar
       $rutaArchivo = $rutaCarpeta . $nombreArchivo;

       
       if (!is_dir($rutaCarpeta)) {
           mkdir($rutaCarpeta, 0777, true);
       }

       // $jsonDataString = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
       // Escribir el contenido JSON en el archivo
       if (file_put_contents($rutaArchivo, $jsonData)) {
           http_response_code(200); // Respuesta exitosa
       } else {
           http_response_code(500); // Error del servidor
       }
   }

    public function convertir_excel_json_data()
    {
        // Ruta del archivo Excel

        // Crear una instancia del lector de Excel

        $data_universo = json_decode(file_get_contents("/var/www/html/files_bucket/migracion/data_cuentas_banc_25072023.json"), true);
        $model =  new Provision();
        $error_update_dia_pago  = array();
        $error_update_data_beneficiario  = array();
        $error_update_data_beneficiario_cuenta = array();
        $data = array();
        // $data = $data_universo;
        // validacion de solo contratos vigentes
        $hoy = date('Y-m-d'); // FECHA ACTUAL EN FORMATO AÑOS-MES-DIA 

        // Restar un mes a la fecha actual
        $fecha_mes_pasado = date('Y-m-d', strtotime('-1 month', strtotime($hoy)));

        // Obtener el último día del mes pasado
        $ultimo_dia_mes_pasado = date('Y-m-t', strtotime($fecha_mes_pasado));
        $data_filtrada = array();
        foreach ($data_universo as $row) {
            if($row['TIPO_DOC_BENEFICIARIO']!=="NO UBICADO EN DATA DE LEANDRO" && $row['TIPO_DOC_BENEFICIARIO']=='DNI' && strlen($row['NRO_DOCUMENTO_BENEFICIARIO'])!=8){
                $data[] = "beneficiario: ".$row['beneficiario_id']."  centor de costos: ".$row['CC_ID'] ." DNI: ".$row['NRO_DOCUMENTO_BENEFICIARIO'];
             }
            // $repuesta = $model->validar_contratos_vigentes($row['contrato_id'], $ultimo_dia_mes_pasado);
            // if ($repuesta["result"] === 1) {
            //     $data[] = $row;
            // } 
            // var_dump($ultimo_dia_mes_pasado . " " . $row['contrato_id']);
        }
        // for ($i = 0; $i < count($data); $i++) {
        //     $contrato_id = intval(!isset($data[$i]['contrato_id']) ? '' : $data[$i]['contrato_id']);
        //     $DIA_DE_PAGO = !isset($data[$i]['DIA_DE_PAGO_VALIDO']) ? '' : intval($data[$i]['DIA_DE_PAGO_VALIDO']);



        //     // update dia de pago 
        //     // $DIA_DE_PAGO = $DIA_DE_PAGO == 0 ? null : $DIA_DE_PAGO;
        //     if($DIA_DE_PAGO != 0){
        //         $respuesta = $model->actualizar_dia_de_pago($contrato_id, $DIA_DE_PAGO);

        //         if ($respuesta["result"] == 0) {
        //             $error_update_dia_pago[] =  $respuesta['contrato_id'] ;  //$respuesta['contrato_id'] . "  result  " . $respuesta['result'];
        //         }
        //     }
        //     // var_dump($contrato_id." ".$DIA_DE_PAGO);
           
        // }

        // for ($i = 0; $i < count($data); $i++) {
        //     $contrato_id = intval(!isset($data[$i]['contrato_id']) ? '' : $data[$i]['contrato_id']);

        //     $TIPO_DOC_BENEFICIARIO_VALIDO = !isset($data[$i]['TIPO_DOC_BENEFICIARIO']) ? '' : $data[$i]['TIPO_DOC_BENEFICIARIO'];
        //     $NRO_DOCUMENTO_BENEFICIARIO_VALIDO = !isset($data[$i]['NRO_DOCUMENTO_BENEFICIARIO']) ? '' : $data[$i]['NRO_DOCUMENTO_BENEFICIARIO'];
        //     $NOMBRES_BENEFICIARIO_VALIDO = !isset($data[$i]['NOMBRES_BENEFICIARIO']) ? '' : $data[$i]['NOMBRES_BENEFICIARIO'];
        //     $BANCO_BENEFICIARIO_VALIDO = !isset($data[$i]['BANCO_BENEFICIARIO_ACTUAL']) ? '' : $data[$i]['BANCO_BENEFICIARIO_ACTUAL'];
        //     $NRO_CUENTA_BENEFICIARIO_VALIDO = !isset($data[$i]['NRO_CUENTA_BENEFICIARIO_VALIDO']) ? '' : $data[$i]['NRO_CUENTA_BENEFICIARIO_VALIDO'];
        //     $NRO_CUENTA_CCI_BENEFICIARIO_VALIDO = !isset($data[$i]['NRO_CUENTA_CCI_BENEFICIARIO']) ? '' : $data[$i]['NRO_CUENTA_CCI_BENEFICIARIO'];

        //     // $TIPO_DOC_BENEFICIARIO_VALIDO = $TIPO_DOC_BENEFICIARIO_VALIDO == ' ' ? 0 : $TIPO_DOC_BENEFICIARIO_VALIDO;

        //     // $NRO_DOCUMENTO_BENEFICIARIO_VALIDO = $NRO_DOCUMENTO_BENEFICIARIO_VALIDO == ' ' ? 0 : $NRO_DOCUMENTO_BENEFICIARIO_VALIDO;
        //     // $NOMBRES_BENEFICIARIO_VALIDO = $NOMBRES_BENEFICIARIO_VALIDO == ' ' ? '' : $NOMBRES_BENEFICIARIO_VALIDO;


        //     if ($TIPO_DOC_BENEFICIARIO_VALIDO === 'DNI') {
        //         $tipo_docu_identidad_id = 1;
        //     } elseif ($TIPO_DOC_BENEFICIARIO_VALIDO === 'RUC') {
        //         $tipo_docu_identidad_id = 2;
        //     } elseif ($TIPO_DOC_BENEFICIARIO_VALIDO === 'Pasaporte') {
        //         $tipo_docu_identidad_id = 3;
        //     } elseif ($TIPO_DOC_BENEFICIARIO_VALIDO === 'Carnet de Extranjeria') {
        //         $tipo_docu_identidad_id = 4;
        //     }elseif ($TIPO_DOC_BENEFICIARIO_VALIDO === 0) {
        //         $tipo_docu_identidad_id = 0;
        //     }

        //     // $BANCO_BENEFICIARIO_VALIDO_ = $BANCO_BENEFICIARIO_VALIDO == ' ' ? null : $BANCO_BENEFICIARIO_VALIDO;
        //     $banco_id = $BANCO_BENEFICIARIO_VALIDO;
        //     if ($TIPO_DOC_BENEFICIARIO_VALIDO !=="NO UBICADO EN DATA DE LEANDRO" ) {
        //         $partes = explode("|", $BANCO_BENEFICIARIO_VALIDO);

        //         $banco_id = intval($partes[0]);

        //     }

        //     //  var_dump($BANCO_BENEFICIARIO_VALIDO_);

        //     // $NRO_CUENTA_BENEFICIARIO_VALIDO = ($NRO_CUENTA_BENEFICIARIO_VALIDO == ' ' || $NRO_CUENTA_BENEFICIARIO_VALIDO == 0) ? null : $NRO_CUENTA_BENEFICIARIO_VALIDO;

        //     // $NRO_CUENTA_CCI_BENEFICIARIO_VALIDO = $NRO_CUENTA_CCI_BENEFICIARIO_VALIDO == ' ' ? null : $NRO_CUENTA_CCI_BENEFICIARIO_VALIDO;

        //     // update dia de pago 
        //     //    var_dump($contrato_id." - ".$tipo_docu_identidad_id." - ". $NRO_DOCUMENTO_BENEFICIARIO_VALIDO 
        //     //    ." - ". $NOMBRES_BENEFICIARIO_VALIDO ." - ".$banco_id." - ".$NRO_CUENTA_BENEFICIARIO_VALIDO." - ".$NRO_CUENTA_CCI_BENEFICIARIO_VALIDO);

        //     if($TIPO_DOC_BENEFICIARIO_VALIDO !=="NO UBICADO EN DATA DE LEANDRO"){
        //         $respuesta_beneficiario = $model->actualizar_data_beneficiario(
        //             $contrato_id,
        //             $tipo_docu_identidad_id,
        //             $NRO_DOCUMENTO_BENEFICIARIO_VALIDO,
        //             $NOMBRES_BENEFICIARIO_VALIDO,
        //             $banco_id,
        //             $NRO_CUENTA_CCI_BENEFICIARIO_VALIDO
        //         );
        //         if ($respuesta_beneficiario["result"] == 0) {
        //             $error_update_data_beneficiario[] = $respuesta_beneficiario['contrato_id'];  //$respuesta_beneficiario['contrato_id'] . "  result  " . $respuesta_beneficiario['result'];
        //         }
        //     }
        //     if($TIPO_DOC_BENEFICIARIO_VALIDO !=="NO UBICADO EN DATA DE LEANDRO" && $NRO_CUENTA_BENEFICIARIO_VALIDO!==0 ){
        //         $respuesta_beneficiario_cuenta = $model->actualizar_data_beneficiario_cuenta_bancaria(
        //             $contrato_id,
                  
        //             $NRO_CUENTA_BENEFICIARIO_VALIDO
        //         );
        //         // var_dump($respuesta_beneficiario_cuenta);

        //         //    var_dump($contrato_id." ".$DIA_DE_PAGO);
        //         if ($respuesta_beneficiario_cuenta["result"] === 0) {
        //             $error_update_data_beneficiario_cuenta[] = $respuesta_beneficiario_cuenta['contrato_id'];  //$respuesta_beneficiario['contrato_id'] . "  result  " . $respuesta_beneficiario['result'];
        //         }
        //     }
        // }


        $respuesta_actualizacion_data = [
            "error_update_dia_pago" => $data,
            // "error_update_data_beneficiario" => $error_update_data_beneficiario,
            // "error_update_data_beneficiario_cuenta" => $error_update_data_beneficiario_cuenta
        ];
        
        $respuesta_json = json_encode($respuesta_actualizacion_data, JSON_PRETTY_PRINT);

        // Establecer el encabezado para la respuesta JSON
        header('Content-Type: application/json');

        // Devolver la respuesta JSON
        echo $respuesta_json;
        // var_dump($error_update_data_beneficiario);
    }
   
    
}
$cron_job = new ProvisionesController();

// $json = json_decode(file_get_contents("php://input"));

// $fecha_limite = $json->fecha_limite;
// $action = $json->action;
// $contrato_id = $json->contrato_id;
// ...
// $jsonArgs = $argv[1];
// $parametros = json_decode($jsonArgs, true);

// // Ahora puedes acceder a los valores del JSON
// $fecha_limite = $parametros['fecha_limite'];
// $action = $parametros['action'];
// $contrato_id = $parametros['contrato_id'];
// // Mostrar los valores en el archivo de registro
// file_put_contents('/var/www/html/sys/controllers/provisiones/registro_log.log', 'La tarea de cron se ha ejecutado : ' . $fecha_limite . ", " . $action . ", " . $contrato_id . PHP_EOL, FILE_APPEND);

// Captura los parámetros desde la línea de comandos


if (isset($argv)) {
    $jsonArgs = $argv[1];
    
    $parametros = json_decode($jsonArgs, true);
    if(isset($parametros["action"]) && $parametros['action']=='generacion_de_provisiones'){
        $respuesta_cron = $cron_job->generacion_de_provisiones();
        

    }
    
  
}