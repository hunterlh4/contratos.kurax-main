<?php
include("../sys/sys_login.php");
//$return["user_id"] = $login;	



if (isset($_POST["where"])) {
	if ($_POST["where"] == "validar_usuario_permiso_botones") {
		if ($login["id"] == 1) {
			$return["permisos"] = true;
		} else {
			if (array_key_exists("filtro", $_POST)) {
				$filtro = $_POST["filtro"];
				$return["filtro"] = $filtro;
			}

			$sql_command_btn = "SELECT id FROM `tbl_botones` WHERE boton ='" . $filtro['text_btn'] . "'";
			$sql_query_command_btn = $mysqli->query($sql_command_btn);
			$id_btn = $sql_query_command_btn->fetch_assoc();
			$return["id_btn"] = $id_btn["id"];


			if ($filtro['sub_sec_id'] == NULL) {
				$sql_command_ms = "SELECT id FROM tbl_menu_sistemas WHERE sec_id='" . $filtro['sec_id'] . "'";
				//$return["queryms"] = $sql_command_ms;
			} else {
				$sql_command_ms = "SELECT id FROM tbl_menu_sistemas WHERE sec_id='" . $filtro['sec_id'] . "' and sub_sec_id ='" . $filtro['sub_sec_id'] . "'";
				//$return["queryms"] = $sql_command_ms;
			}

			$sql_query_ms = $mysqli->query($sql_command_ms);
			while ($itm = $sql_query_ms->fetch_assoc()) {
				$return["id"] = $itm["id"];
				$sql_command = "SELECT count(id) as id FROM tbl_permisos WHERE menu_id = '" . $itm['id'] . "' and  usuario_id='" . $login["id"] . "' and boton_id='" . $id_btn["id"] . "' AND estado ='1'";
				//$return["queryp"] = $sql_command;
				$sql_query = $mysqli->query($sql_command);
				while ($itm = $sql_query->fetch_assoc()) {
					if ($itm["id"] > 0) {
						$return["permisos"] = true;
					} else {
						$return["permisos"] = false;
					}
				}
			}
		}
	}
}
function validar_usuario_permisos($cod_user, $cod_btn, $sec_id, $sub_sec_id)
{
	global $mysqli, $return, $login;

	if ($login["id"] == 1) {
		return  true;
	} else {

		$sql_command_btn = "SELECT id FROM `tbl_botones` WHERE boton ='" . $cod_btn . "'";
		$sql_query_command_btn = $mysqli->query($sql_command_btn);
		$id_btn = $sql_query_command_btn->fetch_assoc();
		//$return["id_btn"]= $id_btn["id"];

		if ($sub_sec_id == NULL) {
			$sql_command_ms = "SELECT id FROM tbl_menu_sistemas WHERE sec_id='" . $sec_id . "'";
		} else {
			$sql_command_ms = "SELECT id FROM tbl_menu_sistemas WHERE sec_id='" . $sec_id . "' and sub_sec_id ='" . $sub_sec_id . "'";
		}

		$sql_query_ms = $mysqli->query($sql_command_ms);
		while ($itm = $sql_query_ms->fetch_assoc()) {
			$return["id"] = $itm["id"];
			$sql_command = "SELECT count(id) as id FROM tbl_permisos WHERE menu_id = '" . $itm['id'] . "' and  usuario_id='" . $login["id"] . "' and boton_id='" . $id_btn["id"] . "' AND estado ='1'";
			$sql_query = $mysqli->query($sql_command);
			while ($itm = $sql_query->fetch_assoc()) {
				if ($itm["id"] > 0) {
					return  true;
				} else {
					return false;
				}
			}
		}
	}
}
