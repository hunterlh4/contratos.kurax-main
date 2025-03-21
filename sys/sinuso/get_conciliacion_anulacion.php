<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_listar"){
    $usuario_id = $login ? $login['id'] : null;

	$etapa_id = $_POST['etapa_id'];
    $tipo_id = $_POST['tipo_id'];
	$usuario_autorizador = $_POST['usuario_autorizador'];
    $proveedor_id = $_POST['proveedor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];
    $fecha_inicio_autorizacion = $_POST['fecha_inicio_autorizacion'];
	$fecha_fin_autorizacion = $_POST['fecha_fin_autorizacion'];


    //  FILTROS

        $where_proveedor="";
        $where_etapa="";
        $where_tipo="";
        $where_autorizador="";
        $where_fecha ="";
        $where_fecha_autorizacion ="";

        if ($proveedor_id != 0){
            $where_proveedor = " AND cp.id = ".$proveedor_id." ";
        }

        if ($etapa_id != 0){
            $where_etapa = " AND ans.etapa_id = ".$etapa_id." ";
        }

        if ($tipo_id != 0){
            $where_tipo = " AND ans.tipo_id = ".$tipo_id." ";
        }

        if ($usuario_autorizador != 0){
            $where_autorizador = " AND ans.user_authorized_id = ".$usuario_autorizador." ";
        }

        if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
            $where_fecha = " AND ct.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            } elseif (!Empty($fecha_inicio)) {
                $where_fecha = " AND ct.created_at >= '$fecha_inicio 00:00:00'";
            } elseif (!Empty($fecha_fin)) {
                $where_fecha = " AND ct.created_at <= '$fecha_fin 23:59:59'";
        }

        if (!Empty($fecha_inicio_autorizacion) && !Empty($fecha_fin_autorizacion)) {
            $where_fecha_autorizacion = " AND ct.authorized_at BETWEEN '$fecha_inicio_autorizacion 00:00:00' AND '$fecha_fin_autorizacion 23:59:59'";
            } elseif (!Empty($fecha_inicio_autorizacion)) {
                $where_fecha_autorizacion = " AND ct.authorized_at >= '$fecha_inicio_autorizacion 00:00:00'";
            } elseif (!Empty($fecha_fin_autorizacion)) {
                $where_fecha_autorizacion = " AND ct.authorized_at <= '$fecha_fin_autorizacion 23:59:59'";
        }

    // PERMISOS DE ETAPAS

        $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'conciliacion' AND sub_sec_id = 'anulacion' LIMIT 1")->fetch_assoc();
        $menu_permiso = $menu_id_consultar["id"];

   
	$query = "
        SELECT
            ans.id AS anulacion_id,
            ct.id,
            cp.nombre AS nombre_proveedor,
			at.nombre AS nombre_tipo,
            ans.etapa_id,
            ae.nombre AS nombre_etapa,
            IFNULL(ans.cantidad_aprobaciones,0) AS cantidad_aprobaciones,
            IFNULL(ans.first_user_authorized_id,0) AS first_user_authorized_id,
            ct.transaccion_id,
            DATE_FORMAT(ct.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
            ce.nombre AS estado_calimaco,
            ans.user_created_id,
            ct.cantidad,
            ct.status
        FROM tbl_conci_anulacion_solicitud ans
            LEFT JOIN tbl_conci_calimaco_transaccion ct
             ON ans.transaccion_id = ct.id
            LEFT JOIN tbl_conci_anulacion_tipo at
            ON ans.tipo_id = at.id
            LEFT JOIN tbl_conci_anulacion_etapa ae
            ON ans.etapa_id = ae.id
            LEFT JOIN tbl_conci_calimaco_estado ce
            ON ct.estado_id = ce.id
            LEFT JOIN tbl_conci_calimaco_metodo cm
            ON ct.metodo_id = cm.id
            LEFT JOIN tbl_conci_proveedor cp
            ON cm.proveedor_id = cp.id
        WHERE ans.status = 1 
        $where_proveedor
        $where_etapa
        $where_tipo
        $where_autorizador
        $where_fecha
        $where_fecha_autorizacion
        ORDER BY ans.created_at DESC
	";

	$list_query = $mysqli->query($query);

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{      
        $botones = "";
        
        $selectQuery = " SELECT 
                ce.id,
                ce.nombre,
                ce.permiso
            FROM tbl_conci_anulacion_etapa ce
            WHERE ce.status = 1 AND ce.id IN(1,2,3,4)" ; 

        $stmt = $mysqli->prepare($selectQuery);
        $stmt->execute();
        $stmt->bind_result($id, $nombre,$permiso);

        $opciones_etapas = "";
        $disable_select = false;
        while ($stmt->fetch()) {

            
            if( $reg->etapa_id == 2  || $reg->etapa_id == 3){
                $opciones_etapas .= '<option value="' . $id . '" ' . ($reg->etapa_id == $id ? 'selected' : '') . '>'.$nombre.'</option>';
                $disable_select = true;
            }else{
                if(($reg->etapa_id == 1) &&  ($permiso ==  'btn_conci_anulacion_registrar'  || $permiso ==  'btn_conci_anulacion_enviar' )){
                    $opciones_etapas .= '<option value="' . $id . '" ' . ($reg->etapa_id == $id ? 'selected' : '') . '>'.$nombre.'</option>';

                }elseif( $reg->etapa_id == 4 && in_array("btn_conci_anulacion_enviar", $usuario_permisos[$menu_permiso]) && ($permiso ==  'btn_conci_anulacion_rechazar'  || $permiso ==  'btn_conci_anulacion_enviar')){
                    $opciones_etapas .= '<option value="' . $id . '" ' . ($reg->etapa_id == $id ? 'selected' : '') . '>'.$nombre.'</option>';

                }elseif( $reg->etapa_id == 4 && !in_array("btn_conci_anulacion_enviar", $usuario_permisos[$menu_permiso])){
                    $opciones_etapas .= '<option value="' . $id . '" ' . ($reg->etapa_id == $id ? 'selected' : '') . '>'.$nombre.'</option>';
                    $disable_select = true;
                }
            }

            if ($reg->status == 0 && $reg->user_created_id == $usuario_id && $reg->nombre_etapa == "Registrado") {
            $disable_select = true;
            }
        }
        

        $stmt->close();
        
        $nombre_etapa = "'".$reg->nombre_etapa."'";
        $combobox_etapa = '<select class="form-control conci_select_filtro" ' . ($disable_select ? 'disabled' : '') . ' onchange="conci_anulacion_btn_cambiar_etapa(this.value,' . $reg->anulacion_id . ',this.options[this.selectedIndex].text)">' . $opciones_etapas . '</select>';
        

        if($reg->nombre_etapa == "Enviado" &&  $reg->first_user_authorized_id !=$usuario_id){

            if(in_array("btn_conci_anulacion_rechazar", $usuario_permisos[$menu_permiso]) && $reg->status != 0):

                $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                        onclick="conci_anulacion_btn_rechazar(' . $reg->anulacion_id . ')"
                        title="Rechazar">Rechazar
                    </a>
                    ';
            endif;

            if(in_array("btn_conci_anulacion_aprobar", $usuario_permisos[$menu_permiso]) && $reg->status != 0):

                $botones .= ' <a class="btn btn-rounded btn-success btn-sm" data-toggle="tooltip" data-placement="top"
                        onclick="conci_anulacion_btn_aprobar(' . $reg->anulacion_id . ',' . $reg->cantidad_aprobaciones . ',' . $reg->first_user_authorized_id .',' . $reg->id .')"
                    title="Aprobar">Aprobar
                </a>
                ';
            endif;

        }

        $botones .= '<a onclick="conci_anulacion_btn_ver('.$reg->anulacion_id.');";
                        class="btn btn-info btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Ver detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    ';

        $botones .= '<a onclick="conci_anulacion_btn_historial('.$reg->anulacion_id.');";
                    class="btn btn-primary btn-sm"
                    data-toggle="tooltip" data-placement="top" title="Historial de cambios">
                    <span class="fa fa-history"></span>
                </a>
                ';

        if ($reg->nombre_etapa == "Registrado" || $reg->nombre_etapa == "Rechazado") {

            $botones .= '<a onclick="conci_anulacion_btn_editar('.$reg->anulacion_id.')";
                            class="btn btn-warning btn-sm"
                            data-toggle="tooltip" data-placement="top" title="Editar">
                            <span class="fa fa-pencil"></span>
                            </a>';
            $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                            onclick="conci_anulacion_btn_eliminar(' . $reg->anulacion_id . ')"
                            title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </a>
                        ';
        }

        $color_resaltar = '';
        if ($reg->nombre_etapa == 'Registrado') {
            $color_resaltar = 'yellow';
        }
        //$stmt->close();
        
		$data[] = array(
            "0" => count($data) + 1,
			"1" => $reg->fecha,
            "2" => $reg->nombre_proveedor,
            "3" => $reg->nombre_tipo,
			"4" => $reg->transaccion_id,
            "5" => $reg->estado_calimaco,
			"6" => $reg->cantidad,
			"7" => $combobox_etapa,
			"8" => $botones,
            "9" => $color_resaltar
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_exportar"){
    require_once '../phpexcel/classes/PHPExcel.php';
    
    $usuario_id = $login ? $login['id'] : null;
    $formato = $_POST['formato'];
    $etapa_id = $_POST['etapa_id'];
    $tipo_id = $_POST['tipo_id'];
	$usuario_autorizador = $_POST['usuario_autorizador'];
    $proveedor_id = $_POST['proveedor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];
    $fecha_inicio_autorizacion = $_POST['fecha_inicio_autorizacion'];
	$fecha_fin_autorizacion = $_POST['fecha_fin_autorizacion'];


    //  FILTROS

        $where_proveedor="";
        $where_etapa="";
        $where_tipo="";
        $where_autorizador="";
        $where_fecha ="";
        $where_fecha_autorizacion ="";

        if ($proveedor_id != 0){
            $where_proveedor = " AND cp.id = ".$proveedor_id." ";
        }

        if ($etapa_id != 0){
            $where_etapa = " AND ans.etapa_id = ".$etapa_id." ";
        }

        if ($tipo_id != 0){
            $where_tipo = " AND ans.tipo_id = ".$tipo_id." ";
        }

        if ($usuario_autorizador != 0){
            $where_autorizador = " AND ans.user_authorized_id = ".$usuario_autorizador." ";
        }

        if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
            $where_fecha = " AND ct.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            } elseif (!Empty($fecha_inicio)) {
                $where_fecha = " AND ct.created_at >= '$fecha_inicio 00:00:00'";
            } elseif (!Empty($fecha_fin)) {
                $where_fecha = " AND ct.created_at <= '$fecha_fin 23:59:59'";
        }

        if (!Empty($fecha_inicio_autorizacion) && !Empty($fecha_fin_autorizacion)) {
            $where_fecha_autorizacion = " AND ct.authorized_at BETWEEN '$fecha_inicio_autorizacion 00:00:00' AND '$fecha_fin_autorizacion 23:59:59'";
            } elseif (!Empty($fecha_inicio_autorizacion)) {
                $where_fecha_autorizacion = " AND ct.authorized_at >= '$fecha_inicio_autorizacion 00:00:00'";
            } elseif (!Empty($fecha_fin_autorizacion)) {
                $where_fecha_autorizacion = " AND ct.authorized_at <= '$fecha_fin_autorizacion 23:59:59'";
        }
   
	$query = "
        SELECT
            ans.id AS anulacion_id,
            DATE_FORMAT(ans.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            DATE_FORMAT(ct.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
            ae.nombre AS nombre_etapa,
            DATE_FORMAT(ans.first_authorized_at, '%d/%m/%Y %H:%i:%s') AS first_authorized_at,
            DATE_FORMAT(ans.second_authorized_at, '%d/%m/%Y %H:%i:%s')AS second_authorized_at,
            uf.usuario AS first_user_authorized,
            us.usuario AS second_user_authorized,
            at.nombre AS nombre_tipo,
            ans.motivo,
            cp.nombre AS nombre_proveedor,
            ct.usuario AS usuario_registrado,
            ct.transaccion_id,
            ct.cantidad,
            DATE_FORMAT(ans.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            DATE_FORMAT(ans.updated_at, '%d/%m/%Y %H:%i:%s') AS updated_at,
            uc.usuario AS user_created,
            up.usuario AS user_updated,
            ans.status
        FROM tbl_conci_anulacion_solicitud ans
            LEFT JOIN tbl_conci_calimaco_transaccion ct
             ON ans.transaccion_id = ct.id
            LEFT JOIN tbl_conci_anulacion_tipo at
            ON ans.tipo_id = at.id
            LEFT JOIN tbl_conci_anulacion_etapa ae
            ON ans.etapa_id = ae.id
            LEFT JOIN tbl_conci_calimaco_estado ce
            ON ct.estado_id = ce.id
            LEFT JOIN tbl_conci_calimaco_metodo cm
            ON ct.metodo_id = cm.id
            LEFT JOIN tbl_conci_proveedor cp
            ON cm.proveedor_id = cp.id
            LEFT JOIN tbl_usuarios uf ON ans.first_user_authorized_id = uf.id
            LEFT JOIN tbl_usuarios us ON ans.second_user_authorized_id = us.id
            LEFT JOIN tbl_usuarios uc ON ans.user_created_id = uc.id
            LEFT JOIN tbl_usuarios up ON ans.user_updated_id = up.id
        WHERE ans.status = 1 
        $where_proveedor
        $where_etapa
        $where_tipo
        $where_autorizador
        $where_fecha
        $where_fecha_autorizacion
        ORDER BY ct.id DESC
	";

	$list_query = $mysqli->query($query);
    $data =  array();


    //  Verificar si la carpeta existe

        $path = "/var/www/html/files_bucket/conciliacion/reportes/";

        if (!is_dir($path)) 
        {

            $data_return = array(
                "error" => 'No existe la carpeta "reportes" en la ruta "/files_bucket/conciliacion/" del servidor. Comunicarse con soporte',
                "titulo" => "Error al exportar",
                "http_code" => 400
            );
            echo json_encode($data_return);
            exit;
        }

    //  Limpia todos los archivos en la carpeta

        $files = glob($path . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    //  Creación de titulos de columnas de archivo

        switch ($formato){
            case "excel":
        
                $objPHPExcel = new PHPExcel();

                // Se asignan las propiedades del libro

                    $objPHPExcel->getProperties()->setCreator("AT") 
                                ->setDescription("Reporte de Anulación");
            
                    $titulosColumnas = array('Nº', 
                                            'Fecha de anulacion', 
                                            'Fecha de Transacción',
                                            'Etapa', 
                                            'Primera Fecha Autorización', 
                                            'Usuario Autorizador', 
                                            'Segunda Fecha Autorización', 
                                            'Usuario Autorizador', 
                                            'Tipo', 
                                            'Motivo', 
                                            'Proveedor', 
                                            'Usuario Registrado', 
                                            'Transaccion ID', 
                                            'Monto',
                                            'Fecha creación de solicitud',
                                            'Usuario creador de solicitud',
                                            'Fecha modificación de solicitud',
                                            'Usuario modificador de solicitud'
                                        );
            
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', $titulosColumnas[0])
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
                        ->setCellValue('R1', $titulosColumnas[17]);
            
                        $cont = 0;
            
                    $i = 2; 
            
            
                    while($reg = $list_query->fetch_object()) 
                    {      
            
                        $cont ++;
            
                        $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $cont)
                        ->setCellValue('B'.$i, $reg->created_at)
                        ->setCellValue('C'.$i, $reg->fecha)
                        ->setCellValue('D'.$i, $reg->nombre_etapa)
                        ->setCellValue('E'.$i, $reg->first_authorized_at)
                        ->setCellValue('F'.$i, $reg->second_authorized_at)
                        ->setCellValue('G'.$i, $reg->first_user_authorized)
                        ->setCellValue('H'.$i, $reg->second_user_authorized)
                        ->setCellValue('I'.$i, $reg->nombre_tipo)
                        ->setCellValue('J'.$i, $reg->motivo)
                        ->setCellValue('K'.$i, $reg->nombre_proveedor)
                        ->setCellValue('L'.$i, $reg->usuario_registrado)
                        ->setCellValue('M'.$i, $reg->transaccion_id)
                        ->setCellValue('N'.$i, $reg->cantidad)
                        ->setCellValue('O'.$i, $reg->created_at)
                        ->setCellValue('P'.$i, $reg->updated_at)
                        ->setCellValue('Q'.$i, $reg->user_created)
                        ->setCellValue('R'.$i, $reg->user_updated);
                        
                        $i++;
                    }
        
                //  Estilización de excel
        
                    $estiloNombresColumnas = array(
                        'font' => array(
                            'name'      => 'Calibri',
                            'bold'      => true,
                            'italic'    => false,
                            'strike'    => false,
                            'size'      => 10,
                            'color'     => array(
                                'rgb' => 'FFFFFF' // Color blanco
                            )
                        ),
                        'fill' => array(
                            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array(
                                'rgb' => '00008B' // Color azul oscuro
                            )
                        ),
                        'alignment' => array(
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
            
                    $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(63);
            
                    $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray($estiloNombresColumnas);
                    $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:U".($i-1));
                    $objPHPExcel->getActiveSheet()->getStyle('A1:R'.($i-1))->applyFromArray($estilo_centrar);
            
                    $objPHPExcel->getActiveSheet()->getStyle('G2:G'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
            
                    for($i = 'A'; $i <= 'R'; $i++)
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
                    }
            
                    $objPHPExcel->getActiveSheet()->setTitle('Anulación');
                    
                    $objPHPExcel->setActiveSheetIndex(0);
            
                //  Descargar excel
            
                    $file_name = "Reporte de Anulación - ".date("Ymd");
                    ini_set('display_errors', 0);
                    ini_set('display_startup_errors', 0);
                    error_reporting(0);
                    
                    // Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
                    header('Cache-Control: max-age=0');
            
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    $excel_path = '/var/www/html/files_bucket/conciliacion/reportes/'.$file_name.'.xls';
                    $excel_path_download = '/files_bucket/conciliacion/reportes/'.$file_name.'.xls';
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
                break;
            case "csv":

                //  Definir titulo de columnas

                    $titulosColumnas = array('Nº', 
                        'Fecha de anulacion', 
                        'Fecha de Transacción',
                        'Etapa', 
                        'Primera Fecha Autorización', 
                        'Usuario Autorizador', 
                        'Segunda Fecha Autorización', 
                        'Usuario Autorizador', 
                        'Tipo', 
                        'Motivo', 
                        'Proveedor', 
                        'Usuario Registrado', 
                        'Transaccion ID', 
                        'Monto',
                        'Fecha creación de solicitud',
                        'Usuario creador de solicitud',
                        'Fecha modificación de solicitud',
                        'Usuario modificador de solicitud'
                    );
                
                // Generar el contenido CSV
                    $csv_content = implode(',', $titulosColumnas) . "\n";
                    
                    $cont = 0;
                    
                    $i = 2;
                    
                    while($reg = $list_query->fetch_object()) {      
                        $cont++;
                    
                        // Generar fila CSV
                        $csv_content .= "$cont,";
                        $csv_content .= "{$reg->created_at},";
                        $csv_content .= "{$reg->fecha},";
                        $csv_content .= "{$reg->nombre_etapa},";
                        $csv_content .= "{$reg->first_authorized_at},";
                        $csv_content .= "{$reg->second_authorized_at},";
                        $csv_content .= "{$reg->first_user_authorized},";
                        $csv_content .= "{$reg->second_user_authorized},";
                        $csv_content .= "{$reg->nombre_tipo},";
                        $csv_content .= "{$reg->motivo},";
                        $csv_content .= "{$reg->nombre_proveedor},";
                        $csv_content .= "{$reg->usuario_registrado},";
                        $csv_content .= "{$reg->transaccion_id},";
                        $csv_content .= "{$reg->cantidad},";
                        $csv_content .= "{$reg->created_at},";
                        $csv_content .= "{$reg->updated_at},";
                        $csv_content .= "{$reg->user_created},";
                        $csv_content .= "{$reg->user_updated}\n";
                    
                        $i++;
                    }
                    
                // Nombre del archivo y ruta de descarga
                $file_name = "Reporte de Anulación - " . date("Ymd") . ".csv";
                $csv_path = '/var/www/html/files_bucket/conciliacion/reportes/' . $file_name;
                
                // Guardar el archivo CSV
                    try {
                        file_put_contents($csv_path, $csv_content);
                    
                        $data_return = array(
                            "ruta_archivo" => '/files_bucket/conciliacion/reportes/' . $file_name,
                            "http_code" => 200
                        );
                        echo json_encode($data_return);
                        exit;
                    } catch (Exception $e) {
                        $data_return = array(
                            "error" => $e->getMessage(),
                            "titulo" => "Error al guardar el CSV",
                            "http_code" => 400
                        );
                        echo json_encode($data_return);
                        exit;
                    }
                break;

            default:
                $data_return = array(
                    "error" => $e,
                    "titulo" => "No es posible descargarlo en ese formato. Comunicarse con soporte",
                    "http_code" => 400
                );
                echo json_encode($data_return);
                exit;
        }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_venta_proveedor_estado_listar") {
    try {
		$proveedor_id = $_POST["proveedor_id"];

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_conci_proveedor_estado
            WHERE status = 1 AND proveedor_id = ?
            ORDER BY nombre ASC;
        ");
        $stmt->bind_param("i", $proveedor_id);
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_etapa_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_conci_anulacion_etapa
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_tipo_listar") {
    try {

        $stmt = $mysqli->prepare("
        SELECT
                id, nombre
            FROM tbl_conci_anulacion_tipo
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_autorizador_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT p.usuario_id, u.usuario AS nombre
                FROM tbl_permisos p
                LEFT JOIN tbl_botones b ON (p.boton_id = b.id) AND b.nombre = 'Ver'
                LEFT JOIN tbl_usuarios u ON p.usuario_id = u.id
                WHERE p.menu_id = 417 AND u.estado = 1 AND u.usuario IS NOT NULL
                GROUP BY p.usuario_id
                ORDER BY u.usuario ASC
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_historial_etapa") {
    $solicitud_anulacion_id = $_POST['solicitud_anulacion_id'];

    try {

        $selectQuery = " SELECT 
                                he.id, 
                                ae.nombre AS etapa_nombre,
                                he.motivo,
                                DATE_FORMAT(he.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
                                u.usuario AS usuario_create
                            FROM tbl_conci_anulacion_solicitud_historial_etapa he
                            LEFT JOIN tbl_conci_anulacion_etapa ae ON he.etapa_id = ae.id
                            LEFT JOIN tbl_usuarios u ON u.id = he.user_created_id
                            WHERE he.solicitud_anulacion_id = ?
                            ORDER BY he.created_at DESC" ; 

        $stmt = $mysqli->prepare($selectQuery);

        $stmt->bind_param("i", $solicitud_anulacion_id);
        $stmt->execute();
        $stmt->bind_result($id, $etapa_nombre,$motivo,$created_at, $usuario_create);

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
                    "5" => ''
                ],
            ],
        ];

        echo json_encode($resultado);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_obtener") {
    $anulacion_id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($anulacion_id != NULL) {
        try {

            //  1. DATOS DEL PROVEEDOR

                $stmtAnulacion = $mysqli->prepare("
                    SELECT 
                        ans.id, 
                        ans.transaccion_id, 
                        ans.tipo_id,
                        ans.motivo,
                        IFNULL(ans.created_at, ''),
                        IFNULL(ans.updated_at, ''),
                        u.usuario AS usuario_create,
                        ua.usuario AS usuario_update,
                        IFNULL(ans.first_authorized_at, ''),
                        IFNULL(ans.second_authorized_at, ''),
                        au.usuario AS first_user_authorized_id,
                        aut.usuario AS second_user_authorized_id
                    FROM tbl_conci_anulacion_solicitud ans
                    LEFT JOIN tbl_conci_anulacion_tipo at ON at.id = ans.tipo_id
                    LEFT JOIN tbl_usuarios u ON u.id=ans.user_created_id
                    LEFT JOIN tbl_usuarios ua ON ua.id=ans.user_updated_id
                    LEFT JOIN tbl_usuarios au ON au.id=ans.first_user_authorized_id
                    LEFT JOIN tbl_usuarios aut ON aut.id=ans.second_user_authorized_id
                    WHERE ans.id=?
                    LIMIT 1
                ");

                $stmtAnulacion->bind_param("i", $anulacion_id);
                if (!$stmtAnulacion->execute()) throw new Exception("Error al ejecutar la consulta de anulación. Comunicarse con soporte.");
                $stmtAnulacion->bind_result(
                                    $id, 
                                    $transaccion_calimaco_id, 
                                    $tipo, 
                                    $motivo,
                                    $anulacion_created_at,
                                    $anulacion_updated_at,
                                    $anulacion_usuario_create,
                                    $anulacion_usuario_update,
                                    $first_authorized_at,
                                    $second_authorized_at,
                                    $first_user_authorized_id,
                                    $second_user_authorized_id);


                if (!$stmtAnulacion->fetch()) throw new Exception("No se encontraron datos de anulación seleccionado. Comunicarse con soporte.");
                $stmtAnulacion->close();
                
            //  2.  DATOS DE TRANSACCIÓN

                $stmtTransaccion = $mysqli->prepare("
                    SELECT 
                        ct.transaccion_id, 
                        cm.nombre,
                        ct.fecha,
                        ce.nombre,
                        ct.fecha_modificacion,
                        ct.hora,
                        ct.usuario,
                        ct.email,
                        ct.cantidad,
                        ct.id_externo,
                        ct.respuesta,
                        ct.agente,
                        ct.fecha_registro_jugador,
                        ct.ref,
                        CASE ct.estado_conciliacion
                            WHEN 1 THEN 'SI'
                            WHEN 0 THEN 'NO'
                            WHEN 2 THEN 'DUPLICADO'
                            ELSE 'Desconocido'
                        END AS estado_conciliacion,
                        CASE ct.estado_liquidacion
                            WHEN 1 THEN 'SI'
                            WHEN 0 THEN 'NO'
                            WHEN 2 THEN 'DUPLICADO'
                            ELSE 'Desconocido'
                        END AS estado_liquidacion,
                        IFNULL(ct.created_at, ''),
                        IFNULL(ct.updated_at, ''),
                        u.usuario AS usuario_create,
                        ua.usuario AS usuario_update
                    FROM tbl_conci_calimaco_transaccion ct
                    LEFT JOIN tbl_conci_calimaco_metodo cm ON cm.id = ct.metodo_id
                    LEFT JOIN tbl_conci_calimaco_estado ce ON ce.id = ct.estado_id
                    LEFT JOIN tbl_usuarios u ON u.id=ct.user_created_id
                    LEFT JOIN tbl_usuarios ua ON ua.id=ct.user_updated_id
                    WHERE ct.id=?
                    LIMIT 1
                ");

                $stmtTransaccion->bind_param("i", $transaccion_calimaco_id);
                if (!$stmtTransaccion->execute()) throw new Exception("Error al ejecutar la consulta de transacción. Comunicarse con soporte.");
                $stmtTransaccion->bind_result(
                                    $transaccion_id, 
                                    $nombre_metodo, 
                                    $fecha,
                                    $nombre_estado,
                                    $fecha_modificacion,
                                    $hora,
                                    $usuario,
                                    $email,
                                    $cantidad,
                                    $id_externo,
                                    $respuesta,
                                    $agente,
                                    $fecha_registro_jugador,
                                    $ref,
                                    $estado_conciliacion,
                                    $estado_liquidacion,
                                    $created_at,
                                    $updated_at,
                                    $usuario_create,
                                    $usuario_update);


                if (!$stmtTransaccion->fetch()) throw new Exception("No se encontraron datos de la transacción seleccionada. Comunicarse con soporte.");
                $stmtTransaccion->close();

                $response = [
                    'status' => 200,
                    'anulacion' => [
                        'id' => $id,
                        'tipo' => $tipo,
                        'motivo' => $motivo,
                        'created_at' => $anulacion_created_at,
                        'updated_at' => $anulacion_updated_at,
                        'usuario_create' => $anulacion_usuario_create,
                        'usuario_update' => $anulacion_usuario_update,
                        'first_authorized_at' => $first_authorized_at,
                        'second_authorized_at' => $second_authorized_at,
                        'first_user_authorized_id' => $first_user_authorized_id,
                        'second_user_authorized_id' => $second_user_authorized_id
                    ],
                    'transaccion' => [
                        'id' => $transaccion_id,
                        'nombre_metodo' => $nombre_metodo,
                        'fecha' => $fecha,
                        'nombre_estado' => $nombre_estado,
                        'fecha_modificacion' => $fecha_modificacion,
                        'hora' => $hora,
                        'usuario' => $usuario,
                        'email' => $email,
                        'cantidad' => $cantidad,
                        'id_externo' => $id_externo,
                        'respuesta' => $respuesta,
                        'agente' => $agente,
                        'fecha_registro_jugador' => $fecha_registro_jugador,
                        'ref' => $ref,
                        'estado_conciliacion' => $estado_conciliacion,
                        'estado_liquidacion' => $estado_liquidacion,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'usuario_create' => $usuario_create,
                        'usuario_update' => $usuario_update
                    ]
                ];
                            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(['status' => 500, 'message' => 'Error en la consulta SQL: '. $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'ID no válido']);
    }
}
