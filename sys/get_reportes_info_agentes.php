<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);




//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_ag") {
	$usuario_id=$login['id'];
	$cargo_id=$login['cargo_id'];


	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
 
  

	$where_fecha_inicio=" AND c.created_at >= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND c.created_at <= date_add('".$busqueda_fecha_fin."', interval 1 day)";
	
	$where_tipo_transaccion="";
 

	$where_cajero="";
	if( (int) $cargo_id === 5 ){
		$where_cajero=" AND tt.user_id='".$usuario_id."' ";
	}  

	$where_local="";
	if( (int) $cargo_id === 5 ){
		$where_local="";
	}  

	$query_1 ="
	SELECT
	a.contrato_id,
	a.c_costos,
	a.nombre_agente,
	a.nombre,
	a.num_ruc,
	a.contacto_email,
	a.contacto_telefono,
	a.created_at,
	a.fecha_suscripcion_contrato,
	group_concat(a.betshop SEPARATOR '') betshop,
	group_concat(a.juegos_virtuales SEPARATOR '') juegos_virtuales,
	group_concat(a.terminales SEPARATOR '') terminales,
	group_concat(a.bingo SEPARATOR '') bingo,
	group_concat(a.deposito_web SEPARATOR '') deposito_web
FROM
	(
	SELECT
		c.contrato_id,
		c.c_costos,
		c.nombre_agente,
		p.nombre,
		p.num_ruc,
		p.contacto_email,
		p.contacto_telefono,
		c.created_at,
		cc.participacion_id,
		cc.porcentaje_participacion,
		cc.condicion_comercial_id,
		c.fecha_suscripcion_contrato,
		-- cp.nombre AS nombre_participacion,
		m.nombre AS nombre_condicion,
IF
	( cc.participacion_id = 1, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) betshop,
IF
	( cc.participacion_id = 2, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) juegos_virtuales,
IF
	( cc.participacion_id = 3, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) terminales,
IF
	( cc.participacion_id = 4, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) bingo,
IF
	( cc.participacion_id = 5, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) deposito_web 
	FROM
		wwwapuestatotal_gestion.cont_contrato c
		INNER JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
		INNER JOIN cont_persona p ON pr.persona_id = p.id
		INNER JOIN cont_cc_agente cc ON cc.contrato_id = c.contrato_id
		INNER JOIN cont_participaciones cp ON cc.participacion_id = cp.id
		INNER JOIN cont_condiciones_comerciales m ON cc.condicion_comercial_id = m.id 
	WHERE
		c.tipo_contrato_id = 6 AND 
		c.status = 1
		".$where_fecha_inicio ."
		".$where_fecha_fin ." 
	) a 
GROUP BY
	a.contrato_id,
	a.c_costos,
	a.nombre_agente,
	a.nombre,
	a.num_ruc,
	a.contacto_email,
	a.contacto_telefono,
	a.created_at,
	a.fecha_suscripcion_contrato

		
		ORDER BY
	a.contrato_id DESC
	";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$list_transaccion=array();
	while ($li=$list_query->fetch_assoc()) {
		$list_transaccion[]=$li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	if(count($list_transaccion)==0){
		$result["http_code"] = 400;
		$result["status"] ="No hay transacciones.";
	} elseif(count($list_transaccion)>0){
		$result["http_code"] = 200;
		$result["status"] ="ok";
		$result["result"] =$list_transaccion;
		//$result["login"]=$login;
	} else{
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
	}

}





//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_ag_export_xls") {

	global $mysqli;
	$usuario_id=$login['id'];
	$cargo_id=$login['cargo_id'];

	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
   

	$where_fecha_inicio=" AND c.created_at >= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND c.created_at <= date_add('".$busqueda_fecha_fin."', interval 1 day)";
	$where_tipo_transaccion="";
 

	$where_cajero="";
	if( (int) $cargo_id === 5 ){
		$where_cajero=" AND tt.user_id='".$usuario_id."' ";
	}  
	
	$where_local="";
	if( (int) $cargo_id === 5 ){
		$where_local="";
	}  

	$liquidacion_id = '2';


	$query_todos = "";
	$row_count_detalle_movilidad = 0;
	$row_count_detalle_liquidacion = 0;
	$suma_monto_total = 0;

	$importe_original = 0;
	$importe_dolares = 0;

	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	$query_cabecera = 
		"SELECT
		a.contrato_id,
		a.c_costos,
		a.nombre_agente,
		a.nombre,
		a.num_ruc,
		a.contacto_email,
		a.contacto_telefono,
		a.created_at,
		a.fecha_suscripcion_contrato,
		group_concat(a.betshop SEPARATOR '') betshop,
		group_concat(a.juegos_virtuales SEPARATOR '') juegos_virtuales,
		group_concat(a.terminales SEPARATOR '') terminales,
		group_concat(a.bingo SEPARATOR '') bingo,
		group_concat(a.deposito_web SEPARATOR '') deposito_web
	FROM
		(
		SELECT
			c.contrato_id,
			c.c_costos,
			c.nombre_agente,
			p.nombre,
			p.num_ruc,
			p.contacto_email,
			p.contacto_telefono,
			c.created_at,
			cc.participacion_id,
			cc.porcentaje_participacion,
			cc.condicion_comercial_id,
			c.fecha_suscripcion_contrato,
			-- cp.nombre AS nombre_participacion,
			m.nombre AS nombre_condicion,
	IF
		( cc.participacion_id = 1, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) betshop,
	IF
		( cc.participacion_id = 2, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) juegos_virtuales,
	IF
		( cc.participacion_id = 3, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) terminales,
	IF
		( cc.participacion_id = 4, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) bingo,
	IF
		( cc.participacion_id = 5, concat( cc.porcentaje_participacion, ' % ', m.nombre ), '' ) deposito_web 
		FROM
			wwwapuestatotal_gestion.cont_contrato c
			INNER JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
			INNER JOIN cont_persona p ON pr.persona_id = p.id
			INNER JOIN cont_cc_agente cc ON cc.contrato_id = c.contrato_id
			INNER JOIN cont_participaciones cp ON cc.participacion_id = cp.id
			INNER JOIN cont_condiciones_comerciales m ON cc.condicion_comercial_id = m.id 
		WHERE
			c.tipo_contrato_id = 6 AND 
			c.status = 1
			".$where_fecha_inicio ."
			".$where_fecha_fin ." 
		) a 
	GROUP BY
		a.contrato_id,
		a.c_costos,
		a.nombre_agente,
		a.nombre,
		a.num_ruc,
		a.contacto_email,
		a.contacto_telefono,
		a.created_at,
		a.fecha_suscripcion_contrato
	
			
			ORDER BY
		a.contrato_id DESC
		";
	
	$list_query_cabecera = $mysqli->query($query_cabecera);

	$row_count_cabecera = $list_query_cabecera->num_rows;

 
 
 

	 
	
	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/descargas/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/descargas/*'); //obtenemos todos los nombres de los ficheros
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
 

	$titulosColumnas_tres = array('CC', 'NOMBRE AGENTE', 'RAZON SOCIAL', 'RUC', 'CORREO', 'CELULAR', 'F. APERTURA', 'TIPO BETSHOP', 'TIPO JUEGOS VIRTUALES', 'TIPO TERMINALES', 'TIPO BINGO', 'TIPO DEPOSITO WEB');


	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
			   
			  
			    ->setCellValue('A1', $titulosColumnas_tres[0])  //Titulo de las columnas
			    ->setCellValue('B1', $titulosColumnas_tres[1])
			    ->setCellValue('C1', $titulosColumnas_tres[2])
			    ->setCellValue('D1', $titulosColumnas_tres[3])
			    ->setCellValue('E1', $titulosColumnas_tres[4])
			    ->setCellValue('F1', $titulosColumnas_tres[5])
			    ->setCellValue('G1', $titulosColumnas_tres[6])
			    ->setCellValue('H1', $titulosColumnas_tres[7])
			    ->setCellValue('I1', $titulosColumnas_tres[8])
			    ->setCellValue('J1', $titulosColumnas_tres[9])
			    ->setCellValue('K1', $titulosColumnas_tres[10])
			    ->setCellValue('L1', $titulosColumnas_tres[11]);
			    

	//Se agregan los datos a la lista del reporte
    $i = 2; //Numero de fila donde se va a comenzar a rellenar

 

	// INICIO DETALLE MOVILIDAD - SI EXISTE MOVILIDAD
	if ($row_count_cabecera > 0)
	{
		while ($reg = $list_query_cabecera->fetch_array()) 
		{

			 
		$c_costos = $reg["c_costos"];
		$nombre_agente = $reg["nombre_agente"];
		$nombre = $reg["nombre"];
		$num_ruc = $reg["num_ruc"];
		$contacto_email = $reg["contacto_email"];
		$contacto_telefono = $reg["contacto_telefono"];
		$created_at = $reg["fecha_suscripcion_contrato"];
		$betshop = $reg["betshop"];
		$juegos_virtuales = $reg["juegos_virtuales"];
		$terminales = $reg["terminales"];
		$bingo = $reg["bingo"];
		$deposito_web = $reg["deposito_web"]; 
			
		 
		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $c_costos)
		->setCellValue('B'.$i, $nombre_agente)
		->setCellValue('C'.$i, $nombre)
		->setCellValue('D'.$i, $num_ruc)
		->setCellValue('E'.$i, $contacto_email)
		->setCellValue('F'.$i, $contacto_telefono)
		->setCellValue('G'.$i, $created_at)
		->setCellValue('H'.$i, $betshop)
		->setCellValue('I'.$i, $juegos_virtuales)
		->setCellValue('J'.$i, $terminales)
		->setCellValue('K'.$i, $bingo)
		->setCellValue('L'.$i, $deposito_web)
		;
$i++;
     
		}
	}

 
 

	$estiloNombresFilas = array(
		'font' => array(
	        'name'      => 'Arial',
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
			        'rgb' => 'ffff00')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

 

	 

	$estiloColoFondoAmarilloOscuro = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => '425CC7')
	  )
	);
	  
	$estiloTituloColumnas = array(
	    'font' => array(
	        'name'  => 'Arial',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => 'FFFFFF'
	        )
	    ),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloCuerpoColumnas = array(
	    'font' => array(
	        'name'  => 'Arial',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => '000000'
	        )
	    ),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);
	  
	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
		'font' => array(
	        'name'  => 'Arial',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => '000000'
	        )
	    ),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	));


	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
 

 

	$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($estiloTituloColumnas);	 

	$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($estiloColoFondoAmarilloOscuro);
  

	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:L".($i));

	 

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE); 
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('INFO DE AGENTES');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Reporte de Agentes.xls');
	header('Cache-Control: max-age=0');

	$date = new DateTime();
	$file_title = "reporte_info_agentes_" . $date->getTimestamp() . "_" . $usuario_id;

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/descargas/'.$file_title.'.xls';
	$excel_path_download = '/files_bucket/contratos/descargas/'.$file_title.'.xls';

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
		"ruta_archivo" => $excel_path_download,
		"estado_archivo" => 1
	));
	exit;
}


echo json_encode($result);


?>