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

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
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

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contratos") {
	
	$empresa_grupo_at_1 = $_POST['empresa_grupo_at_1'];
	$empresa_grupo_at_2 = $_POST['empresa_grupo_at_2'];

	$where_grupo_at_1 = "";
	$where_grupo_at_2 = "";
	if (!empty($empresa_grupo_at_1)) {
		$where_grupo_at_1 = " AND c.empresa_suscribe_id = ".$empresa_grupo_at_1;
	}
	if (!empty($empresa_grupo_at_2)) {
		$where_grupo_at_2 = " AND c.empresa_grupo_at_2 = ".$empresa_grupo_at_2;
	}
	$query = "SELECT c.contrato_id, c.fecha_inicio, c.detalle_servicio,co.sigla, c.codigo_correlativo
	FROM cont_contrato c
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
	INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
	WHERE c.status = 1 AND c.tipo_contrato_id = 7 AND c.etapa_id = 5
	$where_grupo_at_1
	$where_grupo_at_2
	";
	$list_query = $mysqli->query($query);
	$list = [];

	if (!empty($empresa_grupo_at_1) || !empty($empresa_grupo_at_2)) {
		while ($li = $list_query->fetch_assoc()) {
			$date = new DateTime($li['fecha_inicio']);
			$fecha_inicio = $date->format('Y-m-d');
			array_push($list,array(
				'id' => $li['contrato_id'],
				'nombre' => $li['sigla'].$li['codigo_correlativo'].' | '. $fecha_inicio.' - '.$li['detalle_servicio'],
			));
		}
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contrato_interno_por_id") {

	$contrato_id = $_POST["contrato_id"];
	$query =  $mysqli->query("
	SELECT
		c.contrato_id, 
		c.tipo_contrato_id,
		c.empresa_suscribe_id,
		rs1.nombre AS empresa_at1,
		rs2.nombre AS empresa_at2,
		c.detalle_servicio,
		c.periodo_numero,
		p.nombre AS periodo_anio_mes,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		c.fecha_vencimiento_proveedor,
		ar.nombre AS area_creacion,
		c.check_gerencia_interno,
		c.fecha_atencion_gerencia_interno,
		c.aprobacion_gerencia_interno,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.observaciones,
		c.created_at,
		
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado,
		peg.correo AS email_del_gerente_area,

		cpc.nombre AS cargo_persona_contacto,
		cr.nombre AS cargo_responsable,
		ca.nombre AS cargo_aprobante
	FROM 
		cont_contrato c
		INNER JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

		LEFT JOIN tbl_cargos AS cpc ON cpc.id = c.cargo_id_persona_contacto
		LEFT JOIN tbl_cargos AS cr ON cr.id = c.cargo_id_responsable
		LEFT JOIN tbl_cargos AS ca ON ca.id = c.cargo_id_aprobante
	WHERE 
	c.contrato_id = $contrato_id
	");
	
	$row = $query->fetch_assoc();
	$tipo_contrato_id = $row["tipo_contrato_id"];
	$empresa_at1 = $row["empresa_at1"];
	$empresa_at2 = $row["empresa_at2"];
	$detalle_servicio = $row["detalle_servicio"];
	$periodo_numero = $row["periodo_numero"];
	$periodo_anio_mes = $row["periodo_anio_mes"];
	$abogado = $row["abogado"];
	$observaciones = $row["observaciones"];

	$date = date_create($row["fecha_inicio"]);
	$fecha_inicio = date_format($date, "d-m-Y");
	$fecha_vencimiento_proveedor = date_create($row["fecha_vencimiento_proveedor"]);
	$fecha_vencimiento_proveedor = date_format($fecha_vencimiento_proveedor, "d-m-Y");

	$gerente_area_id = trim($row["gerente_area_id"]);
	$cargo_persona_contacto = trim($row["cargo_persona_contacto"]);
	$cargo_responsable = trim($row["cargo_responsable"]);
	$cargo_aprobante = trim($row["cargo_aprobante"]);

	if (empty($gerente_area_id)) {
		$gerente_area_nombre = trim($row["gerente_area_nombre"]);
		$gerente_area_email = trim($row["gerente_area_email"]);
	} else {
		$gerente_area_nombre = trim($row["nombre_del_gerente_area"]);
		$gerente_area_email = trim($row["email_del_gerente_area"]);
	}
	
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
		rl.dni_archivo_id
	FROM 
		cont_representantes_legales rl
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
			<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO INTERNO</div>
			<input type="hidden" value="'.$contrato_id.'" id="id_registro_contrato_id">
			<input type="hidden" value="'.$tipo_contrato_id.'" id="id_tipo_contrato">
		</div>
		<div class="panel-body">
			<form>
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4"><b>DATOS GENERALES</b></div>
				</div>
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 50%;"><b>Empresa AT 1</b></td>
								<td>' . $empresa_at1 . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Datos generales\',\'cont_contrato\',\'empresa_suscribe_id\',\'Empresa Grupo AT 1\',\'select_option\',\'' . $empresa_at1 . '\',\'obtener_empresa_at\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Empresa AT 2</b></td>
								<td>' . $empresa_at2 . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Datos generales\',\'cont_contrato\',\'empresa_grupo_at_2\',\'Empresa Grupo AT 2\',\'select_option\',\'' . $empresa_at2 . '\',\'obtener_empresa_at\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Responsable de Área</b></td>
								<td>' . $gerente_area_nombre . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Datos Generales\',\'cont_contrato\',\'gerente_area_id\',\'Responsable de Área\',\'select_option\',\'' . $gerente_area_nombre . '\',\'obtener_gerentes\',\'\');">
									<span class="fa fa-edit"></span> Editar</a>
								</td>
							</tr>
							<tr>
								<td style="width: 50%;"><b>Cargo Responsable de Área</b></td>
								<td>' . $cargo_responsable . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Datos Generales\',\'cont_contrato\',\'cargo_id_responsable\',\'Cargo Responsable de Área\',\'select_option\',\'' . $cargo_responsable . '\',\'obtener_cargos\',\'\');">
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
					<div class="h4"><b>CUENTAS BANCARIAS</b>
						<button type="button" class="btn btn-sm btn-info" onclick="sec_con_nuevo_aden_int_agregar_representante()">
							<i class="fa fa-plus"></i> Agregar Cuenta Bancaria
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
						<b>Cuenta Bancaria #'.$index.'</b>
					</div>
					<div class="form-group">
						<table class="table table-bordered table-hover">';
						
				$html .= '<tr>
								<td><b>Banco</b></td>
								<td>' . $sel["banco_representante"] . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Información de Cuenta Bancaria\',\'cont_representantes_legales\',\'id_banco\',\'Banco\',\'select_option\',\'' . $sel["banco_representante"] . '\',\'obtener_bancos\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
					
							<tr>
								<td><b>Número de cuenta</b></td>
								<td>' . $sel["nro_cuenta"] . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Información de Cuenta Bancaria\',\'cont_representantes_legales\',\'nro_cuenta\',\'Número de cuenta\',\'varchar\',\'' . $sel["nro_cuenta"] . '\',\'\',\'' . $sel["id"] . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
					
							<tr>
								<td><b>Número CCI</b></td>
								<td>' . $sel["nro_cci"] . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Información de Cuenta Bancaria\',\'cont_representantes_legales\',\'nro_cci\',\'Número CCI\',\'varchar\',\'' . $sel["nro_cci"] . '\',\'\',\'' . $sel["id"] . '\');">
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
								onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Objeto del Contrato\',\'cont_contrato\',\'detalle_servicio\',\'Detalle de servicio a contratar\',\'textarea\',\'' . replace_invalid_caracters_vista($detalle_servicio) . '\',\'\',\'\');">
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
							<td style="width: 50%;"><b>Periodo - Número</b></td>
							<td>' . $periodo_numero . '</td>
							<td style="width: 75px;">
							<a class="btn btn-success btn-xs" 
							onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'periodo_numero\',\'Periodo (Número)\',\'int\',\'' . $periodo_numero . '\',\'\',\'\');">
							<span class="fa fa-edit"></span> Editar
							</a>
							</td>
							</tr>

							<tr>
							<td style="width: 50%;"><b>Periodo - Año o Mes</b></td>
							<td>' . $periodo_anio_mes . '</td>
							<td style="width: 75px;">
							<a class="btn btn-success btn-xs" 
							onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'periodo\',\'Periodo (Año o Mes)\',\'select_option\',\'' . $periodo_anio_mes . '\',\'obtener_periodo\',\'\');">
							<span class="fa fa-edit"></span> Editar
							</a>
							</td>
						</tr>

						<tr>
							<td><b>Fecha de inicio</b></td>
							<td>' . $fecha_inicio . '</td>
							<td>
							<a class="btn btn-success btn-xs" 
							onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'fecha_inicio\',\'Fecha de inicio\',\'date\',\'' . $fecha_inicio . '\',\'\',\'\');">
							<span class="fa fa-edit"></span> Editar
							</a>
							</td>
						</tr>

						<tr>
							<td><b>Fecha de Fin</b></td>
							<td>' . $fecha_vencimiento_proveedor . '</td>
							<td>
							<a class="btn btn-success btn-xs" 
							onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'fecha_vencimiento_proveedor\',\'Fecha de Fin\',\'date\',\'' . $fecha_vencimiento_proveedor . '\',\'\',\'\');">
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
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'moneda_id\',\'Tipo de moneda\',\'select_option\',\'' . $tipo_moneda . '\',\'obtener_monedas\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td style="width: 50%;"><b>Subtotal</b></td>
								<td>' . $subtotal . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'subtotal\',\'Subtotal\',\'decimal\',\'' . $subtotal . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td style="width: 50%;"><b>IGV</b></td>
								<td>' . $igv . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'igv\',\'IGV\',\'decimal\',\'' . $igv . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td style="width: 50%;"><b>Monto Bruto</b></td>
								<td>' . $monto . '</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'monto\',\'Monto Bruto\',\'decimal\',\'' . $monto . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td><b>Tipo de comprobante a emitir</b></td>
								<td>' . $tipo_comprobante . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'tipo_comprobante_id\',\'Tipo de comprobante a emitir\',\'select_option\',\'' . $tipo_comprobante . '\',\'obtener_tipo_comprobante\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td><b>Plazo de Pago</b></td>
								<td>' . $plazo_pago . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'plazo_pago\',\'Plazo de Pago\',\'varchar\',\'' . $plazo_pago . '\',\'\',\'' . $contraprestacion_id . '\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
							<tr>
								<td><b>Forma de pago</b></td>
								<td>' . $forma_pago_detallado . '</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'forma_pago_detallado\',\'Forma de pago\',\'varchar\',\'' . $forma_pago_detallado . '\',\'\',\'' . $contraprestacion_id . '\');">
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
				<div class="h4">4) Observaciones:</div>
				</div>
			
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">					
							<tr>
								<td style="white-space: pre-line;">' . $observaciones . '</td>
								<td style="width: 75px;">
								<a class="btn btn-success btn-xs"
								onclick="sec_con_nuevo_aden_int_solicitud_editar_campo_adenda(\'Observaciones\',\'cont_contrato\',\'observaciones\',\'Observaciones\',\'textarea\',\'' . replace_invalid_caracters_vista($observaciones) . '\',\'\',\'\');">
								<span class="fa fa-edit"></span> Editar
								</a>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col-xs-12 col-md-12 col-lg-12">
					<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="anadirArchivo_adenda_interno()">
						<i class="icon fa fa-save"></i> <span>Agregar archivos</span>
					</button>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div id="archivos" style="width:300px">
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12" id="sec_nuevos_files_prueba">
					<!--<input id="fileToUploadAnexo" accept="application/pdf, image/png, image/jpeg" type="file" multiple="" style="display:none">-->
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