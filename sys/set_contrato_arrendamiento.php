<?php  
date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_arrendamientos")
{

	$user_id = $login?$login['id']:null;
	$area_id = $login ? $login['area_id'] : 0;

	$empresas = [];
	if ($login["usuario_locales"]) {
		$query_empresa = "SELECT l.razon_social_id FROM tbl_locales AS l 
		INNER JOIN tbl_razon_social AS r ON r.id = l.razon_social_id
		WHERE l.estado = 1 AND r.status = 1 
		AND l.id IN (".implode(",", $login["usuario_locales"]).")
		GROUP BY l.razon_social_id 
		ORDER BY l.nombre ASC;";
		$sel_query = $mysqli->query($query_empresa);
		while($sel=$sel_query->fetch_assoc())
		{
			array_push($empresas,$sel['razon_social_id']);
		}
	}
	


	$cont_arrendamiento_param_empresa = $_POST['cont_arrendamiento_param_empresa'];

	$cont_arrendamiento_param_area_solicitante = $_POST['cont_arrendamiento_param_area_solicitante'];

	$cont_arrendamiento_param_ruc = $_POST['cont_arrendamiento_param_ruc'];

	$cont_arrendamiento_param_razon_social = $_POST['cont_arrendamiento_param_razon_social'];

	$cont_arrendamiento_param_moneda = $_POST['cont_arrendamiento_param_moneda'];

	$fecha_inicio_solicitud = $_POST['fecha_inicio_solicitud'];
	$fecha_fin_solicitud = $_POST['fecha_fin_solicitud'];
	$fecha_inicio_inicio = $_POST['fecha_inicio_inicio'];
	$fecha_fin_inicio = $_POST['fecha_fin_inicio'];

	$fecha_inicio_aprobacion = $_POST['search_fecha_inicio_aprobacion_firmado'];
	$fecha_fin_aprobacion = $_POST['search_fecha_fin_aprobacion_firmado'];

	$director_aprobacion_id = trim($_POST['aprobante']);



	

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_fecha_solicitud = "";
	$where_fecha_inicio = "";
	$where_director_aprobacion	=	"";
	$where_estado_aprobacion	=	"";
	
	if ($cont_arrendamiento_param_empresa != ""){
		$where_empresa = " AND c.empresa_suscribe_id = '".$cont_arrendamiento_param_empresa."' ";
	}else{ // En caso que no tenga ninguna empresa seleccionada
		if (count($login["usuario_locales"])) {
			$where_empresa = " AND c.empresa_suscribe_id IN (".implode(",", $empresas).") ";
		}
		
	}

	if(!($area_id == 33 || $area_id == 6 || ( array_key_exists($menu_id,$usuario_permisos) && in_array("see_all", $usuario_permisos[$menu_id]) )) ) // 33:legal 6:sistemas
	{
		if ($cont_arrendamiento_param_area_solicitante != "")
		{
			$where_area_solicitante = " AND a.id = '".$cont_arrendamiento_param_area_solicitante."' ";
		}else{
			$where_area_solicitante = " AND (c.user_created_id = ".$user_id." OR a.id = ".$area_id.") ";
		}
	}

	

	if ($cont_arrendamiento_param_ruc != "")
	{
		$where_ruc = " AND c.ruc = '".$cont_arrendamiento_param_ruc."' ";
	}

	if ($cont_arrendamiento_param_razon_social != "")
	{
		$where_razon_social = " AND c.razon_social = '".$cont_arrendamiento_param_razon_social."' ";
	}

	if ($cont_arrendamiento_param_moneda != "")
	{
		$where_moneda = " AND (c.moneda_id = $cont_arrendamiento_param_moneda OR (SELECT ct.moneda_id FROM cont_contraprestacion ct WHERE ct.contrato_id = c.contrato_id LIMIT 1) = $cont_arrendamiento_param_moneda) ";
	}
	

	if (!Empty($fecha_inicio_solicitud) && !Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
	} elseif (!Empty($fecha_inicio_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
	} elseif (!Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
	}

	if (!Empty($fecha_inicio_inicio) && !Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
	} elseif (!Empty($fecha_inicio_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio >= '$fecha_inicio_inicio 00:00:00'";
	} elseif (!Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio <= '$fecha_fin_inicio 23:59:59'";
	}

	$where_fecha_aprobacion = '';
	if (!Empty($fecha_inicio_aprobacion) && !Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!Empty($fecha_inicio_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor <= '$fecha_fin_aprobacion 23:59:59'";
	}

	if (!Empty($director_aprobacion_id))
	{
		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = ".$director_aprobacion_id." AND (c.check_gerencia_arrendamiento =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_arrendamiento=0) ) OR c.aprobado_por = ".$director_aprobacion_id." ) ";
	}
 
	$query = "
	SELECT
		c.contrato_id, 
		c.codigo_correlativo,
		a.nombre AS area, 
		r.nombre AS empresa_suscribe, 
		c.ruc, 
		c.razon_social, 
		c.fecha_atencion_gerencia_proveedor,
		IFNULL(m.nombre,(SELECT mc.nombre FROM cont_contraprestacion ct INNER JOIN tbl_moneda mc ON ct.moneda_id = mc.id WHERE ct.contrato_id = c.contrato_id LIMIT 1)) AS moneda,
		c.fecha_inicio, 
		concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante, 
		CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
		cs.nombre AS arrendamiento_categoria, 
		tcs.nombre AS arrendamiento_tipo_categoria,
		arc.ruta, 
		arc.nombre,
		co.sigla AS sigla_correlativo,
		c.num_dias_para_alertar_vencimiento,
		c.created_at,
		c.estado_resolucion,
		c.fecha_vencimiento_indefinida_id,
		c.fecha_vencimiento_proveedor
	FROM 
		cont_contrato c
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas a ON c.area_responsable_id = a.id
		LEFT JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN tbl_moneda m ON c.moneda_id = m.id
		LEFT JOIN cont_categoria_servicio cs ON c.categoria_id = cs.id
		LEFT JOIN cont_tipo_categoria_servicio tcs ON c.tipo_contrato_proveedor_id = tcs.id
		LEFT JOIN cont_archivos arc ON c.contrato_id = arc.contrato_id AND arc.archivo_id IN(SELECT MAX(archivo_id) AS archivo_id FROM cont_archivos WHERE status = 1 AND tipo_archivo_id = 19 GROUP BY contrato_id)
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
		LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
	WHERE 
		c.status = 1 
		AND c.etapa_id = 5 
		AND c.tipo_contrato_id = 1  
		$where_empresa
		$where_area_solicitante
		$where_ruc
		$where_razon_social
		$where_moneda
		$where_fecha_solicitud
		$where_fecha_inicio
		$where_fecha_aprobacion
		$where_director_aprobacion

	ORDER BY c.contrato_id DESC
	";
	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{


		$fecha_vencimiento_proveedor = '';
		$estado_contractual = '';
		if ((int) $reg->fecha_vencimiento_indefinida_id == 1) {
			$fecha_vencimiento_proveedor = 'Indefinida';
			$estado_contractual = 'Vigente';
		} else {

			$fechaObj1 = new DateTime($reg->fecha_vencimiento_proveedor);
			$fechaObj2 = new DateTime(date('Y-m-d'));

			if($reg->estado_resolucion == 2){
				$estado_contractual = 'Resuelto';
			}else{
				if ($fechaObj1 > $fechaObj2) {
					$estado_contractual = 'Vigente';
				}else{
					$estado_contractual = 'Vencido';
				}
			}
			$fecha_vencimiento_proveedor = $reg->fecha_vencimiento_proveedor;
		}

		$fecha = new DateTime($reg->fecha_inicio);
		$fecha_convertido = $fecha->format('Y-m-d');

		


		if ( empty(trim($reg->num_dias_para_alertar_vencimiento)) ) {
			$clase_boton_alertar = 'primary';
			$titulo_boton_alerta = 'Alerta por configurar';
		} else {
			$clase_boton_alertar = 'success';
			$titulo_boton_alerta = 'Alerta configurada';
		} 

		$data[] = array(
			"0" => $reg->sigla_correlativo.$reg->codigo_correlativo,
			"1" => $reg->area,
			"2" => $reg->solicitante,
			"3" => $reg->empresa_suscribe,
			"4" => $reg->ruc,
			"5" => $reg->razon_social,
			"6" => $reg->moneda,
			"7" => $reg->created_at,
			"8" => $fecha_convertido,
			"9" => $fecha_vencimiento_proveedor,
			"10" => $estado_contractual,
			"11" => $reg->arrendamiento_categoria,			
			"12" => $reg->arrendamiento_tipo_categoria,
			"13" => $reg->nombre_del_director_a_aprobar,
			"14" => $reg->fecha_atencion_gerencia_proveedor,
			"15" => '<a class="btn btn-rounded btn-primary btn-xs" 
						href="./?sec_id=contrato&amp;sub_sec_id=detalle_solicitud&id=' . $reg->contrato_id . '"
						title="Ver detalle">
						<i class="fa fa-eye"></i> Ver
					</a>
					<a onclick="sec_contrato_arrendamiento_btn_descargar(\''.str_replace("C:/laragon/www/contratos.kurax-main", "", $reg->ruta).$reg->nombre.'\')"; title="Descargar Contrato de Proveedor Final" class="btn btn-primary btn-xs"><i class="fa fa-download"></i> Descargar
					</a>',
			"16" => '<button type="button" class="btn btn-' . $clase_boton_alertar . ' btn-sm" title="' . $titulo_boton_alerta . '" onclick="sec_contrato_arrendamiento_alerta('.$reg->contrato_id.')">
					  <i class="glyphicon glyphicon-bell"></i>
					</button>'
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

if (isset($_POST["accion"]) && $_POST["accion"]==="cont_arrendamiento_reporte_arrendamiento_excel")
{

	$user_id = $login?$login['id']:null;
	$area_id = $login ? $login['area_id'] : 0;

	$empresas = [];
	if ($login["usuario_locales"]) {
		$query_empresa = "SELECT l.razon_social_id FROM tbl_locales AS l 
		INNER JOIN tbl_razon_social AS r ON r.id = l.razon_social_id
		WHERE l.estado = 1 AND r.status = 1 
		AND l.id IN (".implode(",", $login["usuario_locales"]).")
		GROUP BY l.razon_social_id 
		ORDER BY l.nombre ASC;";
		$empresas = [];
		$sel_query = $mysqli->query($query_empresa);
		while($sel=$sel_query->fetch_assoc())
		{
			array_push($empresas,$sel['razon_social_id']);
		}
	}
	

	$cont_arrendamiento_param_empresa = $_POST['cont_arrendamiento_param_empresa'];

	$cont_arrendamiento_param_area_solicitante = $_POST['cont_arrendamiento_param_area_solicitante'];

	$cont_arrendamiento_param_ruc = $_POST['cont_arrendamiento_param_ruc'];

	$cont_arrendamiento_param_razon_social = $_POST['cont_arrendamiento_param_razon_social'];

	$cont_arrendamiento_param_moneda = $_POST['cont_arrendamiento_param_moneda'];

	$fecha_inicio_solicitud = $_POST['fecha_inicio_solicitud'];
	$fecha_fin_solicitud = $_POST['fecha_fin_solicitud'];
	$fecha_inicio_inicio = $_POST['fecha_inicio_inicio'];
	$fecha_fin_inicio = $_POST['fecha_fin_inicio'];

	$fecha_inicio_aprobacion = $_POST['search_fecha_inicio_aprobacion_firmado'];
	$fecha_fin_aprobacion = $_POST['search_fecha_fin_aprobacion_firmado'];

	$director_aprobacion_id = trim($_POST['aprobante']);

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_fecha_solicitud = "";
	$where_fecha_inicio = "";

	$where_director_aprobacion	=	"";
	$where_estado_aprobacion	=	"";

	if ($cont_arrendamiento_param_empresa != "")
	{
		$where_empresa = " AND c.empresa_suscribe_id = '".$cont_arrendamiento_param_empresa."' ";
	}else{ // En caso que no tenga ninguna empresa seleccionada
		if ($login["usuario_locales"]){
			$where_empresa = " AND c.empresa_suscribe_id IN (".implode(",", $empresas).") ";
		}
	}

	if(!($area_id == 33 || $area_id == 6 || ( array_key_exists($menu_id,$usuario_permisos) && in_array("see_all", $usuario_permisos[$menu_id]) )) ) // 33:legal 6:sistemas
	{
		if ($cont_arrendamiento_param_area_solicitante != "")
		{
			$where_area_solicitante = " AND a.id = '".$cont_arrendamiento_param_area_solicitante."' ";
		}else{
			$where_area_solicitante = " AND (c.user_created_id = ".$user_id." OR a.id = ".$area_id.") ";
		}
	}

	if ($cont_arrendamiento_param_ruc != "")
	{
		$where_ruc = " AND c.ruc = '".$cont_arrendamiento_param_ruc."' ";
	}

	if ($cont_arrendamiento_param_razon_social != "")
	{
		$where_razon_social = " AND c.razon_social = '".$cont_arrendamiento_param_razon_social."' ";
	}

	if ($cont_arrendamiento_param_moneda != "")
	{
		$where_moneda = " AND (c.moneda_id = $cont_arrendamiento_param_moneda OR (SELECT ct.moneda_id FROM cont_contraprestacion ct WHERE ct.contrato_id = c.contrato_id LIMIT 1) = $cont_arrendamiento_param_moneda) ";
	}

	if (!Empty($fecha_inicio_solicitud) && !Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
	} elseif (!Empty($fecha_inicio_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
	} elseif (!Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
	}

	if (!Empty($fecha_inicio_inicio) && !Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
	} elseif (!Empty($fecha_inicio_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio >= '$fecha_inicio_inicio 00:00:00'";
	} elseif (!Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio <= '$fecha_fin_inicio 23:59:59'";
	}

	$where_fecha_aprobacion = '';
	if (!Empty($fecha_inicio_aprobacion) && !Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!Empty($fecha_inicio_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor <= '$fecha_fin_aprobacion 23:59:59'";
	}

	if (!Empty($director_aprobacion_id))
	{
		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = ".$director_aprobacion_id." AND (c.check_gerencia_arrendamiento =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_arrendamiento=0) ) OR c.aprobado_por = ".$director_aprobacion_id." ) ";
	}

	$query = "
	SELECT
		c.contrato_id, 
		c.codigo_correlativo,
		a.nombre AS area, 
		c.ruc, 
		c.fecha_atencion_gerencia_proveedor,

		IFNULL(m.nombre,(SELECT mc.nombre FROM cont_contraprestacion ct INNER JOIN tbl_moneda mc ON ct.moneda_id = mc.id WHERE ct.contrato_id = c.contrato_id LIMIT 1)) AS moneda,
		c.fecha_inicio, 
		concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante, 
		co.sigla AS sigla_correlativo,
		a.nombre AS area_solicitante, 
		c.persona_contacto_arrendamiento,
		tcs.nombre AS tipo_contrato, 
		cs.nombre AS categoria,
		r.nombre AS razon_social, 
		c.razon_social AS parte, 
		c.fecha_suscripcion_arrendamiento, 
		c.fecha_vencimiento_proveedor, 
		et.situacion AS estado, 
		f.nombre AS tipo_firma, 
		c.observaciones,
		CONCAT(c.periodo_numero, ' ', pr.nombre) AS periodo,
		ta.nombre AS tipo_terminacion_anticipada,
		m.nombre AS tipo_moneda,
		m.simbolo AS tipo_moneda_simbolo,
		c.monto,
		fp.nombre AS forma_pago,
		tc.nombre AS tipo_comprobante,
		c.plazo_pago,
		c.detalle_servicio,
		c.alcance_servicio,
		c.created_at,
		c.renovacion_automatica,
		CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
		c.estado_resolucion,
		c.fecha_vencimiento_indefinida_id

	FROM 
		cont_contrato c
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas a ON c.area_responsable_id = a.id
		LEFT JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN cont_categoria_servicio cs ON c.categoria_id = cs.id
		LEFT JOIN cont_tipo_categoria_servicio tcs ON c.tipo_contrato_arrendamiento_id = tcs.id
		INNER JOIN cont_etapa et ON c.etapa_id = et.etapa_id
		LEFT JOIN cont_tipo_firma f ON c.tipo_firma_id = f.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN cont_periodo pr ON c.periodo = pr.id
		LEFT JOIN cont_tipo_terminacion_anticipada ta ON c.tipo_terminacion_anticipada_id = ta.id
		LEFT JOIN tbl_moneda m ON c.moneda_id = m.id
		LEFT JOIN cont_forma_pago fp ON c.forma_pago_id = fp.id
		LEFT JOIN cont_tipo_comprobante tc ON c.tipo_comprobante_id = tc.id

		LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
		LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
	WHERE 
		c.status = 1 
		AND c.etapa_id = 5 
		AND c.tipo_contrato_id = 2 
		$where_empresa
		$where_area_solicitante
		$where_ruc
		$where_razon_social
		$where_moneda
		$where_fecha_solicitud
		$where_fecha_inicio
		$where_fecha_aprobacion
		$where_director_aprobacion 
	ORDER BY c.contrato_id DESC
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
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

	$tituloReporte = "Relación de arrendamientoes";

	$titulosColumnas = array(
		'CODIGO', 
		'ÁREA SOLICITANTE', 
		'PERSONA DE CONTACTO', 
		'TIPO DE CONTRATO', 
		'CATEGORÍA', 
		'RAZÓN SOCIAL', 
		'PARTE', 
		'OBJETO', 
		'PLAZO', 
		'MONEDA', 
		'SUBTOTAL', 
		'IGV', 
		'MONTO BRUTO', 
		'TIPO DE COMPROBANTE', 
		'PLAZO DE PAGO', 
		'FORMA DE PAGO', 
		'FECHA DE SOLICITUD', 
		'FECHA SUSCRIPCIÓN', 
		'VENCIMIENTO', 
		'RENOVACIÓN AUTOMÁTICA',
		'ESTADO CONTRACTUAL', 
		'ALCANCE', 
		'TERMINACIÓN ANTICIPADA', 
		'ESTADO', 
		'TIPO DE FIRMA', 
		'APROBANTE',
		'F. APROBACIÓN',
		'ETAPA',
		'OBSERVACIONES'
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
	->setCellValue('V1', $titulosColumnas[21])
	->setCellValue('W1', $titulosColumnas[22])
    ->setCellValue('X1', $titulosColumnas[23])
    ->setCellValue('Y1', $titulosColumnas[24])
    ->setCellValue('Z1', $titulosColumnas[25])
	->setCellValue('AA1', $titulosColumnas[26])
	->setCellValue('AB1', $titulosColumnas[27])
	->setCellValue('AC1', $titulosColumnas[28]);


    //Se agregan los datos a la lista del reporte
	$vigencia = '';
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		if ((int) $fila['estado_resolucion'] == 2) {
			$vigencia = 'RESUELTO';
		}else{
			if($fila['fecha_vencimiento_proveedor'] < date('Y-m-d'))
			{
				$vigencia = 'VENCIDA';
			}
			else
			{
				$vigencia = 'VIGENTE';
			}
		}
		

		$contrato_id = $fila["contrato_id"];
		$renovacion_automatica = $fila['renovacion_automatica'] == 1 ? 'SI':'NO';
		$etapa = $fila['estado_resolucion'] == 2 ? 'Resuelto':'Firmado';
		$sql_contraprestacion = "
		SELECT 
			c.id,
			c.moneda_id,
			m.nombre AS tipo_moneda,
			m.simbolo AS tipo_moneda_simbolo,
			c.subtotal,
			c.igv,
			c.monto,
			c.forma_pago_detallado,
			c.tipo_comprobante_id,
			t.nombre AS tipo_comprobante,
			c.plazo_pago
		FROM 
			cont_contraprestacion c
			INNER JOIN tbl_moneda m ON c.moneda_id = m.id
			INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
		WHERE 
			c.contrato_id = $contrato_id
			AND c.status = 1
		";

		$query = $mysqli->query($sql_contraprestacion);
		$row_count = $query->num_rows;

		$subtotal = "";
		$igv = "";

		if ($row_count > 0) {
			while($sel = $query->fetch_assoc()){
				$tipo_moneda = $sel["tipo_moneda"];
				$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];
				$subtotal = $tipo_moneda_simbolo.' '.number_format($sel["subtotal"], 2, '.', ',');
				$igv = $tipo_moneda_simbolo.' '.number_format($sel["igv"], 2, '.', ',');
				$monto = $tipo_moneda_simbolo.' '.number_format($sel["monto"], 2, '.', ',');
				$forma_pago = $sel["forma_pago_detallado"];
				$tipo_comprobante = $sel["tipo_comprobante"];
				$plazo_pago = $sel["plazo_pago"];
			}
		} elseif ($row_count == 0) {
			$tipo_moneda = $fila["tipo_moneda"];
			$tipo_moneda_simbolo = $fila["tipo_moneda_simbolo"];
			$monto = $tipo_moneda_simbolo.' '.number_format($fila["monto"], 2, '.', ',');
			$forma_pago = $fila["forma_pago"];
			$tipo_comprobante = $fila["tipo_comprobante"];
			$plazo_pago = $fila["plazo_pago"];
		}


		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $fila['sigla_correlativo'].$fila['codigo_correlativo'])
		->setCellValue('B'.$i, $fila['area_solicitante'])
		->setCellValue('C'.$i, $fila['persona_contacto_arrendamiento'])
		->setCellValue('D'.$i, $fila['tipo_contrato'])
		->setCellValue('E'.$i, $fila['categoria'])
		->setCellValue('F'.$i, $fila['razon_social'])
		->setCellValue('G'.$i, $fila['parte'])
		->setCellValue('H'.$i, $fila['detalle_servicio'])
		->setCellValue('I'.$i, $fila['periodo'])
		->setCellValue('J'.$i, $tipo_moneda)
		->setCellValue('K'.$i, $subtotal)
		->setCellValue('L'.$i, $igv)
		->setCellValue('M'.$i, $monto)
		->setCellValue('N'.$i, $tipo_comprobante)
		->setCellValue('O'.$i, $plazo_pago)
		->setCellValue('P'.$i, $forma_pago)
		->setCellValue('Q'.$i, $fila['created_at'])
		->setCellValue('R'.$i, $fila['fecha_suscripcion_arrendamiento'])
		->setCellValue('S'.$i, $fila['fecha_vencimiento_proveedor'])
		->setCellValue('T'.$i, $renovacion_automatica)
		->setCellValue('U'.$i, $vigencia)
		->setCellValue('V'.$i, $fila['alcance_servicio'])
		->setCellValue('W'.$i, $fila['tipo_terminacion_anticipada'])
		->setCellValue('X'.$i, $fila['estado'])
		->setCellValue('Y'.$i, $fila['tipo_firma'])
		->setCellValue('Z'.$i, $fila['nombre_del_director_a_aprobar'])
		->setCellValue('AA'.$i, $fila['fecha_atencion_gerencia_proveedor'])
		->setCellValue('AB'.$i, $etapa)
		->setCellValue('AC'.$i, $fila['observaciones']);
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

	$objPHPExcel->getActiveSheet()->getStyle('A1:AC1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:X".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('I1:L'.($i-1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('contrato arrendamientoes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Contrato de arrendamientoes AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/descargas/Contrato de arrendamientoes AT.xls';
	$excel_path_download = '/files_bucket/contratos/descargas/Contrato de arrendamientoes AT.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (PHPExcel_Writer_Exception $e) 
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_info_alerta") {

    $contrato_id = $_POST["contrato_id"];

    $query = "
	SELECT
	c.razon_social,
	c.fecha_inicio,
	c.fecha_vencimiento_proveedor,
	c.num_dias_para_alertar_vencimiento
	FROM cont_contrato c
	WHERE c.status = 1 AND c.contrato_id = '$contrato_id'
	";

    $list_query = $mysqli->query($query);
    $list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

    $result["http_code"] = 200;
    $result["result"] = $list;
	$result["status"] = "Datos obtenidos de gestion.";

	echo json_encode($result);
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="actualizar_alerta_contrato") {
	$contrato_id = $_POST["contrato_id"];
	$num_dias = $_POST["num_dias"];

	$query_update = "
	UPDATE cont_contrato
	SET num_dias_para_alertar_vencimiento = '$num_dias',
	alerta_enviada_id = NULL
	WHERE contrato_id = '$contrato_id' 
	";
	
	$mysqli->query($query_update);

	if($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
	} else {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
	}

	echo json_encode($result);
	exit();
}

?>
