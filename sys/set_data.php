<?php
$return = [];
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("set_clientes.php");
include("sys_login.php");
// include("../api/where_validar_usuario_permiso_botones.php");
include("../api/validar_permiso_boton.php");
require("/var/www/html/sys/globalFunctions/templates/crud.php");
include("globalFunctions/generalInfo/personal.php");

if (isset($_POST["opt"])) {
	$return["_POST"] = $_POST;
	extract($_POST);
	if ($opt == "check_local_paid") {
		check_local_paid($data);
	}
	if ($opt == "add_item") {
		add_item($data);
	}
	if ($opt == "switch_data") {
		if ($data["table"] == "tbl_locales_web_config" && $data["col"] == "is_open" && $data["val"] == 0) {
			include("sys_configuracion_web.php");
			$data["mensaje"] = enviar_correo_local_cerrado($data["id"]);
		}
		$return["switch_data"] = switch_data($data);
	}
	if ($opt == "sorteo_tienda_nueva_inicio") {
		sorteo_tienda_nueva_inicio($data);
	}
	if ($opt == "save_item") {

		$return["permisos"] = true;

		if ($data["id"] == "new") {
			//echo '---nuevo---';
			add_item($data);
		} else {
			//echo '---no_nuevo---';
			save_item($data);
		}
		// }
	}
	if ($opt == "del_item") {
		del_item($data);
	}
	if ($opt == "sort_list") {
		sort_list($data);
	}
	if ($opt == "save_adm_inputs") {
	}
	if ($opt == "add_cliente_form") {
		print_r($_POST);
	}

	if ($opt == "rec_reprocess") {
		rec_reprocess($data);
	}
	if ($opt == "rec_gen_liq") {
		rec_gen_liq($data);
	}

	if ($opt == "time_test") {
		sleep(1);
		print_r($_POST);
	}
	if ($opt == "import_from_bc") {
		import_from_bc($_POST);
	}
	if ($opt == "liq_pro_action") {
		//print_r($_POST);
		include("sys_recaudacion.php");
		liq_pro_action($_POST["data"]);
	}
	if ($opt == "add_pago_manual") {
		include("sys_transacciones.php");
		include("sys_recaudacion.php");
		add_pago_manual($_POST["data"]);
	}
	if ($opt == "del_pago_manual") {
		include("sys_transacciones.php");
		include("sys_recaudacion.php");
		del_pago_manual($_POST["data"]);
	}
	if ($opt == "recaudacion_add_trans_terminal") {
		include("sys_transacciones.php");
		include("sys_recaudacion.php");
		recaudacion_add_trans_terminal($_POST["data"]);
	}
	if ($opt == "recaudacion_add_trans_bancaria") {
		include("sys_recaudacion.php");
		recaudacion_add_trans_bancaria($_POST["data"]);
	}
	if ($opt == "recaudacion_div_trans_bancaria") {
		include("sys_recaudacion.php");
		recaudacion_div_trans_bancaria($_POST["data"]);
	}
	if ($opt == "recaudacion_hide_trans_bancaria") {
		include("sys_recaudacion.php");
		recaudacion_hide_trans_bancaria($_POST["data"]);
	}
	if ($opt == "recaudacion_process_trans_bancaria") {
		include("sys_recaudacion.php");
		recaudacion_process_trans_bancaria($_POST["data"]);
	}
	if ($opt == "recaudacion_frac_add") {
		include("sys_recaudacion.php");
		recaudacion_frac_add($_POST["data"]);
	}
	if ($opt == "recaudacion_transbanc_save") {
		include("sys_recaudacion.php");
		recaudacion_transbanc_save($_POST["data"]);
	}
	if ($opt == "sec_rtb_assig_save") {
		include("sys_recaudacion.php");
		sec_rtb_assig_save($_POST["data"]);
	}
	if ($opt == "marketing_pizarra_add") {
		include("sys_marketing.php");
		marketing_pizarra_add($_POST["data"]);
	}
	if ($opt == "sec_permisos_copiar_permisos_usuarios_locales") {
		include("sys_usuarios.php");
		usuarios_permisos_copiar_permisos_usuarios_local_x_red($_POST);
	}
	if ($opt == "sec_permisos_copiar_permisos_usuarios_menus") {
		include("sys_usuarios.php");
		usuarios_permisos_copiar_permisos_usuarios_menus_sub_menu($_POST);
	}
}

function check_local_paid($data)
{
	global $mysqli;
	global $return;

	$mysqli->query("
		UPDATE tbl_repositorio_tickets_goldenrace gr 
		SET gr.local_id = (SELECT 
				local_id
			FROM
				tbl_local_proveedor_id
			WHERE
				estado = 1 
				AND servicio_id = 3
				AND canal_de_venta_id = 21
				AND proveedor_id = gr.unit_id
			LIMIT 1)
		WHERE
			gr.local_id IS NULL;
	");

	$proveedores = [];
	$result = $mysqli->query("SELECT proveedor_id, local_id, canal_de_venta_id, servicio_id FROM tbl_local_proveedor_id WHERE estado = 1");
	while ($r = $result->fetch_assoc()) $proveedores[$r["proveedor_id"]][] = $r;

	$repos = [];
	$result = $mysqli->query("SELECT at_unique_id, cashdesk_id, servicio_id FROM tbl_transacciones_repositorio WHERE local_id IS NULL");
	while ($r = $result->fetch_assoc()) $repos[] = $r;

	$mysqli->query("START TRANSACTION");
	foreach ($repos as $repo) {
		if (!isset($proveedores[$repo["cashdesk_id"]])) continue;

		foreach ($proveedores[$repo["cashdesk_id"]] as $proveedor) {
			if ($proveedor["servicio_id"] == $repo["servicio_id"]) {
				$mysqli->query("UPDATE tbl_transacciones_repositorio 
					SET local_id = " . $proveedor["local_id"] . ", canal_de_venta_id = " . $proveedor["canal_de_venta_id"] . "
					WHERE at_unique_id = '" . $repo["at_unique_id"] . "'");
				$mysqli->query("UPDATE tbl_transacciones_detalle 
					SET local_id = " . $proveedor["local_id"] . ", canal_de_venta_id = " . $proveedor["canal_de_venta_id"] . "
					WHERE at_unique_id = '" . $repo["at_unique_id"] . "'");

				break;
			}
		}
	}
	$mysqli->query("COMMIT");

	$repos = [];
	$result = $mysqli->query("SELECT at_unique_id,_paiddate_,paid_cash_desk_name, servicio_id FROM tbl_transacciones_repositorio WHERE at_unique_id IN 
		(SELECT at_unique_id FROM tbl_transacciones_detalle WHERE paid_day IS NOT NULL AND paid_local_id IS NULL AND state != 'ingreso_manual')");
	while ($r = $result->fetch_assoc()) $repos[] = $r;

	$mysqli->query("START TRANSACTION");
	foreach ($repos as $repo) {
		if (!isset($proveedores[$repo["paid_cash_desk_name"]])) continue;

		foreach ($proveedores[$repo["paid_cash_desk_name"]] as $proveedor) {
			if ($proveedor["servicio_id"] == $repo["servicio_id"]) {
				$mysqli->query("UPDATE tbl_transacciones_detalle 
					SET paid_canal_de_venta_id = '" . $proveedor["canal_de_venta_id"] . "', paid_local_id = '" . $proveedor["local_id"] . "', paid_day = '" . $repo["_paiddate_"] . "'
					WHERE at_unique_id = '" . $repo["at_unique_id"] . "'");
				break;
			}
		}
	}
	$mysqli->query("COMMIT");

	$locales_kasnet = [];
	$result = $mysqli->query("
		SELECT
			lp.local_id,
			lp.proveedor_id
		FROM tbl_local_proveedor_id lp
		WHERE
			lp.servicio_id = 7
			AND lp.canal_de_venta_id = 28
			AND lp.proveedor_id IN (
				SELECT 
					kv.terminal
				FROM tbl_repositorio_kasnet_ventas kv
				WHERE kv.local_id IS NULL
			)
	");
	while ($r = $result->fetch_assoc()) $locales_kasnet[$r["proveedor_id"]] = $r["local_id"];
	$mysqli->query("START TRANSACTION");
	foreach ($locales_kasnet as $terminal => $local) {
		$mysqli->query("
			UPDATE tbl_repositorio_kasnet_ventas 
			SET 
				local_id={$local} 
			WHERE 
				local_id IS NULL 
				AND terminal={$terminal}
		");
	}
	$mysqli->query("COMMIT");
}

function add_item($data = false)
{
	global $mysqli;
	global $return;
	global $login;

	if ($data['table'] == "tbl_locales" && $data) {
		if ($local_exists = $mysqli->query("SELECT id FROM tbl_locales WHERE nombre='" . $data["values"]["nombre"] . "'")->fetch_assoc()) {
			echo "El local ya existe.";
			exit();
		}
		if (isset($data["values"]["cc_id"])) {
			if (strlen($data["values"]["cc_id"]) > 0) {
				if ($mysqli->query("SELECT cc_id FROM tbl_locales WHERE nombre='" . $data["values"]["cc_id"] . "'")->fetch_assoc()) {
					echo "El cc ya existe.";
					exit();
				}
			}
		}
		if (!isset($data["values"]["red_id"]) ||  $data["values"]["red_id"] == 0) {
			echo "Seleccionar Red";
			exit();
		}
		if (!isset($data["values"]["razon_social_id"]) ||  $data["values"]["razon_social_id"] == 0) {
			echo "Seleccionar Razón Social";
			exit();
		}
		if (!isset($data["values"]["zona_id"]) ||  $data["values"]["zona_id"] == 0) {
			echo "Seleccionar Zona";
			exit();
		}
		if (!isset($data["values"]["cc_id"]) ||  $data["values"]["cc_id"] == 0) {
			echo "Ingrese el centro de costos";
			exit();
		}
		if ($data["values"]["red_id"] == 5) {
			$data["values"]["operativo"] = 1;
			//$data["values"]["fecha_inicio_operacion"] = date("Y-m-d H:i:s");
			$data["values"]["fecha_registro"] = date("Y-m-d H:i:s");
		}
	}
	if ($data['table'] == "tbl_personal_apt" && $data) {

		$data["values"]["created_at"] = date("Y-m-d H:i:s");
		$data["values"]["user_created_id"] = $login ? $login['id'] : null;
		//validar DNI de personal
		$dni_validado = validarDNIPersonal($data['values']['dni']);

		if ($dni_validado['flag_dni'] === 'existe') {
			$estado = 'x';
			switch ($dni_validado['personal']['estado']) {
				case "1":
					$estado = 'activo';
					break;
				case "0":
					$estado = 'inactivo';
					break;
				default:
					break;
			}
			echo "DNI ya registrado, " . $dni_validado['personal']['nombre_completo'] . " Estado: " . $estado . ".";
			exit();
		}
	}


	// $new_values = array();
	//ubigeo_
	// $ubigeo_id = "";
	// if(array_key_exists("ubigeo_cod_depa", $data["values"])){
	// 	$ubigeo_id .= $data["values"]["ubigeo_cod_depa"];
	// }
	// if(array_key_exists("ubigeo_cod_prov", $data["values"])){
	// 	$ubigeo_id .= $data["values"]["ubigeo_cod_prov"];		
	// }
	// if(array_key_exists("ubigeo_cod_dist", $data["values"])){
	// 	$ubigeo_id .= $data["values"]["ubigeo_cod_dist"];		
	// }
	// if($ubigeo_id){
	// 	$new_values["ubigeo_id"]=$ubigeo_id;
	// }
	// foreach ($data["values"] as $key => $value) {
	// 	if(!in_array($key, array("ubigeo_cod_depa","ubigeo_cod_prov","ubigeo_cod_dist"))){	
	// 		if($value){
	// 			$new_values[$key]=$value;
	// 		}
	// 	}
	// }
	//	/ubigeo_

	//print_r($data);
	$sql_insert = "INSERT INTO ";
	$sql_insert .= $data["table"];
	$sql_insert .= " (";
	$sql_insert .= "estado";
	//$sql_insert.= implode(",", array_keys($new_values));	
	$sql_insert .= ")";
	$sql_insert .= " VALUES ";
	$sql_insert .= " (";
	$sql_insert .= "'1'";
	$sql_insert .= ")";
	//echo ' ** '.$sql_insert;
	//$sql_insert.= "('";

	//foreach ($new_values as $key => $value) {
	//	$new_values[$key]=$mysqli->real_escape_string($value);
	//}

	//$sql_insert.= implode("','", $new_values);	
	//$sql_insert.= "')";
	//$sql_insert.= "";
	$return["sql_insert"] = $sql_insert;
	$mysqli->query($sql_insert);
	if ($mysqli->error) {
		$return["ERROR_MYSQL"] = $mysqli->error;
		print_r($mysqli->error);
		echo $sql_insert;
		exit();
	}
	$inserted = $mysqli->insert_id;
	$return["item_id"] = $mysqli->insert_id;
	$return["data"] = $data;
	$data["id"] = $return["item_id"];

	if ($data['table'] == "tbl_locales") {
		$mysqli->query("UPDATE tbl_locales  SET fecha_registro = now() WHERE id={$inserted}");
		$exists_local_usuario = $mysqli->query("SELECT * FROM tbl_usuarios_locales WHERE usuario_id='" . $data['usr'] . "'")->fetch_assoc();
		if ($exists_local_usuario) {
			$set_query = "INSERT INTO tbl_usuarios_locales (usuario_id,local_id,estado) VALUES ('" . $data['usr'] . "','" . $inserted . "','1')";
			$mysqli->query($set_query);
		}
		$mysqli->query("INSERT INTO tbl_locales_horarios (local_id, horario_id, started_at) VALUES(" . $inserted . ", 1, '2014-01-01')");
		$mysqli->query("INSERT INTO tbl_local_config (local_id,config_id,config_param,created_at,updated_at) 
		VALUES('" . $inserted . "','alerta_gr_turnover','1',NOW(),NOW()),
		('" . $inserted . "','alerta_terminal_deposit','1',NOW(),NOW()),
		('" . $inserted . "','alerta_deposito_web','1',NOW(),NOW()),
		('" . $inserted . "','alerta_simulcast_red_at','1',NOW(),NOW()),
		('" . $inserted . "','alerta_simulcast_agente','1',NOW(),NOW()),
		('" . $inserted . "','alerta_betshop_retail','1',NOW(),NOW()),
		('" . $inserted . "','alert_caja_web_deposit','1',NOW(),NOW()),		
		('" . $inserted . "','alerta_betshop','1',NOW(),NOW())
		");


		$data["local_new"] = true;
	}
	save_item($data);
}
function del_item($data)
{
	global $mysqli;
	$sql_delete = "DELETE FROM ";
	$sql_delete .= $data["table"];
	$sql_delete .= " WHERE ";
	$sql_delete .= " id ";
	$sql_delete .= " = ";
	$sql_delete .= "'";
	$sql_delete .= $data["id"];
	$sql_delete .= "'";
	//echo $sql_delete;
	$return["sql_delete"][] = $sql_delete;
	$mysqli->query($sql_delete);
	$return["data"] = $data;
}
function save_item($data)
{
	global $mysqli;
	global $return;
	global $login;
	//print_r($data);

	$fecha_update = date("Y-m-d H:i:s");

	if (isset($data["values"]["cc_id"])) {
		if (strlen($data["values"]["cc_id"]) > 0) {
			if ($data['table'] == "tbl_locales" && $data) {
				if ($mysqli->query("SELECT cc_id FROM tbl_locales WHERE cc_id='" . $data["values"]["cc_id"] . "' AND id != '$data[id]'")->num_rows >= 1) {
					echo "El cc ya existe.";
					exit();
				}
			}
		}
	}

	if (isset($data["values"]["nombre"])) {
		if (strlen($data["values"]["nombre"]) > 0) {
			if ($data['table'] == "tbl_locales"  && !isset($data["local_new"])) {
				if ($data["values"]["red_id"] == 5) {
					$local_data = $mysqli->query("SELECT nombre FROM tbl_locales WHERE id = '$data[id]'")->fetch_assoc();
					if ($local_data["nombre"] != $data["values"]["nombre"]) {
						if (!($login["id"] == 11 || $login["id"] == 408 || $login["id"] == 199)) {
							echo "El nombre no puede ser cambiado! Comunicarse con el area de soporte en caso desee editarlo.";
							exit();
						}
					}
				}
			}
		}
	}


	if (isset($data["values"]["latitud"])) {
		if (strlen($data["values"]["latitud"]) > 0) {
			if ($data['table'] == "tbl_locales" && $data) {
				$local_id = $data["id"];
				$select_contrato_id = "SELECT contrato_id FROM tbl_locales WHERE id = $local_id";
				$sel_query = $mysqli->query($select_contrato_id);
				$row_count = $sel_query->num_rows;

				if ($row_count > 0) {
					$sel = $sel_query->fetch_assoc();
					$contrato_id = trim($sel['contrato_id']);

					if (!empty($contrato_id)) {
						$latitud = $data['values']['latitud'];

						$query_update = "UPDATE cont_inmueble SET latitud = '$latitud' WHERE contrato_id = $contrato_id AND status = 1";
						$mysqli->query($query_update);
					}
				}
			}
		}
	}

	if (isset($data["values"]["longitud"])) {
		if (strlen($data["values"]["longitud"]) > 0) {
			if ($data['table'] == "tbl_locales" && $data) {
				$local_id = $data["id"];
				$select_contrato_id = "SELECT contrato_id FROM tbl_locales WHERE id = $local_id";
				$sel_query = $mysqli->query($select_contrato_id);
				$row_count = $sel_query->num_rows;

				if ($row_count > 0) {
					$sel = $sel_query->fetch_assoc();
					$contrato_id = trim($sel['contrato_id']);

					if (!empty($contrato_id)) {
						$longitud = $data['values']['longitud'];

						$query_update = "UPDATE cont_inmueble SET longitud = '$longitud' WHERE contrato_id = $contrato_id AND status = 1";
						$mysqli->query($query_update);
					}
				}
			}
		}
	}

	if (isset($data["values"]["cc_id"]) &&  $data["values"]["cc_id"] == 0) {
		echo "Ingrese el centro de costos";
		exit();
	}
	if ($data['table'] == "tbl_locales"  && !isset($data["local_new"])) {
		if (!isset($data["values"]["zona_id"]) ||  $data["values"]["zona_id"] == 0) {
			echo "Seleccionar Zona";
			exit();
		}
	}
	if ($data['table'] == "tbl_locales"  && !isset($data["local_new"])) {
		if (!isset($data["values"]["razon_social_id"]) ||  $data["values"]["razon_social_id"] == 0) {
			echo "Seleccionar Razon Social";
			exit();
		}
	}
	/*
	//if($data['table']=="tbl_locales"  && !isset($data["local_new"]))
	//{
		if(!isset($data["internet_proveedor_id"]) ||  $data["internet_proveedor_id"] == 0){
		//if(!isset($data["internet_proveedor_id"])== 0){
			echo "Seleccionar proveedor de internet";
			exit();
		}
	//}

	if(!isset($data["internet_tipo_id"]) ||  $data["internet_tipo_id"] == 0){
			echo "Seleccionar tipo de internet";
			exit();
		}
*/
	$new_values = [];
	foreach ($data["values"] as $key => $value) {
		if (!in_array($key, ["ubigeo_cod_depa", "ubigeo_cod_prov", "ubigeo_cod_dist"])) {
			$new_values[$key] = $value;
		}
	}
	//ubigeo_
	$ubigeo_id = "";

	if (isset($data['values']) && is_array($data['values'])) {
		if (array_key_exists("ubigeo_cod_depa", $data["values"])) {
			$ubigeo_id .= $data["values"]["ubigeo_cod_depa"];
		}
		if (array_key_exists("ubigeo_cod_prov", $data["values"])) {
			$ubigeo_id .= $data["values"]["ubigeo_cod_prov"];
		}
		if (array_key_exists("ubigeo_cod_dist", $data["values"])) {
			$ubigeo_id .= $data["values"]["ubigeo_cod_dist"];
		}
		if ($ubigeo_id) {
			$new_values["ubigeo_id"] = $ubigeo_id;
		}

		if (array_key_exists("zona_id", $data["values"])) setLocalZona($data['id'], $data['values']['zona_id']);
	}

	if ($data["table"] == "tbl_usuarios") {
		$new_values["password_md5"] = md5($new_values["password"]);
		$fecha_update = date("Y-m-d H:i:s");

		$update_campos_query = "
				UPDATE tbl_usuarios
					SET user_updated_id = '" . $login['id'] . "',
						updated_at = '" . $fecha_update . "'
					WHERE id = '" . $data["id"] . "'
				";
		$mysqli->query($update_campos_query);
	}

	if ($data["table"] == 'tbl_personal_apt') {
		$fecha_update = date("Y-m-d H:i:s");

		if (array_key_exists("values", $data)) {

			$personal_cambio_empresa = "0";
			$personal_query = 'select * from tbl_personal_apt where id = ' . $data["id"];
			$personal = $mysqli->query($personal_query)->fetch_assoc();
			foreach ($new_values as $key => $value) {
				//compara si el valor anterior no es 
				if ($personal[$key] != $value && $value) {
					$insert_personal_auditoria = insertTable('tbl_personal_auditoria', [
						'personal_id' => $data["id"],
						'nombre_personal' => $personal["nombre"] . ' ' . $personal["apellido_paterno"] . ' ' . $personal["apellido_materno"],
						'campo' => $key,
						'valor_anterior' => $personal[$key],
						'valor' => $value,
						'campo_updated_by' => $login['id'],
						'ip' => $login['login_ip'],
						'created_at' => date('Y-m-d H:i:s'),
						'user_created_id' => $login['id']
					]);

					if (!empty($insert_personal_auditoria['mysqli_error'])) {
						print_r($insert_personal_auditoria);
						exit();
					}

					if ($key == 'razon_social_id') {
						$personal_cambio_empresa = "1";
					}
				} else if ($personal[$key] != $value && $key == 'razon_social_id') {
					$personal_cambio_empresa = "1";
				} else {
					// print_r($personal[$key].' : es igual a '. $value);
					// exit();
				}
			}

			// Actualizamos el cargo
			if ($data['values']['area_id'] == 21 && $data['values']['cargo_id'] == 16 && $data['values']['zona_id'] != 0) {
				$update_jefe_zona_query = "
					UPDATE tbl_zonas
					SET jop_id = {$data["id"]}
					WHERE id = {$data['values']["zona_id"]}
				";
				$mysqli->query($update_jefe_zona_query);

				// actualizamos si es que es jefe de zona de otra zona
				$update_jefe_zona_query = "
					UPDATE tbl_zonas
					SET jop_id = NULL
					WHERE jop_id = {$data["id"]} AND id != {$data['values']["zona_id"]}
				";
				$mysqli->query($update_jefe_zona_query);
			}

			// si el personal ya no es JEFE o no pertenece a OPERACIONES
			$query_search_zona_jefe = "SELECT id FROM tbl_zonas WHERE jop_id = '{$data["id"]}'";
			$search_zona_jefe = $mysqli->query($query_search_zona_jefe);

			if (($search_zona_jefe->num_rows > 0 && ($data['values']['area_id'] != 21 || $data['values']['cargo_id'] != 16)) || $personal_cambio_empresa == "1") {
				while ($row_zona = $search_zona_jefe->fetch_assoc()) {
					$query_quitar_jefe = "
						UPDATE tbl_zonas
						SET jop_id = NULL
						WHERE id = {$row_zona['id']}
					";
					$mysqli->query($query_quitar_jefe);
				}
			}

			$update_campos_query = "
				UPDATE tbl_personal_apt
					SET user_updated_id = '" . $login['id'] . "',
						updated_at = '" . $fecha_update . "'
					WHERE id = '" . $data["id"] . "'
				";
			$mysqli->query($update_campos_query);
		}
	}
	//-------------------------------------------------------------------------------------------------------------------//
	//Nueva Regla de Negocio, cuando un local cambia de RED, se eliminan todos los usuarios que tenga
	$bindicador_cambio_red = false;
	if ($data["table"] == "tbl_locales") {
		$red_nueva = $data['values']['red_id'];
		$bindicador_cambio_red = fxCambioRedLocal($data['id'], $red_nueva);
	}

	//-------------------------------------------------------------------------------------------------------------------//
	$sql_update = "UPDATE ";
	$sql_update .= $data["table"];
	$sql_update .= " SET ";
	if (array_key_exists("values", $data)) {
		$vc_n = 0;
		foreach ($new_values as $key => $value) {
			if ($vc_n > 0) {
				$sql_update .= ", ";
			}
			$sql_update .= "" . $key . "";
			$sql_update .= " = ";
			if ($value) {
				$sql_update .= "'" . $mysqli->real_escape_string($value) . "'";
			} else {
				$sql_update .= "NULL";
			}
			$vc_n++;
		}
	}

	$sql_update .= " WHERE ";
	$sql_update .= " id ";
	$sql_update .= " = ";
	$sql_update .= "'";
	$sql_update .= $data["id"];
	$sql_update .= "'";
	$return["sql_update"][] = $sql_update;
	$return["item_id"] = $data["id"];
	$mysqli->query($sql_update);

	if ($mysqli->error) {
		$return["ERROR_MYSQL"] = $mysqli->error;
		print_r($mysqli->error);
		echo $sql_update;
		exit();
	}

	//-------------------------------------------------------------------------------------------------------------------//
	if ($data["table"] == "tbl_locales" && $bindicador_cambio_red) {
		//se trata de un cambio de red, se quita a todos los usuarios
		$bindicador_operacion = fxQuitarUsuariosLocal($data['id']);
	}
	//-------------------------------------------------------------------------------------------------------------------//
	$return["data"] = $data;

	if (array_key_exists("extra", $data)) {
		foreach ($data["extra"] as $key => $extra) {
			if (array_key_exists("type", $extra)) {
				if ($extra["type"] == "usuario_permiso") {
					//print_r($extra);
					if ($extra["checked"]) {
						$exists_permiso = $mysqli->query("SELECT id FROM tbl_usuario_permisos WHERE usuario_id = '" . $data["id"] . "' AND menu_id = '" . $extra["menu"] . "' AND boton = '" . $extra["boton"] . "'")->fetch_assoc();
						if ($exists_permiso) {
							$sql_update_permiso = "UPDATE tbl_usuario_permisos SET estado = '1' WHERE usuario_id = '" . $data["id"] . "' AND menu_id = '" . $extra["menu"] . "' AND boton = '" . $extra["boton"] . "'";
							$mysqli->query($sql_update_permiso);
							$return["sql_update"][] = $sql_update_permiso;
						} else {
							$sql_insert_permiso = "INSERT INTO tbl_usuario_permisos (usuario_id,menu_id,boton,estado) VALUES ('" . $data["id"] . "','" . $extra["menu"] . "','" . $extra["boton"] . "','1')";
							$mysqli->query($sql_insert_permiso);
							$return["sql_insert"][] = $sql_insert_permiso;
						}
					} else {
						$sql_delete_permiso = "DELETE FROM tbl_usuario_permisos WHERE usuario_id = '" . $data["id"] . "' AND menu_id = '" . $extra["menu"] . "' AND boton = '" . $extra["boton"] . "'";
						$mysqli->query($sql_delete_permiso);
						$return["sql_delete"][] = $sql_delete_permiso;
					}
				}
			}
			if (array_key_exists("extra", $extra)) {
				if ($extra["extra"] == "servicio") {
					$exists = $mysqli->query("SELECT id,nombre FROM tbl_local_proveedor_id WHERE local_id = '" . $data["id"] . "' AND servicio_id = '" . $extra["servicio_id"] . "'")->fetch_assoc();

					if ($exists) {
						if ($extra["val"] == $exists["nombre"]) {
							$sql_update_servicio = "UPDATE tbl_local_proveedor_id SET estado = '1' WHERE local_id = '" . $data["id"] . "' AND servicio_id = '" . $extra["servicio_id"] . "'";
							$return["sql_update"][] = $sql_update_servicio;
							$mysqli->query($sql_update_servicio);
						} elseif ($extra["val"] == "") {
							$sql_update_servicio = "UPDATE tbl_local_proveedor_id SET estado = '0' WHERE local_id = '" . $data["id"] . "' AND servicio_id = '" . $extra["servicio_id"] . "'";
							$return["sql_update"][] = $sql_update_servicio;
							$mysqli->query($sql_update_servicio);
						} else {
							$sql_update_servicio = "UPDATE tbl_local_proveedor_id SET nombre = '" . $extra["val"] . "', estado = '1' WHERE local_id = '" . $data["id"] . "' AND servicio_id = '" . $extra["servicio_id"] . "'";
							$return["sql_update"][] = $sql_update_servicio;
							$mysqli->query($sql_update_servicio);
						}
					} else {
						if ($extra["val"]) {
							$sql_insert_servicio = "INSERT INTO tbl_local_proveedor_id (local_id,servicio_id,nombre,estado) VALUES ('" . $data["id"] . "','" . $extra["servicio_id"] . "','" . $extra["val"] . "','1')";
							$return["sql_insert"][] = $sql_insert_servicio;
							$mysqli->query($sql_insert_servicio);
						}
					}
				}
			}
			if (array_key_exists("formula_id", $extra)) {
				$formula = $mysqli->query("SELECT ff.id, 
					ff.participante_id, 
					ff.operador_id, 
					ff.servicio_id, 
					ff.moneda_id,
					ff.fuente_id,
					ff.sobre_id,
					ff.tipo_id
					FROM tbl_facturacion_formulas ff
					WHERE ff.id = '" . $extra["formula_id"] . "'")->fetch_assoc();
				if ($extra["formula_id"] == 17) {
					$fake_detalle = [];
					$fake_detalle["contrato_id"] = $data["id"];
					$fake_detalle["producto_id"] = $extra["producto_id"];
					$fake_detalle["participante_id"] = 0;
					$fake_detalle["operador_id"] = $formula["operador_id"];
					$fake_detalle["servicio_id"] = $formula["servicio_id"];
					$fake_detalle["moneda_id"] = $formula["moneda_id"];
					$fake_detalle["fuente_id"] = $formula["fuente_id"];
					$fake_detalle["sobre_id"] = $formula["sobre_id"];
					$fake_detalle["tipo_id"] = $formula["tipo_id"];
					$fake_detalle["desde"] = 0;
					$fake_detalle["hasta"] = 0;
					$fake_detalle["monto"] = 0;
					$extra["detalles"][0] = $fake_detalle;
				}

				if (array_key_exists("detalles", $extra)) {
					$sql_update_estado = "UPDATE tbl_contrato_formulas SET estado = '0' WHERE contrato_id = '" . $data["id"] . "' AND producto_id = '" . $extra["producto_id"] . "'";
					$mysqli->query($sql_update_estado);

					foreach ($extra["detalles"] as $key => $value) {
						$sql_insert = "INSERT INTO tbl_contrato_formulas (formula_id,contrato_id,producto_id,participante_id,operador_id,servicio_id,moneda_id,fuente_id,sobre_id,tipo_id,desde,hasta,monto,estado) 
						VALUES ('" . $formula["id"] . "'," . value_to_db($value["contrato_id"]) . "," . value_to_db($value["producto_id"]) . ",'" . $formula["participante_id"] . "','" . $formula["operador_id"] . "','" . $formula["servicio_id"] . "','" . $formula["moneda_id"] . "','" . $formula["fuente_id"] . "','" . $formula["sobre_id"] . "','" . $formula["tipo_id"] . "'," . value_to_db($value["desde"]) . "," . value_to_db($value["hasta"]) . "," . value_to_db($value["monto"]) . ",'1')";
						$mysqli->query($sql_insert);
						$return["sql_insert"][] = $sql_insert;
					}
				}
			}
		}
	}
	if (array_key_exists("lp_id", $data)) {
		$lp_id_local_id = $data["id"];

		$array_proveedorid = [];
		foreach ($data["lp_id"] as $key => $extra) {
			$array_proveedorid[] = $extra["proveedor_id"];
			$array_proveedorid_cn[$extra['canal_de_venta_id']][] = $extra["proveedor_id"];
		}

		foreach ($array_proveedorid_cn as $key => $cn_proveedor) {

			$provedor_no_filter = count($cn_proveedor);
			$provedor_filter = count(array_unique($cn_proveedor));
			if ($provedor_no_filter !== $provedor_filter) {
				$return["error_msg"] = "Hay proveedor Id repetido En canal de Venta";
				print_r(json_encode($return));
				die();
			}
		}
		// $ar = count($array_proveedorid);
		// $arr = count(array_unique($array_proveedorid));
		// if(count($array_proveedorid) !== count(array_unique($array_proveedorid))){
		// 	$return["error_msg"] = "Hay proveedor Id repetidos";
		// 	print_r(json_encode($return));
		// 	die();
		// }
		foreach ($data["lp_id"] as $key => $extra) {
			$tt = $new_id = strstr($extra["id"], "new_");
			if ($new_id = strstr($extra["id"], "new_")) {
				$query_validar = "SELECT lp.id,l.nombre AS nombre_local, l.cc_id FROM tbl_local_proveedor_id lp
								LEFT JOIN tbl_locales l ON l.id = lp.local_id
								WHERE lp.proveedor_id = '" . $extra["proveedor_id"] . "'

								and lp.canal_de_venta_id = {$extra['canal_de_venta_id']}
								
								AND lp.estado = 1";
			} else {
				$query_validar = "SELECT lp.id
								,lp.local_id
								,l.nombre  AS nombre_local
								,l.cc_id
								FROM tbl_local_proveedor_id lp
								LEFT JOIN tbl_locales l ON l.id = lp.local_id
								WHERE lp.proveedor_id = '" . $extra["proveedor_id"] . "'
								AND lp.estado = 1

								and lp.canal_de_venta_id = {$extra['canal_de_venta_id']}

								AND lp.local_id != " . $extra["local_id"];
			}
			$exists = $mysqli->query($query_validar);
			if ($exists->num_rows > 0) {
				$exists = $exists->fetch_assoc();
				$return["focus"] = $extra["id"];
				$return["error_msg"] = "Ya existe Proveedor Id " . $extra["proveedor_id"] . " en [" . $exists["cc_id"] . "]" . $exists["nombre_local"];
				print_r(json_encode($return));
				die();
			}
		}
		foreach ($data["lp_id"] as $key => $extra) {
			//print_r($extra);
			if ($new_id = strstr($extra["id"], "new_")) {

				$select_proveedor_local = "SELECT COUNT(id) as cant_proveedor FROM tbl_local_proveedor_id WHERE estado = 1 AND habilitado = 1 AND local_id = " . $extra["local_id"];
				$query_proveedor_local = $mysqli->query($select_proveedor_local);
				$local_proveedor = $query_proveedor_local->fetch_assoc();

				$sql_insert_lp_id = "INSERT INTO tbl_local_proveedor_id (local_id,servicio_id,canal_de_venta_id,nombre,proveedor_id) VALUES ('" . $extra["local_id"] . "','" . $extra["servicio_id"] . "','" . $extra["canal_de_venta_id"] . "'," . value_to_db($extra["nombre"]) . "," . value_to_db($extra["proveedor_id"]) . ")";
				$return["sql_insert"][] = $sql_insert_lp_id;
				$mysqli->query($sql_insert_lp_id);

				if ((int) $local_proveedor['cant_proveedor'] == 0) {
					$update_alta_local = "UPDATE tbl_locales SET fecha_alta = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $extra["local_id"];
					$mysqli->query($update_alta_local);
				}
			} else {

				$sql_update_lp_id = "UPDATE tbl_local_proveedor_id SET nombre = " . value_to_db($extra["nombre"]) . ", proveedor_id = " . value_to_db($extra["proveedor_id"]) . " WHERE id = '" . $extra["id"] . "'";
				$mysqli->query($sql_update_lp_id);
				$return["sql_update"][] = $sql_update_lp_id;
			}
			$lp_id_local_id = $extra["local_id"];
		}
	}
	if (array_key_exists("local_qty", $data)) {
		foreach ($data["local_qty"] as $qty_k => $qty_v) {
			$exists = $mysqli->query("SELECT id FROM tbl_local_qty WHERE local_id = '" . $qty_v["local_id"] . "' AND canal_de_venta_id = '" . $qty_v["cdv"] . "'")->fetch_assoc();
			if (isset($exists["id"]) && $exists["id"]) {
				$local_qty_command = "UPDATE tbl_local_qty SET qty = '" . $qty_v["val"] . "' WHERE local_id = '" . $qty_v["local_id"] . "' AND canal_de_venta_id = '" . $qty_v["cdv"] . "'";
			} else {
				$local_qty_command = "INSERT INTO tbl_local_qty (local_id,canal_de_venta_id,qty) VALUES ('" . $qty_v["local_id"] . "','" . $qty_v["cdv"] . "','" . $qty_v["val"] . "')";
			}
			$mysqli->query($local_qty_command);
		}
	}

	// if(array_key_exists("cctv_id", $data)){
	// 	$lp_id_local_id = $data["id"];
	// 	foreach ($data["cctv_id"] as $key => $extra) {
	// 		//print_r($extra);
	// 		if($new_id = strstr($extra["id"],"new_")){
	// 			$sql_insert_cctv_id = "INSERT INTO tbl_local_cctv (local_id,username,password) VALUES ('".$lp_id_local_id."',".value_to_db($extra["username"]).",".value_to_db($extra["password"]).")";
	// 			$return["sql_insert"][]=$sql_insert_cctv_id;
	// 			$mysqli->query($sql_insert_cctv_id);
	// 		}else{
	// 			$sql_update_cctv_id = "UPDATE tbl_local_cctv SET username = ".value_to_db($extra["username"]).", password = ".value_to_db($extra["password"])." WHERE id = '".$extra["id"]."'";
	// 			$mysqli->query($sql_update_cctv_id);
	// 			$return["sql_update"][]=$sql_update_cctv_id;
	// 		}
	// 	}
	// }

	if (array_key_exists("local_credenciales", $data)) {
		$local_id = $data["id"];
		foreach ($data["local_credenciales"] as $key => $extra) {
			if (trim($extra["id"]) == "") {
				$sql_insert_credential_id = "
				INSERT INTO tbl_local_credencial_detalle_valor (local_id,campo_tipo_credencial_id,valor) 
				VALUES (" . value_to_db($extra["local_id"]) . "," . value_to_db($extra["campo_tipo_credencial_id"]) . "," . value_to_db($extra["valor"]) . ")";
				$mysqli->query($sql_insert_credential_id);
			} else {
				$sql_update_credential_id = "UPDATE tbl_local_credencial_detalle_valor SET valor = " . value_to_db($extra["valor"]) . " WHERE id = '" . $extra["id"] . "'";
				$mysqli->query($sql_update_credential_id);
			}
		}
	}

	//--------------
	if (array_key_exists("servicio_id", $data)) {
		if ($data["servicio_id"] == "") {
			$sql_insert_servicios = "
			INSERT INTO tbl_locales_servicios (local_id,internet_proveedor_id,internet_tipo_id,num_decos_internet,num_decos_directv) 
			VALUES (" . value_to_db($data["id"]) . ",
					" . value_to_db($data["internet_proveedor_id"]) . ",
					" . value_to_db($data["internet_tipo_id"]) . ",
					" . value_to_db($data["num_decos_internet"]) . ",
					" . value_to_db($data["num_decos_directv"]) . "
				)";
			$mysqli->query($sql_insert_servicios);
		} else {

			$sql_update_servicios = "UPDATE tbl_locales_servicios 
						SET num_decos_internet = " . value_to_db($data["num_decos_internet"]) . ",
							num_decos_directv = " . value_to_db($data["num_decos_directv"]) . ",
							internet_proveedor_id = " . value_to_db($data["internet_proveedor_id"]) . ",
							internet_tipo_id = " . value_to_db($data["internet_tipo_id"]) . "
						WHERE local_id = '" . $data["id"] . "'";
			$mysqli->query($sql_update_servicios);
		}
	}
	if (array_key_exists("servicio_id", $data)) {
		if ($data["equipos_id"] == "") {
			$sql_insert_equipos = "
		INSERT INTO tbl_locales_equipos (local_id,num_tv_apuestas_virtuales,num_tv_apuestas_deportivas) 
		VALUES (" . value_to_db($data["id"]) . ",
				" . value_to_db($data["num_tv_apuestas_virtuales"]) . ",
				" . value_to_db($data["num_tv_apuestas_deportivas"]) . "
			)";
			$mysqli->query($sql_insert_equipos);
		} else {
			$sql_update_equipos = "UPDATE tbl_locales_equipos 
					SET num_tv_apuestas_virtuales = " . value_to_db($data["num_tv_apuestas_virtuales"]) . ",
						num_tv_apuestas_deportivas = " . value_to_db($data["num_tv_apuestas_deportivas"]) . "
					WHERE local_id = '" . $data["id"] . "'";
			$mysqli->query($sql_update_equipos);
		}
	}
	if (array_key_exists("servicio_id", $data)) {
		if ($data["equipos_computo_id"] == "") {
			$sql_insert_equipos_computo = "
		INSERT INTO tbl_locales_equipos_computo (local_id,num_cpu,num_monitores,num_autoservicios,num_allinone,num_terminales_hibrido,num_terminales_antiguo) 
		VALUES (" . value_to_db($data["id"]) . ",
				" . value_to_db($data["num_cpu"]) . ",
				" . value_to_db($data["num_monitores"]) . ",
				" . value_to_db($data["num_autoservicios"]) . ",
				" . value_to_db($data["num_allinone"]) . ",
				" . value_to_db($data["num_terminales_hibrido"]) . ",
				" . value_to_db($data["num_terminales_antiguo"]) . "
			)";
			$mysqli->query($sql_insert_equipos_computo);
		} else {
			$sql_update_equipos_computo = "UPDATE tbl_locales_equipos_computo 
						SET num_cpu = " . value_to_db($data["num_cpu"]) . ",
							num_monitores = " . value_to_db($data["num_monitores"]) . ",
							num_autoservicios = " . value_to_db($data["num_autoservicios"]) . ",
							num_allinone = " . value_to_db($data["num_allinone"]) . ",
							num_terminales_hibrido = " . value_to_db($data["num_terminales_hibrido"]) . ",
							num_terminales_antiguo = " . value_to_db($data["num_terminales_antiguo"]) . "
						WHERE local_id = '" . $data["id"] . "'";
			$mysqli->query($sql_update_equipos_computo);
		}
	}
	//-------------------------

	if (array_key_exists("configv2", $data)) {
		foreach ($data["configv2"] as $id => $relacion) {
			$mysqli->query("UPDATE tbl_local_relacion SET proveedor_id='" . $relacion['proveedor_id'] . "', nombre='" . $relacion['nombre'] . "' WHERE id = {$id}");
		}
	}
	if (isset($data["values"]["red_id"]) && $data["values"]["red_id"] > 0) {
		if ((int)$data["values"]["red_id"] == 5) {
			$query_usuario_local = "SELECT * FROM tbl_usuarios_locales WHERE usuario_id= 132 and local_id = " . $data["id"];
			$exists_local_usuario = $mysqli->query($query_usuario_local)->fetch_assoc();
			if (!$exists_local_usuario) {
				$set_query = "INSERT INTO tbl_usuarios_locales (usuario_id,local_id,estado) VALUES (132,'" . $data["id"] . "','1')";
				$mysqli->query($set_query);
			}
		}
	}
	//------------------------------------------------------------------------------------------------------------------------//
	//Se agrega usuarios que tengan permisos a futuras tiendas
	if (isset($data["values"]["red_id"])) {
		$iresultado = assign_users($data["id"], $data["values"]["red_id"]);
	}
	//------------------------------------------------------------------------------------------------------------------------//

}

/**
 * funcionalidad para agregar todos los usuarios a un nuevo local creado que pertenezca a una red
 */
function assign_users($local_id = 0, $red_id = 0)
{
	global $mysqli;
	if ($local_id != 0 && $red_id != 0) {
		try {
			$sql = "insert into tbl_usuarios_locales (usuario_id, local_id, estado, created_at) 
				select usuario_id, $local_id as local_id, 1 as estado, CURRENT_TIMESTAMP as created_at 
				from tbl_usuarios_redes where estado=1 and red_id = " . $red_id;
			/*
			$sql = "insert into tbl_usuarios_locales (usuario_id, local_id, estado, created_at)
			select distinct ul.usuario_id, $local_id as local_id, 1 as estado, CURRENT_TIMESTAMP as created_at
			from tbl_usuarios_locales ul
			inner join tbl_locales lo 
				on lo.id=ul.local_id
				and lo.red_id= $red_id
			where usuario_id not in (
				select usuario_id from tbl_usuarios_locales where local_id = $local_id
			)";
			*/
			$mysqli->query($sql);
			return 1;
		} catch (Exception $e) {
			return 0;
		}
	}
	return 0;
}

/**
 * Funcionalidad para quitar todos los usuarios de un local, excepto cajeros
 */
function fxQuitarUsuariosLocal(int $local_id = 0)
{
	global $mysqli;
	//$vsql = "update tbl_usuarios_locales set estado=0 where local_id=$local_id ";
	$vsql = "update tbl_usuarios_locales ul 
			inner join tbl_usuarios u 
				on u.id=ul.usuario_id
				and ul.local_id=$local_id and ul.estado=1
			inner join tbl_personal_apt p
				on p.id=u.personal_id
				and p.estado=1 and p.cargo_id<>5 
			set ul.estado=0
			";
	if ($local_id != 0) {
		try {
			$mysqli->query($vsql);
			if ($mysqli->error) {
				return false;
			}
			return true;
		} catch (Exception $e) {
			//return 0;
			throw $e;
		}
	}
	return false;
}

/**
 * Funcionalidad que Valida si RED de un local fue cambiado, entonces se quita todos los usuarios del local
 * true si es red diferente 
 */
function fxCambioRedLocal(int $local_id = 0, int $red_id_nueva)
{
	global $mysqli;
	$vsql = "select red_id from tbl_locales where id= $local_id";
	$arr_local = $mysqli->query($vsql)->fetch_assoc();
	$red_id_antigua = $arr_local['red_id'];
	return ($red_id_antigua != $red_id_nueva);
}


function sort_list($data)
{
	global $mysqli;
	foreach ($data["list"] as $key => $value) {
		$sql_update = "UPDATE ";
		$sql_update .= $data["tabla"];
		$sql_update .= " SET ";
		$sql_update .= " orden = '" . $key . "'";
		$sql_update .= " WHERE ";
		$sql_update .= " id ";
		$sql_update .= " = ";
		$sql_update .= "'";
		$sql_update .= $value;
		$sql_update .= "'";
		echo $sql_update;
		echo "\n";
		$mysqli->query($sql_update);
	}
}
function switch_data($data)
{
	global $mysqli;
	global $login;
	$fecha_update = date("Y-m-d H:i:s");

	$ret = [];
	$data["manual"] = $data["val"] == 1 ? 0 : 1;
	$sql_update = "UPDATE ";
	$sql_update .= $data["table"];
	$sql_update .= " SET ";
	$sql_update .= $data["col"];
	$sql_update .= " = ";
	$sql_update .= "'";
	$sql_update .= $data["val"];
	$sql_update .= "'";
	if (
		$data["table"] != 'tbl_caja'
		&& $data["table"] != "tbl_caja_depositos"
		&& $data["table"] != "tbl_locales"
		&& $data["table"] != "tbl_locales_web_config"
		&& $data["table"] != "tbl_local_proveedor_id"
		&& $data["table"] != "tbl_soporte_notas"
		&& $data["table"] != "tbl_menu_sistemas"
		&& $data["table"] != "tbl_personal_apt"
		&& $data["table"] != "cont_categoria_servicio"
		&& $data["table"] != "cont_tipo_categoria_servicio"
		&& $data["table"] != "cont_condicion_economica"
		&& $data["table"] != "cont_locacion"
		&& $data["table"] != "cont_mandato"
		&& $data["table"] != "cont_mutuodinero"
		&& $data["table"] != "mepa_caja_chica_liquidacion"
	)
		$sql_update .= ", manual = " . $data["manual"];
	$sql_update .= " WHERE ";
	if ($data["table"] == "tbl_locales_web_config") {
		$sql_update .= "local_id ";
		if ($data["col"] == 'agente_can_deposit') {
			$usuario_id = $login ? $login['id'] : 0;
			$local_id = $data['id'];
			$insert_activacion =  insertTable('tbl_saldo_web_activaciones', [
				'accion' => $data["val"],
				'user_id' => $usuario_id,
				'local_id' => $local_id,
				'created_at' => "'" . date('Y-m-d H:i:s') . "'",
			]);

			if (!empty($insert_activacion['mysqli_error'])) {
				return $insert_activacion;
			}
		}
		/*tbl_locales_web_config ,col = is_open  && val = 1*/
		if ($data["col"] == 'is_open' && $data["val"] == 1) {
			$l_id = $data["id"];
			$cmd = "SELECT red_id FROM tbl_locales WHERE id = " . $l_id;
			$loc_cmd = $mysqli->query($cmd)->fetch_assoc();
			if ($loc_cmd  != null &&  $loc_cmd["red_id"] == 5) { //AGENTES AT fecha_inicio_operacion
				$f_ini_op = date("Y-m-d H:i:s");
				$mysqli->query("UPDATE tbl_locales SET fecha_inicio_operacion = '$f_ini_op' WHERE fecha_inicio_operacion IS NULL AND id = " . $l_id);
				if ($mysqli->error) {
					$ret["ERROR_MYSQL_f_ini_op"] = $mysqli->error;
				}
			}
		}
		/**/
	} else if ($data["table"] == "cont_condicion_economica") {
		$sql_update .= "condicion_economica_id ";
	} else if ($data["table"] == "cont_locacion") {
		$sql_update .= "idlocacion ";
	} else if ($data["table"] == "cont_mandato") {
		$sql_update .= "idmandato ";
	} else if ($data["table"] == "cont_mutuodinero") {
		$sql_update .= "idmutuodinero ";
	} else if ($data["table"] == "mepa_caja_chica_liquidacion") {
		$sql_update .= "id ";
	} else {
		$sql_update .= "id ";
	}
	$sql_update .= " = ";
	$sql_update .= "'";
	$sql_update .= $data["id"];
	$sql_update .= "'";

	if ($data["table"] == 'tbl_personal_apt') {
		if ($data["id"] != 0 || $data["id"] != "") {
			$personal_query = 'select * from tbl_personal_apt where id = ' . $data["id"];
			$personal = $mysqli->query($personal_query)->fetch_assoc();
			if ($personal[$data["col"]] != $data["val"]) {

				print_r($personal[$data["col"]] . '---' . $data["val"]);

				$insert_personal_auditoria =  insertTable('tbl_personal_auditoria', [
					'personal_id' => $data["id"],
					'nombre_personal' => $personal["nombre"] . ' ' . $personal["apellido_paterno"] . ' ' . $personal["apellido_materno"],
					'campo' => $data["col"],
					'valor_anterior' => $personal[$data["col"]],
					'valor' => $data["val"],
					'campo_updated_by' => $login['id'],
					'ip' => $login['login_ip'],
					'created_at' => date('Y-m-d H:i:s'),
					'user_created_id' => $login['id']
				]);
			}
			$update_campos_query = "
				UPDATE tbl_personal_apt
					SET user_updated_id = '" . $login['id'] . "',
						updated_at = '" . $fecha_update . "'
					WHERE id = '" . $data["id"] . "'
				";
			$mysqli->query($update_campos_query);
		}
	}
	if ($data["table"] == 'tbl_usuarios') {

		$update_campos_query = "
				UPDATE tbl_usuarios
					SET user_updated_id = '" . $login['id'] . "',
						updated_at = '" . $fecha_update . "'
					WHERE id = '" . $data["id"] . "'
				";
		$mysqli->query($update_campos_query);
	}

	// $return["sql_update"]=$sql_update;
	$mysqli->query($sql_update);
	$ret["data"] = $data;
	$ret["sql_update"] = $sql_update;
	return $ret;
}
function sorteo_tienda_nueva_inicio($data)
{
	global $mysqli;
	global $login;

	$user_id = $login ? $login['id'] : 0;

	$mysqli->query("UPDATE tbl_locales SET fecha_sorteo_tienda_nueva = '" . $data['val'] . "' WHERE id='" . $data['id'] . "' and fecha_sorteo_tienda_nueva is null");

	if ((int)$mysqli->affected_rows > 0) {
		$draw_query = "
			SELECT id
			FROM sorteos.draws
			WHERE slug='tiendas_nuevas' 
			ORDER BY id DESC
			LIMIT 1
			";
		$draw_res = $mysqli->query($draw_query)->fetch_assoc();

		$fecha_sorteo = $data['val'];
		for ($i = 0; $i < 7; $i++) {
			$temp_fecha_inicio = date("Y-m-d", strtotime($fecha_sorteo . "+ " . $i . " days"));
			$temp_fecha_fin = date("Y-m-d", strtotime($fecha_sorteo . "+ " . ($i + 1) . " days"));
			$temp_slug = 'tiendas_nuevas_' . $data['id'] . '_' . ($i + 1);

			$event_query_ = "
				SELECT e.id 
				FROM sorteos.events e 
				WHERE e.draw_id='" . $draw_res['id'] . "' 
				AND e.slug='" . $temp_slug . "' 
				ORDER BY e.id DESC 
				LIMIT 1 
				";
			$event_res_ = array();
			$event_res_ = $mysqli->query($event_query_)->fetch_assoc();

			if (count($event_res_) === 0) {
				$mysqli->query("
					INSERT INTO sorteos.events (draw_id,slug,prize,winner,status,starts_at,ends_at,created_at,updated_at) 
					VALUES ('" . $draw_res['id'] . "','" . $temp_slug . "',50,1,1,'" . $temp_fecha_inicio . " 00:00:00','" . $temp_fecha_fin . " 00:00:00',now(),now())
					");
			}

			$event_query = "
				SELECT 
					e.id,
					IFNULL(le.id, 0) le_id
				FROM sorteos.events e 
				LEFT JOIN tbl_local_events le ON le.event_id=e.id 
				WHERE e.draw_id='" . $draw_res['id'] . "' 
				AND e.slug='" . $temp_slug . "' 
				ORDER BY le.id DESC, e.id DESC 
				LIMIT 1 
				";
			$event_res = array();
			$event_res = $mysqli->query($event_query)->fetch_assoc();

			if ((int)$event_res['le_id'] === 0) {
				$mysqli->query("
					INSERT INTO tbl_local_events (local_id,draw_id,event_id,status,user_id,created_at) 
					VALUES ('" . $data['id'] . "','" . $draw_res['id'] . "','" . $event_res['id'] . "',1,'" . $user_id . "',now())
					");
			}
		}
	} else {
		exit();
	}
}
function data_to_db($d)
{
	global $mysqli;
	$tmp = [];
	$nulls = ["null", "", false];
	foreach ($d as $k => $v) {
		if ($v === 0) {
			$tmp[$k] = $v;
		} elseif (in_array($v, $nulls)) {
			$tmp[$k] = "NULL";
		} else {
			if (is_float($v)) {
				$tmp[$k] = "'" . $v . "'";
			} elseif (is_int($v)) {
				$tmp[$k] = $v;
			} else {
				$v = str_replace(",", ".", $v);
				$tmp[$k] = "'" . trim($mysqli->real_escape_string($v)) . "'";
			}
		}
	}
	return $tmp;
}
function value_to_db($v)
{
	global $mysqli;
	$tmp = "";
	$nulls = ["null", ""];
	if (in_array($v, $nulls)) {
		$tmp = "NULL";
	} else {
		if (is_float($v)) {
			$tmp = "'" . $v . "'";
		} elseif (is_int($v)) {
			$tmp = $v;
		} else {
			$v = str_replace(",", ".", $v);
			$tmp = "'" . trim($mysqli->real_escape_string($v)) . "'";
		}
	}
	return $tmp;
}
function rec_reprocess($data)
{
	global $mysqli;
	global $return;
	global $login;

	$proceso_command = "SELECT fecha_inicio,fecha_fin,servicio_id,tipo,archivo_id
	FROM tbl_transacciones_procesos
	WHERE id = '" . $data["id"] . "'";
	$proceso = $mysqli->query($proceso_command)->fetch_assoc();
	$tra_data = [];
	$tra_data["tabla"] = "tbl_transacciones_repositorio";
	$tra_data["servicio_id"] = $proceso["servicio_id"];
	$tra_data["tipo"] = $proceso["tipo"];
	//$tra_data["inicio_fecha"]=substr(strstr($proceso["fecha_inicio"], " ",true),0);
	//$tra_data["inicio_hora"]=substr(strstr($proceso["fecha_inicio"], " "),1);
	//$tra_data["fin_fecha"]=substr(strstr($proceso["fecha_fin"], " ",true),0);
	//$tra_data["fin_hora"]=substr(strstr($proceso["fecha_fin"], " "),1);
	$tra_data["file_id"] = $proceso["archivo_id"];
	$tra_data["file_dir"] = "../files_bucket/";
	require('sys_transacciones.php');
	if ($tra_data["servicio_id"] == 2 || $tra_data["servicio_id"] == 3 || $tra_data["servicio_id"] == 5 || $tra_data["servicio_id"] == 6) {
		$return["import_csv_to_db"] = import_csv_to_db($tra_data);
	} else {
		//$return["transacciones_repositorio"]=transacciones_repositorio($tra_data);
	}

	$proceso_update_command = "UPDATE tbl_transacciones_procesos SET estado = '0' WHERE id = '" . $data["id"] . "'";
	$mysqli->query($proceso_update_command);
}
function rec_gen_liq($data)
{
	global $mysqli;
	global $return;
	require('sys_transacciones.php');
	//$return["hola"]="chau";
	//print_r($data); exit();
	transacciones_build_liquidaciones($data);
}
function import_from_bc($data)
{
	global $mysqli;
	global $return;
	require('sys_transacciones.php');
	if ($data["servicio_id"] == 2) {
		import_csv_to_db($data);
	} else {
		//transacciones_import_from_bc($data);
	}
	//sleep(5);
}

function setLocalZona($local_id, $zona_id)
{
	global $mysqli;
	global $return;

	$query = "
		SELECT zona_id 
		FROM tbl_locales 
		WHERE id = {$local_id}
	";
	$query_zona = null;
	$query_result = $mysqli->query($query);
	if ($mysqli->error) {
		echo $mysqli->error;
		die;
	}
	while ($r = $query_result->fetch_assoc()) $query_zona = $r["zona_id"];

	if ($query_zona !== $zona_id) {
		$mysqli->query("
			INSERT INTO tbl_local_zona (
				local_id,
				zona_id,
				created_at
			) VALUES (
				{$local_id},
				{$zona_id},
				'" . date('Y-m-d H:i:s') . "'
			)
		");
	}
}


// echo "ASDASDAS";
//$return["memory_end"]=memory_get_usage();
//$return["time_end"] = microtime(true);
//$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
//$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
