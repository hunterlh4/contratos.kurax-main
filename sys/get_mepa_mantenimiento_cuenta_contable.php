<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_mantenimiento_cuenta_contable_listar")
{

    $empresa_id = $_POST["empresa_id"];
    $estado_id = $_POST["estado_id"];

    $where_empresa = "";
    $where_estado = "";

    if($estado_id != ""){
        $where_estado = " AND cc.status = $estado_id ";
    }

    if($empresa_id != 0){
        $where_empresa = " AND cc.id_tipos_solicitud = $empresa_id ";
    }

    $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'razon_social' LIMIT 1")->fetch_assoc();
    $menu_permiso = $menu_id_consultar["id"];
    
    //  Condición para ver redes dependiendo de los permisos de locales

    $usuario_id = $login?$login['id']:null;
    $where_local_id = "";
    if($login["usuario_locales"]){
        $where_local_id = " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
        }


    $where_redes = "";

    $select_red =
    "
        SELECT
            l.red_id
        FROM tbl_locales l
        WHERE l.estado = 1 AND l.red_id IS NOT NULL
        $where_local_id
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);

    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
            if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_redes = " lr.id IN ($ids_data_select_red) ";
    }


    //-----------------------------------------------------------------------------------
	$query = "
        SELECT
            cc.id,
            cc.nombre AS nombre,
            cc.codigo,
            IFNULL(cc.cuenta_contable, '') AS cuenta_contable, 
            cc.status,
            rs.nombre AS empresa_at
        FROM mepa_tipo_documento cc
        LEFT JOIN tbl_razon_social rs
        ON cc.id_tipos_solicitud = rs.id
        LEFT JOIN tbl_locales_redes lr
        ON rs.red_id = lr.id
        WHERE -- (rs.id = 14 OR rs.nombre = '30')
        $where_redes
        $where_estado
        $where_empresa
        ORDER BY cc.created_at ASC
	";

	$list_query = $mysqli->query($query);

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{

        $estadoHTML = ($reg->status == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';


        $botones_accion = "";

        if($reg->status == 1){
            $botones_accion = '<a onclick="sec_mepa_mantenimiento_cuenta_contable_obtener('.$reg->id.');";
                                    class="btn btn-warning btn-sm"
                                    data-toggle="tooltip" data-placement="top" title="Editar">
                                    <span class="fa fa-pencil"></span>
                                </a>
                                <a onclick="sec_mepa_mantenimiento_cuenta_contable_eliminar('.$reg->id.');" 
                                    class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Eliminar"> 
                                    <span class="fa fa-trash"></span>
                                </a>';
        }

        $botones = '<a onclick="sec_mepa_mantenimiento_cuenta_contable_ver('.$reg->id.');";
                        class="btn btn-info btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Ver">
                        <span class="fa fa-eye"></span>
                    </a>
                   '.$botones_accion;                

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->codigo,
            "3" => $reg->cuenta_contable,
			"4" => $reg->empresa_at,
			"5" => $estadoHTML,
			"6" => $botones
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_mantenimiento_cuenta_contable_listar_empresa") {
    try {

        //  Condición para ver redes dependiendo de los permisos de locales
            $usuario_id = $login?$login['id']:null;
            $where_local_id = "";
            if($login["usuario_locales"]){
                $where_local_id = " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
                }


            $where_redes = "";

            $select_red =
            "
                SELECT
                    l.red_id
                FROM tbl_locales l
                WHERE l.estado = 1 AND l.red_id IS NOT NULL
                $where_local_id
                GROUP BY l.red_id
            ";

            $data_select_red = $mysqli->query($select_red);

            $row_count_data_select_red = $data_select_red->num_rows;

            $ids_data_select_red = '';
            $contador_ids = 0;
            
            if ($row_count_data_select_red > 0) 
            {
                while ($row = $data_select_red->fetch_assoc()) 
                {
                    if ($contador_ids > 0) 
                    {
                        $ids_data_select_red .= ',';
                    }

                    $ids_data_select_red .= $row["red_id"];           
                    $contador_ids++;
                }

                $where_redes = " AND red_id IN ($ids_data_select_red) ";
            }


            //-----------------------------------------------------------------------------------

        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre AS nombre
            FROM tbl_razon_social
            WHERE status = 1 AND nombre IS NOT NULL
            $where_redes
            ORDER BY nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}
if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_mantenimiento_cuenta_contable_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    za.id, 
                    za.nombre,
                    za.codigo,
                    za.cuenta_contable,
                    za.id_tipos_solicitud AS empresa_id,
                    IFNULL(za.created_at, '') AS created_at,
                    IFNULL(za.updated_at, '') AS updated_at,
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM mepa_tipo_documento za
                LEFT JOIN tbl_usuarios u ON u.id=za.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id=za.user_updated_id
                WHERE za.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $zona_id, 
                                $nombre, 
                                $codigo,
                                $cuenta_contable,
                                $empresa_id,
                                $created_at,
                                $updated_at,
                                $usuario_create,
                                $usuario_update);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $zona_id,
                        'nombre' => $nombre,
                        'codigo' => $codigo,
                        'cuenta_contable' => $cuenta_contable,
                        'empresa_id' => $empresa_id,                    
                        'created_at' =>  $created_at,
                        'updated_at' => $updated_at,
                        'usuario_create' => $usuario_create,
                        'usuario_update' => $usuario_update
                    ],
                ]);
            } else {
                echo json_encode([
                    'status' => 404,
                    'message' => 'No se encontraron datos para el ID proporcionado.',
                ]);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                'status' => 500,
                'message' => 'Error en la consulta SQL: ' . $e->getMessage(),
            ]);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'ID no válido']);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_mantenimiento_cuenta_contable_empresa_listar") {
    try {

        //  Condición para ver redes dependiendo de los permisos de locales

        $usuario_id = $login?$login['id']:null;
        $where_local_id = "";
        if($login["usuario_locales"]){
		    $where_local_id = " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
			}


        $where_redes = "";
    
        $select_red =
        "
            SELECT
                l.red_id
            FROM tbl_locales l
            WHERE l.estado = 1 AND l.red_id IS NOT NULL
            $where_local_id
            GROUP BY l.red_id
        ";
    
        $data_select_red = $mysqli->query($select_red);
    
        $row_count_data_select_red = $data_select_red->num_rows;
    
        $ids_data_select_red = '';
        $contador_ids = 0;
        
        if ($row_count_data_select_red > 0) 
        {
            while ($row = $data_select_red->fetch_assoc()) 
            {
                if ($contador_ids > 0) 
                {
                    $ids_data_select_red .= ',';
                }
    
                $ids_data_select_red .= $row["red_id"];           
                $contador_ids++;
            }
    
            $where_redes = " WHERE lr.id IN ($ids_data_select_red) ";
        }
    
    
        //-----------------------------------------------------------------------------------

        $stmt = $mysqli->prepare("
        SELECT
                rs.id, rs.nombre
            FROM tbl_razon_social rs
            LEFT JOIN tbl_locales_redes lr ON rs.red_id = lr.id
            $where_redes
            ORDER BY rs.nombre ASC;
        ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta.");
        }

        $stmt->execute();
        $list = [];
        $result_set = $stmt->get_result();

        while ($li = $result_set->fetch_assoc()) {
            $list[] = $li;
        }

        if ($mysqli->error) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }

        $result = [];
        if (count($list) == 0) {
            $result["http_code"] = 400;
            $result["result"] = "El usuario no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
            $result["result"] = $list;
        }

        echo json_encode($result);
        exit();
    } catch (Exception $e) {
        $result = [
            "consulta_error" => "Error en la consulta. Comunicarse con Soporte.",
            "http_code" => 500,
            "error_message" => $e->getMessage()
        ];
        echo json_encode($result);
        exit();
    }
}