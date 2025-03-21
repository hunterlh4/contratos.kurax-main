<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_devolucion_listar"){
    $usuario_id = $login ? $login['id'] : null;

    $proveedor_id = $_POST['proveedor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];


    //  FILTROS

        $where_proveedor="";
        $where_fecha ="";

        if ($proveedor_id != 0){
            $where_proveedor = " AND cp.id = ".$proveedor_id." ";
        }

        if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
            $where_fecha = " AND ct.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            } elseif (!Empty($fecha_inicio)) {
                $where_fecha = " AND ct.fecha >= '$fecha_inicio 00:00:00'";
            } elseif (!Empty($fecha_fin)) {
                $where_fecha = " AND ct.fecha <= '$fecha_fin 23:59:59'";
        }
   
	$query = "
        SELECT
            ans.id AS devolucion_id,
            ct.id,
            cp.nombre AS nombre_proveedor,
            ct.transaccion_id,
            DATE_FORMAT(ct.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
            ce.nombre AS estado_calimaco,
            ans.user_created_id,
            ct.cantidad,
            ct.status
        FROM tbl_conci_devolucion_solicitud ans
            LEFT JOIN tbl_conci_calimaco_transaccion ct
             ON ans.transaccion_id = ct.id        
            LEFT JOIN tbl_conci_calimaco_estado ce
            ON ct.estado_id = ce.id
            LEFT JOIN tbl_conci_calimaco_metodo cm
            ON ct.metodo_id = cm.id
            LEFT JOIN tbl_conci_proveedor cp
            ON cm.proveedor_id = cp.id
        WHERE ans.status = 1 
        $where_proveedor
        $where_fecha
        ORDER BY ans.created_at DESC
	";

	$list_query = $mysqli->query($query);

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{      
        $botones = "";

        if($reg->status == 1 ){

            //if(in_array("btn_conci_devolucion_rechazar", $usuario_permisos[$menu_permiso]) && $reg->status != 0):

                $botones .= ' <a class="btn btn-rounded btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                        onclick="conci_devolucion_btn_eliminar(' . $reg->devolucion_id . ','.$reg->id.')" title="Eliminar"><i class="fa fa-trash"></i></a>';
            //endif;

        }

        
		$data[] = array(
            "0" => count($data) + 1,
			"1" => $reg->fecha,
            "2" => $reg->nombre_proveedor,
			"3" => $reg->transaccion_id,
            "4" => $reg->estado_calimaco,
			"5" => $reg->cantidad,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_devolucion_exportar"){
    require_once '../phpexcel/classes/PHPExcel.php';
    
    $usuario_id = $login ? $login['id'] : null;
    $formato = $_POST['formato'];
    $proveedor_id = $_POST['proveedor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];

    //  FILTROS

        $where_proveedor="";
        $where_fecha ="";

        if ($proveedor_id != 0){
            $where_proveedor = " AND cp.id = ".$proveedor_id." ";
        }

        if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
            $where_fecha = " AND ct.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            } elseif (!Empty($fecha_inicio)) {
                $where_fecha = " AND ct.fecha >= '$fecha_inicio 00:00:00'";
            } elseif (!Empty($fecha_fin)) {
                $where_fecha = " AND ct.fecha <= '$fecha_fin 23:59:59'";
        }
   
	$query = "
        SELECT
            ans.id AS devolucion_id,
            DATE_FORMAT(ans.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            DATE_FORMAT(ct.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
            ct.id,
            cp.nombre AS nombre_proveedor,
            ct.transaccion_id,
            ce.nombre AS estado_calimaco,
            ans.user_created_id,
            ct.cantidad,
            ct.status,
            DATE_FORMAT(ans.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            DATE_FORMAT(ans.updated_at, '%d/%m/%Y %H:%i:%s') AS updated_at,
            uc.usuario AS user_created,
            up.usuario AS user_updated
        FROM tbl_conci_devolucion_solicitud ans
            LEFT JOIN tbl_conci_calimaco_transaccion ct
             ON ans.transaccion_id = ct.id        
            LEFT JOIN tbl_conci_calimaco_estado ce
            ON ct.estado_id = ce.id
            LEFT JOIN tbl_conci_calimaco_metodo cm
            ON ct.metodo_id = cm.id
            LEFT JOIN tbl_conci_proveedor cp
            ON cm.proveedor_id = cp.id
            LEFT JOIN tbl_usuarios uc ON ans.user_created_id = uc.id
            LEFT JOIN tbl_usuarios up ON ans.user_updated_id = up.id
        WHERE ans.status = 1 
            $where_proveedor
            $where_fecha
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
                                            'Fecha de devolucion', 
                                            'Fecha de Transacción',
                                            'Proveedor', 
                                            'Transaccion ID', 
                                            'Estado Calimaco', 
                                            'Monto',
                                            'Fecha creación de devolución',
                                            'Usuario creador de devolución',
                                            'Fecha modificación de devolución',
                                            'Usuario modificador de devolución'
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
                        ->setCellValue('K1', $titulosColumnas[10]);
            
                        $cont = 0;
            
                    $i = 2; 
            
            
                    while($reg = $list_query->fetch_object()) 
                    {      
            
                        $cont ++;
            
                        $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $cont)
                        ->setCellValue('B'.$i, $reg->created_at)
                        ->setCellValue('C'.$i, $reg->fecha)
                        ->setCellValue('D'.$i, $reg->nombre_proveedor)
                        ->setCellValue('E'.$i, $reg->transaccion_id)
                        ->setCellValue('F'.$i, $reg->estado_calimaco)
                        ->setCellValue('G'.$i, $reg->cantidad)
                        ->setCellValue('H'.$i, $reg->created_at)
                        ->setCellValue('I'.$i, $reg->updated_at)
                        ->setCellValue('J'.$i, $reg->user_created)
                        ->setCellValue('K'.$i, $reg->user_updated);
                        
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
            
                    $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($estiloNombresColumnas);
                    $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:U".($i-1));
                    $objPHPExcel->getActiveSheet()->getStyle('A1:K'.($i-1))->applyFromArray($estilo_centrar);
            
                    $objPHPExcel->getActiveSheet()->getStyle('G2:G'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
            
                    for($i = 'A'; $i <= 'K'; $i++)
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
                    }
            
                    $objPHPExcel->getActiveSheet()->setTitle('Devolución');
                    
                    $objPHPExcel->setActiveSheetIndex(0);
            
                //  Descargar excel
            
                    $file_name = "Reporte de Devolución - ".date("Ymd");
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
                        'Fecha de devolucion', 
                        'Fecha de Transacción',
                        'Proveedor', 
                        'Transaccion ID', 
                        'Estado Calimaco', 
                        'Monto',
                        'Fecha creación de devolución',
                        'Usuario creador de devolución',
                        'Fecha modificación de devolución',
                        'Usuario modificador de devolución'
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
                        $csv_content .= "{$reg->nombre_proveedor},";
                        $csv_content .= "{$reg->transaccion_id},";
                        $csv_content .= "{$reg->estado_calimaco},";
                        $csv_content .= "{$reg->cantidad},";
                        $csv_content .= "{$reg->created_at},";
                        $csv_content .= "{$reg->updated_at},";
                        $csv_content .= "{$reg->user_created},";
                        $csv_content .= "{$reg->user_updated}\n";
                    
                        $i++;
                    }
                    
                // Nombre del archivo y ruta de descarga
                $file_name = "Reporte de Devolución - " . date("Ymd") . ".csv";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_devolucion_obtener") {
    $devolucion_id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($devolucion_id != NULL) {
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
                    FROM tbl_conci_devolucion_solicitud ans
                    LEFT JOIN tbl_conci_devolucion_tipo at ON at.id = ans.tipo_id
                    LEFT JOIN tbl_usuarios u ON u.id=ans.user_created_id
                    LEFT JOIN tbl_usuarios ua ON ua.id=ans.user_updated_id
                    LEFT JOIN tbl_usuarios au ON au.id=ans.first_user_authorized_id
                    LEFT JOIN tbl_usuarios aut ON aut.id=ans.second_user_authorized_id
                    WHERE ans.id=?
                    LIMIT 1
                ");

                $stmtAnulacion->bind_param("i", $devolucion_id);
                if (!$stmtAnulacion->execute()) throw new Exception("Error al ejecutar la consulta de anulación. Comunicarse con soporte.");
                $stmtAnulacion->bind_result(
                                    $id, 
                                    $transaccion_calimaco_id, 
                                    $tipo, 
                                    $motivo,
                                    $devolucion_created_at,
                                    $devolucion_updated_at,
                                    $devolucion_usuario_create,
                                    $devolucion_usuario_update,
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
                    'devolucion' => [
                        'id' => $id,
                        'tipo' => $tipo,
                        'motivo' => $motivo,
                        'created_at' => $devolucion_created_at,
                        'updated_at' => $devolucion_updated_at,
                        'usuario_create' => $devolucion_usuario_create,
                        'usuario_update' => $devolucion_usuario_update,
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
