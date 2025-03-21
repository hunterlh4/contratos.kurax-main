<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

//////////   FUNCIONES PARA COMPROBANTES

if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_pago_listar")
{
    $usuario_id = $login ? $login['id'] : null;

    // Obtener permisos

    $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'comprobante' AND sub_sec_id = 'pago' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];

    $proveedor_id = $_POST['proveedor_id'];
	$razon_social_id = $_POST['razon_social_id'];
	$etapa_id = $_POST['etapa_id'];
    $estado_id = $_POST['estado_id'];
	$fecha_inicio_registro = $_POST['fecha_inicio_registro'];
	$fecha_fin_registro = $_POST['fecha_fin_registro'];
	$fecha_inicio_emision = $_POST['fecha_inicio_emision'];
	$fecha_fin_emision = $_POST['fecha_fin_emision'];

    //  FILTROS

        $where_proveedor="";
        $where_razon_social="";
        $where_etapa="";
        $where_estado="";
        $where_creador="";

        if(in_array("btn_comp_ver_todo", $usuario_permisos[$menu_permiso])){

            $where_creador = "";
        }else{
            if(in_array("btn_comp_registrar", $usuario_permisos[$menu_permiso])){

                $where_creador = " AND c.user_created_id = '".$usuario_id."' ";
            }
        }

        if (!Empty($proveedor_id)){
            $where_proveedor = " AND c.proveedor_id = '".$proveedor_id."' ";
        }

        if (!Empty($razon_social_id)){
            $where_razon_social = " AND c.razon_social_id = '".$razon_social_id."' ";
        }

        if (!Empty($etapa_id)){
            $where_etapa = " AND c.etapa_id = '".$etapa_id."' ";
        }

        if ($estado_id != ""){
            $where_estado = " AND c.status = '".$estado_id."' ";
        }
     
        $where_fecha_emision= "";
        $where_fecha_registro ="";

        if (!Empty($fecha_inicio_registro) && !Empty($fecha_fin_registro)) {
            $where_fecha_registro = " AND c.created_at BETWEEN '$fecha_inicio_registro 00:00:00' AND '$fecha_fin_registro 23:59:59'";
            } elseif (!Empty($fecha_inicio_registro)) {
                $where_fecha_registro = " AND c.created_at >= '$fecha_inicio_registro 00:00:00'";
            } elseif (!Empty($fecha_fin_registro)) {
                $where_fecha_registro = " AND c.created_at <= '$fecha_fin_registro 23:59:59'";
        }

        if (!Empty($fecha_inicio_emision) && !Empty($fecha_fin_emision)) {
                $where_fecha_emision = " AND c.created_at BETWEEN '$fecha_inicio_emision 00:00:00' AND '$fecha_fin_emision 23:59:59'";
            } elseif (!Empty($fecha_inicio_emision)) {
                $where_fecha_emision = " AND c.created_at >= '$fecha_inicio_emision 00:00:00'";
            } elseif (!Empty($fecha_fin_emision)) {
                $where_fecha_emision = " AND c.created_at <= '$fecha_fin_emision 23:59:59'";
        }

    //  INICIO PERMISOS DE ESTADOS

        $selectQueryPermisoEtapa = "SELECT 
                                    ce.id,
                                    ce.nombre,
                                    ce.permiso
                                FROM tbl_comprobante_etapa ce
                                WHERE ce.status = 1
                                ";

        $stmtPermisoEtapa = $mysqli->prepare($selectQueryPermisoEtapa);
        $stmtPermisoEtapa->execute();
        $stmtPermisoEtapa->bind_result($id_etapa, $nombre_etapa, $permiso_etapa);

        $etapas = "1";
        $etapasArray = [];

        while ($stmtPermisoEtapa->fetch()) {
            if (in_array($permiso_etapa, $usuario_permisos[$menu_permiso])) {
            $etapasArray[] = $id_etapa;
            }
        }


        if(!empty($etapasArray)){

            // Condiciones de etapa

            foreach ($etapasArray as $etapa) {
                if ($etapa > 1) {
                    if ($etapa==5) {
                        $etapasArray[] = $etapa - 2;
            
                    }else{
                        $etapasArray[] = $etapa - 1;
                        }
                    }
                }

            // Filtrar duplicados y ordenar
            $etapasArray = array_unique($etapasArray);
            sort($etapasArray);

            // Si existe la etapa 1, incluir la etapa 4
                
            if (in_array(1, $etapasArray)) {
                $etapasArray[] = 4;
                }
            else{

                // Eliminar la etapa 4 si existe en $etapasArray
                $key = array_search(4, $etapasArray);
                if ($key !== false) {
                    unset($etapasArray[$key]);
                }
                }

            if (in_array(2, $etapasArray) && !in_array(3, $etapasArray)) {
                    // Eliminar la etapa enviar si existe en $etapasArray y no existe la etapa validar
                    $key = array_search(2, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                }

            if (in_array(3, $etapasArray) && !in_array(5, $etapasArray)) {
                    // Eliminar la etapa validar si existe en $etapasArray y no existe la etapa pagar
                    $key = array_search(3, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                    $etapasArray[] = 6;

                }

            if (in_array(6, $etapasArray) && !in_array(2, $etapasArray)) {
                    // Eliminar la etapa Observar por tesoreria si existe en $etapasArray y no existe la etapa enviado
                    $key = array_search(6, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                }
            if (in_array(7, $etapasArray)) {
                $etapasArray[] = 7;
                }


            // Filtrar duplicados y ordenar
            $etapasArray = array_unique($etapasArray);
            sort($etapasArray);    
    
            // Crear la cadena de etapas
            $etapas = implode(",", $etapasArray);

            $where_permiso_etapa = " AND  c.etapa_id IN (".$etapas.") ";


        }else{
            $where_permiso_etapa = " AND c.etapa_id IN (".$etapas.") ";
        }

        $stmtPermisoEtapa->close();

    //  FIN PERMISOS ESTADOS

	$query = "
        SELECT
            c.id,
            c.num_documento,
            DATE_FORMAT(c.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            DATE_FORMAT(c.fecha_emision, '%d-%m-%Y') AS fecha_emision,
            DATE_FORMAT(c.fecha_vencimiento, '%d-%m-%Y') AS fecha_vencimiento,
            cp.ruc AS proveedor_ruc,
            cp.nombre AS proveedor_nombre,
            c.monto,
            c.etapa_id,
            ce.nombre AS etapa_nombre,
            c.user_created_id AS usuario_creador,
            c.status
        FROM tbl_comprobante c
            LEFT JOIN tbl_comprobante_etapa ce
            ON c.etapa_id = ce.id
            LEFT JOIN tbl_comprobante_proveedor cp
            ON c.proveedor_id = cp.id
        WHERE 1=1 
        $where_proveedor
        $where_razon_social
        $where_etapa
        $where_estado
        $where_fecha_emision
        $where_fecha_registro
        $where_permiso_etapa
        $where_creador
        ORDER BY c.id DESC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
        $permiso_editar = "";
        $permiso_exportar_zip = "";
        $permiso_editar_pago="";
        $permiso_revertir_pago="";


        //  PERMISO PARA EDITAR

        if($reg->usuario_creador == $usuario_id){
            $permiso_editar = '<a onclick="sec_comprobante_obtener('.$reg->id.')";
                                class="btn btn-warning btn-sm"
                                data-toggle="tooltip" data-placement="top" title="Editar">
                                <span class="fa fa-pencil"></span>
                            </a>';
        }elseif(in_array("btn_comp_editar_otros", $usuario_permisos[$menu_permiso])){
            $permiso_editar = '<a onclick="sec_comprobante_obtener('.$reg->id.')";
                                    class="btn btn-warning btn-sm"
                                    data-toggle="tooltip" data-placement="top" title="Editar">
                                    <span class="fa fa-pencil"></span>
                                </a>';
        }

        if($reg->etapa_id == 5 && in_array("btn_comp_editar_pago", $usuario_permisos[$menu_permiso])){
            $permiso_editar_pago = '<a onclick="btn_comp_cambiar_etapa(5,'.$reg->id.',1)";
                                    class="btn btn-warning btn-sm"
                                    data-toggle="tooltip" data-placement="top" title="Editar Pago">
                                    <span class="fa fa-pencil"></span>
                                </a>';
        }

        if(in_array("btn_comp_validar", $usuario_permisos[$menu_permiso]) ||  in_array("btn_comp_pagar", $usuario_permisos[$menu_permiso])){
            $permiso_exportar_zip = ' <a onclick="sec_comprobante_exportar_zip('.$reg->id.')";
                                class="btn btn-success btn-sm"
                                data-toggle="tooltip" data-placement="top" title="Exportar archivo zip">
                                <span class="fa fa-folder-open-o"></span>
                            </a>';
        }

        if($reg->etapa_id == 6 && in_array("btn_comp_revertir_pago", $usuario_permisos[$menu_permiso])){
            $permiso_revertir_pago = '<a onclick="btn_comp_cambiar_etapa(7,'.$reg->id.',1)";
                                    class="btn btn-warning btn-sm"
                                    data-toggle="tooltip" data-placement="top" title="Editar Pago">
                                    <span class="fa fa-pencil"></span>
                                </a>';
        }

        //  PERMISO PARA VER E HISTORICO Y EXPORTAR

        $botones = '<a onclick="sec_comprobante_ver('.$reg->id.')";
                        class="btn btn-info btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Ver">
                        <span class="fa fa-eye"></span>
                    </a>
                    '.$permiso_editar.$permiso_exportar_zip.$permiso_editar_pago.'
                    <a onclick="sec_comprobante_obtener_historico_cambios('.$reg->id.');";
                        class="btn btn-primary btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Historial de cambios">
                        <span class="fa fa-history"></span>
                    </a>
                    ';
             
        //  PERMISO PARA ANULAR COMPROBANTES

        if(in_array("btn_comp_anular", $usuario_permisos[$menu_permiso]) && $reg->status != 0):

            //  OBTENER LISTADO DE ETAPAS Y PERMISOS

            $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                        onclick="sec_comprobante_anular(' . $reg->id . ')"
                        title="Anular">
                        <i class="fa fa-trash"></i>
                    </a>
                    ';
        endif;
        
       // OPCIONES DEL COMBOBOX PARA ETAPAS ////////////////////////////////////////////////////////////////////
        
       $selectQuery = " SELECT 
                            ce.id,
                            ce.nombre,
                            ce.permiso
                        FROM tbl_comprobante_etapa ce
                        WHERE ce.status = 1
                        " ; 

        $stmt = $mysqli->prepare($selectQuery);
        $stmt->execute();
        $stmt->bind_result($id, $nombre,$permiso);

        $opciones_etapas = "";
        $disable_select = false;
        while ($stmt->fetch()) {
            if (in_array($permiso, $usuario_permisos[$menu_permiso]) || $reg->etapa_id == $id) {
                //  Si la etapa es Pagado y el permiso es observar por tesoreria no mostrar
                if( $reg->etapa_id == 5 && $permiso == 'btn_comp_observar_tesoreria'){
                }else/*if( $reg->etapa_id == 3 && $permiso == 'btn_comp_revertir_pago'){
                }*/{
                    //  Si la etapa es Validado y el permiso es observar entonces no mostrar
                    if( $reg->etapa_id == 3 && $permiso == 'btn_comp_observar'){
                    }else{

                        //  Si la etapa es Validado y el permiso es observar por tesoreria no mostrar
                        if($reg->etapa_id == 7 &&  $permiso ==  'btn_comp_revertir_pago'){
                            $opciones_etapas .= '<option value="' . $id . '" ' . ($reg->etapa_id == $id ? 'selected' : '') . '>'.$nombre.'</option>';
                            $disable_select = true;

                        }else{
                            if($reg->etapa_id != 5 &&  $permiso ==  'btn_comp_revertir_pago'){                              
                            }else{
                                $opciones_etapas .= '<option value="' . $id . '" ' . ($reg->etapa_id == $id ? 'selected' : '') . '>'.$nombre.'</option>';
    
                            }
                        }
                    }                
                }
                
            }
        
            if ($reg->status == 0) {
               $disable_select = true;
            }
        }
        
        $stmt->close();
        $nombre_etapa = "'".$reg->etapa_nombre."'";
        $combobox_etapa = '<select class="form-control sec_comp_select_filtro" ' . ($disable_select ? 'disabled' : '') . ' onchange="btn_comp_cambiar_etapa(this.value,' . $reg->id . ',this.options[this.selectedIndex].text)">' . $opciones_etapas . '</select>';
        //$combobox_etapa = '<select class="form-control sec_comp_select_filtro" onchange="btn_comp_cambiar_etapa(this.value,' . $reg->id . ',this.options[this.selectedIndex].text)">' . $opciones_etapas . '</select>';

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->created_at,
            "2" => $reg->num_documento,
			"3" => $reg->fecha_emision,
            "4" => $reg->fecha_vencimiento,
			"5" => $reg->proveedor_ruc,
			"6" => $reg->proveedor_nombre,
            "7" => $reg->monto,
			"8" => $combobox_etapa,
			"9" => $botones
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_obtener") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    c.id, 
                    c.proveedor_id,
                    c.tipo_comprobante_id,
                    c.num_documento,
                    IFNULL(c.fecha_emision, '') fecha_emision, 
			        IFNULL(c.fecha_vencimiento, '') fecha_vencimiento,
                    c.razon_social_id,
                    c.area_id,
                    c.etapa_id,
                    c.moneda_id,
                    c.monto,
                    c.status,
                    IFNULL(c.created_at, '') AS created_at,
                    IFNULL(c.updated_at, '') AS updated_at,
                    co.ceco_id,
                    co.num_orden_pago,
                    cf.banco_id,
                    cf.moneda_id,
                    cf.num_cuenta_corriente,
                    cf.num_cuenta_interbancaria,
                    u.usuario AS usuario_create,
                    ua.usuario AS usuario_update,
					IFNULL(cd_cpdf.nombre, '') AS ad_comprobante_pdf,
                    IFNULL(cd_cxml.nombre, '') AS ad_comprobante_xml,
                    IFNULL(cd_cs.nombre, '') AS ad_contrato_servicio,
                    IFNULL(cd_gr.nombre, '') AS ad_guia_remision,
                    IFNULL(cd_ac.nombre, '') AS ad_acta_conformidad,
                    IFNULL(cd_oc.nombre, '') AS ad_orden_compra
                FROM tbl_comprobante c
                LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
                LEFT JOIN tbl_comprobante_orden_compra co ON co.comprobante_id = c.id
                LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
                LEFT JOIN tbl_comprobante_documento cd_cpdf ON cd_cpdf.comprobante_id = c.id AND cd_cpdf.tipo_documento_id =1 AND cd_cpdf.status=1
                LEFT JOIN tbl_comprobante_documento cd_cxml ON cd_cxml.comprobante_id = c.id AND cd_cxml.tipo_documento_id =2 AND cd_cxml.status=1
                LEFT JOIN tbl_comprobante_documento cd_cs ON cd_cs.comprobante_id = c.id AND cd_cs.tipo_documento_id =3 AND cd_cs.status=1
                LEFT JOIN tbl_comprobante_documento cd_gr ON cd_gr.comprobante_id = c.id AND cd_gr.tipo_documento_id =4 AND cd_gr.status=1
                LEFT JOIN tbl_comprobante_documento cd_ac ON cd_ac.comprobante_id = c.id AND cd_ac.tipo_documento_id =5 AND cd_ac.status=1
                LEFT JOIN tbl_comprobante_documento cd_oc ON cd_oc.comprobante_id = c.id AND cd_oc.tipo_documento_id =6 AND cd_oc.status=1
                WHERE c.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $comprobante_id, 
                                $proveedor_id, 
                                $tipo_comprobante_id,
                                $num_documento,
                                $fecha_emision,
                                $fecha_vencimiento,
                                $razon_social_id,
                                $area_id,
                                $etapa_id,
                                $moneda_id,
                                $monto,
                                $status,
                                $created_at,
                                $updated_at,
                                $oc_ceco_id,
                                $oc_num_orden_pago,
                                $fp_banco_id,
                                $fp_moneda_id,
                                $fp_num_cuenta_corriente,
                                $fp_num_cuenta_interbancaria,
                                $usuario_create,
                                $usuario_update,
                                $ad_comprobante_pdf,
                                $ad_comprobante_xml,
                                $ad_contrato_servicio,
                                $ad_guia_remision,
                                $ad_acta_conformidad,
                                $ad_orden_compra
                            );

            if($fecha_emision != '') $fecha_emision = date("d-m-Y", strtotime($fecha_emision));  
            if($fecha_vencimiento != '') $fecha_vencimiento = date("d-m-Y", strtotime($fecha_vencimiento));  
                                                 
            if ($stmt->fetch()) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $comprobante_id,
                        'proveedor_id' => $proveedor_id,
                        'tipo_comprobante_id' => $tipo_comprobante_id,
                        'num_documento' => $num_documento,
                        'fecha_emision' => $fecha_emision,
                        'fecha_vencimiento' => $fecha_vencimiento,
                        'razon_social_id' => $razon_social_id,
                        'area_id' => $area_id,
                        'etapa_id' => $etapa_id,
                        'moneda_id' => $moneda_id,
                        'monto' => $monto,
                        'status' => $status,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'oc_ceco_id' => $oc_ceco_id,
                        'oc_num_orden_pago' => $oc_num_orden_pago,
                        'fp_banco_id' => $fp_banco_id,
                        'fp_moneda_id' => $fp_moneda_id,
                        'fp_num_cuenta_corriente' => $fp_num_cuenta_corriente,
                        'fp_num_cuenta_interbancaria' => $fp_num_cuenta_interbancaria,
                        'usuario_create' => $usuario_create,
                        'usuario_update' => $usuario_update,
                        'ad_comprobante_pdf' => $ad_comprobante_pdf,
                        'ad_comprobante_xml' => $ad_comprobante_xml,
                        'ad_contrato_servicio' => $ad_contrato_servicio,
                        'ad_guia_remision' => $ad_guia_remision,
                        'ad_acta_conformidad' => $ad_acta_conformidad,
                        'ad_orden_compra' => $ad_orden_compra
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_proveedor_obtener_nombre")   /////////////  NEW
{
	try{
		$proveedor_id = $_POST["proveedor_id"];
		$selectQuery = "SELECT 
							nombre
						FROM tbl_comprobante_proveedor 
						WHERE status = '1' AND id = ? 
						ORDER BY nombre ASC LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $proveedor_id);
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
			$result["descripcion"] = "Sin nombre";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_empresa_at_obtener_nombre")   /////////////  NEW
{
	try{
		$razon_social_id = $_POST["razon_social_id"];
		$selectQuery = "SELECT 
							nombre
						FROM tbl_razon_social 
						WHERE status = '1' AND id = ? 
						ORDER BY nombre ASC LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $razon_social_id);
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
			$result["descripcion"] = "Sin nombre";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_ceco_obtener_descripcion")   /////////////  NEW
{
	try{
		$cc_id = $_POST["cc_id"];
		$selectQuery = "SELECT * FROM (
                            SELECT 
                                nombre,
                                cc_id
                                FROM tbl_locales 
                                WHERE estado = '1' AND cc_id <> ''
                        UNION ALL
                            SELECT
                                nombre,
                                centro_costo AS cc_id
                                FROM mepa_zona_asignacion
                                WHERE status = 1
                        ) AS combined_tables
                        WHERE cc_id = ?
                        LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("s", $cc_id);
		$selectStmt->execute();
		$selectStmt->store_result();

		if ($selectStmt->num_rows > 0) {
			$selectStmt->bind_result($descripcion,$cc_id);
			$selectStmt->fetch();

			$result["http_code"] = 200;
			$result["descripcion"] = "(".$cc_id.") ".$descripcion;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_moneda_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT(nombre,' (',simbolo,')') AS nombre
            FROM tbl_moneda
            WHERE estado = 1 AND nombre IS NOT NULL
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_area_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                nombre
            FROM tbl_areas
            WHERE estado = 1 AND nombre IS NOT NULL
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_ceco_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT * FROM (
                SELECT
                    cc_id AS id, 
                    cc_id
                FROM tbl_locales
                WHERE estado = 1 AND nombre IS NOT NULL AND cc_id IS NOT NULL
                UNION ALL
                SELECT
                    centro_costo AS id, 
                    centro_costo AS cc_id
                FROM mepa_zona_asignacion
                WHERE status = 1 AND centro_costo IS NOT NULL
                ) AS combined_tables
                ORDER BY id ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_banco_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre
            FROM tbl_bancos
            WHERE estado = 1 AND nombre IS NOT NULL
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_proveedor_listar_ruc") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                ruc
            FROM tbl_comprobante_proveedor
            WHERE status = 1
            ORDER BY ruc ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_proveedor_listar_ruc_nombre") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT('(',ruc,') ',nombre) AS nombre
            FROM tbl_comprobante_proveedor
            WHERE status = 1 AND ruc IS NOT NULL AND nombre IS NOT NULL
            ORDER BY ruc ASC;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_empresa_at_listar_ruc") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                ruc
            FROM tbl_razon_social
            WHERE status = 1 AND ruc IS NOT NULL
            ORDER BY ruc ASC;
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
            $result["result"] = "La empresa AT no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_empresa_at_listar_nombre") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, 
                CONCAT('(',ruc,') ',nombre) AS nombre
            FROM tbl_razon_social
            WHERE status = 1 AND ruc IS NOT NULL
            ORDER BY ruc ASC;
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
            $result["result"] = "La empresa AT no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_tipo_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_comprobante_tipo
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
            $result["result"] = "El comprobante no existe.";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_etapa_listar") {
    try {

        $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'comprobante' AND sub_sec_id = 'pago' LIMIT 1")->fetch_assoc();

        $menu_permiso = $menu_id_consultar["id"];

        //  INICIO PERMISOS DE ESTADOS

        $selectQueryPermisoEtapa = "SELECT 
                ce.id,
                ce.nombre,
                ce.permiso
            FROM tbl_comprobante_etapa ce
            WHERE ce.status = 1
            ";

        $stmtPermisoEtapa = $mysqli->prepare($selectQueryPermisoEtapa);
        $stmtPermisoEtapa->execute();
        $stmtPermisoEtapa->bind_result($id_etapa, $nombre_etapa, $permiso_etapa);

        $etapas = "1";
        $etapasArray = [];

        while ($stmtPermisoEtapa->fetch()) {
        if (in_array($permiso_etapa, $usuario_permisos[$menu_permiso])) {
            $etapasArray[] = $id_etapa;
        }
        }

        //  VERIFICAR SI EL ARREGLO ESTA VACIO
        if(!empty($etapasArray)){

            // Condiciones de etapa

            foreach ($etapasArray as $etapa) {
                if ($etapa > 1) {
                    if ($etapa==5) {
                        $etapasArray[] = $etapa - 2;
            
                    }else{
                        $etapasArray[] = $etapa - 1;
                        }
                    }
                }

            // Filtrar duplicados y ordenar
            $etapasArray = array_unique($etapasArray);
            sort($etapasArray);

            // Si existe la etapa 2, incluir la etapa 4
                
            if (in_array(1, $etapasArray)) {
                $etapasArray[] = 4;
                }
            else{

                // Eliminar la etapa 4 si existe en $etapasArray
                $key = array_search(4, $etapasArray);
                if ($key !== false) {
                    unset($etapasArray[$key]);
                }
                }

            if (in_array(2, $etapasArray) && !in_array(3, $etapasArray)) {
                    // Eliminar la etapa 4 si existe en $etapasArray
                    $key = array_search(2, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                }
    
            if (in_array(3, $etapasArray) && !in_array(5, $etapasArray)) {
                    // Eliminar la etapa 4 si existe en $etapasArray
                    $key = array_search(3, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                    //$etapasArray[] = 5;
                    $etapasArray[] = 6;

                }
            if (in_array(6, $etapasArray) && !in_array(2, $etapasArray)) {
                // Eliminar la etapa Observar por tesoreria si existe en $etapasArray y no existe la etapa enviado
                $key = array_search(6, $etapasArray);
                if ($key !== false) {
                    unset($etapasArray[$key]);
                }
                }
            // Filtrar duplicados y ordenar
            $etapasArray = array_unique($etapasArray);
            sort($etapasArray);
                        
            // Crear la cadena de etapas
            $etapas = implode(",", $etapasArray);

            $where_permiso_etapa = " AND  id IN (".$etapas.") ";


        }else{
            $where_permiso_etapa = " AND id IN (".$etapas.") ";
        }

        $stmtPermisoEtapa->close();

        //  FIN PERMISOS ESTADOS


        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre
            FROM tbl_comprobante_etapa
            WHERE status = 1 AND nombre IS NOT NULL
            $where_permiso_etapa
            ;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_motivo_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre
            FROM tbl_comprobante_motivo_reversion
            WHERE status = 1 AND nombre IS NOT NULL
            ;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_tipo_documento_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre, extension, obligatorio
            FROM tbl_comprobante_documento_tipo
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
            $result["result"] = "El comprobante no existe.";
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

//---------- Historico de cambios de cuentas contables

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_obtener_campos") {
    try {

        $stmt = $mysqli->prepare("
                                SELECT campo AS id, nombre_campo AS nombre
                                FROM tbl_comprobante_campos
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_obtener_historico") {
    $comprobante_id = $_POST['comprobante_id'];
    $campo_id = $_POST["campo_id"];

    $where_campo = "";
    if($campo_id != ""){
        $where_campo .= " AND ncc.campo = '".$campo_id."' ";


    }
    try {

        $selectQuery = " SELECT 
                            ch.id,
                            ch.valor_anterior,
                            ch.valor_nuevo,
                            ifnull(ncc.nombre_campo,ifnull(ctd.nombre,ctc.nombre)),
                            DATE_FORMAT(ch.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
                            u.usuario
                        FROM tbl_comprobante_historial_cambios ch
                        LEFT JOIN tbl_usuarios u ON ch.user_created_id = u.id
                        LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
                        LEFT JOIN tbl_comprobante_campos ncc ON ncc.campo = ch.nombre_campo
                        LEFT JOIN tbl_comprobante_documento_tipo ctd ON ctd.campo = ch.nombre_campo
                        LEFT JOIN tbl_comprobante_constancia_tipo ctc ON ctc.campo = ch.nombre_campo
                        WHERE ch.status =1 AND ch.comprobante_id = ?
                        $where_campo 
                        ORDER BY ch.created_at DESC" ; 
        //echo $selectQuery;
        $stmt = $mysqli->prepare($selectQuery);

        $stmt->bind_param("i", $comprobante_id);
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_obtener_historico_etapas") {
    $comprobante_id = $_POST['comprobante_id'];

    try {

        $selectQuery = " SELECT * FROM (
                            SELECT 
                                che.id, 
                                ce.nombre AS etapa_nombre, 
                                che.motivo, 
                                DATE_FORMAT(che.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
                                u.usuario AS usuario_create
                            FROM tbl_comprobante_historial_etapas che
                            LEFT JOIN tbl_comprobante_etapa ce ON che.etapa_id = ce.id
                            LEFT JOIN tbl_usuarios u ON u.id = che.user_updated_id
                            WHERE che.comprobante_id = ?
                            UNION ALL
                            SELECT 
                                che.id, 
                                CASE
                                    WHEN che.estado_id = 1 THEN 'Activo'
                                    WHEN che.estado_id = 0 THEN 'Inactivo'
                                    ELSE 'Desconocido'
                                    END AS estado_nombre, 
                                che.motivo, 
                                che.created_at, 
                                u.usuario AS usuario_create
                            FROM tbl_comprobante_historial_estado che
                            LEFT JOIN tbl_usuarios u ON u.id = che.user_created_id
                            WHERE che.comprobante_id = ?
                        ) AS combined_tables
                        ORDER BY created_at DESC;
                        " ; 

        $stmt = $mysqli->prepare($selectQuery);

        $stmt->bind_param("ii", $comprobante_id, $comprobante_id);
        $stmt->execute();
        $stmt->bind_result($id, $etapa_nombre, $motivo, $created_at, $usuario_create);

        $data = [];

        while ($stmt->fetch()) {

            $data[] = [
                "0" => count($data) + 1,
                "1" => $etapa_nombre,
                "2" => $motivo,
                "3" => $created_at,
                "4" => $usuario_create
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

//  EXPORTAR

if (isset($_POST["accion"]) && $_POST["accion"]==="comp_exportar_listado")
{
        $usuario_id = $login ? $login['id'] : null;

    // PERMISOS ESTADOS

        $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'comprobante' AND sub_sec_id = 'pago' LIMIT 1")->fetch_assoc();

        $menu_permiso = $menu_id_consultar["id"];

    //  Variables de ingreso

        $proveedor_id = $_POST['proveedor_id'];
        $razon_social_id = $_POST['razon_social_id'];
        $etapa_id = $_POST['etapa_id'];
        $estado_id = $_POST['estado_id'];
        $fecha_inicio_registro = $_POST['fecha_inicio_registro'];
        $fecha_fin_registro = $_POST['fecha_fin_registro'];
        $fecha_inicio_emision = $_POST['fecha_inicio_emision'];
        $fecha_fin_emision = $_POST['fecha_fin_emision'];

    //  Filtro de busqueda

        $where_proveedor="";
        $where_razon_social="";
        $where_etapa="";
        $where_estado="";
        $where_creador="";


    //  FILTROS

        if(in_array("btn_comp_ver_todo", $usuario_permisos[$menu_permiso])){

            $where_creador = "";
        }else{
            if(in_array("btn_comp_registrar", $usuario_permisos[$menu_permiso])){

                $where_creador = " AND c.user_created_id = '".$usuario_id."' ";
            }
        }

        if (!Empty($proveedor_id))
        {
            $where_proveedor = " AND c.proveedor_id = '".$proveedor_id."' ";
        }

        if (!Empty($razon_social_id))
        {
            $where_razon_social = " AND c.razon_social_id = '".$razon_social_id."' ";
        }

        if (!Empty($etapa_id))
        {
            $where_etapa = " AND c.etapa_id = '".$etapa_id."' ";
        }

        if ($estado_id != "")
        {
            $where_estado = " AND c.status = '".$estado_id."' ";
        }

        $where_fecha_emision= "";
        $where_fecha_registro ="";

        if (!Empty($fecha_inicio_registro) && !Empty($fecha_fin_registro)) {
            $where_fecha_registro = " AND c.created_at BETWEEN '$fecha_inicio_registro 00:00:00' AND '$fecha_fin_registro 23:59:59'";
        } elseif (!Empty($fecha_inicio_registro)) {
            $where_fecha_registro = " AND c.created_at >= '$fecha_inicio_registro 00:00:00'";
        } elseif (!Empty($fecha_fin_registro)) {
            $where_fecha_registro = " AND c.created_at <= '$fecha_fin_registro 23:59:59'";
        }

        if (!Empty($fecha_inicio_emision) && !Empty($fecha_fin_emision)) {
            $where_fecha_emision = " AND c.created_at BETWEEN '$fecha_inicio_emision 00:00:00' AND '$fecha_fin_emision 23:59:59'";
        } elseif (!Empty($fecha_inicio_emision)) {
            $where_fecha_emision = " AND c.created_at >= '$fecha_inicio_emision 00:00:00'";
        } elseif (!Empty($fecha_fin_emision)) {
            $where_fecha_emision = " AND c.created_at <= '$fecha_fin_emision 23:59:59'";
        }

    //  INICIO PERMISOS DE ESTADOS

        $selectQueryPermisoEtapa = "SELECT 
                                    ce.id,
                                    ce.nombre,
                                    ce.permiso
                                FROM tbl_comprobante_etapa ce
                                WHERE ce.status = 1
                                ";

        $stmtPermisoEtapa = $mysqli->prepare($selectQueryPermisoEtapa);
        $stmtPermisoEtapa->execute();
        $stmtPermisoEtapa->bind_result($id_etapa, $nombre_etapa, $permiso_etapa);

        $etapas = "1";
        $etapasArray = [];

        while ($stmtPermisoEtapa->fetch()) {
            if (in_array($permiso_etapa, $usuario_permisos[$menu_permiso])) {
            $etapasArray[] = $id_etapa;
            }
        }


        if(!empty($etapasArray)){

            // Condiciones de etapa

            foreach ($etapasArray as $etapa) {
                if ($etapa > 1) {
                    if ($etapa==5) {
                        $etapasArray[] = $etapa - 2;
            
                    }else{
                        $etapasArray[] = $etapa - 1;
                        }
                    }
                }

            // Filtrar duplicados y ordenar
            $etapasArray = array_unique($etapasArray);
            sort($etapasArray);

            // Si existe la etapa 2, incluir la etapa 4
                
            if (in_array(1, $etapasArray)) {
                $etapasArray[] = 4;
                }
            else{

                // Eliminar la etapa 4 si existe en $etapasArray
                $key = array_search(4, $etapasArray);
                if ($key !== false) {
                    unset($etapasArray[$key]);
                }
                }

            if (in_array(2, $etapasArray) && !in_array(3, $etapasArray)) {
                    // Eliminar la etapa 4 si existe en $etapasArray
                    $key = array_search(2, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                }

            if (in_array(3, $etapasArray) && !in_array(5, $etapasArray)) {
                    // Eliminar la etapa 4 si existe en $etapasArray
                    $key = array_search(3, $etapasArray);
                    if ($key !== false) {
                        unset($etapasArray[$key]);
                    }
                    
                }

            // Filtrar duplicados y ordenar
            $etapasArray = array_unique($etapasArray);
            sort($etapasArray);
                        
            // Crear la cadena de etapas
            $etapas = implode(",", $etapasArray);

            $where_permiso_etapa = " AND  c.etapa_id IN (".$etapas.") ";


        }else{
            $where_permiso_etapa = " AND c.etapa_id IN (".$etapas.") ";
        }

        $stmtPermisoEtapa->close();

    //  FIN PERMISOS ESTADOS

	$query = "
        SELECT
            c.id,
            c.created_at,
            u.usuario AS usuario_create,
            ce.nombre AS etapa_nombre,
            tc.nombre AS tipo_nombre,
            c.num_documento,
            c.fecha_emision,
            c.fecha_vencimiento,
            cp.ruc AS proveedor_ruc,
            cp.nombre AS proveedor_nombre,
            rz.ruc AS empresa_at_ruc,
            rz.nombre AS empresa_at_nombre,
            c.monto,
            oc.num_orden_pago,
            CONCAT(m.nombre,' (',m.simbolo,')') AS moneda,
            a.nombre AS area_nombre,
            b.nombre AS banco_nombre,
            CONCAT(mcf.nombre,' (',mcf.simbolo,')') AS fp_moneda,
            cf.num_cuenta_corriente,
            cf.num_cuenta_interbancaria,    
            c.updated_at,
            ua.usuario AS usuario_update
        FROM tbl_comprobante c
            LEFT JOIN tbl_comprobante_etapa ce ON c.etapa_id = ce.id
            LEFT JOIN tbl_comprobante_proveedor cp  ON c.proveedor_id = cp.id
            LEFT JOIN tbl_comprobante_orden_compra oc  ON c.id = oc.comprobante_id
            LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
            LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
                LEFT JOIN tbl_comprobante_tipo tc ON tc.id = c.tipo_comprobante_id
                LEFT JOIN tbl_razon_social rz ON rz.id = c.razon_social_id
                LEFT JOIN tbl_areas a ON a.id = c.area_id
                LEFT JOIN tbl_bancos b ON cf.banco_id = b.id
                LEFT JOIN tbl_moneda m ON m.id = c.moneda_id
                LEFT JOIN tbl_moneda mcf ON mcf.id = cf.moneda_id
        WHERE 1=1 
        $where_proveedor
        $where_razon_social
        $where_etapa
        $where_estado
        $where_fecha_emision
        $where_fecha_registro
        $where_permiso_etapa
        $where_creador
        ORDER BY c.id DESC
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/comprobantes/reportes/";

	if (!is_dir($path)) 
	{

        $data_return = array(
            "error" => 'No existe la carpeta "reportes" en la ruta "/files_bucket/comprobantes/" del servidor',
            "titulo" => "Error al exportar el excel",
            "http_code" => 400
        );
        echo json_encode($data_return);
        exit;
	}

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$titulosColumnas = array('Nº', 
                            'Fecha de registro', 
                            'Usuario que registró', 
                            'Etapa', 
                            'Tipo de comprobante', 
                            'Número de documento', 
                            'F. de Emisión', 
                            'F. de vencimiento', 
                            'Monto del importe', 
                            'Número de Orden de Servicio o de Compra', 
                            'Moneda', 
                            'RUC del Proveedor', 
                            'Nombre del proveedor', 
                            'RUC de Empresa AT', 
                            'Nombre de Empresa AT', 
                            'Área Solicitante', 
                            'Banco',
                            'Moneda',
                            'Nro. Cuenta Corriente',
                            'Nro. Código de Cuenta Interbancaria CCI.',
                            'F. de ultima modificación',
                            'Usuario que modifico'
                        );

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
        ->setCellValue('B1', $titulosColumnas[1])
        ->setCellValue('C1', $titulosColumnas[2])
        ->setCellValue('D1', $titulosColumnas[3])
        ->setCellValue('E1', $titulosColumnas[4])
        ->setCellValue('F1', $titulosColumnas[5])
        ->setCellValue('G1', $titulosColumnas[6])
        ->setCellValue('H1', $titulosColumnas[7])
        ->setCellValue('I1', $titulosColumnas[8])
        ->setCellValue('J1', $titulosColumnas[9])
        ->setCellValue('K1', $titulosColumnas[10])
        ->setCellValue('L1', $titulosColumnas[11])
        ->setCellValue('M1', $titulosColumnas[12])
        ->setCellValue('N1', $titulosColumnas[13])
        ->setCellValue('O1', $titulosColumnas[14])
        ->setCellValue('P1', $titulosColumnas[15])
        ->setCellValue('Q1', $titulosColumnas[16])
        ->setCellValue('R1', $titulosColumnas[17])
        ->setCellValue('S1', $titulosColumnas[18])
        ->setCellValue('T1', $titulosColumnas[19])
        ->setCellValue('U1', $titulosColumnas[20])
        ->setCellValue('V1', $titulosColumnas[21]);
    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['created_at'])
		->setCellValue('C'.$i, $fila['usuario_create'])
		->setCellValue('D'.$i, $fila['etapa_nombre'])
		->setCellValue('E'.$i, $fila['tipo_nombre'])
		->setCellValue('F'.$i, $fila['num_documento'])
		->setCellValue('G'.$i, $fila['fecha_emision'])
		->setCellValue('H'.$i, $fila['fecha_vencimiento'])
		->setCellValue('I'.$i, $fila['monto'])
		->setCellValue('J'.$i, $fila['num_orden_pago'])
		->setCellValue('K'.$i, $fila['moneda'])
		->setCellValue('L'.$i, $fila['proveedor_ruc'])
		->setCellValue('M'.$i, $fila['proveedor_nombre'])
		->setCellValue('N'.$i, $fila['empresa_at_ruc'])
		->setCellValue('O'.$i, $fila['empresa_at_nombre'])
        ->setCellValue('P'.$i, $fila['area_nombre'])
		->setCellValue('Q'.$i, $fila['banco_nombre'])
        ->setCellValue('R'.$i, $fila['fp_moneda'])
		->setCellValue('S'.$i, $fila['num_cuenta_corriente'])
		->setCellValue('T'.$i, $fila['num_cuenta_interbancaria'])
		->setCellValue('U'.$i, $fila['updated_at'])
        ->setCellValue('V'.$i, $fila['usuario_update']);
		
		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => '000000'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => 'FFFFFF')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    ),
	    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	// SIRVE PARA PONER ALTURA A LA FILA, EN ESTE CASO A LA FILA 1.
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(63);

	$objPHPExcel->getActiveSheet()->getStyle('A1:V1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:V".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:V'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('I2:I'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'V'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Comprobantes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
    
    $file_name = "Comprobantes_".date("Ymd");
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/comprobantes/reportes/'.$file_name.'.xls';
    $excel_path_download = '/files_bucket/comprobantes/reportes/'.$file_name.'.xls';
	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
        $data_return = array(
            "error" => $e,
            "titulo" => "Error al guardar el excel",
            "http_code" => 400
        );
        echo json_encode($data_return);
		exit;
	}

    $data_return = array(
        "ruta_archivo" => $excel_path_download,
        "http_code" => 200
    );
	echo json_encode($data_return);
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_exportar_zip") {
    $comprobante_id = $_POST['comprobante_id'];


	// DATA DE RESUMEN DE SOLICITUD
	$queryResumenSolicitud = "
            SELECT 
            c.id, 
            c.proveedor_id,
            c.tipo_comprobante_id,
            c.num_documento,
            cp.ruc AS proveedor_ruc,
            IFNULL(c.fecha_emision, '') fecha_emision, 
            IFNULL(c.fecha_vencimiento, '') fecha_vencimiento,
            c.razon_social_id,
            c.area_id,
            c.etapa_id,
            c.moneda_id,
            c.monto,
            c.status,
            IFNULL(c.created_at, '') AS created_at,
            IFNULL(c.updated_at, '') AS updated_at,
            co.ceco_id,
            co.num_orden_pago,
            cf.banco_id,
            cf.moneda_id,
            cf.num_cuenta_corriente,
            cf.num_cuenta_interbancaria,
            u.usuario AS usuario_create,
            ua.usuario AS usuario_update,
            p.correo AS correo_create
        FROM tbl_comprobante c
        LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
        LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
        LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id 
        LEFT JOIN tbl_comprobante_proveedor cp ON cp.id = c.proveedor_id
        LEFT JOIN tbl_comprobante_orden_compra co ON co.comprobante_id = c.id
        LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
        WHERE c.id= '".$comprobante_id."'
        LIMIT 1
        ";
	$list_query_resumen = $mysqli->query($queryResumenSolicitud)->fetch_assoc();
	$num_documento=$list_query_resumen["num_documento"];
	$fecha_emision=$list_query_resumen["fecha_emision"];


    // PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
    $path = '/var/www/html/files_bucket/comprobantes/archivos_zip/';

    if (!is_dir($path)) 
	{
        echo json_encode(array(
            "descripcion" => 'No existe la carpeta "archivos_zip" en la ruta "/files_bucket/comprobantes/" del servidor',
            "titulo" =>  'Error al exportar zip',
            "http_code" => 400
        ));
        exit();

		//mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/comprobantes/archivos_zip/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file);
	}

    // Crear un archivo ZIP en la dirección especificada
    $zipFilename = 'Comprobante - '.$num_documento.' - '.$fecha_emision.'.zip';

	$zip = new ZipArchive();
	// Ruta absoluta
	$nombreArchivoZip = $path . $zipFilename;
	$nombreArchivoZipRuta = '/files_bucket/comprobantes/archivos_zip/' . $zipFilename;

	if (!$zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
		exit("Error abriendo ZIP en $nombreArchivoZip");
	}

	////////////////////////////////////////////////////////////////////////////
	$sel_query_detalle_liquidacion = $mysqli->prepare("
            SELECT
                dl.id,
                dl.nombre,
                dl.extension,
                dl.download
            FROM tbl_comprobante_constancia dl
            WHERE dl.comprobante_id = ?
            AND dl.status =1
            UNION
            SELECT
                dl.id,
                dl.nombre,
                dl.extension,
                dl.download
            FROM tbl_comprobante_documento dl
            WHERE dl.comprobante_id = ?
            AND dl.status =1
        ");
        $sel_query_detalle_liquidacion->bind_param("ii", $comprobante_id, $comprobante_id);
        $sel_query_detalle_liquidacion->execute();

        $list_query_liquidacion = $sel_query_detalle_liquidacion->get_result();
        $row_count_detalle_liquidacion = $list_query_liquidacion->num_rows;

        if ($row_count_detalle_liquidacion > 0) {
            while ($row = $list_query_liquidacion->fetch_assoc()) {
                $fileToInclude = $row['nombre'];
                $fileFullPath = $row['download']. $row['nombre'];
                // Agregar el archivo al archivo ZIP
                $zip->addFile($fileFullPath, $fileToInclude);
            }
        }
	/////////////////////////////////////////////////////////////////////////////
	// No olvides cerrar el archivo
	$resultado = $zip->close();
    //chmod($zipFilename, 0777);
    
	if ($resultado) {
		
		$jsonResponse = json_encode(array(
            "ruta_archivo" => $nombreArchivoZipRuta,
            "http_code" => 200
        ));

        echo $jsonResponse;
		// Cerrar la conexión a la base de datos
		$mysqli->close();
	} else {
		echo "Error creando archivo";
	}
    
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_da_descargar") {
    $comprobante_id = $_POST['comprobante_id'];
    $tipo_documento_id = $_POST['tipo_documento_id'];

	////////////////////////////////////////////////////////////////////////////
	$selectQuery = "SELECT nombre,extension
                    FROM tbl_comprobante_documento
                    WHERE comprobante_id = ?
                    AND tipo_documento_id= ?
                    AND status=1";

    $selectStmt = $mysqli->prepare($selectQuery);
    $selectStmt->bind_param("ii", $comprobante_id, $tipo_documento_id);
    $selectStmt->execute();
    $selectStmt->store_result();

    if ($selectStmt->num_rows > 0) {
        $selectStmt->bind_result($nombre,$extension);
        $selectStmt->fetch();
        $nombreArchivoZipRuta = '/files_bucket/comprobantes/documentos/' . $nombre;

		$jsonResponse = json_encode(array(
            "ruta_archivo" => $nombreArchivoZipRuta,
            "nombre" => $nombre,
            "extension" => $extension

        ));

        echo $jsonResponse;
		
	} else {
		echo "Error creando archivo";
	}
    
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_obtener_pago") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id != NULL) {
        try {
            $stmt = $mysqli->prepare("
                SELECT 
                    c.id, 
                    IFNULL(cf.banco_id, 0) AS banco_id,
                    IFNULL(cf.moneda_id, 0) AS moneda_id,
                    cf.num_cuenta_corriente,
                    cf.num_cuenta_interbancaria,
					IFNULL(cc_pd.nombre, '') AS cd_pago_detraccion,
                    IFNULL(cc_pd.monto, '') AS cd_pd_monto,
                    IFNULL(cc_pd.moneda_id, '') AS cd_pd_moneda_id,
                    IFNULL(cc_pp.nombre, '') AS cd_pago_proveedor,
                    IFNULL(cc_pp.monto, '') AS cd_pp_monto,
                    IFNULL(cc_pp.moneda_id, '') AS cd_pp_moneda_id
                FROM tbl_comprobante c
                LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
                LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
                LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
                LEFT JOIN tbl_comprobante_constancia cc_pd ON cc_pd.comprobante_id = c.id AND cc_pd.tipo_constancia_id =1 AND cc_pd.status=1
                LEFT JOIN tbl_comprobante_constancia cc_pp ON cc_pp.comprobante_id = c.id AND cc_pp.tipo_constancia_id =2 AND cc_pp.status=1
                WHERE c.id=?
                LIMIT 1
            ");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result(
                                $comprobante_id,                            
                                $fp_banco_id,
                                $fp_moneda_id,
                                $fp_num_cuenta_corriente,
                                $fp_num_cuenta_interbancaria,
                                $cd_pago_detraccion,
                                $cd_pd_monto,
                                $cd_pd_moneda_id,
                                $cd_pago_proveedor,
                                $cd_pp_monto,
                                $cd_pp_moneda_id
                            );
                                    
            if ($stmt->fetch() && $fp_banco_id != 0) {
                echo json_encode([
                    'status' => 200,
                    'result' => [
                        'id' => $comprobante_id,                    
                        'fp_banco_id' => $fp_banco_id,
                        'fp_moneda_id' => $fp_moneda_id,
                        'fp_num_cuenta_corriente' => $fp_num_cuenta_corriente,
                        'fp_num_cuenta_interbancaria' => $fp_num_cuenta_interbancaria,
                        'cd_pago_detraccion' => $cd_pago_detraccion,
                        'cd_pd_monto' => $cd_pd_monto,
                        'cd_pd_moneda_id' => $cd_pd_moneda_id,
                        'cd_pago_proveedor' => $cd_pago_proveedor,
                        'cd_pp_monto' => $cd_pp_monto,
                        'cd_pp_moneda_id' => $cd_pp_moneda_id
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_obtener_usuario_registrador_ruc") {
    try {
        $codigo = "usuario_registrador_ruc_contador_comprobante";
        $selectQuery = "SELECT valor FROM tbl_parametros_generales WHERE estado = 1 AND codigo = ? LIMIT 1";
        $selectStmt = $mysqli->prepare($selectQuery);
        $selectStmt->bind_param("s", $codigo);
        $selectStmt->execute();
        $selectStmt->store_result();

        if ($selectStmt->num_rows > 0) {
            $selectStmt->bind_result($valor);
            $selectStmt->fetch();

            $result["http_code"] = 200;
            $result["descripcion"] = $valor;
        } else {
            $result["http_code"] = 400;
            $result["titulo"] = "Alerta";
            $result["descripcion"] = "Sin nombre";
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
