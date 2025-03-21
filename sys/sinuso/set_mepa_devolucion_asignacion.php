<?php

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_devolucion_asignacion_listar_asignacion")
{
	$usuario_id = $login?$login['id']:null;
	$usuario_area_id = $login?$login['area_id']:null;

	$param_usuario = $_POST['param_usuario'];
	
	$where_usuario_asignado = "";
	$where_filtro_tesoreria = "";
	
	if($param_usuario != 0)
	{
		$where_usuario_asignado = " AND a.usuario_asignado_id = '".$param_usuario."' ";
	}

	if($usuario_area_id == 3)
	{
		$where_filtro_tesoreria = " AND a.se_solicito_devolucion = 1 AND a.tipo_devolucion = 11 AND a.confirmacion_tesoreria = 0 ";
	}
	else
	{
		$where_filtro_tesoreria = " AND a.aplica_devolucion = 1 ";
	}

	$query = "
		SELECT
			a.id, a.usuario_asignado_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario,
		    a.empresa_id,
		    rs.nombre AS empresa,
		    a.zona_asignacion_id,
		    z.nombre AS zona,
		    a.fondo_asignado, a.saldo_disponible,
		    et.situacion AS situacion_asignacion, a.se_solicito_devolucion,
		    a.fecha_devolucion, a.tipo_devolucion, ts.nombre AS nombre_devolucion,
		    a.aplica_voucher,
		    a.imagen,
		    a.extension,
		    a.size,
		    a.ruta,
		    a.download,
		    concat(IFNULL(tpac.nombre, ''),' ', IFNULL(tpac.apellido_paterno, ''), ' ', IFNULL(tpac.apellido_materno, '')) AS usuario_atencion_cierre,
		    a.confirmacion_tesoreria
		FROM mepa_asignacion_caja_chica a
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_usuario_asignacion_detalle uad
            ON a.usuario_asignado_id = uad.usuario_id 
            AND uad.mepa_asignacion_rol_id = 3 AND uad.status = 1
            INNER JOIN mepa_usuario_asignacion ua
            ON uad.mepa_usuario_asignacion_id = ua.id
            INNER JOIN mepa_usuario_asignacion_detalle uada
            ON ua.id = uada.mepa_usuario_asignacion_id 
            AND uada.mepa_asignacion_rol_id = 2 AND uada.status = 1
			INNER JOIN tbl_razon_social rs
			ON a.empresa_id = rs.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN cont_etapa et
			ON a.situacion_etapa_id = et.etapa_id
			LEFT JOIN mepa_tipos_solicitud ts
			ON a.tipo_devolucion = ts.id
			LEFT JOIN tbl_usuarios tuac
			ON a.user_id_carga_voucher = tuac.id
			LEFT JOIN tbl_personal_apt tpac
			ON tuac.personal_id = tpac.id
		WHERE uada.usuario_id = '".$usuario_id."' 
			AND a.situacion_etapa_id = 8 
			AND a.status = 1 
			".$where_usuario_asignado."
			".$where_filtro_tesoreria."
		ORDER BY 
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) ASC,
			rs.nombre ASC, 
			z.nombre ASC
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	// AREA 3 = TESORERIA
	if($usuario_area_id == 3)
	{
		while($reg = $list_query->fetch_object()) 
		{
			$data[] = array(
				"0" => $num,
				"1" => $reg->usuario,
				"2" => $reg->empresa,
				"3" => $reg->zona,
				"4" => "S/ ".$reg->fondo_asignado,
				"5" => "S/ ".$reg->saldo_disponible,
				"6" => $reg->situacion_asignacion,
				"7" => '<a   
	                        class="btn btn-info btn-xs"
	                        onclick="sec_mepa_devolucion_btn_detalle_devolucion_asignacion(\''.$reg->usuario_atencion_cierre.'\', \''.$reg->fecha_devolucion.'\', \''.$reg->nombre_devolucion.'\', \''.$reg->aplica_voucher.'\', \''.$reg->imagen.'\', \''.$reg->extension.'\', \''.$reg->download.'\');";
	                        data-toggle="tooltip" 
	                        data-placement="top" 
	                        title="Ver Detalle">
	                        <span class="fa fa-eye"></span>
	                        	Ver Detalle
	                    </a>',
	            "8" => '<a
							class="btn btn-success btn-xs"
							onclick="mepa_devolucion_asignacion_tesoreria_confirmar_deolucion('.$reg->id.');";
	                        class="btn btn-success btn-xs"
	                        data-toggle="tooltip" 
	                        data-placement="top" 
	                        title="Confirmar Devolución">
	                        <span class="fa fa-check"></span>
	                        Confirmar devolución
	                    </a>'
			);

			$num++;
		}
	}
	else
	{
		while($reg = $list_query->fetch_object()) 
		{
			$data[] = array(
				"0" => $num,
				"1" => $reg->usuario,
				"2" => $reg->empresa,
				"3" => $reg->zona,
				"4" => "S/ ".$reg->fondo_asignado,
				"5" => "S/ ".$reg->saldo_disponible,
				"6" => $reg->situacion_asignacion,
				"7" => ($reg->se_solicito_devolucion == 0) ?
						'
						<a
							class="btn btn-success btn-xs"
							onclick="sec_mepa_devolucion_btn_crear_devolucion_asignacion('.$reg->id.');";
	                        class="btn btn-success btn-xs"
	                        data-toggle="tooltip" 
	                        data-placement="top" 
	                        title="Crear Devolución">
	                        <span class="fa fa-check"></span>
	                        Crear Devolución
	                    </a>
						'
						:
						'<a   
	                        class="btn btn-info btn-xs"
	                        onclick="sec_mepa_devolucion_btn_detalle_devolucion_asignacion(\''.$reg->usuario_atencion_cierre.'\', \''.$reg->fecha_devolucion.'\', \''.$reg->nombre_devolucion.'\', \''.$reg->aplica_voucher.'\', \''.$reg->imagen.'\', \''.$reg->extension.'\', \''.$reg->download.'\');";
	                        data-toggle="tooltip" 
	                        data-placement="top" 
	                        title="Acceder a la Solicitud">
	                        <span class="fa fa-eye"></span>
	                        	Ver Detalle
	                    </a>',
	            "8" => ($reg->tipo_devolucion == 11) ? 
	            		(($reg->confirmacion_tesoreria == 0) ? 'Pendiente' : 'Confirmada')
	            		: 'No corresponde verificar por Tesoreria'
			);

			$num++;
		}
	}
	

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_devolucion_asignacion_crear_devolucion")
{
	extract($_POST);
	
	$modal_form_id_asignacion = $_POST["modal_form_id_asignacion"];
	$modal_form_param_tipo_devolucion = $_POST["mepa_devolucion_asignacion_modal_form_param_tipo_devolucion"];
	
	$modal_form_param_fecha = $_POST["mepa_devolucion_asignacion_modal_form_param_fecha_inicio"];
	$modal_form_param_fecha = date("Y-m-d", strtotime($modal_form_param_fecha));

	$modal_param_aplica_voucher = $_POST["param_aplica_voucher"];

	$error = '';

	if($modal_param_aplica_voucher == 1)
	{
		if(isset($_FILES['mepa_devolucion_asignacion_modal_param_voucher']))
		{
			$path = "/var/www/html/files_bucket/mepa/devolucion_asignacion/";
			$download = "/files_bucket/mepa/devolucion_asignacion/";

			if (!is_dir($path)) 
			{
				mkdir($path, 0777, true);
			}

			$file_name = $_FILES['mepa_devolucion_asignacion_modal_param_voucher']['name'];
			$file_tmp = $_FILES['mepa_devolucion_asignacion_modal_param_voucher']['tmp_name'];
			$file_size = $_FILES['mepa_devolucion_asignacion_modal_param_voucher']['size'];
			$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			

			$nombreFileUpload = "id_asignacion_".$modal_form_id_asignacion."_fecha_carga_".date('YmdHis'). ".".$file_extension;
			$nombreDownload = $download.$nombreFileUpload;
			move_uploaded_file($file_tmp, $path. $nombreFileUpload);

			if($modal_form_param_tipo_devolucion == 11)
			{
				$query_update = "
							UPDATE mepa_asignacion_caja_chica 
								SET se_solicito_devolucion = 1,
								    fecha_devolucion = '".$modal_form_param_fecha."',
								    aplica_voucher = 1,
								    tipo_devolucion = '".$modal_form_param_tipo_devolucion."',
								    imagen = '".$nombreFileUpload."',
								    extension = '".$file_extension."',
								    size = '".$file_size."',
								    ruta = '".$path."',
								    download = '".$nombreDownload."',
								    user_id_carga_voucher = '".$login["id"]."',
								    confirmacion_tesoreria = 0
							WHERE id = '".$modal_form_id_asignacion."'
							";
			}
			else
			{
				$query_update = "
							UPDATE mepa_asignacion_caja_chica 
								SET saldo_disponible = 0,
									se_solicito_devolucion = 1,
								    fecha_devolucion = '".$modal_form_param_fecha."',
								    aplica_voucher = 1,
								    tipo_devolucion = '".$modal_form_param_tipo_devolucion."',
								    imagen = '".$nombreFileUpload."',
								    extension = '".$file_extension."',
								    size = '".$file_size."',
								    ruta = '".$path."',
								    download = '".$nombreDownload."',
								    user_id_carga_voucher = '".$login["id"]."',
								    confirmacion_tesoreria = 0
							WHERE id = '".$modal_form_id_asignacion."'
							";
			}
			
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = "Por favor seleccionar un archivo.";

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		if($modal_form_param_tipo_devolucion == 11)
		{
			$query_update = "
						UPDATE mepa_asignacion_caja_chica 
							SET se_solicito_devolucion = 1,
							    fecha_devolucion = '".$modal_form_param_fecha."',
							    aplica_voucher = 0,
							    tipo_devolucion = '".$modal_form_param_tipo_devolucion."',
							    user_id_carga_voucher = '".$login["id"]."',
							    confirmacion_tesoreria = 0
						WHERE id = '".$modal_form_id_asignacion."'
						";
		}
		else
		{
			$query_update = "
						UPDATE mepa_asignacion_caja_chica 
							SET saldo_disponible = 0,
								se_solicito_devolucion = 1,
							    fecha_devolucion = '".$modal_form_param_fecha."',
							    aplica_voucher = 0,
							    tipo_devolucion = '".$modal_form_param_tipo_devolucion."',
							    user_id_carga_voucher = '".$login["id"]."',
							    confirmacion_tesoreria = 0
						WHERE id = '".$modal_form_id_asignacion."'
						";
		}
		
	}

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$error .= $mysqli->error;
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos registrados correctamente.";
		$result["error"] = $error;

		echo json_encode($result);
		exit();
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = $error;

		echo json_encode($result);
		exit();
	}

}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_devolucion_asignacion_tesoreria_confirmar_deolucion")
{
	$param_asignacion_id = $_POST["param_asignacion_id"];
	
	$usuario_id = $login?$login['id']:null;
	$error = '';

	$query_update = "
					UPDATE mepa_asignacion_caja_chica 
						SET saldo_disponible = 0,
							confirmacion_tesoreria = 1,
						    fecha_tesoreria = '".date('Y-m-d')."',
						    user_id_confirmacion_tesoreria = '".$usuario_id."'
					WHERE id = '".$param_asignacion_id."'
					";

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$error .= $mysqli->error;
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos registrados correctamente.";
		$result["error"] = $error;

		echo json_encode($result);
		exit();
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = $error;

		echo json_encode($result);
		exit();
	}

}

?>