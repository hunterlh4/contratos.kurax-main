<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_slot_listar_locales_busqueda") 
{

	$tipo_busqueda = $_POST["tipo_busqueda"];

	if($tipo_busqueda == 1)
	{
		$query = "
				SELECT
					l.id AS local_id, l.nombre
				FROM tbl_caja_prestamo_slot cps
					INNER JOIN tbl_locales l
					ON cps.local_id_origen = l.id
				WHERE cps.status = 1
				GROUP BY l.id
			";
	}
	else
	{
		$query = "
				SELECT
					l.id AS local_id, l.nombre
				FROM tbl_caja_prestamo_slot cps
					INNER JOIN tbl_locales l
					ON cps.local_id_destino = l.id
				WHERE cps.status = 1
				GROUP BY l.id
			";
	}

	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0)
	{
		$result["http_code"] = 400;
		$result["result"] = "No se encontro resultados.";
	} 
	elseif (count($list) > 0) 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
		$result["existe_caja_abierta"] = $existe_cajas;
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["result"] = "No existen registros.";
	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_slot_listar_prestamos")
{
	$param_tipo_busqueda = $_POST["param_tipo_busqueda"];
	$param_local = $_POST["param_local"];
	$incluir_busqueda_por_fecha = $_POST["incluir_busqueda_por_fecha"];

	$param_fecha_inicio = $_POST['param_fecha_inicio'];
	$param_fecha_inicio = date("Y-m-d", strtotime($param_fecha_inicio));

	$param_fecha_fin = $_POST['param_fecha_fin'];
	$param_fecha_fin = date("Y-m-d", strtotime($param_fecha_fin));

	$param_situacion = $_POST["param_situacion"];
	
	$where_local = "";
	$where_situacion = "";
	$where_fechas = "";

	if($param_tipo_busqueda == 1)
	{
		//BUSQUEDA POR TIENDA ORIGEN

		if($param_local != 0)
		{
			$where_local = " AND cps.local_id_origen = '".$param_local."' ";
		}
	}
	else
	{
		//BUSQUEDA POR TIENDA DESTINO
		
		if($param_local != 0)
		{
			$where_local = " AND cps.local_id_destino = '".$param_local."' ";
		}
	}

	if($param_situacion != 0)
	{
		$where_situacion = " AND cps.situacion_atencion_etapa_id = '".$param_situacion."' ";
	}

	if($incluir_busqueda_por_fecha == 1)
	{
		$where_fechas = " AND DATE_FORMAT(cps.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_inicio."' AND '".$param_fecha_fin."' ";
	}

	$query = "
		SELECT
			cps.id,
			lo.id AS local_origen_id,
			lo.cc_id AS local_origen_ceco,
		    lo.nombre AS local_origen,
		    cps.caja_id_origen AS caja_origen,
		    ld.cc_id AS local_destino_ceco,
		    ld.id AS local_destino_id,
		    ld.nombre AS local_destino,
		    cps.caja_id_destino AS caja_destino,
		    cps.monto,
		    cps.user_created_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		    cps.situacion_atencion_etapa_id AS situacion,
		    cps.created_at AS fecha_solicitud
		FROM tbl_caja_prestamo_slot cps
			INNER JOIN tbl_locales lo
			ON cps.local_id_origen = lo.id
			INNER JOIN tbl_locales ld
			ON cps.local_id_destino = ld.id
			INNER JOIN tbl_usuarios tu
			ON cps.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE cps.status = 1
			".$where_local."
			".$where_situacion."
			".$where_fechas."
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->local_origen . ' [' . $reg->local_origen_ceco . ']',
			"2" => $reg->caja_origen,
			"3" => $reg->local_destino . ' [' . $reg->local_destino_ceco . ']',
			"4" => $reg->caja_destino,
			"5" => "S/ ".number_format($reg->monto, 2, '.', ','),
			"6" => $reg->usuario_solicitante,
			// "7" => ($reg->situacion == 1 ? 'Pendiente' : 'Aprobado'),
			"7" => ($reg->situacion == 1 ? 'Pendiente' : ($reg->situacion == 2 ? 'Aprobado' : 'Anulado')),
			"8" => $reg->fecha_solicitud,
			"9" => '<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=prestamo&amp;sub_sec_id=slot_detalle_solicitud&id='.$reg->id.'&amp;param=1"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
			        '
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_slot_listar_locales_destino_receptor") 
{

	$id_local_origen = $_POST["id_local_origen"];

	$usuario_id = $login?$login['id']:null;
	$usuario_zona_id = $login?$login['zona_id']:null;

	$fecha_hoy = date('Y-m-d');

	// INICIO VALIDAR SI EXISTE UNA CAJA ABIERTA DEL LOCAL (ORIGEN) DE QUIEN SE HARA EL PRESTAMO

	$existe_cajas = 0;

	$select_caja_abierta = 
	"
		SELECT
			lc.id, lc.caja_tipo_id, c.id AS caja_id
		FROM tbl_local_cajas lc
			INNER JOIN tbl_caja c
			ON lc.id = c.local_caja_id
		WHERE lc.local_id = '".$id_local_origen."' 
			AND lc.caja_tipo_id = 1 
			AND c.estado = 0
			AND c.fecha_operacion = '".$fecha_hoy."'
	";

	$query_caja_abierta = $mysqli->query($select_caja_abierta);

	$cant_cajas = $query_caja_abierta->num_rows;


	if($cant_cajas > 0) 
	{
	    // SI EXISTE CAJA ABIERTA
	    $existe_cajas = 1;
	}

	// FIN VALIDAR SI EXISTE UNA CAJA ABIERTA DEL LOCAL (ORIGEN) DE QUIEN SE HARA EL PRESTAMO

	$query = 
	"
		SELECT
			l.id AS local_id, 
			l.cc_id as ceco,
			l.nombre
		FROM tbl_locales l
		WHERE l.zona_id = '".$usuario_zona_id."' AND l.id != '".$id_local_origen."' AND l.nombre IS NOT NULL
        ORDER BY l.nombre ASC
	";
	
	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0)
	{
		$result["http_code"] = 400;
		$result["result"] = "No se encontro resultados.";
	} 
	elseif (count($list) > 0) 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
		$result["existe_caja_abierta"] = $existe_cajas;
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["result"] = "No existen registros.";
	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_prestamo_slot_modal_nuevo_prestamo")
{
	$param_local_origen = $_POST['form_modal_sec_prestamo_slot_param_local_origen'];
	$param_local_destino = $_POST['form_modal_sec_prestamo_slot_param_local_destino'];
	// $param_monto = $_POST['form_modal_sec_prestamo_slot_param_monto'];
	$param_monto = str_replace(",","",$_POST["form_modal_sec_prestamo_slot_param_monto"]);

	$error = '';
	$respuesta_email = "";
	$id_caja_existente = "";
	$existe_cajas = 0;
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		// INICIO VALIDAR SI EXISTE UNA CAJA ABIERTA DEL LOCAL (ORIGEN) DE QUIEN SE HARA EL PRESTAMO

		$existe_cajas = 0;

		$select_caja_abierta = "
					SELECT
						lc.id, lc.caja_tipo_id, c.id AS caja_id
					FROM tbl_local_cajas lc
						INNER JOIN tbl_caja c
						ON lc.id = c.local_caja_id
					WHERE lc.local_id = '".$param_local_origen."' AND lc.caja_tipo_id = 1 AND c.estado = 0
				";

		$query_caja_abierta = $mysqli->query($select_caja_abierta);

		$cant_cajas = $query_caja_abierta->num_rows;


		if($cant_cajas > 0) 
		{
		    // SI EXISTE CAJA ABIERTA
		    $row = $query_caja_abierta->fetch_assoc();
		    $id_caja_existente = $row["caja_id"];
		    $existe_cajas = 1;
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "No se encontro caja abierta.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		// FIN VALIDAR SI EXISTE UNA CAJA ABIERTA DEL LOCAL (ORIGEN) DE QUIEN SE HARA EL PRESTAMO

		// situacion_atencion_etapa_id
		// 1: Pendiente
		// 2: Aprobado
		// 3: Anulado
		$query_insert = "INSERT INTO tbl_caja_prestamo_slot
						(
							local_id_origen,
							caja_id_origen,
							caja_origen_entrega_dinero,
							monto,
							local_id_destino,
							caja_id_destino,
							caja_destino_recibe_dinero,
							situacion_atencion_etapa_id,
							status,
							user_created_id,
							created_at,
							user_updated_id,
							updated_at
						)
						VALUES
						(
							'".$param_local_origen."',
							'".$id_caja_existente."',
							0,
							'".$param_monto."',
							'".$param_local_destino."',
							'',
							0,
							'1',
							1,
							'".$login["id"]."', 
							'".date('Y-m-d H:i:s')."',
							'".$login["id"]."', 
							'".date('Y-m-d H:i:s')."'
						)
						";
		$mysqli->query($query_insert);

		$id_prestamo = mysqli_insert_id($mysqli);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Error al registrar.";
			$result["error"] = $error;
		}
		else
		{
			// INICIO ENVIAR CORREO

			$respuesta_email = send_email_solicitud_prestamo_slot_tiendas($id_prestamo);

			// FIN ENVIAR CORREO

			$result["http_code"] = 200;
			$result["status"] = "Datos guardados";
			$result["error"] = "";
			$result["respuesta_email"] = $respuesta_email;

		}

	}
	else
	{
		$result["http_code"] = 400;
        $result["status"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_slot_btn_export")
{
	$param_tipo_busqueda = $_POST["param_tipo_busqueda"];
	$param_local = $_POST["param_local"];
	$incluir_busqueda_por_fecha = $_POST["incluir_busqueda_por_fecha"];

	$param_fecha_inicio = $_POST['param_fecha_inicio'];
	$param_fecha_inicio = date("Y-m-d", strtotime($param_fecha_inicio));

	$param_fecha_fin = $_POST['param_fecha_fin'];
	$param_fecha_fin = date("Y-m-d", strtotime($param_fecha_fin));

	$param_situacion = $_POST["param_situacion"];
	
	$where_local = "";
	$where_situacion = "";
	$where_fechas = "";



	if($param_tipo_busqueda == 1)
	{
		//BUSQUEDA POR TIENDA ORIGEN

		if($param_local != 0)
		{
			$where_local = " AND cps.local_id_origen = '".$param_local."' ";
		}
	}
	else
	{
		//BUSQUEDA POR TIENDA DESTINO
		
		if($param_local != 0)
		{
			$where_local = " AND cps.local_id_destino = '".$param_local."' ";
		}
	}

	if($param_situacion != 0)
	{
		$where_situacion = " AND cps.situacion_atencion_etapa_id = '".$param_situacion."' ";
	}

	if($incluir_busqueda_por_fecha == 1)
	{
		$where_fechas = " AND DATE_FORMAT(cps.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_inicio."' AND '".$param_fecha_fin."' ";
	}

	$query = "
		SELECT
			cps.id,
			lo.id AS local_origen_id,
		    lo.nombre AS local_origen,
		    cps.caja_id_origen AS caja_origen,
		    cps.caja_origen_entrega_dinero,
		    cps.monto,
		    ld.id AS local_destino_id,
		    ld.nombre AS local_destino,
		    cps.caja_id_destino AS caja_destino,
		    cps.caja_destino_recibe_dinero,
		    cps.user_created_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		    cps.situacion_atencion_etapa_id,
		    cps.created_at AS fecha_solicitud,
		    concat(IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS usuario_atencion,
			cps.fecha_atencion
		FROM tbl_caja_prestamo_slot cps
			INNER JOIN tbl_locales lo
			ON cps.local_id_origen = lo.id
			INNER JOIN tbl_locales ld
			ON cps.local_id_destino = ld.id
			INNER JOIN tbl_usuarios tu
			ON cps.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			LEFT JOIN tbl_usuarios tua
			ON cps.usuario_atencion_id = tua.id
			LEFT JOIN tbl_personal_apt tpa
			ON tua.personal_id = tpa.id
		WHERE cps.status = 1
			".$where_local."
			".$where_situacion."
			".$where_fechas."
		ORDER BY cps.id DESC
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/prestamos/slot/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/prestamos/slot/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

    $tituloReporte = "Relación de préstamo Slot";

	$titulosColumnas = array('Nº', 'Tienda Origen', 'Caja Prestadora', 'Confirmación Dinero Entregado', 'Monto', 'Tienda Destino', 'Caja Receptora', 'Confirmación Dinero Recibido', 'Solicitante', 'Fecha Solicitud', 'Situación', 'Atendido Por', 'Fecha Atención');

	// Se combinan las celdas A1 hasta K1, para colocar ahí el titulo del reporte
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:M1');
	
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
    ->setCellValue('M2', $titulosColumnas[12]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 3; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$situacion = "";
		$dinero_entregado = "";
		$dinero_recibido = "";

		if($fila['situacion_atencion_etapa_id'] == 1)
		{
			$situacion = "Pendiente";
		}
		else if($fila['situacion_atencion_etapa_id'] == 2)
		{
			$situacion = "Aprobado";
		}
		else if($fila['situacion_atencion_etapa_id'] == 3)
		{
			$situacion = "Anulado";
		}

		if($fila['caja_origen_entrega_dinero'] == 1)
		{
			$dinero_entregado = "Si";
		}
		else
		{
			$dinero_entregado = "No";
		}
		
		if($fila['caja_destino_recibe_dinero'] == 1)
		{
			$dinero_recibido = "Si";
		}
		else
		{
			$dinero_recibido = "No";
		}

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['local_origen'])
		->setCellValue('C'.$i, $fila['caja_origen'])
		->setCellValue('D'.$i, $dinero_entregado)
		->setCellValue('E'.$i, 'S/ '.$fila['monto'])
		->setCellValue('F'.$i, $fila['local_destino'])
		->setCellValue('G'.$i, $fila['caja_destino'])
		->setCellValue('H'.$i, $dinero_recibido)
		->setCellValue('I'.$i, $fila['usuario_solicitante'])
		->setCellValue('J'.$i, $fila['fecha_solicitud'])
		->setCellValue('K'.$i, $situacion)
		->setCellValue('L'.$i, $fila['usuario_atencion'])
		->setCellValue('M'.$i, $fila['fecha_atencion']);
		
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

	$objPHPExcel->getActiveSheet()->getStyle('A1:M1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:M".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('D3:D'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Préstamo Slot');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Préstamo Slot.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/prestamos/slot/Préstamo Slot.xls';
	$excel_path_download = '/files_bucket/prestamos/slot/Préstamo Slot.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

function send_email_solicitud_prestamo_slot_tiendas($id_prestamo)
{
	include("db_connect.php");
	include("sys_login.php");

	$respuesta_email = 0;

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	$sel_query = $mysqli->query("
		SELECT
			cps.id,
			lo.id AS local_origen_id,
		    lo.nombre AS local_origen,
		    cps.caja_id_origen,
		    cps.monto,
		    ld.id AS local_destino_id,
		    ld.nombre AS local_destino,
		    cps.caja_id_destino,
		    cps.user_created_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		    cps.created_at AS fecha_solicitud,
		    cps.situacion_atencion_etapa_id
		FROM tbl_caja_prestamo_slot cps
			INNER JOIN tbl_locales lo
			ON cps.local_id_origen = lo.id
			INNER JOIN tbl_locales ld
			ON cps.local_id_destino = ld.id
			INNER JOIN tbl_usuarios tu
			ON cps.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE cps.id = '".$id_prestamo."'
	");

	$body = "";
	$body .= '<html>';

	$situacion_atencion = "";

	while($sel = $sel_query->fetch_assoc())
	{
		$id = $sel["id"];
		$local_origen_id = $sel["local_origen_id"];
		$local_origen = $sel["local_origen"];
		$caja_id_origen = $sel["caja_id_origen"];
		$monto = $sel["monto"];
		$local_destino_id = $sel["local_destino_id"];
		$local_destino = trim($sel["local_destino"]);
		$caja_id_destino = trim($sel["caja_id_destino"]);
		$user_created_id = trim($sel["user_created_id"]);
		$usuario_solicitante = trim($sel["usuario_solicitante"]);
		$fecha_solicitud = trim($sel["fecha_solicitud"]);
		$situacion_atencion_etapa_id = trim($sel["situacion_atencion_etapa_id"]);

		if($situacion_atencion_etapa_id == 1)
		{
			$situacion_atencion = "Pendiente";
		}
		else
		{
			$situacion_atencion = "Aprobado";
		}
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nueva Solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Usuario Solicitante:</b></td>';
			$body .= '<td>'.$usuario_solicitante.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tienda Origen:</b></td>';
			$body .= '<td>'.$local_origen.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Caja Prestadora:</b></td>';
			$body .= '<td>'.$caja_id_origen.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Monto:</b></td>';
			$body .= '<td>S/ '.number_format($monto, 2 , '.', ',').'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tienda Destino:</b></td>';
			$body .= '<td>'.$local_destino.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Caja Receptora:</b></td>';
			$body .= '<td>'.$caja_id_destino.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$fecha_solicitud.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Situación:</b></td>';
			$body .= '<td>'.$situacion_atencion.'</td>';
		$body .= '</tr>';
	
		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=prestamo&sub_sec_id=slot_detalle_solicitud&id='.$id_prestamo.'&amp;param=2" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	$sub_titulo_email = "";

    if (env('SEND_EMAIL') == 'test')
    {
        $sub_titulo_email = "TEST SISTEMAS: ";
    }
    
	$titulo_email = $sub_titulo_email."Préstamo entre tiendas - Tienda: ".$local_origen." - Nueva Solicitud de Prestamo ID: ".$id_prestamo;
	
	$cc = [
	];

	$bcc = [
	];

	// INICIO LISTAR USUARIOS DE LA TIENDAS ORIGEN Y DESTINO DEL PRESTAMO
	// USUARIOS: SUPERVISORES Y CAJEROS
	// AREA OPERACIONES: 21
	// CARGO CAJERO: 5
	// CARGO SUPERVISOR: 4
	$select_usuarios_enviar_a = 
	"
		SELECT DISTINCT
			p.correo
		FROM tbl_usuarios_locales ul
			LEFT JOIN tbl_usuarios u
			ON ul.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE ul.local_id IN ('".$local_origen_id."', '".$local_destino_id."') AND ul.estado = 1 
			AND p.correo IS NOT NULL AND p.estado = 1 
			AND (p.area_id = 21 AND p.cargo_id IN (4, 5))
	";

	$sel_query_usuarios_enviar_a = $mysqli->query($select_usuarios_enviar_a);

	$row_count = $sel_query_usuarios_enviar_a->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_usuarios_enviar_a->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($cc, $sel['correo']);	
			}
		}
	}
	// FIN LISTAR USUARIOS DE LA TIENDAS ORIGEN Y DESTINO DEL PRESTAMO
	
	// INICIO LISTAR JEFE COMERCIALES DE LAS TIENDAS DEL PRESTAMO REALIZADO
	$select_usuarios_jc_enviar_a = 
	"
		SELECT DISTINCT
			p.correo
		FROM tbl_locales l
			INNER JOIN tbl_zonas z
			ON l.zona_id = z.id
			INNER JOIN tbl_personal_apt p
			ON p.id = z.jop_id
		WHERE l.id IN ('".$local_origen_id."', '".$local_destino_id."') 
			AND p.correo IS NOT NULL AND p.estado = 1
	";
	
	$sel_query_usuarios_jc_enviar_a = $mysqli->query($select_usuarios_jc_enviar_a);

	$row_count_jc = $sel_query_usuarios_jc_enviar_a->num_rows;

	if ($row_count_jc > 0)
	{
		while($sel = $sel_query_usuarios_jc_enviar_a->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($cc, $sel['correo']);	
			}
		}
	}
	// FIN LISTAR JEFE COMERCIALES DE LAS TIENDAS DEL PRESTAMO REALIZADO

	//INICIO: LISTAR USUARIOS DEL GRUPO - COPIA OCULTA
	$query_select_usuario_sistemas_cco = 
	"
		SELECT
			pg.id, pg.metodo, pg.status AS prestamo_grupo_estado,
		    pu.usuario_id, p.nombre, p.correo
		FROM tbl_prestamo_mantenimiento_correo_grupo pg
			INNER JOIN tbl_prestamo_mantenimiento_correo_usuario pu
			ON pg.id = pu.tbl_prestamo_mantenimiento_correo_grupo_id
			LEFT JOIN tbl_usuarios u
			ON pu.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE pg.metodo = 'prestamo_entre_tiendas_area_sistemas_cco' 
			AND pg.status = 1 
			AND pu.status = 1
	";

	$sel_query_select_usuario_sistemas_cco = $mysqli->query($query_select_usuario_sistemas_cco);

	$row_count = $sel_query_select_usuario_sistemas_cco->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_select_usuario_sistemas_cco->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($bcc, $sel['correo']);
			}
		}
	}
	//FIN: LISTAR USUARIOS DEL GRUPO - COPIA OCULTA

	$request = [
		"subject" => $titulo_email,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;
		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();

		return $respuesta_email = true;

	}
	catch (Exception $e) 
	{
		return $respuesta_email = $e;
	}
}

?>