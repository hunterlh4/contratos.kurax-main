<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'garantia_locales' LIMIT 1");
while($r = $result->fetch_assoc()) $menu_id = $r["id"];

function sec_garantia_guardar_archivos($files_to_save, $id_solicitud_garantia, $estado_garantia) {
    global $mysqli, $return;

    $path = "/var/www/html/files_bucket/solicitud_garantia/";
    if (!is_dir($path)) mkdir($path, 0777, true);
    for ($i=0; $i < count($files_to_save["name"]); $i++) {
        $file = $files_to_save['name'][$i];
        $tmp = $files_to_save['tmp_name'][$i];
        $size = $files_to_save['size'][$i];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'mp4', 'avi');
        
        if(!in_array($ext, $valid_extensions)) {
            $return["error"] = true;
            $return["error_title"] = 'Extensión no permitida';
            $return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif', 'mp4', 'avi'";
            echo json_encode($return);
            die();
        }
        
        $dateFileName =   date('YmdHis');
        $filename = $id_solicitud_garantia."_".$dateFileName."_".$file;
        $filepath = $path.$filename;
        move_uploaded_file($files_to_save['tmp_name'][$i], $filepath);

        if ($estado_garantia == '1') {
            $mysqli->query("
                UPDATE tbl_garantia_solicitudes
                SET foto_terminado = '$filename'
                WHERE id = $id_solicitud_garantia
            ");
        } else {
            $mysqli->query("
                INSERT INTO tbl_garantia_solicitudes_archivos
                (
                    id_garantia_solicitud,
                    archivo,
                    estado
                )
                VALUES
                (
                    $id_solicitud_garantia,
                    '$filename',
                    1
                )"
            );
        }
    }
}

function sec_garantia_guardar_mantenimiento($id_solicitud_garantia) {
    global $mysqli, $login, $return;

    $sql_garantia = "
        SELECT loc.zona_id, gs.local_id, gs.sistema_id, gsis.nombre as nombre_sistema, gs.reporte, loc.longitud, loc.latitud
        FROM tbl_garantia_solicitudes gs
        INNER JOIN tbl_locales loc ON loc.id = gs.local_id
        INNER JOIN tbl_garantia_sistemas gsis ON gsis.id = gs.sistema_id
        WHERE gs.id = $id_solicitud_garantia
    ";
    $result = $mysqli->query($sql_garantia);
    while ($row = $result->fetch_assoc()) {
        $data_garantia = $row;
    }

    $sql_sistema_nombre = "
        SELECT id
        FROM tbl_solicitud_mantenimiento_sistema
        WHERE nombre LIKE '{$data_garantia['nombre_sistema']}'
    ";
    $result_sistema = $mysqli->query($sql_sistema_nombre);
    while ($row = $result_sistema->fetch_assoc()) {
        $sistema_id_mantenimiento = $row;
    }

    $data_garantia["zona_id"] = ($data_garantia["zona_id"] == '') ? 'NULL' : $data_garantia["zona_id"];
    $insert_command = "
        INSERT INTO tbl_solicitud_mantenimiento
        (
            created_at
            ,user_id
            ,sistema_id
            ,zona_id
            ,local_id
            ,reporte
            ,longitud
            ,latitud
            ,estado
            ,id_garantia
        )
        VALUES
        (
            now()
            ,".$login["id"]."
            ,".$sistema_id_mantenimiento["id"]."
            ,".$data_garantia["zona_id"]."
            ,".$data_garantia["local_id"]."
            ,'DESDE GARANTÍA: ".$data_garantia["reporte"]."'
            ,'".$data_garantia["longitud"]."'
            ,'".$data_garantia["latitud"]."'
            ,'Solicitud'
            ,".$id_solicitud_garantia."
        )
	";
	$mysqli->query($insert_command);
	if($mysqli->error){
        $return['error'] = true;
        $return["error_title"] = '¡Atención!';
        $return['error_msg'] = 'Ha ocurrido un error al derivar a Mantenimiento';
        $return['query'] = $sql_sistema_nombre;
        echo json_encode($return);
		exit();
	}

}

if (isset($_POST["opt"])) {
    if ($_POST["opt"] == "sec_garantia_locales_table") {
        $draw = $_POST['draw'];
        $start = $_POST['start'];
        $length = $_POST['length'];
        $searchValue = $_POST['search']['value'];
        $columnIndex = $_POST['order'][0]['column']; // Column index
	    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir'];
        $totalRecords = 0;
        $totalFiltered = 0;

        $list_where = "";

        if($searchValue != ""){
            $list_where .= " AND (
                loc.id LIKE '%$searchValue%' OR
                loc.cc_id LIKE '%$searchValue%' OR
                loc.nombre LIKE '%$searchValue%'
            ) ";
        }

        // consulta base
        $sql = "
            SELECT
                loc.id,
                loc.cc_id,
                z.nombre as zona_nombre,
                loc.nombre,
                loc.fecha_inicio_garantia,
                loc.fecha_fin_garantia,
                IF(loc.fecha_inicio_garantia IS NOT NULL AND loc.fecha_fin_garantia IS NOT NULL, DATEDIFF(loc.fecha_fin_garantia, CURDATE()), '-') as dias
            FROM
                tbl_locales loc
            LEFT JOIN
	            tbl_zonas z ON (z.id = loc.zona_id)
            WHERE
                loc.operativo = 1
                AND loc.red_id = 1
        ";
        $result = $mysqli->query($sql);
        $totalRecords = $result->num_rows;

        // se cuenta el número de registros después de aplicar la búsqueda
        $sql .= "
                $list_where
        ";
        $result = $mysqli->query($sql);
        $totalFiltered = $result->num_rows;

        // consulta para obtener los datos requeridos
        $limit = "LIMIT $start, $length";
        if ($length == -1) {
            $limit = "";
        }
        $sql .= "
            ORDER BY
                $columnName
                $columnSortOrder
                
            $limit
        ";
        $result = $mysqli->query($sql);

        $btn_editar_fecha = 0;
        if (array_key_exists($menu_id, $usuario_permisos) && in_array("editar_fecha", $usuario_permisos[$menu_id])) {
            $btn_editar_fecha = 1;
        }

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $row['fecha_inicio_garantia'] = (!is_null($row['fecha_inicio_garantia'])) ? date("d-m-Y", strtotime($row['fecha_inicio_garantia'])) : null;
            $row['fecha_fin_garantia'] = (!is_null($row['fecha_fin_garantia'])) ? date("d-m-Y", strtotime($row['fecha_fin_garantia'])) : null;

            $row_opciones = '';
            if ($btn_editar_fecha === 1) {
                $row_opciones .= '
                <button type="button" class="btn btn-rounded btn-default btn-sm btn_modal_editar_fecha"
                    data-id="'.$row['id'].'"
                    data-cc_id="'.$row['cc_id'].'"
                    data-nombre="'.$row['nombre'].'"
                    data-fecha_inicio_garantia="'.$row['fecha_inicio_garantia'].'"
                    data-fecha_fin_garantia="'.$row['fecha_fin_garantia'].'"
                    title="Editar Fechas de Garantía">

                    <span class="glyphicon glyphicon-edit"></span>
                </button>
                ';
            }

            $row_opciones .= '
                <button type="button" class="btn btn-rounded btn-default btn-sm btn_local_nueva_solicitud"
                    data-id="'.$row['id'].'"
                    data-nombre="'.$row['nombre'].'"
                    data-dias="'.$row['dias'].'"
                    title="Nueva solicitud"
                >
                    <span class="glyphicon glyphicon-plus"></span>
                </button>
            ';

            $data[] = array(
                "id" => $row['id'],
                "cc_id" => $row['cc_id'],
                "zona_nombre" => $row['zona_nombre'],
                "nombre" => $row['nombre'],
                "fecha_inicio_garantia" => $row['fecha_inicio_garantia'],
                "fecha_fin_garantia" => $row['fecha_fin_garantia'],
                "dias" => $row['dias'],
                "options" => $row_opciones
            );
        }

        $return['sql'] = $sql;
        $return["draw"] = intval($draw);
        $return["recordsTotal"] = $totalRecords;
        $return["recordsFiltered"] = $totalFiltered;
        $return["data"] = $data;
    }
    else if ($_POST["opt"] == "sec_garantia_cambiar_fechas"){
        $id_local_garantia = $_POST['id_local_garantia'];
        $fecha_inicio_garantia = date('Y-m-d', strtotime($_POST['fecha_inicio_garantia']));
        $fecha_fin_garantia = date('Y-m-d', strtotime($_POST['fecha_fin_garantia']));

        $sql = "
        UPDATE tbl_locales
        SET fecha_inicio_garantia = '$fecha_inicio_garantia', fecha_fin_garantia = '$fecha_fin_garantia'
        WHERE id = $id_local_garantia";
        $mysqli->query($sql);

        if ($mysqli->error) {
            $mysqli->rollback();
            $return['error'] = true;
            $return['message'] = 'Ha ocurrido un error';
            $return['status'] = 404;
            $return['query'] = $sql;
        }
        
    }
    else if ($_POST["opt"] == "sec_garantia_comprobar_disp_local"){
        $local_id = $_POST['id'];

        $sql = " SELECT IFNULL(DATEDIFF(fecha_fin_garantia, CURDATE()), '') AS dias FROM tbl_locales WHERE id = $local_id";
        $result = $mysqli->query($sql);

        while ($row = $result->fetch_assoc()) {
            $num_dias = $row['dias'];
        }
        $return["num_dias"] = $num_dias;
    }
    else if ($_POST["opt"] == "sec_garantia_guardar_solicitud") {
        $data = $_POST;

        // se busca si se aplica la garantía
        $sql = "SELECT IFNULL(DATEDIFF(fecha_fin_garantia, CURDATE()), '') AS dias
        FROM tbl_locales
        WHERE id = " . $data['modal_local_select_id'];
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $num_dias = $row['dias'];
        }
        if ($num_dias == '' || $num_dias <= 0) {
            $return["error"] = true;
            $return["error_title"] = 'No aplica';
            $return["error_msg"] = "La garantía no está disponible para este local";
            echo json_encode($return);
            die();
        }

        $insert_command = "
        INSERT INTO tbl_garantia_solicitudes
            (local_id,
            sistema_id,
            subsistema_id,
            reporte,
            estado,
            tipo_criticidad,
            created_at)
            VALUES
            (".$data['modal_local_select_id'].",
            ".$data['modal_local_select_sistema'].",
            ".$data['modal_local_select_subsistema'].",
            '".$data['modal_local_reporte']."',
            0,
            ".$data['modal_local_select_criticidad'].",
            NOW())
        ";
        $mysqli->query($insert_command);
		if($mysqli->error){
            $return['error'] = true;
            $return["error_title"] = '¡Atención!';
            $return['error_msg'] = 'Ha ocurrido un error';
            $return['query'] = $insert_command;
            echo json_encode($return);
			exit();
		}
		$id_solicitud_garantia = $mysqli->insert_id;

        if ($_FILES['modal_local_files']['error'][0] === 0) {
            sec_garantia_guardar_archivos($_FILES['modal_local_files'], $id_solicitud_garantia, '0');
        }
    }
    else if ($_POST["opt"] == "sec_garantia_solicitudes_table") {
        $draw = $_POST['draw'];
        $start = $_POST['start'];
        $length = $_POST['length'];
        $searchValue = $_POST['search']['value'];
        $columnIndex = $_POST['order'][0]['column']; // Column index
	    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir'];

        $fecha_inicio = isset($_POST["fecha_inicio"])?$_POST["fecha_inicio"]:"";
        $fecha_fin = isset($_POST["fecha_fin"])?$_POST["fecha_fin"]:"";
        $fecha_fin = date("Y-m-d", strtotime($fecha_fin." +1 days"));

        $totalRecords = 0;
        $totalFiltered = 0;

        $list_where = "";

        if($searchValue != ""){
            $list_where .= " AND
                z.nombre LIKE '%$searchValue%' OR
                loc.nombre LIKE '%$searchValue%' OR
                gsis.nombre LIKE '%$searchValue%' OR
                gsubsis.nombre LIKE '%$searchValue%'
            ";
        }

        // consulta base
        $sql = "
            SELECT
                gs.id,
                gs.created_at,
                z.nombre AS nombre_zona,
                loc.nombre AS nombre_local,
                gsis.nombre AS nombre_sistema,
                gsubsis.nombre AS nombre_subsistema,
                gtc.nombre as criticidad,
                gs.reporte,
                ge.nombre as estado
            FROM
                tbl_garantia_solicitudes gs
                LEFT JOIN tbl_garantia_estados ge ON (ge.id = gs.estado)
                LEFT JOIN tbl_garantia_tipo_criticidad gtc ON (gtc.id = gs.tipo_criticidad)
                LEFT JOIN tbl_locales loc ON (loc.id = gs.local_id)
                LEFT JOIN tbl_zonas z ON (z.id = loc.zona_id)
                LEFT JOIN tbl_garantia_sistemas gsis ON (gsis.id = gs.sistema_id)
                LEFT JOIN tbl_garantia_subsistemas gsubsis ON (gsubsis.id = gs.subsistema_id)
            WHERE
                1
        ";

        if($_POST['zona'] != '') {
            $list_where .= "AND z.nombre IN ('".str_replace(",", "','", $_POST["zona"])."')";
        }
        if($_POST['tienda'] != '') {
            $list_where .= "AND loc.nombre IN ('".str_replace(",", "','", $_POST["tienda"])."')";
        }
        if($_POST['estado'] != '') {
            $list_where .= "AND gs.estado IN ('".str_replace(",", "','", $_POST["estado"])."')";
        }
        if($_POST['sistema'] != '') {
            $list_where .= "AND gsis.nombre IN ('".str_replace(",", "','", $_POST["sistema"])."')";
        }
        if($_POST['subsitema'] != '') {
            $list_where .= "AND gsubsis.nombre IN ('".str_replace(",", "','", $_POST["subsitema"])."')";
        }
        if($_POST['criticidad'] != '') {
            $list_where .= "AND gtc.nombre IN ('".str_replace(",", "','", $_POST["criticidad"])."')";
        }

        if ($fecha_inicio != '') {
            $list_where .= " AND gs.created_at >= '$fecha_inicio'";
        }
        if ($fecha_fin != '') {
            $list_where .= " AND gs.created_at < '$fecha_fin'";
        }

        $result = $mysqli->query($sql);
        $totalRecords = $result->num_rows;

        // se cuenta el número de registros después de aplicar la búsqueda
        $sql .= "
                $list_where
        ";
        $result = $mysqli->query($sql);
        $totalFiltered = $result->num_rows;

        // consulta para obtener los datos requeridos
        $limit = "LIMIT $start, $length";
        if ($length == -1) {
            $limit = "";
        }
        $sql .= "
            ORDER BY
                $columnName
                $columnSortOrder
                
            $limit
        ";
        $result = $mysqli->query($sql);

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $options_data = '
                <button type="button" class="btn btn-rounded btn-info btn-sm btn_modal_detalle_solicitud" data-id="'.$row['id'].'" title="Ver">
                    <span class="fa fa-eye"></span> Ver
                </button>
            ';
            $data[] = array(
                "id" => $row['id'],
                "created_at" => date("d-m-Y H:i:s", strtotime($row['created_at'])),
                "nombre_zona" => $row['nombre_zona'],
                "nombre_local" => $row['nombre_local'],
                "nombre_sistema" => $row['nombre_sistema'],
                "nombre_subsistema" => $row['nombre_subsistema'],
                "criticidad" => $row['criticidad'],
                "reporte" => $row['reporte'],
                "estado" => $row['estado'],
                "options" => $options_data,
            );
        }

        $return['sql'] = $sql;

        $return["draw"] = intval($draw);
        $return["recordsTotal"] = $totalRecords;
        $return["recordsFiltered"] = $totalFiltered;
        $return["data"] = $data;
    }
    else if ($_POST["opt"] == "sec_garantia_detalle_solicitud") {
        $id_solicitud_garantia = $_POST['id_solicitud_garantia'];

        $sql = "
            SELECT
                gs.id,
                gs.created_at,
                z.nombre AS nombre_zona,
                loc.nombre AS nombre_local,
                gsis.nombre AS nombre_sistema,
                gsubsis.nombre AS nombre_subsistema,
                gs.tipo_criticidad,
                gs.reporte,
                gs.foto_terminado,
                gs.estado
            FROM
                tbl_garantia_solicitudes gs
                LEFT JOIN tbl_locales loc ON (loc.id = gs.local_id)
                LEFT JOIN tbl_zonas z ON (z.id = loc.zona_id)
                LEFT JOIN tbl_garantia_sistemas gsis ON (gsis.id = gs.sistema_id)
                LEFT JOIN tbl_garantia_subsistemas gsubsis ON (gsubsis.id = gs.subsistema_id)
            WHERE
                gs.id = $id_solicitud_garantia
        ";
        $result = $mysqli->query($sql);

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $row['created_at'] = date("d-m-Y H:i:s", strtotime($row['created_at']));
            $data = $row;
        }

        $command = "
            SELECT id, archivo
            FROM tbl_garantia_solicitudes_archivos
            WHERE id_garantia_solicitud = $id_solicitud_garantia
        ";
        $list_query=$mysqli->query($command);
        $imagenes = array();
        while ($li=$list_query->fetch_assoc()) {
            $imagenes[]=$li;
        }
        $return["data"] = $data;
        $return["imagenes"] = $imagenes;
    }
    else if ($_POST['opt'] == "sec_garantia_actualizar_solicitud") {
        $id_solicitud_garantia = $_POST['modal_solicitud_id'];
        $estado_garantia = $_POST['modal_solicitud_estado'];
        $criticidad_garantia = $_POST['modal_solicitud_criticidad'];

        if ($estado_garantia == 3) {
            sec_garantia_guardar_mantenimiento($id_solicitud_garantia);
        }

        $sql = "
            UPDATE tbl_garantia_solicitudes
            SET
                estado = $estado_garantia,
                tipo_criticidad = $criticidad_garantia,
                updated_at = NOW()
            WHERE id = $id_solicitud_garantia
        ";
        $mysqli->query($sql);
		if($mysqli->error){
            $return['error'] = true;
            $return["error_title"] = '¡Atención!';
            $return['error_msg'] = 'Ha ocurrido un error al actualizar la información';
            $return['query'] = $sql;
            echo json_encode($return);
			exit();
		}

        if ($_FILES['modal_solicitud_files']['error'][0] === 0) {
            sec_garantia_guardar_archivos($_FILES['modal_solicitud_files'], $id_solicitud_garantia, $estado_garantia);
        }
    }
    else if ($_POST['opt'] == "sec_garantia_select_subsistema") {
        $id_sistema = $_POST['id'];

        $sql = "
            SELECT
                id,
                nombre
            FROM
                tbl_garantia_subsistemas
            WHERE
                id_sistema = '$id_sistema'
                AND estado = 1
        ";
        $result = $mysqli->query($sql);
        $return['sql'] = $sql;
        $return['option_subsistema'] = "";
        $return['option_subsistema'] = "<option value='none' selected disabled>--- Seleccione Subsistema</option>";
        foreach ($result as $val) {            
            $return['option_subsistema'] .= "<option value='" . $val['id'] . "'>" . $val['nombre'] . "</option>";
        }
    }
    else if ($_POST['opt'] == "sec_garantia_derivar_a_mantenimiento") {
        $id_solicitud_garantia = $_POST['id_solicitud'];

        sec_garantia_guardar_mantenimiento($id_solicitud_garantia);
        
        $sql = "
            UPDATE tbl_garantia_solicitudes
            SET
                estado = 3,
                updated_at = NOW()
            WHERE id = $id_solicitud_garantia
        ";
        $mysqli->query($sql);
		if($mysqli->error){
            $return['error'] = true;
            $return["error_title"] = '¡Atención!';
            $return['error_msg'] = 'Ha ocurrido un error al actualizar la información';
            $return['query'] = $sql;
            echo json_encode($return);
			exit();
		}
    }
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
echo json_encode($return);
?>