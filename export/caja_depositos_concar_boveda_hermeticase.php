<?php
if(isset($_POST["sec_caja_hermeticase_concar_boveda_excel_ant"])){
    $get_data = $_POST["sec_caja_hermeticase_concar_boveda_excel"];
    $return = array();
    $return["memory_init"]=memory_get_usage();
    $return["time_init"] = microtime(true);
    date_default_timezone_set("America/Lima");
    include("../sys/global_config.php");
    include("../sys/db_connect.php");
    include("../sys/sys_login.php");

    $local_id = $get_data["sec_caja_hermeticase_concar_boveda_local_id"];
    $tipo_cambio = $get_data["sec_caja_hermeticase_concar_boveda_tipo_cambio"];
    $correlativo_inicial = $get_data["sec_caja_hermeticase_concar_boveda_correlativo_inicial"];
    $fecha_inicio = date("Y-m-d",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_inicio_concar"]));
    $fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_inicio_concar"]));
    $fecha_fin = date("Y-m-d",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]));
    $fecha_fin = date('Y-m-d', strtotime("+1 day", strtotime($fecha_fin)));
    $fecha_fin_pretty = date("d/m/Y",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]));
    $fecha_query  =  " AND rhpv.fecha_documento >= '" . $fecha_inicio . "'";
    $fecha_query .=  " AND rhpv.fecha_documento < '" . $fecha_fin . "'";
    $caja_correlativo = $get_data["sec_caja_hermeticase_concar_boveda_correlativo_inicial"];

	//	Filtrado por permisos de locales

		$permiso_locales="";
		if($login && $login["usuario_locales"]){
			$permiso_locales.=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
			}

    $local_query = "";
    if( $local_id != "_all_" && $local_id != "_all_terminales_" )
    {
        $local_query = " AND l.id = '".$local_id."'";
    }else{
		$local_query = $permiso_locales;
	}
    if($local_id != "" ){
        $locales = array();
        $local_titulo = array();
        $sql_command = "SELECT l.id,
                l.nombre,
                (
                    SELECT cli.ruc FROM tbl_contratos c
                    LEFT JOIN tbl_clientes cli ON cli.id = c.cliente_id
                    WHERE c.local_id = l.id
                    AND cli.estado = 1
                    LIMIT 1
                )
                AS ruc
            FROM tbl_locales l
            WHERE l.id NOT IN (1)
            AND l.reportes_mostrar = '1'
            AND l.operativo in (1,2)
            AND l.red_id = 1
        ";
        $sql_query = $mysqli->query($sql_command);
        $locales_array = [];
        while($itm = $sql_query->fetch_assoc()){
            $locales[$itm["id"]] = str_replace(" ","_",strtolower($itm["nombre"]));
            $locales_array[$itm["id"]] = $itm;
            $local_titulo[$itm["id"]] = strtoupper($itm["nombre"]);
        }
        $mysqli->query("COMMIT");
        $sql_tra = "SELECT
                rhpv.id ,
                rhpv.numero ,
                rhpv.glosa,
                l.id AS local_id,
                l.cc_id,
                l.nombre AS local_nombre,
                rhpv.fecha_documento AS fecha_Excel,
                rhpv.importe AS importe,
                rhpv.saldo AS saldo
            FROM tbl_repositorio_hermeticase_prestamos_boveda rhpv
            LEFT JOIN tbl_locales l ON l.id = rhpv.local_id
            WHERE glosa = 'PRESTAMOS BOVEDAS'
            -- AND l.cc_id in ( 3032 ,3055,3125,3020 ,3097,3119)
            -- AND l.cc_id in ( 3205)
            AND rhpv.saldo > 0
            ORDER BY rhpv.fecha_documento ASC
            ";
        $result_transac = $mysqli->query($sql_tra);
        $prestamos_bovedas = [];
        while($r = $result_transac->fetch_assoc()){
            $prestamos_bovedas[$r["local_id"]][$r["id"]] = $r;
        }

        $sql_tra = "SELECT  
                rhpv.id , 
                rhpv.numero , 
                rhpv.glosa,
                l.id AS local_id,
                l.cc_id,
                l.nombre AS local_nombre,
                rhpv.fecha_documento AS fecha_Excel,
                rhpv.saldo,
                rhpv.importe AS importe
            FROM tbl_repositorio_hermeticase_prestamos_boveda rhpv
            LEFT JOIN tbl_locales l ON l.id = rhpv.local_id
            WHERE glosa != 'PRESTAMOS BOVEDAS' 
            -- AND l.cc_id in ( 3032 ,3055,3125,3020 ,3097,3119)
            -- AND l.cc_id in ( 3205)
            $fecha_query
            ";

        $result_transac = $mysqli->query($sql_tra);
        //echo "<pre>sql_tra:";print_r($sql_tra);echo "</pre>";
        $registros = [];
        while($r = $result_transac->fetch_assoc()){
            $registros[] = $r;
        }


        $sql_tra = "SELECT
                thm.id ,
                thm.nro_doc ,
                DATE(th.fecha_inicio) AS fecha_Excel,
                l.id AS local_id,
                l.cc_id,
                l.nombre AS local_nombre,
                th.monto AS importe
            FROM tbl_transacciones_hermeticase_movimientos thm
            LEFT JOIN tbl_transacciones_hermeticase th ON TRIM(LEADING '0' FROM th.nro_operacion) = TRIM(LEADING '0' FROM thm.nro_doc)
            LEFT JOIN tbl_locales l ON l.id = th.local_id
            WHERE thm.importe > 0 AND th.caja_id IS NOT NULL
            ";
        $result_transac = $mysqli->query($sql_tra);
        $mov_array = [];
        while($r = $result_transac->fetch_assoc()){
            $imp = rtrim((strpos($r["importe"],".") !== false ? rtrim($r["importe"], "0") : $r["importe"]),".");
            $mov_array[$r["local_id"]][$r["fecha_Excel"]][$imp] = $r;
        }


        //*turnos caja */
        $fecha_query  =  " AND th.fecha_inicio >= '" . $fecha_inicio . "'";
        $fecha_query .=  " AND th.fecha_inicio < '" . $fecha_fin . "'";
        $caja_command = "SELECT
                c.id,
            (SELECT SUM(IFNULL(df.valor,0)) FROM tbl_caja_datos_fisicos df WHERE df.caja_id = c.id AND df.tipo_id = '26') AS hermeticase_boveda
            FROM tbl_caja c
            LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
            LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
            WHERE c.estado = '1'
            AND c.validar = '1'";

        if(!($local_id == "_all_" || $local_id == "_all_terminales_")){
            $caja_command .= " AND l.id = '".$local_id."'";
        }else{
            $caja_command .= $permiso_locales;
        }
        $caja_command .= " AND c.fecha_operacion >= '".$fecha_inicio."'
            AND c.fecha_operacion < '".$fecha_fin."'
            AND
            (
                (SELECT SUM(IFNULL(df.valor,0)) FROM tbl_caja_datos_fisicos df WHERE df.caja_id = c.id AND df.tipo_id = '26')
            ) > 0
            GROUP BY c.fecha_operacion, l.id, c.turno_id
            ORDER BY c.fecha_operacion ASC, l.nombre ASC, c.turno_id ASC
        ";
        $mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
        //echo $caja_command;
        $caja_query = $mysqli->query($caja_command);
        $cajas = [];
        while($c = $caja_query->fetch_assoc()){
            $cajas[$c["id"]]= $c;
        }
        if($mysqli->error){
            print_r($mysqli->error);
            exit();
        }
        $mysqli->query("COMMIT");
        //echo "<pre>cajas:";print_r($cajas);echo "</pre>";

        $sql_tra = "SELECT
                thm.nro_doc,
                th.caja_id ,
                l.id AS local_id,
                l.cc_id,
                l.nombre AS 'local_nombre',
                th.nro_operacion AS 'nro_operacion',
                th.monto AS 'importe',
                th.fecha_inicio AS 'fecha_Excel',
                th.tipo AS 'tipo_transaccion'
            FROM tbl_transacciones_hermeticase th
            LEFT JOIN tbl_caja c on c.id = th.caja_id
            LEFT JOIN tbl_locales l ON l.id = th.local_id
            LEFT JOIN tbl_transacciones_hermeticase_movimientos thm ON TRIM(LEADING '0' FROM thm.nro_doc) = th.nro_operacion
            WHERE th.caja_id IS NOT NULL AND
            (
                (SELECT SUM(IFNULL(df.valor,0)) FROM tbl_caja_datos_fisicos df WHERE df.caja_id = c.id AND df.tipo_id = '26')
            ) = th.monto
            $fecha_query
            ";

        $result_transac = $mysqli->query($sql_tra);
        $turnos_caja_boveda = [];
        while($r = $result_transac->fetch_assoc()){
            $turnos_caja_boveda[$r["local_id"]][] = $r;
        }
        //echo "<pre>turnos_caja_boveda:";print_r($turnos_caja_boveda);echo "</pre>";
        /*turnos caja*/
        
        $mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
        $table = array();
        $table["tbody"] = array(); 

        $fecha_inicio_correlativo = date("Y-m-01",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_inicio_concar"]));
        $fecha_fin_correlativo = date("Y-m-01",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]));

            //echo "<pre>mov_array:";print_r($mov_array);echo "</pre>";
        // //echo "<pre>registros:";print_r($registros);echo "</pre>";
        foreach ($registros as  $row) {
            $haber_importe_Original = $row["importe"];
            $haber_importe_dolares = 0;
            $haber_importe_soles = 0;
            $nombre_Glosa = "";
            $cc = "";
            $tipo_documento = "EN";
            $debe_Haber = "D";
            $haber_importe_soles = $haber_importe_Original;
            $haber_importe_dolares = ($haber_importe_Original/(float)$tipo_cambio);
            $cuenta_contable = "10411024";
            $codigo_anexo = $cuenta_contable;
            $nro_documento = '0000'.(string)"422309";
            $codigo_area = '101';
            $fecha_Deposito = date("d/m/Y",strtotime($row["fecha_Excel"]));
            $mes = date("m",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]));
            
            $importe = $row["importe"];
            $haberes = [];
            //echo "<pre>row:";print_r($row);echo "</pre>";

            $imp = rtrim((strpos($row["importe"],".") !== false ? rtrim($row["importe"], "0") : $row["importe"]),".");
            if( isset( $mov_array[$row["local_id"]][$row["fecha_Excel"]][$imp] ) )
            {
                $nro_documento = $mov_array[$row["local_id"]] [$row["fecha_Excel"]] [$imp]["nro_doc"];
            }
            else
            {
                continue;
            }
            if( isset($prestamos_bovedas[$row["local_id"]]))
            {
                $saldo = $importe;
                foreach( $prestamos_bovedas[$row["local_id"]] as $id => $prest )
                {
                    $saldo_prest = $prest["saldo"];
                    if( $saldo <= 0 || $prestamos_bovedas[$row["local_id"]][$prest["id"]]["saldo"] <= 0 )
                    {
                        break;
                    }
                    if( $saldo == $saldo_prest)
                    {
                        $haberes[] = [
                            "id" => $prest["id"] ,
                            "debe_id" => $row["id"],
                            "importe" => $saldo,
                            "saldo" => 0 
                        ];
                        $update_saldo = "UPDATE tbl_repositorio_hermeticase_prestamos_boveda
                            SET saldo = (saldo  - $saldo )
                            WHERE id = ". $prest['id'];
                        //$mysqli->query($update_saldo);
                        $prestamos_bovedas[$row["local_id"]][$prest["id"]]["saldo"] = 0 ;
                        break;
                    }
                    if( $saldo > $saldo_prest)
                    {
                        $haberes[] = [
                            "id" => $prest["id"] ,
                            "debe_id" => $row["id"],
                            "importe" => $saldo_prest,
                            "saldo" => 0
                        ];
                        $saldo =  $saldo - $saldo_prest ;//4000- 3000 = 1000;
                        $update_saldo = "UPDATE tbl_repositorio_hermeticase_prestamos_boveda 
                            SET saldo = (saldo  - $saldo )
                            WHERE id = ".$prest['id'];
                        //$mysqli->query($update_saldo);
                        $prestamos_bovedas[$row["local_id"]][$prest["id"]]["saldo"] = ($prestamos_bovedas[$row["local_id"]][$prest["id"]]["saldo"] - $saldo) ;
                        continue;
                    }
                    if( $saldo < $saldo_prest)
                    {
                        //$saldo =  ;//1500- 1000 = 500;
                        $haberes[] = [
                            "id" => $prest["id"] ,
                            "debe_id" => $row["id"],
                            "importe" => $saldo,
                            "saldo" =>   ($saldo_prest - $saldo )
                        ];
                        $saldo = ($saldo_prest - $saldo );
                        $update_saldo = "UPDATE tbl_repositorio_hermeticase_prestamos_boveda 
                            SET saldo = (saldo  - $saldo )
                            WHERE id = ". $prest['id'];
                        //$mysqli->query($update_saldo);
                        $prestamos_bovedas[$row["local_id"]][$prest["id"]]["saldo"] = $saldo ;
                        break;
                    }

                }
            }
            $suma_h = array_sum(array_map(function($item) { 
                return $item['importe']; 
            }, $haberes));
            if( $suma_h < $haber_importe_soles )
            {
                continue;
            }
            $nro_comprobante = $mes.zerofill($caja_correlativo,4);
            if ( count($haberes) > 0 )
            {
                $glosa_principal = "Dev.Boveda " . $row["cc_id"] . " " . $row["local_nombre"];
                $tipo_documento = 'EN';
                $codigo_area = "101";
                $tr = array();
                $tr["sub_Diario"] = '2120';
                $tr["nro_Comprobante"] = $nro_comprobante;
                $tr["fecha_Comprobante"] = $fecha_fin_pretty;
                $tr["codigo_Moneda"] = 'MN';
                $tr["glosa_Principal"] = $glosa_principal;
                $tr["tipo_Cambio"] = $tipo_cambio;
                $tr["tipo_Conversion"] = 'V';
                $tr["flag_Conversion"] = 'S';
                $tr["fecha_Tipo_Cambio"] = $fecha_fin_pretty;
                $tr["cuenta_Contable"] = $cuenta_contable;
                $tr["codigo_Anexo"] =  $codigo_anexo;
                $tr["codigo_Centro_Costo"] = '';
                $tr["debe_Haber"] = $debe_Haber;
                $tr["importe_Original"] = number_format($haber_importe_soles, 2, ".", "");
                $tr["importe_Dolares"] = number_format(($haber_importe_soles/(float)$tipo_cambio), 2, ".", "");
                $tr["importe_Soles"] = number_format($haber_importe_soles, 2, ".", "");
                $tr["tipo_Documento"] = $tipo_documento;
                $tr["nro_Documento"] = $nro_documento;
                $tr["fecha_Documento"] = $fecha_Deposito;
                $tr["fecha_Vencimiento"] = $fecha_Deposito;
                $tr["codigo_Area"] = $codigo_area;
                $tr["glosa_Detalle"] = $glosa_principal;
                $tr["codigo_Anexo_Auxiliar"] = '';
                $tr["medio_Pago"] = '001';
                $tr["tipo_Documento_Referencia"] = '';
                $tr["numero_Documento_Referencia"] = '';
                $tr["fecha_Documento_Referencia"] =  '';
                $tr["nro_Registradora"] = '';
                $tr["base_Imponible"] = '';
                $tr["igv_Documento"] = '';
                $tr["tipo_Referencia"] = '';
                $tr["numero_Serie"] = '';
                $tr["fecha_Operacion"] = '';
                $tr["tipo_Tasa"] = '';
                $tr["tasa_Detraccion_Percepcion"] = '';
                $tr["importe_Base_Dolares"] = '';
                $tr["importe_Base_Soles"] = '';
                $tr["tipo_Cambio_F"] = '';
                $tr["importe_Igv_Fiscal"] = '';
                $table["tbody"][] = $tr;
                $caja_correlativo++;
            }
            //echo "<pre>haberes:";print_r($haberes);echo "</pre>";
            
            foreach( $haberes as $hab)
            {
                $haber_importe_soles = $hab["importe"];
                $glosa_principal = "Dev.Boveda " . $row["cc_id"] . " " . $row["local_nombre"];
                $codigo_anexo = $row["cc_id"] . "-FONDO BOVEDA";
                $debe_Haber = "H";
                $tipo_documento = 'PR';
                $nro_documento = '0'.date("m",strtotime($row["fecha_Excel"])) . "-" .date("Y",strtotime($row["fecha_Excel"]));
                $codigo_area = "343";
                $codigo_anexo = $row["cc_id"] . "-FONDO BOVEDA";
                $cuenta_contable = "101121";
                $tr = array();
                $tr["sub_Diario"] = '2120';
                $tr["nro_Comprobante"] = $nro_comprobante;
                $tr["fecha_Comprobante"] = $fecha_fin_pretty;
                $tr["codigo_Moneda"] = 'MN';
                $tr["glosa_Principal"] = $glosa_principal;
                $tr["tipo_Cambio"] = $tipo_cambio;
                $tr["tipo_Conversion"] = 'V';
                $tr["flag_Conversion"] = 'S';
                $tr["fecha_Tipo_Cambio"] = $fecha_fin_pretty;
                $tr["cuenta_Contable"] = $cuenta_contable;
                $tr["codigo_Anexo"] =  $codigo_anexo;
                $tr["codigo_Centro_Costo"] = '';
                $tr["debe_Haber"] = $debe_Haber;
                $tr["importe_Original"] = number_format($haber_importe_soles, 2, ".", "");
                $tr["importe_Dolares"] = number_format(($haber_importe_soles/(float)$tipo_cambio), 2, ".", "");
                $tr["importe_Soles"] = number_format($haber_importe_soles, 2, ".", "");
                $tr["tipo_Documento"] = $tipo_documento;
                $tr["nro_Documento"] = $nro_documento;
                $tr["fecha_Documento"] = $fecha_Deposito;
                $tr["fecha_Vencimiento"] = $fecha_Deposito;
                $tr["codigo_Area"] = $codigo_area;
                $tr["glosa_Detalle"] = $glosa_principal;
                $tr["codigo_Anexo_Auxiliar"] = 'A0003';
                $tr["medio_Pago"] = '';
                $tr["tipo_Documento_Referencia"] = $tipo_documento;
                $tr["numero_Documento_Referencia"] = $nro_documento;
                $tr["fecha_Documento_Referencia"] = $fecha_Deposito;
                $tr["nro_Registradora"] = '';
                $tr["base_Imponible"] = '';
                $tr["igv_Documento"] = '';
                $tr["tipo_Referencia"] = '';
                $tr["numero_Serie"] = '';
                $tr["fecha_Operacion"] = '';
                $tr["tipo_Tasa"] = '';
                $tr["tasa_Detraccion_Percepcion"] = '';
                $tr["importe_Base_Dolares"] = '';
                $tr["importe_Base_Soles"] = '';
                $tr["tipo_Cambio_F"] = '';
                $tr["importe_Igv_Fiscal"] = '';
                $table["tbody"][] = $tr;
            }			
            $caja_correlativo++;
        }

        $mes = date("m",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]));
        // foreach ($turnos_caja_boveda as $id_local => $transacciones) {
        //     $haber_importe_Original = 0;
        //     $haber_importe_dolares = 0;
        //     $haber_importe_soles = 0;
        //     $nombre_Glosa = "";
        //     $cc = "";
        //     $tipo_documento = "";
        //     $debe_Haber = "";
        //     //echo "<pre>caja:";print_r($cajas);echo "</pre>";
        //     foreach ($transacciones as $key => $value) {
        //         $nro_comprobante = $mes.zerofill($caja_correlativo,4);
        //         $tipo_documento = "EN";
        //         $debe_Haber = "D";
        //         $haber_importe_soles = (float)$value["importe"];
        //         $haber_importe_dolares = ($haber_importe_Original/(float)$tipo_cambio);
        //         $cuenta_contable = "10411024";
        //         $codigo_anexo = $cuenta_contable;
        //         $nro_documento = '0000'.(string)$value["nro_operacion"];
        //         $codigo_area = '101';
        //         $fecha_Deposito = date("d/m/Y",strtotime($value["fecha_Excel"]));

        //         $glosa_principal = cortar_cadena("Dev.Boveda " . $value["cc_id"] . " " . $value["local_nombre"],30);

        //         $tr = array();
        //         $tr["sub_Diario"] = '2120';
        //         $tr["nro_Comprobante"] = $nro_comprobante;
        //         $tr["fecha_Comprobante"] = $fecha_fin_pretty;
        //         $tr["codigo_Moneda"] = 'MN';
        //         $tr["glosa_Principal"] = $glosa_principal;
        //         $tr["tipo_Cambio"] = $tipo_cambio;
        //         $tr["tipo_Conversion"] = 'V';
        //         $tr["flag_Conversion"] = 'S';
        //         $tr["fecha_Tipo_Cambio"] = $fecha_fin_pretty;
        //         $tr["cuenta_Contable"] = $cuenta_contable;
        //         $tr["codigo_Anexo"] =  $codigo_anexo;// isset($locales_array[$valueCaja] ) ? $locales_array[$valueCaja]["ruc"] : "";
        //         $tr["codigo_Centro_Costo"] = '';
        //         $tr["debe_Haber"] = $debe_Haber;
        //         $tr["importe_Original"] = number_format($haber_importe_soles, 2, ".", "");
        //         $tr["importe_Dolares"] = number_format(($haber_importe_soles/(float)$tipo_cambio), 2, ".", "");
        //         $tr["importe_Soles"] = number_format($haber_importe_soles, 2, ".", "");
        //         $tr["tipo_Documento"] = $tipo_documento;
        //         $tr["nro_Documento"] = $nro_documento;
        //         $tr["fecha_Documento"] = $fecha_Deposito;
        //         $tr["fecha_Vencimiento"] = $fecha_Deposito;
        //         $tr["codigo_Area"] = $codigo_area;
        //         $tr["glosa_Detalle"] = $glosa_principal;
        //         $tr["codigo_Anexo_Auxiliar"] = '';
        //         $tr["medio_Pago"] = '001';
        //         $tr["tipo_Documento_Referencia"] = '';
        //         $tr["numero_Documento_Referencia"] = '';
        //         $tr["fecha_Documento_Referencia"] =  $fecha_Deposito;
        //         $tr["nro_Registradora"] = '';
        //         $tr["base_Imponible"] = '';
        //         $tr["igv_Documento"] = '';
        //         $tr["tipo_Referencia"] = '';
        //         $tr["numero_Serie"] = '';
        //         $tr["fecha_Operacion"] = '';
        //         $tr["tipo_Tasa"] = '';
        //         $tr["tasa_Detraccion_Percepcion"] = '';
        //         $tr["importe_Base_Dolares"] = '';
        //         $tr["importe_Base_Soles"] = '';
        //         $tr["tipo_Cambio_F"] = '';
        //         $tr["importe_Igv_Fiscal"] = '';
        //         $table["tbody"][] = $tr;

        //         //tipo_documento =>  venta me   , herm boveda  pr //
        //         // echo $value["caja_id"];
        //         $caja_boveda  = $cajas[$value["caja_id"]]["hermeticase_boveda"];
        //         $tipo_documento = "PR";
        //         $debe_Haber = "H";

        //         $nombre_Glosa = $value["local_nombre"];
        //         $cc = $value["cc_id"];
        //         $haber_importe_Original += (float)$value["importe"];
        //         $haber_importe_dolares += (float)$value["importe"]/(float)$tipo_cambio;
        //         $haber_importe_soles += (float)$value["importe"];

        //         $cuenta_contable = '101121';
        //         $codigo_anexo = $value["cc_id"]."-FONDO BOVEDA";
        //         $codigo_area = '343';
        //         $nro_documento = '0'.date("m-Y", strtotime($value["fecha_Excel"]));

        //         date("m").zerofill($caja_correlativo,4);
        //         $tr = array();
        //         $tr["sub_Diario"] = '2120';
        //         $tr["nro_Comprobante"] = $nro_comprobante;
        //         $tr["fecha_Comprobante"] = $fecha_fin_pretty;
        //         $tr["codigo_Moneda"] = 'MN';
        //         $tr["glosa_Principal"] = $glosa_principal;
        //         $tr["tipo_Cambio"] = $tipo_cambio;
        //         $tr["tipo_Conversion"] = 'V';
        //         $tr["flag_Conversion"] = 'S';
        //         $tr["fecha_Tipo_Cambio"] = $fecha_fin_pretty;
        //         $tr["cuenta_Contable"] =  $cuenta_contable;
        //         $tr["codigo_Anexo"] =  $codigo_anexo;
        //         $tr["codigo_Centro_Costo"] = '';
        //         $tr["debe_Haber"] = $debe_Haber;
        //         $tr["importe_Original"] = (float)$value["importe"];
        //         $tr["importe_Dolares"] = (float)$value["importe"]/(float)$tipo_cambio;//p
        //         $tr["importe_Soles"] = (float)$value["importe"];
        //         $tr["tipo_Documento"] = $tipo_documento;
        //         $tr["nro_Documento"] = $nro_documento;
        //         $tr["fecha_Documento"] = $fecha_Deposito;
        //         $tr["fecha_Vencimiento"] = $fecha_Deposito;
        //         $tr["codigo_Area"] = $codigo_area;
        //         $tr["glosa_Detalle"] = $glosa_principal;
        //         $tr["codigo_Anexo_Auxiliar"] = 'A00' . date("m", strtotime($value["fecha_Excel"]));
        //         $tr["medio_Pago"] = '';
        //         $tr["tipo_Documento_Referencia"] = $tipo_documento;
        //         $tr["numero_Documento_Referencia"] = $nro_documento;
        //         $tr["fecha_Documento_Referencia"] = $fecha_Deposito;
        //         $tr["nro_Registradora"] = '';
        //         $tr["base_Imponible"] = '';
        //         $tr["igv_Documento"] = '';
        //         $tr["tipo_Referencia"] = '';
        //         $tr["numero_Serie"] = '';
        //         $tr["fecha_Operacion"] = '';
        //         $tr["tipo_Tasa"] = '';
        //         $tr["tasa_Detraccion_Percepcion"] = '';
        //         $tr["importe_Base_Dolares"] = '';
        //         $tr["importe_Base_Soles"] = '';
        //         $tr["tipo_Cambio_F"] = '';
        //         $tr["importe_Igv_Fiscal"] = '';

        //         $table["tbody"][] = $tr;
        //     }
        //     $caja_correlativo++;
        // }

        date_default_timezone_set('America/Mexico_City');

        if (PHP_SAPI == 'cli')
            die('Este archivo solo se puede ver desde un navegador web');

        require_once '../phpexcel/classes/PHPExcel.php';  
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("GestionApuestaTotal") // Nombre del autor
            ->setLastModifiedBy("Codedrinks") //Ultimo usuario que lo modificó
            ->setTitle("Reporte Concar") // Titulo
            ->setSubject("Reporte Excel Concar") //Asunto
            ->setDescription("Reporte Depositos") //Descripción
            ->setKeywords("depositos") //Etiquetas
            ->setCategory("Reporte excel"); //Categorias
        $tituloReporte = "Ingresos";
        $titulosColumnas = array(
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
            'Tipo Cambio para "F"',
            'Importe de IGV sin derecho credito fiscal',
            'Tasa IGV'
        );
        
        $restricciones = array(
            "Restricciones",/*A*/ 
            "Ver T.G. 02",/*B*/ 
            "Los dos primeros dígitos son el mes y los otros 4 siguientes un correlativo",/*C*/ 
            "",/*D*/ 
            "Ver T.G. 03",/*E*/ 
            "",/*F*/ 
            "Llenar  solo si Tipo de Conversión es 'C'. Debe estar entre >=0 y <=9999.999999",/*G*/ 
            "Solo: 'C'= Especial, 'M'=Compra, 'V'=Venta , 'F' De acuerdo a fecha",/*H*/ 
            "Solo: 'S' = Si se convierte, 'N'= No se convierte",/*I*/ 
            "Si  Tipo de Conversión 'F'",/*J*/ 
            "Debe existir en el Plan de Cuentas",/*K*/ 
            "Si Cuenta Contable tiene seleccionado Tipo de Anexo, debe existir en la tabla de Anexos",/*L*/
            "Si Cuenta Contable tiene habilitado C. Costo, Ver T.G. 05",/*M*/
            "'D' ó 'H'",/*N*/
            "Importe original de la cuenta contable. Obligatorio, debe estar entre >=0 y <=99999999999.99",/*O*/
            "Importe de la Cuenta Contable en Dólares. Obligatorio si Flag de Conversión de Moneda esta en 'N', debe estar entre >=0 y <=99999999999.99",/*P*/
            "Importe de la Cuenta Contable en Soles. Obligatorio si Flag de Conversión de Moneda esta en 'N', debe estra entre >=0 y <=99999999999.99",/*Q*/
            "Si Cuenta Contable tiene habilitado el Documento Referencia Ver T.G. 06",/*R*/
            "Si Cuenta Contable tiene habilitado el Documento Referencia Incluye Serie y Número",/*S*/
            "Si Cuenta Contable tiene habilitado el Documento Referencia",/*T*/
            "Si Cuenta Contable tiene habilitada la Fecha de Vencimiento",/*U*/
            "Si Cuenta Contable tiene habilitada el Area. Ver T.G. 26",
            "",
            "Si Cuenta Contable tiene seleccionado Tipo de Anexo Referencia",
            "Si Cuenta Contable tiene habilitado Tipo Medio Pago. Ver T.G. 'S1'",
            "Si Tipo de Documento es 'NA' ó 'ND' Ver T.G. 06",
            "Si Tipo de Documento es 'NC', 'NA' ó 'ND', incluye Serie y Número    /*AA*/",
            "Si Tipo de Documento es 'NC', 'NA' ó 'ND'",
            "Si Tipo de Documento es 'NC', 'NA' ó 'ND'. Solo cuando el Tipo Documento de Referencia 'TK'",
            "Si Tipo de Documento es 'NC', 'NA' ó 'ND'",
            "Si Tipo de Documento es 'NC', 'NA' ó 'ND'",
            "Si la Cuenta Contable tiene Habilitado Documento Referencia 2 y  Tipo de Documento es 'TK'",
            "Si la Cuenta Contable teien Habilitado Documento Referencia 2 y  Tipo de Documento es 'TK'",
            "Si la Cuenta Contable tiene Habilitado Documento Referencia 2. Cuando Tipo de Documento es 'TK', consignar la fecha de emision del ticket",
            "Si la Cuenta Contable tiene configurada la Tasa:  Si es '1' ver T.G. 28 y '2' ver T.G. 29",
            "Si la Cuenta Contable tiene conf. en Tasa:  Si es '1' ver T.G. 28 y '2' ver T.G. 29. Debe estar entre >=0 y <=999.99",
            "Si la Cuenta Contable tiene configurada la Tasa. Debe ser el importe total del documento y estar entre >=0 y <=99999999999.99",
            "Si la Cuenta Contable tiene configurada la Tasa. Debe ser el importe total del documento y estar entre >=0 y <=99999999999.99",
            "Especificar solo si Tipo Conversión es 'F'. Se permite 'M' Compra y 'V' Venta.",
            "Especificar solo para comprobantes de compras con IGV sin derecho de crédito Fiscal. Se detalle solo en la cuenta 42xxxx",
            "Obligatorio para comprobantes de compras, valores validos 0,10,18."
            );
        $tamano_formato = array(
                "Tamaño/Formato",
                "4 Caracteres",
                "6 Caracteres",
                "dd/mm/aaaa",
                "2 Caracteres",
                "40 Caracteres",
                "Numérico 11, 6",
                "1 Caracteres",
                "1 Caracteres",
                "dd/mm/aaaa",
                "12 Caracteres",
                "18 Caracteres",
                "6 Caracteres",
                "1 Carácter",
                "Numérico 14,2",
                "Numérico 14,2",
                "Numérico 14,2",
                "2 Caracteres",
                "20 Caracteres",
                "dd/mm/aaaa",
                "dd/mm/aaaa",
                "3 Caracteres",
                "30 Caracteres",
                "18 Caracteres",
                "8 Caracteres",
                "2 Caracteres",
                "20 Caracteres",
                "dd/mm/aaaa",
                "20 Caracteres",
                "Numérico 14,2 ",
                "Numérico 14,2",
                " 'MQ'",
                "15 caracteres",
                "dd/mm/aaaa",
                "5 Caracteres",
                "Numérico 14,2",
                "Numérico 14,2",
                "Numérico 14,2",
                "1 Caracter",
                "Numérico 14,2",
                "Numérico 14,2"
            );
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            // ->setCellValue('A1',$tituloReporte) // Titulo del reporte
            ->setCellValue('A1',  $titulosColumnas[0])  //Titulo de las columnas
            ->setCellValue('B1',  $titulosColumnas[1])  //Titulo de las columnas
            ->setCellValue('C1',  $titulosColumnas[2])
            ->setCellValue('D1',  $titulosColumnas[3])
            ->setCellValue('E1',  $titulosColumnas[4])
            ->setCellValue('F1',  $titulosColumnas[5])  //Titulo de las columnas
            ->setCellValue('G1',  $titulosColumnas[6])
            ->setCellValue('H1',  $titulosColumnas[7])
            ->setCellValue('I1',  $titulosColumnas[8])
            ->setCellValue('J1',  $titulosColumnas[9])  //Titulo de las columnas
            ->setCellValue('K1',  $titulosColumnas[10])
            ->setCellValue('L1',  $titulosColumnas[11])
            ->setCellValue('M1',  $titulosColumnas[12])
            ->setCellValue('N1',  $titulosColumnas[13])  //Titulo de las columnas
            ->setCellValue('O1',  $titulosColumnas[14])
            ->setCellValue('P1',  $titulosColumnas[15])
            ->setCellValue('Q1',  $titulosColumnas[16])
            ->setCellValue('R1',  $titulosColumnas[17])
            ->setCellValue('S1',  $titulosColumnas[18])
            ->setCellValue('T1',  $titulosColumnas[19])  //Titulo de las columnas
            ->setCellValue('U1',  $titulosColumnas[20])
            ->setCellValue('V1',  $titulosColumnas[21])
            ->setCellValue('W1',  $titulosColumnas[22])
            ->setCellValue('X1',  $titulosColumnas[23])  //Titulo de las columnas
            ->setCellValue('Y1',  $titulosColumnas[24])
            ->setCellValue('Z1',  $titulosColumnas[25])
            ->setCellValue('AA1',  $titulosColumnas[26])
            ->setCellValue('AB1',  $titulosColumnas[27])  //Titulo de las columnas
            ->setCellValue('AC1',  $titulosColumnas[28])
            ->setCellValue('AD1',  $titulosColumnas[29])
            ->setCellValue('AE1',  $titulosColumnas[30])
            ->setCellValue('AF1',  $titulosColumnas[31])
            ->setCellValue('AG1',  $titulosColumnas[32])
            ->setCellValue('AH1',  $titulosColumnas[33])  //Titulo de las columnas
            ->setCellValue('AI1',  $titulosColumnas[34])
            ->setCellValue('AJ1',  $titulosColumnas[35])
            ->setCellValue('AK1',  $titulosColumnas[36])
            ->setCellValue('AL1',  $titulosColumnas[37])
            ->setCellValue('AM1',  $titulosColumnas[38])
            ->setCellValue('AN1',  $titulosColumnas[39])  //Titulo de las columnas;
            ->setCellValue('AO1',  $titulosColumnas[40])  //Titulo de las columnas;

            ->setCellValue('A2',  $restricciones[0])  //Titulo de las columnas
            ->setCellValue('B2',  $restricciones[1])  //Titulo de las columnas
            ->setCellValue('C2',  $restricciones[2])
            ->setCellValue('D2',  $restricciones[3])
            ->setCellValue('E2',  $restricciones[4])
            ->setCellValue('F2',  $restricciones[5])  //Titulo de las columnas
            ->setCellValue('G2',  $restricciones[6])
            ->setCellValue('H2',  $restricciones[7])
            ->setCellValue('I2',  $restricciones[8])
            ->setCellValue('J2',  $restricciones[9])  //Titulo de las columnas
            ->setCellValue('K2',  $restricciones[10])
            ->setCellValue('L2',  $restricciones[11])
            ->setCellValue('M2',  $restricciones[12])
            ->setCellValue('N2',  $restricciones[13])  //Titulo de las columnas
            ->setCellValue('O2',  $restricciones[14])
            ->setCellValue('P2',  $restricciones[15])
            ->setCellValue('Q2',  $restricciones[16])
            ->setCellValue('R2',  $restricciones[17])
            ->setCellValue('S2',  $restricciones[18])
            ->setCellValue('T2',  $restricciones[19])  //Titulo de las columnas
            ->setCellValue('U2',  $restricciones[20])
            ->setCellValue('V2',  $restricciones[21])
            ->setCellValue('W2',  $restricciones[22])
            ->setCellValue('X2',  $restricciones[23])  //Titulo de las columnas
            ->setCellValue('Y2',  $restricciones[24])
            ->setCellValue('Z2',  $restricciones[25])
            ->setCellValue('AA2',  $restricciones[26])
            ->setCellValue('AB2',  $restricciones[27])  //Titulo de las columnas
            ->setCellValue('AC2',  $restricciones[28])
            ->setCellValue('AD2',  $restricciones[29])
            ->setCellValue('AE2',  $restricciones[30])
            ->setCellValue('AF2',  $restricciones[31])
            ->setCellValue('AG2',  $restricciones[32])
            ->setCellValue('AH2',  $restricciones[33])  //Titulo de las columnas
            ->setCellValue('AI2',  $restricciones[34])
            ->setCellValue('AJ2',  $restricciones[35])
            ->setCellValue('AK2',  $restricciones[36])
            ->setCellValue('AL2',  $restricciones[37])
            ->setCellValue('AM2',  $restricciones[38])
            ->setCellValue('AN2',  $restricciones[39])  //Titulo de las columnas;
            ->setCellValue('AO2',  $restricciones[40])  //Titulo de las columnas;

            ->setCellValue('A3',  $tamano_formato[0])  //Titulo de las columnas
            ->setCellValue('B3',  $tamano_formato[1])  //Titulo de las columnas
            ->setCellValue('C3',  $tamano_formato[2])
            ->setCellValue('D3',  $tamano_formato[3])
            ->setCellValue('E3',  $tamano_formato[4])
            ->setCellValue('F3',  $tamano_formato[5])  //Titulo de las columnas
            ->setCellValue('G3',  $tamano_formato[6])
            ->setCellValue('H3',  $tamano_formato[7])
            ->setCellValue('I3',  $tamano_formato[8])
            ->setCellValue('J3',  $tamano_formato[9])  //Titulo de las columnas
            ->setCellValue('K3',  $tamano_formato[10])
            ->setCellValue('L3',  $tamano_formato[11])
            ->setCellValue('M3',  $tamano_formato[12])
            ->setCellValue('N3',  $tamano_formato[13])  //Titulo de las columnas
            ->setCellValue('O3',  $tamano_formato[14])
            ->setCellValue('P3',  $tamano_formato[15])
            ->setCellValue('Q3',  $tamano_formato[16])
            ->setCellValue('R3',  $tamano_formato[17])
            ->setCellValue('S3',  $tamano_formato[18])
            ->setCellValue('T3',  $tamano_formato[19])  //Titulo de las columnas
            ->setCellValue('U3',  $tamano_formato[20])
            ->setCellValue('V3',  $tamano_formato[21])
            ->setCellValue('W3',  $tamano_formato[22])
            ->setCellValue('X3',  $tamano_formato[23])  //Titulo de las columnas
            ->setCellValue('Y3',  $tamano_formato[24])
            ->setCellValue('Z3',  $tamano_formato[25])
            ->setCellValue('AA3',  $tamano_formato[26])
            ->setCellValue('AB3',  $tamano_formato[27])  //Titulo de las columnas
            ->setCellValue('AC3',  $tamano_formato[28])
            ->setCellValue('AD3',  $tamano_formato[29])
            ->setCellValue('AE3',  $tamano_formato[30])
            ->setCellValue('AF3',  $tamano_formato[31])
            ->setCellValue('AG3',  $tamano_formato[32])
            ->setCellValue('AH3',  $tamano_formato[33])  //Titulo de las columnas
            ->setCellValue('AI3',  $tamano_formato[34])
            ->setCellValue('AJ3',  $tamano_formato[35])
            ->setCellValue('AK3',  $tamano_formato[36])
            ->setCellValue('AL3',  $tamano_formato[37])
            ->setCellValue('AM3',  $tamano_formato[38])
            ->setCellValue('AN3',  $tamano_formato[39])
            ->setCellValue('AO3',  $tamano_formato[40])
        ;  //Titulo de las columnas;
        $i = 4;
        foreach ($table["tbody"] as $k => $tr) {
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$tr["sub_Diario"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$tr["nro_Comprobante"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$tr["fecha_Comprobante"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$tr["codigo_Moneda"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$tr["glosa_Principal"]);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$tr["tipo_Cambio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$tr["tipo_Conversion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$tr["flag_Conversion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,$tr["fecha_Tipo_Cambio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$i,$tr["cuenta_Contable"]);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$i,$tr["codigo_Anexo"]);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$i,$tr["codigo_Centro_Costo"]);
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$i,$tr["debe_Haber"]);
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$i,$tr["importe_Original"]);
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$i,$tr["importe_Dolares"]);
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i,$tr["importe_Soles"]);
            $objPHPExcel->getActiveSheet()->setCellValue('R'.$i,$tr["tipo_Documento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('S'.$i,$tr["nro_Documento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('T'.$i,$tr["fecha_Documento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('U'.$i,$tr["fecha_Vencimiento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('V'.$i,$tr["codigo_Area"]);
            $objPHPExcel->getActiveSheet()->setCellValue('W'.$i,$tr["glosa_Detalle"]);
            $objPHPExcel->getActiveSheet()->setCellValue('X'.$i,$tr["codigo_Anexo_Auxiliar"]);
            $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i,$tr["medio_Pago"]);
            $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i,$tr["tipo_Documento_Referencia"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i,$tr["numero_Documento_Referencia"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i,$tr["fecha_Documento_Referencia"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i,$tr["nro_Registradora"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AD'.$i,$tr["base_Imponible"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AE'.$i,$tr["igv_Documento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AF'.$i,$tr["tipo_Referencia"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AG'.$i,$tr["numero_Serie"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AH'.$i,$tr["fecha_Operacion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AI'.$i,$tr["tipo_Tasa"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i,$tr["tasa_Detraccion_Percepcion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AK'.$i,$tr["importe_Base_Dolares"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AL'.$i,$tr["importe_Base_Soles"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AM'.$i,$tr["tipo_Cambio_F"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AN'.$i,$tr["importe_Igv_Fiscal"]);
            $i++;
        }       

        $estiloTituloReporte = array(
            'font' => array(
                'name'      => 'Verdana',
                'bold'      => true,
                'italic'    => false,
                'strike'    => false,
                'size' =>16,
                'color'     => array(
                    'rgb' => 'FFFFFF'
                )
            ),
            'fill' => array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'argb' => 'FF220835')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );
            
        $estiloTituloColumnas = array(
            'font' => array(
                'name'  => 'calibri',
                'bold'  => false,
                'size'  => 10,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'color'=>array('rgb'=>'FFC000')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN ,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
            ),
            'alignment' =>  array(
                'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap'      => true
            )
        );
            
        $estiloInformacion = new PHPExcel_Style();
        $estiloInformacion->applyFromArray( array(
            'font' => array(
                'name'  => 'calibri',
                'bold'  => false,
                'size'  => 10,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            
        ));  

        $objPHPExcel->getActiveSheet()->getStyle('B1:AO1')->applyFromArray($estiloTituloColumnas);
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AN".($i-1));

        for($j=2;$j<=$i;$j++){
            $objPHPExcel->getActiveSheet()->getStyle('G'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle('O'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle('P'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle('Q'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle('L'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->getStyle('S'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(28);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(28);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(false);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setAutoSize(false);   
        $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(18);

        $objPHPExcel->getActiveSheet()->setTitle('Depositos');
        $objPHPExcel->setActiveSheetIndex(0);
        // Inmovilizar paneles
        //$objPHPExcel->getActiveSheet(0)->freezePane('A4');
        //$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

        $local_titulo_parcial = "";
        $local_id_parcial = "";
        $local_id_name = "";

        if ($get_data['sec_caja_hermeticase_concar_boveda_local_id'] == '_all_'){
            $local_titulo_parcial = "Todos";
            $local_id_parcial = "Todos";
            $local_id_name = -1;
        }  
        else 
        {
            $local_titulo_parcial = $local_titulo[$get_data['sec_caja_hermeticase_concar_boveda_local_id']];
            $local_id_parcial = $locales[$get_data['sec_caja_hermeticase_concar_boveda_local_id']];
            $local_id_name = $get_data['sec_caja_hermeticase_concar_boveda_local_id'];
        }

        $titulo_reporte_cajas = "REPORTE CONCAR ".$local_titulo_parcial;
        $titulo_file_reporte_cajas = "Depositos_Hermeticase_Caja_Concar_".$local_id_parcial."_".date("d-m-Y",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_inicio_concar"]))."_al_".date("d-m-Y",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]))."_".date("Ymdhis");

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_cajas.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $excel_export = 'export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
        $excel_path = '/var/www/html/' . $excel_export;
        $excel_path_download = $excel_export;
        $url = $titulo_file_reporte_cajas.'.xls';
        $objWriter->save($excel_path);

        $insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
        $insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
        $mysqli->query($insert_cmd);
        $exported_id = $mysqli->insert_id;

        $mysqli->query("
            INSERT INTO tbl_concar_hermeticase_boveda_historico (
                local_id,
                exported_id,
                usuario_id,
                cambio,
                correlativo,
                fecha_operacion,
                fecha_inicio,
                fecha_fin
            ) VALUES (
                '". $local_id_name ."',
                ".$exported_id.",
                ".$login["id"].",
                ".$get_data["sec_caja_hermeticase_concar_boveda_tipo_cambio"].",
                ".(($get_data["sec_caja_hermeticase_concar_boveda_correlativo_inicial"] != "") ? "'".$get_data["sec_caja_hermeticase_concar_boveda_correlativo_inicial"]."'" : "null").",

                '".date('Y-m-d H:i:s')."',
                '".$fecha_inicio."',
                '".$fecha_fin."'
            )
        ");
        if($mysqli->error){
            print_r($mysqli->error);
            exit();
        }

        echo json_encode(array(
            "path" => $excel_path_download,
            "url" => $titulo_file_reporte_cajas.'.xls',
            "tipo" => "excel",
            "ext" => "xls",
            "size" => filesize($excel_path),
            "fecha_registro" => date("d-m-Y h:i:s"),
            "sql" => $insert_cmd
        ));

        exit;
    }
    else{
        echo json_encode(array(
            "error" => true,
            "mensaje" => "No hay resultados para mostrar"
        ));
    }
}
if(isset($_POST["sec_caja_hermeticase_concar_boveda_excel"])){

    $get_data = $_POST["sec_caja_hermeticase_concar_boveda_excel"];
    $return = array();
    $return["memory_init"]=memory_get_usage();
    $return["time_init"] = microtime(true);
    date_default_timezone_set("America/Lima");
    include("../sys/global_config.php");
    include("../sys/db_connect.php");
    include("../sys/sys_login.php");

    $local_id = $get_data["sec_caja_hermeticase_concar_boveda_local_id"];
    $tipo_cambio = $get_data["sec_caja_hermeticase_concar_boveda_tipo_cambio"];
    $correlativo_inicial = $get_data["sec_caja_hermeticase_concar_boveda_correlativo_inicial"];
    $fecha_inicio = date("Y-m-d",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_inicio_concar"]));
    $fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_inicio_concar"]));
    $fecha_fin = date("Y-m-d",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]));
    $fecha_fin = date('Y-m-d', strtotime("+1 day", strtotime($fecha_fin)));
    $fecha_fin_pretty = date("d/m/Y",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]));
    $fecha_query  =  " AND rhpv.fecha_documento >= '" . $fecha_inicio . "'";
    $fecha_query .=  " AND rhpv.fecha_documento < '" . $fecha_fin . "'";
    $caja_correlativo = $get_data["sec_caja_hermeticase_concar_boveda_correlativo_inicial"];

    $locales = array();
    $local_titulo = array();
    $sql_command = "SELECT id,nombre FROM tbl_locales";
    $sql_query = $mysqli->query($sql_command);
    while($itm=$sql_query->fetch_assoc()){
        $locales[$itm["id"]] = str_replace(" ","_",strtolower($itm["nombre"]));
        $local_titulo[$itm["id"]] = strtoupper($itm["nombre"]);
    }

    //	Filtrado por permisos de locales
        $permiso_locales="";

        if($login["usuario_locales"]){
            $permiso_locales .=" WHERE l.id IN (".implode(",", $login["usuario_locales"]).") ";
            }

    if($local_id !=""){
        $query = "SELECT
                    (SELECT SUM(IFNULL(df.valor,0)) FROM tbl_caja_datos_fisicos df WHERE df.caja_id = c.id AND df.tipo_id = '26') ,
                    l.cc_id,
                    l.nombre AS local_nombre,
                    thm.id ,
                    thm.nro_doc ,
                    DATE(th.fecha_inicio) AS fecha_Excel,
                    DATE(th.fecha_inicio) AS fecha_operacion,
                    l.id AS local_id,
                    th.nro_operacion,
                    thm.nro_doc as numero_documento,
                    th.monto AS importe
                FROM tbl_transacciones_hermeticase_movimientos thm
                LEFT JOIN tbl_transacciones_hermeticase th ON TRIM(LEADING '0' FROM th.nro_operacion) = TRIM(LEADING '0' FROM thm.nro_doc)
                LEFT JOIN tbl_locales l ON l.id = th.local_id
                LEFT JOIN tbl_caja c ON c.id = th.caja_id
                WHERE thm.importe > 0 AND th.caja_id IS NOT NULL
                AND
                (
                    (SELECT SUM(IFNULL(df.valor,0)) FROM tbl_caja_datos_fisicos df WHERE df.caja_id = c.id AND df.tipo_id = '26')
                ) > 0
                AND (
                    (SELECT SUM(IFNULL(df.valor,0)) FROM tbl_caja_datos_fisicos df WHERE df.caja_id = c.id AND df.tipo_id = '26')
                ) = th.monto
                AND th.fecha_inicio >= '" . $fecha_inicio . "'
                AND th.fecha_inicio < '" . $fecha_fin . "'
            ";

        $result = $mysqli->query($query);
        $transacciones = []; //D
        while($row = $result->fetch_assoc()){
            $transacciones[$row["cc_id"]][] = $row;
            $total = $transacciones[$row["cc_id"]]["total"] ?? 0;
            $transacciones[$row["cc_id"]]["total"] = $total + $row["importe"];
        }
        //  echo "<pre>";print_r($transacciones);echo "<pre>";
        $query = "SELECT rhpv.id ,
                    rhpv.numero ,
                    rhpv.glosa,
                    rhpv.comprobante_sd AS sd,
                    l.id AS local_id,
                    l.cc_id,
                    l.nombre AS local_nombre,
                    rhpv.fecha_documento AS fecha_Excel,
                    rhpv.fecha_documento AS fecha_documento,
                    rhpv.importe AS importe,
                    rhpv.saldo AS saldo,
                    rhpv.documento as numero_documento
                FROM tbl_repositorio_hermeticase_prestamos_boveda rhpv
                LEFT JOIN tbl_locales l ON l.id = rhpv.local_id
                $permiso_locales
                -- WHERE glosa = 'PRESTAMOS BOVEDAS'
                ORDER BY rhpv.fecha_documento";

        $result = $mysqli->query($query);
        $prestamos_boveda = []; //H
        while($row = $result->fetch_assoc()){
            $prestamos_boveda[$row["cc_id"]][] = $row;
            $total = $prestamos_boveda[$row["cc_id"]]["total"] ?? 0;
            $prestamos_boveda[$row["cc_id"]]["total"] = $total + $row["importe"];
        }
        //echo "<pre>prestamos_boveda";print_r($prestamos_boveda);echo "<pre>";
        $table=array();
        $table["tbody"]=array();

        foreach ($transacciones as $key => $transacciones_group){
            // echo "<pre>prestamos_boveda[key]";print_r($prestamos_boveda[$key]);echo "<pre>";
            // echo "<pre>transacciones_group";print_r($transacciones_group);echo "<pre>";
            if(!array_key_exists($key, $prestamos_boveda)) continue;
            if ($transacciones_group["total"] <= $prestamos_boveda[$key]["total"]){
                $first_transaccion = $transacciones_group[0];
                $extra_data = [
                    "fecha_fin_pretty" => $fecha_fin_pretty,
                    "nombre_glosa" => cortar_cadena('Hermet. Dev.Boveda '.$first_transaccion["cc_id"].' '.$first_transaccion["local_nombre"], 40),
                    "glosa_detalle" => cortar_cadena('Hermet. Dev.Boveda '.$first_transaccion["cc_id"].' '.$first_transaccion["local_nombre"], 30),
                    "tipo_cambio" => $tipo_cambio,
                    "nro_comprobante" => date("m", strtotime($first_transaccion["fecha_operacion"])).zerofill($caja_correlativo,4)
                ];
                $transacciones_total = $transacciones_group["total"];
                foreach ($transacciones_group as $transacciones){
                    if (!is_array($transacciones)) continue;

                    $table["tbody"][] = format_row($transacciones, 0, $extra_data, 'd');
                }

                foreach ($prestamos_boveda[$key] as $prestamos){
                    if (!is_array($prestamos)) continue;

                    if ($transacciones_total <= $prestamos["importe"]){
                        $table["tbody"][] = format_row($prestamos, $transacciones_total, $extra_data, 'h');
                        break;
                    } else {
                        $transacciones_total -= $prestamos["importe"];
                        $table["tbody"][] = format_row($prestamos, $prestamos["importe"], $extra_data, 'h');
                    }
                }
                $caja_correlativo++;
            }
        }
        // echo "<pre>table";print_r($table["tbody"]);echo "<pre>";
        date_default_timezone_set('America/Mexico_City');
        if (PHP_SAPI == 'cli') die('Este archivo solo se puede ver desde un navegador web');

        require_once '../phpexcel/classes/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("GestionApuestaTotal") // Nombre del autor
        ->setTitle("Reporte Concar Boveda") // Titulo
        ->setSubject("Reporte Excel Concar Boveda") //Asunto
        ->setDescription("Reporte Depositos Boveda") //Descripción
        ->setKeywords("depositos") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias

        $titulosColumnas = array(
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
            'Tipo Cambio para "F"',
            'Importe de IGV sin derecho credito fiscal',
            'Tasa IGV'
        );
            $restricciones = array(
                "Restricciones",/*A*/ 
                "Ver T.G. 02",/*B*/ 
                "Los dos primeros dígitos son el mes y los otros 4 siguientes un correlativo",/*C*/ 
                "",/*D*/ 
                "Ver T.G. 03",/*E*/ 
                "",/*F*/ 
                "Llenar  solo si Tipo de Conversión es 'C'. Debe estar entre >=0 y <=9999.999999",/*G*/ 
                "Solo: 'C'= Especial, 'M'=Compra, 'V'=Venta , 'F' De acuerdo a fecha",/*H*/ 
                "Solo: 'S' = Si se convierte, 'N'= No se convierte",/*I*/ 
                "Si  Tipo de Conversión 'F'",/*J*/ 
                "Debe existir en el Plan de Cuentas",/*K*/ 
                "Si Cuenta Contable tiene seleccionado Tipo de Anexo, debe existir en la tabla de Anexos",/*L*/
                "Si Cuenta Contable tiene habilitado C. Costo, Ver T.G. 05",/*M*/
                "'D' ó 'H'",/*N*/
                "Importe original de la cuenta contable. Obligatorio, debe estar entre >=0 y <=99999999999.99",/*O*/
                "Importe de la Cuenta Contable en Dólares. Obligatorio si Flag de Conversión de Moneda esta en 'N', debe estar entre >=0 y <=99999999999.99",/*P*/
                "Importe de la Cuenta Contable en Soles. Obligatorio si Flag de Conversión de Moneda esta en 'N', debe estra entre >=0 y <=99999999999.99",/*Q*/
                "Si Cuenta Contable tiene habilitado el Documento Referencia Ver T.G. 06",/*R*/
                "Si Cuenta Contable tiene habilitado el Documento Referencia Incluye Serie y Número",/*S*/
                "Si Cuenta Contable tiene habilitado el Documento Referencia",/*T*/
                "Si Cuenta Contable tiene habilitada la Fecha de Vencimiento",/*U*/
                "Si Cuenta Contable tiene habilitada el Area. Ver T.G. 26",
                "",
                "Si Cuenta Contable tiene seleccionado Tipo de Anexo Referencia",
                "Si Cuenta Contable tiene habilitado Tipo Medio Pago. Ver T.G. 'S1'",
                "Si Tipo de Documento es 'NA' ó 'ND' Ver T.G. 06",
                "Si Tipo de Documento es 'NC', 'NA' ó 'ND', incluye Serie y Número    /*AA*/",
                "Si Tipo de Documento es 'NC', 'NA' ó 'ND'",
                "Si Tipo de Documento es 'NC', 'NA' ó 'ND'. Solo cuando el Tipo Documento de Referencia 'TK'",
                "Si Tipo de Documento es 'NC', 'NA' ó 'ND'",
                "Si Tipo de Documento es 'NC', 'NA' ó 'ND'",
                "Si la Cuenta Contable tiene Habilitado Documento Referencia 2 y  Tipo de Documento es 'TK'",
                "Si la Cuenta Contable teien Habilitado Documento Referencia 2 y  Tipo de Documento es 'TK'",
                "Si la Cuenta Contable tiene Habilitado Documento Referencia 2. Cuando Tipo de Documento es 'TK', consignar la fecha de emision del ticket",
                "Si la Cuenta Contable tiene configurada la Tasa:  Si es '1' ver T.G. 28 y '2' ver T.G. 29",
                "Si la Cuenta Contable tiene conf. en Tasa:  Si es '1' ver T.G. 28 y '2' ver T.G. 29. Debe estar entre >=0 y <=999.99",
                "Si la Cuenta Contable tiene configurada la Tasa. Debe ser el importe total del documento y estar entre >=0 y <=99999999999.99",
                "Si la Cuenta Contable tiene configurada la Tasa. Debe ser el importe total del documento y estar entre >=0 y <=99999999999.99",
                "Especificar solo si Tipo Conversión es 'F'. Se permite 'M' Compra y 'V' Venta.",
                "Especificar solo para comprobantes de compras con IGV sin derecho de crédito Fiscal. Se detalle solo en la cuenta 42xxxx",
                "Obligatorio para comprobantes de compras, valores validos 0,10,18."
                );
            $tamano_formato = array(
                    "Tamaño/Formato",
                    "4 Caracteres",
                    "6 Caracteres",
                    "dd/mm/aaaa",
                    "2 Caracteres",
                    "40 Caracteres",
                    "Numérico 11, 6",
                    "1 Caracteres",
                    "1 Caracteres",
                    "dd/mm/aaaa",
                    "12 Caracteres",
                    "18 Caracteres",
                    "6 Caracteres",
                    "1 Carácter",
                    "Numérico 14,2",
                    "Numérico 14,2",
                    "Numérico 14,2",
                    "2 Caracteres",
                    "20 Caracteres",
                    "dd/mm/aaaa",
                    "dd/mm/aaaa",
                    "3 Caracteres",
                    "30 Caracteres",
                    "18 Caracteres",
                    "8 Caracteres",
                    "2 Caracteres",
                    "20 Caracteres",
                    "dd/mm/aaaa",
                    "20 Caracteres",
                    "Numérico 14,2 ",
                    "Numérico 14,2",
                    " 'MQ'",
                    "15 caracteres",
                    "dd/mm/aaaa",
                    "5 Caracteres",
                    "Numérico 14,2",
                    "Numérico 14,2",
                    "Numérico 14,2",
                    "1 Caracter",
                    "Numérico 14,2",
                    "Numérico 14,2"
                );
        $objPHPExcel->setActiveSheetIndex(0)
            // ->setCellValue('A1',$tituloReporte) // Titulo del reporte
            ->setCellValue('A1',  $titulosColumnas[0])  //Titulo de las columnas
            ->setCellValue('B1',  $titulosColumnas[1])  //Titulo de las columnas
            ->setCellValue('C1',  $titulosColumnas[2])
            ->setCellValue('D1',  $titulosColumnas[3])
            ->setCellValue('E1',  $titulosColumnas[4])
            ->setCellValue('F1',  $titulosColumnas[5])  //Titulo de las columnas
            ->setCellValue('G1',  $titulosColumnas[6])
            ->setCellValue('H1',  $titulosColumnas[7])
            ->setCellValue('I1',  $titulosColumnas[8])
            ->setCellValue('J1',  $titulosColumnas[9])  //Titulo de las columnas
            ->setCellValue('K1',  $titulosColumnas[10])
            ->setCellValue('L1',  $titulosColumnas[11])
            ->setCellValue('M1',  $titulosColumnas[12])
            ->setCellValue('N1',  $titulosColumnas[13])  //Titulo de las columnas
            ->setCellValue('O1',  $titulosColumnas[14])
            ->setCellValue('P1',  $titulosColumnas[15])
            ->setCellValue('Q1',  $titulosColumnas[16])
            ->setCellValue('R1',  $titulosColumnas[17])
            ->setCellValue('S1',  $titulosColumnas[18])
            ->setCellValue('T1',  $titulosColumnas[19])  //Titulo de las columnas
            ->setCellValue('U1',  $titulosColumnas[20])
            ->setCellValue('V1',  $titulosColumnas[21])
            ->setCellValue('W1',  $titulosColumnas[22])
            ->setCellValue('X1',  $titulosColumnas[23])  //Titulo de las columnas
            ->setCellValue('Y1',  $titulosColumnas[24])
            ->setCellValue('Z1',  $titulosColumnas[25])
            ->setCellValue('AA1',  $titulosColumnas[26])
            ->setCellValue('AB1',  $titulosColumnas[27])  //Titulo de las columnas
            ->setCellValue('AC1',  $titulosColumnas[28])
            ->setCellValue('AD1',  $titulosColumnas[29])
            ->setCellValue('AE1',  $titulosColumnas[30])
            ->setCellValue('AF1',  $titulosColumnas[31])
            ->setCellValue('AG1',  $titulosColumnas[32])
            ->setCellValue('AH1',  $titulosColumnas[33])  //Titulo de las columnas
            ->setCellValue('AI1',  $titulosColumnas[34])
            ->setCellValue('AJ1',  $titulosColumnas[35])
            ->setCellValue('AK1',  $titulosColumnas[36])
            ->setCellValue('AL1',  $titulosColumnas[37])
            ->setCellValue('AM1',  $titulosColumnas[38])
            ->setCellValue('AN1',  $titulosColumnas[39])  //Titulo de las columnas;
            ->setCellValue('AO1',  $titulosColumnas[40])  //Titulo de las columnas;

            ->setCellValue('A2',  $restricciones[0])  //Titulo de las columnas
            ->setCellValue('B2',  $restricciones[1])  //Titulo de las columnas
            ->setCellValue('C2',  $restricciones[2])
            ->setCellValue('D2',  $restricciones[3])
            ->setCellValue('E2',  $restricciones[4])
            ->setCellValue('F2',  $restricciones[5])  //Titulo de las columnas
            ->setCellValue('G2',  $restricciones[6])
            ->setCellValue('H2',  $restricciones[7])
            ->setCellValue('I2',  $restricciones[8])
            ->setCellValue('J2',  $restricciones[9])  //Titulo de las columnas
            ->setCellValue('K2',  $restricciones[10])
            ->setCellValue('L2',  $restricciones[11])
            ->setCellValue('M2',  $restricciones[12])
            ->setCellValue('N2',  $restricciones[13])  //Titulo de las columnas
            ->setCellValue('O2',  $restricciones[14])
            ->setCellValue('P2',  $restricciones[15])
            ->setCellValue('Q2',  $restricciones[16])
            ->setCellValue('R2',  $restricciones[17])
            ->setCellValue('S2',  $restricciones[18])
            ->setCellValue('T2',  $restricciones[19])  //Titulo de las columnas
            ->setCellValue('U2',  $restricciones[20])
            ->setCellValue('V2',  $restricciones[21])
            ->setCellValue('W2',  $restricciones[22])
            ->setCellValue('X2',  $restricciones[23])  //Titulo de las columnas
            ->setCellValue('Y2',  $restricciones[24])
            ->setCellValue('Z2',  $restricciones[25])
            ->setCellValue('AA2',  $restricciones[26])
            ->setCellValue('AB2',  $restricciones[27])  //Titulo de las columnas
            ->setCellValue('AC2',  $restricciones[28])
            ->setCellValue('AD2',  $restricciones[29])
            ->setCellValue('AE2',  $restricciones[30])
            ->setCellValue('AF2',  $restricciones[31])
            ->setCellValue('AG2',  $restricciones[32])
            ->setCellValue('AH2',  $restricciones[33])  //Titulo de las columnas
            ->setCellValue('AI2',  $restricciones[34])
            ->setCellValue('AJ2',  $restricciones[35])
            ->setCellValue('AK2',  $restricciones[36])
            ->setCellValue('AL2',  $restricciones[37])
            ->setCellValue('AM2',  $restricciones[38])
            ->setCellValue('AN2',  $restricciones[39])  //Titulo de las columnas;
            ->setCellValue('AO2',  $restricciones[40])  //Titulo de las columnas;

            ->setCellValue('A3',  $tamano_formato[0])  //Titulo de las columnas
            ->setCellValue('B3',  $tamano_formato[1])  //Titulo de las columnas
            ->setCellValue('C3',  $tamano_formato[2])
            ->setCellValue('D3',  $tamano_formato[3])
            ->setCellValue('E3',  $tamano_formato[4])
            ->setCellValue('F3',  $tamano_formato[5])  //Titulo de las columnas
            ->setCellValue('G3',  $tamano_formato[6])
            ->setCellValue('H3',  $tamano_formato[7])
            ->setCellValue('I3',  $tamano_formato[8])
            ->setCellValue('J3',  $tamano_formato[9])  //Titulo de las columnas
            ->setCellValue('K3',  $tamano_formato[10])
            ->setCellValue('L3',  $tamano_formato[11])
            ->setCellValue('M3',  $tamano_formato[12])
            ->setCellValue('N3',  $tamano_formato[13])  //Titulo de las columnas
            ->setCellValue('O3',  $tamano_formato[14])
            ->setCellValue('P3',  $tamano_formato[15])
            ->setCellValue('Q3',  $tamano_formato[16])
            ->setCellValue('R3',  $tamano_formato[17])
            ->setCellValue('S3',  $tamano_formato[18])
            ->setCellValue('T3',  $tamano_formato[19])  //Titulo de las columnas
            ->setCellValue('U3',  $tamano_formato[20])
            ->setCellValue('V3',  $tamano_formato[21])
            ->setCellValue('W3',  $tamano_formato[22])
            ->setCellValue('X3',  $tamano_formato[23])  //Titulo de las columnas
            ->setCellValue('Y3',  $tamano_formato[24])
            ->setCellValue('Z3',  $tamano_formato[25])
            ->setCellValue('AA3',  $tamano_formato[26])
            ->setCellValue('AB3',  $tamano_formato[27])  //Titulo de las columnas
            ->setCellValue('AC3',  $tamano_formato[28])
            ->setCellValue('AD3',  $tamano_formato[29])
            ->setCellValue('AE3',  $tamano_formato[30])
            ->setCellValue('AF3',  $tamano_formato[31])
            ->setCellValue('AG3',  $tamano_formato[32])
            ->setCellValue('AH3',  $tamano_formato[33])  //Titulo de las columnas
            ->setCellValue('AI3',  $tamano_formato[34])
            ->setCellValue('AJ3',  $tamano_formato[35])
            ->setCellValue('AK3',  $tamano_formato[36])
            ->setCellValue('AL3',  $tamano_formato[37])
            ->setCellValue('AM3',  $tamano_formato[38])
            ->setCellValue('AN3',  $tamano_formato[39])
            ->setCellValue('AO3',  $tamano_formato[40])
            ;

        $i= 4;
        foreach ($table["tbody"] as $k => $tr) {
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$tr["sub_Diario"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$tr["nro_Comprobante"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$tr["fecha_Comprobante"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$tr["codigo_Moneda"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$tr["glosa_Principal"]);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$tr["tipo_Cambio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$tr["tipo_Conversion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$tr["flag_Conversion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,$tr["fecha_Tipo_Cambio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$i,$tr["cuenta_Contable"]);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$i,$tr["codigo_Anexo"]);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$i,$tr["codigo_Centro_Costo"]);
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$i,$tr["debe_Haber"]);
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$i,$tr["importe_Original"]);
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$i,$tr["importe_Dolares"]);
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i,$tr["importe_Soles"]);
            $objPHPExcel->getActiveSheet()->setCellValue('R'.$i,$tr["tipo_Documento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('S'.$i,$tr["nro_Documento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('T'.$i,$tr["fecha_Documento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('U'.$i,$tr["fecha_Vencimiento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('V'.$i,$tr["codigo_Area"]);
            $objPHPExcel->getActiveSheet()->setCellValue('W'.$i,$tr["glosa_Detalle"]);
            $objPHPExcel->getActiveSheet()->setCellValue('X'.$i,$tr["codigo_Anexo_Auxiliar"]);
            $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i,$tr["medio_Pago"]);
            $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i,$tr["tipo_Documento_Referencia"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i,$tr["numero_Documento_Referencia"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i,$tr["fecha_Documento_Referencia"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i,$tr["nro_Registradora"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AD'.$i,$tr["base_Imponible"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AE'.$i,$tr["igv_Documento"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AF'.$i,$tr["tipo_Referencia"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AG'.$i,$tr["numero_Serie"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AH'.$i,$tr["fecha_Operacion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AI'.$i,$tr["tipo_Tasa"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i,$tr["tasa_Detraccion_Percepcion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AK'.$i,$tr["importe_Base_Dolares"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AL'.$i,$tr["importe_Base_Soles"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AM'.$i,$tr["tipo_Cambio_F"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AN'.$i,$tr["importe_Igv_Fiscal"]);
            $i++;
        }

        $estiloTituloReporte = array(
            'font' => array(
                'name'      => 'Verdana',
                'bold'      => true,
                'italic'    => false,
                'strike'    => false,
                'size' =>16,
                'color'     => array(
                    'rgb' => 'FFFFFF'
                )
            ),
            'fill' => array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'argb' => 'FF220835')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $estiloTituloColumnas = array(
            'font' => array(
                'name'  => 'calibri',
                'bold'  => false,
                'size'  => 10,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'color'=>array('rgb'=>'FFC000')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN ,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
            ),
            'alignment' =>  array(
                'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap'      => true
            )
        );

        $estiloInformacion = new PHPExcel_Style();
        $estiloInformacion->applyFromArray( array(
            'font' => array(
                'name'  => 'calibri',
                'bold'  => false,
                'size'  => 10,
                'color' => array(
                    'rgb' => '000000'
                )
            ),

        ));

        $objPHPExcel->getActiveSheet()->getStyle('B1:AO1')->applyFromArray($estiloTituloColumnas);
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AN".($i-1));

        for($j=2;$j<=$i;$j++){
            $objPHPExcel->getActiveSheet()->getStyle('G'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle('O'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle('P'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle('Q'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle('L'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->getStyle('S'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(28);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(28);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(18);

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Depositos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        $local_titulo_parcial =($get_data['sec_caja_hermeticase_concar_boveda_local_id']=='_all_')?'Todos':$local_titulo[$get_data['sec_caja_hermeticase_concar_boveda_local_id']];
        $local_id_parcial =($get_data['sec_caja_hermeticase_concar_boveda_local_id']=='_all_')?'Todos':$locales[$get_data['sec_caja_hermeticase_concar_boveda_local_id']];

        $titulo_reporte_cajas = "REPORTE CONCAR BOVEDA".$local_titulo_parcial;
        $titulo_file_reporte_cajas = "Depositos_Caja_Concar_Boveda_Hermeticase_".$local_id_parcial."_".date("d-m-Y",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_inicio_concar"]))."_al_".date("d-m-Y",strtotime($get_data["input_text-sec_caja_hermeticase_concar_boveda_fecha_fin_concar"]))."_".date("Ymdhis");

        $local_titulo_parcial = "";
        $local_id_parcial = "";
        $local_id_name = "";

        if ($get_data['sec_caja_hermeticase_concar_boveda_local_id'] == '_all_'){
            $local_titulo_parcial = "Todos";
            $local_id_parcial = "Todos";
            $local_id_name = -1;
        }  
        else 
        {
            $local_titulo_parcial = $local_titulo[$get_data['sec_caja_hermeticase_concar_boveda_local_id']];
            $local_id_parcial = $locales[$get_data['sec_caja_hermeticase_concar_boveda_local_id']];
            $local_id_name = $get_data['sec_caja_hermeticase_concar_boveda_local_id'];
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_cajas.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $excel_path = '/var/www/html/export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
        //$excel_path = '../export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
        $excel_path_download = '/export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
        $url = $titulo_file_reporte_cajas.'.xls';
        $objWriter->save($excel_path);

        $insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
        $insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
        $mysqli->query($insert_cmd);
        $exported_id = $mysqli->insert_id;

        echo json_encode(array(
            "path" => $excel_path_download,
            "url" => $titulo_file_reporte_cajas.'.xls',
            "tipo" => "excel",
            "ext" => "xls",
            "size" => filesize($excel_path),
            "fecha_registro" => date("d-m-Y h:i:s"),
            "sql" => $insert_cmd
        ));
        
        $mysqli->query("INSERT INTO tbl_concar_hermeticase_boveda_historico (
            local_id,
            exported_id,
            usuario_id,
            cambio,
            correlativo,
            fecha_operacion,
            fecha_inicio,
            fecha_fin
        ) VALUES (
            '". $local_id_name ."',
            ".$exported_id.",
            ".$login["id"].",
            ".$get_data["sec_caja_hermeticase_concar_boveda_tipo_cambio"].",
            ".(($get_data["sec_caja_hermeticase_concar_boveda_correlativo_inicial"] != "") ? "'".$get_data["sec_caja_hermeticase_concar_boveda_correlativo_inicial"]."'" : "null").",

            '".date('Y-m-d H:i:s')."',
            '".$fecha_inicio."',
            '".$fecha_fin."'
        )
    ");
        exit;
    }
    else{
        print_r('No hay resultados para mostrar');
    }
}

function zerofill($valor, $longitud){
    $res = str_pad($valor, $longitud, '0', STR_PAD_LEFT);
    return $res;
}

function cortar_cadena($cadena, $longitud){
    if (strlen($cadena) > $longitud)
    {
        $devolver = mb_substr($cadena, 0, $longitud);
    }
    else{
        $devolver = $cadena;
    }
    return $devolver;
}

function format_row($row, $amount, $extra_data, $type): array
{
    $tr=array();
    $tr["sub_Diario"] = '2120';
    $tr["nro_Comprobante"]= $extra_data["nro_comprobante"]; // proviene de rtb.fecha_operacion, todo_ es del mismo periodo, de inicio a fin de mes
    $tr["fecha_Comprobante"] = $extra_data["fecha_fin_pretty"];
    $tr["codigo_Moneda"] = 'MN';
    $tr["glosa_Principal"]= $extra_data["nombre_glosa"];
    $tr["tipo_Cambio"]= $extra_data["tipo_cambio"];
    $tr["tipo_Conversion"]='V';
    $tr["flag_Conversion"]='S';
    $tr["fecha_Tipo_Cambio"]= $extra_data["fecha_fin_pretty"];
    //$tr["cuenta_Contable"]='!';
    //$tr["codigo_Anexo"] = '!';
    $tr["codigo_Centro_Costo"]='';
    //$tr["debe_Haber"]='!';
    //$tr["importe_Original"]= "!";
    //$tr["importe_Dolares"]= "!";
    //$tr["importe_Soles"]= "!";
    //$tr["tipo_Documento"]='!';
    //$tr["nro_Documento"]='!';
    //$tr["fecha_Documento"]='!';
    //$tr["fecha_Vencimiento"]= '!';
    //$tr["codigo_Area"]='!';
    $tr["glosa_Detalle"]=$extra_data["glosa_detalle"];
    //$tr["codigo_Anexo_Auxiliar"] = '!';
    //$tr["medio_Pago"]='!';
    //$tr["tipo_Documento_Referencia"]='!';
    //$tr["numero_Documento_Referencia"]='!';
    //$tr["fecha_Documento_Referencia"]= '!';
    $tr["nro_Registradora"]='';
    $tr["base_Imponible"]='';
    $tr["igv_Documento"]='';
    $tr["tipo_Referencia"]='';
    $tr["numero_Serie"]='';
    $tr["fecha_Operacion"]='';
    $tr["tipo_Tasa"]='';
    $tr["tasa_Detraccion_Percepcion"]='';
    $tr["importe_Base_Dolares"]='';
    $tr["importe_Base_Soles"]='';
    $tr["tipo_Cambio_F"]='';
    $tr["importe_Igv_Fiscal"]='';

    if ($type === "d"){
        $tr["cuenta_Contable"] = '10411024';
        $tr["codigo_Anexo"] = '10411024';
        $tr["debe_Haber"] = "D";
        $tr["importe_Original"] = (float) $row["importe"];
        $tr["importe_Dolares"] = (float) $row["importe"]/(float)$extra_data["tipo_cambio"];
        $tr["importe_Soles"] = (float) $row["importe"];
        $tr["tipo_Documento"] = 'EN';
        $tr["nro_Documento"] = $row["nro_operacion"];
        $tr["fecha_Documento"]= date("d/m/Y",strtotime($row["fecha_operacion"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
        $tr["fecha_Vencimiento"]= date("d/m/Y",strtotime($row["fecha_operacion"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
        $tr["codigo_Area"]='101'; // ES 101 HARCODED
        $tr["codigo_Anexo_Auxiliar"] = '';
        $tr["medio_Pago"]='001';
        $tr["tipo_Documento_Referencia"]='';
        $tr["numero_Documento_Referencia"]='';
        $tr["fecha_Documento_Referencia"]= '';
    } elseif ($type === "h"){
        //$tr["sub_Diario"] = $row["sd"];
        $tr["cuenta_Contable"] = '101121';
        $tr["codigo_Anexo"] = $row["cc_id"] . '-FONDO BOVEDA';
        $tr["debe_Haber"] = "H";
        $tr["importe_Original"] = number_format($amount, 2, ".", "");
        $tr["importe_Dolares"] = number_format($amount/(float)$extra_data["tipo_cambio"], 2, ".", "");
        $tr["importe_Soles"] = number_format($amount, 2, ".", "");
        $tr["tipo_Documento"] = 'PR';
        $nro_doc = $row["numero_documento"];
        if( substr($nro_doc ,0 , 3) == "PR ")
        {
            $nro_doc = substr($nro_doc , 3);
        }
        $tr["nro_Documento"] = $nro_doc; // FechaExcel
        $tr["fecha_Documento"] = date("d/m/Y",strtotime($row["fecha_documento"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
        $tr["fecha_Vencimiento"]= date("d/m/Y",strtotime($row["fecha_documento"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
        $tr["codigo_Area"]='343';
        $tr["codigo_Anexo_Auxiliar"] = 'A0003';
        $tr["medio_Pago"]='';
        $tr["tipo_Documento_Referencia"]='PR';
        $tr["numero_Documento_Referencia"]= $nro_doc; // FechaExcel desde los más antiguos
        $tr["fecha_Documento_Referencia"]= date("d/m/Y",strtotime($row["fecha_documento"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
    }

    return $tr;
}

?>
