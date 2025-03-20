<?php
include("db_connect.php");
include("sys_login.php");
include("globalFunctions/generalInfo/personal.php");


if(isset($_POST["sec_personal_get_tabla_usuarios"])){
	$data = $_POST["sec_personal_get_tabla_usuarios"];

	$data['offset'] = $data['limit']*$data['page'];

	$list_where = " WHERE tpa.estado = '1'";
	if(isset($data['inactivo'])) $list_where = " WHERE tpa.estado != '1'";
	
	if($data['filter'] != ""){
		$list_where .= " AND (
			tpa.nombre LIKE '%{$data['filter']}%' OR
			tpa.apellido_paterno LIKE '%{$data['filter']}%' OR
			tpa.apellido_materno LIKE '%{$data['filter']}%' OR
			tpa.dni LIKE '%{$data['filter']}%' OR
			tpa.telefono LIKE '%{$data['filter']}%' OR
			tpa.celular LIKE '%{$data['filter']}%' OR
			tpa.correo LIKE '%{$data['filter']}%' OR
			z.nombre LIKE '%{$data['filter']}%' OR
			a.nombre LIKE '%{$data['filter']}%' OR
			e.nombre LIKE '%{$data['filter']}%' OR
			c.nombre LIKE '%{$data['filter']}%'
		) ";
        //tpa.fecha_ingreso_laboral LIKE '%{$data['filter']}%' OR
	}

	$list_query=$mysqli->query("
		SELECT 
		    tpa.id,
		    tpa.nombre,
		    tpa.apellido_paterno,
		    tpa.apellido_materno,
		    tpa.fecha_ingreso_laboral,
		    tpa.consorcio_id,
		    tpa.dni,
		    tpa.telefono,
		    tpa.celular,
		    tpa.correo,
		    c.nombre as cargo_nombre,
		    a.nombre as area_nombre,
		    z.nombre as zona_nombre,
		    tpa.sistema_id,
		    e.nombre AS empresa,
		    tpa.estado
		FROM
		    tbl_personal_apt  tpa
		LEFT JOIN
		    tbl_areas a ON tpa.area_id  = a.id
		LEFT JOIN
		    tbl_zonas z ON tpa.zona_id  = z.id
		LEFT JOIN
		    tbl_cargos c ON tpa.cargo_id=c.id
	    LEFT JOIN 
			tbl_razon_social e ON tpa.razon_social_id = e.id {$list_where}
		ORDER BY tpa.id ASC
		LIMIT ".$data['limit']." OFFSET ".$data['offset']);
	$list=array();

	while ($li=$list_query->fetch_assoc()) {
		$list[]=$li;
	}

	$num_rows = $mysqli->query("SELECT 
		    tpa.id,
		    tpa.nombre,
		    tpa.apellido_paterno,
		    tpa.apellido_materno,
		    tpa.fecha_ingreso_laboral,
		    tpa.consorcio_id,
		    tpa.dni,
		    tpa.telefono,
		    tpa.celular,
		    tpa.correo,
		    c.nombre as cargo_nombre,
		    a.nombre as area_nombre,
		    z.nombre as zona_nombre,
		    tpa.sistema_id,
		    e.nombre AS empresa,
		    tpa.estado
		FROM
		    tbl_personal_apt  tpa
		LEFT JOIN
		    tbl_areas a ON tpa.area_id  = a.id
		LEFT JOIN
		    tbl_zonas z ON tpa.zona_id  = z.id
		LEFT JOIN
		    tbl_cargos c ON tpa.cargo_id=c.id 
		LEFT JOIN 
			tbl_razon_social e ON tpa.razon_social_id = e.id {$list_where}")->num_rows;

	$list_cols = array();
	$list_cols["id"]="ID";

	$list_cols["nombre"]="NOMBRE";
	$list_cols["apellido_paterno"]="AP. PATERNO";
	$list_cols["apellido_materno"]="AP. MATERNO";
	// $list_cols["fecha_ingreso_laboral"]="F. INGRESO";
	/*
		$list_cols["consorcio_id"]="CONSORCIO";
	*/	
	$list_cols["dni"]="DNI";
	$list_cols["telefono"]="TELEFONO";
	$list_cols["celular"]="CELULAR";
	$list_cols["correo"]="CORREO";
	$list_cols["zona_nombre"]="ZONA";	
	$list_cols["area_nombre"]="AREA";
	$list_cols["empresa"]="EMPRESA";	
	$list_cols["cargo_nombre"]="CARGO";		
	//$list_cols["sistema_id"]="SISTEMA";
	$list_cols["estado"]="ESTADO";
	$list_cols["opciones"]="OPCIONES";

	$body = "";

	$body .= '<thead>';
	$body .= '<tr>';
	foreach ($list_cols as $key => $value) {
		if($key=="id") 				$body .= '<th class="w-20px">ID</th>';
		elseif($key=="opciones") 	$body .= '<th class="w-100px">OPCIONES</th>';
		elseif($key=="estado") 		$body .= '<th class="w-65px">ESTADO</th>';
		else 						$body .= '<th>'.$value.'</th>';
	}
	$body .= '</tr>';
	$body .= '</thead>';
	$body .= '<tbody>';
	foreach ($list as $l_k => $l_v) {
		$body .= '<tr>';
		foreach ($list_cols as $key => $value) {
			if($key=="opciones"){
				$body .= '<td class="text-center">';
				$body .= '<a ';
				$body .= 'class="btn btn-rounded btn-default btn-sm btn-edit btn_editar_personal" ';
				$body .= 'title="Editar" ';
				$body .= 'data-button="edit" ';
				$body .= 'data-href="./?sec_id=personal&amp;item_id='.$l_v["id"].'"';
				$body .= 'href="./?sec_id=personal&amp;item_id='.$l_v["id"].'">';
				$body .= '<i class="glyphicon glyphicon-edit"></i>';
				$body .= '</a>';
				if(array_key_exists(40,$usuario_permisos) && in_array("log_personal", $usuario_permisos[40])){
				$body .= '<a ';
				$body .= 'class="btn btn-rounded btn-sm btn-default btn_log_personal btn-degradado" style="color:black" ';
				$body .= 'title="Historial de cambios de personal" ';
				$body .= 'data-button="log" ';
				$body .= 'data-href="./?sec_id=personal&amp;item_id='.$l_v["id"].'"';
				$body .= 'href="./?sec_id=personal&sub_sec_id=log_personal&amp;item_id='.$l_v["id"].'">';
				$body .= '<i class="glyphicon glyphicon-time"></i>';
				$body .= '</a>';
				}
				$body .= '</td>';
			}elseif($key=="estado"){
				$body .= '<td class="text-center">';
				if($l_v["estado"]) $body .= '<div class="btn btn-sm btn-default text-success btn-estado"><span class="glyphicon glyphicon-ok-circle"></span></div>';
				else $body .= '<div class="btn btn-sm btn-default text-danger btn-estado"><span class="glyphicon glyphicon-remove-circle"></span></div>';
				$body .= '</td>';
			}elseif($key=="id") $body .= '<td class="text-right ">'.$l_v[$key].'</td>';
			else $body .= '<td>'.$l_v[$key].'</td>';
		}
		$body .= '</tr>';
	}
	$body .= '</tbody>';
	echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
}

if(isset($_POST['sec_personal_validar_dni'])) {
	$data = $_POST['sec_personal_validar_dni'];

	$result = validarDNIPersonal($data['dni']);
	
	echo json_encode($result);
	exit();
}

if(isset($_POST['sec_personal_get_log_personal'])) {
	$filtros_fecha = '';
	
	if($_POST['fecha_inicio'] != ''){
		$filtros_fecha .= 'AND pa.created_at >= "'.$_POST['fecha_inicio'].'"';
	}
	if($_POST['fecha_fin'] != ''){
		$filtros_fecha .= 'AND pa.created_at <= "'.$_POST['fecha_fin'].' 23:59:59"';
	}

	$query =   "SELECT 
					pa.personal_id, date_format(pa.created_at, '%d/%m/%Y %H:%i:%s') as fecha, u.usuario as updated_by_nombre, p.nombre, 
					pa.campo as campo_codigo, 
					pa.valor,
					pa.valor_anterior,
					if(pa.campo = 'cargo_id',
							'Cargo',
						if(pa.campo = 'zona_id',
							'Zona',
							if(pa.campo = 'area_id',
								'Área',
								if(pa.campo = 'razon_social_id',
										'Empresa',
									if(pa.campo = 'estado',
										'Estado',
										if(pa.campo = 'apellido_paterno',
												'Apellido paterno',
											if(pa.campo = 'apellido_materno',
												'Apellido materno',
												if(pa.campo = 'nombre',
													'Nombre',
													if(pa.campo = 'correo',
														'Correo',
														if(pa.campo = 'dni',
																'DNI',
															if(pa.campo = 'celular',
																'Celular',
																if(pa.campo = 'telefono',
																	'Teléfono',
																	pa.campo
																)
															)
														)
													)
												)
											)
										)
									)
								)
							)
						)
					) as campo,
					if(pa.campo = 'cargo_id',
						CAST((select concat('[',id,'] ',nombre) from tbl_cargos where id = pa.valor) as CHAR),
						if(pa.campo = 'zona_id',
							CAST((select concat('[',id,'] ',nombre) from tbl_zonas where id = pa.valor) as CHAR),
							if(pa.campo = 'area_id',
								CAST((select concat('[',id,'] ',nombre) from tbl_areas where id = pa.valor) as CHAR),
								if(pa.campo = 'razon_social_id',
									CAST((select concat('[',id,'] ',nombre) from tbl_razon_social where id = pa.valor) as CHAR),
									if(pa.campo = 'estado',
										if(pa.valor = 0, 'Inactivo', 'Activo'),
									pa.valor)
								)
							)
						)
					) as valor_relacionado,
					if(pa.campo = 'cargo_id',
						CAST((select concat('[',id,'] ',nombre) from tbl_cargos where id = pa.valor_anterior) as CHAR),
						if(pa.campo = 'zona_id',
							CAST((select concat('[',id,'] ',nombre) from tbl_zonas where id = pa.valor_anterior) as CHAR),
							if(pa.campo = 'area_id',
								CAST((select concat('[',id,'] ',nombre) from tbl_areas where id = pa.valor_anterior) as CHAR),
								if(pa.campo = 'razon_social_id',
									CAST((select concat('[',id,'] ',nombre) from tbl_razon_social where id = pa.valor_anterior) as CHAR),
									if(pa.campo = 'estado',
										if(pa.valor_anterior = 0, 'Inactivo', 'Activo'),
									pa.valor_anterior)
								)
							)
						)
					) as valor_anterior_relacionado,
					pa.ip 
				FROM tbl_personal_auditoria pa
					INNER JOIN tbl_personal_apt p ON pa.personal_id = p.id
					INNER JOIN tbl_usuarios u ON pa.campo_updated_by = u.id
				WHERE pa.personal_id =  ".$_POST['personal_id']."
				$filtros_fecha
				";
	$result = $mysqli->query($query);
	
	$auditoria = $result->fetch_all(MYSQLI_ASSOC);
	
	$personal = "SELECT *, concat(IFNULL(nombre,''), ' ', IFNULL(apellido_paterno, ''),' ', ifnull(apellido_materno, '')) as nombre_completo FROM  tbl_personal_apt WHERE id = ".$_POST['personal_id'].";";
	$personal = $mysqli->query($personal); 
	
	$personal = $personal->fetch_assoc();
	// echo json_encode($personal);
	// exit();

	$result = [
		'personal' =>  $personal,
		'auditoria' => $auditoria
	];

	echo json_encode($result);
	exit();
}

if(isset($_POST['obtener_zonas_por_empresa'])) {

	$where = 'AND z.razon_social_id != 30';
	if ($_POST['razon_social_id'] == 30) {
		$where = 'AND z.razon_social_id = '.$_POST['razon_social_id'];
	}
	$query = "SELECT z.id, z.nombre FROM tbl_zonas z WHERE 1 = 1 ".$where." ORDER BY z.ord";
	
	$list_query = $mysqli->query($query);

	$zonas = array();
	while ($li = $list_query->fetch_assoc()) {
		$zonas[] = $li;
	}

	$result["http_code"] = 200;
	$result["result"] = $zonas;
	echo json_encode($result);
	exit();
}



?>