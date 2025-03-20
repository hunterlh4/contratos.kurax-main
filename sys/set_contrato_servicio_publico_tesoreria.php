<?php 

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

function contrato_servicio_publico_tesoreria_nombre_mes($fecha)
{
	if (Empty($fecha))
	{
		return '';
	}
	
	$anio = date("Y", strtotime($fecha));
	$mes = date("m", strtotime($fecha));
	$nombre_mes = "";
	
	switch ($mes)
	{
		case '01': $nombre_mes = "Enero"; break;
		case '02': $nombre_mes = "Febrero"; break;
		case '03': $nombre_mes = "Marzo"; break;
		case '04': $nombre_mes = "Abril"; break;
		case '05': $nombre_mes = "Mayo"; break;
		case '06': $nombre_mes = "Junio"; break;
		case '07': $nombre_mes = "Julio"; break;
		case '08': $nombre_mes = "Agosto"; break;
		case '09': $nombre_mes = "Septiembre"; break;
		case '10': $nombre_mes = "Octubre"; break;
		case '11': $nombre_mes = "Noviembre"; break;
		case '12': $nombre_mes = "Diciembre"; break;
	}
	return $nombre_mes." del ".$anio;
}

function contrato_servicio_publico_tesoreria_item_atencion_nombre_mes($fecha)
{
	if (Empty($fecha))
	{
		return '';
	}

	$anio = date("Y", strtotime($fecha));
	$mes = date("m", strtotime($fecha));
	$nombre_mes = "";
	switch ($mes)
	{
		case '01': $nombre_mes = "Enero"; break;
		case '02': $nombre_mes = "Febrero"; break;
		case '03': $nombre_mes = "Marzo"; break;
		case '04': $nombre_mes = "Abril"; break;
		case '05': $nombre_mes = "Mayo"; break;
		case '06': $nombre_mes = "Junio"; break;
		case '07': $nombre_mes = "Julio"; break;
		case '08': $nombre_mes = "Agosto"; break;
		case '09': $nombre_mes = "Septiembre"; break;
		case '10': $nombre_mes = "Octubre"; break;
		case '11': $nombre_mes = "Noviembre"; break;
		case '12': $nombre_mes = "Diciembre"; break;
	}

	return $nombre_mes." del ".$anio;
}

function contrato_servicio_publico_tesoreria_item_atencion_nombre_resumen_mes($fecha)
{
    if (empty($fecha))
    {
        return '';
    }

    $anio = date("Y", strtotime($fecha));
    $mes = date("m", strtotime($fecha));
    $nombre_mes = "";
    switch ($mes)
    {
        case '01': $nombre_mes = "ENE"; break;
        case '02': $nombre_mes = "FEB"; break;
        case '03': $nombre_mes = "MAR"; break;
        case '04': $nombre_mes = "ABR"; break;
        case '05': $nombre_mes = "MAY"; break;
        case '06': $nombre_mes = "JUN"; break;
        case '07': $nombre_mes = "JUL"; break;
        case '08': $nombre_mes = "AGO"; break;
        case '09': $nombre_mes = "SEP"; break;
        case '10': $nombre_mes = "OCT"; break;
        case '11': $nombre_mes = "NOV"; break;
        case '12': $nombre_mes = "DIC"; break;
    }

    return $nombre_mes;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_servicio_publico_tesoreria_obtener_supervisores") 
{

	$zona_id = $_POST["zona_id"];
	
	$where_zona = "";
	$where_local = "";
	
	$usuario_id = $login?$login['id']:null;
		// verificar si el usuario tiene todos los permisos locales 
	$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;

	$list_query_permisos = $mysqli->query($query);
	if ($list_query_permisos->num_rows > 0) {
		$where_local = " AND  ul2.usuario_id ='".$usuario_id."' ";
	}

	if($zona_id != 0)
	{
		$where_zona = " AND l.zona_id = '".$zona_id."' ";
	}

	$query = 
	"
		SELECT
			us.id,
			concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) AS nombre
		FROM tbl_locales l
			INNER JOIN tbl_usuarios_locales ul
			ON l.id = ul.local_id
			INNER JOIN tbl_usuarios AS us
			ON ul.usuario_id = us.id AND ul.estado = 1
			INNER JOIN tbl_personal_apt AS ps
			ON us.personal_id = ps.id AND ps.area_id = 21 AND ps.cargo_id = 4
			LEFT JOIN tbl_usuarios_locales ul2 ON ul2.local_id = l.id
			WHERE l.estado=1
		".$where_zona."
		".$where_local."
		GROUP BY us.id
		ORDER BY concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) ASC
	";
	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["status"] = "Error.";
		$result["result"] = $mysqli->error;
	}
	else
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;	
	}
	
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_servicio_publico_tesoreria_obtener_locales") 
{

	$param_zona = $_POST["param_zona"];
	$param_supervisor = $_POST["param_supervisor"];
	
	$where_supervisor = "";
	$where_zona = "";
	$where_local = "";
	$usuario_id = $login?$login['id']:null;
		// verificar si el usuario tiene todos los permisos locales 
	$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;

	$list_query_permisos = $mysqli->query($query);
	if ($list_query_permisos->num_rows > 0) {
		$where_local = " AND  tul.usuario_id ='".$usuario_id."' ";
	}

	if($param_supervisor != 0)
	{
		$where_supervisor = " AND ul.usuario_id = '".$param_supervisor."' ";
	}
	else
	{
		if($param_zona != 0)
		{
			$where_zona = " AND l.zona_id = '".$param_zona."' ";	
		}
	}

	$query = 
	"
		SELECT
			l.id,
		    l.nombre
		FROM tbl_locales l
			INNER JOIN tbl_usuarios_locales ul
			ON l.id = ul.local_id AND ul.estado = 1
			INNER JOIN tbl_usuarios AS us
			ON ul.usuario_id = us.id
			INNER JOIN tbl_personal_apt AS ps
			ON us.personal_id = ps.id AND ps.area_id = 21 AND ps.cargo_id = 4
			LEFT JOIN tbl_usuarios_locales tul 
			on tul.local_id = l.id
		WHERE l.estado = 1
		".$where_supervisor."
		".$where_zona."
		".$where_local."
		GROUP BY l.id
		ORDER BY l.nombre ASC
	";
	// var_dump($query);exit();
	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["status"] = "Error.";
		$result["result"] = $mysqli->error;
	}
	else
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;	
	}
	
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_buscar_servicio_publico")
{
	$param_buscar_por = $_POST['param_buscar_por'];
	$param_tipo_recibo = $_POST['param_tipo_recibo'];
	$param_tipo_servicio = $_POST['param_tipo_servicio'];
	$param_empresa_arrendataria = $_POST['param_empresa_arrendataria'];
	$param_zona = $_POST['param_zona'];
	$param_supervisor = $_POST['param_supervisor'];
	$param_local = $_POST['param_local'];
	$param_periodo = $_POST['param_periodo'];
	$param_fecha_incio = date("Y-m-d", strtotime($_POST["param_fecha_incio"]));
	$param_fecha_fin = date("Y-m-d", strtotime($_POST["param_fecha_fin"]));
	$param_estado = $_POST['param_estado'];

	$query = "";
	
	$where_tipo_recibo = "";
	$where_tipo_servicio = "";
	$where_empresa_arrendataria = "";
	$where_zona = "";
	$where_supervisor = "";
	$where_local = "";
	$where_periodo = "";
	$where_fechas = "";
	$where_estado = "";

	if($param_tipo_recibo != 0)
	{
		if($param_tipo_recibo == 1)
		{
			// RECIBOS TOTALES
			$where_tipo_recibo = " AND cis.tipo_compromiso_pago_id IN(3)";
		}
		else if($param_tipo_recibo == 2)
		{
			// RECIBOS COMPARTIDOS
			$where_tipo_recibo = " AND cis.tipo_compromiso_pago_id IN(1, 2, 4, 5, 6)";
		}
	}

	if($param_tipo_servicio != 0)
	{
		$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = '".$param_tipo_servicio."' ";
	}

	if($param_empresa_arrendataria != 0)
	{
		$where_empresa_arrendataria = " AND l.razon_social_id = '".$param_empresa_arrendataria."' ";
	}

	if($param_zona != 0)
	{
		$where_zona = " AND l.zona_id = '".$param_zona."' ";
	}

	if($param_supervisor != 0)
	{
		$where_supervisor = " AND us.id = '".$param_supervisor."' ";
	}

	if($param_local != 0)
	{
		$where_local = " AND l.id = '".$param_local."' ";
	}

	if($param_buscar_por == 1)
	{
		// BUSCAR POR PERIODO
		if($param_periodo != 0)
		{
			$anio = substr($param_periodo, 0 , 4);
			$mes = substr($param_periodo, 5, 2);
			$where_periodo = " AND month(sp.periodo_consumo) = '".$mes."' 
								AND year(sp.periodo_consumo) = '".$anio."' ";
		}
	}
	else if($param_buscar_por == 2)
	{
		// BUSCAR POR FECHAS
		if($param_fecha_incio != "" && $param_fecha_fin != "")
		{
			$where_fechas = " AND (date_format(sp.fecha_vencimiento, '%Y-%m-%d') BETWEEN '".$param_fecha_incio."' AND '".$param_fecha_fin ."') ";
		}
	}

	if($param_estado != 0)
	{
		$where_estado = " AND sp.estado = '".$param_estado."' ";
	}

	$query = "
		SELECT
			sp.id,
		    r.nombre AS empresa_arrendataria,
		    z.nombre AS zona_nombre,
		    concat( IFNULL(pj.nombre, ''),' ', IFNULL(pj.apellido_paterno, ''),' ', IFNULL(pj.apellido_materno, '')) AS jefe_comercial,
		    concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) AS creador,
		    IFNULL(l.cc_id,'0') AS local_centro_costo,
		    l.id AS local_id,
			l.nombre AS local_nombre,
		    tsp.nombre AS tipo_servicio_nombre,
		    ea.nombre_comercial AS empresa_agua_nombre_comercial,
			el.nombre_comercial AS empresa_luz_nombre_comercial,
		    sp.periodo_consumo,
		    cis.tipo_servicio_id AS tipo_servicio_publico,
		    cis.nro_suministro,
		    sp.monto_total AS monto,
		    sp.fecha_emision,
		    sp.fecha_vencimiento,
		    sp.estado,
		    esp.nombre as estado_nombre,
		    sp.id_tipo_servicio_publico,
		    sp.total_pagar AS total_pagado
		FROM cont_local_servicio_publico sp
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			INNER JOIN cont_contrato_detalle cd
			ON i.contrato_id = cd.contrato_id AND i.contrato_detalle_id = cd.id
			INNER JOIN cont_contrato c
			ON cd.contrato_id = c.contrato_id
			INNER JOIN tbl_locales AS l
			ON l.contrato_id = c.contrato_id
			INNER JOIN tbl_razon_social AS r 
			ON r.id = l.razon_social_id
			INNER JOIN tbl_zonas AS z
			ON l.zona_id = z.id
			LEFT JOIN tbl_personal_apt AS pj
			ON z.jop_id = pj.id 
			INNER JOIN tbl_usuarios uc
			ON sp.user_created_id = uc.id
			INNER JOIN tbl_personal_apt AS ps
			ON uc.personal_id = ps.id
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			LEFT JOIN cont_local_servicio_publico_empresas ea
			ON i.id_empresa_servicio_agua = ea.id
			LEFT JOIN cont_local_servicio_publico_empresas el
			ON i.id_empresa_servicio_luz = el.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
		WHERE sp.status = 1
			".$where_tipo_recibo."
			".$where_tipo_servicio."
			".$where_empresa_arrendataria."
			".$where_zona."
			".$where_supervisor."
			".$where_local."
			".$where_periodo."
			".$where_fechas."
			".$where_estado."
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$onclick_servicio_publico = "agregarMonto('".$reg->id_tipo_servicio_publico."', '".$reg->local_id."', '".$reg->periodo_consumo."', '".$reg->local_centro_costo." ".$reg->local_nombre."', '".$reg->id."')";

		$tipo_servicio_publico = $reg->tipo_servicio_publico;

		$empresa_servicio_publico = "El servicio publico no es Agua ni Luz.";

		if($tipo_servicio_publico == 1)
		{
			// LUZ
			$empresa_servicio_publico = $reg->empresa_luz_nombre_comercial;
		}
		else if($tipo_servicio_publico == 2)
		{
			// AGUA
			$empresa_servicio_publico = $reg->empresa_agua_nombre_comercial;
		}

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->jefe_comercial,
			"2" => $reg->creador,
			"3" => '['.$reg->local_centro_costo.'] '.$reg->local_nombre,
			"4" => $reg->tipo_servicio_nombre,
			"5" => $empresa_servicio_publico,
			"6" => contrato_servicio_publico_tesoreria_nombre_mes($reg->periodo_consumo),
			"7" => $reg->nro_suministro,
			"8" => $reg->monto,
			"9" => $reg->fecha_vencimiento,
			"10" => $reg->estado_nombre,
			"11" => $reg->total_pagado,
			"12" => '
					<button type="button" class="btn btn-sm btn-info" style="margin-left: 10px" onclick="'.$onclick_servicio_publico.'"><i class="fa fa-eye"></i></button>
					'
		);

		$num++;
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

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_reporte_btn_listar_servicios_publicos")
{
	$param_buscar_por = $_POST['param_buscar_por'];
	$param_tipo_recibo = $_POST['param_tipo_recibo'];
	$param_tipo_servicio = $_POST['param_tipo_servicio'];
	$param_empresa_arrendataria = $_POST['param_empresa_arrendataria'];
	$param_zona = $_POST['param_zona'];
	$param_supervisor = $_POST['param_supervisor'];
	$param_local = $_POST['param_local'];
	$param_periodo = $_POST['param_periodo'];
	$param_fecha_incio = date("Y-m-d", strtotime($_POST["param_fecha_incio"]));
	$param_fecha_fin = date("Y-m-d", strtotime($_POST["param_fecha_fin"]));
	$param_estado = $_POST['param_estado'];

	$query = "";
	
	$where_tipo_recibo = "";
	$where_tipo_servicio = "";
	$where_empresa_arrendataria = "";
	$where_zona = "";
	$where_supervisor = "";
	$where_local = "";
	$where_periodo = "";
	$where_fechas = "";
	$where_estado = "";

	if($param_tipo_recibo != 0)
	{
		if($param_tipo_recibo == 1)
		{
			// RECIBOS TOTALES
			$where_tipo_recibo = " AND cis.tipo_compromiso_pago_id IN(3)";
		}
		else if($param_tipo_recibo == 2)
		{
			// RECIBOS COMPARTIDOS
			$where_tipo_recibo = " AND cis.tipo_compromiso_pago_id IN(1, 2, 4, 5, 6)";
		}
	}

	if($param_tipo_servicio != 0)
	{
		$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = '".$param_tipo_servicio."' ";
	}

	if($param_empresa_arrendataria != 0)
	{
		$where_empresa_arrendataria = " AND c.empresa_suscribe_id = '".$param_empresa_arrendataria."' ";
	}

	if($param_zona != 0)
	{
		$where_zona = " AND l.zona_id = '".$param_zona."' ";
	}

	if($param_supervisor != 0)
	{
		$where_supervisor = " AND us.id = '".$param_supervisor."' ";
	}

	if($param_local != 0)
	{
		$where_local = " AND l.id = '".$param_local."' ";
	}

	if($param_buscar_por == 1)
	{
		// BUSCAR POR PERIODO
		if($param_periodo != 0)
		{
			$anio = substr($param_periodo, 0 , 4);
			$mes = substr($param_periodo, 5, 2);	
			$where_periodo = " AND month(sp.periodo_consumo) = '".$mes."' 
								AND year(sp.periodo_consumo) = '".$anio."' ";
		}
	}
	else if($param_buscar_por == 2)
	{
		// BUSCAR POR FECHAS
		if($param_fecha_incio != "" && $param_fecha_fin != "")
		{
			$where_fechas = " AND (sp.fecha_vencimiento BETWEEN '".$param_fecha_incio."' 
									AND '".$param_fecha_fin ."') ";
		}
	}

	if($param_estado != 0)
	{
		$where_estado = " AND sp.estado = '".$param_estado."' ";
	}

	$query = "
		SELECT
			sp.id,
		    r.nombre AS empresa_arrendataria,
		    z.nombre AS zona_nombre,
		    concat( IFNULL(pj.nombre, ''),' ', IFNULL(pj.apellido_paterno, ''),' ', IFNULL(pj.apellido_materno, '')) AS jefe_comercial,
		    sp.created_at AS fecha_creacion,
		    concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) AS creador,

		   concat( IFNULL(pac.nombre, ''),' ', IFNULL(pac.apellido_paterno, ''),' ', IFNULL(pac.apellido_materno, '')) AS usuario_atencion_contabilidad,
		   sp.fecha_atencion_contabilidad,
		    IFNULL(l.cc_id,'0') AS local_centro_costo,
		    l.id AS local_id,
			l.nombre AS local_nombre,
		    tsp.nombre AS tipo_servicio_nombre,
		    ea.nombre_comercial AS empresa_agua_nombre_comercial,
			el.nombre_comercial AS empresa_luz_nombre_comercial,
		    sp.periodo_consumo,
		    cis.tipo_servicio_id AS tipo_servicio_publico,
		    cis.nro_suministro,
		    sp.monto_total AS monto,
		    sp.fecha_emision,
		    sp.fecha_vencimiento,
		    sp.estado,
		    esp.nombre as estado_nombre,
		    sp.total_pagar AS total_pagado,
		    p.fecha_comprobante AS fecha_pago
		FROM cont_local_servicio_publico sp
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			INNER JOIN cont_contrato_detalle cd
			ON i.contrato_id = cd.contrato_id AND i.contrato_detalle_id = cd.id
			INNER JOIN cont_contrato c
			ON cd.contrato_id = c.contrato_id
			INNER JOIN tbl_razon_social AS r 
			ON r.id = c.empresa_suscribe_id
			INNER JOIN tbl_locales AS l
			ON l.contrato_id = c.contrato_id
			INNER JOIN tbl_zonas AS z
			ON l.zona_id = z.id
			LEFT JOIN tbl_personal_apt AS pj
			ON z.jop_id = pj.id 
			INNER JOIN tbl_usuarios uc
			ON sp.user_created_id = uc.id
			INNER JOIN tbl_personal_apt AS ps
			ON uc.personal_id = ps.id
			LEFT JOIN tbl_usuarios uac
			ON sp.user_atencion_contabilidad = uac.id
			LEFT JOIN tbl_personal_apt AS pac
			ON uac.personal_id = pac.id
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			LEFT JOIN cont_local_servicio_publico_empresas ea
			ON i.id_empresa_servicio_agua = ea.id
			LEFT JOIN cont_local_servicio_publico_empresas el
			ON i.id_empresa_servicio_luz = el.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
			 LEFT JOIN cont_ser_pub_programacion_detalle pd
		    ON sp.id = pd.cont_local_servicio_publico_id
		    LEFT JOIN cont_ser_pub_programacion p
		    ON pd.cont_ser_pub_programacion_id = p.id
		WHERE sp.status = 1
			".$where_tipo_recibo."
			".$where_tipo_servicio."
			".$where_empresa_arrendataria."
			".$where_zona."
			".$where_supervisor."
			".$where_local."
			".$where_periodo."
			".$where_fechas."
			".$where_estado."
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/*'); //obtenemos todos los nombres de los ficheros
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

    $tituloReporte = "Relación de Servicios Públicos - Tesorería";

	$titulosColumnas = array('Nº', 'Empresa Arrendataria', 'Zona', 'Jefe Comercial', 'Creado Por', 'F. Creación', 'Local', 'Servicio', 'Empresa', 'Periodo', 'Cod. Suministro', 'Monto', 'F. Vencimiento', 'Estado', 'Usuario Atención Contabilidad', 'F. Atención Contabilidad', 'Monto Total', 'F. Pago');

	// Se combinan las celdas A1 hasta M1, para colocar ahí el titulo del reporte
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:R1');
	
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
    ->setCellValue('N2', $titulosColumnas[13])
    ->setCellValue('O2', $titulosColumnas[14])
    ->setCellValue('P2', $titulosColumnas[15])
    ->setCellValue('Q2', $titulosColumnas[16])
    ->setCellValue('R2', $titulosColumnas[17]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 3; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$tipo_servicio_publico = $fila['tipo_servicio_publico'];

		$empresa_servicio_publico = "El servicio publico no es Agua ni Luz.";

		if($tipo_servicio_publico == 1)
		{
			// LUZ
			$empresa_servicio_publico = $fila['empresa_luz_nombre_comercial'];
		}
		else if($tipo_servicio_publico == 2)
		{
			// AGUA
			$empresa_servicio_publico = $fila['empresa_agua_nombre_comercial'];
		}

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['empresa_arrendataria'])
		->setCellValue('C'.$i, $fila['zona_nombre'])
		->setCellValue('D'.$i, $fila['jefe_comercial'])
		->setCellValue('E'.$i, $fila['creador'])
		->setCellValue('F'.$i, $fila['fecha_creacion'])
		->setCellValue('G'.$i, '['.$fila['local_centro_costo'].'] '.$fila['local_nombre'])
		->setCellValue('H'.$i, $fila['tipo_servicio_nombre'])
		->setCellValue('I'.$i, $empresa_servicio_publico)
		->setCellValue('J'.$i, $fila['periodo_consumo'])
		->setCellValue('K'.$i, $fila['nro_suministro'])
		->setCellValue('L'.$i, "S/ ".$fila['monto'])
		->setCellValue('M'.$i, $fila['fecha_vencimiento'])
		->setCellValue('N'.$i, $fila['estado_nombre'])
		->setCellValue('O'.$i, $fila['usuario_atencion_contabilidad'])
		->setCellValue('P'.$i, $fila['fecha_atencion_contabilidad'])
		->setCellValue('Q'.$i, "S/ ".$fila['total_pagado'])
		->setCellValue('R'.$i, $fila['fecha_pago']);
		
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

	$objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:Q".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:R'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('L3:L'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Servicios Públicos');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Servicios Públicos Tesorería.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/Servicios Públicos Tesorería.xls';
	$excel_path_download = '/files_bucket/contratos/servicios_publicos/reportes/tesoreria/Servicios Públicos Tesorería.xls';

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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_servicio_publico_tesoreria_listar_programacion_pago")
{
	$param_tipo_solicitud = $_POST['param_tipo_solicitud'];

	$param_fecha_inicio = $_POST['param_fecha_inicio'];
	$param_fecha_inicio = date("Y-m-d", strtotime($param_fecha_inicio));

	$param_fecha_fin = $_POST['param_fecha_fin'];
	$param_fecha_fin = date("Y-m-d", strtotime($param_fecha_fin));

	$usuario_id = $login?$login['id']:null;

	// INICIO: VERIFICAR RED
	
	$where_redes = "";

	$select_red =
	"
	    SELECT
	        l.red_id
	    FROM tbl_usuarios_locales ul
	        INNER JOIN tbl_locales l
	        ON ul.local_id = l.id
	    WHERE ul.usuario_id = '".$usuario_id."'
	    	AND ul.estado = 1 AND l.red_id IS NOT NULL
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

	    $where_redes = " AND rs.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	$query = 
	"
		SELECT
			spp.id,
		    spp.tipo_solicitud_id,
		    spts.nombre AS tipo_solicitud,
		    rs.nombre AS tipo_empresa,
		    spp.created_at AS fecha_programacion,    
		    (
				SELECT
					IFNULL(SUM(spi.total_pagar), 0) AS monto
		        FROM cont_ser_pub_programacion_detalle pdi
					INNER JOIN cont_local_servicio_publico spi
					ON pdi.cont_local_servicio_publico_id = spi.id
				WHERE pdi.cont_ser_pub_programacion_id = spp.id AND pdi.status = 1
			) AS importe_total,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_creacion,
		    tesp.nombre AS situacion_programacion,
		    spp.se_cargo_comprobante
		FROM cont_ser_pub_programacion spp
			INNER JOIN cont_ser_pub_tipo_solicitud spts
			ON spp.tipo_solicitud_id = spts.id
			INNER JOIN tbl_razon_social rs
			ON spp.tipo_empresa_id = rs.id
			INNER JOIN tbl_usuarios tu
			ON spp.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_tipo_estado_servicio_publico tesp
			ON spp.situacion_id = tesp.id
		WHERE spp.status = 1 AND spp.tipo_solicitud_id = '".$param_tipo_solicitud."'
			AND DATE_FORMAT(spp.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_inicio."' AND '".$param_fecha_fin."'
			".$where_redes."
			GROUP BY  spp.id
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$exportButton = ($param_tipo_solicitud == 2) ? 
						'<button type="button" class="btn text-center btn-primary btn-sm" title="Generar txt" 
							onclick="contrato_servicio_publico_tesoreria_item_detalle_programacion_exportar_text_detalle('.$reg->id.');">
							<i class="fa fa-file-text-o"></i>
						</button>' 
						: '';

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->tipo_solicitud,
			"2" => $reg->tipo_empresa,
			"3" => $reg->fecha_programacion,
			"4" => "S/ ".$reg->importe_total,
			"5" => $reg->usuario_creacion,
			"6" => $reg->situacion_programacion,
			"7" => ($reg->se_cargo_comprobante == 0) ? 
					'<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=contrato&amp;sub_sec_id=servicio_publico_tesoreria&item_detalle_programacion='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
					'. $exportButton.'
                    <a onclick="";
                        class="btn btn-warning btn-sm"
                        href="./?sec_id=contrato&amp;sub_sec_id=servicio_publico_tesoreria&item_atencion='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Editar Programación">
                        <span class="fa fa-edit"></span>
                    </a>
                    '
                    : 
                    '<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=contrato&amp;sub_sec_id=servicio_publico_tesoreria&item_detalle_programacion='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    '. $exportButton
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

if(isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago")
{

	$tipo_consulta = $_POST["param_tipo_consulta"];
	$param_tipo_solicitud = $_POST["param_tipo_solicitud"];
	$param_tipo_solicitud_nombre = $_POST["param_tipo_solicitud_nombre"];
	$param_tipo_empresa = $_POST["param_tipo_empresa"];
	$param_tipo_servicio = $_POST["param_tipo_servicio"];
	$param_ids_recibo = $_POST["ids_recibos"];

	$contador_array_ids = 0;
	$data_param_ids_recibo = json_decode($param_ids_recibo);
	
	$ids_recibos = '';

	if($param_tipo_servicio != 0){
        $where_tipo_servicio .= " AND sp.id_tipo_servicio_publico = ".$param_tipo_servicio. " ";
	}

	foreach ($data_param_ids_recibo as $value) 
	{
		if ($contador_array_ids > 0) 
		{
			$ids_recibos .= ',';
		}
		
		$ids_recibos .= $value;			
		$contador_array_ids++;
	}

	if($contador_array_ids == 0)
	{
		$ids_recibos = 0;
	}

	$where_tipo_solicitud = "";

	if($param_tipo_solicitud == 1)
	{
		// RECIBOS TOTALES
		$where_tipo_solicitud = " AND cis.tipo_compromiso_pago_id IN(3)";
	}
	else if($param_tipo_solicitud == 2)
	{
		// RECIBOS COMPARTIDOS
		$where_tipo_solicitud = " AND cis.tipo_compromiso_pago_id IN(1, 2, 4, 5, 6)";
	}

	$where_not_ids_recibo = "";
	$where_not_ids_recibo_seleccionado = "";

	$html = '';
	$tbody = '';
	$ids_agregar_todos = '';
	$total_monto_programado = 0;

	if($tipo_consulta == '1')
	{
		// INICIO LISTAR LOS RECIBOS QUE YA SE REGISTRARON EN ALGUNA PROGRAMACION DE PAGOS
		
		$query_detalle_programacion = 
		"
			SELECT
				pd.id, pd.cont_ser_pub_programacion_id, pd.cont_local_servicio_publico_id
			FROM cont_ser_pub_programacion_detalle pd
			WHERE pd.status = 1
		";

		$list_query_detalle = $mysqli->query($query_detalle_programacion);

		$ids_programacion_registrado = '';
		$contador_ids = 0;
		
		while ($row = $list_query_detalle->fetch_assoc()) 
		{
			if ($contador_ids > 0) 
			{
				$ids_programacion_registrado .= ',';
			}

			$ids_programacion_registrado .= $row["cont_local_servicio_publico_id"];			
			$contador_ids++;
		}

		// FIN LISTAR LOS RECIBOS QUE YA SE REGISTRARON EN ALGUNA PROGRAMACION DE PAGOS

		if($ids_programacion_registrado != '')
		{
			$where_not_ids_recibo .= " AND sp.id NOT IN(" . $ids_programacion_registrado . ")";
		}

		if ($ids_recibos != '') 
		{
			$where_not_ids_recibo_seleccionado .= " AND sp.id NOT IN(" . $ids_recibos . ")";
		}

		$query = 
		"
			SELECT
				sp.id, sp.id_local, IFNULL(l.cc_id,'0') AS local_centro_costo,
			    l.nombre AS local,
			    sp.inmueble_suministros_id,
			    cis.nro_suministro AS suministro,
			    cis.tipo_compromiso_pago_id,
			    tps.nombre AS compromiso_pago,
			    sp.id_tipo_servicio_publico,
			    tsp.nombre AS tipo_servicio_nombre,
			    cis.tipo_servicio_id AS tipo_servicio_publico,
				cis.nombre_beneficiario,
				cis.nro_cuenta_soles,
				cis.nro_documento_beneficiario,
				td.nombre AS tipo_documento_beneficiario,
			    ea.nombre_comercial AS empresa_agua_nombre_comercial,
				el.nombre_comercial AS empresa_luz_nombre_comercial,
			    sp.periodo_consumo,
			    sp.total_pagar AS total_pagado,
			    sp.estado,
			    esp.nombre as estado_nombre
			FROM cont_local_servicio_publico sp
				INNER JOIN tbl_locales l
				ON sp.id_local = l.id
				INNER JOIN cont_inmueble_suministros cis
				ON sp.inmueble_suministros_id = cis.id
				INNER JOIN cont_inmueble ci
				ON cis.inmueble_id = ci.id
				LEFT JOIN cont_local_servicio_publico_empresas ea
				ON ci.id_empresa_servicio_agua = ea.id
				LEFT JOIN cont_local_servicio_publico_empresas el
				ON ci.id_empresa_servicio_luz = el.id
				LEFT JOIN tbl_tipo_documento td
				ON cis.tipo_documento_beneficiario = td.id
				INNER JOIN cont_contrato cc
				ON ci.contrato_id = cc.contrato_id
				INNER JOIN cont_tipo_pago_servicio tps
				ON cis.tipo_compromiso_pago_id = tps.id
				INNER JOIN cont_tipo_servicio_publico tsp
				ON sp.id_tipo_servicio_publico = tsp.id
				INNER JOIN cont_tipo_estado_servicio_publico AS esp 
				ON sp.estado = esp.id
			WHERE sp.status = 1 AND sp.estado = 2 
				AND l.razon_social_id = '".$param_tipo_empresa."'
				".$where_tipo_solicitud."
				".$where_not_ids_recibo."
				".$where_not_ids_recibo_seleccionado."
				".$where_tipo_servicio."
		";

		$list_query = $mysqli->query($query);

		if($mysqli->error)
		{
			$result["http_code"] = 400;
			$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query;
			echo json_encode($result);
			exit();
		}

		$row_count = $list_query->num_rows;

		if($row_count > 0) 
		{
			$num = 1;

			while ($row = $list_query->fetch_assoc()) 
			{
				$id = $row["id"];
				$local_centro_costo = $row["local_centro_costo"];
				$local = $row["local"];
				$suministro = $row["suministro"];
				$tipo_servicio_nombre = $row["tipo_servicio_nombre"];
				$periodo_consumo = contrato_servicio_publico_tesoreria_item_atencion_nombre_mes($row["periodo_consumo"]);
				$total_pagado = $row["total_pagado"];
				$nombre_beneficiario = $row["nombre_beneficiario"];
				$nro_cuenta_soles = $row["nro_cuenta_soles"];
				$tipo_documento_beneficiario = $row["tipo_documento_beneficiario"];
				$nro_documento_beneficiario = $row["nro_documento_beneficiario"];

				$tipo_servicio_publico = $row["tipo_servicio_publico"];

				$empresa_servicio_publico = "El servicio publico no es Agua ni Luz.";

				if($tipo_servicio_publico == 1)
				{
					// LUZ
					$empresa_servicio_publico = $row["empresa_luz_nombre_comercial"];
				}
				else if($tipo_servicio_publico == 2)
				{
					// AGUA
					$empresa_servicio_publico = $row["empresa_agua_nombre_comercial"];
				}

				$tbody .= '<tr>';
					$tbody .= '<td>'.$num.'</td>';
					$tbody .= '<td>['.$local_centro_costo.'] '.$local.'</td>';
					$tbody .= '<td>'.$suministro.'</td>';
					$tbody .= '<td>'.$empresa_servicio_publico.'</td>';
					$tbody .= '<td>'.$tipo_servicio_nombre.'</td>';
					$tbody .= '<td>'.$periodo_consumo.'</td>';
					$tbody .= '<td>'.$total_pagado.'</td>';
					if($param_tipo_solicitud == 2)
					{
					$tbody .= '<td>'.$nombre_beneficiario.'</td>';
					$tbody .= '<td>'.$tipo_documento_beneficiario.'</td>';
					$tbody .= '<td>'.$nro_documento_beneficiario.'</td>';
					$tbody .= '<td>'.$nro_cuenta_soles.'</td>';
					}
					$tbody .= '<td>';
						$tbody .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Agregar a la programación de pagos" onclick="contrato_servicio_publico_tesoreria_item_atencion_agregar_recibo_a_la_programacion_pagos(' . $row["id"] . ')">';
						$tbody .= '<i class="fa fa-plus"></i>';
						$tbody .= '</a>';
					$tbody .= '</td>';

					$onclick = "ModalCancelar('".$row["id_tipo_servicio_publico"]."', '".$row["id_local"]."', '".$row["periodo_consumo"]."', '".$row["local_centro_costo"]." ".$row["local"]."', '".$row["id"]."')";

					$tbody .= '<td>';
						$tbody .= '<a class="btn btn-warning btn-xs" 
									data-toggle="tooltip" data-placement="top" title="Ver" 
									onclick="ModalCancelar(' . $onclick . ')">';
						$tbody .= '<i class="fa fa-eye"></i>';
						$tbody .= '</a>';
					$tbody .= '</td>';

				$tbody .= '</tr>';

				if ($num == 1) 
				{
					$ids_agregar_todos .= $id;
				} 
				else 
				{
					$ids_agregar_todos .= "," . $id;
				}

				$total_monto_programado += $total_pagado;
				$num += 1;
			}

			$html .= '<table class="table table-bordered" style="font-size: 12px;">';
				$html .= '<thead>';
					
					$html .= '<tr>';
						$html .= '<th colspan="12" style="background-color: #E5E5E5;">';
							
							$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px; text-align: left;">';
							$html .= 'Recibos: Tipo '.$param_tipo_solicitud_nombre;
							$html .= '</div>';
							
							$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
								$html .= '<button type="button" class="btn btn-success btn-xs"  title="Agregar todos" style="width: 100px;" onclick="contrato_servicio_publico_tesoreria_item_atencion_agregar_varios_recibo_a_la_programacion_pagos(' . $ids_agregar_todos . ')">';
								$html .= '<i class="fa fa-plus"></i>';
								$html .= ' Agregar todos';
								$html .= '</button>';
							$html .= '</div>';

						$html .= '</th>';
					$html .= '</tr>';

					$html .= '<tr>';
						$html .= '<th>#</th>';
						$html .= '<th>Local</th>';
						$html .= '<th># Suministro</th>';
						$html .= '<th>Empresa</th>';
						$html .= '<th>Servicio</th>';
						$html .= '<th>Periodo</th>';
						$html .= '<th>Monto</th>';
						if($param_tipo_solicitud == 2)
						{
						$html .= '<th>Nombre Beneficiario</th>';
						$html .= '<th>Tipo Documento</th>';
						$html .= '<th>Número Documento Beneficiario</th>';
						$html .= '<th>Número Cuenta Soles</th>';
						}
						$html .= '<th>Agregar</th>';
						$html .= '<th>Ver</th>';
					$html .= '</tr>';
				$html .= '</thead>';
				
				$html .= '<tbody>';
					$html .= $tbody;
				$html .= '</tbody>';
			$html .= '</table>';
		}
		else if ($row_count == 0)
		{
			// NO EXISTEN DATOS PENDIENTE DE PAGOS
			$html .= '<table class="table table-bordered" style="font-size: 12px;">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<th style="background-color: #E5E5E5;">';
						$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px; text-align: left;">';
							
							$html .= 'Recibos: Tipo '.$param_tipo_solicitud_nombre;

							$html .= '</div>';
						$html .= '</th>';
					$html .= '</tr>';
				$html .= '</thead>';
				
				$html .= '<tbody>';
					$html .= '<tr>';
						$html .= '<td style="text-align: center;">No existen registros</td>';
					$html .= '</tr>';
				$html .= '</tbody>';
			$html .= '</table>';
		}
	}
	else
	{
		$where_not_ids_recibo_seleccionado .= " AND sp.id IN(" . $ids_recibos . ")";

		$query = "
			SELECT
				sp.id, sp.id_local,
				IFNULL(l.cc_id,'0') AS local_centro_costo,
			    l.nombre AS local,
			    sp.inmueble_suministros_id,
			    cis.nro_suministro AS suministro,
			    cis.tipo_compromiso_pago_id,
			    tps.nombre AS compromiso_pago,
			    sp.id_tipo_servicio_publico,
			    tsp.nombre AS tipo_servicio_nombre,
			    cis.tipo_servicio_id AS tipo_servicio_publico,
			    ea.nombre_comercial AS empresa_agua_nombre_comercial,
				el.nombre_comercial AS empresa_luz_nombre_comercial,
			    sp.periodo_consumo,
			    sp.total_pagar AS total_pagado,
			    sp.estado,
			    esp.nombre as estado_nombre
			FROM cont_local_servicio_publico sp
				INNER JOIN tbl_locales l
				ON sp.id_local = l.id
				INNER JOIN cont_inmueble_suministros cis
				ON sp.inmueble_suministros_id = cis.id
				INNER JOIN cont_inmueble ci
				ON cis.inmueble_id = ci.id
				LEFT JOIN cont_local_servicio_publico_empresas ea
				ON ci.id_empresa_servicio_agua = ea.id
				LEFT JOIN cont_local_servicio_publico_empresas el
				ON ci.id_empresa_servicio_luz = el.id
				INNER JOIN cont_tipo_pago_servicio tps
				ON cis.tipo_compromiso_pago_id = tps.id
				INNER JOIN cont_tipo_servicio_publico tsp
				ON sp.id_tipo_servicio_publico = tsp.id
				INNER JOIN cont_tipo_estado_servicio_publico AS esp 
				ON sp.estado = esp.id
			WHERE sp.status = 1 AND sp.estado = 2
				".$where_not_ids_recibo_seleccionado."
		";

		$list_query = $mysqli->query($query);

		if($mysqli->error)
		{
			$result["http_code"] = 400;
			$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query;
			echo json_encode($result);
			exit();
		}

		$row_count = $list_query->num_rows;

		if($row_count > 0) 
		{
			$num = 1;

			while ($row = $list_query->fetch_assoc()) 
			{
				$id = $row["id"];
				$local_centro_costo = $row["local_centro_costo"];
				$local = $row["local"];
				$suministro = $row["suministro"];
				$tipo_servicio_nombre = $row["tipo_servicio_nombre"];
				$periodo_consumo = contrato_servicio_publico_tesoreria_item_atencion_nombre_mes($row["periodo_consumo"]);
				$total_pagado = $row["total_pagado"];

				$tipo_servicio_publico = $row["tipo_servicio_publico"];

				$empresa_servicio_publico = "El servicio publico no es Agua ni Luz.";

				if($tipo_servicio_publico == 1)
				{
					// LUZ
					$empresa_servicio_publico = $row["empresa_luz_nombre_comercial"];
				}
				else if($tipo_servicio_publico == 2)
				{
					// AGUA
					$empresa_servicio_publico = $row["empresa_agua_nombre_comercial"];
				}

				$tbody .= '<tr>';
					$tbody .= '<td>'.$num.'</td>';
					$tbody .= '<td>['.$local_centro_costo.'] '.$local.'</td>';
					$tbody .= '<td>'.$suministro.'</td>';
					$tbody .= '<td>'.$empresa_servicio_publico.'</td>';
					$tbody .= '<td>'.$tipo_servicio_nombre.'</td>';
					$tbody .= '<td>'.$periodo_consumo.'</td>';
					$tbody .= '<td>'.$total_pagado.'</td>';
					$tbody .= '<td>';
						$tbody .= '<a class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Quitar de la programación de pagos" onclick="contrato_servicio_publico_tesoreria_item_atencion_quitar_recibo_a_la_programacion_pagos(' . $row["id"] . ')">';
						$tbody .= '<i class="fa fa-minus"></i>';
						$tbody .= '</a>';
					$tbody .= '</td>';
				$tbody .= '</tr>';

				if($num == 1)
				{
					$ids_agregar_todos .= $id;
				} 
				else 
				{
					$ids_agregar_todos .= "," . $id;
				}

				$total_monto_programado += $total_pagado;
				$num += 1;
			}

			$html .= '<table class="table table-bordered" style="font-size: 12px;">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<th colspan="8" style="background-color: #E5E5E5;">';
							
							$html .= '<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px" style="text-align: left;">';
							$html .= 'Recibos Seleccionados: Tipo '.$param_tipo_solicitud_nombre;
							$html .= '</div>';
							
							$html .= '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">';
								$html .= '<button type="button" class="btn btn-warning btn-xs" title="Quitar todos" style="width: 100px;" onclick="contrato_servicio_publico_tesoreria_item_atencion_quitar_varios_recibo_a_la_programacion_pagos(' . $ids_agregar_todos . ')">';
								$html .= '<i class="fa fa-minus"></i>';
								$html .= ' Quitar todos';
								$html .= '</button>';
							$html .= '</div>';

						$html .= '</th>';
					$html .= '</tr>';

					$html .= '<tr>';
						$html .= '<th>#</th>';
						$html .= '<th>Local</th>';
						$html .= '<th># Suministro</th>';
						$html .= '<th>Empresa</th>';
						$html .= '<th>Servicio</th>';
						$html .= '<th>Periodo</th>';
						$html .= '<th>Monto</th>';
						$html .= '<th>Quitar</th>';
					$html .= '</tr>';
				$html .= '</thead>';
				
				$html .= '<tbody>';
					$html .= $tbody;

					$html .= '<tr>';
						$html .= '<th colspan="8" style="text-align: right; background-color: #E5E5E5;">';
						$html .= '</th>';
					$html .= '</tr>';
					
					$html .= '<tr style="font-size: 13px;">';
						$html .= '<th colspan="7" style="text-align: right;">';
							$html .= 'Total Recibos:';
						$html .= '</th>';
						$html .= '<th style="text-align: right;">';
							$html .= $row_count;
						$html .= '</th>';
					$html .= '</tr>';
					
					$html .= '<tr style="font-size: 13px;">';
						$html .= '<th colspan="7" style="text-align: right;">';
							$html .= 'Total monto:';
						$html .= '</th>';
						$html .= '<th style="text-align: right;">';
							$html .= number_format($total_monto_programado, 2, '.', ',');
						$html .= '</th>';
					$html .= '</tr>';

				$html .= '</tbody>';
			$html .= '</table>';
		}
		else if($row_count == 0)
		{
			// NO EXISTEN DATOS PENDIENTE DE PAGOS
			$html .= '<table class="table table-bordered" style="font-size: 12px;">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<th style="background-color: #E5E5E5;">';
							$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px" style="text-align: left;">';
							$html .= 'Recibos Seleccionados: Tipo '.$param_tipo_solicitud_nombre;
							$html .= '</div>';
						$html .= '</th>';
					$html .= '</tr>';
				$html .= '</thead>';
			
				$html .= '<tbody>';
					$html .= '<tr>';
						$html .= '<td style="text-align: center;">No existen registros</td>';
					$html .= '</tr>';
				$html .= '</tbody>';
			$html .= '</table>';
		}
	}

	if ($row_count >= 0) 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $html;
	}
	else 
	{
		$result["http_code"] = 400;
		$result["result"] = "No hay registros de Asignación por pagar.";
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_item_atencion_guardar_programacion_de_pago") 
{

	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');

	if((int)$user_id > 0)
	{
		$param_tipo_solicitud = $_POST["param_tipo_solicitud"];
		$param_tipo_empresa = $_POST["param_tipo_empresa"];
		$ids_recibos = $_POST["ids_recibos"];
		$data_ids_programacion_guardar = json_decode($ids_recibos);

		
		// INICIO INSERTAR PROGRAMACIÓN
		$query_insert_programacion = 
		"
			INSERT INTO cont_ser_pub_programacion
			(
				tipo_solicitud_id,
				tipo_empresa_id,
				situacion_id,
				se_cargo_comprobante,
				status,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'" . $param_tipo_solicitud . "',
				'" . $param_tipo_empresa . "',
				7,
				0,
				1,
				" . $user_id . ",
				'" . $created_at . "',
				" . $user_id . ",
				'" . $created_at . "'
			)
		";

		$mysqli->query($query_insert_programacion);
		
		$programacion_id = mysqli_insert_id($mysqli);
		
		if($mysqli->error)
		{
			$result["http_code"] = 400;
			$result["result"] = 'Ocurrio un error al guardar: ' . $mysqli->error . $query_insert_programacion;
			echo json_encode($result);
			exit();
		}
		// FIN INSERTAR PROGRAMACIÓN

		// INICIO INSERTAR DETALLE PROGRAMACIÓN
		$array_detalle_programacion_id = [];

		foreach ($data_ids_programacion_guardar as $value_id) 
		{
			$query_insert_detalle_programacion = 
			"
				INSERT INTO cont_ser_pub_programacion_detalle
				(
					cont_ser_pub_programacion_id,
					cont_local_servicio_publico_id,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					" . $programacion_id . ",
					" . $value_id . ",
					1,
					" . $user_id . ",
					'" . $created_at . "',
					" . $user_id . ",
					'" . $created_at . "'
				)";

			$mysqli->query($query_insert_detalle_programacion);
			
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			
			if($mysqli->error)
			{
				$result["http_code"] = 400;
				$result["result"] = 'Ocurrio un error al guardar el detalle: ' . $mysqli->error . $query_insert_detalle_programacion;
				echo json_encode($result);
				exit();

			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN

		$result["http_code"] = 200;
		$result["status"] = "Datos guardados correctamente.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
        $result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_item_atencion_editar_programacion_de_pago") 
{
	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');
	$array_detalle_programacion_id = [];

	if((int) $user_id > 0)
	{
		$programacion_id = $_POST["programacion_id_edit"];
		$ids_recibos = $_POST["ids_recibos"];
		$array_prestamo_detalle_nuevos = json_decode($ids_recibos);

		// INICIO OBTENER DETALLE ACTUALES
		$query_detalle_programacion = 
		"
			SELECT 
			    cont_local_servicio_publico_id
			FROM cont_ser_pub_programacion_detalle
			WHERE status = 1 AND cont_ser_pub_programacion_id = " . $programacion_id . "
		";

		$list_query = $mysqli->query($query_detalle_programacion);

		if($mysqli->error)
		{
			$result["http_code"] = 400;
			$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query_detalle_programacion;
			echo json_encode($result);
			exit();
		}

		$row_count = $list_query->num_rows;

		$array_detalle_actuales = array();

		if ($row_count > 0) 
		{
			while ($row = $list_query->fetch_assoc()) 
			{
				array_push($array_detalle_actuales, $row["cont_local_servicio_publico_id"]);
			}
		}
		// FIN OBTENER DETALLE ACTUALES

		// INICIO INSERTAR DETALLE PROGRAMACIÓN NUEVOS
		$array_nuevos_prestamos = array_diff($array_prestamo_detalle_nuevos, $array_detalle_actuales);

		foreach ($array_nuevos_prestamos as $value_id) 
		{
			$query_insert_detalle_programacion = 
			"
				INSERT INTO cont_ser_pub_programacion_detalle
				(
					cont_ser_pub_programacion_id,
					cont_local_servicio_publico_id,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					" . $programacion_id . ",
					" . $value_id . ",
					1,
					" . $user_id . ",
					'" . $created_at . "',
					" . $user_id . ",
					'" . $created_at . "'
				)";
			
			$mysqli->query($query_insert_detalle_programacion);
			
			$array_detalle_programacion_id[] = mysqli_insert_id($mysqli);
			
			if($mysqli->error)
			{
				$result["http_code"] = 400;
				$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query_insert_detalle_programacion;
				echo json_encode($result);
				exit();
			}
		}
		// FIN INSERTAR DETALLE PROGRAMACIÓN NUEVOS
		
		// INICIO ELIMINAR DETALLE ANTERIORES DE LA PROGRAMACION

		$array_prestamo_debaja = array_diff($array_detalle_actuales, $array_prestamo_detalle_nuevos);
		
		foreach ($array_prestamo_debaja as $value_id_de_baja) 
		{
			$sql_prestamo_de_baja = 
			"
				DELETE FROM cont_ser_pub_programacion_detalle
				WHERE cont_ser_pub_programacion_id = $programacion_id 
					AND cont_local_servicio_publico_id = $value_id_de_baja
			";
			
			$mysqli->query($sql_prestamo_de_baja);
			
			if($mysqli->error)
			{
				$result["http_code"] = 400;
				$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $sql_prestamo_de_baja;
				echo json_encode($result);
				exit();
			}
		}

		// FIN ELIMINAR DETALLE ANTERIORES DE LA PROGRAMACION

		$result["http_code"] = 200;
		$result["status"] = "Datos editados correctamente.";
		$result["result"] = $array_detalle_programacion_id;
	}
	else
	{
		$result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_item_detalle_programacion_guardar_comprobante_pago") 
{
	$error = '';

	$user_id = $login?$login['id']:null;
	
	if((int)$user_id > 0)
	{
		$tesoreria_fecha_comprobante_pago = $_POST['contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago'];
		$tesoreria_fecha_comprobante_pago = date("Y-m-d", strtotime($tesoreria_fecha_comprobante_pago));

		$servicio_publico_programacion_id = $_POST['servicio_publico_programacion_id'];

		if(isset($_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago']))
		{
			// INICIO: INSERTAMOS EL FILE EN LA TABLA cont_ser_pub_programacion_files

			$path = "/var/www/html/files_bucket/contratos/servicios_publicos/programacion/comprobante_pago/";
			$download = "/files_bucket/contratos/servicios_publicos/programacion/comprobante_pago/";

			if (!is_dir($path))
			{
				mkdir($path, 0777, true);
			}

			$cant = 1;

			for ($i=0; $i < count($_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago']['name']); $i++)
			{
				$file_name = $_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago']['name'][$i];
				$file_tmp = $_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago']['tmp_name'][$i];
				$file_size = $_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago']['size'][$i];
				$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
				

				$nombreFileUpload = "id_".$servicio_publico_programacion_id."_item_".$cant."_imagen_fecha_".date('YmdHis').".".$file_extension;

				$nombreDownload = $download.$nombreFileUpload;
				move_uploaded_file($file_tmp, $path. $nombreFileUpload);

				$query_insert_file = "INSERT INTO cont_ser_pub_programacion_files
									(
										cont_ser_pub_programacion_id,
										imagen,
										extension,
										size,
										ruta,
									    download,
										status,
										user_created_id,
										created_at,
										user_updated_id,
										updated_at
									) 
									VALUES 
									(
										'".$servicio_publico_programacion_id."',
										'".$nombreFileUpload."',
										'".$file_extension."',
										'".$file_size."',
										'".$path."',
									    '".$nombreDownload."',
									    1,
										'".$login["id"]."', 
										'".date('Y-m-d H:i:s')."',
										'".$login["id"]."', 
										'".date('Y-m-d H:i:s')."'
									)";

				$mysqli->query($query_insert_file);

				if($mysqli->error)
				{
					$error .= $mysqli->error;

					$result["http_code"] = 400;
					$result["status"] = "Ocurrio un error.";
					$result["error"] = $mysqli->error;

					echo json_encode($result);
					exit();
				}

				$cant++;
			}

			// FIN: INSERTAMOS EL FILE EN LA TABLA cont_ser_pub_programacion_files

			// INICIO: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA cont_ser_pub_programacion

			$query_update = 
			"
				UPDATE cont_ser_pub_programacion 
					SET fecha_carga_comprobante = '".date('Y-m-d H:i:s')."',
					    user_id_carga_comprobante = '".$login["id"]."',
					    se_cargo_comprobante = 1,
					    fecha_comprobante = '".$tesoreria_fecha_comprobante_pago."',
					    situacion_id = 8
				WHERE id = '".$servicio_publico_programacion_id."'
			";

			$mysqli->query($query_update);

			if($mysqli->error)
			{
				$error .= $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

			// FIN: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA cont_ser_pub_programacion
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = "Por favor seleccionar un archivo.";

			echo json_encode($result);
			exit();
		}

		// INICIO: ACTUALIZAR LA SITUACION DEL RECIBO COMO PAGO REALIZADO

		$select_usuarios_actualizar_estado = 
		"
			SELECT
				pd.id,
			    pd.cont_ser_pub_programacion_id,
			    pd.cont_local_servicio_publico_id,
				sp.inmueble_suministros_id,
				sp.periodo_consumo,
				sp.total_pagar AS total_pagado,
				sp.estado
			FROM cont_ser_pub_programacion p
				INNER JOIN cont_ser_pub_programacion_detalle pd
				ON p.id = pd.cont_ser_pub_programacion_id
				INNER JOIN cont_local_servicio_publico sp
				ON pd.cont_local_servicio_publico_id = sp.id
			WHERE p.id = '".$servicio_publico_programacion_id."' AND p.situacion_id = 8 
				AND pd.status = 1 AND sp.estado = 2
		";

		$sel_query_usuarios_actualizar_pago = $mysqli->query($select_usuarios_actualizar_estado);

		$row_count = $sel_query_usuarios_actualizar_pago->num_rows;

		$fecha_cancelacion_tesoreria = date("Y-m-d H:i:s");

		if ($row_count > 0)
		{
			while($sel = $sel_query_usuarios_actualizar_pago->fetch_assoc())
			{
				$cont_local_servicio_publico_id = $sel["cont_local_servicio_publico_id"];
				
				$query_update = 
				"
					UPDATE cont_local_servicio_publico 
						SET estado = 3,
							fecha_cancelacion_tesoreria = '".$fecha_cancelacion_tesoreria."'
					WHERE id = '".$cont_local_servicio_publico_id."'
				";

				$mysqli->query($query_update);

				if($mysqli->error)
				{
					$error .= $mysqli->error;
				}
			}
		}

		// FIN: ACTUALIZAR LA SITUACION DEL RECIBO COMO PAGO REALIZADO
		

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos guardados.";
			$result["error"] = $error;
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante_pago") 
{
	$error = '';

	$tesoreria_fecha_comprobante_pago_edit = $_POST['tesoreria_fecha_comprobante_pago_edit'];
	$tesoreria_fecha_comprobante_pago_edit = date("Y-m-d", strtotime($tesoreria_fecha_comprobante_pago_edit));

	$tesoreria_motivo_comprobante_pago_edit = $_POST['tesoreria_motivo_comprobante_pago_edit'];
	$servicio_publico_programacion_id = $_POST['servicio_publico_programacion_id'];

	if(isset($_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit']))
	{
		// INICIO: ELIMINAMOS LOS REGISTROS EXISTENTES DE LA BD

		$query_delete = 
		"
			DELETE FROM cont_ser_pub_programacion_files 
			WHERE cont_ser_pub_programacion_id = '".$servicio_publico_programacion_id."'
		";

		$mysqli->query($query_delete);

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		// FIN: ELIMINAMOS LOS REGISTROS EXISTENTES DE LA BD

		// INICIO: INSERTAMOS EL FILE EN LA TABLA cont_ser_pub_programacion_files

		$path = "/var/www/html/files_bucket/contratos/servicios_publicos/programacion/comprobante_pago/";
		$download = "/files_bucket/contratos/servicios_publicos/programacion/comprobante_pago/";

		if (!is_dir($path)) 
		{
			mkdir($path, 0777, true);
		}

		$cant = 1;

		for ($i=0; $i < count($_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit']['name']); $i++)
		{
			$file_name = $_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit']['name'][$i];
			$file_tmp = $_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit']['tmp_name'][$i];
			$file_size = $_FILES['contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit']['size'][$i];
			$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			

			$nombreFileUpload = "id_".$servicio_publico_programacion_id."_item_".$cant."_imagen_fecha_".date('YmdHis'). ".".$file_extension;
			$nombreDownload = $download.$nombreFileUpload;
			move_uploaded_file($file_tmp, $path. $nombreFileUpload);

			$query_insert_file = "INSERT INTO cont_ser_pub_programacion_files
									(
										cont_ser_pub_programacion_id,
										imagen,
										extension,
										size,
										ruta,
									    download,
										status,
										user_created_id,
										created_at,
										user_updated_id,
										updated_at
									) 
									VALUES 
									(
										'".$servicio_publico_programacion_id."',
										'".$nombreFileUpload."',
										'".$file_extension."',
										'".$file_size."',
										'".$path."',
									    '".$nombreDownload."',
									    1,
										'".$login["id"]."', 
										'".date('Y-m-d H:i:s')."',
										'".$login["id"]."', 
										'".date('Y-m-d H:i:s')."'
									)";

			$mysqli->query($query_insert_file);

			if($mysqli->error)
			{
				$error .= $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

			$cant++;
		}

		// FIN: INSERTAMOS EL FILE EN LA TABLA cont_ser_pub_programacion_files

		// INICIO: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA cont_ser_pub_programacion

		$query_update = 
		"
			UPDATE cont_ser_pub_programacion 
				SET fecha_carga_comprobante = '".date('Y-m-d H:i:s')."',
				    user_id_carga_comprobante = '".$login["id"]."',
				    se_cargo_comprobante = 1,
				    fecha_comprobante = '".$tesoreria_fecha_comprobante_pago_edit."',
				    comentario_edicion_comprobante = '".$tesoreria_motivo_comprobante_pago_edit."',
				    situacion_id = 8
			WHERE id = '".$servicio_publico_programacion_id."'
		";

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		// FIN: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA cont_ser_pub_programacion
	}
	else
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = "Por favor seleccionar un archivo.";

		echo json_encode($result);
		exit();
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
	}
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = $error;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_item_detalle_plantilla_totales_concar_excel")
{
	
	$razon_social_igh = getParameterGeneral('razon_social_igh');

	$programacion_id = $_POST['programacion_id'];
	$num_comprobante = $_POST['programacion_num_comprobante'];
	$num_movimiento = $_POST['programacion_num_movimiento'];
	$servicio = $_POST['programacion_servicio'];

	$query_update_num_comprobante = 
	"
		UPDATE cont_ser_pub_programacion 
			SET numero_comprobante_concar = '".$num_comprobante."'
		WHERE id = '".$programacion_id."'
	";

	$mysqli->query($query_update_num_comprobante);
	
	$importe_dolares = 0;
	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	//-------------------  Filtro de tipo de servicio
	if($servicio != 0){
		$where_tipo_servicio .= " AND sp.id_tipo_servicio_publico = ".$servicio. " ";
		$where_tipo_servicio_detalle .= " AND spi.id_tipo_servicio_publico = ".$servicio. " ";
	}
	
	//-------------------

	$param_cuenta_contable = getParameterGeneral('servicio_publico_tesoreria_cuenta_contable_debe');
	$param_codigo_anexo = getParameterGeneral('servicio_publico_tesoreria_cuenta_contable_codigo_anexo');
	$param_codigo_area_agua = getParameterGeneral('servicio_publico_tesoreria_codigo_area_agua');
	$param_codigo_area_luz = getParameterGeneral('servicio_publico_tesoreria_codigo_area_luz');

	$query = 
	"
		SELECT
			p.id AS programacion_id,
			rs.id AS razon_social_id,
			rs.subdiario AS sub_diario,
			l.cc_id,
			s.nro_suministro,
			sp.serie,
			sp.numero_recibo,
			sp.fecha_emision AS fecha_emision_sp,
			sp.fecha_vencimiento AS fecha_vencimiento_sp,
			sp.periodo_consumo,
			rs.subdiario AS sub_diario,
		    p.numero_comprobante_concar,
		    p.fecha_comprobante,
		    'MN' AS codigo_moneda,
		    l.nombre AS glosa_principal,
		    tc.monto_venta AS tipo_cambio,
		    'V' AS tipo_conversion,
			'S' AS flag_conversion_moneda,
		    tc.fecha AS fecha_tipo_cambio,
		    '{$param_cuenta_contable}' AS cuenta_contable,
		    '{$param_codigo_anexo}' AS codigo_anexo,
		    s.tipo_servicio_id AS tipo_servicio_publico,
		    ea.ruc AS empresa_agua_ruc,
			el.ruc AS empresa_luz_ruc,
			'' AS codigo_centro_costo,
		    '' AS debe_haber,
		    sp.total_pagar AS importe_original,
			'' AS tipo_documento,
		    sp.numero_recibo AS num_documento,
			pd.num_transferencia_banco,
		    p.fecha_comprobante AS fecha_documento,
			p.fecha_comprobante AS fecha_vencimiento,
		    '{$param_codigo_area_agua}' AS codigo_area_agua,
			'{$param_codigo_area_luz}' AS codigo_area_luz,
		    '' AS glosa_detalle,
		    '' AS codigo_anexo_auxiliar,
			'' AS medio_pago,
			'' AS tipo_documento_referencia,
			'' AS num_documento_referencia,
			'' AS fecha_documento_referencia,
			'' AS maquina_registradora_tipo_documento,
			'' AS base_imponible_documento_referencia,
			'' AS igv_documento_provision,
			'' AS tipo_referencia_estado,
			'' AS num_serie_caja_registradora,
			'' AS fecha_operacion,
			'' AS tipo_tasa,
			'' AS tasa_detraccion_percepcion,
			'' AS importe_base_detraccion_percepcion_dolares,
			'' AS importe_base_detraccion_percepcion_soles,
			'' AS tipo_cambio_para_f,
			'' AS importe_igv_sin_derecho_credito_fiscal,
			'' AS tasa_igv
		FROM cont_ser_pub_programacion p
			INNER JOIN cont_ser_pub_programacion_detalle pd
			ON p.id = pd.cont_ser_pub_programacion_id
			INNER JOIN tbl_razon_social rs
			ON p.tipo_empresa_id = rs.id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
			INNER JOIN cont_local_servicio_publico sp
			ON pd.cont_local_servicio_publico_id = sp.id
			INNER JOIN tbl_locales l
			ON sp.id_local = l.id
			INNER JOIN cont_inmueble_suministros s
			ON sp.inmueble_suministros_id = s.id
			INNER JOIN cont_inmueble i
			ON s.inmueble_id = i.id
			LEFT JOIN cont_local_servicio_publico_empresas ea
			ON i.id_empresa_servicio_agua = ea.id
			LEFT JOIN cont_local_servicio_publico_empresas el
			ON i.id_empresa_servicio_luz = el.id
		WHERE p.id = {$programacion_id} AND pd.status = 1
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/*'); //obtenemos todos los nombres de los ficheros
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

	$tituloReporte = "Reporte Concar";

	$titulosColumnas = array('Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 'Importe Base Detracción/Percepción Soles', 'Tipo Cambio para F', 'Importe de IGV sin derecho credito fiscal', 'Tasa IGV');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B1', $titulosColumnas[0])	//Titulo de las columnas
			    ->setCellValue('C1', $titulosColumnas[1])
			    ->setCellValue('D1', $titulosColumnas[2])
			    ->setCellValue('E1', $titulosColumnas[3])
			    ->setCellValue('F1', $titulosColumnas[4])
			    ->setCellValue('G1', $titulosColumnas[5])
			    ->setCellValue('H1', $titulosColumnas[6])
			    ->setCellValue('I1', $titulosColumnas[7])
			    ->setCellValue('J1', $titulosColumnas[8])
			    ->setCellValue('K1', $titulosColumnas[9])
			    ->setCellValue('L1', $titulosColumnas[10])
			    ->setCellValue('M1', $titulosColumnas[11])
			    ->setCellValue('N1', $titulosColumnas[12])
			    ->setCellValue('O1', $titulosColumnas[13])
			    ->setCellValue('P1', $titulosColumnas[14])
			    ->setCellValue('Q1', $titulosColumnas[15])
			    ->setCellValue('R1', $titulosColumnas[16])
			    ->setCellValue('S1', $titulosColumnas[17])
			    ->setCellValue('T1', $titulosColumnas[18])
			    ->setCellValue('U1', $titulosColumnas[19])
			    ->setCellValue('V1', $titulosColumnas[20])
			    ->setCellValue('W1', $titulosColumnas[21])
			    ->setCellValue('X1', $titulosColumnas[22])
			    ->setCellValue('Y1', $titulosColumnas[23])
			    ->setCellValue('Z1', $titulosColumnas[24])
			    ->setCellValue('AA1', $titulosColumnas[25])
			    ->setCellValue('AB1', $titulosColumnas[26])
			    ->setCellValue('AC1', $titulosColumnas[27])
			    ->setCellValue('AD1', $titulosColumnas[28])
			    ->setCellValue('AE1', $titulosColumnas[29])
			    ->setCellValue('AF1', $titulosColumnas[30])
			    ->setCellValue('AG1', $titulosColumnas[31])
			    ->setCellValue('AH1', $titulosColumnas[32])
			    ->setCellValue('AI1', $titulosColumnas[33])
			    ->setCellValue('AJ1', $titulosColumnas[34])
			    ->setCellValue('AK1', $titulosColumnas[35])
			    ->setCellValue('AL1', $titulosColumnas[36])
			    ->setCellValue('AM1', $titulosColumnas[37])
			    ->setCellValue('AN1', $titulosColumnas[38])
			    ->setCellValue('AO1', $titulosColumnas[39]);

	//Se agregan los datos a la lista del reporte
	
	$i = 4; //Numero de fila donde se va a comenzar a rellenar
	while ($fila = $list_query->fetch_array()) 
	{
		if(is_null($fila['tipo_cambio']) OR empty($fila['tipo_cambio']))
		{
			echo json_encode(array(
				"ruta_archivo" => "No existe el tipo de cambio de la fecha ".$fila['fecha_comprobante'],
				"estado_archivo" => 0
			));
			exit;
		}

		$fecha_anio = date('Y', strtotime($fila['fecha_comprobante']));
		$fecha_mes = date('m', strtotime($fila['fecha_comprobante']));

		$importe_dolares = $fila['importe_original'] / $fila['tipo_cambio'];

		
		$tipo_servicio_publico = $fila['tipo_servicio_publico'];

		if($tipo_servicio_publico == 1)
		{
			// LUZ
			$empresa_servicio_publico_ruc = $fila['empresa_luz_ruc'];
			$codigo_area = $fila['codigo_area_luz'];
			$nombre_servicio = "LUZ";

		}
		else if($tipo_servicio_publico == 2)
		{
			// AGUA
			$empresa_servicio_publico_ruc = $fila['empresa_agua_ruc'];
			$codigo_area = $fila['codigo_area_agua'];
			$nombre_servicio = "AGUA";
		}
		else
		{
			echo json_encode(array(
				"ruta_archivo" => "El servicio publico no es Agua ni Luz",
				"estado_archivo" => 0
			));
			exit;
		}

		if($fila['razon_social_id'] == $razon_social_igh)
		{
			$glosa_principal_igh = "PAGO SERV, ".$nombre_servicio;
			$glosa_principal = substr($glosa_principal_igh, 0, 40);
			
			setlocale(LC_TIME, 'spanish');
			$periodo = 	contrato_servicio_publico_tesoreria_item_atencion_nombre_resumen_mes($fila['periodo_consumo']);
			$glosa_detalle_igh = $fila['cc_id'] . " SUM " . $fila['nro_suministro'] . " " . $periodo;
			$glosa_detalle=substr($glosa_detalle_igh, 0, 30);
			$fecha_documento = date('d/m/Y', strtotime($fila['fecha_emision_sp']));
			$fecha_vencimiento = date('d/m/Y', strtotime($fila['fecha_vencimiento_sp']));
			$medio_pago = substr($fila['medio_pago'], 0, 8);
		}
		else
		{
			$glosa_principal=substr($fila['glosa_principal'], 0, 40);
			$glosa_detalle =substr($fila['glosa_principal'], 0, 30);
			$medio_pago =substr($fila['medio_pago'], 0, 8);
			$fecha_documento = date('d/m/Y', strtotime($fila['fecha_documento']));
			$fecha_vencimiento = date('d/m/Y', strtotime($fila['fecha_vencimiento']));
		}
		
		$num_documento = $fila['serie']."-".$fila['num_documento'];
		$num_documento = substr($num_documento, 0, 20);
		
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
			->setCellValue('C'.$i, substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6))
			->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
			->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
			->setCellValue('F'.$i, $glosa_principal)
			->setCellValue('G'.$i, $fila['tipo_cambio'])
			->setCellValue('H'.$i, $fila['tipo_conversion'])
			->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
			->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
			->setCellValue('K'.$i, substr($fila['cuenta_contable'], 0, 12))
			->setCellValue('L'.$i, substr($empresa_servicio_publico_ruc, 0, 18))
			->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
			->setCellValue('N'.$i, substr('D', 0, 1))
			->setCellValue('O'.$i, $fila['importe_original'])
			->setCellValue('P'.$i, $importe_dolares)
			->setCellValue('Q'.$i, $fila['importe_original'])
			->setCellValue('R'.$i, substr('RC', 0, 2))
			->setCellValue('S'.$i, $num_documento)
			->setCellValue('T'.$i, $fecha_documento)
			->setCellValue('U'.$i, $fecha_vencimiento)
			->setCellValue('V'.$i, substr('', 0, 3))
			->setCellValue('W'.$i, $glosa_detalle)
			->setCellValue('X'.$i, substr($fila['codigo_anexo_auxiliar'], 0, 18))
			->setCellValue('Y'.$i, $medio_pago)
			->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
			->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
			->setCellValue('AB'.$i, empty($fila['fecha_documento_referencia'])?'':date('d/m/Y', strtotime($fila['fecha_documento_referencia'])))
			->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
			->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
			->setCellValue('AE'.$i, $fila['igv_documento_provision'])
			->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
			->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
			->setCellValue('AH'.$i, $fila['fecha_operacion'])
			->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
			->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
			->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
			->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
			->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
			->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
			->setCellValue('AO'.$i, $fila['tasa_igv']);

		$i++;

		$razon_social_registro = $fila['razon_social_id'];


		//		Consulta de Cuenta contable IGH
			$proceso_pago_servicios_tesoreria = "PAGO SERVICIOS";
			$selectQuery = "SELECT num_cuenta_contable 
							FROM cont_num_cuenta nc
							LEFT JOIN cont_num_cuenta_proceso ncp ON ncp.id =nc.cont_num_cuenta_proceso_id
							WHERE ncp.nombre= ? AND nc.razon_social_id = ?";

			$selectStmt = $mysqli->prepare($selectQuery);
			$selectStmt->bind_param("si", $proceso_pago_servicios_tesoreria,$razon_social_registro);
			$selectStmt->execute();
			$selectStmt->store_result();

			if ($selectStmt->num_rows > 0) {
				$selectStmt->bind_result($cuenta_contable);
				$selectStmt->fetch();
				$cod_anexo = $cuenta_contable;
			}else{
				$cuenta_contable = "No existe cuenta contable";
			}



		if($razon_social_registro == $razon_social_igh){
			$glosa_principal_igh = "PAGO SERV, ".$nombre_servicio;
			$glosa_principal_sub=substr($glosa_principal_igh, 0, 40);
			
			setlocale(LC_TIME, 'spanish');
			$periodo = 	contrato_servicio_publico_tesoreria_item_atencion_nombre_resumen_mes($fila['periodo_consumo']);
			$glosa_detalle_igh = $fila['cc_id'] . " SUM " . $fila['nro_suministro'] . " " . $periodo;
			$glosa_detalle=substr($glosa_detalle_igh, 0, 30);

			$num_documento_igh = $fila['serie']."-".$fila['numero_recibo'];
			$medio_pago = substr('003', 0, 8);

			$fecha_documento_sub = date('d/m/Y', strtotime($fila['fecha_emision_sp']));
			$fecha_vencimiento_sub = date('d/m/Y', strtotime($fila['fecha_vencimiento_sp']));

			$num_cuenta_contable = $cuenta_contable;


		}else{
			$glosa_principal_sub=substr($fila['glosa_principal'], 0, 40);
			$glosa_detalle_sub=substr($fila['glosa_detalle'], 0, 30);
			$medio_pago =substr('001', 0, 8);
			$fecha_documento_sub = date('d/m/Y', strtotime($fila['fecha_documento']));
			$fecha_vencimiento_sub = date('d/m/Y', strtotime($fila['fecha_vencimiento']));

			$num_cuenta_contable = substr($fila['codigo_anexo'], 0, 12);
			
		}

		$glosa_detalle_sub=substr($fila['glosa_detalle'], 0, 30);

	    $objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
		->setCellValue('C'.$i, substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6))
		->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
		->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
		->setCellValue('F'.$i, $glosa_principal)
		->setCellValue('G'.$i, $fila['tipo_cambio'])
		->setCellValue('H'.$i, $fila['tipo_conversion'])
		->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
		->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
		->setCellValue('K'.$i, $num_cuenta_contable)
		->setCellValue('L'.$i, $num_cuenta_contable)
		->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
		->setCellValue('N'.$i, substr('H', 0, 1))
		->setCellValue('O'.$i, $fila['importe_original'])
		->setCellValue('P'.$i, $importe_dolares)
		->setCellValue('Q'.$i, $fila['importe_original'])
		->setCellValue('R'.$i, substr('TR', 0, 2))
		->setCellValue('S'.$i, substr($fila['num_transferencia_banco'], 0, 20))
		->setCellValue('T'.$i, $fecha_documento_sub)
		->setCellValue('U'.$i, $fecha_vencimiento_sub)
		->setCellValue('V'.$i, substr($codigo_area, 0, 3))
		->setCellValue('W'.$i, $glosa_detalle_sub)
		->setCellValue('X'.$i, substr($fila['codigo_anexo_auxiliar'], 0, 18))
		->setCellValue('Y'.$i, $medio_pago)
		->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
		->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
		->setCellValue('AB'.$i, empty($fila['fecha_documento_referencia'])?'':date('d/m/Y', strtotime($fila['fecha_documento_referencia'])))
		->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
		->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
		->setCellValue('AE'.$i, $fila['igv_documento_provision'])
		->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
		->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
		->setCellValue('AH'.$i, $fila['fecha_operacion'])
		->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
		->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
		->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
		->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
		->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
		->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
		->setCellValue('AO'.$i, $fila['tasa_igv']);

		$i++;

	}



	$estiloColoFondoAmarilloOscuro = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => 'ffc000')
	  )
	);
	  
	$estiloTituloColumnas = array(
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
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));


	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(57);



	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloColoFondoAmarilloOscuro);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloColoFondoAmarilloOscuro);

	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:AN".($i-1));

	$objPHPExcel->getActiveSheet()->getStyle('O4:O'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('P4:P'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('Q4:Q'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Plantilla Concar Servicio Publico.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/Plantilla Concar Servicio Publico.xls';
	$excel_path_download = '/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/Plantilla Concar Servicio Publico.xls';
	
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


if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_item_detalle_plantilla_compartidos_concar_excel")
{
	$razon_social_igh = getParameterGeneral('razon_social_igh');

	$programacion_id = $_POST['programacion_id'];
	$num_comprobante = $_POST['programacion_num_comprobante'];
	$num_movimiento = $_POST['programacion_num_movimiento'];
	$servicio = $_POST['programacion_servicio'];


	$query_update_num_comprobante = 
	"
		UPDATE cont_ser_pub_programacion 
			SET numero_comprobante_concar = '".$num_comprobante."'
		WHERE id = '".$programacion_id."'
	";

	$mysqli->query($query_update_num_comprobante);
	
	$query_todos = "";

	$importe_dolares = 0;
	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";
	$where_tipo_servicio ="";
	$where_tipo_servicio_detalle ="";

	//-------------------  Filtro de tipo de servicio
    if($servicio != 0){
        $where_tipo_servicio .= " AND sp.id_tipo_servicio_publico = ".$servicio. " ";
		$where_tipo_servicio_detalle .= " AND spi.id_tipo_servicio_publico = ".$servicio. " ";
	}

	//-------------------
	
	$param_codigo_area_agua = getParameterGeneral('servicio_publico_tesoreria_codigo_area_agua');
	$param_codigo_area_luz = getParameterGeneral('servicio_publico_tesoreria_codigo_area_luz');
	$param_cuenta_contable = getParameterGeneral('servicio_publico_tesoreria_cuenta_contable_debe');
	$param_codigo_anexo = getParameterGeneral('servicio_publico_tesoreria_cuenta_contable_codigo_anexo');
	
	$param_glosa_principal_sub_total = getParameterGeneral('servicio_publico_tesoreria_compartidos_glosa_principal');
	
	$query = 
	"
		SELECT
			p.id AS programacion_id,
			rs.id AS razon_social_id,
			s.nro_suministro,
			sp.serie,
			sp.numero_recibo,
			sp.periodo_consumo,
			rs.subdiario AS sub_diario,
		    p.numero_comprobante_concar,
		    p.fecha_comprobante,
		    'MN' AS codigo_moneda,
			l.cc_id,
		    l.nombre AS glosa_principal,
		    tc.monto_venta AS tipo_cambio,
		    'V' AS tipo_conversion,
			'S' AS flag_conversion_moneda,
		    tc.fecha AS fecha_tipo_cambio,
		    '{$param_cuenta_contable}' AS cuenta_contable,
		    '{$param_codigo_anexo}' AS codigo_anexo,
		    s.tipo_servicio_id AS tipo_servicio_publico,
		    ea.ruc AS empresa_agua_ruc,
			el.ruc AS empresa_luz_ruc,
			'' AS codigo_centro_costo,
		    '' AS debe_haber,
		    sp.total_pagar AS importe_original,
			'' AS tipo_documento,
		    sp.numero_recibo AS num_documento,
			pd.num_transferencia_banco,
			sp.total_pagar AS importe_original,
		    p.fecha_comprobante AS fecha_documento,
			p.fecha_comprobante AS fecha_vencimiento,
		    sp.fecha_emision AS fecha_emision_sp,
			sp.fecha_vencimiento AS fecha_vencimiento_sp,
		    '' AS codigo_area_agua,
			'' AS codigo_area_luz,
		    '' AS glosa_detalle,
		    '' AS codigo_anexo_auxiliar,
			'' AS medio_pago,
			'' AS tipo_documento_referencia,
			'' AS num_documento_referencia,
			'' AS fecha_documento_referencia,
			'' AS maquina_registradora_tipo_documento,
			'' AS base_imponible_documento_referencia,
			'' AS igv_documento_provision,
			'' AS tipo_referencia_estado,
			'' AS num_serie_caja_registradora,
			'' AS fecha_operacion,
			'' AS tipo_tasa,
			'' AS tasa_detraccion_percepcion,
			'' AS importe_base_detraccion_percepcion_dolares,
			'' AS importe_base_detraccion_percepcion_soles,
			'' AS tipo_cambio_para_f,
			'' AS importe_igv_sin_derecho_credito_fiscal,
			'' AS tasa_igv
		FROM cont_ser_pub_programacion p
			INNER JOIN cont_ser_pub_programacion_detalle pd
			ON p.id = pd.cont_ser_pub_programacion_id
			INNER JOIN tbl_razon_social rs
			ON p.tipo_empresa_id = rs.id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
			INNER JOIN cont_local_servicio_publico sp
			ON pd.cont_local_servicio_publico_id = sp.id
			INNER JOIN tbl_locales l
			ON sp.id_local = l.id
			INNER JOIN cont_inmueble_suministros s
			ON sp.inmueble_suministros_id = s.id
			INNER JOIN cont_inmueble i
			ON s.inmueble_id = i.id
			LEFT JOIN cont_local_servicio_publico_empresas ea
			ON i.id_empresa_servicio_agua = ea.id
			LEFT JOIN cont_local_servicio_publico_empresas el
			ON i.id_empresa_servicio_luz = el.id
		WHERE p.id = {$programacion_id} AND pd.status = 1
		$where_tipo_servicio 
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/*'); //obtenemos todos los nombres de los ficheros
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

	$tituloReporte = "Reporte Concar";

	$titulosColumnas = array('Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 'Importe Base Detracción/Percepción Soles', 'Tipo Cambio para F', 'Importe de IGV sin derecho credito fiscal', 'Tasa IGV');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B1', $titulosColumnas[0])	//Titulo de las columnas
			    ->setCellValue('C1', $titulosColumnas[1])
			    ->setCellValue('D1', $titulosColumnas[2])
			    ->setCellValue('E1', $titulosColumnas[3])
			    ->setCellValue('F1', $titulosColumnas[4])
			    ->setCellValue('G1', $titulosColumnas[5])
			    ->setCellValue('H1', $titulosColumnas[6])
			    ->setCellValue('I1', $titulosColumnas[7])
			    ->setCellValue('J1', $titulosColumnas[8])
			    ->setCellValue('K1', $titulosColumnas[9])
			    ->setCellValue('L1', $titulosColumnas[10])
			    ->setCellValue('M1', $titulosColumnas[11])
			    ->setCellValue('N1', $titulosColumnas[12])
			    ->setCellValue('O1', $titulosColumnas[13])
			    ->setCellValue('P1', $titulosColumnas[14])
			    ->setCellValue('Q1', $titulosColumnas[15])
			    ->setCellValue('R1', $titulosColumnas[16])
			    ->setCellValue('S1', $titulosColumnas[17])
			    ->setCellValue('T1', $titulosColumnas[18])
			    ->setCellValue('U1', $titulosColumnas[19])
			    ->setCellValue('V1', $titulosColumnas[20])
			    ->setCellValue('W1', $titulosColumnas[21])
			    ->setCellValue('X1', $titulosColumnas[22])
			    ->setCellValue('Y1', $titulosColumnas[23])
			    ->setCellValue('Z1', $titulosColumnas[24])
			    ->setCellValue('AA1', $titulosColumnas[25])
			    ->setCellValue('AB1', $titulosColumnas[26])
			    ->setCellValue('AC1', $titulosColumnas[27])
			    ->setCellValue('AD1', $titulosColumnas[28])
			    ->setCellValue('AE1', $titulosColumnas[29])
			    ->setCellValue('AF1', $titulosColumnas[30])
			    ->setCellValue('AG1', $titulosColumnas[31])
			    ->setCellValue('AH1', $titulosColumnas[32])
			    ->setCellValue('AI1', $titulosColumnas[33])
			    ->setCellValue('AJ1', $titulosColumnas[34])
			    ->setCellValue('AK1', $titulosColumnas[35])
			    ->setCellValue('AL1', $titulosColumnas[36])
			    ->setCellValue('AM1', $titulosColumnas[37])
			    ->setCellValue('AN1', $titulosColumnas[38])
			    ->setCellValue('AO1', $titulosColumnas[39]);

	//Se agregan los datos a la lista del reporte
	
	$i = 4; //Numero de fila donde se va a comenzar a rellenar
	while ($fila = $list_query->fetch_array())
	{
		if(is_null($fila['tipo_cambio']) OR empty($fila['tipo_cambio']))
		{
			echo json_encode(array(
				"ruta_archivo" => "No existe el tipo de cambio de la fecha ".$fila['fecha_comprobante'],
				"estado_archivo" => 0
			));
			exit;
		}

		$fecha_anio = date('Y', strtotime($fila['fecha_comprobante']));
		$fecha_mes = date('m', strtotime($fila['fecha_comprobante']));

		$importe_dolares = $fila['importe_original'] / $fila['tipo_cambio'];

		
		$tipo_servicio_publico = $fila['tipo_servicio_publico'];

		if($tipo_servicio_publico == 1)
		{
			// LUZ
			$empresa_servicio_publico_ruc = $fila['empresa_luz_ruc'];
			$codigo_area = $fila['codigo_area_luz'];
			$nombre_servicio = "LUZ";

		}
		else if($tipo_servicio_publico == 2)
		{
			// AGUA
			$empresa_servicio_publico_ruc = $fila['empresa_agua_ruc'];
			$codigo_area = $fila['codigo_area_agua'];
			$nombre_servicio = "AGUA";

		}
		else
		{
			echo json_encode(array(
				"ruta_archivo" => "El servicio publico no es Agua ni Luz",
				"estado_archivo" => 0
			));
			exit;
		}

		if($fila['razon_social_id'] == $razon_social_igh)
		{
			$glosa_principal_igh = "PAGO SERV, ".$nombre_servicio;
			$glosa_principal = substr($glosa_principal_igh, 0, 40);
			
			setlocale(LC_TIME, 'spanish');
			$periodo = 	contrato_servicio_publico_tesoreria_item_atencion_nombre_resumen_mes($fila['periodo_consumo']);
			$glosa_detalle_igh = $fila['cc_id'] . " SUM " . $fila['nro_suministro'] . " " . $periodo;
			$glosa_detalle=substr($glosa_detalle_igh, 0, 30);
			$fecha_documento = date('d/m/Y', strtotime($fila['fecha_emision_sp']));
			$fecha_vencimiento = date('d/m/Y', strtotime($fila['fecha_vencimiento_sp']));
		}
		else
		{
			$glosa_principal=substr($fila['glosa_principal'], 0, 40);
			$glosa_detalle =substr($fila['glosa_principal'], 0, 30);
			$fecha_documento = date('d/m/Y', strtotime($fila['fecha_documento']));
			$fecha_vencimiento = date('d/m/Y', strtotime($fila['fecha_vencimiento']));
		}

		$num_documento = $fila['serie']."-".$fila['num_documento'];
		$num_documento = substr($num_documento, 0, 20);

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
			->setCellValue('C'.$i, substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6))
			->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
			->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
			->setCellValue('F'.$i, $glosa_principal)
			->setCellValue('G'.$i, $fila['tipo_cambio'])
			->setCellValue('H'.$i, $fila['tipo_conversion'])
			->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
			->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
			->setCellValue('K'.$i, substr($fila['cuenta_contable'], 0, 12))
			->setCellValue('L'.$i, substr($empresa_servicio_publico_ruc, 0, 18))
			->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
			->setCellValue('N'.$i, substr('D', 0, 1))
			->setCellValue('O'.$i, $fila['importe_original'])
			->setCellValue('P'.$i, $importe_dolares)
			->setCellValue('Q'.$i, $fila['importe_original'])
			->setCellValue('R'.$i, substr('RC', 0, 2))
			->setCellValue('S'.$i, $num_documento)
			->setCellValue('T'.$i, $fecha_documento)
			->setCellValue('U'.$i, $fecha_vencimiento)
			->setCellValue('V'.$i, substr('', 0, 3))
			->setCellValue('W'.$i, $glosa_detalle)
			->setCellValue('X'.$i, substr($fila['codigo_anexo_auxiliar'], 0, 18))
			->setCellValue('Y'.$i, substr($fila['medio_pago'], 0, 8))
			->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
			->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
			->setCellValue('AB'.$i, empty($fila['fecha_documento_referencia'])?'':date('d/m/Y', strtotime($fila['fecha_documento_referencia'])))
			->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
			->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
			->setCellValue('AE'.$i, $fila['igv_documento_provision'])
			->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
			->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
			->setCellValue('AH'.$i, $fila['fecha_operacion'])
			->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
			->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
			->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
			->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
			->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
			->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
			->setCellValue('AO'.$i, $fila['tasa_igv']);

		$i++;
	}

	$query_monto_agua_y_luz = 
	"
		SELECT
			p.id AS programacion_id,
			rs.id AS razon_social_id,
			rs.subdiario AS sub_diario,
			sp.id_tipo_servicio_publico,
		    p.numero_comprobante_concar,
		    p.fecha_comprobante,
		    'MN' AS codigo_moneda,
		    '{$param_glosa_principal_sub_total}' AS glosa_principal,
		    tc.monto_venta AS tipo_cambio,
		    'V' AS tipo_conversion,
			'S' AS flag_conversion_moneda,
		    tc.fecha AS fecha_tipo_cambio,
		    '{$param_codigo_anexo}' AS cuenta_contable,
		    '{$param_codigo_anexo}' AS codigo_anexo,
		    '' AS tipo_servicio_publico,
		    '' AS empresa_agua_ruc,
			'' AS empresa_luz_ruc,
			'' AS codigo_centro_costo,
		    'H' AS debe_haber,
		    (
				SELECT
					IFNULL(SUM(spi.total_pagar), 0) AS monto
				FROM cont_ser_pub_programacion_detalle pdi
					INNER JOIN cont_local_servicio_publico spi
					ON pdi.cont_local_servicio_publico_id = spi.id
				WHERE pdi.cont_ser_pub_programacion_id = p.id AND pdi.status = 1 $where_tipo_servicio_detalle 
			) AS importe_original,
			'' AS tipo_documento,
		    '' AS num_documento,
			'1' AS num_transferencia_banco,
		    p.fecha_comprobante AS fecha_documento,
			p.fecha_comprobante AS fecha_vencimiento,
		    '{$param_codigo_area_agua}' AS codigo_area_agua,
			'{$param_codigo_area_luz}' AS codigo_area_luz,
			'{$param_glosa_principal_sub_total}' AS glosa_detalle,
		    '' AS codigo_anexo_auxiliar,
			'' AS medio_pago,
			'' AS tipo_documento_referencia,
			'' AS num_documento_referencia,
			'' AS fecha_documento_referencia,
			'' AS maquina_registradora_tipo_documento,
			'' AS base_imponible_documento_referencia,
			'' AS igv_documento_provision,
			'' AS tipo_referencia_estado,
			'' AS num_serie_caja_registradora,
			'' AS fecha_operacion,
			'' AS tipo_tasa,
			'' AS tasa_detraccion_percepcion,
			'' AS importe_base_detraccion_percepcion_dolares,
			'' AS importe_base_detraccion_percepcion_soles,
			'' AS tipo_cambio_para_f,
			'' AS importe_igv_sin_derecho_credito_fiscal,
			'' AS tasa_igv
		FROM cont_ser_pub_programacion p
			INNER JOIN tbl_razon_social rs
			ON p.tipo_empresa_id = rs.id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
			INNER JOIN cont_ser_pub_programacion_detalle pd
			ON p.id = pd.cont_ser_pub_programacion_id
			INNER JOIN cont_local_servicio_publico sp
			ON pd.cont_local_servicio_publico_id = sp.id
			-- INNER JOIN tbl_locales l
			-- ON sp.id_local = l.id
		
			-- INNER JOIN cont_inmueble_suministros s
			-- ON sp.inmueble_suministros_id = s.id
		WHERE p.id = {$programacion_id}
		$where_tipo_servicio 
		LIMIT 1
	";

	$list_query_monto_agua_y_luz = $mysqli->query($query_monto_agua_y_luz);

	while ($fila = $list_query_monto_agua_y_luz->fetch_array())
	{
		$importe_dolares = $fila['importe_original'] / $fila['tipo_cambio'];
		$razon_social_registro = $fila['razon_social_id'];


		//		Consulta de Cuenta contable IGH
			$proceso_pago_servicios_tesoreria = "PAGO SERVICIOS";
			$selectQuery = "SELECT num_cuenta_contable 
							FROM cont_num_cuenta nc
							LEFT JOIN cont_num_cuenta_proceso ncp ON ncp.id =nc.cont_num_cuenta_proceso_id
							WHERE ncp.nombre= ? AND nc.razon_social_id = ?";

			$selectStmt = $mysqli->prepare($selectQuery);
			$selectStmt->bind_param("si", $proceso_pago_servicios_tesoreria,$razon_social_registro);
			$selectStmt->execute();
			$selectStmt->store_result();

			if ($selectStmt->num_rows > 0) {
				$selectStmt->bind_result($num_cuenta_contable);
				$selectStmt->fetch();
				$cod_anexo = $num_cuenta_contable;
			}else{
				$num_cuenta_contable = "No existe cuenta contable";
			}

		if($razon_social_registro == $razon_social_igh){
			$glosa_principal=substr($glosa_principal_igh, 0, 40);
			//setlocale(LC_TIME, 'spanish');
			//$periodo = strtoupper(strftime('%b', strtotime($fila['fecha_documento'])));
			//$glosa_detalle_igh = $fila['cc_id'] . " SUM " . $fila['nro_suministro'] . " " . $periodo;
			$glosa_detalle_igh=substr($fila['glosa_principal'], 0, 40);

			$glosa_detalle=substr($glosa_detalle_igh, 0, 30);
            $num_documento_igh = $num_movimiento;
			//$num_documento_igh = substr($fila['num_transferencia_banco'], 0, 20);
			//$num_documento_igh = "S".$fila['serie']."-".$fila['numero_recibo'];
			$num_documento = substr($num_documento_igh, 0, 20);
			$medio_pago = substr('003', 0, 8);

			//$tipo_servicio_publico = $fila['tipo_servicio_publico'];
			if($tipo_servicio_publico == 1)
			{
				// LUZ
				$codigo_area = $param_codigo_area_luz;
				$nombre_servicio = "LUZ";

			}
			else if($tipo_servicio_publico == 2)
			{
				// AGUA
				$codigo_area =  $param_codigo_area_agua;
				$nombre_servicio = "AGUA";

			}
			$glosa_principal_igh = "PAGO SERV, ".$nombre_servicio;

		}else{
			$glosa_principal=substr($fila['glosa_principal'], 0, 40);
			$glosa_detalle=substr($fila['glosa_detalle'], 0, 30);
			$num_documento = substr($fila['num_transferencia_banco'], 0, 20);
			$medio_pago = substr('001', 0, 8);
			$codigo_area = substr($fila['codigo_area_agua'], 0, 3);
			//$num_cuenta_contable = substr($fila['cuenta_contable'], 0, 12);
			$cod_anexo = substr($num_cuenta_contable, 0, 18);
		}

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
		->setCellValue('C'.$i, substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6))
		->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
		->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
		->setCellValue('F'.$i, $glosa_principal)
		->setCellValue('G'.$i, $fila['tipo_cambio'])
		->setCellValue('H'.$i, $fila['tipo_conversion'])
		->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
		->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
		->setCellValue('K'.$i, $num_cuenta_contable)
		->setCellValue('L'.$i, $cod_anexo)
		->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
		->setCellValue('N'.$i, substr($fila['debe_haber'], 0, 1))
		->setCellValue('O'.$i, $fila['importe_original'])
		->setCellValue('P'.$i, $importe_dolares)
		->setCellValue('Q'.$i, $fila['importe_original'])
		->setCellValue('R'.$i, substr('TR', 0, 2))
		->setCellValue('S'.$i, $num_documento)
		->setCellValue('T'.$i, date('d/m/Y', strtotime($fila['fecha_documento'])))
		->setCellValue('U'.$i, date('d/m/Y', strtotime($fila['fecha_vencimiento'])))
		->setCellValue('V'.$i, $codigo_area)
		->setCellValue('W'.$i, $glosa_detalle)
		->setCellValue('X'.$i, substr($fila['codigo_anexo_auxiliar'], 0, 18))
		->setCellValue('Y'.$i, $medio_pago)
		->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
		->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
		->setCellValue('AB'.$i, empty($fila['fecha_documento_referencia'])?'':date('d/m/Y', strtotime($fila['fecha_documento_referencia'])))
		->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
		->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
		->setCellValue('AE'.$i, $fila['igv_documento_provision'])
		->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
		->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
		->setCellValue('AH'.$i, $fila['fecha_operacion'])
		->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
		->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
		->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
		->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
		->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
		->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
		->setCellValue('AO'.$i, $fila['tasa_igv']);

		$i++;
	}

	$estiloColoFondoAmarilloOscuro = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => 'ffc000')
	  )
	);
	  
	$estiloTituloColumnas = array(
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
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));


	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(57);



	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloColoFondoAmarilloOscuro);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloColoFondoAmarilloOscuro);

	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:AN".($i-1));

	$objPHPExcel->getActiveSheet()->getStyle('O4:O'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('P4:P'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('Q4:Q'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Plantilla Concar Servicio Publico.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/Plantilla Concar Servicio Publico.xls';
	$excel_path_download = '/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/Plantilla Concar Servicio Publico.xls';
	
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

if (isset($_POST["accion"]) && $_POST["accion"] === "contrato_servicio_publico_tesoreria_item_detalle_guardar_programacion_detalle_num_transferencia")
{
	$detalle_id = $_POST["detalle_id"];
	$num_transferencia_banco = $_POST["num_transferencia_banco"];

	if($num_transferencia_banco == "" || strlen($num_transferencia_banco) > 10)
	{
		$result["focus"] = "num_transferencia_banco";
		$result["mensaje"] = "Ingresar Nº Transferencia.";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}
	
	$query_update = 
	"
		UPDATE cont_ser_pub_programacion_detalle 
			SET num_transferencia_banco = '".$num_transferencia_banco."'  
		WHERE id = ".$detalle_id;

	$mysqli->query($query_update);

	$result["query"] = $query_update;
	$result["mensaje"] = "Nº Transferencia Actualizada";
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_tesoreria_item_detalle_programacion_anular_detalle")
{
	$message = "";
	$fecha = date("Y-m-d H:i:s");

    $usuario_id = $login ? $login['id'] : null;

	if ((int)$usuario_id > 0) {

		$programacion_detalle_id = (int)$_POST['programacion_detalle_id'];

		$query_anular_detalle = "
                UPDATE cont_ser_pub_programacion_detalle 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

        $stmt = $mysqli->prepare($query_anular_detalle);
        $stmt->bind_param("isi", $usuario_id, $fecha, $programacion_detalle_id);

        if ($stmt->execute()) {
			
			$message = "Recibo anulado";
			$status = true;
			$result["status"] = $status;
			$result["message"] = $message;

		}else{

			$status = false;
			$message = $mysqli->error;
			$result["status"] = $status;
			$result["message"] = "Error al ejecutar la consulta: " . $stmt->error;;

			print_r(json_encode($result));
			exit();
		}


		//-------------------------------  Consultar recibo adjuntado

		$selectQuery = "SELECT cont_local_servicio_publico_id 
				FROM cont_ser_pub_programacion_detalle
				WHERE id = ?";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $programacion_detalle_id);
		$selectStmt->execute();
		$selectStmt->store_result();

		if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($cont_local_servicio_publico_id);
		$selectStmt->fetch();
		}

		//------------- Cambiar el estado del registro del servicio publico para que se pueda reprogramar su pago

		$query_anular_detalle = "
						UPDATE cont_local_servicio_publico 
						SET 
							estado = 2,
							user_updated_id = ?,
							updated_at = ?
						WHERE 
							id = ?
						";

		$stmt = $mysqli->prepare($query_anular_detalle);
		$stmt->bind_param("isi", $usuario_id, $fecha, $cont_local_servicio_publico_id);

		if ($stmt->execute()) {

			$message = "Recibo anulado";
			$status = true;
			$result["status"] = $status;
			$result["message"] = $message;

		}else{

			$status = false;
			$message = $mysqli->error;

			$result["status"] = $status;
			$result["message"] = "Error al ejecutar la consulta: " . $stmt->error;;

			print_r(json_encode($result));
			exit();
		}

	} else {
        $result["status"] = false;
		$result["message"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";

		print_r(json_encode($result));
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_servicio_publico_tesoreria_item_detalle_programacion_totales")
{
	$programacion_id = $_POST["programacion_id"];
	
	$query = 
	"
		SELECT
			pd.id,
			CONCAT('[' ,l.cc_id, '] ', l.nombre) AS local,
		    sp.inmueble_suministros_id,
		    cis.tipo_servicio_id AS tipo_servicio_publico,
			cis.nro_suministro AS suministro,
			cis.tipo_compromiso_pago_id,
			tps.nombre AS compromiso_pago,
		    sp.id_tipo_servicio_publico,
			tsp.nombre AS tipo_servicio_nombre,
			ea.nombre_comercial AS empresa_agua_nombre_comercial,
			el.nombre_comercial AS empresa_luz_nombre_comercial,
		    sp.periodo_consumo,
			sp.total_pagar AS total_pagado,
			pd.num_transferencia_banco,
			sp.estado,
			esp.nombre as estado_nombre
		FROM cont_ser_pub_programacion_detalle pd
			INNER JOIN cont_local_servicio_publico sp
			ON pd.cont_local_servicio_publico_id = sp.id
			INNER JOIN tbl_locales l
			ON sp.id_local = l.id
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			INNER JOIN cont_tipo_pago_servicio tps
			ON cis.tipo_compromiso_pago_id = tps.id
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
			LEFT JOIN cont_local_servicio_publico_empresas ea
			ON i.id_empresa_servicio_agua = ea.id
			LEFT JOIN cont_local_servicio_publico_empresas el
			ON i.id_empresa_servicio_luz = el.id
		WHERE pd.status = 1 AND pd.cont_ser_pub_programacion_id = '".$programacion_id."'
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	//$path = "/var/www/html/files_bucket/prestamos/boveda/reporte/";
	$path = "/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria*'); //obtenemos todos los nombres de los ficheros
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

    $tituloReporte = "Relación de Servicios Públicos";

	$titulosColumnas = array('Nº', 'ID', 'Local', '# Suministro', 'Servicio', 'Empresa', 'Periodo', 'Monto', 'Nº Transferencia');

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
    ->setCellValue('I1', $titulosColumnas[8]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($sel = $list_query->fetch_array()) 
	{
		$cont ++;

		$programacion_detalle_id = $sel["id"];
		$local = $sel["local"];
		$tipo_servicio_publico = $sel["tipo_servicio_publico"];
		$suministro = $sel["suministro"];
		$tipo_servicio_nombre = $sel["tipo_servicio_nombre"];
		$periodo_consumo = contrato_servicio_publico_tesoreria_item_atencion_nombre_mes($sel["periodo_consumo"]);
		$total_pagado = $sel["total_pagado"];
		$num_transferencia_banco = $sel["num_transferencia_banco"];

		$empresa_servicio_publico = "El servicio publico no es Agua ni Luz.";

		if($tipo_servicio_publico == 1)
		{
			// LUZ
			$empresa_servicio_publico = $sel["empresa_luz_nombre_comercial"];
		}
		else if($tipo_servicio_publico == 2)
		{
			// AGUA
			$empresa_servicio_publico = $sel["empresa_agua_nombre_comercial"];
		}

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $programacion_detalle_id)
		->setCellValue('C'.$i, $local)
		->setCellValue('D'.$i, $suministro)
		->setCellValue('E'.$i, $tipo_servicio_nombre)
		->setCellValue('F'.$i, $empresa_servicio_publico)
		->setCellValue('G'.$i, $periodo_consumo)
		->setCellValue('H'.$i, "S/ ".$total_pagado)
		->setCellValue('I'.$i, $num_transferencia_banco);
		
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
	            'rgb' => 'ffffff'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => '000000')
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

	$objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:I".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:I'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('H2:H'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Servicios Públicos');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Servicios Públicos.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/Servicios Públicos.xls';
	$excel_path_download = '/files_bucket/contratos/servicios_publicos/reportes/tesoreria/Servicios Públicos.xls';

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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_servicio_publico_tesoreria_item_detalle_programacion_compartidos")
{
	$programacion_id = $_POST["programacion_id"];
	
	$query = 
	"
		SELECT
			pd.id,
			CONCAT('[' ,l.cc_id, '] ', l.nombre) AS local,
		    sp.inmueble_suministros_id,
		    cis.tipo_servicio_id AS tipo_servicio_publico,
			cis.nro_suministro AS suministro,
			cis.tipo_compromiso_pago_id,
			tps.nombre AS compromiso_pago,
		    sp.id_tipo_servicio_publico,
			tsp.nombre AS tipo_servicio_nombre,
			ea.nombre_comercial AS empresa_agua_nombre_comercial,
			el.nombre_comercial AS empresa_luz_nombre_comercial,
		    sp.periodo_consumo,
			sp.total_pagar AS total_pagado,
			pd.num_transferencia_banco,
			sp.estado,
			esp.nombre as estado_nombre
		FROM cont_ser_pub_programacion_detalle pd
			INNER JOIN cont_local_servicio_publico sp
			ON pd.cont_local_servicio_publico_id = sp.id
			INNER JOIN tbl_locales l
			ON sp.id_local = l.id
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			INNER JOIN cont_tipo_pago_servicio tps
			ON cis.tipo_compromiso_pago_id = tps.id
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
			LEFT JOIN cont_local_servicio_publico_empresas ea
			ON i.id_empresa_servicio_agua = ea.id
			LEFT JOIN cont_local_servicio_publico_empresas el
			ON i.id_empresa_servicio_luz = el.id
		WHERE pd.status = 1 AND pd.cont_ser_pub_programacion_id = '".$programacion_id."'
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	//$path = "/var/www/html/files_bucket/prestamos/boveda/reporte/";
	$path = "/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria*'); //obtenemos todos los nombres de los ficheros
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

    $tituloReporte = "Relación de Servicios Públicos";

	$titulosColumnas = array('Nº', 'ID', 'Local', '# Suministro', 'Servicio', 'Empresa', 'Periodo', 'Monto');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B1', $titulosColumnas[1])
    ->setCellValue('C1', $titulosColumnas[2])
    ->setCellValue('D1', $titulosColumnas[3])
    ->setCellValue('E1', $titulosColumnas[4])
    ->setCellValue('F1', $titulosColumnas[5])
    ->setCellValue('G1', $titulosColumnas[6])
    ->setCellValue('H1', $titulosColumnas[7]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($sel = $list_query->fetch_array()) 
	{
		$cont ++;

		$programacion_detalle_id = $sel["id"];
		$local = $sel["local"];
		$tipo_servicio_publico = $sel["tipo_servicio_publico"];
		$suministro = $sel["suministro"];
		$tipo_servicio_nombre = $sel["tipo_servicio_nombre"];
		$periodo_consumo = contrato_servicio_publico_tesoreria_item_atencion_nombre_mes($sel["periodo_consumo"]);
		$total_pagado = $sel["total_pagado"];

		$empresa_servicio_publico = "El servicio publico no es Agua ni Luz.";

		if($tipo_servicio_publico == 1)
		{
			// LUZ
			$empresa_servicio_publico = $sel["empresa_luz_nombre_comercial"];
		}
		else if($tipo_servicio_publico == 2)
		{
			// AGUA
			$empresa_servicio_publico = $sel["empresa_agua_nombre_comercial"];
		}

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $programacion_detalle_id)
		->setCellValue('C'.$i, $local)
		->setCellValue('D'.$i, $suministro)
		->setCellValue('E'.$i, $tipo_servicio_nombre)
		->setCellValue('F'.$i, $empresa_servicio_publico)
		->setCellValue('G'.$i, $periodo_consumo)
		->setCellValue('H'.$i, "S/ ".$total_pagado);
		
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
	            'rgb' => 'ffffff'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => '000000')
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

	$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:H".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:H'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('H2:H'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Servicios Públicos');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Servicios Públicos.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/Servicios Públicos.xls';
	$excel_path_download = '/files_bucket/contratos/servicios_publicos/reportes/tesoreria/Servicios Públicos.xls';

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

echo json_encode($result);

?>