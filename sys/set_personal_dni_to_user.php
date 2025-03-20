<?php
header('Content-Encoding: UTF-8');
include("global_config.php");
include("db_connect.php");
include("sys_login.php");

// Para la exportación en Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
$filename = "usuarios_por_dni.xls";
header("Content-Disposition: attachment; filename=\"$filename\"");

$estilo_fila = "";
$color_file = 0;
$output = "
<table>
    <thead>
        <tr style='background-color:#1F4E78; color: #FFFFFF;'>
            <th>ID USER</th>
            <th>DNI</th>
            <th>USER</th>
            <th>NOMBRE</th>
            <th>A. PATERNO</th>
            <th>SISTEMA</th>
            <th>AREA</th>
            <th>CARGO</th>
            <th>GRUPO</th>
            <th>ZONA</th>
            <th>STATUS</th>
        </tr>
    </thead>
    <tbody>
";

$arr = explode("\r\n", trim($_POST['dni_to_search_textarea']));

for ($i = 0; $i < count((array)$arr); $i++) {
    $arr[$i] = preg_replace('/\s+/', '',$arr[$i]);
    $query_data_dni = "SELECT 	p.id,
        u.id as id_user,
        p.dni,
        u.usuario,		
        p.nombre AS personal_nombre,
        p.apellido_paterno AS apellidos,
        s.nombre AS sistema,
        a.nombre AS area,
        c.nombre AS cargo,
        g.nombre AS grupo,
        z.nombre AS zona,
        IF(u.estado = 1, 'Activo', 'Inactivo') AS estado
        FROM tbl_usuarios  u
        LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
        LEFT JOIN tbl_sistemas s ON (s.id = u.sistema_id)
        LEFT JOIN tbl_areas a ON (a.id = p.area_id)
        LEFT JOIN tbl_cargos c ON (c.id = p.cargo_id)
        LEFT JOIN tbl_zonas z ON (p.zona_id = z.id)
        LEFT JOIN tbl_usuarios_grupos g ON (g.id = u.grupo_id)
        WHERE p.dni is not null and p.dni in
        (".$arr[$i].")";
    $dni_personal_datos = $mysqli->query($query_data_dni);
    $dni_exist = mysqli_num_rows($dni_personal_datos);

    if ($color_file == 0) {
        $estilo_fila = "style='background-color:#E7E6E6;'";
        $color_file = 1;
    } else {
        $estilo_fila = "";
        $color_file = 0;
    }

    if ($dni_exist < 1) {
        $estilo_fila = "style='background-color:#FFFF00;'";
        $output.= "
        <tr ".$estilo_fila.">
            <td style=\"mso-number-format:'@';\">".$arr[$i]."</td>
            <td>DNI no encontrado</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        ";
    } else {
        foreach ($dni_personal_datos as $dni_user) {
            $output.= "
            <tr ".$estilo_fila.">
                <td style=\"mso-number-format:'@';\">".$dni_user['id_user']."</td>
                <td style=\"mso-number-format:'@';\">".$dni_user['dni']."</td>
                <td>".$dni_user['usuario']."</td>
                <td>".$dni_user['personal_nombre']."</td>
                <td>".$dni_user['apellidos']."</td>
                <td>".$dni_user['sistema']."</td>
                <td>".$dni_user['area']."</td>
                <td>".$dni_user['cargo']."</td>
                <td>".$dni_user['grupo']."</td>
                <td>".$dni_user['zona']."</td>
                <td>".$dni_user['estado']."</td>
            </tr>
            ";
        }
    }
    

}
$output.= "
            </tbody>
        </table>
        ";

$output = iconv("UTF-8", "ISO-8859-1", $output);
echo $output;

?>