<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);

include("db_connect.php");
include("sys_login.php");

if(isset($_POST["opt"])){
    if($_POST["opt"]=="descarga_usuarios_activos"){
        header('Content-Encoding: UTF-8');
		header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		$filename = "usuarios_activos.xls";
		header("Content-Disposition: attachment; filename=\"$filename\"");

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
                    <th>N° TIENDAS</th>
                    <th>AREA</th>
                    <th>CARGO</th>
                    <th>GRUPO</th>
                    <th>STATUS</th>
				</tr>
			</thead>
			<tbody>
		";

		$active_user_query = "
            SELECT
                u.id as id_user,
                p.dni AS dni,
                u.usuario,
                p.nombre AS nombre,
                p.apellido_paterno AS apellido_paterno,
                s.nombre AS sistema,
                SUM(IF(l.operativo = 1 AND ul.estado = 1, 1, 0)) AS num_tiendas,
                -- l.cc_id,
                -- l.nombre AS tienda,
                a.nombre AS area,
                c.nombre AS cargo,
                g.nombre AS grupo,
                -- z.nombre AS zona,
                IF(u.estado = 1, 'Activo', 'Inactivo') AS estado
            FROM
                tbl_usuarios u
                LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
                LEFT JOIN tbl_sistemas s ON (s.id = u.sistema_id)
                LEFT JOIN tbl_usuarios_locales ul ON (ul.usuario_id = u.id)
                LEFT JOIN tbl_locales l ON (l.id = ul.local_id)
                LEFT JOIN tbl_areas a ON (a.id = p.area_id)
                LEFT JOIN tbl_cargos c ON (c.id = p.cargo_id)
                LEFT JOIN tbl_usuarios_grupos g ON (g.id = u.grupo_id)
                -- LEFT JOIN tbl_zonas z ON (z.id = l.zona_id)
            WHERE
                u.estado = 1
            GROUP BY u.id
            ORDER BY id_user ASC
		";
        $active_user_data = $mysqli->query($active_user_query);

        foreach ($active_user_data as $data_user) {
			$output.= "
			<tr style='background-color:#E7E6E6;'>
				<td>".$data_user['id_user']."</td>
				<td>".$data_user['dni']."</td>
				<td>".$data_user['usuario']."</td>
				<td>".$data_user['nombre']."</td>
				<td>".$data_user['apellido_paterno']."</td>
				<td>".$data_user['sistema']."</td>
				<td>".$data_user['num_tiendas']."</td>
				<td>".$data_user['area']."</td>
				<td>".$data_user['cargo']."</td>
				<td>".$data_user['grupo']."</td>
				<td>".$data_user['estado']."</td>
			</tr>
			";
		}
        $output.= "
            </tbody>
        </table>
        ";
        $output = iconv("UTF-8", "ISO-8859-1", $output);
        echo $output;
    }
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
//print_r(json_encode($return));

?>