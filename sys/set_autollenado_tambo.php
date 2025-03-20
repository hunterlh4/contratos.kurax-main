<?php
$return = array();
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/sys/curl_helper.php';//ACAACA

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

//sys/get_caja_monto_inicial.php -  abrir_caja_monto_inicial_refresh
function abrir_caja_monto_inicial_refresh($get_data)
{
    global $mysqli;
    $sql_command = "SELECT 
				df.valor
				FROM tbl_caja_datos_fisicos df
				WHERE df.tipo_id = '11'
				AND df.caja_id = (SELECT c.id FROM tbl_caja c WHERE c.local_caja_id = '" . $get_data["local_caja_id"] . "' AND c.estado = '1' ORDER BY c.fecha_operacion DESC, c.fecha_cierre DESC LIMIT 1)";
    $sql_query = $mysqli->query($sql_command);
    $itm = $sql_query->fetch_assoc();

    if (!$itm) {
        $itm = $mysqli->query("SELECT IFNULL(valor,0) as valor FROM tbl_local_caja_config WHERE local_id = '" . $get_data["local_id"] . "' AND estado = '1' AND campo = 'monto_inicial'")->fetch_assoc();
    }

    $itm["valor"] = (isset($itm["valor"]) && $itm["valor"]) ? $itm["valor"] : number_format(0, 2);
    return $itm;
}

//sys/set_caja.php -  sec_caja_abrir_turno
function sec_caja_abrir_turno($data_abrir_turno)
{
    global $login;
    global $mysqli;
    $return = [];
    $save_data = $data_abrir_turno["sec_caja_abrir_turno"];

    ////////////
    if ($login) {
        $save_data["turno_data"]["usuario_id"] = $login["id"];
        $save_data["turno_data"]["fecha_apertura"] = date("Y-m-d H:i:s");
        $save_data["turno_data"]["estado"] = 0;
        $save_data["turno_data"]["at_unique_id"] = md5($save_data["turno_data"]["local_caja_id"] . $save_data["turno_data"]["turno_id"] . $save_data["turno_data"]["fecha_operacion"] . $save_data["turno_data"]["fecha_apertura"]);

        $nd = array();
        foreach ($save_data["turno_data"] as $i => $v) {
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
        $data_to_db = $nd;

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
                $has_turnos_command = "SELECT
									c.id
								FROM tbl_caja c
								WHERE c.local_caja_id = " . $data_to_db["local_caja_id"];
                $has_turnos = $mysqli->query($has_turnos_command)->num_rows;

                $is_local_red_at_command = "SELECT
									l.id
								FROM tbl_locales l
								WHERE l.id = " . $local_id .
                    " AND l.red_id in (1,9)";
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
            //echo $open_command;
            if ($open) {
                $return["open"] = $open;
            } else {
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
                            $dato["at_unique_id"] = md5("df_caja_id_" . $caja_id . "_tipo_id_" . $tipo_id);
                            $dato["caja_unique_id"] = $save_data["turno_data"]["at_unique_id"];
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
    return $return;
}

//sys/set_caja.php -  sec_caja_guardar
function sec_caja_guardar($data_caja_guardar)
{
    global $mysqli;
    global $login;
    $save_data = $data_caja_guardar["sec_caja_guardar"];

    $caja = $mysqli->query("SELECT at_unique_id, fecha_operacion, usuario_id FROM tbl_caja WHERE id = '" . $save_data["item_id"] . "'")->fetch_assoc();

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
					JOIN tbl_caja sqc ON sqc.fecha_operacion = DATE(tt.created_at) 
						-- AND sqc.usuario_id=ce.usuario_id 
						AND sqc.turno_id=ce.turno_id
						AND sqc.local_caja_id = ce.local_caja_id
					JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
					JOIN tbl_locales ssql ON ssql.id = sqlc.local_id AND ssql.cc_id = tt.cc_id
				WHERE
					DATE(tt.created_at) = '" . $fecha_operacion . "'
					AND sqc.id = '" . $id_caja_update . "'
			) a2 ON a2.tt_id = a1.id
			SET a1.turno_id = a2.turno_id_update
			";
        $mysqli->query($query_update_sw_turno);

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

        $existen_cajas_eliminadas = $mysqli->query("SELECT 
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
            //include '/var/www/html/sys/curl_helper.php';//ACAACA
            $response = curl_helper($url, $request, $headers);
        }

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

                if ($tipo_id == 20 && $save_data["estado"] == 1) { //kasnet
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
						ORDER BY k.created_at DESC
						LIMIT 1
					");
                    while ($r = $result->fetch_assoc()) $saldo_kasnet = $r;

                    if ($valid_local) {
                        $date = date("Y-m-d H:i:s");
                        $saldo_incremento = $dato["valor"];
                        $dato["valor"] += $saldo_kasnet["saldo_anterior"];
                        $mysqli->query("
							UPDATE
								tbl_saldo_kasnet
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
                        $query_caja_id = "SELECT caja_id FROM tbl_saldo_kasnet WHERE caja_id = '" . $save_data["item_id"] . "'";
                        $num_caja_id = $mysqli->query($query_caja_id)->num_rows;

                        if ($num_caja_id > 0) {
                            $mysqli->query("
								UPDATE tbl_saldo_kasnet
								SET saldo_incremento=" . $saldo_incremento . ",
									saldo_final=" . $dato["valor"] . ",
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
                if ($tipo_id == 25 && $save_data["estado"] == 1) { //disashop
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

    $update_caja_command = "UPDATE tbl_caja SET observaciones = '" . trim($mysqli->real_escape_string($save_data["observaciones"])) . "', estado = '" . $save_data["estado"] . "'";
    if ($save_data["estado"] == 1) {
        $update_caja_command .= " ,fecha_cierre = '" . date("Y-m-d H:i:s") . "'";
    }
    $update_caja_command .= " WHERE id = '" . $save_data["item_id"] . "'";
    $mysqli->query($update_caja_command);
    if ($mysqli->error) {
        print_r($mysqli->error);
        echo "\n";
        echo $update_caja_command;
        exit();
    }
    // print_r($save_data);


}

if (isset($_POST["autollenado_tambo"])) {
    extract($_POST);
    $date1 = $autollenado_tambo_fecha_inicio;
    $date2 = $autollenado_tambo_fecha_fin;
    $time = strtotime($date1);
    $last = date('Y-m-d', strtotime($date2));

    if ($_POST["autollenado_tambo_local_id"] == "") {
        $return["error"] = "Debe Seleccionar Local";
        $return["error_focus"] = "autollenado_tambo_local_id";
        die(json_encode($return));
    }
    if (!isset($_POST["autollenado_tambo_local_caja_id"]) || $_POST["autollenado_tambo_local_caja_id"] == "") {
        $return["error"] = "Debe Seleccionar Caja";
        $return["error_focus"] = "autollenado_tambo_local_caja_id";
        die(json_encode($return));
    }
    if ($time > strtotime($date2)) {
        $return["error"] = "Fecha Fin menor a Fecha Inicio";
        $return["error_focus"] = "input_text-autollenado_tambo_fecha_fin";
        die(json_encode($return));
    }

    $return["fecha_inicio"] = $date1;
    $return["fecha_fin"] = $date2;
    $dias = [];

    do {
        $dia = date('Y-m-d', $time);
        $dias[] = $dia;
        $time = strtotime('+1 day', $time);
    } while ($dia != $last);

    $cajas_abiertas = [];

    $billetero_values = [];
    $ffin = date("Y-m-d", strtotime($autollenado_tambo_fecha_fin . " +1 day"));
    $result = $mysqli->query("SELECT
			SUM(d.col_Amount) AS col_Amount, 
			d.col_TransactionDate AS fecha 
			FROM bc_apuestatotal.at_TerminalDocument AS d
			LEFT JOIN bc_apuestatotal.tbl_CashDesk AS cd ON  d.col_CashDeskId = cd.col_id
			LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp	ON  lp.proveedor_id = cd.col_id
			LEFT JOIN wwwapuestatotal_gestion.tbl_locales l ON l.id = lp.local_id 
			WHERE d.col_TransactionDate >= '$autollenado_tambo_fecha_inicio' 
			AND d.col_TransactionDate  < '$ffin'
			AND lp.local_id = $autollenado_tambo_local_id
			AND d.col_TypeId = 705
			GROUP by d.col_TransactionDate
	");
    while ($r = $result->fetch_assoc()) {
        $billetero_values[$r["fecha"]] = $r["col_Amount"];
    }
    $cajas_cerradas = [];
    foreach ($dias as $dia) {
        $data_c["local_id"] = $autollenado_tambo_local_id;
        $data_c["local_caja_id"] = $autollenado_tambo_local_caja_id;
        $autollenado_tambo_apertura = abrir_caja_monto_inicial_refresh($data_c)["valor"];

        /*sec_caja_abrir_turno*/
        $data_abrir_turno["sec_caja_abrir_turno"]["turno_data"]["fecha_operacion"] = $dia;
        $data_abrir_turno["sec_caja_abrir_turno"]["turno_data"]["local_caja_id"] = $autollenado_tambo_local_caja_id;
        $data_abrir_turno["sec_caja_abrir_turno"]["turno_data"]["turno_id"] = $autollenado_tambo_turno_id;
        $data_abrir_turno["sec_caja_abrir_turno"]["datos_fisicos"]["1"]["tipo_id"] = 1;
        $data_abrir_turno["sec_caja_abrir_turno"]["datos_fisicos"]["1"]["valor"] = $autollenado_tambo_apertura;
        $abrir_turno = sec_caja_abrir_turno($data_abrir_turno);
        //echo "<pre>abrir_turno: ";print_r($abrir_turno);echo "</pre>";

        if (isset($abrir_turno["caja_id"])) {
            $caja_id = $abrir_turno["caja_id"];
            //$billetero_value = 1100;
            $billetero_value = isset($billetero_values[$dia]) ? $billetero_values[$dia] : 0;

            $cajas_abiertas[] = array("id" => $caja_id
            , "fecha" => $dia
            , "turno" => $autollenado_tambo_turno_id
            , "local" => $autollenado_tambo_local_id
            , "billetero" => $billetero_value
            );
            /*if($dia == $last)
            {
                break;
            }*/

            //cerrar caja
            $item = $mysqli->query("SELECT
									c.id
									,l.cc_id
									,c.local_caja_id
									,l.id AS local_id
									,CONCAT('[',l.cc_id,']',' ',l.nombre) AS local_nombre
									,IF(u.personal_id,CONCAT('[',u.usuario,']',' ',IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,'')),u.usuario) AS usuario_nombre
									,lc.nombre AS caja_nombre
									,ct.nombre AS caja_tipo
									,IFNULL(c.turno_id,'-') AS turno
									,c.fecha_operacion
									,c.fecha_apertura
									,c.fecha_cierre
									,c.observaciones
									,c.estado
									,l.red_id
									,COALESCE(c.validar,0) AS validar
									,IF(c.estado=1,'Cerrado',IF(c.estado=2,'Re-Abierto','Abierto')) as estado_nombre
								FROM tbl_caja c
								LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
								LEFT JOIN tbl_caja_tipos ct ON (ct.id = lc.caja_tipo_id)
								LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
								LEFT JOIN tbl_usuarios u ON (u.id = c.usuario_id)
								LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
								WHERE c.id = '" . $caja_id . "'")->fetch_assoc();
            //datos del sistema
            $local_caja_detalle_tipos_command = "SELECT
												lcdt.id
												,lcdt.nombre
												,lcdt.descripcion
												,cdt.nombre AS tipo
												,cdt.id AS tipo_id
												,cdt.in
												,cdt.out
												,lcdt.estado
												,cd.ingreso AS ingreso
												,cd.salida AS salida
												,l.red_id
												,cdt.id AS cdt_id
												FROM tbl_local_caja_detalle_tipos lcdt
												LEFT JOIN tbl_caja_detalle_tipos cdt ON (cdt.id = lcdt.detalle_tipos_id)
												LEFT JOIN tbl_caja_detalle cd ON (cd.tipo_id = lcdt.id AND cd.caja_id = '" . $caja_id . "')
												LEFT JOIN tbl_locales l on l.id = lcdt.local_id
												WHERE lcdt.local_id = '" . $item["local_id"] . "'
												AND lcdt.estado = 1
												ORDER BY lcdt.orden ASC";
            $local_caja_detalle_tipos_query = $mysqli->query($local_caja_detalle_tipos_command);
            $total_ingreso = 0;
            $total_salida = 0;
            while ($lcdt = $local_caja_detalle_tipos_query->fetch_assoc()) {
                $data_caja_guardar2["sec_caja_guardar"]["detalles"][$lcdt["id"]]["tipo_id"] = $lcdt["id"];
                $data_caja_guardar2["sec_caja_guardar"]["detalles"][$lcdt["id"]]["ingreso"] = 0;

                if ($lcdt["tipo_id"] == 4)//billetero
                {
                    $data_caja_guardar2["sec_caja_guardar"]["detalles"][$lcdt["id"]]["tipo_id"] = $lcdt["id"];
                    $data_caja_guardar2["sec_caja_guardar"]["detalles"][$lcdt["id"]]["ingreso"] = $billetero_value;
                }
                if ($lcdt["red_id"] == 1 && $lcdt["cdt_id"] == 12) {
                    continue;
                }
                $ganancia = ($lcdt["ingreso"] - $lcdt["salida"]);
                $total_ingreso += $lcdt["ingreso"];
                $total_salida += $lcdt["salida"];
            }

            $item_id = $caja_id;
            //datos fisicos
            $local_caja_config = array();
            $local_caja_config["monto_inicial"] = array("nombre" => "Monto Inicial", "valor" => 0);
            $local_caja_config["valla_deposito"] = array("nombre" => "Valla Depósito", "valor" => 0);

            $local_caja_config_command = "SELECT campo, valor FROM tbl_local_caja_config WHERE local_id = '" . $item["local_id"] . "' AND estado = '1' GROUP BY campo";
            $local_caja_config_query = $mysqli->query($local_caja_config_command);
            while ($lcc = $local_caja_config_query->fetch_assoc()) {
                $local_caja_config[$lcc["campo"]]["valor"] = $lcc["valor"];
            }
            $prestamo_boveda_command = "SELECT
										SUM(IF(df.tipo_id=2,df.valor,0)) AS boveda_prestamo,
										SUM(IF(df.tipo_id=3,df.valor,0)) AS boveda_devolucion,
										SUM(IF(df.tipo_id=26,df.valor,0)) AS boveda_hermeticase,
										CAST(SUM(IF(df.tipo_id=2,df.valor,0)) - SUM(IF(df.tipo_id=3,df.valor,0)) - SUM(IF(df.tipo_id=26,df.valor,0))  AS DECIMAL(20,2)) AS boveda_deuda
									FROM tbl_caja c
									LEFT JOIN tbl_caja_datos_fisicos df ON (df.caja_id = c.id)
									WHERE c.local_caja_id = '" . $item["local_caja_id"] . "'
									AND c.estado = '1'";
            $prestamo_boveda_query = $mysqli->query($prestamo_boveda_command);
            $prestamo_boveda = $prestamo_boveda_query->fetch_assoc();

            $local_caja_config["deuda_boveda"]["valor"] = $prestamo_boveda["boveda_deuda"];

            $prestamo_slot_command = "SELECT
										SUM(IF(df.tipo_id=12,df.valor,0)) AS slot_prestamo,
										SUM(IF(df.tipo_id=13,df.valor,0)) AS slot_devolucion,
										CAST(SUM(IF(df.tipo_id=12,df.valor,0)) - SUM(IF(df.tipo_id=13,df.valor,0)) AS DECIMAL(20,2)) AS slot_deuda
									FROM tbl_caja c
									LEFT JOIN tbl_caja_datos_fisicos df ON (df.caja_id = c.id)
									WHERE c.local_caja_id = '" . $item["local_caja_id"] . "'
									AND c.estado = '1'";
            $prestamo_slot_query = $mysqli->query($prestamo_slot_command);

            $local_caja_config["deuda_slot"]["valor"] = 0;

            if ($prestamo_slot_query) {
                $prestamo_slot = $prestamo_slot_query->fetch_assoc();
                $local_caja_config["deuda_slot"]["valor"] = $prestamo_slot["slot_deuda"];
            }

            $saldo_resumen_kasnet = 0;
            $fecha_apertura = date("Y-m-d H:i:s");

            $result = $mysqli->query("
				SELECT fecha_apertura FROM tbl_caja_eliminados
				WHERE
					turno_id = '" . $item["turno"] . "'
					AND fecha_operacion = '" . $item["fecha_operacion"] . "'
					AND local_caja_id = '" . $item["local_caja_id"] . "'
					AND estado = 1
				ORDER BY fecha_apertura ASC
				LIMIT 1
			");

            if ($result) {
                while ($r = $result->fetch_assoc()) {
                    $fecha_apertura = $r["fecha_apertura"];
                }
            }

            $result = $mysqli->query("SELECT IFNULL(k.saldo_final, 0) AS saldo_final FROM tbl_caja c
				INNER JOIN tbl_local_cajas lc ON lc.id = c.local_caja_id
				LEFT JOIN tbl_saldo_kasnet k ON k.local_id = lc.local_id
				WHERE c.id = " . $item_id . "
				AND k.estado = 1
				AND created_at <= '{$fecha_apertura}'
				ORDER BY k.created_at DESC
				LIMIT 1");

            if ($result) {
                while ($r = $result->fetch_assoc()) {
                    $saldo_resumen_kasnet = $r["saldo_final"];
                }
            }

            $local_caja_config["saldo_kasnet"]["valor"] = $saldo_resumen_kasnet;

            /*lcc saldo_disashop*/
            $saldo_resumen_disashop = 0;
            $fecha_apertura = date("Y-m-d H:i:s");

            $result = $mysqli->query("
				SELECT fecha_apertura FROM tbl_caja_eliminado
				WHERE
					turno_id = '" . $item["turno"] . "'
					AND fecha_operacion = '" . $item["fecha_operacion"] . "'
					AND local_caja_id = '" . $item["local_caja_id"] . "'
					AND estado = 1
				ORDER BY fecha_apertura ASC
				LIMIT 1
			");

            if ($result) {
                while ($r = $result->fetch_assoc()) {
                    $fecha_apertura = $r["fecha_apertura"];
                }
            }

            $result = $mysqli->query("SELECT IFNULL(k.saldo_final, 0) AS saldo_final FROM tbl_caja c
				INNER JOIN tbl_local_cajas lc ON lc.id = c.local_caja_id
				LEFT JOIN tbl_saldo_disashop k ON k.local_id = lc.local_id
				WHERE c.id = " . $item_id . "
				AND k.estado = 1
				AND created_at <= '{$fecha_apertura}'
				ORDER BY k.created_at DESC
				LIMIT 1");

            if ($result) {
                while ($r = $result->fetch_assoc()) {
                    $saldo_resumen_disashop = $r["saldo_final"];
                }
            }

            $local_caja_config["saldo_disashop"]["valor"] = $saldo_resumen_disashop;
            /*fin lcc saldo_disashop*/
            $where_local = "";
            $deuda_slot_flag = 0;
            if ($item["red_id"] == 1 || $item["red_id"] == 9) {
                $where_local = "WHERE dft.columna != 'deposito_cliente_directo'";
                $deuda_slot_flag = 1;
            }

            $datos_fisicos_command = "SELECT
										dft.id
										,dft.nombre
										,dft.descripcion
										,dft.columna
										,dft.operador
										,dft.mostrar
										,df.valor AS valor
									FROM tbl_caja_datos_fisicos_tipos dft
									LEFT JOIN tbl_caja_datos_fisicos df ON (df.caja_id = '" . $caja_id . "' AND df.tipo_id = dft.id)
									$where_local
									ORDER BY dft.id ASC";
            $datos_fisicos_query = $mysqli->query($datos_fisicos_command);
            $datos_fisicos = array();
            $total_datos_fisicos = 0;
            $data_caja_guardar = null;

            $data_caja_guardar2["sec_caja_guardar"]["item_id"] = $caja_id;
            $data_caja_guardar2["sec_caja_guardar"]["estado"] = 1;
            $data_caja_guardar2["sec_caja_guardar"]["observaciones"] = "";

            if ($datos_fisicos_query) {
                while ($df = $datos_fisicos_query->fetch_assoc()) {
                    $df_val = $df["valor"];
                    $estado = (int) $item["estado"];
                    if ($df["operador"] === "h" &&  $estado !== 1) {
                        $df_val = $local_caja_config[$df["columna"]]["valor"];
                    }
                    $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][$df["id"]]["tipo_id"] = $df["id"];
                    $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][$df["id"]]["valor"] = $df_val;
                }
            }

            $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][5]["tipo_id"] = 5;
            $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][5]["valor"] = $billetero_value;
            $cierre_sistema = $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][1]["valor"] + $billetero_value;
            $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][10]["tipo_id"] = 10;
            $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][10]["valor"] = $cierre_sistema;

            $cierre_efectivo = $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][5]["valor"] + $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][1]["valor"];//billetero + apertura efec
            $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][11]["tipo_id"] = 11;//cierre efectivo
            $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][11]["valor"] = $cierre_efectivo;

            $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][20]["valor"] = 0;
            $data_caja_guardar2["sec_caja_guardar"]["datos_fisicos"][25]["valor"] = 0;
            //echo "<pre>data_caja_guardar2: ";print_r($data_caja_guardar2);echo "</pre>";die();
            // echo "<pre>data_caja_guardar2: ";print_r($data_caja_guardar2);echo "</pre>";die();

            sec_caja_guardar($data_caja_guardar2);
            //echo "CERRADA caja_id = ".$caja_id . " - - - - -";
            $cajas_cerradas[] = array(
                "id" => $caja_id
            , "fecha" => $dia
            , "turno" => $autollenado_tambo_turno_id
            , "local" => $autollenado_tambo_local_id
            , "billetero" => $billetero_value
            );
        } else {
            $return = $abrir_turno;// $return["open"] = $open;
        }

    }
    $return["cajas_abiertas"] = $cajas_abiertas;
    $return["cajas_cerradas"] = $cajas_cerradas;
    $return["dias"] = $dias;
    $mensaje = "Se procesaron " . count($dias) . " días:\n<br>";
    foreach ($cajas_abiertas as $iii => $r) {
        $iii++;
        $mensaje .= $iii . ".- " . $r["id"] . " " . $r["fecha"] . " Turno " . $r["turno"] . "\n<br>";
    }
    $return["mensaje"] = $mensaje;
    print_r(json_encode($return));
}

?>