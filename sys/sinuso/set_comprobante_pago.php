<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");
include("function_replace_invalid_caracters.php");
include '/var/www/html/sys/envio_correos.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA RAZON SOCIAL

if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {

        $id = $_POST["id"];

        $path = "/var/www/html/files_bucket/comprobantes/documentos/";
            if (!is_dir($path)) 
            {
                $result["http_code"] = 400;
                $result["titulo"] = 'Error al guardar los archivos';
                $result["descripcion"] = 'No existe la carpeta "documentos" en la ruta "/files_bucket/comprobantes/" del servidor';
                echo json_encode($result);
                exit();
        
                //mkdir($path, 0777, true);
            }

        $param_proveedor_id = $_POST["param_proveedor_id"];
		$param_tipo_comprobante_id = $_POST["param_tipo_comprobante_id"];
        $param_num_documento = $_POST["param_num_documento"];
        $param_fecha_emision = date("Y-m-d", strtotime($_POST["param_fecha_emision"]));
        $param_fecha_vencimiento = date("Y-m-d", strtotime($_POST["param_fecha_vencimiento"]));
        $param_razon_social_id = $_POST["param_razon_social_id"];
        $param_area_id = $_POST["param_area_id"];
        $param_monto = $_POST["param_monto"];
        $param_moneda_id = $_POST["param_moneda_id"];
        $param_num_orden_pago = $_POST["param_num_orden_pago"];
        $param_ceco_id =$_POST["param_ceco_id"];
        /*
        $param_fp_banco_id = $_POST["param_fp_banco_id"];
        $param_fp_moneda_id = $_POST["param_fp_moneda_id"];
        $param_fp_num_cuenta_corriente = $_POST["param_num_cuenta_corriente"];
        $param_fp_num_cuenta_interbancaria = $_POST["param_num_cuenta_interbancaria"];
        */
        if ((int)$id > 0) {

            $error = "";
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            //  CONSULTAR ESTADO PREVIO DEL COMPROBANTE

            $stmt = $mysqli->prepare("
                    SELECT 
                        COALESCE(cp.ruc,'Seleccione'),
                        COALESCE(tc.nombre,'Seleccione'),
                        c.num_documento,
                        IFNULL(c.fecha_emision, '') fecha_emision, 
                        IFNULL(c.fecha_vencimiento, '') fecha_vencimiento,
                        COALESCE(rz.ruc,'Seleccione'),
                        COALESCE(a.nombre,'Seleccione'),
                        COALESCE(CONCAT(m.nombre,' (',m.simbolo,')'),'Seleccione'),
                        c.monto,
                        COALESCE(oc.ceco_id,'Seleccione'),
                        oc.num_orden_pago,
                        IFNULL(cd_cpdf.nombre, '') AS ad_comprobante_pdf,
						IFNULL(cd_cxml.nombre, '') AS ad_comprobante_xml,
						IFNULL(cd_cs.nombre, '') AS ad_contrato_servicio,
						IFNULL(cd_gr.nombre, '') AS ad_guia_remision,
						IFNULL(cd_ac.nombre, '') AS ad_acta_conformidad,
						IFNULL(cd_oc.nombre, '') AS ad_orden_compra    
                    FROM tbl_comprobante c
                    LEFT JOIN tbl_comprobante_orden_compra oc ON oc.comprobante_id = c.id
                    LEFT JOIN tbl_comprobante_proveedor cp ON cp.id = c.proveedor_id
                    LEFT JOIN tbl_comprobante_tipo tc ON tc.id = c.tipo_comprobante_id
                    LEFT JOIN tbl_razon_social rz ON rz.id = c.razon_social_id
                    LEFT JOIN tbl_areas a ON a.id = c.area_id
                    LEFT JOIN tbl_moneda m ON m.id = c.moneda_id
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
                                $proveedor_id, 
                                $tipo_comprobante_id,
                                $num_documento,
                                $fecha_emision,
                                $fecha_vencimiento,
                                $razon_social_id,
                                $area_id,
                                $moneda_id,
                                $monto,
                                $oc_ceco_id,
                                $oc_num_orden_pago,
                                $ad_comprobante_pdf,
                                $ad_comprobante_xml,
                                $ad_contrato_servicio,
                                $ad_guia_remision,
                                $ad_acta_conformidad,
                                $ad_orden_compra);
            $stmt->fetch();
            $stmt->close();

            $historial_query = $mysqli->prepare("
                    INSERT INTO tbl_comprobante_historial_cambios (
                    comprobante_id,
                    valor_anterior,
                    valor_nuevo,
                    nombre_campo,
                    status,
                    user_created_id,
                    created_at
                    ) VALUES (?, ?, ?, ?, 1, ?,?)
                    ");
            
            // Definir un array asociativo para mapear los campos a sus valores originales
            
            $campos_originales = array(
                'proveedor_id' => $proveedor_id,
                'tipo_comprobante_id' => $tipo_comprobante_id,
                'num_documento' => $num_documento,
                'fecha_emision' => $fecha_emision,
                'fecha_vencimiento' => $fecha_vencimiento,
                'razon_social_id' => $razon_social_id,
                'area_id' => $area_id,
                'moneda_id' => $moneda_id,
                'monto' => $monto,
                'oc_ceco_id' => $oc_ceco_id,
                'oc_num_orden_pago' => $oc_num_orden_pago,
                'ad_comprobante_pdf' => $ad_comprobante_pdf,
                'ad_comprobante_xml' => $ad_comprobante_xml,
                'ad_contrato_servicio' => $ad_contrato_servicio,
                'ad_guia_remision' => $ad_guia_remision,
                'ad_acta_conformidad' => $ad_acta_conformidad,
                'ad_orden_compra' => $ad_orden_compra,
                );
            $cambios_realizados = false;

            foreach ($campos_originales as $campo => $valor_anterior) {

                // Comparar el valor original con el valor actual en $_POST
                $valor_nuevo = $_POST[$campo];

                if ((string)$valor_anterior != (string)$valor_nuevo) {
                    // Si hay un cambio, registrar en el historial
                    $cambios_realizados = true;
                    $historial_query->bind_param("isssis", $id, $valor_anterior, $valor_nuevo, $campo, $usuario_id, $fecha);
                    $historial_query->execute();
                }
            }

            $historial_query->close();


            if ($cambios_realizados) {

                //  ACTUALIZACION DE DATOS DE COMPROBANTE DE PAGO

                $query_update_comprobante = "UPDATE tbl_comprobante 
                    SET proveedor_id = '".$param_proveedor_id."',
                    tipo_comprobante_id = '".$param_tipo_comprobante_id."',
                    num_documento = '".$param_num_documento."',
                    fecha_emision = '".$param_fecha_emision."',
                    fecha_vencimiento = '".$param_fecha_vencimiento."',
                    razon_social_id = '".$param_razon_social_id."',
                    area_id = '".$param_area_id."',
                    monto = '".$param_monto."',
                    moneda_id = '".$param_moneda_id."',             
                    user_updated_id = '".$usuario_id."',
                    updated_at = '".date('Y-m-d H:i:s')."'
                    WHERE id = {$id}
                    ";
                $mysqli->query($query_update_comprobante);

                if($mysqli->error){
                    $error = $mysqli->error;

                    $result["http_code"] = 400;
                    $result["titulo"] = "Error al editar.";
                    $result["descripcion"] = $error;
                    $result["query"] = $query_update_comprobante;

                    echo json_encode($result);
                    exit();
                }

                //  ACTUALIZACION DE DATOS DE ORDEN DE COMPRA
                
                $query_update_comprobante_oc = "UPDATE tbl_comprobante_orden_compra 
                    SET ceco_id = '".$oc_ceco_id."',
                    num_orden_pago = '".$param_num_orden_pago."', 
                    user_updated_id = '".$usuario_id."',
                    updated_at = '".$fecha."'
                    WHERE comprobante_id = {$id}
                    ";
                $mysqli->query($query_update_comprobante_oc);

                if($mysqli->error){
                    $error = $mysqli->error;

                    $result["http_code"] = 400;
                    $result["titulo"] = "Error al editar.";
                    $result["descripcion"] = $error;
                    $result["query"] = $query_update_comprobante_oc;

                    echo json_encode($result);
                    exit();
                }

                //  ACTUALIZACION DE DATOS DE FORMA DE PAGO
                /*
                $query_update_comprobante_fp = "UPDATE tbl_comprobante_forma_pago 
                    SET banco_id = '".$param_fp_banco_id."',
                    moneda_id = '".$param_fp_moneda_id."',
                    num_cuenta_corriente = '".$param_fp_num_cuenta_corriente."',
                    num_cuenta_interbancaria = '".$param_fp_num_cuenta_interbancaria."',
                    user_updated_id = '".$usuario_id."',
                    updated_at = '".date('Y-m-d H:i:s')."'
                    WHERE comprobante_id = {$id}
                    ";
                $mysqli->query($query_update_comprobante_fp);

                if($mysqli->error){
                    $error = $mysqli->error;

                    $result["http_code"] = 400;
                    $result["titulo"] = "Error al editar.";
                    $result["descripcion"] = $error;
                    $result["query"] = $query_update_comprobante_fp;

                    echo json_encode($result);
                    exit();
                }
                */

                //  GUARDAR CAMBIOS DE ARCHIVOS ADJUNTOS
                if ($_POST['ad_acta_conformidad'] != $ad_acta_conformidad ){

                    //  DESHABILITAR ARCHIVO ANTERIORES
                    $query_update_comprobante = "UPDATE tbl_comprobante_documento 
                                                SET 
                                                    status = 0,
                                                    user_updated_id = ?,
                                                    updated_at = ?
                                                WHERE 
                                                    comprobante_id = ?
                                                AND tipo_documento_id = 5";
                    $stmt = $mysqli->prepare($query_update_comprobante);
                    $stmt->bind_param("iss", $usuario_id, $fecha, $id);

                    if ($stmt->execute()) {
                        if (isset($_FILES['form_comp_da_param_ac_archivo']) && $_FILES['form_comp_da_param_ac_archivo']['error'] === UPLOAD_ERR_OK) {
                            if (!is_dir($path)) mkdir($path, 0777, true);
                            $filename = $_FILES['form_comp_da_param_ac_archivo']['name'];
                            $filenametem = $_FILES['form_comp_da_param_ac_archivo']['tmp_name'];
                            $filesize = $_FILES['form_comp_da_param_ac_archivo']['size'];
                            $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                            
                            if($filename != ""){
                                $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                $nombre_archivo = $param_num_documento . " - Acta de Conformidad - ".date('YmdHis') . "." . $fileExt;
                                move_uploaded_file($filenametem, $path . $nombre_archivo);

                                $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                                comprobante_id,
                                                                tipo_documento_id,
                                                                nombre,
                                                                extension,
                                                                size,
                                                                ruta,
                                                                download,
                                                                status,
                                                                user_created_id,
                                                                user_updated_id,
                                                                created_at,
                                                                updated_at
                                                                ) 
                                                            VALUES (?, 5, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                $stmtCd->bind_param("isssssiiss", $id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);

                                if ($stmtCd->execute()) {

                                    $result["http_code"] = 200;
                                    $result["titulo"] = "Creación exitosa.";
                                    $result["descripcion"] = "El comprobante se registro éxitosamente";
                                } else {
                                    $error = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                }
                                $stmtCd->close();
                            }
                        }
                    }
                }

                if ($_POST['ad_comprobante_pdf'] != $ad_comprobante_pdf ){

                    //  DESHABILITAR ARCHIVO ANTERIORES
                    $query_update_comprobante = "UPDATE tbl_comprobante_documento 
                                                SET 
                                                    status = 0,
                                                    user_updated_id = ?,
                                                    updated_at = ?
                                                WHERE 
                                                    comprobante_id = ?
                                                AND tipo_documento_id = 1";
                    $stmt = $mysqli->prepare($query_update_comprobante);
                    $stmt->bind_param("iss", $usuario_id, $fecha, $id);

                    if ($stmt->execute()) {
                        if (isset($_FILES['form_comp_da_param_cpdf_archivo']) && $_FILES['form_comp_da_param_cpdf_archivo']['error'] === UPLOAD_ERR_OK) {
                            if (!is_dir($path)) mkdir($path, 0777, true);
                            $filename = $_FILES['form_comp_da_param_cpdf_archivo']['name'];
                            $filenametem = $_FILES['form_comp_da_param_cpdf_archivo']['tmp_name'];
                            $filesize = $_FILES['form_comp_da_param_cpdf_archivo']['size'];
                            $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                            
                            if($filename != ""){
                                $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                $ruc_proveedor =  $_POST["proveedor_id"];
                                $nombre_archivo = "F".$ruc_proveedor . " - ".$param_fecha_emision."_".date("YmdHis"). "." . $fileExt;
                                move_uploaded_file($filenametem, $path . $nombre_archivo);
    
                                $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                                comprobante_id,
                                                                tipo_documento_id,
                                                                nombre,
                                                                extension,
                                                                size,
                                                                ruta,
                                                                download,
                                                                status,
                                                                user_created_id,
                                                                user_updated_id,
                                                                created_at,
                                                                updated_at
                                                                ) 
                                                            VALUES (?, 1, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                $stmtCd->bind_param("isssssiiss", $id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);
    
                                if ($stmtCd->execute()) {
    
                                    $result["http_code"] = 200;
                                    $result["titulo"] = "Creación exitosa.";
                                    $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                                } else {
                                    $result["http_code"] = 400;
                                    $result["titulo"] = "Error.";
                                    $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                }
                                $stmtCd->close();
                            }
                        }else {

                            $error = "No se encontró el Comprobante(PDF) ";
                        }
                    }
                }

                if ($_POST['ad_comprobante_xml'] != $ad_comprobante_xml ){

                    //  DESHABILITAR ARCHIVO ANTERIORES
                    $query_update_comprobante = "UPDATE tbl_comprobante_documento 
                                                SET 
                                                    status = 0,
                                                    user_updated_id = ?,
                                                    updated_at = ?
                                                WHERE 
                                                    comprobante_id = ?
                                                AND tipo_documento_id = 2";
                    $stmt = $mysqli->prepare($query_update_comprobante);
                    $stmt->bind_param("iss", $usuario_id, $fecha, $id);

                    if ($stmt->execute()) {
                        if (isset($_FILES['form_comp_da_param_cxml_archivo']) && $_FILES['form_comp_da_param_cxml_archivo']['error'] === UPLOAD_ERR_OK) {
                            if (!is_dir($path)) mkdir($path, 0777, true);
                            $filename = $_FILES['form_comp_da_param_cxml_archivo']['name'];
                            $filenametem = $_FILES['form_comp_da_param_cxml_archivo']['tmp_name'];
                            $filesize = $_FILES['form_comp_da_param_cxml_archivo']['size'];
                            $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                            
                            if($filename != ""){
                                $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                $ruc_proveedor =  $_POST["proveedor_id"];
                                $nombre_archivo = "F".$ruc_proveedor . " - ".$param_fecha_emision."_".date("YmdHis"). "." . $fileExt;
                                move_uploaded_file($filenametem, $path . $nombre_archivo);
    
                                $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                                comprobante_id,
                                                                tipo_documento_id,
                                                                nombre,
                                                                extension,
                                                                size,
                                                                ruta,
                                                                download,
                                                                status,
                                                                user_created_id,
                                                                user_updated_id,
                                                                created_at,
                                                                updated_at
                                                                ) 
                                                            VALUES (?, 2, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                $stmtCd->bind_param("isssssiiss", $id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);
    
                                if ($stmtCd->execute()) {
    
                                    $result["http_code"] = 200;
                                    $result["titulo"] = "Creación exitosa.";
                                    $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                                } else {
                                    $result["http_code"] = 400;
                                    $result["titulo"] = "Error.";
                                    $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                }
                                $stmtCd->close();
                            }
                        }elseif($param_tipo_comprobante_id ==1 || $param_tipo_comprobante_id ==3) {
                            $error = "No se encontró el Comprobante (XML)";
                        }
                    }
                }

                if ($_POST['ad_contrato_servicio'] != $ad_contrato_servicio ){

                    //  DESHABILITAR ARCHIVO ANTERIORES
                    $query_update_comprobante = "UPDATE tbl_comprobante_documento 
                                                SET 
                                                    status = 0,
                                                    user_updated_id = ?,
                                                    updated_at = ?
                                                WHERE 
                                                    comprobante_id = ?
                                                AND tipo_documento_id = 3";
                    $stmt = $mysqli->prepare($query_update_comprobante);
                    $stmt->bind_param("iss", $usuario_id, $fecha, $id);

                    if ($stmt->execute()) {
                        if (isset($_FILES['form_comp_da_param_cs_archivo']) && $_FILES['form_comp_da_param_cs_archivo']['error'] === UPLOAD_ERR_OK) {
                            if (!is_dir($path)) mkdir($path, 0777, true);
                            $filename = $_FILES['form_comp_da_param_cs_archivo']['name'];
                            $filenametem = $_FILES['form_comp_da_param_cs_archivo']['tmp_name'];
                            $filesize = $_FILES['form_comp_da_param_cs_archivo']['size'];
                            $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                            
                            if($filename != ""){
                                $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                //$ruc_proveedor =  $_POST["proveedor_id"];
                                $nombre_archivo = $param_num_documento . " - Contrato de Servicio o Licencia firmado - ".date('YmdHis') . "." . $fileExt;
                                move_uploaded_file($filenametem, $path . $nombre_archivo);
    
                                $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                                comprobante_id,
                                                                tipo_documento_id,
                                                                nombre,
                                                                extension,
                                                                size,
                                                                ruta,
                                                                download,
                                                                status,
                                                                user_created_id,
                                                                user_updated_id,
                                                                created_at,
                                                                updated_at
                                                                ) 
                                                            VALUES (?, 3, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                $stmtCd->bind_param("isssssiiss", $id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);
    
                                if ($stmtCd->execute()) {
    
                                    $result["http_code"] = 200;
                                    $result["titulo"] = "Creación exitosa.";
                                    $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                                } else {
                                    $result["http_code"] = 400;
                                    $result["titulo"] = "Error.";
                                    $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                }
                                $stmtCd->close();
                            }
                        }else {

                            //$error = "No se encontró el Comprobante (XML)";
                        }
                    }
                }

                if ($_POST['ad_guia_remision'] != $ad_guia_remision){

                    //  DESHABILITAR ARCHIVO ANTERIORES
                    $query_update_comprobante = "UPDATE tbl_comprobante_documento 
                                                SET 
                                                    status = 0,
                                                    user_updated_id = ?,
                                                    updated_at = ?
                                                WHERE 
                                                    comprobante_id = ?
                                                AND tipo_documento_id = 4";
                    $stmt = $mysqli->prepare($query_update_comprobante);
                    $stmt->bind_param("iss", $usuario_id, $fecha, $id);

                    if ($stmt->execute()) {
                        if (isset($_FILES['form_comp_da_param_gr_archivo']) && $_FILES['form_comp_da_param_gr_archivo']['error'] === UPLOAD_ERR_OK) {
                            if (!is_dir($path)) mkdir($path, 0777, true);
                            $filename = $_FILES['form_comp_da_param_gr_archivo']['name'];
                            $filenametem = $_FILES['form_comp_da_param_gr_archivo']['tmp_name'];
                            $filesize = $_FILES['form_comp_da_param_gr_archivo']['size'];
                            $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                            
                            if($filename != ""){
                                $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                $nombre_archivo = $param_num_documento . " - Guia de Remisión - ".date('YmdHis') . "." . $fileExt;
                                move_uploaded_file($filenametem, $path . $nombre_archivo);
    
                                $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                                comprobante_id,
                                                                tipo_documento_id,
                                                                nombre,
                                                                extension,
                                                                size,
                                                                ruta,
                                                                download,
                                                                status,
                                                                user_created_id,
                                                                user_updated_id,
                                                                created_at,
                                                                updated_at
                                                                ) 
                                                            VALUES (?, 4, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                $stmtCd->bind_param("isssssiiss", $id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);
    
                                if ($stmtCd->execute()) {
    
                                    $result["http_code"] = 200;
                                    $result["titulo"] = "Creación exitosa.";
                                    $result["descripcion"] = "Se guardaron los cambios correctamente.";
                                } else {
                                    $result["http_code"] = 400;
                                    $result["titulo"] = "Error.";
                                    $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                }
                                $stmtCd->close();
                            }
                        }else {

                            $error = "No se encontró la Guía de Remisión(PDF)";
                        }
                    }
                }

                if ($_POST['ad_orden_compra'] != $ad_orden_compra){

                    //  DESHABILITAR ARCHIVO ANTERIORES
                    $query_update_comprobante = "UPDATE tbl_comprobante_documento 
                                                SET 
                                                    status = 0,
                                                    user_updated_id = ?,
                                                    updated_at = ?
                                                WHERE 
                                                    comprobante_id = ?
                                                AND tipo_documento_id = 6";
                    $stmt = $mysqli->prepare($query_update_comprobante);
                    $stmt->bind_param("iss", $usuario_id, $fecha, $id);

                    if ($stmt->execute()) {
                        if (isset($_FILES['form_comp_da_param_oc_archivo']) && $_FILES['form_comp_da_param_oc_archivo']['error'] === UPLOAD_ERR_OK) {
                            if (!is_dir($path)) mkdir($path, 0777, true);
                            $filename = $_FILES['form_comp_da_param_oc_archivo']['name'];
                            $filenametem = $_FILES['form_comp_da_param_oc_archivo']['tmp_name'];
                            $filesize = $_FILES['form_comp_da_param_oc_archivo']['size'];
                            $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                            
                            if($filename != ""){
                                $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                //$ruc_proveedor =  $_POST["proveedor_id"];
                                $nombre_archivo = $param_num_documento . " - Orden de Compra - ".date('YmdHis') . "." . $fileExt;
                                move_uploaded_file($filenametem, $path . $nombre_archivo);
    
                                $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                                comprobante_id,
                                                                tipo_documento_id,
                                                                nombre,
                                                                extension,
                                                                size,
                                                                ruta,
                                                                download,
                                                                status,
                                                                user_created_id,
                                                                user_updated_id,
                                                                created_at,
                                                                updated_at
                                                                ) 
                                                            VALUES (?, 6, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                $stmtCd->bind_param("isssssiiss", $id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);
    
                                if ($stmtCd->execute()) {
    
                                    $result["http_code"] = 200;
                                    $result["titulo"] = "Creación exitosa.";
                                    $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                                } else {
                                    $result["http_code"] = 400;
                                    $result["titulo"] = "Error.";
                                    $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                }
                                $stmtCd->close();
                            }
                        }else {

                            //$error = "No se encontró la Guía de Remisión(PDF)";
                        }
                    }
                }

            }else{
                $result["http_code"] = 400;
                $result["titulo"] = "Editar";
                $result["descripcion"] = "No hay cambios para guardar";

                echo json_encode($result);
                exit();
            }

            if($error == ''){
                $result["http_code"] = 200;
                $result["titulo"] = "Edición exitosa";
                $result["descripcion"] = "El comprobante se editó éxitosamente";

                echo json_encode($result);
                exit();
            }
            else{
                $result["http_code"] = 400;
                $result["titulo"] = "Error al guardar.";
                $result["descripcion"] = "Error en la preparación de la consulta: " . $error;

                echo json_encode($result);
                exit();
            }

		}else{

            $error = '';

            //   DATOS DEL COMPROBANTE DE PAGO
            
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_comprobante = "
                INSERT INTO tbl_comprobante (
                    proveedor_id,
                    tipo_comprobante_id,
                    num_documento,
                    fecha_emision,
                    fecha_vencimiento,
                    razon_social_id,
                    area_id,
                    etapa_id,
                    moneda_id,
                    monto,
                    status,
                    created_at,
                    user_created_id
                )  
                VALUES (?,?,?,?,?,?,?,1,?,?, 1, ?, ?)
            ";

            $stmt = $mysqli->prepare($sql_insert_comprobante);


            if ($stmt) {
                $stmt->bind_param("iisssiiidsi", 
                                        $param_proveedor_id, 
                                        $param_tipo_comprobante_id,
                                        $param_num_documento, 
                                        $param_fecha_emision, 
                                        $param_fecha_vencimiento,
                                        $param_razon_social_id,
                                        $param_area_id,
                                        $param_moneda_id,
                                        $param_monto,
                                        $fecha,
                                        $usuario_id
                                    );
                try {
                    $stmt->execute();
                    $result["http_code"] = 200;
                    $result["titulo"] = "Creación exitosa.";
                    $result["descripcion"] = "El comprobante se registro éxitosamente";
                    $comprobante_id = $mysqli->insert_id;

                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["titulo"] = "Error al guardar.";
                    $result["descripcion"] = "Error al ejecutar la consulta: " . $mysqli->error;
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["titulo"] = "Error al guardar.";
                $result["descripcion"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }

            //   DATOS DE ORDEN DE COMPRA

            $error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_comprobante_op = "
                INSERT INTO tbl_comprobante_orden_compra (
                    comprobante_id,
                    ceco_id,
                    num_orden_pago,
                    status,
                    created_at,
                    user_created_id
                )  
                VALUES (?,?,?,1,?,?)
            ";

            $stmt = $mysqli->prepare($sql_insert_comprobante_op);


            if ($stmt) {
                $stmt->bind_param("isssi", 
                                        $comprobante_id, 
                                        $param_ceco_id,
                                        $param_num_orden_pago, 
                                        $fecha,
                                        $usuario_id
                                    );
                try {
                    $stmt->execute();

                    ////    SUBIR ARCHIVOS  /////////////////////////////////////////////////////

                    if (isset($_FILES['form_comp_da_param_ac_archivo']) && $_FILES['form_comp_da_param_ac_archivo']['error'] === UPLOAD_ERR_OK) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        $filename = $_FILES['form_comp_da_param_ac_archivo']['name'];
                        $filenametem = $_FILES['form_comp_da_param_ac_archivo']['tmp_name'];
                        $filesize = $_FILES['form_comp_da_param_ac_archivo']['size'];
                        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                        
                        if($filename != ""){
                            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                            $nombre_archivo = $param_num_documento . " - Acta de Conformidad - ".date('YmdHis') . "." . $fileExt;
                            move_uploaded_file($filenametem, $path . $nombre_archivo);

                            $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                            comprobante_id,
                                                            tipo_documento_id,
                                                            nombre,
                                                            extension,
                                                            size,
                                                            ruta,
                                                            download,
                                                            status,
                                                            user_created_id,
                                                            user_updated_id,
                                                            created_at,
                                                            updated_at
                                                            ) 
                                                        VALUES (?, 5, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                            $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                            $stmtCd->bind_param("isssssiiss", $comprobante_id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);

                            if ($stmtCd->execute()) {

                                $result["http_code"] = 200;
                                $result["titulo"] = "Creación exitosa.";
                                $result["descripcion"] = "El comprobante se registro éxitosamente";
                            } else {
                                $result["http_code"] = 400;
                                $result["titulo"] = "Error.";
                                $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                            }
                            $stmtCd->close();
                        }
                    }

                    if (isset($_FILES['form_comp_da_param_cpdf_archivo']) && $_FILES['form_comp_da_param_cpdf_archivo']['error'] === UPLOAD_ERR_OK) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        $filename = $_FILES['form_comp_da_param_cpdf_archivo']['name'];
                        $filenametem = $_FILES['form_comp_da_param_cpdf_archivo']['tmp_name'];
                        $filesize = $_FILES['form_comp_da_param_cpdf_archivo']['size'];
                        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                        
                        if($filename != ""){
                            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                            $ruc_proveedor =  $_POST["proveedor_id"];
                            $nombre_archivo = "F".$ruc_proveedor . " - ".$param_fecha_emision."_".date("YmdHis"). "." . $fileExt;
                            move_uploaded_file($filenametem, $path . $nombre_archivo);

                            $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                            comprobante_id,
                                                            tipo_documento_id,
                                                            nombre,
                                                            extension,
                                                            size,
                                                            ruta,
                                                            download,
                                                            status,
                                                            user_created_id,
                                                            user_updated_id,
                                                            created_at,
                                                            updated_at
                                                            ) 
                                                        VALUES (?, 1, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                            $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                            $stmtCd->bind_param("isssssiiss", $comprobante_id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);

                            if ($stmtCd->execute()) {

                                $result["http_code"] = 200;
                                $result["titulo"] = "Creación exitosa.";
                                $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                            } else {
                                $result["http_code"] = 400;
                                $result["titulo"] = "Error.";
                                $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                            }
                            $stmtCd->close();
                        }
                    }else {
                        $result["http_code"] = 400;
                        $result["titulo"] = "Error.";
                        $result["descripcion"] = "Nose encontro el Comprobante (PDF) ";
                    }

                    if (isset($_FILES['form_comp_da_param_cxml_archivo']) && $_FILES['form_comp_da_param_cxml_archivo']['error'] === UPLOAD_ERR_OK) {
                            if (!is_dir($path)) mkdir($path, 0777, true);
                            $filename = $_FILES['form_comp_da_param_cxml_archivo']['name'];
                            $filenametem = $_FILES['form_comp_da_param_cxml_archivo']['tmp_name'];
                            $filesize = $_FILES['form_comp_da_param_cxml_archivo']['size'];
                            $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                            
                            if($filename != ""){
                                $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                $ruc_proveedor =  $_POST["proveedor_id"];
                                $nombre_archivo = "F".$ruc_proveedor . " - ".$param_fecha_emision."_".date("YmdHis")."." . $fileExt;
                                move_uploaded_file($filenametem, $path . $nombre_archivo);

                                $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                                comprobante_id,
                                                                tipo_documento_id,
                                                                nombre,
                                                                extension,
                                                                size,
                                                                ruta,
                                                                download,
                                                                status,
                                                                user_created_id,
                                                                user_updated_id,
                                                                created_at,
                                                                updated_at
                                                                ) 
                                                            VALUES (?, 2, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                $stmtCd->bind_param("isssssiiss", $comprobante_id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);

                                if ($stmtCd->execute()) {

                                    $result["http_code"] = 200;
                                    $result["titulo"] = "Creación exitosa.";
                                    $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                                } else {
                                    $result["http_code"] = 400;
                                    $result["titulo"] = "Error.";
                                    $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                }
                                $stmtCd->close();
                            }
                        }elseif($param_tipo_comprobante_id ==1 || $param_tipo_comprobante_id ==3) {
                            $result["http_code"] = 400;
                            $result["titulo"] = "Error.";
                            $result["descripcion"] = "Nose encontro el Comprobante (XML) ";
                        }
                    

                    if (isset($_FILES['form_comp_da_param_cs_archivo']) && $_FILES['form_comp_da_param_cs_archivo']['error'] === UPLOAD_ERR_OK) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        $filename = $_FILES['form_comp_da_param_cs_archivo']['name'];
                        $filenametem = $_FILES['form_comp_da_param_cs_archivo']['tmp_name'];
                        $filesize = $_FILES['form_comp_da_param_cs_archivo']['size'];
                        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                        
                        if($filename != ""){
                            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                            //$ruc_proveedor =  $_POST["proveedor_id"];
                            $nombre_archivo = $param_num_documento . " - Contrato de Servicio o Licencia firmado - ".date('YmdHis') . "." . $fileExt;
                            move_uploaded_file($filenametem, $path . $nombre_archivo);

                            $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                            comprobante_id,
                                                            tipo_documento_id,
                                                            nombre,
                                                            extension,
                                                            size,
                                                            ruta,
                                                            download,
                                                            status,
                                                            user_created_id,
                                                            user_updated_id,
                                                            created_at,
                                                            updated_at
                                                            ) 
                                                        VALUES (?, 3, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                            $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                            $stmtCd->bind_param("isssssiiss", $comprobante_id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);

                            if ($stmtCd->execute()) {

                                $result["http_code"] = 200;
                                $result["titulo"] = "Creación exitosa.";
                                $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                            } else {
                                $result["http_code"] = 400;
                                $result["titulo"] = "Error.";
                                $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                            }
                            $stmtCd->close();
                        }
                    }
                    
                    //  VERIFICAR SI EXISTE LA ORDEN DE COMPRA
                    if (isset($_FILES['form_comp_da_param_oc_archivo']) && $_FILES['form_comp_da_param_oc_archivo']['error'] === UPLOAD_ERR_OK) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        $filename = $_FILES['form_comp_da_param_oc_archivo']['name'];
                        $filenametem = $_FILES['form_comp_da_param_oc_archivo']['tmp_name'];
                        $filesize = $_FILES['form_comp_da_param_oc_archivo']['size'];
                        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                        
                        if($filename != ""){
                            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                            //$ruc_proveedor =  $_POST["proveedor_id"];
                            $nombre_archivo = $param_num_documento . " - Orden de Compra - ".date('YmdHis') . "." . $fileExt;
                            move_uploaded_file($filenametem, $path . $nombre_archivo);

                            $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                            comprobante_id,
                                                            tipo_documento_id,
                                                            nombre,
                                                            extension,
                                                            size,
                                                            ruta,
                                                            download,
                                                            status,
                                                            user_created_id,
                                                            user_updated_id,
                                                            created_at,
                                                            updated_at
                                                            ) 
                                                        VALUES (?, 6, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                            $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                            $stmtCd->bind_param("isssssiiss", $comprobante_id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);

                            if ($stmtCd->execute()) {

                                $result["http_code"] = 200;
                                $result["titulo"] = "Creación exitosa.";
                                $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                            } else {
                                $result["http_code"] = 400;
                                $result["titulo"] = "Error.";
                                $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                            }
                            $stmtCd->close();
                        }
                    }else if(!isset($_FILES['form_comp_da_param_cs_archivo']) && $_FILES['form_comp_da_param_cs_archivo']['error'] != UPLOAD_ERR_OK) {
                        $result["http_code"] = 400;
                        $result["titulo"] = "Error.";
                        $result["descripcion"] = "Nose encontro el Contrato de servicio o licencia firmado ni la orden de compra ";
                    }

                    if (isset($_FILES['form_comp_da_param_gr_archivo']) && $_FILES['form_comp_da_param_gr_archivo']['error'] === UPLOAD_ERR_OK) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        $filename = $_FILES['form_comp_da_param_gr_archivo']['name'];
                        $filenametem = $_FILES['form_comp_da_param_gr_archivo']['tmp_name'];
                        $filesize = $_FILES['form_comp_da_param_gr_archivo']['size'];
                        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                        
                        if($filename != ""){
                            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                            $nombre_archivo = $param_num_documento . " - Guia de Remisión - ".date('YmdHis') . "." . $fileExt;
                            move_uploaded_file($filenametem, $path . $nombre_archivo);

                            $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_documento (
                                                            comprobante_id,
                                                            tipo_documento_id,
                                                            nombre,
                                                            extension,
                                                            size,
                                                            ruta,
                                                            download,
                                                            status,
                                                            user_created_id,
                                                            user_updated_id,
                                                            created_at,
                                                            updated_at
                                                            ) 
                                                        VALUES (?, 4, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                            $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                            $stmtCd->bind_param("isssssiiss", $comprobante_id, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);

                            if ($stmtCd->execute()) {

                                $result["http_code"] = 200;
                                $result["titulo"] = "Creación exitosa.";
                                $result["descripcion"] = "Se guardaron los cambios correctamente.";
                            } else {
                                $result["http_code"] = 400;
                                $result["titulo"] = "Error.";
                                $result["descripcion"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                            }
                            $stmtCd->close();
                        }
                    }

                /////////////////////////////////////////////////////////////////////////////

                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["titulo"] = "Error.";
                    $result["descripcion"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["titulo"] = "Error al guardar.";
                $result["descripcion"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }

        }
    } else {
        $result["http_code"] = 400;
        $result["titulo"] = "Error.";
        $result["descripcion"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $comprobante_id = $_POST["comprobante_id"];
        $motivo = $_POST["motivo"];

        if ((int)$comprobante_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_comprobante = "UPDATE tbl_comprobante 
                                         SET 
                                             status = 0,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_comprobante);
            $stmt->bind_param("iss", $usuario_id, $fecha, $comprobante_id);

            if ($stmt->execute()) {

                //  REGISTRAR CAMBIOS DE ESTADO
                $sql_insert_historico_estado = "INSERT INTO tbl_comprobante_historial_estado (
                                                    comprobante_id,
                                                    estado_id,
                                                    motivo,
                                                    status,
                                                    created_at,
                                                    user_created_id
                                                ) 
                                                VALUES (?, 0, ?, 1, ?, ?)";
                $stmtEstado = $mysqli->prepare($sql_insert_historico_estado);
                //$motivo = ''; // Debes asignar un valor a $motivo aquí
                $stmtEstado->bind_param("issi", $comprobante_id, $motivo, $fecha, $usuario_id);

                if ($stmtEstado->execute()) {
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta de inserción de historial: " . $stmtEstado->error;
                }
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "El ID del comprobante no es válido.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_etapa_cambiar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $comprobante_id = $_POST["comprobante_id"];
        $etapa_id = $_POST["etapa_id"];

        if ((int)$comprobante_id > 0) {
            $error = '';

            //  ACTUALIZAR ETAPA
            $query_update_comprobante = "UPDATE tbl_comprobante 
                                         SET 
                                             etapa_id = ?,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_comprobante);
            $stmt->bind_param("iiss", $etapa_id, $usuario_id, $fecha, $comprobante_id);

            if ($stmt->execute()) {

                //  REGISTRAR CAMBIOS DE ETAPA
                $sql_insert_historico_estado = "INSERT INTO tbl_comprobante_historial_etapas (
                                                    comprobante_id,
                                                    etapa_id,
                                                    status,
                                                    created_at,
                                                    user_updated_id
                                                ) 
                                                VALUES (?, ?, 1, ?, ?)";
                $stmtEstado = $mysqli->prepare($sql_insert_historico_estado);
                $stmtEstado->bind_param("iisi", $comprobante_id, $etapa_id, $fecha, $usuario_id);

                if ($stmtEstado->execute()) {
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta de inserción de historial: " . $stmtEstado->error;
                }
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "El ID del comprobante no es válido.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_observar") {
    
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $comprobante_id = $_POST["comprobante_id"];
        $motivo = $_POST["motivo"];
        $etapa_id = $_POST["etapa_id"];

        if ((int)$comprobante_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_comprobante = "UPDATE tbl_comprobante 
                                         SET 
                                             etapa_id = ?,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_comprobante);
            $stmt->bind_param("iiss", $etapa_id, $usuario_id, $fecha, $comprobante_id);

            if ($stmt->execute()) {

                if($etapa_id == 7){
                    //  ACTUALIZAR ESTADO
                    $query_update_comprobante = "UPDATE tbl_comprobante 
                                SET 
                                    status = 0,
                                    user_updated_id = ?,
                                    updated_at = ?
                                WHERE 
                                    id = ?";
                    $stmt = $mysqli->prepare($query_update_comprobante);
                    $stmt->bind_param("iss", $usuario_id, $fecha, $comprobante_id);

                    $stmt->execute();
                 }

                //  REGISTRAR CAMBIOS DE ESTADO
                $sql_insert_historico_estado = "INSERT INTO tbl_comprobante_historial_etapas (
                                                    comprobante_id,
                                                    etapa_id,
                                                    motivo,
                                                    status,
                                                    created_at,
                                                    user_updated_id
                                                ) 
                                                VALUES (?, ?, ?, 1, ?, ?)";
                    $stmtEstado = $mysqli->prepare($sql_insert_historico_estado);
                    //$motivo = ''; // Debes asignar un valor a $motivo aquí
                    $motivo=strtoupper(replace_invalid_caracters($motivo));
                    $stmtEstado->bind_param("iissi", $comprobante_id, $etapa_id, $motivo, $fecha, $usuario_id);

                    if ($stmtEstado->execute()) {
                        $result["http_code"] = 200;
                        $result["status"] = "Datos obtenidos de gestión.";
                        if($etapa_id == 4){
                            sendEmailObservacion($comprobante_id, $motivo, $fecha);

                        }elseif($etapa_id == 6){
                            sendEmailObservacionTesoreria($comprobante_id, $motivo, $fecha);

                        }

                    } else {
                        $result["http_code"] = 400;
                        $result["error"] = "Error al ejecutar la consulta de inserción de historial: " . $stmtEstado->error;
                    }
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "El ID del comprobante no es válido.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_pagar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    
    if ((int)$usuario_id > 0) {
        $comprobante_id = $_POST["comprobante_id"];
        $param_cd_monto = $_POST["param_cd_monto"];
        $param_cd_moneda_id = $_POST["param_cd_moneda_id"];
        $param_cpp_monto = $_POST["param_cpp_monto"];
        $param_cpp_moneda_id = $_POST["param_cpp_moneda_id"];

        $param_fp_banco_id = $_POST["param_fp_banco_id"];
        $param_fp_moneda_id = $_POST["param_fp_moneda_id"];
        $param_fp_num_cuenta_corriente = $_POST["param_fp_num_cuenta_corriente"];
        $param_fp_num_cuenta_interbancaria = $_POST["param_fp_num_cuenta_interbancaria"];
        $pagado_anteriormente = $_POST["pagado"];


        if ((int)$comprobante_id > 0) {
            $error = '';

            $path = "/var/www/html/files_bucket/comprobantes/constancias/";
            if (!is_dir($path)) 
            {
                $result["http_code"] = 400;
                $result["error"] = 'No existe la carpeta "constancias" en la ruta "/files_bucket/comprobantes/" del servidor';
                echo json_encode($result);
                exit();
        
                //mkdir($path, 0777, true);
            }

            //  ACTUALIZAR ETAPA
            $query_update_comprobante = "UPDATE tbl_comprobante 
                                         SET 
                                             etapa_id = 5,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_comprobante);
            $stmt->bind_param("iss", $usuario_id, $fecha, $comprobante_id);

            if ($stmt->execute()) {

                //  REGISTRAR CAMBIOS DE ETAPA
                $sql_insert_historico_estado = "INSERT INTO tbl_comprobante_historial_etapas (
                                                    comprobante_id,
                                                    etapa_id,
                                                    status,
                                                    created_at,
                                                    user_updated_id
                                                ) 
                                                VALUES (?, 5, 1, ?, ?)";
                $stmtEstado = $mysqli->prepare($sql_insert_historico_estado);
                $stmtEstado->bind_param("isi", $comprobante_id, $fecha, $usuario_id);

                if ($stmtEstado->execute()) {
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta de inserción de historial: " . $stmtEstado->error;
                }
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmt->error;
            }
            $stmt->close();

            //  REGISTRAR DATOS DE PAGO

            //  VERFICAR SI EL COMPROBANTE FUE PAGADO ANTERIORMENTE

            if ($pagado_anteriormente == 0) {
            //  CONSTANCIA DE DETRACCION
                $selectQuery = "SELECT num_documento 
                            FROM tbl_comprobante
                            WHERE id = ?";

                $selectStmt = $mysqli->prepare($selectQuery);
                $selectStmt->bind_param("i", $comprobante_id);
                $selectStmt->execute();
                $selectStmt->store_result();

                if ($selectStmt->num_rows > 0) {
                    $selectStmt->bind_result($comprobante_num_documento);
                    $selectStmt->fetch();

                    if (isset($_FILES['form_comp_pagar_cd_param_archivo']) && $_FILES['form_comp_pagar_cd_param_archivo']['error'] === UPLOAD_ERR_OK) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        $filename = $_FILES['form_comp_pagar_cd_param_archivo']['name'];
                        $filenametem = $_FILES['form_comp_pagar_cd_param_archivo']['tmp_name'];
                        $filesize = $_FILES['form_comp_pagar_cd_param_archivo']['size'];
                        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                        
                        if($filename != ""){
                            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                            $nombre_archivo = $comprobante_num_documento . " - Constancia de Detraccion " . date('YmdHis') . "." . $fileExt;
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
        
                            $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_constancia (
                                                            comprobante_id,
                                                            tipo_constancia_id,
                                                            moneda_id,
                                                            monto,
                                                            nombre,
                                                            extension,
                                                            size,
                                                            ruta,
                                                            download,
                                                            status,
                                                            user_created_id,
                                                            user_updated_id,
                                                            created_at,
                                                            updated_at
                                                            ) 
                                                        VALUES (?, 1, ?, ?, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                            $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                            $stmtCd->bind_param("iissssssiiss", $comprobante_id, $param_cd_moneda_id, $param_cd_monto, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);
        
                            if ($stmtCd->execute()) {
        
                                $result["http_code"] = 200;
                                $result["status"] = "Datos obtenidos de gestión.";
                            } else {
                                $result["http_code"] = 400;
                                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                            }
                            $stmtCd->close();
                        }
                    }else {
                        $result["http_code"] = 400;
                        $result["error"] = "Nose encontro la constancia de Detracción ";
                    }
        
                    if (isset($_FILES['form_comp_pagar_cpp_param_archivo']) && $_FILES['form_comp_pagar_cpp_param_archivo']['error'] === UPLOAD_ERR_OK) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        $filename = $_FILES['form_comp_pagar_cpp_param_archivo']['name'];
                        $filenametem = $_FILES['form_comp_pagar_cpp_param_archivo']['tmp_name'];
                        $filesize = $_FILES['form_comp_pagar_cpp_param_archivo']['size'];
                        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                        
                        if($filename != ""){
                            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                            $nombre_archivo = $comprobante_num_documento . " - Constancia de Pago al Proveedor " . date('YmdHis') . "." . $fileExt;
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
        
                            $query_insert_ccp_constancia = "INSERT INTO tbl_comprobante_constancia (
                                                            comprobante_id,
                                                            tipo_constancia_id,
                                                            moneda_id,
                                                            monto,
                                                            nombre,
                                                            extension,
                                                            size,
                                                            ruta,
                                                            download,
                                                            status,
                                                            user_created_id,
                                                            user_updated_id,
                                                            created_at,
                                                            updated_at
                                                            ) 
                                                        VALUES (?, 2, ?, ?, ?, ? ,? , ?, ?, 1, ?, ?, ?, ?)";
                            $stmtCpp = $mysqli->prepare($query_insert_ccp_constancia);
                            $stmtCpp->bind_param("iissssssiiss", $comprobante_id, $param_cpp_moneda_id, $param_cpp_monto, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id,  $usuario_id, $fecha,$fecha);
        
                            if ($stmtCpp->execute()) {
        
                                $result["http_code"] = 200;
                                $result["status"] = "Datos obtenidos de gestión.";
                            } else {
                                $result["http_code"] = 400;
                                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCpp->error;
                            }
                            $stmtCpp->close();
                        }
                    }else {
                        $result["http_code"] = 400;
                        $result["error"] = "Nose encontro la constancia de Pago al Proveedor ";
                    }

                    //   DATOS DE FORMA DE PAGO

                    $error = '';

                    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                    $sql_insert_comprobante_fp = "
                        INSERT INTO tbl_comprobante_forma_pago (
                            comprobante_id,
                            banco_id,
                            moneda_id,
                            num_cuenta_corriente,
                            num_cuenta_interbancaria,
                            status,
                            created_at,
                            user_created_id
                        )  
                        VALUES (?,?,?,?,?,1,?,?)
                    ";

                    $stmt_fp = $mysqli->prepare($sql_insert_comprobante_fp);


                    if ($stmt_fp) {
                        $stmt_fp->bind_param("iiisssi", 
                                                $comprobante_id, 
                                                $param_fp_banco_id,
                                                $param_fp_moneda_id, 
                                                $param_fp_num_cuenta_corriente,
                                                $param_fp_num_cuenta_interbancaria,
                                                $fecha,
                                                $usuario_id
                                            );
                        try {
                            $stmt_fp->execute();
                            $result["http_code"] = 200;
                            $result["status"] = "Datos obtenidos de gestión.";
                        } catch (Exception $e) {
                            $result["http_code"] = 400;
                            $result["titulo"] = "Error al guardar.";
                            $result["descripcion"] = "Error en la preparación de la consulta: " . $mysqli->error;
                        }

                        $stmt_fp->close();
                    } else {
                        $result["http_code"] = 400;
                        $result["titulo"] = "Error al guardar.";
                        $result["descripcion"] = "Error en la preparación de la consulta: " . $mysqli->error;
                    }
                }
            }else{

                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                //  CONSULTAR ESTADO PREVIO DEL COMPROBANTE

                $stmt = $mysqli->prepare("
                        SELECT 
                            IFNULL(b_cf.nombre, 'Seleccione') AS cf_banco,
                            IFNULL(CONCAT(m_cf.nombre,' (',m_cf.simbolo,')'), 'Seleccione') AS cf_moneda,
                            cf.num_cuenta_corriente,
                            cf.num_cuenta_interbancaria,
                            IFNULL(cc_pd.nombre, '') AS cd_pago_detraccion,
                            IFNULL(cc_pd.monto, '') AS cd_pd_monto,
                            IFNULL(CONCAT(m_cc_pd.nombre,' (',m_cc_pd.simbolo,')'), 'Seleccione') AS cd_pd_moneda_id,
                            IFNULL(cc_pp.nombre, '') AS cd_pago_proveedor,
                            IFNULL(cc_pp.monto, '') AS cd_pp_monto,
                            IFNULL(CONCAT(m_cc_pp.nombre,' (',m_cc_pp.simbolo,')'), 'Seleccione') AS cd_pp_moneda_id,
                            c.num_documento
                        FROM tbl_comprobante c
                        LEFT JOIN tbl_usuarios u ON u.id = c.user_created_id
                        LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
                        LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
                        LEFT JOIN tbl_moneda m_cf ON cf.moneda_id = m_cf.id
                        LEFT JOIN tbl_bancos b_cf ON cf.banco_id = b_cf.id
                        LEFT JOIN tbl_comprobante_constancia cc_pd ON cc_pd.comprobante_id = c.id AND cc_pd.tipo_constancia_id =1 AND cc_pd.status=1
                        LEFT JOIN tbl_moneda m_cc_pd ON m_cc_pd.id = cc_pd.moneda_id
                        LEFT JOIN tbl_comprobante_constancia cc_pp ON cc_pp.comprobante_id = c.id AND cc_pp.tipo_constancia_id =2 AND cc_pp.status=1
                        LEFT JOIN tbl_moneda m_cc_pp ON m_cc_pp.id = cc_pp.moneda_id
                        WHERE c.id=?
                        LIMIT 1
                        ");

                $stmt->bind_param("i", $comprobante_id);
                $stmt->execute();
                $stmt->bind_result(
                                    $fp_banco_id,
                                    $fp_moneda_id,
                                    $fp_num_cuenta_corriente,
                                    $fp_num_cuenta_interbancaria,
                                    $cd_pago_detraccion,
                                    $cd_pd_monto,
                                    $cd_pd_moneda_id,
                                    $cd_pago_proveedor,
                                    $cd_pp_monto,
                                    $cd_pp_moneda_id,
                                    $param_num_documento);
                $stmt->fetch();
                $stmt->close();

                $historial_query = $mysqli->prepare("
                        INSERT INTO tbl_comprobante_historial_cambios (
                        comprobante_id,
                        valor_anterior,
                        valor_nuevo,
                        nombre_campo,
                        status,
                        user_created_id,
                        created_at
                        ) VALUES (?, ?, ?, ?, 1, ?,?)
                        ");
                
                // Definir un array asociativo para mapear los campos a sus valores originales
                
                $campos_originales = array(
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
                    );
                $cambios_realizados = false;

                foreach ($campos_originales as $campo => $valor_anterior) {

                    // Comparar el valor original con el valor actual en $_POST
                    $valor_nuevo = $_POST[$campo];

                    if ((string)$valor_anterior != (string)$valor_nuevo) {
                        // Si hay un cambio, registrar en el historial
                        $cambios_realizados = true;
                        $historial_query->bind_param("isssis", $comprobante_id, $valor_anterior, $valor_nuevo, $campo, $usuario_id, $fecha);
                        $historial_query->execute();
                    }
                }

                $historial_query->close();


                if ($cambios_realizados) {

                    //  ACTUALIZACION DE DATOS DE FORMA DE PAGO
                
                    $query_update_comprobante_fp = "UPDATE tbl_comprobante_forma_pago 
                        SET banco_id = '".$param_fp_banco_id."',
                        moneda_id = '".$param_fp_moneda_id."',
                        num_cuenta_corriente = '".$param_fp_num_cuenta_corriente."',
                        num_cuenta_interbancaria = '".$param_fp_num_cuenta_interbancaria."',
                        user_updated_id = '".$usuario_id."',
                        updated_at = '".date('Y-m-d H:i:s')."'
                        WHERE comprobante_id = {$comprobante_id}
                        ";
                    $mysqli->query($query_update_comprobante_fp);

                    if($mysqli->error){
                        $error = $mysqli->error;

                        $result["http_code"] = 400;
                        $result["titulo"] = "Error al editar.";
                        $result["descripcion"] = $error;
                        $result["query"] = $query_update_comprobante_fp;

                        echo json_encode($result);
                        exit();
                    }
                
                    $path = "/var/www/html/files_bucket/comprobantes/constancias/";

                                    //  GUARDAR CAMBIOS DE ARCHIVOS ADJUNTOS
                    if ($_POST['cd_pago_detraccion'] != $cd_pago_detraccion){

                        //  DESHABILITAR ARCHIVO ANTERIORES
                        $query_update_comprobante = "UPDATE tbl_comprobante_constancia 
                                                    SET 
                                                        status = 0,
                                                        user_updated_id = ?,
                                                        updated_at = ?
                                                    WHERE 
                                                        comprobante_id = ?
                                                    AND tipo_constancia_id = 1";
                        $stmt = $mysqli->prepare($query_update_comprobante);
                        $stmt->bind_param("iss", $usuario_id, $fecha, $comprobante_id);

                        if ($stmt->execute()) {
                            if (isset($_FILES['form_comp_pagar_cd_param_archivo']) && $_FILES['form_comp_pagar_cd_param_archivo']['error'] === UPLOAD_ERR_OK) {
                                if (!is_dir($path)) mkdir($path, 0777, true);
                                $filename = $_FILES['form_comp_pagar_cd_param_archivo']['name'];
                                $filenametem = $_FILES['form_comp_pagar_cd_param_archivo']['tmp_name'];
                                $filesize = $_FILES['form_comp_pagar_cd_param_archivo']['size'];
                                $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                                
                                if($filename != ""){
                                    $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                    $nombre_archivo = $param_num_documento . " - Constancia de Detraccion ".date('YmdHis') . "." . $fileExt;
                                    move_uploaded_file($filenametem, $path . $nombre_archivo);

                                    $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_constancia (
                                                                    comprobante_id,
                                                                    tipo_constancia_id,
                                                                    moneda_id,
                                                                    monto,
                                                                    nombre,
                                                                    extension,
                                                                    size,
                                                                    ruta,
                                                                    download,
                                                                    status,
                                                                    user_created_id,
                                                                    user_updated_id,
                                                                    created_at,
                                                                    updated_at
                                                                    ) 
                                                                VALUES (?, 1, ?, ?, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                    $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                    $stmtCd->bind_param("iissssssiiss", $comprobante_id, $param_cd_moneda_id, $param_cd_monto, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);

                                    if ($stmtCd->execute()) {

                                        $result["http_code"] = 200;
                                        $result["titulo"] = "Creación exitosa.";
                                        $result["descripcion"] = "El comprobante se registro éxitosamente";
                                    } else {
                                        $error = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                    }
                                    $stmtCd->close();
                                }
                            }else {

                                $error = "Nose encontro el Acta de Conformidad ";
                            }
                        }
                    }else{
                        //  DESHABILITAR ARCHIVO ANTERIORES
                        $query_update_comprobante_pd = "UPDATE tbl_comprobante_constancia 
                        SET 
                            monto = ?,
                            moneda_id = ?,
                            user_updated_id = ?,
                            updated_at = ?
                        WHERE 
                            comprobante_id = ?
                        AND status = 1
                        AND tipo_constancia_id = 1";
                        $stmt_pd = $mysqli->prepare($query_update_comprobante_pd);
                        $stmt_pd->bind_param("siisi", $param_cd_monto, $param_cd_moneda_id, $usuario_id, $fecha, $comprobante_id);
                        $stmt_pd->execute();
                    }

                    if ($_POST['cd_pago_proveedor'] != $cd_pago_proveedor ){

                        //  DESHABILITAR ARCHIVO ANTERIORES
                        $query_update_comprobante = "UPDATE tbl_comprobante_constancia 
                                                    SET 
                                                        status = 0,
                                                        user_updated_id = ?,
                                                        updated_at = ?
                                                    WHERE 
                                                        comprobante_id = ?
                                                    AND tipo_constancia_id = 2";
                        $stmt = $mysqli->prepare($query_update_comprobante);
                        $stmt->bind_param("iss", $usuario_id, $fecha, $comprobante_id);

                        if ($stmt->execute()) {
                            if (isset($_FILES['form_comp_pagar_cpp_param_archivo']) && $_FILES['form_comp_pagar_cpp_param_archivo']['error'] === UPLOAD_ERR_OK) {
                                $filename = $_FILES['form_comp_pagar_cpp_param_archivo']['name'];
                                $filenametem = $_FILES['form_comp_pagar_cpp_param_archivo']['tmp_name'];
                                $filesize = $_FILES['form_comp_pagar_cpp_param_archivo']['size'];
                                $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                                
                                if($filename != ""){
                                    $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
                                    $nombre_archivo = $param_num_documento . " - Constancia de Pago al Proveedor ".date('YmdHis') . "." . $fileExt;
                                    move_uploaded_file($filenametem, $path . $nombre_archivo);
        
                                    $query_insert_cd_constancia = "INSERT INTO tbl_comprobante_constancia (
                                                                    comprobante_id,
                                                                    tipo_constancia_id,
                                                                    moneda_id,
                                                                    monto,
                                                                    nombre,
                                                                    extension,
                                                                    size,
                                                                    ruta,
                                                                    download,
                                                                    status,
                                                                    user_created_id,
                                                                    user_updated_id,
                                                                    created_at,
                                                                    updated_at
                                                                    ) 
                                                                VALUES (?, 2, ?, ?, ?, ? ,? , ?, ?, 1, ?,?,?, ?)";
                                    $stmtCd = $mysqli->prepare($query_insert_cd_constancia); 
                                    $stmtCd->bind_param("iissssssiiss", $comprobante_id, $param_cpp_moneda_id, $param_cpp_monto, $nombre_archivo, $fileExt, $filesize, $path, $path, $usuario_id, $usuario_id, $fecha,$fecha);
        
                                    if ($stmtCd->execute()) {
        
                                        $result["http_code"] = 200;
                                        $result["titulo"] = "Creación exitosa.";
                                        $result["descripcion"] = "Se guardaron los cambios exitosamente.";
                                    } else {
                                        $result["http_code"] = 400;
                                        $result["titulo"] = "Error.";
                                        $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmtCd->error;
                                    }
                                    $stmtCd->close();
                                }
                            }else {
                                $result["http_code"] = 400;
                                $result["error"] = "No se encontró la constancia de pago al proveedor(PDF).";
                            }
                        }
                    }else{
                        //  DESHABILITAR ARCHIVO ANTERIORES
                        $query_update_comprobante_pp = "UPDATE tbl_comprobante_constancia 
                        SET 
                            monto = ?,
                            moneda_id = ?,
                            user_updated_id = ?,
                            updated_at = ?
                        WHERE 
                            comprobante_id = ?
                        AND status = 1
                        AND tipo_constancia_id = 2";
                        $stmt_pp = $mysqli->prepare($query_update_comprobante_pp);
                        $stmt_pp->bind_param("siisi", $param_cpp_monto, $param_cpp_moneda_id, $usuario_id, $fecha, $comprobante_id);
                        $stmt_pp->execute();
                    }

                }else{
                    $result["http_code"] = 400;
                    $result["error"] = "No hay cambios para guardar";

                    echo json_encode($result);
                    exit();
                }
        }
        
        } else {
            $result["http_code"] = 400;
            $result["error"] = "El ID del comprobante no es válido.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
    }

    echo json_encode($result);
    exit();
}

function sendEmailObservacion($comprobante_id, $motivo, $fecha) {
	include '/var/www/html/sys/mailer/class.phpmailer.php';

	include("db_connect.php");
	include("sys_login.php");

    try {
	
	$sel_query = $mysqli->query("SELECT 
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
        WHERE c.id= ".$comprobante_id."
        LIMIT 1");
	$body = "";
	$body .= '<html>';

	$email_user_created = '';
	$num_documento = "";
    $proveedor_ruc ="";
    $created_at = "";
    $fecha = "";// date('d-m-Y');
	$host = $_SERVER["HTTP_HOST"];
	while($sel = $sel_query->fetch_assoc())
	{
	
		$num_documento = $sel["num_documento"];
		$proveedor_ruc = $sel["proveedor_ruc"];
        $email_user_created = $sel["correo_create"];
        $created_at = $sel["created_at"];
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Notificación de Observación de Comprobante </b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Número de Comprobante:</b></td>';
			$body .= '<td>'.$num_documento.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>RUC del Proveedor:</b></td>';
			$body .= '<td>'.$proveedor_ruc.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Registro:</b></td>';
			$body .= '<td>'.$created_at.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observaciones:</b></td>';
			$body .= '<td>'.$motivo.'</td>';
		$body .= '</tr>';

		$body .= '</table>';

		}
		$body .= '</html>';
		$body .= "";
		//
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";

        $sub_titulo_email = "";

		if (env('SEND_EMAIL') == 'test')
		{
			$sub_titulo_email = "TEST SISTEMAS: ";
		}

		$subject = $sub_titulo_email."Gestión - Comprobante Observado - ".$num_documento. " ".$fecha;


        $mail->AddAddress($email_user_created);
        
		$mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
        
        $mail->FromName = env('MAIL_GESTION_NAME');
        $mail->Subject  = $subject;
        $mail->Body     = $body;
        $mail->isHTML(true);
        $mail->send();

    } catch (phpmailerException $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
        $mail->Host       = "smtp.gmail.com";
        $mail->Port       = 465;
        $mail->SMTPSecure = "ssl";

        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');

		$mail->AddAddress($email_user_created);
      
        $mail->FromName = "Apuesta Total - Comprobante de Pago";
        $mail->Subject  = "Error de envio de emails :: Alertas phpmailerException";
        $mail->Body     = $e->errorMessage();
        $mail->send();

    } catch (Exception $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";

		$mail->AddAddress($email_user_created);
        
        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
       
        $mail->FromName = "Apuesta Total - Comprobante de Pago - Fail";
        $mail->Subject  = "Error de envio de emails :: Comprobante de Pago";
        $mail->Body     = $e->getMessage();
        $mail->send();
    }
}

function sendEmailObservacionTesoreria($comprobante_id, $motivo, $fecha) {
	include '/var/www/html/sys/mailer/class.phpmailer.php';

	include("db_connect.php");
	include("sys_login.php");

    try {
	
	$sel_query = $mysqli->query("SELECT 
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
        LEFT JOIN tbl_comprobante_historial_etapas ce ON ce.comprobante_id = c.id AND ce.etapa_id = 3
        LEFT JOIN tbl_usuarios ua ON ua.id = c.user_updated_id
        LEFT JOIN tbl_usuarios u ON u.id = ce.user_updated_id AND ce.comprobante_id = c.id AND ce.etapa_id = 3
        LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id 
        LEFT JOIN tbl_comprobante_proveedor cp ON cp.id = c.proveedor_id
        LEFT JOIN tbl_comprobante_orden_compra co ON co.comprobante_id = c.id
        LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
        WHERE c.id= ".$comprobante_id."
        LIMIT 1");
	$body = "";
	$body .= '<html>';

	$email_user_created = '';
	$num_documento = "";
    $proveedor_ruc ="";
    $created_at = "";
    $fecha = "";// date('d-m-Y');
	$host = $_SERVER["HTTP_HOST"];
	while($sel = $sel_query->fetch_assoc())
	{
	
		$num_documento = $sel["num_documento"];
		$proveedor_ruc = $sel["proveedor_ruc"];
        $email_user_created = $sel["correo_create"];
        $created_at = $sel["created_at"];
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Notificación de Observación por Tesorería </b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Número de Comprobante:</b></td>';
			$body .= '<td>'.$num_documento.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>RUC del Proveedor:</b></td>';
			$body .= '<td>'.$proveedor_ruc.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Registro:</b></td>';
			$body .= '<td>'.$created_at.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observaciones:</b></td>';
			$body .= '<td>'.$motivo.'</td>';
		$body .= '</tr>';

		$body .= '</table>';

		}
		$body .= '</html>';
		$body .= "";
		
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";

        $sub_titulo_email = "";

		if (env('SEND_EMAIL') == 'test')
		{
			$sub_titulo_email = "TEST SISTEMAS: ";
		}

		$subject = $sub_titulo_email."Gestión - Comprobante Observado por Tesorería- ".$num_documento . " ".$fecha;


        $mail->AddAddress($email_user_created);
        
		$mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
        
        $mail->FromName = env('MAIL_GESTION_NAME');
        $mail->Subject  = $subject;
        $mail->Body     = $body;
        $mail->isHTML(true);
        $mail->send();

    } catch (phpmailerException $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
        $mail->Host       = "smtp.gmail.com";
        $mail->Port       = 465;
        $mail->SMTPSecure = "ssl";

        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');

		$mail->AddAddress($email_user_created);
      
        $mail->FromName = "Apuesta Total - Comprobante de Pago";
        $mail->Subject  = "Error de envio de emails :: Alertas phpmailerException";
        $mail->Body     = $e->errorMessage();
        $mail->send();

    } catch (Exception $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";

		$mail->AddAddress($email_user_created);
        
        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
       
        $mail->FromName = "Apuesta Total - Comprobante de Pago - Fail";
        $mail->Subject  = "Error de envio de emails :: Comprobante de Pago";
        $mail->Body     = $e->getMessage();
        $mail->send();
    }
}