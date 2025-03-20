<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El área no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El área no existe.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_cargo_usuario") {



	if ($_POST['type'] == 'persona_contacto'){
		$usuario_id = $login?$login['id']:null;
		$query = "SELECT pe.id, pe.cargo_id FROM tbl_usuarios AS u 
		INNER JOIN tbl_personal_apt AS pe ON pe.id = u.personal_id AND pe.estado = 1
		WHERE u.id = ".$usuario_id;
		$list_query = $mysqli->query($query);
		$data = $list_query->fetch_assoc();

		if(isset($data['cargo_id'])){
			$result["status"] = 200;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = $data['cargo_id'];
			echo json_encode($result);
			exit();
		}

		$result["status"] = 400;
		$result["message"] = "Datos obtenidos de gestion.";
		$result["result"] = 0;
		echo json_encode($result);
		exit();
	}else if($_POST['type'] == 'aprobador'){
		$usuario_id = 3218; // lourder britto
		$query = "SELECT pe.id, pe.cargo_id FROM tbl_usuarios AS u 
		INNER JOIN tbl_personal_apt AS pe ON pe.id = u.personal_id AND pe.estado = 1
		WHERE u.id = ".$usuario_id;
		$list_query = $mysqli->query($query);
		$data = $list_query->fetch_assoc();

		if(isset($data['cargo_id'])){
			$result["status"] = 200;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = $data['cargo_id'];
			echo json_encode($result);
			exit();
		}

		$result["status"] = 400;
		$result["message"] = "Datos obtenidos de gestion.";
		$result["result"] = 0;
		echo json_encode($result);
		exit();

	}else{
		$usuario_id = $_POST['usuario_id'];
		if(isset( $_POST['usuario_id']) && !($usuario_id == "0" || $usuario_id == "A") ){
			$query = "SELECT pe.id, pe.cargo_id FROM tbl_usuarios AS u 
			INNER JOIN tbl_personal_apt AS pe ON pe.id = u.personal_id AND pe.estado = 1
			WHERE u.id = ".$usuario_id;
			$list_query = $mysqli->query($query);
			$data = $list_query->fetch_assoc();

			if(isset($data['cargo_id'])){
				$result["status"] = 200;
				$result["message"] = "Datos obtenidos de gestion.";
				$result["result"] = $data['cargo_id'];
				echo json_encode($result);
				exit();
			}

			$result["status"] = 400;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = 0;
			echo json_encode($result);
			exit();
		}else{
			$result["status"] = 400;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = 0;
			echo json_encode($result);
			exit();
		}
	}

}
?>