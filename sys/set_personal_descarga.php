<?php
header('Content-Encoding: UTF-8');
include("global_config.php");
include("db_connect.php");
include("sys_login.php");

$output_csv_descarga_final = "";
if ($_POST["tipo_descarga"] == "descarga_formato") {
    // Para la exportación en Excel
    $filename = "formato_importacion_masiva.csv";
    header("Content-Description: File Transfer");
    header("Content-type: application/force-download");
    header('Content-Disposition: attachment; filename="'.$filename.'";');

    $output_csv_descarga_final = "NOMBRE;A_PATERNO;A_MATERNO;DNI;ID_AREA;ID_CARGO;CORREO_(Opcional);TELEFONO_(Opcional);CELULAR_(Opcional);ID_ZONA_(Opcional);USUARIO_(Opcional);COMPLEMENTO_(Opcional);ID_SISTEMA;ID_GRUPO";
    echo $output_csv_descarga_final;
}
else if ($_POST["tipo_descarga"] == "descarga_registro_masivo") {
    $date_search_inicio = date('Y-m-d H:i:s', strtotime($_POST['fecha_registro_masivo_inicio']));
    $date_search_final = date('Y-m-d H:i:s', strtotime($_POST['fecha_registro_masivo_final']));

    // Buscamos la información
    $user_personal_command = "SELECT tpa.dni, tpa.nombre, tpa.apellido_paterno, tpa.apellido_materno, tu.id as id_usuario, tu.usuario, tu.fecha_masivo
                            FROM tbl_personal_apt tpa
                            inner join tbl_usuarios tu on tpa.id = tu.personal_id
                            where tu.fecha_masivo BETWEEN '".$date_search_inicio."' and '".$date_search_final."'";
    $user_personal_datos = $mysqli->query($user_personal_command);
    $up_exist = mysqli_num_rows($user_personal_datos);

    if ($up_exist > 0) {
        // Para la exportación en Excel
        $filename = "registro_masivo_de_fecha_".$_POST['fecha_registro_masivo_inicio'].".xls";
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = "
        <table>
            <thead>
                <tr style='background-color:#1F4E78; color: #FFFFFF;'>
                    <th>DNI</th>
                    <th>NOMBRE</th>
                    <th>A. PATERNO</th>
                    <th>A. MATERNO</th>
                    <th>ID usuario</th>
                    <th>USUARIO</th>
                    <th>CONTRASEÑA (la que fue generada automaticamente al importar)</th>
                    <th>FECHA DE REGISTRO</th>
                </tr>
            </thead>
            <tbody>
        ";
        $i = 0;
        foreach ($user_personal_datos as $out_datos) {
            $pass_user = strtolower($out_datos['usuario']).'.123456';
            if ($i == 0) {
                $estilo_fila = "style='background-color:#E7E6E6;'";
                $i = 1;
            } else {
                $estilo_fila = "";
                $i = 0;
            }
            $output.= "
            <tr ".$estilo_fila.">
                <td style=\"mso-number-format:'@';\">".$out_datos['dni']."</td>
                <td>".$out_datos['nombre']."</td>
                <td>".$out_datos['apellido_paterno']."</td>
                <td>".$out_datos['apellido_materno']."</td>
                <td>".$out_datos['id_usuario']."</td>
                <td>".$out_datos['usuario']."</td>
                <td>".$pass_user."</td>
                <td style=\"mso-number-format:'d/mm/yyyy hh:mm:ss AM/PM';\">".$out_datos['fecha_masivo']."</td>
            </tr>
            ";
        }
        $output.= "
            </tbody>
        </table>
        ";
        
        $output = iconv("UTF-8", "ISO-8859-1", $output);
        echo $output;
    } else {
        echo "
            <script>
                alert('No hay registros subidos masivamente para esta fecha');
                history.back();
            </script>
            ";
    }
}
else if ($_POST["tipo_descarga"] == "descarga_registro_personal"){
    $area_did = $_POST["area_download_id"];
    $cargo_did = $_POST["cargo_download_id"];
    $zona_did = $_POST["zona_download_id"];

    $list_where = " WHERE pa.estado = '1'";
	if($_POST["estado_download_id"] == 'all'){
		$list_where .= " OR pa.estado != '1'";
	} else if($_POST["estado_download_id"] != 1){
		$list_where = " WHERE pa.estado != '1'";
	}
    if ($area_did != 'all') $list_where .= " AND pa.area_id = '$area_did'";
    if ($cargo_did != 'all') $list_where .= " AND pa.cargo_id = '$cargo_did'";
    if ($zona_did != 'all') $list_where .= " AND pa.zona_id = '$zona_did'";
    $sql_query = "SELECT
                pa.id,
                pa.nombre,
                pa.apellido_paterno,
                pa.apellido_materno,
                pa.dni,
                ar.nombre AS area_nombre,
                ca.nombre AS cargo_nombre,
                pa.telefono,
                pa.correo,
                zo.nombre AS zona_nombre,
                pa.estado
            FROM tbl_personal_apt pa
            LEFT JOIN tbl_areas ar ON pa.area_id = ar.id
            LEFT JOIN tbl_cargos ca ON pa.cargo_id = ca.id
            LEFT JOIN tbl_zonas zo ON pa.zona_id = zo.id
            $list_where
            ORDER BY id ASC
    ";
    $result = $mysqli->query($sql_query);

    $filename = "registro_personal_".date("d-m-Y h:i:s").".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = "
        <table>
            <thead>
                <tr style='background-color:#1F4E78; color: #FFFFFF;'>
                    <th>ID</th>
                    <th>NOMBRE</th>
                    <th>A. PATERNO</th>
                    <th>A. MATERNO</th>
                    <th>DNI</th>
                    <th>AREA</th>
                    <th>CARGO</th>
                    <th>TELEFONO</th>
                    <th>CORREO</th>
                    <th>ZONA</th>
                    <th>ESTADO</th>
                </tr>
            </thead>
            <tbody>
    ";
    $i = 1;
    foreach ($result as $out_datos) {
        if ($i == 0) {
            $estilo_fila = "style='background-color:#E7E6E6;'";
            $i = 1;
        } else {
            $estilo_fila = "";
            $i = 0;
        }
        $output.= "
            <tr $estilo_fila>
                <td>".$out_datos['id']."</td>
                <td>".$out_datos['nombre']."</td>
                <td>".$out_datos['apellido_paterno']."</td>
                <td>".$out_datos['apellido_materno']."</td>
                <td style=\"mso-number-format:'@';\">".$out_datos['dni']."</td>
                <td>".$out_datos['area_nombre']."</td>
                <td>".$out_datos['cargo_nombre']."</td>
                <td>".$out_datos['telefono']."</td>
                <td>".$out_datos['correo']."</td>
                <td>".$out_datos['zona_nombre']."</td>
        ";
        if ($out_datos['estado'] == 1){
            $output .= "<td>Activo</td>";
        } else {
            $output .= "<td>Inactivo</td>";
        }
        $output .="</tr>";
    }
    $output.= "
            </tbody>
        </table>
    ";
    
    $output = iconv("UTF-8", "ISO-8859-1", $output);
    echo $output;
}
?>