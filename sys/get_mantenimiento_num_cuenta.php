<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//-----------  Cuentas contables

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_cuenta_bancaria_listar")
{

    $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'num_cuenta' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];

    //--------------- Filtro de permiso de redes
    $where_local_id = "";
    if($login && $login["usuario_locales"]){
        $where_local_id = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
        
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

        $where_redes = " AND rz.red_id IN ($ids_data_select_red) ";
    }

    //------------------------------
    
	$query = "
		SELECT
			nc.id,
		    COALESCE(c.nombre,'') AS canal,
		    rz.nombre AS empresa,
		    COALESCE(b.nombre,'') AS banco,
		    COALESCE(nc.num_cuenta_corriente,'') AS num_cuenta_corriente,
		    nc.subdiario,
		    COALESCE(m.nombre,'') AS moneda,
		    nc.num_cuenta_contable,
		    COALESCE(nc.cod_anexo,'') AS cod_anexo,
		    COALESCE(tp.nombre,'') AS tipo_pago
		FROM cont_num_cuenta nc
			LEFT JOIN tbl_canales_at c
			ON nc.canal_id = c.id
			LEFT JOIN tbl_razon_social rz
			ON nc.razon_social_id = rz.id
			LEFT JOIN tbl_bancos b
			ON nc.banco_id = b.id
			LEFT JOIN tbl_moneda m
			ON nc.moneda_id = m.id
			LEFT JOIN cont_tipo_programacion tp
			ON nc.tipo_pago_id = tp.id
		WHERE nc.status = 1
        $where_redes
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
        $botones = '
                    <a onclick="sec_mantenimiento_num_cuenta_obtener_cuenta_bancaria('.$reg->id.');";
                        class="btn btn-warning btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Editar">
                        <span class="fa fa-pencil"></span>
                    </a>
                    <a onclick="sec_mantenimiento_num_cuenta_obtener_historico_cambios('.$reg->id.');";
                        class="btn btn-primary btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Historial de cambios">
                        <span class="fa fa-history"></span>
                    </a>';

        if(in_array("delete", $usuario_permisos[$menu_permiso])):
            $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="sec_mantenimiento_num_cuenta_inactivar(' . $reg->id . ')"
                            title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </a>
                        ';
            endif;
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->canal,
			"2" => $reg->empresa,
			"3" => $reg->banco,
			"4" => $reg->num_cuenta_corriente,
			"5" => $reg->subdiario,
			"6" => $reg->moneda,
			"7" => $reg->num_cuenta_contable,
			"8" => $reg->cod_anexo,
			"9" => $reg->tipo_pago,
			"10" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_cuenta_bancaria_obtener")
{
	$param_cuenta_bancaria_id = $_POST["param_cuenta_bancaria_id"];
	
	$query = 
	"
		SELECT
			nc.id,
		    c.id AS canal_id,
		    c.nombre AS canal,
		    rz.id AS empresa_id,
		    rz.nombre AS empresa,
		    COALESCE(b.id,0) AS banco_id,
			COALESCE(b.nombre,'') AS banco,
		    COALESCE(nc.num_cuenta_corriente,'') AS num_cuenta_corriente,
		    nc.subdiario,
		    COALESCE(m.id,0) AS moneda_id,
		    COALESCE(m.nombre,'') AS moneda,
		    nc.num_cuenta_contable,
            nc.num_cuenta_contable_haber,
			COALESCE(nc.cod_anexo,'') AS cod_anexo,
		    COALESCE(tp.id,0) AS tipo_pago_id,
			COALESCE(nc.cont_num_cuenta_proceso_id,0) AS proceso_id,
			COALESCE(tp.nombre,'') AS tipo_pago
		FROM cont_num_cuenta nc
			LEFT JOIN tbl_canales_at c
			ON nc.canal_id = c.id
			LEFT JOIN tbl_razon_social rz
			ON nc.razon_social_id = rz.id
			LEFT JOIN tbl_bancos b
			ON nc.banco_id = b.id
			LEFT JOIN tbl_moneda m
			ON nc.moneda_id = m.id
			LEFT JOIN cont_tipo_programacion tp
			ON nc.tipo_pago_id = tp.id
		WHERE nc.id = {$param_cuenta_bancaria_id}
		LIMIT 1
	";

	$list_query = $mysqli->query($query);
	
	$lista_datos = array();

	while ($li = $list_query->fetch_assoc())
	{
		$lista_datos[] = $li;
	}
	
	if ($mysqli->error)
	{
		$result["error"] = $mysqli->error;

	}
	if (count($lista_datos) == 0)
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	elseif (count($lista_datos) > 0)
	{
		$result["http_code"] = 200;
		$result["titulo"] = "OK";
		$result["descripcion"] = $lista_datos;
	}
	else
	{
		$result["http_code"] = 400;
		$result["titulo"] = "Error";
		$result["descripcion"] = "Ocurrió un error al obtener el registro";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_cuenta_bancaria_obtener_subdiario_descripcion")
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


//---------- Historico de cambios de cuentas contables

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_cuenta_bancaria_campo_listar") {
    try {

        $stmt = $mysqli->prepare("
                                SELECT campo AS id, nombre_campo AS nombre
                                FROM cont_num_cuenta_campos
                                WHERE status = 1;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_cuenta_bancaria_obtener_historico") {
    $cuenta_id = $_POST['cuenta_id'];
    $campo_id = $_POST['campo_id'];

    $where_campo = "";
    if($campo_id != ""){
        $where_campo .= " AND nch.nombre_campo = '".$campo_id."' ";


    }
    try {

        $selectQuery = " SELECT 
                            nch.id,
                            nch.valor_anterior,
                            nch.valor_nuevo,
                            ncc.nombre_campo,
                            nch.fecha_registro,
                            u.usuario
                        FROM cont_num_cuenta_historial_cambios nch
                        LEFT JOIN tbl_usuarios u ON nch.usuario_id = u.id
                        LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
                        LEFT JOIN cont_num_cuenta_campos ncc ON ncc.campo = nch.nombre_campo
                        WHERE nch.status =1 AND nch.cont_num_cuenta_id = ?
                        $where_campo 
                        ORDER BY nch.fecha_registro DESC" ; 
        //echo $selectQuery;
        $stmt = $mysqli->prepare($selectQuery);

        $stmt->bind_param("i", $cuenta_id);
        $stmt->execute();
        $stmt->bind_result($id, $valor_anterior, $valor_nuevo, $nombre_campo, $fecha_registro, $usuario);

        $data = [];

        while ($stmt->fetch()) {

            $data[] = [
                "0" => count($data) + 1,
                "1" => $valor_anterior,
                "2" => $valor_nuevo,
                "3" => $nombre_campo,
                "4" => $fecha_registro,
                "5" => $usuario
            ];
        }

        $stmt->close();

        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data,
        ];

        echo json_encode($resultado);
    } catch (Exception $e) {
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => [
                [
                    "0" => "error",
                    "1" => 'Comunicarse con Soporte, error: ' . $e->getMessage(),
                    "2" => '',
                    "3" => '',
                    "4" => '',
                    "5" => ''
                ],
            ],
        ];

        echo json_encode($resultado);
    }
}


//-----------  Subdiarios
if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_subdiarios_datatable") {

    $query = "
			SELECT 
			ncs.id, 
			ncs.cod_operacion,
			ncs.descripcion,
			ncs.status,
			ncs.created_at,
            u.usuario AS usuario_creador,
            ncs.updated_at,
            uu.usuario AS usuario_modificador
		FROM cont_num_cuenta_subdiario ncs
        LEFT JOIN tbl_usuarios u
        ON ncs.user_created_id = u.id
        LEFT JOIN tbl_usuarios uu
        ON ncs.user_updated_id = uu.id
		WHERE ncs.status = '1'
		ORDER BY ncs.cod_operacion ASC;
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $list_query = $stmt->get_result();
    $stmt->close();

    $data = [];

    if ($mysqli->error) {
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => '', 
            "5" => '', 
            "6" => '', 
            "7" => '', 
            "8" => ''
        ];

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_assoc()) {
			$estadoHTML = ($reg['status'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

            $botones = '<a class="btn btn-rounded btn-danger btn-xs" data-toggle="tooltip" data-placement="top"
                                    onclick="sec_mantenimiento_num_cuenta_eliminar_subdiario(' . $reg['id'] . ')"
                                    title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </a>
								<button type=button" class="btn btn-rounded btn-primary btn-xs" onclick="sec_mantenimiento_num_cuenta_obtener_subdiario('.$reg['id'].')">
                                        <i class="fa fa-pencil"></i>												
                                    </button>';

            $data[] = [
                "0" => $cont,
                "1" => $reg['cod_operacion'],
                "2" => $reg['descripcion'],
                "3" => $estadoHTML,
                "4" => $reg['created_at'],
                "5" => $reg['usuario_creador'],
                "6" => $reg['updated_at'],
                "7" => $reg['usuario_modificador'],
                "8" => $botones
            ];

            $cont++;
        }

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
    }

    echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_subdiarios_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    id, 
                    cod_operacion,
                    descripcion
                FROM cont_num_cuenta_subdiario 
                WHERE status = '1' AND id=?
                ORDER BY descripcion ASC
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($subdiario_id, $cod_operacion, $descripcion);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $subdiario_id,
                        'cod_operacion' => $cod_operacion,
                        'descripcion' => $descripcion
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_subdiario_verificar")
{
	$cod_operacion = $_POST["cod_operacion"];
	$id_subdiario = $_POST["id_subdiario"];

	// Consultar si el número de recibo ya fue registrado
	$selectQuery = "
					SELECT 
						sd.cod_operacion,
						sd.descripcion,
						u.usuario
					FROM cont_num_cuenta_subdiario sd
					LEFT JOIN tbl_usuarios u
					ON sd.user_created_id = u.id
					LEFT JOIN tbl_personal_apt p
					ON p.id = u.personal_id
					WHERE sd.status = '1' AND sd.id<> ? AND sd.cod_operacion=?
					ORDER BY sd.cod_operacion ASC
					LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_subdiario, $cod_operacion);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($cod_operacion, $descripcion, $usuario);
		$selectStmt->fetch();

		$result["http_code"] = 200;
		$result["titulo"] = "Código de operación: ". $cod_operacion ." ya fue registrado con la descripción:".$descripcion." por el usuario:". $usuario;
	}else{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	
	echo json_encode($result);
	exit();
}

//-----------  Procesos

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_proceso_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT cp.id, cp.nombre
            FROM cont_num_cuenta_proceso AS cp 
            WHERE cp.status = 1
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


if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_proceso_datatable") {
    $query = "
        SELECT 
            ncp.id,
            ncp.nombre,
            ncp.descripcion,
            ncp.status,
            ncp.created_at,
            u.usuario AS usuario_creador,
            ncp.updated_at,
            uu.usuario AS usuario_modificador
        FROM cont_num_cuenta_proceso ncp
        LEFT JOIN tbl_usuarios u
        ON ncp.user_created_id = u.id
        LEFT JOIN tbl_usuarios uu
        ON ncp.user_updated_id = uu.id
        WHERE ncp.status = 1;
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $list_query = $stmt->get_result();
    $stmt->close();

    $data = [];

    if ($mysqli->error) {
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => '', 
            "5" => '',
            "6" => '', 
            "7" => '', 
            "8" => ''
        ];

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_assoc()) {
			$estadoHTML = ($reg['status'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            $botones = '<a class="btn btn-rounded btn-danger btn-xs" data-toggle="tooltip" data-placement="top"
                            onclick="sec_mantenimiento_num_cuenta_eliminar_proceso(' . $reg['id'] . ')"
                            title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </a>
                        <button type=button" class="btn btn-rounded btn-primary btn-xs" onclick="sec_mantenimiento_num_cuenta_obtener_proceso('.$reg['id'].')">
                                <i class="fa fa-pencil"></i>												
                            </button>';
            $data[] = [
                "0" => $cont,
                "1" => $reg['nombre'],
                "2" => $reg['descripcion'],
                "3" => $estadoHTML,
                "4" => $reg['created_at'],
                "5" => $reg['usuario_creador'],
                "6" => $reg['updated_at'],
                "7" => $reg['usuario_modificador'],
                "8" => $botones
            ];

            $cont++;
        }

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
    }

    echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_proceso_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                ncp.id,
                ncp.nombre,
                ncp.descripcion
                FROM cont_num_cuenta_proceso ncp
                WHERE ncp.status = 1 AND ncp.id = ?
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($proceso_id, $nombre, $descripcion);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $proceso_id,
                        'nombre' => $nombre,
                        'descripcion' => $descripcion
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_proceso_validar")
{
	$nombre = $_POST["nombre"];
	$id_proceso = $_POST["id_proceso"];

	$selectQuery = "
            SELECT 
            ncp.id,
            ncp.descripcion,
            u.usuario
        FROM cont_num_cuenta_proceso ncp
        INNER JOIN tbl_usuarios u
        ON ncp.user_created_id = u.id
        WHERE ncp.status = 1 AND ncp.id<> ? AND ncp.nombre= ?
        ORDER BY ncp.nombre ASC
        LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_proceso, $nombre);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($cod_operacion, $descripcion, $usuario);
		$selectStmt->fetch();

		$result["http_code"] = 200;
		$result["titulo"] = "El nombre del proceso: ". $nombre ." ya fue registrado con la descripción:".$descripcion." por el usuario:". $usuario;
	}else{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	
	echo json_encode($result);
	exit();
}


//-----------  Tipo de pago


if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_tipo_pago_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT cp.id, cp.nombre
            FROM cont_tipo_programacion AS cp 
            WHERE cp.status = 1
            ORDER BY cp.nombre ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_tipo_pago_datatable") {
    $query = "
        SELECT 
            ncp.id,
            ncp.nombre,
            ncp.status,
            ncp.created_at,
            u.usuario AS usuario_creador,
            ncp.updated_at,
            uu.usuario AS usuario_modificador
        FROM cont_tipo_programacion ncp
        LEFT JOIN tbl_usuarios u
        ON ncp.user_created_id = u.id
        LEFT JOIN tbl_usuarios uu
        ON ncp.user_updated_id = uu.id
        WHERE ncp.status = 1;
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $list_query = $stmt->get_result();
    $stmt->close();

    $data = [];

    if ($mysqli->error) {
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => '',
            "5" => '',
            "6" => '',
            "7" => ''
        ];

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_assoc()) {
			$estadoHTML = ($reg['status'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            $botones = '<a class="btn btn-rounded btn-danger btn-xs" data-toggle="tooltip" data-placement="top"
                            onclick="sec_mantenimiento_num_cuenta_eliminar_tipo_pago(' . $reg['id'] . ')"
                            title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </a>
                        <button type=button" class="btn btn-rounded btn-primary btn-xs" onclick="sec_mantenimiento_num_cuenta_obtener_tipo_pago('.$reg['id'].')">
                                <i class="fa fa-pencil"></i>												
                            </button>';
            $data[] = [
                "0" => $cont,
                "1" => $reg['nombre'],
                "2" => $estadoHTML,
                "3" => $reg['created_at'],
                "4" => $reg['usuario_creador'],
                "5" => $reg['updated_at'],
                "6" => $reg['usuario_modificador'],
                "7" => $botones
            ];

            $cont++;
        }

        $resultado = [
            "sEcho" => 1,
            "iTotalREcords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
    }

    echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_tipo_pago_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                ncp.id,
                ncp.nombre
                FROM cont_tipo_programacion ncp
                WHERE ncp.status = 1 AND ncp.id = ?
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($tipo_pago_id, $nombre);

            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $tipo_pago_id,
                        'nombre' => $nombre
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_tipo_pago_validar")
{
	$nombre = $_POST["nombre"];
	$id_tipo_pago = $_POST["id_tipo_pago"];

	$selectQuery = "
            SELECT 
            ncp.id,
            u.usuario
        FROM cont_tipo_programacion ncp
        LEFT JOIN tbl_usuarios u
        ON ncp.user_created_id = u.id
        WHERE ncp.status = 1 AND ncp.id<> ? AND ncp.nombre= ?
        ORDER BY ncp.nombre ASC
        LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_tipo_pago, $nombre);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($cod_operacion, $usuario);
		$selectStmt->fetch();

		$result["http_code"] = 200;
		$result["titulo"] = "El nombre del tipo de pago: ". $nombre ." ya fue registrado por el usuario:". $usuario;
	}else{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	
	echo json_encode($result);
	exit();
}


//-----------  Tipo de pago

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_moneda_listar") {
    try {

        $stmt = $mysqli->prepare("
                SELECT
                id, nombre
            FROM tbl_moneda
            WHERE estado = 1
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_banco_listar") {
    try {

        $stmt = $mysqli->prepare("
                        SELECT
                        id, nombre
                    FROM tbl_bancos
                    WHERE estado = 1
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_empresa_listar") {
    try {

        //--------------- Filtro de permiso de redes
        $where_local_id = "";

        if($login && $login["usuario_locales"]){
            $where_local_id = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
            
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

        //------------------------------
        $stmt = $mysqli->prepare("
                            SELECT
                            id, nombre
                        FROM tbl_razon_social
                        WHERE status = 1
                        $where_redes
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

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_canal_listar") {
    try {

        $stmt = $mysqli->prepare("
                                SELECT
                                id, nombre
                            FROM tbl_canales_at
                            WHERE status = 1
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