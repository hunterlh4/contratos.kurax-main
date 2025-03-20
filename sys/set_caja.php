<?php
include("db_connect.php");
include("sys_login.php");
//include '/var/www/html/sys/curl_helper.php';
require_once '/var/www/html/env.php';
date_default_timezone_set("America/Lima");

$return = array();
if ($login) {
    if (isset($_POST["sec_caja_abrir_turno"])) {
        $save_data = $_POST["sec_caja_abrir_turno"];
        if ($login) {
            $save_data["turno_data"]["usuario_id"] = $login["id"];
            $save_data["turno_data"]["fecha_apertura"] = date("Y-m-d H:i:s");
            $save_data["turno_data"]["estado"] = 0;
            $save_data["turno_data"]["validar"] = 0;
            $save_data["turno_data"]["at_unique_id"] = md5($save_data["turno_data"]["local_caja_id"] . $save_data["turno_data"]["turno_id"] . $save_data["turno_data"]["fecha_operacion"] . $save_data["turno_data"]["fecha_apertura"]);

            $data_to_db = data_to_db($save_data["turno_data"]);

            $exists_local_id_command = "SELECT
									l.local_id
								FROM tbl_local_cajas l
								WHERE l.id = " . $data_to_db["local_caja_id"] . "
								AND l.estado = 1";
            $exists_local_id = $mysqli->query($exists_local_id_command)->fetch_assoc();
            $local_id = 0;
            if (!$exists_local_id) {
                $return["exists_local_id"] = $exists_local_id;
                echo json_encode($return, true);
                die;
            } else {
                $local_id = $exists_local_id['local_id'];
            }

            $exists_in_array_command = "SELECT
									c.nombre,
									c.config_data
								FROM tbl_configuracion c
								WHERE c.id = 3
								";
            $exists_exception = $mysqli->query($exists_in_array_command)->fetch_assoc();
            $exception_estado = false;
            if ($exists_exception) {
                if ($exists_exception['config_data'] != "") {
                    $locales_array = explode(',', $exists_exception['config_data']);
                    if (in_array(str_replace("'", "", $local_id), $locales_array)) {
                        //echo var_dump($locales_array);exit();
                        $exception_estado = true;
                    }
                }
            }

            if (!$exception_estado) {
                $exists_command = "SELECT
									c.id
								FROM tbl_caja c
								WHERE c.local_caja_id = " . $data_to_db["local_caja_id"] . "
								AND c.fecha_operacion >= '" . date('Y-m-d', strtotime("+1 day", strtotime(str_replace("'", "", $data_to_db["fecha_operacion"])))) . "'
								";
                $exists = $mysqli->query($exists_command)->fetch_assoc();

                if ($exists) {
                    $return["exists_fecha_superior"] = "exists_fecha_superior";
                    echo json_encode($return, true);
                    die;
                }

                $exists_command = "SELECT
									c.id
								FROM tbl_caja c
								WHERE c.local_caja_id = " . $data_to_db["local_caja_id"] . "
								AND c.turno_id = " . $data_to_db["turno_id"] . "
								AND c.fecha_operacion = " . $data_to_db["fecha_operacion"] . "
								";
                $exists = $mysqli->query($exists_command)->fetch_assoc();
                if ($exists) {
                    $return["exists"] = $exists;
                    echo json_encode($return, true);
                    die;
                }

                $turno_id = (int)(str_replace("'", "", $data_to_db["turno_id"])) - 1;
                if ($turno_id > 0) {
                    $exists_turno_anterior_command = "SELECT
										c.id
									FROM tbl_caja c
									WHERE c.local_caja_id = " . $data_to_db["local_caja_id"] . "
									AND c.turno_id = " . $turno_id . "
									AND c.fecha_operacion = " . $data_to_db["fecha_operacion"] . "
									";
                    $exists_turno_anterior = $mysqli->query($exists_turno_anterior_command)->fetch_assoc();
                    if (!$exists_turno_anterior) {
                        $return["exists_turno_anterior"] = "exists_turno_anterior";
                        echo json_encode($return, true);
                        die;
                    }
                } else {
                    $fecha_operacion = (string)$data_to_db["fecha_operacion"];
                    $date = DateTime::createFromFormat('Y-m-d', str_replace("'", "", $fecha_operacion))->format('d-m-Y');
                    $fecha_anterior = date('Y-m-d', strtotime($date . ' - 1 day'));

                    $exists_fecha_anterior_command = "SELECT
										c.id
									FROM tbl_caja c
									WHERE c.local_caja_id = " . $data_to_db["local_caja_id"] . "
									AND c.fecha_operacion = '" . $fecha_anterior . "'
									";
                    //echo $exists_fecha_anterior_command;exit();

                    $has_turnos_command = "SELECT
										c.id
									FROM tbl_caja c
									WHERE c.local_caja_id = " . $data_to_db["local_caja_id"];
                    $has_turnos = $mysqli->query($has_turnos_command)->num_rows;

                    $is_local_red_at_command = "SELECT
                                        l.id
                                    FROM tbl_locales l
                                    WHERE l.id = " . $local_id .
                        " AND l.red_id in (1,9,16)";
                    $is_local_red_at = $mysqli->query($is_local_red_at_command)->fetch_assoc();
                    $exists_fecha_anterior = $mysqli->query($exists_fecha_anterior_command)->fetch_assoc();
                    if ($is_local_red_at) {
                        if (!$exists_fecha_anterior && $has_turnos > 0 && $local_id != "481" && $local_id != "763" && $local_id != "1433"
                            && $local_id != "1497") {//local Activaciones , 1497 Plaza Vea Tacna
                            $return["exists_fecha_anterior"] = "exists_fecha_anterior";
                            echo json_encode($return, true);
                            die;
                        }
                    }
                }
            }


            $exists_command = "SELECT
									c.id
								FROM tbl_caja c
								WHERE c.local_caja_id = " . $data_to_db["local_caja_id"] . "
								AND c.turno_id = " . $data_to_db["turno_id"] . "
								AND c.fecha_operacion = " . $data_to_db["fecha_operacion"] . "
								";
            $exists = $mysqli->query($exists_command)->fetch_assoc();
            if ($exists) {
                $return["exists"] = $exists;
            } else {
                $open_command = "SELECT
									c.id
								FROM tbl_caja c
								WHERE c.local_caja_id = " . $data_to_db["local_caja_id"] . "
								AND c.estado = '0'
								";
                $open = $mysqli->query($open_command)->fetch_assoc();
                if ($open) {
                    $return["open"] = $open;
                } else {
                
                    // $mysqli->query("START TRANSACTION");
                    $command = "INSERT INTO tbl_caja";
                    $command .= "(";
                    $command .= implode(",", array_keys($data_to_db));
                    $command .= ")";
                    $command .= " VALUES ";
                    $command .= "(";
                    $command .= implode(",", $data_to_db);
                    $command .= ")";
                    $mysqli->query($command);
                    // echo $command; exit();
                    if ($mysqli->error) {
                        print_r($mysqli->error);
                        echo "\n";
                        echo $command;
                        exit();
                    }
                    // echo "asdadasdsa";
                    // exit();
                    // sleep(1);
                    $caja = $mysqli->query("SELECT id FROM tbl_caja WHERE at_unique_id = '" . $save_data["turno_data"]["at_unique_id"] . "'")->fetch_assoc();
                    // $caja=false;
                    if ($caja) {
                        // $caja_id = $mysqli->insert_id;
                        $caja_id = $caja["id"];
                        $return["caja_id"] = $caja_id;
                        if (array_key_exists("datos_fisicos", $save_data)) {
                            $datos_fisicos = array();
                            foreach ($save_data["datos_fisicos"] as $tipo_id => $df) {
                                $datos_fisicos[$tipo_id] = $df;
                            }
                            foreach ($datos_fisicos as $tipo_id => $dato) {
                                $dato["caja_id"] = $caja_id;
                                // $dato["at_unique_id"]=md5($caja_id.$tipo_id); //GENERA ERROR
                                $dato["at_unique_id"] = md5("df_caja_id_" . $caja_id . "_tipo_id_" . $tipo_id);
                                $dato["caja_unique_id"] = $save_data["turno_data"]["at_unique_id"];
                                // print_r($dato);
                                $dato_to_db = data_to_db($dato);
                                $dato_command = "INSERT INTO tbl_caja_datos_fisicos";
                                $dato_command .= "(";
                                $dato_command .= implode(",", array_keys($dato_to_db));
                                $dato_command .= ")";
                                $dato_command .= " VALUES ";
                                $dato_command .= "(";
                                $dato_command .= implode(",", $dato_to_db);
                                $dato_command .= ")";
                                // $dato_command.=" ON DUPLICATE KEY UPDATE ";
                                // $uqn=0;
                                // $only_update_array = array("valor");
                                // foreach ($dato_to_db as $iey => $value) {
                                // 	if(in_array($iey, $only_update_array)){
                                // 		if($uqn>0) { $dato_command.=","; }
                                // 		$dato_command.= $iey." = VALUES(".$iey.")";
                                // 		$uqn++;
                                // 	}
                                // }
                                $mysqli->query($dato_command);
                                if ($mysqli->error) {
                                    print_r($mysqli->error);
                                    echo "\n";
                                    echo $dato_command;
                                    exit();
                                }
                            }
                        } else {
                            echo "ERROR No datos fisicos enviados";
                        }
                        // $mysqli->query("COMMIT");

                        ///vinc dep libres
                        if (isset($save_data["depositos_libres"])) {
                            $depositos_libres_arr = $save_data["depositos_libres"];//[]
                            $depositos_libres = implode(',', $depositos_libres_arr);
                            $comando_vincular_depositos = "UPDATE tbl_caja_clientes_depositos
							 SET turno_id= " . $caja_id . " 
							 WHERE id in(" . $depositos_libres . ")";
                            $mysqli->query($comando_vincular_depositos);
                        }
                        /////

                    } else {
                        echo "ERROR Caja no creada correctamente.";
                    }
                }
            }


        } else {
            $return["no_login"] = true;
        }
    }
    if (isset($_POST["sec_caja_guardar"])) {
        include("/var/www/html/sys/set_caja_notificacion.php");

        $save_data = $_POST["sec_caja_guardar"];

        $caja = $mysqli->query("SELECT c.at_unique_id, 
                                        c.fecha_operacion,  
                                        c.fecha_apertura, 
                                        lc.nombre AS caja_nombre, 
                                        c.fecha_cierre, 
                                        c.turno_id, 
                                        c.estado,
                                        c.usuario_id,
                                        CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,'')) AS usuario_nombre, 
                                        z.id AS zona_id,
                                        l.nombre AS local_nombre
                                FROM tbl_caja c
                                    INNER JOIN tbl_local_cajas lc ON lc.id = c.local_caja_id
                                    INNER JOIN tbl_locales l ON l.id = lc.local_id
                                    INNER JOIN tbl_zonas z ON z.id = l.zona_id
                                    LEFT JOIN tbl_usuarios u ON (u.id = c.usuario_id)
                                    LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
                                WHERE c.id = '" . $save_data["item_id"] . "'")->fetch_assoc();

        // CONDICIÓN PARA NO VOLVER A CERRAR UNA CAJA CERRADA
        if($save_data["estado"] == 1 && $caja["estado"]==1) {
            exit();
        }

        if ($save_data["estado"] != 2 && isset($caja["at_unique_id"])) {
            if (array_key_exists("detalles", $save_data)) {
                foreach ($save_data["detalles"] as $tipo_id => $detalle) {
                    $detalle["caja_id"] = ($save_data["item_id"]);
                    // $detalle["at_unique_id"]=md5($save_data["item_id"].$tipo_id); //GENERA ERROR
                    $detalle["at_unique_id"] = md5("d_caja_id" . $save_data["item_id"] . "_tipo_id_" . $tipo_id);
                    $detalle["caja_unique_id"] = $caja["at_unique_id"];
                    $detalle_to_db = data_to_db($detalle);
                    // print_r($detalle);
                    $detalle_command = "INSERT INTO tbl_caja_detalle";
                    $detalle_command .= "(";
                    $detalle_command .= implode(",", array_keys($detalle_to_db));
                    $detalle_command .= ")";
                    $detalle_command .= " VALUES ";
                    $detalle_command .= "(";
                    $detalle_command .= implode(",", $detalle_to_db);
                    $detalle_command .= ")";
                    $detalle_command .= " ON DUPLICATE KEY UPDATE ";

                    $uqn = 0;
                    $only_update_array = array("ingreso", "salida", "caja_id");
                    foreach ($detalle_to_db as $iey => $value) {
                        if (in_array($iey, $only_update_array)) {
                            if ($uqn > 0) {
                                $detalle_command .= ",";
                            }
                            $detalle_command .= $iey . " = VALUES(" . $iey . ")";
                            $uqn++;
                        }
                    }
                    $mysqli->query($detalle_command);
                    if ($mysqli->error) {
                        print_r($mysqli->error);
                        echo "\n";
                        echo $detalle_command;
                        exit();
                    }
                    //echo $detalle_command; echo "\n";
                }
            }

            $id_caja_update = $save_data["item_id"];
            $fecha_operacion = $caja["fecha_operacion"];

            //TORITO
            $query_update_torito_turno = " 
                UPDATE tbl_torito_acceso a1
                JOIN (
                    SELECT
                        ta.id ta_id,
                        DATE(ta.created_at),
                        ta.idcashier user_id,
                        ta.idstore local_id,
                        ce.id turno_id_eliminado,
                        sqc.id turno_id_update 
                    FROM
                        tbl_torito_transaccion tt
                        JOIN tbl_torito_acceso ta ON ta.partnertoken = tt.partnertoken 
                        AND ta.idcashier = tt.user_id AND tt.cc_id=ta.idstore
                        JOIN tbl_caja_eliminados ce ON ce.id = ta.turno_id 
                        JOIN tbl_caja sqc ON sqc.fecha_operacion = tt.date AND sqc.turno_id=ce.turno_id
                        JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
                        JOIN tbl_locales ssql ON ssql.id = sqlc.local_id AND ssql.cc_id = ta.idstore
                    WHERE
                        tt.date = '" . $fecha_operacion . "'
                        AND sqc.id = '" . $id_caja_update . "'
                ) a2 ON a2.ta_id = a1.id
                SET a1.turno_id = a2.turno_id_update
                ";
            $mysqli->query($query_update_torito_turno);

            $query_update_torito = " 
                UPDATE tbl_torito_acceso a1
                JOIN (
                    SELECT
                        ta.id ta_id,
                        DATE(ta.created_at),
                        ta.idcashier user_id,
                        ta.idstore local_id,
                        ce.id turno_id_eliminado,
                        sqc.id turno_id_update 
                    FROM
                        tbl_torito_transaccion tt
                        JOIN tbl_torito_acceso ta ON ta.partnertoken = tt.partnertoken 
                        AND ta.idcashier = tt.user_id AND tt.cc_id=ta.idstore
                        JOIN tbl_caja_eliminados ce ON ce.id = ta.turno_id 
                        JOIN tbl_caja sqc ON sqc.fecha_operacion = tt.date -- AND sqc.turno_id=ce.turno_id
                        JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
                        JOIN tbl_locales ssql ON ssql.id = sqlc.local_id AND ssql.cc_id = ta.idstore
                    WHERE
                        tt.date = '" . $fecha_operacion . "'
                        AND sqc.id = '" . $id_caja_update . "'
                ) a2 ON a2.ta_id = a1.id
                SET a1.turno_id = a2.turno_id_update
                ";
            $mysqli->query($query_update_torito);

            //TELEVENTAS
            //Capturamos todas las transacciones con el turno eliminado que tengan la misma fecha_operacion, turno_id y local_id
            $query_update_televentas = " 
                UPDATE tbl_televentas_clientes_transaccion a1
                JOIN (
                    SELECT 
                        tct.id tct_id, 
                        DATE(tct.created_at) fecha_transaccion,
                        ce.id turno_id_eliminado, 
                        ce.turno_id turno_eliminado,
                        ce.fecha_operacion fecha_operacion_eliminado,
                        ce_ssql.nombre local_eliminado,
                        ce.usuario_id usuario_id_eliminado, 
                        sqc.id turno_id_update,
                        sqc.turno_id turno_update,
                        sqc.fecha_operacion fecha_operacion_update,
                        sqc_loc.nombre local_nuevo,
                        sqc.usuario_id usuario_id_update 
                    FROM tbl_televentas_clientes_transaccion tct 
                    JOIN tbl_caja_eliminados ce on ce.id = tct.turno_id 
                    JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
                    JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
                    JOIN tbl_caja sqc ON sqc.fecha_operacion = ce.fecha_operacion 
                        AND sqc.turno_id = ce.turno_id 
                    JOIN tbl_local_cajas sqc_caj ON sqc_caj.id = sqc.local_caja_id 
                    JOIN tbl_locales sqc_loc ON sqc_loc.id = sqc_caj.local_id 
                        AND sqc_loc.id = ce_ssql.id 
                    WHERE 
                        tct.estado = 1 
                        AND sqc.id = '" . $id_caja_update . "' 
                ) a2 ON a2.tct_id = a1.id 
                SET a1.turno_id = a2.turno_id_update 
                ";
            //            -- AND DATE(tct.created_at) = '" .$fecha_operacion. "' 
            //            -- AND tct.user_id = '" .$caja["usuario_id"]. "' 
            $mysqli->query($query_update_televentas);
            //echo $query_update_televentas;
            //die();

            //SALDO WEB
           // actualizar mediante turno_id y fecha de operacion
	   // se comenta porque la segunda query actualiza sin restricciones
          /*  $query_update_sw_turno = " 
                UPDATE tbl_saldo_web_transaccion a1
                JOIN (
                    SELECT
                        tt.id tt_id,
                        DATE(tt.created_at) registro,
                        tt.user_id,
                        tt.cc_id local_id,
                        ce.id turno_id_eliminado,
                        sqc.id turno_id_update 
                    FROM
                        tbl_saldo_web_transaccion tt
                        JOIN tbl_caja_eliminados ce ON ce.id = tt.turno_id 
                            AND ce.fecha_operacion = DATE(tt.created_at) 
                        JOIN tbl_caja sqc ON sqc.fecha_operacion = ce.fecha_operacion -- DATE(tt.created_at) 
                            -- AND sqc.usuario_id=ce.usuario_id 
                            AND sqc.turno_id=ce.turno_id
                            -- AND sqc.local_caja_id = ce.local_caja_id
                        JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
                        JOIN tbl_locales ssql ON ssql.id = sqlc.local_id AND ssql.cc_id = tt.cc_id
                    WHERE
                        DATE(tt.created_at) = '" . $fecha_operacion . "'
                        AND sqc.id = '" . $id_caja_update . "'
                ) a2 ON a2.tt_id = a1.id
                SET a1.turno_id = a2.turno_id_update
                ";
            $mysqli->query($query_update_sw_turno);*/

           // actualizar mediante local_caja_id y fecha de operacion
	   // no se valida turno_id ni local_caja_id
           $query_update_sw_turno = "
                UPDATE tbl_saldo_web_transaccion a1
                JOIN (
                    SELECT
                        tt.id tt_id,
                        DATE(tt.created_at) registro,
                        tt.user_id,
                        tt.cc_id local_id,
                        ce.id turno_id_eliminado,
                        sqc.id turno_id_update
                    FROM
                        tbl_saldo_web_transaccion tt
                        JOIN tbl_caja_eliminados ce ON ce.id = tt.turno_id
                            AND ce.fecha_operacion = DATE(tt.created_at)
                        JOIN tbl_caja sqc ON sqc.fecha_operacion = ce.fecha_operacion
                            -- AND sqc.usuario_id=ce.usuario_id
                            -- AND sqc.turno_id=ce.turno_id
                            -- AND sqc.local_caja_id = ce.local_caja_id
                        JOIN tbl_local_cajas lc ON lc.id = sqc.local_caja_id
                        JOIN tbl_locales l ON l.id = lc.local_id AND l.cc_id = tt.cc_id
                    WHERE
                        DATE(tt.created_at) = '" . $fecha_operacion . "'
                        AND sqc.id = '" . $id_caja_update . "'
                ) a2 ON a2.tt_id = a1.id
                SET a1.turno_id = a2.turno_id_update
                ";
            $mysqli->query($query_update_sw_turno);

            //SALDO TELESERVICIOS
            $query_update_saldo_teleservicios_turno = "
                UPDATE tbl_saldo_teleservicios_transaccion a1
                JOIN (
                    SELECT
                        tt.id tt_id,
                        DATE(tt.created_at) registro,
                        tt.user_id,
                        tt.cc_id local_id,
                        ce.id turno_id_eliminado,
                        sqc.id turno_id_update
                    FROM
                        tbl_saldo_teleservicios_transaccion tt
                        JOIN tbl_caja_eliminados ce ON ce.id = tt.turno_id AND ce.fecha_operacion = DATE(tt.created_at)
                        JOIN tbl_caja sqc ON sqc.fecha_operacion = ce.fecha_operacion
                        JOIN tbl_local_cajas lc ON lc.id = sqc.local_caja_id
                        JOIN tbl_locales l ON l.id = lc.local_id AND l.cc_id = tt.cc_id
                    WHERE
                        DATE(tt.created_at) = '" . $fecha_operacion . "'
                        AND sqc.id = '" . $id_caja_update . "'
                ) a2 ON a2.tt_id = a1.id
                SET a1.turno_id = a2.turno_id_update
                ";
            $mysqli->query($query_update_saldo_teleservicios_turno);
            
            //AUTOSERVICIOS
            /*$data_to_db = data_to_db($save_data["turno_data"]);
            $exists_local_id = $mysqli->query("SELECT l.local_id FROM tbl_local_cajas l WHERE l.id = " . $data_to_db["local_caja_id"] . " AND l.estado = 1")->fetch_assoc();
            if ($exists_local_id) {
                $local_id = $exists_local_id['local_id'];
                $exists_fecha_operacion = $mysqli->query("SELECT c.fecha_operacion FROM tbl_caja c WHERE c.id = '" . $save_data["item_id"] . "'")->fetch_assoc();
                if ($exists_fecha_operacion) {
                    $fecha_operacion = $exists_fecha_operacion['fecha_operacion'];
                    $exists_caja = $mysqli->query("SELECT * FROM  tbl_caja_eliminados AS ce LEFT JOIN tbl_local_cajas  AS lc ON  lc.id = ce.local_caja_id WHERE  ce.fecha_operacion = '" . $fecha_operacion . "' AND lc.local_id = '" . $local_id . "' ORDER BY ce.id DESC LIMIT 1;")->fetch_assoc();
                    if($exists_caja) {

                    }
                }
            }*/

            //INICIO AUTOSERVICIOS
            /*$existen_cajas_eliminadas = $mysqli->query("SELECT
                                                   ce.id                   caja_id_eliminado,
                                                   ce.turno_id             turno_eliminado,
                                                   ce.fecha_operacion      fecha_operacion_eliminado,
                                                   ce_ssql.nombre          local_eliminado,
                                                   ce.usuario_id           usuario_id_eliminado,
                                                   sqc.id                  caja_id_update,
                                                   sqc.turno_id            turno_update,
                                                   sqc.fecha_operacion     fecha_operacion_update,
                                                   sqc_loc.nombre          local_nuevo,
                                                   sqc.usuario_id          usuario_id_update
                                            FROM   tbl_caja_eliminados ce
                                                   JOIN tbl_local_cajas ce_sqlc
                                                        ON  ce_sqlc.id = ce.local_caja_id
                                                   JOIN tbl_locales ce_ssql
                                                        ON  ce_ssql.id = ce_sqlc.local_id
                                                   JOIN tbl_caja sqc
                                                        ON  sqc.fecha_operacion = ce.fecha_operacion
                                                        AND sqc.turno_id = ce.turno_id
                                                   JOIN tbl_local_cajas sqc_caj
                                                        ON  sqc_caj.id = sqc.local_caja_id
                                                   JOIN tbl_locales sqc_loc
                                                        ON  sqc_loc.id = sqc_caj.local_id
                                                        AND sqc_loc.id = ce_ssql.id
                                            WHERE  sqc.id = '" . $save_data["item_id"] . "'");
            if ($existen_cajas_eliminadas) {
                $data = [];
                while ($row = $existen_cajas_eliminadas->fetch_assoc()) {
                       $data[] = $row;
                }

                $url = env('TERMINALES_URL') . 'terminaltransaccion/settransacctionupdatecaja';
                $auth_token = env('TERMINALES_AUTH_TOKEN');
                $headers = [
                    'Content-type: application/json', 'token: ' . $auth_token
                ];
                $request = [
                    'cajas' => json_encode($data)
                ];

                $result = curl_helper($url, $request, $headers);
                if(!$result['error']) {
                    $query_update = "UPDATE tbl_terminal_transacciones a1
                    JOIN (
                            SELECT
                                ttt.id tct_id,
                                DATE(ttt.created_at) fecha_transaccion,
                                ce.id turno_id_eliminado,
                                ce.turno_id turno_eliminado,
                                ce.fecha_operacion fecha_operacion_eliminado,
                                ce_ssql.nombre local_eliminado,
                                ce.usuario_id usuario_id_eliminado,
                                sqc.id turno_id_update,
                                sqc.turno_id turno_update,
                                sqc.fecha_operacion fecha_operacion_update,
                                sqc_loc.nombre local_nuevo,
                                sqc.usuario_id usuario_id_update
                            FROM tbl_terminal_transacciones ttt
                                    JOIN tbl_caja_eliminados ce on ce.id = ttt.ext_caja_id
                                    JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id
                                    JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id
                                    JOIN tbl_caja sqc ON sqc.fecha_operacion = ce.fecha_operacion
                                AND sqc.turno_id = ce.turno_id
                                    JOIN tbl_local_cajas sqc_caj ON sqc_caj.id = sqc.local_caja_id
                                    JOIN tbl_locales sqc_loc ON sqc_loc.id = sqc_caj.local_id
                                AND sqc_loc.id = ce_ssql.id
                            WHERE
                                    ttt.ext_estado = 1
                            AND sqc.id = '{$save_data["item_id"]}'
                    ) a2 ON a2.tct_id = a1.id
                       SET a1.ext_caja_id = a2.turno_id_update;";
                    try {
                        $mysqli->query($query_update);
                    } catch (Exception $e) {

                    }
                }
            }*/
            //FIN AUTOSERVICIOS

            /*
            $query_update_sw = " 
                UPDATE tbl_saldo_web_transaccion a1
                JOIN (
                    SELECT
                        tt.id tt_id,
                        DATE(tt.created_at) registro,
                        tt.user_id,
                        tt.cc_id local_id,
                        ce.id turno_id_eliminado,
                        sqc.id turno_id_update 
                    FROM
                        tbl_saldo_web_transaccion tt
                        JOIN tbl_caja_eliminados ce ON ce.id = tt.turno_id AND ce.fecha_operacion = DATE(tt.created_at) 
                        JOIN tbl_caja sqc ON sqc.fecha_operacion = DATE(tt.created_at) AND sqc.usuario_id=ce.usuario_id -- AND sqc.turno_id=ce.turno_id
                        JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
                        JOIN tbl_locales ssql ON ssql.id = sqlc.local_id AND ssql.cc_id = tt.cc_id
                    WHERE
                        DATE(tt.created_at) = '" .$fecha_operacion. "'
                        AND sqc.id = '" .$id_caja_update. "'
                ) a2 ON a2.tt_id = a1.id
                SET a1.turno_id = a2.turno_id_update
                ";
            $mysqli->query($query_update_sw);
            */

            /*
            SE COMENTO ESTE UPDATE CON PREVIA COORDINACION CON TANIA Y BRENDA
            CAJERO REALIZAN TRANSACCIONES EN TIENDAS EQUIVOCADAS Y SE HIBAN AL CIERRE DE OTRO USUARIO DE LA MISMA TIENDA
            $query_update_sw = " 
                UPDATE tbl_saldo_web_transaccion a1
                JOIN (
                    SELECT
                        tt.id tt_id,
                        DATE(tt.created_at) registro,
                        tt.user_id,
                        tt.cc_id local_id,
                        ce.id turno_id_eliminado,
                        sqc.id turno_id_update 
                    FROM
                        tbl_saldo_web_transaccion tt
                        JOIN tbl_caja_eliminados ce ON ce.id = tt.turno_id 
                        JOIN tbl_caja sqc ON sqc.fecha_operacion = DATE(tt.created_at) -- AND sqc.usuario_id=ce.usuario_id -- AND sqc.turno_id=ce.turno_id
                        JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
                        JOIN tbl_locales ssql ON ssql.id = sqlc.local_id AND ssql.cc_id = tt.cc_id
                    WHERE
                        DATE(tt.created_at) = '" .$fecha_operacion. "'
                        AND sqc.id = '" .$id_caja_update. "'
                ) a2 ON a2.tt_id = a1.id
                SET a1.turno_id = a2.turno_id_update
                ";
            $mysqli->query($query_update_sw);
            */

            if (array_key_exists("datos_fisicos", $save_data)) {
                $datos_fisicos = $save_data["datos_fisicos"];
                foreach ($datos_fisicos as $tipo_id => $dato) {
                    $operation_day = date('w', strtotime($caja["fecha_operacion"]));
                    if ($dato["tipo_id"] === "9" && !in_array($operation_day, [3, 4])) {
                        continue;
                    }

                    if ($tipo_id == 20 && $save_data["estado"] == 1 && $caja["fecha_cierre"]==NULL && $caja["estado"]==0) { //kasnet
                        $local_id = "";
                        $result = $mysqli->query("
							SELECT
								l.id
							FROM tbl_locales l
							INNER JOIN tbl_local_cajas lc ON lc.local_id = l.id
							INNER JOIN tbl_caja c ON c.local_caja_id = lc.id
							WHERE c.id = " . $save_data["item_id"]
                        );
                        while ($r = $result->fetch_assoc()) $local_id = $r["id"];

                        $caja_final = [];
                        $result = $mysqli->query("
							SELECT turno_id, fecha_operacion, local_caja_id
							FROM tbl_caja
							WHERE id = " . $save_data["item_id"]);
                        while ($r = $result->fetch_assoc()) $caja_final = $r;

                        $fecha_cierre = date('Y-m-d H:i:s');
                        if (isset($caja_final['turno_id'], $caja_final['fecha_operacion'], $caja_final['local_caja_id'])) {
                            $result = $mysqli->query("
								SELECT fecha_cierre FROM tbl_caja_eliminados
								WHERE
									turno_id = '" . $caja_final['turno_id'] . "'
									AND fecha_operacion = '" . $caja_final['fecha_operacion'] . "'
									AND local_caja_id = '" . $caja_final['local_caja_id'] . "'
									AND estado = 1
								ORDER BY fecha_apertura ASC
								LIMIT 1
							");
                            while ($r = $result->fetch_assoc()) {
                                if ($r["fecha_cierre"])
                                    $fecha_cierre = $r["fecha_cierre"];
                            }
                        }

                        $valid_local = false;
                        if ($local_id) {
                            $result = $mysqli->query("
								SELECT l.id FROM tbl_locales l
								INNER JOIN tbl_local_caja_detalle_tipos lt ON lt.local_id = l.id
								WHERE operativo = 1 AND lt.detalle_tipos_id = 13 AND l.id=" . $local_id);
                            while ($r = $result->fetch_assoc()) $valid_local = $r["id"];
                        }

                        $saldo_kasnet = [];
                        $result = $mysqli->query("
							SELECT
								lc.local_id,
								IFNULL(k.saldo_final, 0) AS saldo_anterior
							FROM tbl_caja c
							INNER JOIN tbl_local_cajas lc ON lc.id = c.local_caja_id
							LEFT JOIN tbl_saldo_kasnet k ON (k.local_id = lc.local_id AND k.estado = 1 AND k.created_at <= '$fecha_cierre')
							WHERE
								c.id = " . $save_data["item_id"] . "
							ORDER BY k.created_at DESC, k.id DESC
							LIMIT 1
						");
                        while ($r = $result->fetch_assoc()) $saldo_kasnet = $r;

                        if ($valid_local) {
                            $saldo_incremento = $dato["valor"];
                            $dato["valor"] += $saldo_kasnet["saldo_anterior"];
                            $nuevo_saldo = $saldo_incremento + $saldo_kasnet["saldo_anterior"];
                            $mysqli->query("
								UPDATE
									tbl_saldo_kasnet
								SET
									saldo_anterior = {$dato['valor']},
									saldo_final = saldo_incremento+{$nuevo_saldo},
									updated_at = '{$date}'
								WHERE
									local_id = {$local_id}
									AND tipo_id = 2
									AND created_at >= '{$fecha_cierre}'
							");

                            // Comprobamos que no se repita caja_id
                            $query_caja_id = "SELECT caja_id FROM tbl_saldo_kasnet WHERE caja_id = '" . $save_data["item_id"] . "'";
                            $num_caja_id = $mysqli->query($query_caja_id)->num_rows;

                            if ($num_caja_id > 0) {
                                $mysqli->query("
                                    UPDATE tbl_saldo_kasnet
                                    SET saldo_incremento=" . $saldo_incremento . ",
                                        saldo_final=" . $nuevo_saldo . ",
                                        session_cookie='" . $login["sesion_cookie"] . "',
                                        updated_at='" . $fecha_cierre . "'
                                    WHERE caja_id = '" . $save_data["item_id"] . "'
                                    "
                                );
                            } else if ($num_caja_id == 0) {
                                $mysqli->query("
                                INSERT INTO tbl_saldo_kasnet(
                                        caja_id,
                                        local_id,
                                        saldo_anterior,
                                        saldo_incremento,
                                        saldo_final,
                                        session_cookie,
                                        estado,
                                        created_at,
                                        updated_at
                                    )
                                    VALUES(
                                        " . $save_data["item_id"] . ",
                                        " . $local_id . ",
                                        " . $saldo_kasnet["saldo_anterior"] . ",
                                        " . $saldo_incremento . ",
                                        " . $nuevo_saldo . ",
                                        '" . $login["sesion_cookie"] . "',
                                        '1',
                                        '" . $fecha_cierre . "',
                                        '" . $fecha_cierre . "'
                                    )
                                    ON DUPLICATE KEY UPDATE
                                        saldo_incremento=" . $saldo_incremento . ",
                                        saldo_final=" . $nuevo_saldo . ",
                                        session_cookie='" . $login["sesion_cookie"] . "',
                                        updated_at='" . $fecha_cierre . "'
                                ");
                            }

                            $query = "
								SELECT 
									id,
									local_id,
									saldo_incremento AS incremento,
									created_at AS created
								FROM tbl_saldo_kasnet 
								WHERE estado = 1
								AND caja_id = " . $save_data["item_id"] . "
								ORDER BY id DESC
								LIMIT 1
							";
                            $rows = false;
                            $query_result = $mysqli->query($query);
                            if ($mysqli->error) {
                                echo $mysqli->error;
                                die;
                            }
                            if ($r = $query_result->fetch_assoc()) {
                                $ultimo = $r;

                                //consulta si hay resgistros posteriores a la fecha
                                $registros_posteriores = "
									SELECT
										id,
										saldo_incremento,
										tipo_id,
										caja_id,
										created_at
									FROM tbl_saldo_kasnet
									WHERE
										local_id =" . $ultimo["local_id"] . "
										AND created_at > '" . $ultimo["created"] . "'
									ORDER BY created_at ASC
								";
                                $result_post = $mysqli->query($registros_posteriores);


                                while ($r = $result_post->fetch_assoc()) {
                                    $post = $r;

                                    //consulto el registro anterior a la fecha de este registro
                                    $registro_anterior = "
										SELECT
											saldo_final
										FROM tbl_saldo_kasnet
										WHERE
											local_id=" . $ultimo["local_id"] . "
											AND  estado = 1
											AND created_at < '" . $post["created_at"] . "'
										ORDER BY created_at DESC
										LIMIT 1
									";


                                    $resul = $mysqli->query($registro_anterior);
                                    while ($saldo_antPost = $resul->fetch_assoc()) {
                                        $new_saldo_fin = (double)($saldo_antPost['saldo_final'] + $post["saldo_incremento"]);

                                        $update_kasnet = "
											UPDATE tbl_saldo_kasnet
											SET
												saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
												saldo_final=" . $new_saldo_fin . "
											WHERE id=" . $post["id"] . "
										";


                                        $fin = $mysqli->query($update_kasnet);

                                        if ($post["tipo_id"] == 1) {
                                            $update_datos_fisicos = "
												UPDATE tbl_caja_datos_fisicos
												SET
													valor=" . $new_saldo_fin . "
												WHERE
													caja_id=" . $post["caja_id"] . "
													AND tipo_id=20
												";

                                            $fin2 = $mysqli->query($update_datos_fisicos);
                                        }

                                        if ($fin) {
                                            $saldo_antPost['saldo_final'] = "";
                                        }
                                        //var_dump($fin);
                                    }
                                }
                            }
                        }
                    }

                    /*DISASHOP*/
                    if ($tipo_id == 25 && $save_data["estado"] == 1 && $caja["fecha_cierre"]==NULL && $caja["estado"]==0) { //disashop
                        $local_id = "";
                        $result = $mysqli->query("
                            SELECT
                                l.id
                            FROM tbl_locales l
                            INNER JOIN tbl_local_cajas lc ON lc.local_id = l.id
                            INNER JOIN tbl_caja c ON c.local_caja_id = lc.id
                            WHERE c.id = " . $save_data["item_id"]
                        );
                        while ($r = $result->fetch_assoc()) $local_id = $r["id"];

                        $caja_final = [];
                        $result = $mysqli->query("
                            SELECT turno_id, fecha_operacion, local_caja_id
                            FROM tbl_caja
                            WHERE id = " . $save_data["item_id"]);
                        while ($r = $result->fetch_assoc()) $caja_final = $r;

                        $fecha_cierre = date('Y-m-d H:i:s');
                        if (isset($caja_final['turno_id'], $caja_final['fecha_operacion'], $caja_final['local_caja_id'])) {
                            $result = $mysqli->query("
                                SELECT fecha_cierre FROM tbl_caja_eliminados
                                WHERE
                                    turno_id = '" . $caja_final['turno_id'] . "'
                                    AND fecha_operacion = '" . $caja_final['fecha_operacion'] . "'
                                    AND local_caja_id = '" . $caja_final['local_caja_id'] . "'
                                    AND estado = 1
                                ORDER BY fecha_apertura ASC
                                LIMIT 1
                            ");
                            while ($r = $result->fetch_assoc()) {
                                if ($r["fecha_cierre"])
                                    $fecha_cierre = $r["fecha_cierre"];
                            }
                        }

                        $valid_local = false;
                        if ($local_id) {
                            /*detalle_tipos_id =  21 disashop*/
                            $result = $mysqli->query("
                                SELECT l.id FROM tbl_locales l
                                INNER JOIN tbl_local_caja_detalle_tipos lt ON lt.local_id = l.id
                                WHERE operativo = 1 AND lt.detalle_tipos_id = 21 AND l.id=" . $local_id);
                            while ($r = $result->fetch_assoc()) $valid_local = $r["id"];
                        }

                        $saldo_kasnet = [];
                        $result = $mysqli->query("
                            SELECT
                                lc.local_id,
                                IFNULL(k.saldo_final, 0) AS saldo_anterior
                            FROM tbl_caja c
                            INNER JOIN tbl_local_cajas lc ON lc.id = c.local_caja_id
                            LEFT JOIN tbl_saldo_disashop k ON (k.local_id = lc.local_id AND k.estado = 1 AND k.created_at <= '$fecha_cierre')
                            WHERE
                                c.id = " . $save_data["item_id"] . "
                            ORDER BY k.created_at DESC
                            LIMIT 1
                        ");
                        while ($r = $result->fetch_assoc()) $saldo_kasnet = $r;
                        if ($valid_local) {
                            $saldo_incremento = $dato["valor"];
                            $dato["valor"] += $saldo_kasnet["saldo_anterior"];
                            $mysqli->query("
                                UPDATE
                                    tbl_saldo_disashop
                                SET
                                    saldo_anterior = {$dato['valor']},
                                    saldo_final = saldo_incremento+{$dato['valor']},
                                    updated_at = '{$date}'
                                WHERE
                                    local_id = {$local_id}
                                    AND tipo_id = 2
                                    AND created_at >= '{$fecha_cierre}'
                            ");

                            // Comprobamos que no se repita caja_id
                            $query_disashop_caja_id = "SELECT caja_id FROM tbl_saldo_disashop WHERE caja_id = '" . $save_data["item_id"] . "'";
                            $num_disashop_caja_id = $mysqli->query($query_disashop_caja_id)->num_rows;

                            if ($num_disashop_caja_id > 0) {
                                $mysqli->query("
                                    UPDATE tbl_saldo_disashop
                                    SET saldo_incremento=" . $saldo_incremento . ",
                                        saldo_final=" . $dato["valor"] . ",
                                        session_cookie='" . $login["sesion_cookie"] . "',
                                        updated_at='" . $fecha_cierre . "'
                                    WHERE caja_id = '" . $save_data["item_id"] . "'
                                    "
                                );
                            } else if ($num_disashop_caja_id == 0) {
                                $mysqli->query("
                                    INSERT INTO tbl_saldo_disashop(
                                        caja_id,
                                        local_id,
                                        saldo_anterior,
                                        saldo_incremento,
                                        saldo_final,
                                        session_cookie,
                                        estado,
                                        created_at,
                                        updated_at
                                    )
                                    VALUES(
                                        " . $save_data["item_id"] . ",
                                        " . $local_id . ",
                                        " . $saldo_kasnet["saldo_anterior"] . ",
                                        " . $saldo_incremento . ",
                                        " . $dato["valor"] . ",
                                        '" . $login["sesion_cookie"] . "',
                                        '1',
                                        '" . $fecha_cierre . "',
                                        '" . $fecha_cierre . "'
                                    )
                                    ON DUPLICATE KEY UPDATE
                                        saldo_incremento=" . $saldo_incremento . ",
                                        saldo_final=" . $dato["valor"] . ",
                                        session_cookie='" . $login["sesion_cookie"] . "',
                                        updated_at='" . $fecha_cierre . "'"
                                );
                            }

                            $query = "
                                SELECT 
                                    id,
                                    local_id,
                                    saldo_incremento AS incremento,
                                    created_at AS created
                                FROM tbl_saldo_disashop 
                                WHERE estado = 1
                                AND caja_id = " . $save_data["item_id"] . "
                                ORDER BY id DESC
                                LIMIT 1
                            ";
                            $rows = false;
                            $query_result = $mysqli->query($query);
                            if ($mysqli->error) {
                                echo $mysqli->error;
                                die;
                            }
                            if ($r = $query_result->fetch_assoc()) {
                                $ultimo = $r;
                                //consulta si hay resgistros posteriores a la fecha
                                $registros_posteriores = "
                                    SELECT
                                        id,
                                        saldo_incremento,
                                        tipo_id,
                                        caja_id,
                                        created_at
                                    FROM tbl_saldo_disashop
                                    WHERE
                                        local_id =" . $ultimo["local_id"] . "
                                        AND created_at > '" . $ultimo["created"] . "'
                                    ORDER BY created_at ASC
                                ";
                                $result_post = $mysqli->query($registros_posteriores);


                                while ($r = $result_post->fetch_assoc()) {
                                    $post = $r;

                                    //consulto el registro anterior a la fecha de este registro
                                    $registro_anterior = "
                                        SELECT
                                            saldo_final
                                        FROM tbl_saldo_disashop
                                        WHERE
                                            local_id=" . $ultimo["local_id"] . "
                                            AND  estado = 1
                                            AND created_at < '" . $post["created_at"] . "'
                                        ORDER BY created_at DESC
                                        LIMIT 1
                                    ";

                                    $resul = $mysqli->query($registro_anterior);
                                    while ($saldo_antPost = $resul->fetch_assoc()) {
                                        $new_saldo_fin = (double)($saldo_antPost['saldo_final'] + $post["saldo_incremento"]);

                                        $update_kasnet = "
                                            UPDATE tbl_saldo_disashop
                                            SET
                                                saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
                                                saldo_final=" . $new_saldo_fin . "
                                            WHERE id=" . $post["id"] . "
                                        ";


                                        $fin = $mysqli->query($update_kasnet);

                                        if ($post["tipo_id"] == 1) {
                                            /*tipo_id = 25  disashop*/
                                            $update_datos_fisicos = "
                                                UPDATE tbl_caja_datos_fisicos
                                                SET
                                                    valor=" . $new_saldo_fin . "
                                                WHERE
                                                    caja_id=" . $post["caja_id"] . "
                                                    AND tipo_id=25
                                                ";

                                            $fin2 = $mysqli->query($update_datos_fisicos);
                                        }

                                        if ($fin) {
                                            $saldo_antPost['saldo_final'] = "";
                                        }
                                        //var_dump($fin);
                                    }
                                }
                            }
                        }
                    }
                    /*FIn DISASHOP*/

                    $dato["caja_id"] = ($save_data["item_id"]);
                    // $dato["at_unique_id"]=md5($save_data["item_id"].$tipo_id); //GENERA ERROR
                    $dato["at_unique_id"] = md5("df_caja_id_" . $save_data["item_id"] . "_tipo_id_" . $tipo_id);
                    $dato["caja_unique_id"] = $caja["at_unique_id"];
                    // print_r($dato);
                    $dato_to_db = data_to_db($dato);
                    $dato_command = "INSERT INTO tbl_caja_datos_fisicos";
                    $dato_command .= "(";
                    $dato_command .= implode(",", array_keys($dato_to_db));
                    $dato_command .= ")";
                    $dato_command .= " VALUES ";
                    $dato_command .= "(";
                    $dato_command .= implode(",", $dato_to_db);
                    $dato_command .= ")";
                    $dato_command .= " ON DUPLICATE KEY UPDATE ";
                    $uqn = 0;
                    $only_update_array = array("valor");
                    foreach ($dato_to_db as $iey => $value) {
                        if (in_array($iey, $only_update_array)) {
                            if ($uqn > 0) {
                                $dato_command .= ",";
                            }
                            $dato_command .= $iey . " = VALUES(" . $iey . ")";
                            $uqn++;
                        }
                    }
                    $mysqli->query($dato_command);
                    if ($mysqli->error) {
                        print_r($mysqli->error);
                        echo "\n";
                        echo $dato_command;
                        exit();
                    }
                }
            }
        }
        $fecha_cierre=date("Y-m-d H:i:s");

        $update_caja_command = "UPDATE tbl_caja SET observaciones = '" . trim($mysqli->real_escape_string($save_data["observaciones"])) . "', estado = '" . $save_data["estado"] . "'";
        if ($save_data["estado"] == 1) {
            $update_caja_command .= " ,fecha_cierre = '" . $fecha_cierre . "'";
        }

        $update_caja_command .= " WHERE id = '" . $save_data["item_id"] . "'";
        $mysqli->query($update_caja_command);

        if ($mysqli->error) {
            print_r($mysqli->error);
            echo "\n";
            echo $update_caja_command;
            exit();
        }else{
            //  NOTIFICACION DE NO VENTA GOLDEN RACE Y BINGO
            if ($save_data["estado"] == 1) { 
                $permisos_query = $mysqli->query("SELECT p.correo
                                                        FROM tbl_personal_apt AS p
                                                        INNER JOIN tbl_usuarios AS u ON p.id = u.personal_id 
                                                        INNER JOIN tbl_permisos AS pm ON pm.usuario_id = u.id 
                                                        INNER JOIN tbl_botones AS b ON b.id = pm.boton_id 
                                                        WHERE u.estado = 1 AND p.estado = 1 AND p.zona_id='".$caja["zona_id"]."' AND p.correo IS NOT NULL AND b.boton='notificacion_no_venta';");

                $row_count_permisos= $permisos_query->num_rows;
        
                //  VERIFICACIÓN DE USUARIOS CON PERMISOS
                if($row_count_permisos > 0){
                    $gr_ingreso_query = $mysqli->query("SELECT cd.ingreso
                                        FROM tbl_caja_detalle cd
                                        INNER JOIN tbl_caja c ON cd.caja_id=c.id   
                                        INNER JOIN tbl_local_caja_detalle_tipos lcdt ON lcdt.id = cd.tipo_id                 
                                        WHERE c.id = '" . $save_data["item_id"] . "' AND detalle_tipos_id=3 LIMIT 1;");
                    $bingo_ingreso_query = $mysqli->query("SELECT cd.ingreso
                                        FROM tbl_caja_detalle cd
                                        INNER JOIN tbl_caja c ON cd.caja_id=c.id   
                                        INNER JOIN tbl_local_caja_detalle_tipos lcdt ON lcdt.id = cd.tipo_id                 
                                        WHERE c.id = '" . $save_data["item_id"] . "' AND detalle_tipos_id=15 LIMIT 1;");

                    $row_count_gr_ingreso = $gr_ingreso_query->num_rows;
                    $row_count_bingo_ingreso = $bingo_ingreso_query->num_rows;

                    //  CONDICIÓN DE ALERTA
                    if($row_count_gr_ingreso > 0 || $row_count_bingo_ingreso > 0){
                        $gr_ingreso = $gr_ingreso_query->fetch_assoc();
                        $bingo_ingreso = $bingo_ingreso_query->fetch_assoc();

                        $local_nombre=$caja["local_nombre"];
                        $usuario=$caja["usuario_nombre"];

                            $body = "<div class='container'>";                    
                            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family: arial" width="500px">';
                            $body .= '<thead>';
                            $body .= '<tr style="font-size: 16px; background-color:#395168; color: #fff">';
                            $body .= '<tr>';
                            $body .= '<th  colspan="2"  style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
                            $body .= $local_nombre;
                            $body .= '</th>';
                            $body .= '</tr>';
                            $body .= '<tr>';
                            $body .= '<td style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
                            $body .= 'ID de caja';
                            $body .= '</td>';
                            $body .= '<td style="vertical-align: middle; font-size: 16px">';
                            $body .= $save_data["item_id"];
                            $body .= '</td>';
                            $body .= '</tr>';
                            $body .= '<tr>';
                            $body .= '<td style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
                            $body .= 'Caja';
                            $body .= '</td>';
                            $body .= '<td style="vertical-align: middle; font-size: 16px">';
                            $body .= $caja["caja_nombre"];
                            $body .= '</td>';
                            $body .= '</tr>';
                            $body .= '<tr>';
                            $body .= '<td style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
                            $body .= 'Turno';
                            $body .= '</td>';
                            $body .= '<td style="vertical-align: middle; font-size: 16px">';
                            $body .= $caja["turno_id"];
                            $body .= '</td>';
                            $body .= '</tr>';
                            $body .= '<tr>';
                            $body .= '<td style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
                            $body .= 'Fecha operación';
                            $body .= '</td>';
                            $body .= '<td style="vertical-align: middle; font-size: 16px">';
                            $body .= $caja["fecha_operacion"];
                            $body .= '</td>';
                            $body .= '</tr>';
                            $body .= '<tr>';
                            $body .= '<td style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
                            $body .= 'Apertura';
                            $body .= '</td>';
                            $body .= '<td style="vertical-align: middle; font-size: 16px">';
                            $body .= $caja["fecha_apertura"];
                            $body .= '</td>';
                            $body .= '</tr>';
                            $body .= '<tr>';
                            $body .= '<td style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
                            $body .= 'Cierre';
                            $body .= '</td>';
                            $body .= '<td style="vertical-align: middle; font-size: 16px">';
                            $body .= $fecha_cierre;
                            $body .= '</td>';
                            $body .= '</tr>';
                            $body .= '</tbody>';          
                            $body .= '</table>';
                            $body .= '</div>';
                        //  GOLDER RACE
                        if($gr_ingreso["ingreso"]==NULL || $gr_ingreso["ingreso"]==0){
                        sendEmail_notificacionNoVenta($body, $local_nombre,$usuario,'Golden Race',env('SEND_EMAIL'),$caja["zona_id"]);
                        }
                        //  BINGO
                        if($bingo_ingreso["ingreso"]==NULL || $bingo_ingreso["ingreso"]==0){
                        sendEmail_notificacionNoVenta($body, $local_nombre,$usuario,'Bingo',env('SEND_EMAIL'),$caja["zona_id"]);

                        }
                    }
                }
            }

        }
        
        // print_r($save_data);


    }

    if (isset($_POST["sec_caja_eliminar"])) {
        $data = $_POST["sec_caja_eliminar"];
        $caja_command = "DELETE FROM tbl_caja WHERE id = '" . $data["item_id"] . "'";
        $caja_detalle_command = "DELETE FROM tbl_caja_detalle WHERE caja_id = '" . $data["item_id"] . "'";
        $caja_datos_fisicos_command = "DELETE FROM tbl_caja_datos_fisicos WHERE caja_id = '" . $data["item_id"] . "'";
        $solicitud_estado_command = "UPDATE tbl_solicitud_prestamo SET estado=6,caja_id = NULL WHERE caja_id = '" . $data["item_id"] . "'";


        $return["caja"] = $mysqli->query("SELECT
				c.id
				,c.local_caja_id
				,l.id AS local_id
				,CONCAT('[',l.id,']',' ',l.nombre) AS local_nombre
				,IF(u.personal_id,CONCAT('[',u.usuario,']',' ',IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,'')),u.usuario) AS usuario_nombre
				,lc.nombre AS caja_nombre
				,ct.nombre AS caja_tipo
				,IFNULL(c.turno_id,'-') AS turno
				,c.fecha_operacion
				,c.fecha_apertura
				,c.fecha_cierre
				,c.observaciones
				,c.estado
				,IF(c.estado=1,'Cerrado',IF(c.estado=2,'Re-Abierto','Abierto')) as estado_nombre
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_caja_tipos ct ON (ct.id = lc.caja_tipo_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			LEFT JOIN tbl_usuarios u ON (u.id = c.usuario_id)
			LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
			WHERE c.id = '" . $data["item_id"] . "'")->fetch_assoc();


        $checkCaja = isset($return["caja"]) ? $return["caja"] : "";
        $caja_local_id = "";
        if ($checkCaja != "") {
            $caja_local_id = isset($return["caja"]["local_id"]) ? $return["caja"]["local_id"] : "";
        }

        $local_caja_detalle_tipos_command = "SELECT
			lcdt.id
			,lcdt.nombre
			,lcdt.descripcion
			,cdt.nombre AS tipo
			,cdt.in
			,cdt.out
			,lcdt.estado
			,cd.ingreso AS ingreso
			,cd.salida AS salida
			FROM tbl_local_caja_detalle_tipos lcdt
			LEFT JOIN tbl_caja_detalle_tipos cdt ON (cdt.id = lcdt.detalle_tipos_id)
			LEFT JOIN tbl_caja_detalle cd ON (cd.tipo_id = lcdt.id AND cd.caja_id = '" . $data["item_id"] . "')
			WHERE lcdt.local_id = '" . $caja_local_id . "'
			ORDER BY lcdt.orden ASC";


        $local_caja_detalle_tipos_query = $mysqli->query($local_caja_detalle_tipos_command);
        while ($lcdt = $local_caja_detalle_tipos_query->fetch_assoc()) {
            $return["caja_detalles"][] = $lcdt;
        }

        $datos_fisicos_command = "SELECT
									dft.id
									,dft.nombre
									,dft.columna
									,dft.operador
									,dft.mostrar
									,df.valor AS valor
								FROM tbl_caja_datos_fisicos_tipos dft
								LEFT JOIN tbl_caja_datos_fisicos df ON (df.caja_id = '" . $data["item_id"] . "' AND df.tipo_id = dft.id)
								ORDER BY dft.ord ASC";
        $datos_fisicos_query = $mysqli->query($datos_fisicos_command);
        while ($df = $datos_fisicos_query->fetch_assoc()) {
            $return["caja_datos_fisicos"][] = $df;
        }

        $query = "
			SELECT 
				id,
				local_id,
				saldo_incremento AS incremento,
				created_at AS created
			FROM tbl_saldo_kasnet 
			WHERE estado = 1
			AND caja_id = " . $data["item_id"] . "
			ORDER BY id DESC
			LIMIT 1
		";
        $rows = false;
        $query_result = $mysqli->query($query);
        if ($mysqli->error) {
            echo $mysqli->error;
            die;
        }
        if ($r = $query_result->fetch_assoc()) {
            $ultimo = $r;

            $mysqli->query("UPDATE tbl_saldo_kasnet SET estado = 0 WHERE id = " . $r["id"]);

            //consulta si hay resgistros posteriores a la fecha
            $registros_posteriores = "
				SELECT
					id,
					saldo_incremento,
					tipo_id,
					caja_id,
					created_at
				FROM tbl_saldo_kasnet
				WHERE
					local_id =" . $ultimo["local_id"] . "
					AND created_at > '" . $ultimo["created"] . "'
				ORDER BY created_at ASC
			";
            $result_post = $mysqli->query($registros_posteriores);


            while ($r = $result_post->fetch_assoc()) {
                $post = $r;

                //consulto el registro anterior a la fecha de este registro
                $registro_anterior = "
					SELECT
						saldo_final
					FROM tbl_saldo_kasnet
					WHERE
						local_id=" . $ultimo["local_id"] . "
						AND  estado = 1
						AND created_at < '" . $post["created_at"] . "'
					ORDER BY created_at DESC
					LIMIT 1
				";


                $resul = $mysqli->query($registro_anterior);
                while ($saldo_antPost = $resul->fetch_assoc()) {
                    $new_saldo_fin = (double)($saldo_antPost['saldo_final'] + $post["saldo_incremento"]);

                    $update_kasnet = "
						UPDATE tbl_saldo_kasnet
						SET
							saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
							saldo_final=" . $new_saldo_fin . "
						WHERE id=" . $post["id"] . "
					";


                    $fin = $mysqli->query($update_kasnet);

                    if ($post["tipo_id"] == 1) {
                        $update_datos_fisicos = "
							UPDATE tbl_caja_datos_fisicos
							SET
								valor=" . $new_saldo_fin . "
							WHERE
								caja_id=" . $post["caja_id"] . "
								AND tipo_id=20
							";

                        $fin2 = $mysqli->query($update_datos_fisicos);
                    }

                    if ($fin) {
                        $saldo_antPost['saldo_final'] = "";
                    }
                    //var_dump($fin);
                }
            }
        }


        /*diashop*/
        $query = "
            SELECT 
                id,
                local_id,
                saldo_incremento AS incremento,
                created_at AS created
            FROM tbl_saldo_disashop 
            WHERE estado = 1
            AND caja_id = " . $data["item_id"] . "
            ORDER BY id DESC
            LIMIT 1
        ";
        $rows = false;
        $query_result = $mysqli->query($query);
        if ($mysqli->error) {
            echo $mysqli->error;
            die;
        }
        if ($r = $query_result->fetch_assoc()) {
            $ultimo = $r;

            $mysqli->query("UPDATE tbl_saldo_disashop SET estado = 0 WHERE id = " . $r["id"]);

            //consulta si hay resgistros posteriores a la fecha
            $registros_posteriores = "
                SELECT
                    id,
                    saldo_incremento,
                    tipo_id,
                    caja_id,
                    created_at
                FROM tbl_saldo_disashop
                WHERE
                    local_id =" . $ultimo["local_id"] . "
                    AND created_at > '" . $ultimo["created"] . "'
                ORDER BY created_at ASC
            ";
            $result_post = $mysqli->query($registros_posteriores);


            while ($r = $result_post->fetch_assoc()) {
                $post = $r;

                //consulto el registro anterior a la fecha de este registro
                $registro_anterior = "
                    SELECT
                        saldo_final
                    FROM tbl_saldo_disashop
                    WHERE
                        local_id=" . $ultimo["local_id"] . "
                        AND  estado = 1
                        AND created_at < '" . $post["created_at"] . "'
                    ORDER BY created_at DESC
                    LIMIT 1
                ";


                $resul = $mysqli->query($registro_anterior);
                while ($saldo_antPost = $resul->fetch_assoc()) {
                    $new_saldo_fin = (double)($saldo_antPost['saldo_final'] + $post["saldo_incremento"]);

                    $update_disashop = "
                        UPDATE tbl_saldo_disashop
                        SET
                            saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
                            saldo_final=" . $new_saldo_fin . "
                        WHERE id=" . $post["id"] . "
                    ";


                    $fin = $mysqli->query($update_disashop);

                    if ($post["tipo_id"] == 1) {
                        $update_datos_fisicos = "
                            UPDATE tbl_caja_datos_fisicos
                            SET
                                valor=" . $new_saldo_fin . "
                            WHERE
                                caja_id=" . $post["caja_id"] . "
                                AND tipo_id=25
                            ";

                        $fin2 = $mysqli->query($update_datos_fisicos);
                    }

                    if ($fin) {
                        $saldo_antPost['saldo_final'] = "";
                    }
                    //var_dump($fin);
                }
            }
        }
        /*fin disashop*/

        $mysqli->query("UPDATE tbl_repositorio_transacciones_bancarias SET tipo=0, caja_id=NULL WHERE caja_id =" . $data["item_id"]);
        $caja_eliminada_query = "INSERT INTO tbl_caja_eliminados (id, at_unique_id, turno_id, usuario_id, local_caja_id, fecha_apertura, fecha_operacion, fecha_cierre, observaciones, estado, validar) SELECT id, at_unique_id, turno_id, usuario_id, local_caja_id, fecha_apertura, fecha_operacion, fecha_cierre, observaciones, estado, validar FROM tbl_caja WHERE id = $data[item_id]";
        $query = "INSERT INTO tbl_caja_eliminados SELECT * FROM tbl_caja WHERE id =" . $data["item_id"];
        $mysqli->query($caja_eliminada_query);
        $mysqli->query("UPDATE tbl_caja_eliminados SET fecha_eliminacion = NOW() WHERE id = $data[item_id];");

        $login_id = $values["usuario_id"] = (is_array($login) ? (array_key_exists("id", $login) ? $login["id"] : "") : "");
        $mysqli->query("UPDATE tbl_caja_eliminados SET usuario_eliminacion = '$login_id' WHERE id = $data[item_id];");

        $mysqli->query("INSERT INTO tbl_caja_detalle_eliminados SELECT * FROM tbl_caja_detalle WHERE caja_id =" . $data["item_id"]);
        $mysqli->query("INSERT INTO tbl_caja_datos_fisicos_eliminados SELECT * FROM tbl_caja_datos_fisicos WHERE caja_id =" . $data["item_id"]);
        $mysqli->query($solicitud_estado_command);
        $mysqli->query($caja_command);
        $mysqli->query($caja_detalle_command);
        $mysqli->query($caja_datos_fisicos_command);


        $solicitud_depositos_command = "UPDATE tbl_caja_clientes_depositos SET turno_id = null WHERE turno_id=" . $data["item_id"];
        $mysqli->query($solicitud_depositos_command);
        $mysqli->query("UPDATE tbl_registro_premios rp SET rp.caja_id = 0 WHERE rp.caja_id = $data[item_id];");


        //cierre de efectivo 
        $mysqli->query("UPDATE tbl_caja_cierre_efectivo SET status = 0, updated_at = '".date('Y-m-d H:i:s')."', user_updated_id = '".$login_id."' WHERE caja_id = $data[item_id];");

        // echo $caja_command;
        // echo "\n";
        // echo $caja_detalle_command;
        // echo "\n";
        // echo $caja_datos_fisicos_command;
        // echo "\n";
    }

    if (isset($_POST["sec_caja_archivo_guardar"])) {
        $item_id = $_POST["item_id"];
        $result = [];

        $path = '/var/www/html/files_bucket/cajas/';

        if (!is_dir($path)) {
            $result['status'] = 400;
            $result['message'] = 'La ruta de almacenamiento no existe. Comunicarse con soporte';
            echo json_encode($result);
            exit();
        }

        for ($i = 0; $i < count($_FILES['archivo']['name']); $i++) {
            $file = $_FILES['archivo']['name'][$i];
            $size = $_FILES['archivo']['size'][$i];
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext == "exe" || $ext == "EXE"  || $ext == "html"  || $ext == "HTML") {
                $result['status'] = 400;
                $result['message'] = 'El archivo '.$file.' no cumple el formato establecido';
                echo json_encode($result);
                exit();
            }
        }

        for ($i = 0; $i < count($_FILES['archivo']['name']); $i++) {
            
            $file = $_FILES['archivo']['name'][$i];
            $size = $_FILES['archivo']['size'][$i];
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // Limpiar el nombre del archivo: quitar tildes y convertir a minúsculas
            $clean_filename = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $file));
            // Reemplazar caracteres especiales no alfanuméricos con guiones bajos
            $clean_filename = preg_replace("/[^a-z0-9.]/", "_", $clean_filename);

        

            $filename = $item_id . "_" . strtolower(preg_replace('/.[\w]+$/', '', $clean_filename)) . "_" . date('YmdHis');
            $filename = str_replace(",", "_", $filename);
            $filepath = $path . $filename . "." . $ext;

            // Mover el archivo a la ruta de almacenamiento
            if (move_uploaded_file($_FILES['archivo']['tmp_name'][$i], $filepath)) {
                $mysqli->query("
                    INSERT INTO tbl_archivos(
                        tabla,
                        item_id,
                        ext,size,
                        archivo,
                        fecha,
                        orden,
                        estado
                    )
                    VALUES(
                        'tbl_caja',
                        '" . $item_id . "',
                        '" . $ext . "',
                        '" . $size . "',
                        '" . $filename . "." . $ext . "',
                        '" . date('Y-m-d H:i:s') . "',
                        '0',
                        '1'
                    )"
                );
                $insert_id = mysqli_insert_id($mysqli);

                $result[] = [
                    'id' => $insert_id,
                    'filename' => $filename . "." . $ext,
                    'filepath' => $filepath
                ];
            }            
        }
        echo json_encode($result);
        die;
    }

    if (isset($_POST["sec_caja_archivo_eliminar"])) {
        $caja_id = $_POST["caja_id"];
        $item_id = $_POST["sec_caja_archivo_eliminar"];
        $nombre = $_POST["nombre_archivo"];
        $loginAreaID = isset($_POST["loginAreaID"]) ? $_POST["loginAreaID"] : '';

        $estado = $mysqli->query("SELECT validar FROM tbl_caja l WHERE l.id = '" . $caja_id . "'")->fetch_assoc();
        $rowEstado = $estado['validar'];

        if ($rowEstado == 1 && $loginAreaID != 6) {
            echo "validado";
            exit();
        }
        $file = '/var/www/html/files_bucket/cajas/' . $nombre;
        if (file_exists($file)) {
            rename('/var/www/html/files_bucket/cajas/' . $nombre, '/var/www/html/files_bucket/cajas/del_' . $nombre);
        } else {
            echo 'El archivo ' . $file . ' no existe.';
        }
        $delete_archivo = "update tbl_archivos SET estado = '0' WHERE id='" . $item_id . "'";
        $respuesta = $mysqli->query($delete_archivo);
        if ($respuesta === TRUE) {
            echo "ok";
        } else {
            echo "Error";
        }
        die;
    }

    if (isset($_POST["sec_caja_correo"])) {
        include('../sys/mailer/class.phpmailer.php');

        // /var/www/html/sys/mailer/class.phpmailer.php
        $mail = new PHPMailer(true);
        $save_data = $_POST["sec_caja_correo"];
        $detTurno = $save_data["turno"];
        $diferencia = $save_data["diferencia"];
        $usuElimina = $save_data["usuElimina"];
        $idcc = $save_data["idcc"];
        $color = negative_check($diferencia);

        $local = $save_data["local"];
        $url = $save_data["url"];
        $apertura = $save_data["apertura"];
        $subject = "diff - c.c. " . $idcc . " - Local: " . $local . " - Fecha : " . date("Y-m-d") . " ";
        $destinatarios = $mysqli->query("SELECT correo FROM tbl_destinatario WHERE estado = 1");
        if ($destinatarios) {

            $myArray = array();
            while ($df = $destinatarios->fetch_assoc()) {
                array_push($myArray, $df['correo']);
            }

            //$sent_email= "'".implode("','",$myArray)."'";

            try {
                $mail->IsSMTP();

                $mail->SMTPDebug = 1;
                $mail->SMTPAuth = true;

                $mail->Host = "smtp.gmail.com";
                $mail->Port = 465;
                $mail->SMTPSecure = "ssl";
                while (list ($iey, $val) = each($myArray)) {
                    $mail->AddAddress($val);
                }
                //$mail->AddAddress('ghaluizu@testtest.hotmail.com','qew@testtest.hotmail.com','otr@testtest.hotmail.com');
                $mail->Username = env('MAIL_GESTION_USER');
                $mail->Password = env('MAIL_GESTION_PASS');
                $mail->FromName = "Apuesta Total";
                $mail->Subject = $subject;
                $mail->Body = '<div style="text-align:center"><div style="font-size:16px;">Existe diferencia de : <span style="font-weight:bold;color:' . $color . '">' . $diferencia . '</span></div><div style="margin-top: 2px;">Url : <a href="' . $url . '">Caja Detalle Apuesta total</a></div><div>Usuario que Cerro : ' . $usuElimina . '</div><h3>Informacion del Turno</h3><table border="1" style="text-align:left;border:0.2px solid black;margin: auto;
		margin-top: 20px;">' . html_entity_decode(htmlspecialchars($detTurno)) . '</table><div>';
                $mail->isHTML(true);
                if ($mail->Send()) {
                    $return["email_sent"] = "ok";
                }
            } catch (phpmailerException $ex) {
                $return["email_error"] = $mail->ErrorInfo;
                $insert_data["is_error"] = $mail->ErrorInfo;
            }

        }
        // print_r($save_data);
    }

    if (isset($_POST["sec_caja_observacion_control_interno"])) {
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
        $query = "";

        if ($id > 0 && $tipo == "remove") {
            $query = "UPDATE tbl_caja_observaciones_lista SET state = 0 WHERE id = $id";
        } else if ($id > 0) {
            $query = "UPDATE tbl_caja_observaciones_lista SET titulo = '$titulo', descripcion = '$descripcion' WHERE id = $id";
        } else {
            $query = "INSERT INTO tbl_caja_observaciones_lista(titulo,descripcion,orden) VALUES('$titulo','$descripcion',$id)";
        }

        $result = $mysqli->query($query);
        if ($result) {
            get_tbl_caja_observaciones_lista($mysqli);
        } else {
            echo "error";
        }

        die;
    }


    if (isset($_POST["sec_caja_add_oci"])) {
        $idOci = isset($_POST['idOci']) ? $_POST['idOci'] : '';
        $idCaja = isset($_POST['idCaja']) ? $_POST['idCaja'] : '';

        if (!empty($idOci) && !empty($idCaja)) {
            $query = "UPDATE tbl_caja SET id_oci = $idOci WHERE id = $idCaja";
            $result = $mysqli->query($query);
            if ($result) {
                echo "ok";
            } else {
                echo "db";
            }
        } else {
            echo "data";
        }

        die;
    }

    if (isset($_POST["sec_caja_remove_oci"])) {
        $idCaja = isset($_POST['idCaja']) ? $_POST['idCaja'] : '';
        $query = "UPDATE tbl_caja SET id_oci = 0 WHERE id = $idCaja";
        $result = $mysqli->query($query);
        if ($result) {
            echo "ok";
        } else {
            echo "db";
        }
        die;
    }

    if (isset($_POST["accion"]) && $_POST["accion"] === "caja_btn_slot_salida_confirmar_entrega") {
        $id_prestamo = $_POST["id_prestamo"];

        $result = array();
        $error = '';

        $query_update = "
                    UPDATE tbl_caja_prestamo_slot 
                        SET caja_origen_entrega_dinero = 1
                    WHERE id = '" . $id_prestamo . "'
                    ";


        $mysqli->query($query_update);

        if ($mysqli->error) {
            $error = $mysqli->error;
        }

        if ($error == '') {
            $result["http_code"] = 200;
            $result["status"] = "Datos realizados correctamente.";
            $result["error"] = $error;
        } else {
            $result["http_code"] = 400;
            $result["status"] = "Error.";
            $result["error"] = $error;
        }

        echo json_encode($result);
        exit();
    }

    if (isset($_POST["accion"]) && $_POST["accion"] === "caja_btn_slot_entrada_confirmar_recibo") {
        $id_prestamo = $_POST["id_prestamo"];

        $result = array();
        $error = '';

        $query_update = "
                    UPDATE tbl_caja_prestamo_slot
                        SET caja_destino_recibe_dinero = 1
                    WHERE id = '" . $id_prestamo . "'
                    ";


        $mysqli->query($query_update);

        if ($mysqli->error) {
            $error = $mysqli->error;
        }

        if ($error == '') {
            $result["http_code"] = 200;
            $result["status"] = "Datos realizados correctamente.";
            $result["error"] = $error;
            send_email_confirmar_recibo_dinero($id_prestamo);
        } else {
            $result["http_code"] = 400;
            $result["status"] = "Error.";
            $result["error"] = $error;
        }

        echo json_encode($result);
        exit();
    }

    if (isset($_POST["accion"]) && $_POST["accion"] === "caja_btn_vincular_slot_caja_eliminada") {
        $id_caja_actual = $_POST["id_caja_actual"];

        $result = array();
        $error = '';

        // INICIO VINCULAR PRESTAMO SALIDA, CAMPO: caja_id_origen
        $select_vincular_prestamo_salida = "
                SELECT
                    ce.id, ce.turno_id, ce.local_caja_id, ce.fecha_operacion,
                    ps.id AS prestamo_id, 
                    ps.local_id_origen, ps.caja_id_origen,
                    ps.local_id_destino, ps.caja_id_destino
                FROM tbl_caja c
                INNER JOIN tbl_caja_eliminados ce
                ON (ce.turno_id = c.turno_id AND ce.local_caja_id = c.local_caja_id AND ce.fecha_operacion = c.fecha_operacion)
                INNER JOIN tbl_caja_prestamo_slot ps
                ON ce.id = ps.caja_id_origen
                WHERE c.id = '" . $id_caja_actual . "'
            ";

        $query_vincular_prestamo_union = $mysqli->query($select_vincular_prestamo_salida);

        while ($sel = $query_vincular_prestamo_union->fetch_assoc()) {
            $id = $sel["id"];
            $turno_id = $sel["turno_id"];
            $local_caja_id = $sel["local_caja_id"];
            $fecha_operacion = $sel["fecha_operacion"];
            $prestamo_id = $sel["prestamo_id"];
            $local_id_origen = $sel["local_id_origen"];
            $caja_id_origen = $sel["caja_id_origen"];
            $local_id_destino = $sel["local_id_destino"];
            $caja_id_destino = $sel["caja_id_destino"];

            $query_update = "
                            UPDATE tbl_caja_prestamo_slot
                                SET caja_id_origen = '" . $id_caja_actual . "'
                            WHERE id = '" . $prestamo_id . "'
                            ";

            $mysqli->query($query_update);

            if ($mysqli->error) {
                $error = $mysqli->error;

                $result["http_code"] = 400;
                $result["status"] = "Error.";
                $result["error"] = $error;

                echo json_encode($result);
                exit();
            }
        }

        // FIN VINCULAR PRESTAMO SALIDA, CAMPO: caja_id_origen

        // INICIO VINCULAR PRESTAMO INGRESO, CAMPO: caja_id_destino
        $select_vincular_prestamo_ingreso = "
                SELECT
                    ce.id, ce.turno_id, ce.local_caja_id, ce.fecha_operacion,
                    ps.id AS prestamo_id, 
                    ps.local_id_origen, ps.caja_id_origen,
                    ps.local_id_destino, ps.caja_id_destino
                FROM tbl_caja c
                INNER JOIN tbl_caja_eliminados ce
                ON (ce.turno_id = c.turno_id AND ce.local_caja_id = c.local_caja_id AND ce.fecha_operacion = c.fecha_operacion)
                INNER JOIN tbl_caja_prestamo_slot ps
                ON ce.id = ps.caja_id_destino
                WHERE c.id = '" . $id_caja_actual . "'
            ";

        $query_vincular_prestamo_union = $mysqli->query($select_vincular_prestamo_ingreso);

        while ($sel = $query_vincular_prestamo_union->fetch_assoc()) {
            $id = $sel["id"];
            $turno_id = $sel["turno_id"];
            $local_caja_id = $sel["local_caja_id"];
            $fecha_operacion = $sel["fecha_operacion"];
            $prestamo_id = $sel["prestamo_id"];
            $local_id_origen = $sel["local_id_origen"];
            $caja_id_origen = $sel["caja_id_origen"];
            $local_id_destino = $sel["local_id_destino"];
            $caja_id_destino = $sel["caja_id_destino"];

            $query_update = "
                            UPDATE tbl_caja_prestamo_slot
                                SET caja_id_destino = '" . $id_caja_actual . "'
                            WHERE id = '" . $prestamo_id . "'
                            ";

            $mysqli->query($query_update);

            if ($mysqli->error) {
                $error = $mysqli->error;

                $result["http_code"] = 400;
                $result["status"] = "Error.";
                $result["error"] = $error;

                echo json_encode($result);
                exit();
            }
        }

        // FIN VINCULAR PRESTAMO SALIDA, CAMPO: caja_id_destino

        if ($mysqli->error) {
            $error = $mysqli->error;
        }

        if ($error == '') {
            $result["http_code"] = 200;
            $result["status"] = "Datos realizados correctamente.";
            $result["error"] = $error;
        } else {
            $result["http_code"] = 400;
            $result["status"] = "Error.";
            $result["error"] = $error;
        }

        echo json_encode($result);
        exit();
    }

    if(isset($_POST["accion"]) && $_POST["accion"] === "caja_btn_boveda_confirmar_ingreso")
    {
        $user_id = $login?$login['id']:null;

        if((int)$user_id > 0)
        {
            $id_prestamo = $_POST["id_prestamo"];
            $caja_id = $_POST["caja_id"];

            $result = array();
            $error = '';

            $select_verificar_prestamo = 
            "
                SELECT
                    p.id
                FROM tbl_caja_prestamo_boveda p
                WHERE p.id = '".$id_prestamo."' AND p.caja_id_recibe_dinero = 0
            ";

            $sel_query_verificar_prestamo = $mysqli->query($select_verificar_prestamo);

            $row_count = $sel_query_verificar_prestamo->num_rows;

            if ($row_count > 0)
            {
                $query_update = "
                        UPDATE tbl_caja_prestamo_boveda
                            SET caja_id_recibe_dinero = 1,
                                caja_id_receptora = '".$caja_id."',
                                fecha_recibe_dinero = '".date('Y-m-d H:i:s')."'
                        WHERE id = '" . $id_prestamo . "'
                        ";


                $mysqli->query($query_update);

                if ($mysqli->error)
                {
                    $error = $mysqli->error;
                }

                if ($error == '')
                {
                    $result["http_code"] = 200;
                    $result["status"] = "Datos realizados correctamente.";
                    $result["error"] = $error;
                }
                else
                {
                    $result["http_code"] = 400;
                    $result["status"] = "Error.";
                    $result["error"] = $error;
                }

                echo json_encode($result);
                exit();
            }
            else
            {
                $result["http_code"] = 400;
                $result["status"] = "Error.";
                $result["error"] = "Ya se encuentra registrado el recibo del dinero";

                echo json_encode($result);
                exit();
            }

        }
        else
        {
            $result["http_code"] = 400;
            $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

            echo json_encode($result);
            exit();
        }
        
    }

    if (isset($_POST["accion"]) && $_POST["accion"] === "caja_btn_vincular_boveda_caja_eliminada")
    {
        $usuario_id = $login?$login['id']:null;
    
        $id_caja_actual = $_POST["id_caja_actual"];

        $result = array();
        $error = '';

        // INICIO VINCULAR PRESTAMO SALIDA, CAMPO: caja_id_origen
        $select = "
                SELECT
                    ce.id, ce.turno_id, ce.local_caja_id, ce.fecha_operacion,
                    pb.id AS prestamo_id, 
                    pb.local_id, pb.caja_id_receptora
                FROM tbl_caja c
                    INNER JOIN tbl_caja_eliminados ce
                    ON (ce.turno_id = c.turno_id AND ce.local_caja_id = c.local_caja_id AND ce.fecha_operacion = c.fecha_operacion)
                    INNER JOIN tbl_caja_prestamo_boveda pb
                    ON ce.id = pb.caja_id_receptora
                WHERE c.id = '" . $id_caja_actual . "'
            ";

        $query_vincular = $mysqli->query($select);

        while ($sel = $query_vincular->fetch_assoc())
        {
            $id = $sel["id"];
            $turno_id = $sel["turno_id"];
            $local_caja_id = $sel["local_caja_id"];
            $fecha_operacion = $sel["fecha_operacion"];
            $prestamo_id = $sel["prestamo_id"];
            $local_id = $sel["local_id"];
            $caja_id_receptora = $sel["caja_id_receptora"];

            $query_update = "
                            UPDATE tbl_caja_prestamo_boveda
                                SET caja_id_receptora = '" . $id_caja_actual . "'
                            WHERE id = '" . $prestamo_id . "'
                            ";

            $mysqli->query($query_update);

            if ($mysqli->error)
            {
                $error = $mysqli->error;

                $result["http_code"] = 400;
                $result["status"] = "Error.";
                $result["error"] = $error;

                echo json_encode($result);
                exit();
            }
        }

        // FIN VINCULAR PRESTAMO SALIDA, CAMPO: caja_id_origen

        if ($error == '')
        {
            $result["http_code"] = 200;
            $result["status"] = "Datos realizados correctamente.";
            $result["error"] = $error;
        }
        else
        {
            $result["http_code"] = 400;
            $result["status"] = "Error.";
            $result["error"] = $error;
        }

        echo json_encode($result);
        exit();
    }

    if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_caja_dato_fisico"){

        try {
            $caja_dato_fisico_id = $_POST["caja_dato_fisico_id"];
            $error = '';
    
            $select_caja = "SELECT 
                            df.id, 
                            df.caja_id,
                            df.caja_unique_id,
                            df.valor,
                            df.tipo_id,
                            dft.nombre, 
                            dft.operador
                        FROM tbl_caja_datos_fisicos AS df
                        INNER JOIN tbl_caja_datos_fisicos_tipos AS dft ON df.tipo_id = dft.id
                        WHERE df.id = '" . $caja_dato_fisico_id . "'";
            $query_caja = $mysqli->query($select_caja);
            $data = $query_caja->fetch_assoc();
    
            if ($data) {
                $result['status'] = 200;
                $result['message'] = '';
                $result['result'] = $data;
                echo json_encode($result);
                exit();
            }
    
            $result['status'] = 404;
            $result['message'] = 'A ocurrido un error, intento mas tarde.';
            $result['result'] = '';
            echo json_encode($result);
            exit();

        } catch (\Exception $e) {
            
            $result['status'] = 404;
            $result['message'] = 'A ocurrido un error, intento mas tarde.';
            $result['result'] = $e;
            echo json_encode($result);
            exit();
        }
    }


    if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_caja_dato_fisico"){

        try {

            $caja_id = $_POST["caja_id"];
            $id = $_POST["id"];
            $valor = $_POST["valor"];
            $error = '';


            //DATOS FISICO A CAMBIAR
            $select_dato_fisico = "SELECT df.id,  df.valor, df.tipo_id, c.fecha_operacion, lc.local_id
            FROM tbl_caja_datos_fisicos AS df 
            INNER JOIN tbl_caja AS c ON c.id = df.caja_id
            INNER JOIN tbl_local_cajas AS lc ON lc.id = c.local_caja_id
            WHERE  df.id = ".$id;
            $query_dato_fisico = $mysqli->query($select_dato_fisico);
            $dato_fisico = $query_dato_fisico->fetch_assoc();

            if (Empty($dato_fisico['valor'])) {
                $dato_fisico['valor'] = 0;
            }
            
            //modificar datos fisico
            $select_caja = "UPDATE tbl_caja_datos_fisicos SET valor = '".$valor."' WHERE id =".$id;
            $query_caja = $mysqli->query($select_caja);

            // registrar hostorial de cambio
            $query_insert_historial = "INSERT INTO tbl_caja_datos_fisicos_historial_cambios (
                caja_dato_fisico_id,
                valor_anterior,
                valor_nuevo,
                status,
                id_usuario,
                created_at
            ) VALUES (
                '".$dato_fisico['id']."',
                '".$dato_fisico['valor']."',
                '".$valor."',
                1,
                ".$login['id'].",
                '".date('Y-m-d H:i:s')."'
            )"; 
            $mysqli->query($query_insert_historial);
            
            if ($query_caja) {
                $select_datos_fisicos = "SELECT 
                    df.id, 
                    df.caja_id,
                    df.caja_unique_id,
                    df.valor,
                    dft.nombre, 
                    dft.operador,
                    dft.columna
                FROM tbl_caja_datos_fisicos AS df
                INNER JOIN tbl_caja_datos_fisicos_tipos AS dft ON df.tipo_id = dft.id
                WHERE  df.caja_id = ".$caja_id;
                $datos_fisicos_query = $mysqli->query($select_datos_fisicos);
                $cierre_sistema = 0;
                $cierre_efectivo = 0;
                $diferencia = 0;
                while($df = $datos_fisicos_query->fetch_assoc()){
                    if ($df['operador'] == "+") {
                        $cierre_sistema += $df['valor'];
                    }else if($df['operador'] == "-"){
                        $cierre_sistema -= $df['valor'];
                    }
                }


                $query_select_cierre_sistema = "SELECT * FROM tbl_caja_datos_fisicos where tipo_id = 10 AND caja_id = ".$caja_id; 
                $caja_cierre_sistema = $mysqli->query($query_select_cierre_sistema);
                $caja_cierre_sistema = $caja_cierre_sistema->fetch_assoc();
    
                if (isset($caja_cierre_sistema['id'])){
                      //modificar cierre de datos fisico
                    $query_update_df_cierre_sistema = "UPDATE tbl_caja_datos_fisicos SET valor = '".$cierre_sistema."' WHERE id = ".$caja_cierre_sistema['id']; 
                    $mysqli->query($query_update_df_cierre_sistema);
    

                    if($dato_fisico['tipo_id'] == 3){ //Devolución Bóveda
                        
                        $fecha_recalculo = date("Y-m-d",strtotime($dato_fisico['fecha_operacion']."- 1 days")); 
                        //INICIO QUERY UPDATE SALDOS BOVEDA
                        $selector_query = "
                        select ss.fecha_operacion,ss.turno_id,ft.*,df.operador,df.nombre from tbl_local_cajas as de 
                        left join tbl_caja as ss on ss.local_caja_id = de.id
                        left join tbl_caja_datos_fisicos as ft on ft.caja_id =  ss.id
                        left join tbl_caja_datos_fisicos_tipos as df on df.id = ft.tipo_id
                        where ss.fecha_operacion>= '".$fecha_recalculo."' and local_id = '".$dato_fisico['local_id']."' and ss.estado=1
                        order by ss.fecha_operacion asc , ss.turno_id asc , ft.tipo_id asc
                        ";
                    

                        $mysql_result = $mysqli->query($selector_query);
                        $locales_result = array();

                        while ($r = $mysql_result->fetch_assoc()) {
                            $r['valor'] = (empty($r['valor'])) ? 0 : floatval($r['valor']);
                            $r['new_valor'] = $r['valor'];
                            $r['old_valor'] = $r['valor'];
                            $r['updated'] = 0;
                            $locales_result[$r['caja_id']][$r['tipo_id']] = $r;
                        }
                        $array_data = [];
                        $init_counter = 1;
                        foreach ($locales_result as $key => $caja_fisica) {
                            $contador = $init_counter++;
                            $suma_resultado = 0;
                            foreach ($caja_fisica as $key => $value) {
                                $suma_resultado = $value['valor'];
                                $caja_fisica[$key]['counter'] = $contador;
                            }

                            $array_data[$caja_fisica[$key]['counter']] = $caja_fisica;
                        }
                        $update_data = array();
                        foreach ($array_data as $key => $data) {

                            $cierre_sistema     = 0.00;
                            $operador_mas       = 0;
                            $operador_menos     = 0;
                            foreach ($data as $key_data => $value) {
                                if ($value['operador'] == '+') {
                                    $operador_mas += floatval($value['valor']);
                                } elseif ($value['operador'] == '-') {
                                    $operador_menos += floatval($value['valor']);
                                }
                            }
                            $cierre_sistema = $operador_mas - $operador_menos;
                            $array_data[$key][10]['new_valor'] = $cierre_sistema;
                            if (isset($array_data[$key - 1])) {
                                $array_data[$key][17]['new_valor'] = $array_data[$key - 1][17]['valor'] - $array_data[$key][3]['valor'] + $array_data[$key][2]['valor']- $array_data[$key][26]['valor'];
                                $array_data[$key][17]['valor'] =  $array_data[$key - 1][17]['valor'] - $array_data[$key][3]['valor'] + $array_data[$key][2]['valor']- $array_data[$key][26]['valor'];
                            }
                            $deuda_boveda_siguiente_caja = $data[17]['valor'] - $data[3]['valor'];
                            if (isset($array_data[$key + 1])) {
                                $array_data[$key + 1][17]['valor'] = $deuda_boveda_siguiente_caja;
                            }

                            if ((string)$array_data[$key][17]['old_valor'] == (string)$array_data[$key][17]['new_valor']) {
                                $array_data[$key][17]['updated'] = 0;
                            } else {
                                $array_data[$key][17]['updated'] = 1;
                            }
                        }

                        $array_selector_data = array();
                        $status = false;
                        if (isset($_GET['update']) && $_GET['update'] === 'true') {
                            $status = true;
                        }
                        foreach ($array_data as $key => $data) {
                            foreach ($data as $key => $value) {
                                if ($value['updated'] == 1) {
                                    
                                    $query_update_boveda = "UPDATE tbl_caja_datos_fisicos SET valor = {$value['new_valor']}  WHERE id = {$value['id']}";
                                    $mysqli->query($query_update_boveda);
                                }
                            }
                        }

                        //FIN QUERY UPDATE SALDOS BOVEDA
                                
                    }

                    // registrar hostorial de cambio
                    $query_insert_historial = "INSERT INTO tbl_caja_datos_fisicos_historial_cambios (
                        caja_dato_fisico_id,
                        valor_anterior,
                        valor_nuevo,
                        status,
                        id_usuario,
                        created_at
                    ) VALUES (
                        '".$caja_cierre_sistema['id']."',
                        '".$caja_cierre_sistema['valor']."',
                        '".$cierre_sistema."',
                        1,
                        ".$login['id'].",
                        '".date('Y-m-d H:i:s')."'
                    )"; 
                    $mysqli->query($query_insert_historial);
                }

                      
             

                $result['status'] = 200;
                $result['message'] = '';
                $result['result'] = 0;
                echo json_encode($result);
                exit();
            }
    
            
            


        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['message'] = 'A ocurrido un error, intento mas tarde.';
            $result['result'] = $e;
            echo json_encode($result);
            exit();
        }
    }

    if (isset($_POST["accion"]) && $_POST["accion"] === "historial_cambios_caja_dato_fisico"){

        try {

            $caja_dato_fisico_id = $_POST["caja_dato_fisico_id"];
           
            $query_historial = "SELECT hc.id, hc.valor_anterior, hc.valor_nuevo, hc.created_at, dft.nombre AS tipo,
            CONCAT('[',IFNULL(us.usuario,''),'] ', IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,''),' ', IFNULL(p.apellido_materno,'')) AS usuario 
            FROM tbl_caja_datos_fisicos_historial_cambios AS hc
            INNER JOIN tbl_caja_datos_fisicos AS df ON df.id = hc.caja_dato_fisico_id
            INNER JOIN tbl_caja_datos_fisicos_tipos AS dft ON dft.id = df.tipo_id
            INNER JOIN tbl_usuarios AS us ON us.id = hc.id_usuario
            INNER JOIN tbl_personal_apt AS p ON p.id = us.personal_id AND p.estado = 1
            WHERE hc.status = 1 AND hc.caja_dato_fisico_id = '".$caja_dato_fisico_id."' 
            ORDER BY hc.created_at DESC";
            $list_query = $mysqli->query($query_historial);

            $table_historial = 
            '<table class="table table-condensed table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Usuario</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Valor Anterior</th>
                        <th class="text-center">Valor Nuevo</th>
                        <th class="text-center">Fecha</th>
                    </tr>
                </thead>
                <tbody>';
            while ($li = $list_query->fetch_assoc()) {
                $table_historial .='
                <tr>
                    <td class="text-left">'.$li['usuario'].'</td>
                    <td class="text-left">'.$li['tipo'].'</td>
                    <td class="text-right">'.$li['valor_anterior'].'</td>
                    <td class="text-right">'.$li['valor_nuevo'].'</td>
                    <td class="text-center">'.$li['created_at'].'</td>
                </tr>';
            }
            $table_historial .=' 
                </tbody>
            </table>';
            
            
            $result['status'] = 200;
            $result['message'] = '';
            $result['result'] = $table_historial;
            echo json_encode($result);
            exit();

        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['message'] = 'A ocurrido un error, intento mas tarde.';
            $result['result'] = $e;
            echo json_encode($result);
            exit();
        }
    }


    if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_dato_sistema"){

        try {

            $caja_detalle_id = $_POST["caja_detalle_id"];
            $tipo_id = $_POST["tipo_id"];
           
            $query_select = "SELECT
            lcdt.id
            ,lcdt.nombre
            ,lcdt.descripcion
            ,cdt.nombre AS tipo
            ,cdt.in
            ,cdt.out
            ,lcdt.estado
            ,cd.ingreso AS ingreso
            ,cd.salida AS salida
            ,cd.id AS caja_detalle_id
            ,l.red_id
            ,cdt.id AS cdt_id
            FROM tbl_local_caja_detalle_tipos lcdt
            LEFT JOIN tbl_caja_detalle_tipos cdt ON (cdt.id = lcdt.detalle_tipos_id)
            LEFT JOIN tbl_caja_detalle cd ON (cd.tipo_id = lcdt.id)
            LEFT JOIN tbl_locales l on l.id = lcdt.local_id
            WHERE lcdt.estado = 1";

            if(!empty($caja_detalle_id)){
               $query_select .= " AND cd.id = ".$caja_detalle_id.""; 
            }

            if(!empty($tipo_id)){
                $query_select .= " AND lcdt.id = ".$tipo_id.""; 
             }

            $query_select .= " ORDER BY lcdt.orden ASC";
            $list_query = $mysqli->query($query_select);
            $caja_detalle = $list_query->fetch_assoc();

            if(empty($caja_detalle_id)){
                $caja_detalle['caja_detalle_id'] = "";
                $caja_detalle['ingreso'] = "0.00";
                $caja_detalle['salida'] = "0.00";

            } else {
                $caja_detalle['ingreso'] = Empty( $caja_detalle['ingreso']) ? '0.00': $caja_detalle['ingreso'];
                $caja_detalle['salida'] = Empty( $caja_detalle['salida']) ? '0.00': $caja_detalle['salida'];
            }

            $result['status'] = 200;
            $result['message'] = '';
            $result['result'] = $caja_detalle;
            echo json_encode($result);
            exit();

        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['message'] = 'A ocurrido un error, intento mas tarde.';
            $result['result'] = $e;
            echo json_encode($result);
            exit();
        }
    }

    if (isset($_POST["accion"]) && $_POST["accion"] === "modificar_dato_sistema"){

        try {

            $caja_detalle_id = $_POST["caja_detalle_id"];
            $caja_id = $_POST["caja_id"];
            $tipo_id = $_POST["tipo_id"];
            $local_id = $_POST["local_id"];
            $ingreso = $_POST["ingreso"];
            $salida = $_POST["salida"];
           
            $query_select = "SELECT cd.id, cd.caja_id, cd.ingreso, cd.salida, c.turno_id, c.local_caja_id, c.fecha_operacion, lcdt.detalle_tipos_id
            FROM tbl_caja_detalle AS cd 
            LEFT JOIN tbl_local_caja_detalle_tipos as lcdt ON lcdt.id = cd.tipo_id
            LEFT JOIN tbl_caja_detalle_tipos AS cdt ON cdt.id = lcdt.detalle_tipos_id
            LEFT JOIN tbl_caja AS c ON c.id = cd.caja_id
            WHERE cd.id = '".$caja_detalle_id."'";

            $list_query = $mysqli->query($query_select);
            $caja_detalle_old = $list_query->fetch_assoc();
            $caja_detalle_old['ingreso'] = Empty($caja_detalle_old['ingreso']) ? 0 : $caja_detalle_old['ingreso'];
            $caja_detalle_old['salida'] = Empty($caja_detalle_old['salida']) ? 0 : $caja_detalle_old['salida'];

            if($caja_detalle_id != null){
                $query_update = "UPDATE tbl_caja_detalle SET ingreso = '".$ingreso."', salida = '".$salida."' WHERE  id = '".$caja_detalle_id."'";
                $mysqli->query($query_update);
                if($mysqli->error){
                    $result['status'] = 404;
                    $result['message'] = 'A ocurrido un error, intentelo de nuevo. Error: '. $mysqli->error ;
                    $result['query'] = $query_update;
                    echo json_encode($result);
                    exit();
                }

            } else {
                $select_caja = "SELECT * FROM tbl_caja where id = {$caja_id}";
                $caja = $mysqli->query($select_caja);

                if($mysqli->error){
                    $result['status'] = 404;
                    $result['message'] = 'A ocurrido un error, intentelo de nuevo. Error: '. $mysqli->error ;
                    $result['query'] = $select_caja;
                    echo json_encode($result);
                    exit();
                }

                $caja = $caja->fetch_assoc();

                $detalle = [];
                $detalle["caja_id"] = $caja_id;
                $detalle["tipo_id"] = $tipo_id;
                $detalle["ingreso"] = $ingreso;
                $detalle["salida"] = $salida;
                $detalle["estado"] = "1";
                $detalle["at_unique_id"] = md5("d_caja_id" . $caja_id . "_tipo_id_" . $tipo_id);
                $detalle["caja_unique_id"] = $caja["at_unique_id"];

                $insert_caja_detalle = "INSERT INTO tbl_caja_detalle";
                $insert_caja_detalle .= "(caja_id, tipo_id, ingreso, salida, estado, at_unique_id, caja_unique_id)";
                $insert_caja_detalle .= " VALUES ";
                $insert_caja_detalle .= "(";
                $insert_caja_detalle .= $caja_id .', ';
                $insert_caja_detalle .= $tipo_id .', ';
                $insert_caja_detalle .= $ingreso .', ';
                $insert_caja_detalle .= $salida .', ';
                $insert_caja_detalle .= $detalle['estado'] .', ';
                $insert_caja_detalle .= "'" . $detalle['at_unique_id'] ."', ";
                $insert_caja_detalle .= "'" . $detalle['caja_unique_id'] ."' ";
                $insert_caja_detalle .= ")";
                
                $mysqli->query($insert_caja_detalle);
                
                if($mysqli->error){
                    $result['status'] = 404;
                    $result['message'] = 'A ocurrido un error, intentelo de nuevo. Error: '. $mysqli->error ;
                    $result['query'] = $insert_caja_detalle;
                    echo json_encode($result);
                    exit();
                }

                $caja_detalle_id = $mysqli->insert_id;
            }

            $query_insert = "INSERT INTO tbl_caja_datos_sistema_historial_cambios (id_detalle_caja, valor_anterior_ingreso, valor_anterior_salida, valor_nuevo_ingreso, valor_nuevo_salida, status, user_created_id, created_at)
             VALUES ('".$caja_detalle_id."', '".$caja_detalle_old['ingreso']."', '".$caja_detalle_old['salida']."', '".$ingreso."', '".$salida."', '1', '".$login['id']."', '".date('Y-m-d H:i:s')."')";
            $mysqli->query($query_insert);

            if($mysqli->error){
                $result['status'] = 404;
                $result['message'] = 'A ocurrido un error, intentelo de nuevo. Error: '. $mysqli->error ;
                $result['query'] = $query_insert;
                echo json_encode($result);
                exit();
            }

            //Inicio kasnet 
            if($caja_detalle_old['detalle_tipos_id'] == 13 && !Empty($caja_detalle_old["local_caja_id"])){ // kASNET
                $resultado_kasnet = -1 * ($ingreso - $salida);
                $query_result_sk = $mysqli->query("SELECT * FROM tbl_saldo_kasnet WHERE caja_id = '".$caja_id."' AND tipo_id = 1 LIMIT 1");
                $result_sk = $query_result_sk->fetch_assoc();


                if (isset($result_sk['id']) && !Empty($result_sk['id'])){
                    $new_saldo_fin = (float) $result_sk['saldo_anterior'] + $resultado_kasnet;
                    $update_kasnet = "UPDATE tbl_saldo_kasnet
                        SET
                            saldo_incremento=" . $resultado_kasnet . ",
                            saldo_final=" . $new_saldo_fin . "
                        WHERE id=" . $result_sk["id"];
                        $mysqli->query($update_kasnet);


                        $update_datos_fisicos = "UPDATE tbl_caja_datos_fisicos
                                    SET
                                        valor=" . $new_saldo_fin . "
                                    WHERE
                                        caja_id=" . $caja_id . "
                                        AND tipo_id=20";
                        $mysqli->query($update_datos_fisicos);

                        $ultimo = [
                            'id' => $result_sk["id"],
                            'local_id' => $result_sk["local_id"],
                            'incremento' => $resultado_kasnet,
                            'created' => $result_sk["created_at"],
                        ];
        
                        //consulta si hay resgistros posteriores a la fecha
                        $registros_posteriores = "
                            SELECT
                                id,
                                saldo_incremento,
                                tipo_id,
                                caja_id,
                                created_at
                            FROM tbl_saldo_kasnet
                            WHERE
                                local_id =" . $ultimo["local_id"] . "
                                AND created_at > '" . $ultimo["created"] . "'
                            ORDER BY created_at ASC
                        ";
                        $result_post = $mysqli->query($registros_posteriores);
        
                        $row_count = $result_post->num_rows;
                        if($row_count > 0){
                            while ($r = $result_post->fetch_assoc()) {
                                $post = $r;
            
                                //consulto el registro anterior a la fecha de este registro
                                $registro_anterior = "
                                    SELECT
                                        saldo_final
                                    FROM tbl_saldo_kasnet
                                    WHERE
                                        local_id=" . $ultimo["local_id"] . "
                                        AND  estado = 1
                                        AND created_at < '" . $post["created_at"] . "'
                                    ORDER BY created_at DESC
                                    LIMIT 1
                                ";
            
            
                                $resul = $mysqli->query($registro_anterior);
                                while ($saldo_antPost = $resul->fetch_assoc()) {
                                    $new_saldo_fin = (float)($saldo_antPost['saldo_final'] + $post["saldo_incremento"]);
            
                                    $update_kasnet = "
                                        UPDATE tbl_saldo_kasnet
                                        SET
                                            saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
                                            saldo_final=" . $new_saldo_fin . "
                                        WHERE id=" . $post["id"] . "
                                    ";
            
            
                                    $fin = $mysqli->query($update_kasnet);
            
                                    if ($post["tipo_id"] == 1) {
                                        $update_datos_fisicos = "
                                            UPDATE tbl_caja_datos_fisicos
                                            SET
                                                valor=" . $new_saldo_fin . "
                                            WHERE
                                                caja_id=" . $post["caja_id"] . "
                                                AND tipo_id=20
                                            ";
            
                                        $fin2 = $mysqli->query($update_datos_fisicos);
                                    }
            
                                    if ($fin) {
                                        $saldo_antPost['saldo_final'] = "";
                                    }
                                    //var_dump($fin);
                                }
                            }
                        }else{
                            if ($result_sk["tipo_id"] == 1) {
                                $update_datos_fisicos = "
                                    UPDATE tbl_caja_datos_fisicos
                                    SET
                                        valor=" . $new_saldo_fin . "
                                    WHERE
                                        caja_id=" . $result_sk["caja_id"] . "
                                        AND tipo_id=20
                                    ";
                
                                $mysqli->query($update_datos_fisicos);
                            }
                        }
                        
                }                
            }
            //Fin kasnet

             //Inicio Disashop 
             if($caja_detalle_old['detalle_tipos_id'] == 21 && !Empty($caja_detalle_old["local_caja_id"])){ // DISASHOP
                $resultado_disashop = -1 * ($ingreso - $salida);
                $query_result_sd = $mysqli->query("SELECT * FROM tbl_saldo_disashop WHERE caja_id = '".$caja_id."' AND tipo_id = 1 LIMIT 1");
                $result_sd = $query_result_sd->fetch_assoc();

                if (isset($result_sd['id']) && !Empty($result_sd['id'])){
                    $new_saldo_fin = (float) $result_sd['saldo_anterior'] + $resultado_disashop;
                    $update_disashop = "UPDATE tbl_saldo_disashop
                        SET
                            saldo_incremento=" . $resultado_disashop . ",
                            saldo_final=" . $new_saldo_fin . "
                        WHERE id=" . $result_sd["id"];
                        $mysqli->query($update_disashop);

                        $update_datos_fisicos = "UPDATE tbl_caja_datos_fisicos
                                    SET
                                        valor=" . $new_saldo_fin . "
                                    WHERE
                                        caja_id=" . $caja_id . "
                                        AND tipo_id=25";
                        $mysqli->query($update_datos_fisicos);


                        $ultimo = [
                            'id' => $result_sd["id"],
                            'local_id' => $result_sd["local_id"],
                            'incremento' => $resultado_disashop,
                            'created' => $result_sd["created_at"],
                        ];
        
                        //consulta si hay resgistros posteriores a la fecha
                        $registros_posteriores = "
                            SELECT
                                id,
                                saldo_incremento,
                                tipo_id,
                                caja_id,
                                created_at
                            FROM tbl_saldo_disashop
                            WHERE
                                local_id =" . $ultimo["local_id"] . "
                                AND created_at > '" . $ultimo["created"] . "'
                            ORDER BY created_at ASC
                        ";
                        $result_post = $mysqli->query($registros_posteriores);
                        $row_count = $result_post->num_rows;
                        if($row_count > 0){
                            while ($r = $result_post->fetch_assoc()) {
                                $post = $r;
            
                                //consulto el registro anterior a la fecha de este registro
                                $registro_anterior = "
                                    SELECT
                                        saldo_final
                                    FROM tbl_saldo_disashop
                                    WHERE
                                        local_id=" . $ultimo["local_id"] . "
                                        AND  estado = 1
                                        AND created_at < '" . $post["created_at"] . "'
                                    ORDER BY created_at DESC
                                    LIMIT 1
                                ";
            
            
                                $resul = $mysqli->query($registro_anterior);
                                while ($saldo_antPost = $resul->fetch_assoc()) {
                                    $new_saldo_fin = (float)($saldo_antPost['saldo_final'] + $post["saldo_incremento"]);
            
                                    $update_disashop = "
                                        UPDATE tbl_saldo_disashop
                                        SET
                                            saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
                                            saldo_final=" . $new_saldo_fin . "
                                        WHERE id=" . $post["id"] . "
                                    ";
            
            
                                    $fin = $mysqli->query($update_disashop);
            
                                    if ($post["tipo_id"] == 1) {
                                        $update_datos_fisicos = "
                                            UPDATE tbl_caja_datos_fisicos
                                            SET
                                                valor=" . $new_saldo_fin . "
                                            WHERE
                                                caja_id=" . $post["caja_id"] . "
                                                AND tipo_id=25
                                            ";
            
                                        $fin2 = $mysqli->query($update_datos_fisicos);
                                    }
            
                                    if ($fin) {
                                        $saldo_antPost['saldo_final'] = "";
                                    }
                                    //var_dump($fin);
                                }
                            }
                        }else{
                            if ($result_sd["tipo_id"] == 1) {
                                $update_datos_fisicos = "
                                    UPDATE tbl_caja_datos_fisicos
                                    SET
                                        valor=" . $new_saldo_fin . "
                                    WHERE
                                        caja_id=" . $result_sd["caja_id"] . "
                                        AND tipo_id=25
                                    ";
                
                                $mysqli->query($update_datos_fisicos);
                            }
                        }
                        
                }                
            }
            //Fin Disashop


            //resultado datos fisico
            $local_caja_detalle_tipos_command = "SELECT
                lcdt.id
                ,lcdt.nombre
                ,lcdt.descripcion
                ,cdt.nombre AS tipo
                ,cdt.in
                ,cdt.out
                ,lcdt.estado
                ,cd.ingreso AS ingreso
                ,cd.salida AS salida
                ,l.red_id
                ,cdt.id AS cdt_id
                ,cd.id as id_caja_detalle
                FROM tbl_local_caja_detalle_tipos lcdt
                LEFT JOIN tbl_caja_detalle_tipos cdt ON (cdt.id = lcdt.detalle_tipos_id)
                LEFT JOIN tbl_caja_detalle cd ON (cd.tipo_id = lcdt.id AND cd.caja_id = '".$caja_id."')
                LEFT JOIN tbl_locales l on l.id = lcdt.local_id
                WHERE lcdt.local_id = '".$local_id."'
                AND lcdt.estado = 1
                ORDER BY lcdt.orden ASC";
            $local_caja_detalle_tipos_query = $mysqli->query($local_caja_detalle_tipos_command);
            $total_ingreso = 0;
            $total_salida = 0;
            while($lcdt = $local_caja_detalle_tipos_query->fetch_assoc()){
                if($lcdt["red_id"] == 1 && $lcdt["cdt_id"] == 12){
                    continue;
                }
                $ganancia = ($lcdt["ingreso"] - $lcdt["salida"]);
                $total_ingreso += $lcdt["ingreso"];
                $total_salida += $lcdt["salida"];
            }
            $total_resultado = $total_ingreso - $total_salida;


            //modificar resultado de datos fisicos
            $query_select_resultado = "SELECT * FROM tbl_caja_datos_fisicos where tipo_id = 5 AND caja_id = ".$caja_id; 
            $caja_resultado = $mysqli->query($query_select_resultado);
            $caja_resultado = $caja_resultado->fetch_assoc();

            if (isset($caja_resultado['id'])){
                $query_update_df_resultado = "UPDATE tbl_caja_datos_fisicos SET valor = '".$total_resultado."' WHERE id = ".$caja_resultado['id']; 
                $mysqli->query($query_update_df_resultado);

                // registrar hostorial de cambio
                $query_insert_historial = "INSERT INTO tbl_caja_datos_fisicos_historial_cambios (
                    caja_dato_fisico_id,
                    valor_anterior,
                    valor_nuevo,
                    status,
                    id_usuario,
                    created_at
                ) VALUES (
                    '".$caja_resultado['id']."',
                    '".$caja_resultado['valor']."',
                    '".$total_resultado."',
                    1,
                    ".$login['id'].",
                    '".date('Y-m-d H:i:s')."'
                )"; 
                $mysqli->query($query_insert_historial);
            }

           


            // cierre d datos fisicos
            $select_datos_fisicos = "SELECT 
                df.id, 
                df.caja_id,
                df.caja_unique_id,
                df.valor,
                dft.nombre, 
                dft.operador,
                dft.columna
            FROM tbl_caja_datos_fisicos AS df
            INNER JOIN tbl_caja_datos_fisicos_tipos AS dft ON df.tipo_id = dft.id
            WHERE  df.caja_id = ".$caja_id;
            $datos_fisicos_query = $mysqli->query($select_datos_fisicos);
            $cierre_sistema = 0;
            $cierre_efectivo = 0;
            $diferencia = 0;
            while($df = $datos_fisicos_query->fetch_assoc()){
                if ($df['operador'] == "+") {
                    $cierre_sistema += $df['valor'];
                }else if($df['operador'] == "-"){
                    $cierre_sistema -= $df['valor'];
                }
            }

            $query_select_cierre_sistema = "SELECT * FROM tbl_caja_datos_fisicos where tipo_id = 10 AND caja_id = ".$caja_id; 
            $caja_cierre_sistema = $mysqli->query($query_select_cierre_sistema);
            $caja_cierre_sistema = $caja_cierre_sistema->fetch_assoc();

            if (isset($caja_cierre_sistema['id'])){
                  //modificar cierre de datos fisico
                $query_update_df_cierre_sistema = "UPDATE tbl_caja_datos_fisicos SET valor = '".$cierre_sistema."' WHERE id = ".$caja_cierre_sistema['id']; 
                $mysqli->query($query_update_df_cierre_sistema);

                // registrar hostorial de cambio
                $query_insert_historial = "INSERT INTO tbl_caja_datos_fisicos_historial_cambios (
                    caja_dato_fisico_id,
                    valor_anterior,
                    valor_nuevo,
                    status,
                    id_usuario,
                    created_at
                ) VALUES (
                    '".$caja_cierre_sistema['id']."',
                    '".$caja_cierre_sistema['valor']."',
                    '".$cierre_sistema."',
                    1,
                    ".$login['id'].",
                    '".date('Y-m-d H:i:s')."'
                )"; 
                $mysqli->query($query_insert_historial);
            }


            $result['status'] = 200;
            $result['message'] = 'Se ha modificado correctamente el dato del sistema.';
            $result['result'] = [];
            echo json_encode($result);
            exit();

        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['message'] = 'A ocurrido un error, intentelo de nuevo.';
            $result['result'] = $e;
            echo json_encode($result);
            exit();
        }
    }

    if (isset($_POST["accion"]) && $_POST["accion"] === "historial_cambios_caja_dato_sistema"){

        try {

            $caja_detalle_id = $_POST["caja_detalle_id"];
           
            $query_historial = "SELECT
            cd.id
            ,lcdt.nombre
            ,lcdt.descripcion
            ,cdt.nombre AS tipo
            ,dthc.valor_anterior_ingreso
            ,dthc.valor_anterior_salida
            ,dthc.valor_nuevo_ingreso
            ,dthc.valor_nuevo_salida
           ,CONCAT('[',IFNULL(us.usuario,''),'] ', IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,''),' ', IFNULL(p.apellido_materno,'')) AS usuario,
            DATE_FORMAT(dthc.created_at,'%d-%m-%Y %H:%i:%s') as created_at
            
            FROM tbl_local_caja_detalle_tipos lcdt
            INNER JOIN tbl_caja_detalle_tipos cdt ON (cdt.id = lcdt.detalle_tipos_id)
            INNER  JOIN tbl_caja_detalle cd ON (cd.tipo_id = lcdt.id)
            INNER JOIN tbl_locales l on l.id = lcdt.local_id
            INNER JOIN tbl_caja_datos_sistema_historial_cambios AS dthc ON  dthc.id_detalle_caja = cd.id
            INNER JOIN tbl_usuarios AS us ON us.id = dthc.user_created_id
           INNER JOIN tbl_personal_apt AS p ON p.id = us.personal_id AND p.estado = 1
            WHERE cd.id = ".$caja_detalle_id." AND dthc.status = 1
            ORDER BY dthc.created_at DESC";
            $list_query = $mysqli->query($query_historial);

            $table_historial = 
            '<table class="table table-condensed table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center">Usuario</th>
                        <th rowspan="2" class="text-center">Tipo</th>
                        <th colspan="3" class="text-center">Valor Anterior</th>
                        <th colspan="3" class="text-center">Valor Nuevo</th>
                        <th rowspan="2" class="text-center">Fecha</th>
                    </tr>
                    <tr>
                        <td class="text-center">Ingreso</td>
                        <td class="text-center">Salida</td>
                        <td class="text-center">Resultado</td>
                        <td class="text-center">Ingreso</td>
                        <td class="text-center">Salida</td>
                        <td class="text-center">Resultado</td>
                    </tr>
                </thead>
                <tbody>';
            while ($li = $list_query->fetch_assoc()) {
                $resultado_valor_anterior = $li['valor_anterior_ingreso'] - $li['valor_anterior_salida'];
                $resultado_valor_anterior = number_format($resultado_valor_anterior,2,'.','');

                $resultado_valor_nuevo = $li['valor_nuevo_ingreso'] - $li['valor_nuevo_salida'];
                $resultado_valor_nuevo = number_format($resultado_valor_nuevo,2,'.','');

                $table_historial .='
                <tr>
                    <td class="text-left">'.$li['usuario'].'</td>
                    <td class="text-left">'.$li['nombre'].'</td>
                    <td class="text-right">'.$li['valor_anterior_ingreso'].'</td>
                    <td class="text-right">'.$li['valor_anterior_salida'].'</td>
                    <td class="text-right">'.$resultado_valor_anterior.'</td>

                    <td class="text-right">'.$li['valor_nuevo_ingreso'].'</td>
                    <td class="text-right">'.$li['valor_nuevo_salida'].'</td>
                    <td class="text-right">'.$resultado_valor_nuevo.'</td>

                    <td class="text-center">'.$li['created_at'].'</td>
                </tr>';
            }
            $table_historial .=' 
                </tbody>
            </table>';
            
            
            $result['status'] = 200;
            $result['message'] = '';
            $result['result'] = $table_historial;
            echo json_encode($result);
            exit();

        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['message'] = 'A ocurrido un error, intento mas tarde.';
            $result['result'] = $e;
            echo json_encode($result);
            exit();
        }
    }





} else {
    $return["no_login"] = true;
}

function get_tbl_caja_observaciones_lista($mysqli)
{
    echo '<ul>';
    $query = "SELECT * FROM tbl_caja_observaciones_lista WHERE state = 1";
    $result = $mysqli->query($query);
    $i = 1;
    foreach ($result as $row) {
        $modalTipo = "edit";
        $idObservacion = $row['id'];
        $tituloObservacion = $row['titulo'];
        $descripcionObservacion = $row['descripcion'];
        echo '<li>
				<div class="_titulo"><span class="_count">' . $i . '</span>' . $row["titulo"] . '</div>
				<div class="_icono" onclick="modalObservacionesCi(\'' . $modalTipo . '\',' . $idObservacion . ',\'' . $tituloObservacion . '\',\'' . $descripcionObservacion . '\')">
					<i class="glyphicon glyphicon-pencil"></i>
				</div>
			</li>';
        $i++;
    }
    echo '</ul>';
}

function negative_check($value)
{
    if (isset($value)) {
        if (substr(strval($value), 0, 1) == "-") return 'red';
        else return 'blue';
    }
}

function data_to_db($d)
{
    global $mysqli;
    $nd = array();
    foreach ($d as $i => $v) {
        if (is_numeric($v)) {
            $nd[$i] = "'" . $v . "'";
        } else {
            if ($v) {
                $nd[$i] = "'" . trim($mysqli->real_escape_string($v)) . "'";
            } else {
                $nd[$i] = "NULL";
            }
        }
    }
    return $nd;
}

function send_email_confirmar_recibo_dinero($id_prestamo)
{
    include("db_connect.php");
    include("sys_login.php");
    include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
    include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
    
    $respuesta_email = 0;

    $host = $_SERVER["HTTP_HOST"];
    $titulo_email = "";

    $sel_query = $mysqli->query("
        SELECT
            cps.id,
            lo.id AS local_origen_id,
            lo.nombre AS local_origen,
            cps.caja_id_origen,
            cps.monto,
            ld.id AS local_destino_id,
            ld.nombre AS local_destino,
            cps.caja_id_destino,
            cps.user_created_id,
            concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
            cps.created_at AS fecha_solicitud,
            cps.situacion_atencion_etapa_id,
            concat(IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS usuario_atencion,
            cps.fecha_atencion AS fecha_atencion
        FROM tbl_caja_prestamo_slot cps
            INNER JOIN tbl_locales lo
            ON cps.local_id_origen = lo.id
            INNER JOIN tbl_locales ld
            ON cps.local_id_destino = ld.id
            INNER JOIN tbl_usuarios tu
            ON cps.user_created_id = tu.id
            INNER JOIN tbl_personal_apt tp
            ON tu.personal_id = tp.id

            LEFT JOIN tbl_usuarios tua
            ON cps.usuario_atencion_id = tua.id
            LEFT JOIN tbl_personal_apt tpa
            ON tua.personal_id = tpa.id
        WHERE cps.id = '" . $id_prestamo . "'
    ");

    $body = "";
    $body .= '<html>';

    $situacion_atencion = "";

    while ($sel = $sel_query->fetch_assoc()) {
        $id = $sel["id"];
        $local_origen_id = $sel["local_origen_id"];
        $local_origen = $sel["local_origen"];
        $caja_id_origen = $sel["caja_id_origen"];
        $monto = $sel["monto"];
        $local_destino_id = $sel["local_destino_id"];
        $local_destino = trim($sel["local_destino"]);
        $caja_id_destino = trim($sel["caja_id_destino"]);
        $user_created_id = trim($sel["user_created_id"]);
        $usuario_solicitante = trim($sel["usuario_solicitante"]);
        $fecha_solicitud = trim($sel["fecha_solicitud"]);
        $fecha_atencion = trim($sel["fecha_atencion"]);
        $situacion_atencion_etapa_id = trim($sel["situacion_atencion_etapa_id"]);
        $usuario_atencion = trim($sel["usuario_atencion"]);

        if ($situacion_atencion_etapa_id == 1) {
            $situacion_atencion = "Pendiente";
        } else {
            $situacion_atencion = "Aprobado";
        }


        $body .= '<div>';
        $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

        $body .= '<thead>';

        $body .= '<tr>';
        $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
        $body .= '<b>Atención Prestamo</b>';
        $body .= '</th>';
        $body .= '</tr>';

        $body .= '</thead>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Usuario Solicitante:</b></td>';
        $body .= '<td>' . $usuario_solicitante . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
        $body .= '<td>' . $fecha_solicitud . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tienda Origen:</b></td>';
        $body .= '<td>' . $local_origen . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd"><b>Caja Prestadora:</b></td>';
        $body .= '<td>' . $caja_id_origen . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd"><b>Monto:</b></td>';
        $body .= '<td>S/ ' . number_format($monto, 2, '.', ',') . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tienda Destino:</b></td>';
        $body .= '<td>' . $local_destino . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd"><b>Caja Receptora:</b></td>';
        $body .= '<td>' . $caja_id_destino . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Usuario Atención:</b></td>';
        $body .= '<td>' . $usuario_atencion . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd"><b>Fecha Atención:</b></td>';
        $body .= '<td>' . $fecha_atencion . '</td>';
        $body .= '</tr>';

        $body .= '<tr>';
        $body .= '<td style="background-color: #ffffdd"><b>Situación:</b></td>';
        $body .= '<td>' . $situacion_atencion . '</td>';
        $body .= '</tr>';

        $body .= '</table>';
        $body .= '</div>';

    }
    $body .= '<div>';
    $body .= '<br>';
    $body .= '</div>';

    $body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
    $body .= '<a href="' . $host . '/?sec_id=prestamo&sub_sec_id=slot_detalle_solicitud&id=' . $id_prestamo . '&amp;param=2" target="_blank">';
    $body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
    $body .= '<b>Ver Solicitud</b>';
    $body .= '</button>';
    $body .= '</a>';
    $body .= '</div>';

    $body .= '</html>';
    $body .= "";

    $sub_titulo_email = "";

    if (env('SEND_EMAIL') == 'test')
    {
        $sub_titulo_email = "TEST SISTEMAS: ";
    }
    
    $titulo_email = $sub_titulo_email."Préstamo entre tiendas - Tienda Receptora: " . $local_destino . " - Confirmación Entrega de Dinero ID: " . $id_prestamo;

    $cc = [
        //"erick.arias@testtest.apuestatotal.com"
    ];

    // INICIO LISTAR USUARIOS DE LA TIENDAS ORIGEN Y DESTINO DEL PRESTAMO
    // USUARIOS: SUPERVISORES, CAJEROS Y CONTROL INTERNO
    // AREA OPERACIONES: 21
    // CARGO CAJERO: 5
    // CARGO SUPERVISOR: 4
    // AREA: CONTROL INTERNO: 22

    $select_usuarios_enviar_a =
        "
            SELECT DISTINCT
                p.correo
            FROM tbl_usuarios_locales ul
                LEFT JOIN tbl_usuarios u
                ON ul.usuario_id = u.id
                LEFT JOIN tbl_personal_apt p
                ON u.personal_id = p.id
            WHERE ul.local_id IN ('" . $local_origen_id . "', '" . $local_destino_id . "') 
                AND ul.estado = 1 AND p.correo IS NOT NULL AND p.estado = 1 
                AND (p.area_id = 21 AND p.cargo_id IN (4 ,5) OR (p.area_id = 22)
                )
        ";

    $sel_query_usuarios_enviar_a = $mysqli->query($select_usuarios_enviar_a);

    $row_count = $sel_query_usuarios_enviar_a->num_rows;

    if ($row_count > 0) {
        while ($sel = $sel_query_usuarios_enviar_a->fetch_assoc()) {
            if (!is_null($sel['correo']) and !empty($sel['correo'])) {
                array_push($cc, $sel['correo']);
            }
        }
    }

    // FIN LISTAR USUARIOS DE LA TIENDAS ORIGEN Y DESTINO DEL PRESTAMO

    // INICIO LISTAR JEFE COMERCIALES DE LAS TIENDAS DEL PRESTAMO REALIZADO

    $select_usuarios_jc_enviar_a =
        "
            SELECT
                p.correo
            FROM tbl_locales l
                INNER JOIN tbl_zonas z
                ON l.zona_id = z.id
                INNER JOIN tbl_personal_apt p
                ON p.id = z.jop_id
            WHERE l.id IN ('" . $local_origen_id . "', '" . $local_destino_id . "') 
                AND p.correo IS NOT NULL AND p.estado = 1
        ";

    $sel_query_usuarios_jc_enviar_a = $mysqli->query($select_usuarios_jc_enviar_a);

    $row_count_jc = $sel_query_usuarios_jc_enviar_a->num_rows;

    if ($row_count_jc > 0) {
        while ($sel = $sel_query_usuarios_jc_enviar_a->fetch_assoc()) {
            if (!is_null($sel['correo']) and !empty($sel['correo'])) {
                array_push($cc, $sel['correo']);
            }
        }
    }

    // FIN LISTAR JEFE COMERCIALES DE LAS TIENDAS DEL PRESTAMO REALIZADO

    $bcc = [
        // SISTEMAS
    ];

    $request = [
        "subject" => $titulo_email,
        "body" => $body,
        "cc" => $cc,
        "bcc" => $bcc,
        "attach" => [
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
        $mail->From = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
        $mail->FromName = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

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
        $mail->Subject = $request["subject"];
        $mail->Body = $request["body"];
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->send();

        return $respuesta_email = true;

    } catch (Exception $e) {
        return $respuesta_email = $e;
    }
}


function get_query_string_selector($array_dt, $updated = false)
{
    global $mysqli;
    // -------
    $query_selector = "select * from tbl_caja_datos_fisicos where id= {$array_dt['id']}";
    $mysql_result = $mysqli->query($query_selector);
    $result_query_selector_after = array();
    while ($r = $mysql_result->fetch_assoc()) {
        $result_query_selector_after = $r;
    }
    // -----
    $query_update = "UPDATE tbl_caja_datos_fisicos SET valor = {$array_dt['new_valor']}  WHERE id = {$array_dt['id']}";
    $result_query_selector_before = array('not_updated' => '0');
    if ($updated) {
        $mysqli->query($query_update);

        $mysql_result = $mysqli->query($query_selector);
        while ($r = $mysql_result->fetch_assoc()) {
            $result_query_selector_before = $r;
        }
    }

    $selector = [
        'id_table' => $array_dt['id'],
        'query_selector' => $query_selector,
        'result_query_selector_after' => json_encode($result_query_selector_after),
        'query_update' => $query_update,
        'result_query_selector_before' => json_encode($result_query_selector_before)

    ];
    return $selector;
}

echo json_encode($return, true);
?>
