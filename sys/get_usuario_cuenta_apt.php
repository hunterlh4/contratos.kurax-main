<?php
include("db_connect.php");
include("sys_login.php");
$return = array();

if (isset($_POST["accion"]) && $_POST["accion"] === "crear_usuario_cuentas_apt") {

    $id_usuario = $login['id'];
    
    $cuenta_id = $_POST["id_cuenta_apt"];
    $activar = $_POST["activar"];
    $result = array();
    
    try {

        $query = "    
            UPDATE tbl_usuario_cuentas_apt
            SET				
                estado =  $activar
                
            WHERE usuario_id = $id_usuario AND cuenta_apt_id = $cuenta_id
            ";
        $return_update = $mysqli->query($query);
        $filas_afectada = $mysqli->affected_rows;
        if ($filas_afectada==0) {
            $query = "    
            INSERT INTO tbl_usuario_cuentas_apt
			(
				
				usuario_id,
				cuenta_apt_id,
				estado
			)
			VALUES
			(
                $id_usuario,
                $cuenta_id,
                $activar 			
			)
            ";
        $return_insert = $mysqli->query($query);
        }
 
       
        $result["http_code"] = 201;
        $result["mensaje"] = "Exito..";
        $result["error"] ='false';
       
   
        
    } catch (Exception $e) {
        $result["http_code"] = 400;
        $result["mensaje"] = "Error..";
        $result["error"] =$e;
    }
}
echo json_encode($result);
