<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA RAZON SOCIAL

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_razon_social_listar")
{

    $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'razon_social' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];

	$query = "
        SELECT
            rz.id,
            rz.nombre AS empresa,
            COALESCE(c.nombre,'') AS canal,
            rz.subdiario,
            lr.nombre AS red,
            rz.ruc
        FROM tbl_razon_social rz
            LEFT JOIN tbl_canales_at c
            ON rz.canal_id = c.id
            LEFT JOIN tbl_locales_redes lr
            ON rz.red_id = lr.id
        WHERE rz.status = 1
        ORDER BY rz.nombre ASC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{

        $botones = '<a onclick="sec_mantenimiento_razon_social_ver('.$reg->id.');";
                        class="btn btn-info btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Ver">
                        <span class="fa fa-eye"></span>
                    </a>
                    <a onclick="sec_mantenimiento_razon_social_obtener('.$reg->id.');";
                        class="btn btn-warning btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Editar">
                        <span class="fa fa-pencil"></span>
                    </a>';
                    
        if(in_array("delete", $usuario_permisos[$menu_permiso])):
                $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                        onclick="sec_mantenimiento_razon_social_eliminar(' . $reg->id . ')"
                        title="Eliminar">
                        <i class="fa fa-trash"></i>
                    </a>
                    ';
         endif;

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->empresa,
			"2" => $reg->canal,
			"3" => $reg->red,
			"4" => $reg->ruc,
			"5" => $reg->subdiario,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_razon_social_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    rs.id, 
                    rs.nombre,
                    rs.subdiario,
                    rs.codigo_empresa,
                    IFNULL(rs.estado_vale,0),
                    rs.ruc,
                    IFNULL(rs.canal_id,0),
                    IFNULL(rs.red_id,0),
                    rs.estado_tesoreria,
                    rs.subdiario_contabilidad,
                    rs.codigo_sap,
                    rs.subdiario_compra_con_igv,
                    rs.subdiario_compra_sin_igv,
                    rs.subdiario_cancelacion_caja_chica,
                    IFNULL(rs.permiso_servicios_publicos,0),
                    IFNULL(rs.habilitado_prestamo_boveda,0),
                    IFNULL(rs.habilitado_recargas_kasnet,0),
                    IFNULL(rs.created_at, '') AS created_at,
                    IFNULL(rs.updated_at, '') AS updated_at,
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update
                FROM tbl_razon_social rs
                LEFT JOIN tbl_usuarios u ON u.id=rs.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id=rs.user_updated_id
                WHERE rs.status = '1' AND rs.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $razon_social_id, 
                                $nombre, 
                                $subdiario,
                                $codigo_empresa,
                                $estado_vale,
                                $ruc,
                                $canal_id,
                                $red_id,
                                $estado_tesoreria,
                                $subdiario_contabilidad,
                                $codigo_sap,
                                $subdiario_compra_con_igv,
                                $subdiario_compra_sin_igv,
                                $subdiario_cancelacion_caja_chica,
                                $permiso_servicios_publicos,
                                $habilitado_prestamo_boveda,
                                $habilitado_recargas_kasnet,
                                $created_at,
                                $updated_at,
                                $usuario_create,
                                $usuario_update);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $razon_social_id,
                        'nombre' => $nombre,
                        'subdiario' => $subdiario,
                        'codigo_empresa' => $codigo_empresa,
                        'estado_vale' => $estado_vale,
                        'ruc' => $ruc,
                        'canal_id' => $canal_id,
                        'red_id' => $red_id,
                        'estado_tesoreria' => $estado_tesoreria,
                        'subdiario_contabilidad' => $subdiario_contabilidad,
                        'codigo_sap' => $codigo_sap,
                        'subdiario_compra_con_igv' => $subdiario_compra_con_igv,
                        'subdiario_compra_sin_igv' => $subdiario_compra_sin_igv,
                        'subdiario_cancelacion_caja_chica' => $subdiario_cancelacion_caja_chica,
                        'permiso_servicios_publicos' => $permiso_servicios_publicos,
                        'habilitado_prestamo_boveda' => $habilitado_prestamo_boveda,
                        'habilitado_recargas_kasnet' => $habilitado_recargas_kasnet,
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

//////////   FUNCIONES PARA SUBDIARIOS

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_razon_social_obtener_subdiario_descripcion")
{
	try{
		$subdiario = $_POST["subdiario"];
		$selectQuery = "SELECT 
							descripcion AS nombre
						FROM cont_num_cuenta_subdiario 
						WHERE status = '1' AND cod_operacion = ? 
						ORDER BY descripcion ASC LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("s", $subdiario);
		$selectStmt->execute();
		$selectStmt->store_result();

		if ($selectStmt->num_rows > 0) {
			$selectStmt->bind_result($descripcion);
			$selectStmt->fetch();

			$result["http_code"] = 200;
			$result["descripcion"] = $descripcion;
		}else{
			$result["http_code"] = 400;
			$result["titulo"] = "Alerta";
			$result["descripcion"] = "Sin descripción";
		}
		$selectStmt->close();
		echo json_encode($result);
		exit();
	} catch (Exception $e) {
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
}

//////////   FUNCIONES PARA CANALES Y REDES

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimientorazon_social_canal_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_canales_at
            WHERE status = 1
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimientorazon_social_red_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_locales_redes
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