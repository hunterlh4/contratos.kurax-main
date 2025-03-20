<?php

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	
include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);
$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'billetera' AND sub_sec_id = 'reporte' LIMIT 1")->fetch_assoc();
$menu_permiso = $menu_id_consultar["id"];
$permiso_menu = $usuario_permisos[$menu_permiso];

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_reporte_listar_transacciones")
{
	$query = prepareQueryListarTransacciones($_POST);

	$list_query = $mysqli->query($query);

	$data =  array();

	$totales = [];

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->tipo_registro,
			"2" => $reg->nombre_corto,
			"3" => $reg->fecha_deposito,
			"4" => $reg->estado_nombre,
			"5" => $reg->usuario_validador,
			"6" => $reg->usuario_revision,
			"7" => $reg->fecha_validacion,
			"8" => $reg->tienda,
			"9" => "S/ ".number_format($reg->monto_deposito, 2, '.', ','),
			"10" => $reg->nombre_depositante,
			"11" => $reg->numero_operacion
		);

		$totales['estados'][$reg->estado_id]['descripcion'] = $reg->estado_nombre;
		$totales['estados'][$reg->estado_id]['suma'] += $reg->monto_deposito;
		$totales['estados'][$reg->estado_id]['cantidad'] ++;
		$totales['origen'][$reg->tipo_registro_id]['descripcion'] = $reg->tipo_registro;
		$totales['origen'][$reg->tipo_registro_id]['suma'] += $reg->monto_deposito;
		$totales['origen'][$reg->tipo_registro_id]['cantidad'] ++;
		$totales['total'][0]['suma'] += $reg->monto_deposito;
		$totales['total'][0]['cantidad'] ++;
		$totales['total'][0]['descripcion'] = 'Total';
	}


	$resultado = array(
		"sEcho" => 1,
		"query" => $query,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data,
		"totales" => $totales
	);

	echo json_encode($resultado);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_billetera_reporte_export_listar_transacciones")
{
	$query = prepareQueryListarTransacciones($_POST);

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/billetera_digital/reporte/transacciones/";

	if (!is_dir($path)) 
	{
		echo json_encode(array(
			"res_ruta_archivo" => "",
			"res_estado_archivo" => 0,
			"res_titulo" => "Error al descargar",
			"res_descripcion" => "No existe el directorio, contactar al área de soporte."
		));
		exit;
	}

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

    $tituloReporte = "Reporte de Transacciones";

	$titulosColumnas = array('Nº', 'ID', 'Tipo Registro', 'Teléfono', 'Fecha Depósito', 'Estado', 'Validador', 'Fecha Revision', 'Cajero', 'Fecha Validación', 'Tienda', 'Monto', 'Cliente', 'Nº Operación');

	// Se combinan las celdas A1 hasta J1, para colocar ahí el titulo del reporte
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:N1');
	
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $tituloReporte);

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A2', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B2', $titulosColumnas[1])
    ->setCellValue('C2', $titulosColumnas[2])
    ->setCellValue('D2', $titulosColumnas[3])
    ->setCellValue('E2', $titulosColumnas[4])
    ->setCellValue('F2', $titulosColumnas[5])
    ->setCellValue('G2', $titulosColumnas[6])
    ->setCellValue('H2', $titulosColumnas[7])
    ->setCellValue('I2', $titulosColumnas[8])
    ->setCellValue('J2', $titulosColumnas[9])
    ->setCellValue('K2', $titulosColumnas[10])
    ->setCellValue('L2', $titulosColumnas[11])
    ->setCellValue('M2', $titulosColumnas[12])
    ->setCellValue('N2', $titulosColumnas[13]);
	
	//Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 3; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['id'])
		->setCellValue('C'.$i, $fila['tipo_registro'])
		->setCellValue('D'.$i, $fila['nombre_corto'])
		->setCellValue('E'.$i, $fila['fecha_deposito'])
		->setCellValue('F'.$i, $fila['estado_nombre'])
		->setCellValue('G'.$i, $fila['usuario_revision'])
		->setCellValue('H'.$i, $fila['fecha_revision'])
		->setCellValue('I'.$i, $fila['usuario_validador'])
		->setCellValue('J'.$i, $fila['fecha_validacion'])
		->setCellValue('K'.$i, $fila['tienda'])
		->setCellValue('L'.$i, "S/ ".$fila['monto_deposito'])
		->setCellValue('M'.$i, $fila['nombre_depositante'])
		->setCellValue('N'.$i, $fila['numero_operacion']);
		
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

	$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:N".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:N'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('L3:L'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'N'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Reporte Transacciones');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Reporte Transacciones.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/billetera_digital/reporte/transacciones/Reporte Transacciones.xls';
	$excel_path_download = '/files_bucket/billetera_digital/reporte/transacciones/Reporte Transacciones.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(array(
			"res_ruta_archivo" => "",
			"res_estado_archivo" => 0,
			"res_titulo" => "Error al descargar",
			"res_descripcion" => $e
		));
		exit;
	}

	echo json_encode(array(
		"res_ruta_archivo" => $excel_path_download,
		"res_estado_archivo" => 1
	));
	exit;
}

function prepareQueryListarTransacciones($data){
	global $login;
	global $permiso_menu;

	$param_tipo_origen = $data['param_tipo_origen'];
	$param_telefono = $data['param_telefono'];
	$param_usuario_cajero = $data['param_usuario_cajero'];
	$param_fecha_inicio_validacion = '';
	$param_fecha_fin_validacion = '';
	if($data['param_fecha_inicio_validacion'] != ''){
		$param_fecha_inicio_validacion = $data['param_fecha_inicio_validacion'];
		$param_fecha_inicio_validacion = date("Y-m-d", strtotime($param_fecha_inicio_validacion));
		$param_fecha_fin_validacion = $data['param_fecha_fin_validacion'];
		$param_fecha_fin_validacion = date("Y-m-d", strtotime($param_fecha_fin_validacion));
	}
	$param_fecha_inicio_deposito = '';
	$param_fecha_fin_deposito = '';
	
	if($data['param_fecha_inicio_deposito'] != ''){
		$param_fecha_inicio_deposito = date("Y-m-d", strtotime($data['param_fecha_inicio_deposito']));
		$param_fecha_fin_deposito = date("Y-m-d", strtotime($data['param_fecha_fin_deposito']));
	}
	$param_usuario_validador_manual = $data['param_usuario_validador_manual'];
	$param_estado = $data['param_estado'];
	$param_tienda = $data['param_tienda'];
	$param_monto_desde = $data['param_monto_desde'];
	$param_monto_hasta = $data['param_monto_hasta'];
	$param_cliente = $data['param_cliente'];

	$cant_palabras = explode(" ", $param_cliente);
	$where_param_tipo_origen = "";
	$where_param_telefono = "";
	$where_param_usuario_cajero = "";
	$where_param_usuario_validador_manual = "";
	$where_param_estado = "";
	$where_param_tienda = "";
	$where_param_cliente = "";
	$where_param_fecha_validacion = "";
	$where_param_fecha_deposito = "";

	$where_permiso_cajero = '';
	$where_permiso_cajero_fecha_validacion = '';
	$where_permiso_cajero_fecha_deposito = '';

	$cantidad_dias = getParameterGeneral('billetera_reporte_cantidad_dias_atrás_permitidos_cajero');

	if(in_array("reporte_general", $permiso_menu)){
		$where_permiso_cajero = '';
	} else if(in_array("reporte_cajero", $permiso_menu)) {
		if(!empty($cantidad_dias)){
			$where_permiso_cajero_fecha_validacion = ' AND DATE(bt.fecha_validacion) >= DATE_ADD(DATE(NOW()), INTERVAL -' . $cantidad_dias  . ' DAY)';
			$where_permiso_cajero_fecha_deposito = ' AND DATE(bt.fecha_deposito) >= DATE_ADD(DATE(NOW()), INTERVAL -' . $cantidad_dias  . ' DAY)';
		}

		if(isset($_COOKIE["usuario_local_id"])){
			$local_id_cookie = $_COOKIE["usuario_local_id"];
		}
	
		if(count($login["usuario_locales"])){
			$where_permiso_cajero .= " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ") ";
		}

		if($local_id_cookie != ''){
			$where_permiso_cajero .= " AND l.id = {$local_id_cookie}";
		}

		$where_permiso_cajero .= " AND uv.id = " . $login['id'] . " ";
		$where_permiso_cajero .= " AND te.estado NOT IN (1)";
	}

	if($param_fecha_inicio_validacion != '' && $param_fecha_fin_validacion != ''){
		$where_param_fecha_validacion .= " AND DATE(bt.fecha_validacion) BETWEEN '{$param_fecha_inicio_validacion}' and '{$param_fecha_fin_validacion}' " .$where_permiso_cajero_fecha_validacion;
	}
			
	if($param_fecha_inicio_deposito != '' && $param_fecha_fin_deposito != ''){
		$where_param_fecha_deposito .= " AND DATE(bt.fecha_deposito) BETWEEN '{$param_fecha_inicio_deposito}' and '{$param_fecha_fin_deposito}' " .$where_permiso_cajero_fecha_deposito;
	}

	if($param_tipo_origen != "0")
	{
		$where_param_tipo_origen = " AND bt.billetera_registro_id = '".$param_tipo_origen."' ";
	}

	if($param_telefono != "0")
	{
		$where_param_telefono = " AND bt.billetera_telefono_id = '".$param_telefono."' ";
	}


	if($param_estado != "0")
	{
		$where_param_estado = " AND bt.estado_transaccion_id = '".$param_estado."' ";
	}
	// TODOS LOS ESTADOS Y LOS FILTROS DEL REVISOR Y VALIDADOR

	if($param_usuario_cajero != "0")
	{
		$where_param_usuario_cajero .= 
		"
			AND bt.cajero_id = '".$param_usuario_cajero."'
		";
	}

	if($param_usuario_validador_manual != "0")
	{
		$where_param_usuario_validador_manual .= 
		"
			AND bt.usuario_revision_id = '".$param_usuario_validador_manual."'
		";
	}

	if($param_tienda != "0")
	{
		$where_param_tienda = " AND bt.local_id = '".$param_tienda."' ";
	}



	foreach($cant_palabras as $palabra)
	{
		if($palabra != "")
		{
			$where_param_cliente .= " AND nombre_depositante REGEXP '[[:<:]]".$palabra."[[:>:]]' ";
		}
	}

	$query = 
	"	SELECT 
			bt.id, bt.billetera_registro_id, 
			br.id AS tipo_registro_id,
			br.nombre AS tipo_registro, 
		    bt.billetera_telefono_id, 
			bte.billetera_cuenta_id, 
			bc.nombre_corto, 
		    DATE_FORMAT(bt.fecha_deposito, '%Y-%m-%d %H:%i') AS fecha_deposito,
		    bt.estado_transaccion_id, 
			te.id AS estado_id,  
			te.descripcion AS estado_nombre,  
		    concat(IFNULL(pr.nombre, ''),' ', IFNULL(pr.apellido_paterno, ''),' ', IFNULL(pr.apellido_materno, '')) AS usuario_revision,
		    DATE_FORMAT(bt.fecha_revision, '%Y-%m-%d %H:%i') AS fecha_revision,
		    concat(IFNULL(pv.nombre, ''),' ', IFNULL(pv.apellido_paterno, ''),' ', IFNULL(pv.apellido_materno, '')) AS usuario_validador,
		    DATE_FORMAT(bt.fecha_validacion, '%Y-%m-%d %H:%i') AS fecha_validacion,
		    bt.local_id,
			IFNULL(CONCAT(l.nombre, ' [', l.cc_id, ']'), '') AS tienda,
		    bt.monto_deposito, bt.nombre_depositante, bt.numero_operacion
		FROM tbl_billetera_transacciones bt
			INNER JOIN tbl_billetera_registro br ON bt.billetera_registro_id = br.id
			INNER JOIN tbl_billetera_telefonos bte				ON bt.billetera_telefono_id = bte.id
		    INNER JOIN tbl_billetera_cuentas bc					ON bte.billetera_cuenta_id = bc.id
		    LEFT JOIN tbl_usuarios ur							ON bt.usuario_revision_id = ur.id
			LEFT JOIN tbl_personal_apt pr						ON ur.personal_id = pr.id 
		    LEFT JOIN tbl_usuarios uv							ON bt.cajero_id = uv.id
			LEFT JOIN tbl_personal_apt pv						ON uv.personal_id = pv.id
		    INNER JOIN tbl_billetera_transacciones_estados te	ON bt.estado_transaccion_id = te.estado
		    LEFT JOIN tbl_locales l								ON bt.local_id = l.id
		WHERE bt.status = 1 
			AND (bt.monto_deposito BETWEEN '".$param_monto_desde."' AND '".$param_monto_hasta."')
				".$where_permiso_cajero."
				".$where_param_tipo_origen."
				".$where_param_telefono."
				".$where_param_estado."
				".$where_param_cliente."
				".$where_param_usuario_validador_manual."
				".$where_param_fecha_validacion."
				".$where_param_fecha_deposito."
				".$where_param_usuario_cajero."
				".$where_param_tienda."
		ORDER BY bt.id DESC
	";

	return $query;
}
?>