<?php
include("db_connect.php");

if (isset($_POST['opt'])) {
    if ($_POST['opt']=='select_permisos_change') {
        $query = "SELECT
                u.id as id ,
                u.usuario as usuario,
                p.nombre as nombre,
                p.apellido_paterno as apellido_paterno,
                p.apellido_materno as apellido_materno,
                s.id as sistema_id,
                s.nombre as sistema 
            FROM tbl_usuarios u 
            INNER JOIN tbl_personal_apt p
            ON u.personal_id = p.id 
            INNER JOIN tbl_sistemas s
            ON u.sistema_id = s.id WHERE u.estado = '1' AND u.sistema_id = '".$_POST['id']."'";
        $result = $mysqli->query($query);
        $option_op = "<option selected disabled>Seleccione opción</option>";
        foreach ($result as $row_select_upermisos) {            
            $option_op.= "<option value='".$row_select_upermisos['id']."'> [".$row_select_upermisos['id']."] - ".$row_select_upermisos['nombre']." ".$row_select_upermisos['apellido_paterno']." ".$row_select_upermisos['apellido_materno']."</option>";
        }
        echo json_encode($option_op);
    }
}
?>