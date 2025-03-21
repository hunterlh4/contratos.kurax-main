<?php
include("db_connect.php");
include("sys_login.php");

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_soporte_alerta_apuestas_deportivas_listar_locales") {
    try {
        $error = '';
        $querySelectListarLocales = "
		SELECT
            lc.id,
            lc.local_id,
            l.cc_id,
            l.nombre,
            lc.config_id,
            lc.config_param,
            lc.created_at,
            lc.updated_at
        FROM
            tbl_local_config AS lc
            LEFT JOIN tbl_locales AS l ON l.id = lc.local_id
            WHERE lc.config_id = 'alerta_terminal_deposit' and l.red_id in (1,5,16,9)
		";
        $resultQuery = $mysqli->query($querySelectListarLocales);
        $arrayReturn = array();
        while ($li = $resultQuery->fetch_assoc()) {
            $temp = new stdClass();
            $temp->id = $li['id'];
            $temp->local_id = $li['local_id'];
            $temp->cc_id = $li['cc_id'];
            $temp->nombre = $li['nombre'];
            $temp->config_id = $li['config_id'];
            $temp->config_param = $li['config_param'];
            $temp->created_at = $li['created_at'];
            $temp->updated_at = $li['updated_at'];
            array_push($arrayReturn, $temp);
            unset($temp);
        }

        if ($mysqli->error) {
            $error .= 'Error: ' . $mysqli->error . $querySelectListarLocales;
        }
        if ($error == '') {
            $result["http_code"] = 200;
            $result["status"] = "Datos Listados.";
            $result["error"] = false;
            $result["data"] = $arrayReturn;
        } else {
            $result["http_code"] = 400;
            $result["status"] = "Ocurrio un error";
            $result["error"] = true;
        }
    } catch (Exception $e) {
        http_response_code(500);
        $result["mensaje"]    = $e->getMessage();
        $result["error"]    = true;
    }
}
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_soporte_alerta_apuestas_deportivas_cambiar_estado") {
    try {
        $error = '';
        $mensaje = '';
        $id = (int)$_POST['id'];
        $estado = 0;
        $querySelectObtener = "		
        SELECT            
        lc.config_param
        FROM
        tbl_local_config AS lc
        WHERE lc.id = '{$id}'
		";
        $resultQuery = $mysqli->query($querySelectObtener);
        if ($mysqli->error) {
            $error .= 'Error: ' . $mysqli->error . $querySelectObtener;
        }
        $dtReturn = '';
        while ($li = $resultQuery->fetch_assoc()) {
            $dtReturn=$li;
        }
        if ((Int)$dtReturn['config_param']==0) {
            $estado=1;
            $mensaje = 'Activado';
        }else{
            $estado=0;
            $mensaje = 'Inactivo';
        }
        $queryUpdate = "
        UPDATE tbl_local_config
        SET
            config_param = {$estado},	
            updated_at = now()
        WHERE id = '{$id}'
        ";
        $resultQueryUpdate = $mysqli->query($queryUpdate);
        if ($mysqli->error) {
            $error .= 'Error: ' . $mysqli->error . $queryUpdate;
        }
        if ($error == '') {
            $result["http_code"] = 200;
            $result["status"] = "Actualizado.";
            $result["error"] = false;
        } else {
            $result["http_code"] = 400;
            $result["status"] = "Ocurrio un error";
            $result["error"] = true;
        }
    } catch (Exception $e) {
        http_response_code(500);
        $result["mensaje"]    = $e->getMessage();
        $result["error"]    = true;
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
