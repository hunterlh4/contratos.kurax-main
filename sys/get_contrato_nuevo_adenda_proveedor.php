<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_proveedores") {
	$usuario_id = $login?$login['id']:null;
	$query = "SELECT c.ruc AS id, c.razon_social as nombre
				FROM
					cont_contrato c
				WHERE
					tipo_contrato_id = 2
					-- AND c.user_created_id = $usuario_id
					AND c.etapa_id = 5
				GROUP BY c.ruc , c.razon_social
				ORDER BY c.razon_social ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contratos") {
	
	$proveedor = $_POST['proveedor'];
	
	$query = "SELECT c.contrato_id, c.fecha_inicio, c.detalle_servicio, co.sigla, c.codigo_correlativo
	FROM cont_contrato c
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE c.tipo_contrato_id = 2 AND c.etapa_id = 5 AND c.status = 1
	AND c.ruc = '" . $proveedor . "'";
	$list_query = $mysqli->query($query);
	$list = [];

	while ($li = $list_query->fetch_assoc()) {
		$date = new DateTime($li['fecha_inicio']);
		$fecha_inicio = $date->format('Y-m-d');
		array_push($list,array(
			'id' => $li['contrato_id'],
			'nombre' => $li['sigla'].$li['codigo_correlativo'].' | '.$fecha_inicio.' - '.$li['detalle_servicio'],
		));
	}
	
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_plazo") {
	$query = "SELECT * FROM cont_tipo_plazo WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_abogados") {

	$query = "SELECT  u.id ,
	CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
	FROM 
		tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_cargos AS c ON c.id = p.cargo_id
	WHERE 
		u.estado = 1
		AND p.estado = 1
		AND p.area_id IN (33)
	ORDER BY 
		p.nombre ASC,
		p.apellido_paterno ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "La provincia no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "La provincia no existe.";
	}
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contrato_proveedor_por_id") {

	$contrato_id = $_POST["contrato_id"];
	
	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)':'(AT)';

	$query = $mysqli->query("
	SELECT 
		c.tipo_contrato_id,
		c.empresa_suscribe_id,
		r.nombre AS empresa_suscribe,
		c.ruc,
		c.razon_social,
		c.nombre_comercial,
		c.vigencia,
		c.dni_representante,
		c.nombre_representante,
		c.observaciones,
		c.persona_contacto_proveedor,
		c.user_created_id,
		c.detalle_servicio,
		c.periodo_numero,
		c.periodo,
		pd.nombre AS periodo_anio_mes,
		c.fecha_inicio,
		c.fecha_vencimiento_proveedor,
		CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
		c.created_at,
		c.tipo_terminacion_anticipada_id,
		t.nombre AS tipo_terminacion_anticipada,
		c.terminacion_anticipada,
		c.alcance_servicio,
		tp.nombre AS plazo,
		c.plazo_id,
		ta.nombre AS area_responsable,
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,

		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		peg.correo AS email_del_gerente_area,

		cpc.nombre AS cargo_persona_contacto,
		cr.nombre AS cargo_responsable,
		ca.nombre AS cargo_aprobante,
		CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado
	FROM
		cont_contrato c
		LEFT JOIN cont_periodo pd ON c.periodo = pd.id
		LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		LEFT JOIN tbl_areas ta ON ta.id = c.area_responsable_id
		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

		LEFT JOIN tbl_cargos AS cpc ON cpc.id = c.cargo_id_persona_contacto
		LEFT JOIN tbl_cargos AS cr ON cr.id = c.cargo_id_responsable
		LEFT JOIN tbl_cargos AS ca ON ca.id = c.cargo_id_aprobante

		LEFT JOIN cont_tipo_terminacion_anticipada t ON c.tipo_terminacion_anticipada_id = t.id
	WHERE 
		c.contrato_id = $contrato_id
	");
	$row = $query->fetch_assoc();
	$empresa_suscribe = $row["empresa_suscribe"];
	$persona_contacto_proveedor = $row["persona_contacto_proveedor"];
	$tipo_contrato_id = $row["tipo_contrato_id"]; 
	$gerente_area_id = trim($row["gerente_area_id"]);
	$abogado = trim($row["abogado"]);

	$cargo_persona_contacto = $row["cargo_persona_contacto"];
	$cargo_responsable = $row["cargo_responsable"];
	$cargo_aprobante = $row["cargo_aprobante"];
	$area_responsable = $row["area_responsable"];

	if (empty($gerente_area_id)) {
		$gerente_area_nombre = trim($row["gerente_area_nombre"]);
		$gerente_area_email = trim($row["gerente_area_email"]);
	} else {
		$gerente_area_nombre = trim($row["nombre_del_gerente_area"]);
		$gerente_area_email = trim($row["email_del_gerente_area"]);
	}
	
	$ruc = $row["ruc"];
	$razon_social = $row["razon_social"];
	$nombre_comercial = $row["nombre_comercial"];
	$dni_representante = $row["dni_representante"];
	$nombre_representante = $row["nombre_representante"];

	$detalle_servicio = $row["detalle_servicio"];
	$plazo = $row["plazo"];
	$plazo_id = $row["plazo_id"];
	$periodo_numero = $row["periodo_numero"];
	$periodo_anio_mes = $row["periodo_anio_mes"];
	$observaciones = $row["observaciones"];

	$date = date_create($row["fecha_inicio"]);
	$fecha_inicio = date_format($date, "d-m-Y");

	$fecha_vencimiento_proveedor = date_create($row["fecha_vencimiento_proveedor"]);
	$fecha_vencimiento_proveedor = date_format($fecha_vencimiento_proveedor, "d-m-Y");

	
	$alcance_servicio = $row["alcance_servicio"];
	$tipo_terminacion_anticipada_id = $row["tipo_terminacion_anticipada_id"];
	$tipo_terminacion_anticipada = $row["tipo_terminacion_anticipada"];
	$terminacion_anticipada = $row["terminacion_anticipada"];

	
	
	$repre_query = $mysqli->query("
	SELECT 
		rl.id, 
		rl.dni_representante, 
		rl.nombre_representante, 
		rl.nro_cuenta_detraccion,
		rl.id_banco, 
		b.nombre as banco_representante, 
		rl.nro_cuenta, 
		rl.nro_cci, 
		rl.vigencia_archivo_id,
		rl.dni_archivo_id,
		td.nombre as nombre_tipo_doc
	FROM 
		cont_representantes_legales rl
		LEFT JOIN cont_tipo_docu_identidad as td ON td.id = rl.tipo_documento_id
		LEFT JOIN tbl_bancos b on b.id = rl.id_banco
	WHERE 
		rl.contrato_id = $contrato_id"
	);

	$contraprestacion_query = $mysqli->query("
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
	");


	
	
	$html = '
	<div class="panel">
		<div class="panel-heading">
			<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO DE PROVEEDORES</div>
			<input type="hidden" value="'.$contrato_id.'" id="id_registro_contrato_id">
			<input type="hidden" value="'.$tipo_contrato_id.'" id="id_tipo_contrato">
		</div>
		<div class="panel-body">
		<form id="sec_nuevo_form_adenda_proveeedor" name="sec_nuevo_form_adenda_proveeedor" method="POST" enctype="multipart/form-data" autocomplete="off">
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4"><b>DATOS GENERALES</b></div>
				</div>
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 50%;"><b>Empresa Contratante</b></td>
								<td>' . $empresa_suscribe . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Datos generales\',\'cont_contrato\',\'empresa_suscribe_id\',\'Empresa Contratante\',\'select_option\',\'' . $empresa_suscribe . '\',\'obtener_empresa_at\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Persona Contacto '.$valor_empresa_contacto.'</b></td>
								<td>' . $persona_contacto_proveedor . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'persona_contacto_proveedor\',\'Persona Contacto '.$valor_empresa_contacto.'\',\'varchar\',\'' . $persona_contacto_proveedor . '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Cargo Persona Contacto '.$valor_empresa_contacto.'</b></td>
								<td>' . $cargo_persona_contacto . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'cargo_id_persona_contacto\',\'Cargo Persona Contacto '.$valor_empresa_contacto.'\',\'select_option\',\'' . $cargo_persona_contacto . '\',\'obtener_cargos\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Responsable de Área</b></td>
								<td>' . $gerente_area_nombre . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'gerente_area_id\',\'Responsable de Área\',\'select_option\',\'' . $gerente_area_nombre . '\',\'obtener_gerentes\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Cargo Responsable de Área</b></td>
								<td>' . $cargo_responsable . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'cargo_id_responsable\',\'Cargo Responsable de Área\',\'select_option\',\'' . $cargo_responsable . '\',\'obtener_cargos\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Área Responsable</b></td>
								<td>' . $area_responsable . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'area_responsable_id\',\'Área Responsable\',\'select_option\',\'' . $area_responsable . '\',\'obtener_areas\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<br>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4"><b>DATOS DEL PROVEEDOR</b>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 50%;"><b>Número de RUC</b></td>
								<td>' . $ruc . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'ruc\',\'Número de RUC\',\'varchar\',\'' . $ruc . '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Razón Social</b></td>
								<td>' . $razon_social . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'razon_social\',\'Razón Social\',\'varchar\',\'' . $razon_social . '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Nombre Comercial</b></td>
								<td>' . $nombre_comercial . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'nombre_comercial\',\'Nombre Comercial\',\'varchar\',\'' . $nombre_comercial . '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
						</table>
					</div>
				</div>

						


				<div class="col-xs-12 col-md-12 col-lg-12">
					<br>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4"><b>REPRESENTANTE LEGAL</b>
						<button type="button" class="btn btn-sm btn-info" onclick="sec_con_nuevo_aden_int_agregar_representante()">
							<i class="fa fa-plus"></i> Agregar Representante Legal
						</button>
					</div>
				</div>';

				$row_count = $repre_query->num_rows;
				$index = 1;
				if ($row_count > 0) {
					while($sel=$repre_query->fetch_assoc()){
				$html .= '
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="w-100">
						<b>Representante Legal #'.$index.'</b>
					</div>
					<div class="form-group">
						<table class="table table-bordered table-hover">';
						
				$html .= '<tr>
								<td><b>Tipo Documento del representante legal</b></td>
								<td>' . $sel["nombre_tipo_doc"] . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'tipo_documento_id\',\'Tipo Documento del representante legal\',\'select_option\',\'' . $sel["nombre_tipo_doc"] . '\',\'obtener_tipo_docu_identidad\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>


							<tr>
								<td><b>Nro Documento del representante legal</b></td>
								<td>' . $sel["dni_representante"] . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'dni_representante\',\'Nro Documento del representante legal\',\'varchar\',\'' . $sel["dni_representante"] . '\',\'\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>

							<tr>
								<td><b>Nombre completo del representante legal</b></td>
								<td>' . $sel["nombre_representante"] . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nombre_representante\',\'Nombre completo del representante legal\',\'varchar\',\'' . $sel["nombre_representante"] . '\',\'\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>

							<tr>
								<td><b>Número de cuenta de detracción (Banco de la Nación)</b></td>
								<td>' . $sel["nro_cuenta_detraccion"] . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cuenta_detraccion\',\'Número de cuenta de detracción (Banco de la Nación)\',\'varchar\',\'' . $sel["nro_cuenta_detraccion"] . '\',\'\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
							
						

							<tr>
								<td><b>Banco</b></td>
								<td>' . $sel["banco_representante"] . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'id_banco\',\'Banco\',\'select_option\',\'' . $sel["banco_representante"] . '\',\'obtener_bancos\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
					
							<tr>
								<td><b>Número de cuenta</b></td>
								<td>' . $sel["nro_cuenta"] . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cuenta\',\'Número de cuenta\',\'varchar\',\'' . $sel["nro_cuenta"] . '\',\'\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
					
							<tr>
								<td><b>Número CCI</b></td>
								<td>' . $sel["nro_cci"] . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cci\',\'Número CCI\',\'varchar\',\'' . $sel["nro_cci"] . '\',\'\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>';

						
				$html .= '
						</table>
					</div>
				</div>';
				$index++;
					}
				}else{
					$html .= '
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="w-100">
						<b>Representante Legal</b>
					</div>
					<div class="form-group">
						<table class="table table-bordered table-hover">';
						
				$html .= '<tr>
								<td><b>DNI del representante legal</b></td>
								<td>' . $dni_representante . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'dni_representante\',\'DNI del representante legal\',\'varchar\',\'' . $dni_representante . '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>

							<tr>
								<td><b>Nombre completo del representante legal</b></td>
								<td>' . $nombre_representante . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'nombre_representante\',\'Nombre completo del representante legal\',\'varchar\',\'' . $nombre_representante . '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>';
				$html .= '
						</table>
					</div>
				</div>';
				}
				

				$html .= '
				<div class="col-xs-12 col-md-12 col-lg-12">
				<br>
				</div>
			
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4"><b>CONDICIONES COMERCIALES</b></div>
				</div>
			
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4">1) Objeto del Contrato:</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="white-space: pre-line;">' . $detalle_servicio . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs"
								onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Objeto del Contrato\',\'cont_contrato\',\'detalle_servicio\',\'Detalle de servicio a contratar\',\'textarea\',\'' . replace_invalid_caracters_vista($detalle_servicio) . '\',\'\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4">2) Plazo del Contrato:</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 50%;"><b>Plazo</b></td>
								<td>' . $plazo . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
								onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'plazo_id\',\'Plazo\',\'select_option\',\'' . $plazo . '\',\'obtener_tipo_plazo\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>';

							if ($plazo_id == 1) {
								$html .= '
							<tr>
								<td style="width: 50%;"><b>Periodo - Número</b></td>
								<td>' . $periodo_numero . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
								onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'periodo_numero\',\'Periodo (Número)\',\'int\',\'' . $periodo_numero . '\',\'\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>

							<tr>
								<td style="width: 50%;"><b>Periodo - Año o Mes</b></td>
								<td>' . $periodo_anio_mes . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
								onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'periodo\',\'Periodo (Año o Mes)\',\'select_option\',\'' . $periodo_anio_mes . '\',\'obtener_periodo\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>';
							}
							

						$html .= '

						<tr>
							<td><b>Fecha de inicio</b></td>
							<td>' . $fecha_inicio . '</td>
							<td>
							<a class="btn btn-success btn-xs" 
							onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'fecha_inicio\',\'Fecha de inicio\',\'date\',\'' . $fecha_inicio . '\',\'\',\'\');">
							<span class="fa fa-edit"></span> Editar
							</a>
							</td>
						</tr>

						<tr>
						<td><b>Fecha de Fin</b></td>
						<td>' . $fecha_vencimiento_proveedor . '</td>
						<td>
						<a class="btn btn-success btn-xs" 
						onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'fecha_vencimiento_proveedor\',\'Fecha de Fin\',\'date\',\'' . $fecha_vencimiento_proveedor . '\',\'\',\'\');">
						<span class="fa fa-edit"></span> Editar
						</a>
						</td>
					</tr>

						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
				<br>
				</div>
				
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4">3) Contraprestación: 
						<a class="btn btn-success btn-xs" 
						onclick="sec_con_nuevo_aden_int_nuevo_contraprestacion_modal()">
						<span class="fa fa-edit"></span> Agregar Contraprestación
						</a>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">';
						$row_count = $contraprestacion_query->num_rows;
						if ($row_count > 0) {
							$contador_contraprestacion = 1;
							while($sel = $contraprestacion_query->fetch_assoc()){
								$contraprestacion_id = $sel["id"];
								$tipo_moneda = $sel["tipo_moneda"];
								$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];
								$subtotal = $tipo_moneda_simbolo.' '.number_format($sel["subtotal"], 2, '.', ',');
								$igv = $tipo_moneda_simbolo.' '.number_format($sel["igv"], 2, '.', ',');
								$monto = $tipo_moneda_simbolo.' '.number_format($sel["monto"], 2, '.', ',');
								$forma_pago_detallado = $sel["forma_pago_detallado"];
								$tipo_comprobante = $sel["tipo_comprobante"];
								$plazo_pago = $sel["plazo_pago"];
							
								$html .= '
							<tr>
								<td style="width: 50%;"><b>Tipo de moneda</b></td>
								<td>' . $tipo_moneda . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'moneda_id\',\'Tipo de moneda\',\'select_option\',\'' . $tipo_moneda . '\',\'obtener_monedas\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td style="width: 50%;"><b>Subtotal</b></td>
								<td>' . $subtotal . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'subtotal\',\'Subtotal\',\'decimal\',\'' . $subtotal . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td style="width: 50%;"><b>IGV</b></td>
								<td>' . $igv . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'igv\',\'IGV\',\'decimal\',\'' . $igv . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td style="width: 50%;"><b>Monto Bruto</b></td>
								<td>' . $monto . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'monto\',\'Monto Bruto\',\'decimal\',\'' . $monto . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td><b>Tipo de comprobante a emitir</b></td>
								<td>' . $tipo_comprobante . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'tipo_comprobante_id\',\'Tipo de comprobante a emitir\',\'select_option\',\'' . $tipo_comprobante . '\',\'obtener_tipo_comprobante\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td><b>Plazo de Pago</b></td>
								<td style="white-space: pre-line;">' . $plazo_pago . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'plazo_pago\',\'Plazo de Pago\',\'textarea\',\'' . replace_invalid_caracters_vista($plazo_pago) . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td><b>Forma de pago</b></td>
								<td style="white-space: pre-line;">' . $forma_pago_detallado . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'forma_pago_detallado\',\'Forma de pago\',\'textarea\',\'' . replace_invalid_caracters_vista($forma_pago_detallado) . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>';

							}
						}

					$html .= '
					</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
				<br>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
				<div class="h4">4) Alcance del Servicio:</div>
				</div>
			
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">					
							<tr>
								<td style="white-space: pre-line;">' . $alcance_servicio . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs"
								onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Alcance del servicio\',\'cont_contrato\',\'alcance_servicio\',\'Alcance del servicio\',\'textarea\',\'' . replace_invalid_caracters_vista($alcance_servicio) . '\',\'\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>
						</table>
					</div>
				</div>


				<div class="col-xs-12 col-md-12 col-lg-12">
				<br>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
				<div class="h4">5) Terminación Anticipada:</div>
				</div>
			
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">					
							<tr>
								<td>' . $tipo_terminacion_anticipada . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs"
								onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Terminación Anticipada\',\'cont_contrato\',\'tipo_terminacion_anticipada_id\',\'Terminación Anticipada\',\'select_option\',\'' . $tipo_terminacion_anticipada . '\',\'obtener_tipo_terminacion_anticipada\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>
						</table>
					</div>
				</div>
					
				<div class="col-xs-12 col-md-12 col-lg-12">
				<br>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
				<div class="h4">6) Observaciones:</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">					
							<tr>
								<td style="white-space: pre-line;">' . $observaciones . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs"
								onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Observaciones\',\'cont_contrato\',\'observaciones\',\'Observaciones\',\'textarea\',\'' . replace_invalid_caracters_vista($observaciones) . '\',\'\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
				<div class="h4">7) Objeto de Adenda:</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">					
							<tr>
								<td style="white-space: pre-line;"></td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs"
								onclick="sec_con_nuevo_aden_prov_solicitud_editar_campo_adenda(\'Objeto de Adenda\',\'cont_contrato\',\'objeto_adenda\',\'Objeto de Adenda\',\'textarea\',\'\',\'\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="sec_modal_nuevo_archivo_anexo(2)">
						<i class="icon fa fa-save"></i> <span>Agregar archivos</span>
					</button>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<br>
					<div id="archivos" style="width:300px">
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12" id="sec_nuevo_nuevos_anexos_listado">
					<input id="fileToUploadAnexo" accept="application/pdf, image/png, image/jpeg" type="file" multiple="" style="display:none">
				</div>

				
				
				';
				$html .='
			</form>
		</div>
	</div>';
	



	

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$result["status"] = 200;
	$result["messages"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_docu_identidad") {
	$query = "SELECT * FROM cont_tipo_docu_identidad WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_empresa_at") {
	$query = "SELECT id, nombre
	FROM tbl_razon_social
	WHERE status = 1
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipos_de_archivos") {
	$tipo_contrato_id = $_POST["tipo_contrato_id"];

	$query = "
	SELECT 
		tipo_archivo_id, nombre_tipo_archivo
	FROM
		cont_tipo_archivos
	WHERE
		status = 1
		AND tipo_contrato_id = $tipo_contrato_id
		AND tipo_archivo_id NOT IN (16 , 17, 19)
	ORDER BY nombre_tipo_archivo ASC
	";

	$list_query = $mysqli->query($query);
	$list_proc_tipos_archivos = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_proc_tipos_archivos[] = $li;
	}

	$result["http_code"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list_proc_tipos_archivos;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_terminacion_anticipada") {
	$query = "SELECT * FROM cont_tipo_terminacion_anticipada WHERE status = 1";

	$list_query = $mysqli->query($query);
	$list_proc_tipos_archivos = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_proc_tipos_archivos[] = $li;
	}

	$result["http_code"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list_proc_tipos_archivos;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_bancos") {
	$query = "SELECT id, ifnull(nombre, '') nombre 
				FROM tbl_bancos
				WHERE estado = 1";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_periodo") {
	$query = "SELECT * FROM cont_periodo WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_comprobante") {
	$query = "SELECT * FROM cont_tipo_comprobante WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_forma_pago") {
	$query = "SELECT * FROM cont_forma_pago WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_monedas") {
	$query = "SELECT id, nombre FROM tbl_moneda WHERE id IN (1,2) AND estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_proveedores") {
	$proveedores = isset($_POST['proveedores']) ? $_POST['proveedores']:[];

	$html = '';
	$html .= '<table class="table table-hover">
		<thead>
			<th class="text-center">DNI R.L</th>
			<th class="text-center">Nombre Completo R.L</th>
			<th class="text-center">N° Cuenta Detracción</th>
			<th class="text-center">Banco</th>
			<th class="text-center">N° Cuenta</th>
			<th class="text-center">N° CCI</th>
			<th class="text-center">Vigencia de Poder</th>
			<th class="text-center">DNI</th>
			<th class="text-center">Acc.</th>
		</thead>
		<tbody>';

		for ($i=0; $i < count($proveedores) ; $i++) { 
			$html .= '
			<tr>
				<td class="text-left">'.$proveedores[$i]['dni_representante'].'</td>
				<td class="text-left">'.$proveedores[$i]['nombre_representante'].'</td>
				<td class="text-left">'.$proveedores[$i]['nro_cuenta_detraccion'].'</td>
				<td class="text-left">'.$proveedores[$i]['banco_nombre'].'</td>
				<td class="text-left">'.$proveedores[$i]['nro_cuenta'].'</td>
				<td class="text-left">'.$proveedores[$i]['nro_cci'].'</td>
				<td><input type="file" name="vigencia_nuevo_representante_' .$i .'" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>
				<td><input type="file" name="dni_nuevo_representante_' .$i .'" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>
				<td><button type="button" class="btn btn-sm btn-success" onclick="sec_con_nuevo_int_editar_proveedor('.$i.')"><i class="fa fa-edit"></i></button></td>
				<td><button type="button" class="btn btn-sm btn-danger" onclick="sec_con_nuevo_int_eliminar_proveedor('.$i.')"><i class="fa fa-close"></i></button></td>
			</tr>
			';
		}
			
	$html .= '
		</tbody>
	</table>';
	


	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_contraprestaciones") {
	$contraprestaciones = isset($_POST['contraprestaciones']) ? $_POST['contraprestaciones']:[];

	$html = '';
	$html .= '<table class="table table-hover">
		<thead>
			<th class="text-center">Tipo de Moneda</th>
			<th class="text-center">Subtotal</th>
			<th class="text-center">IGV</th>
			<th class="text-center">Monto Bruto</th>
			<th class="text-center">Forma de Pago</th>
			<th class="text-center">Tipo de Comprobante a Emitir</th>
			<th class="text-center">Plazo de pago</th>
			<th colspan="2" class="text-center">Opciones</th>
		</thead>
		<tbody>';

		for ($i=0; $i < count($contraprestaciones) ; $i++) { 
			$html .= '
			<tr>
				<td class="text-left">'.$contraprestaciones[$i]['moneda_nombre'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['subtotal'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['igv'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['monto'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['forma_pago_detallado'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['tipo_comprobante_nombre'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['plazo_pago'].'</td>
				<td><button type="button" class="btn btn-sm btn-success" onclick="sec_con_nuevo_int_editar_contraprestacion('.$i.')"><i class="fa fa-edit"></i></button></td>
				<td><button type="button" class="btn btn-sm btn-danger" onclick="sec_con_nuevo_int_eliminar_contraprestacion('.$i.')"><i class="fa fa-close"></i></button></td>
			</tr>
			';
		}
			
	$html .= '
		</tbody>
	</table>';
	


	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_cargos") {

	$query = "SELECT c.id, c.nombre FROM tbl_cargos AS c WHERE c.estado = 1 ORDER BY c.nombre ASC";
	$list_query = $mysqli->query($query);
	
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_areas") {

	$query = "SELECT  ta.id, ta.nombre from tbl_areas ta where ta.estado = 1";
	$list_query = $mysqli->query($query);
	
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_gerentes") {

	$query = "SELECT 
		u.id, 
		CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
	FROM 
		tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN cont_usuarios_gerentes g ON u.id = g.user_id
	WHERE 
		u.estado = 1
		AND g.status = 1
	ORDER BY 
	p.nombre ASC,
	p.apellido_paterno ASC";
	$list_query = $mysqli->query($query);
	
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

?>