<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

if (isset($_POST["accion"]) && $_POST["accion"] === "comprobante_reporte_listar")
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

    // PERMISOS

    $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'comprobante' AND sub_sec_id = 'pago' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];

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
            c.user_created_id AS usuario_creador
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
        $where_creador
        ORDER BY c.id DESC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
        $permiso_editar = "";


        //  PERMISO PARA VER E HISTORICO Y EXPORTAR

        $botones = '
                    <a onclick="sec_comprobante_reporte_exportar_zip('.$reg->id.');";
                        class="btn btn-success btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Exportar archivo zip">
                        <span class="fa fa-folder-open-o"></span>
                    </a>
                    <a onclick="sec_comprobante_reporte_obtener_historico_cambios('.$reg->id.');";
                        class="btn btn-primary btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Historial de cambios">
                        <span class="fa fa-history"></span>
                    </a>
                    ';
             
        
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->created_at,
            "2" => $reg->num_documento,
			"3" => $reg->fecha_emision,
            "4" => $reg->fecha_vencimiento,
			"5" => $reg->proveedor_ruc,
			"6" => $reg->proveedor_nombre,
            "7" => $reg->monto,
			"8" => $reg->etapa_nombre,
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

if (isset($_POST["accion"]) && $_POST["accion"] === "comp_reporte_etapa_listar") {
    try {

        $stmt = $mysqli->prepare("
            SELECT
                id, 
                nombre_descriptivo AS nombre
            FROM tbl_comprobante_etapa
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

if (isset($_POST["accion"]) && $_POST["accion"]==="comp_reporte_exportar_listado")
{

    // PERMISOS ESTADOS

    $menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'comprobante' AND sub_sec_id = 'pago' LIMIT 1")->fetch_assoc();

    $menu_permiso = $menu_id_consultar["id"];


    $usuario_id = $login ? $login['id'] : null;

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
            LEFT JOIN tbl_comprobante_forma_pago cf ON cf.comprobante_id = c.id
            LEFT JOIN tbl_comprobante_orden_compra oc  ON c.id = oc.comprobante_id
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

		//mkdir($path, 0777, true);
	}

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT")
    ->setDescription("Reporte");

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
    $file_name = "Comprobantes_Reporte_".date("Ymd");

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'.$file_name.'.xls');
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