<?php  
date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_grupo_asignacion") {
    $usuario_id = $login ? $login['id'] : null;

    if ((int)$usuario_id > 0) {
        $grupo_id = $_POST["grupo_id"];
        $titulo = $_POST["titulo"];
        $descripcion = $_POST["descripcion"];
        $reportar_gerencia = $_POST["reportar_gerencia"];
        $usuario_creador_id = $_POST["usuario_creador_id"];
        $usuario_aprobador_id = $_POST["usuario_aprobador_id"];
        $fecha = date("Y-m-d H:i:s");

        $error = '';

        $mysqli->autocommit(false);

        try {
            if ((int)$grupo_id > 0) {
                // Actualizar grupo existente
                $stmt_update_grupo = $mysqli->prepare("
                    UPDATE mepa_usuario_asignacion
                    SET titulo = ?, descripcion = ?, reportar_gerencia = ?, status = 1, user_updated_id = ?, updated_at = ?
                    WHERE id = ?
                ");

                $stmt_update_grupo->bind_param("sssiss", $titulo, $descripcion, $reportar_gerencia, $usuario_id, $fecha, $grupo_id);
                $stmt_update_grupo->execute();

                if ($stmt_update_grupo->error) {
                    throw new Exception('Error al actualizar el grupo: ' . $stmt_update_grupo->error);
                }

                $stmt_update_grupo->close();

                // Actualizar o crear usuario creador
                actualizarUsuarioAsignacionDetalle($mysqli, $grupo_id, $usuario_creador_id, 1, $usuario_id, $fecha);

                // Actualizar o crear usuario aprobador
                actualizarUsuarioAsignacionDetalle($mysqli, $grupo_id, $usuario_aprobador_id, 2, $usuario_id, $fecha);
            } else {
                // Crear nuevo grupo
                $stmt_insert_grupo = $mysqli->prepare("
                    INSERT INTO mepa_usuario_asignacion (titulo, descripcion, reportar_gerencia, status, user_created_id, created_at, user_updated_id, updated_at)
                    VALUES (?, ?, ?, 1, ?, ?, ?, ?)
                ");

                $stmt_insert_grupo->bind_param("ssiisis", $titulo, $descripcion, $reportar_gerencia, $usuario_id, $fecha, $usuario_id, $fecha);
                $stmt_insert_grupo->execute();

                if ($stmt_insert_grupo->error) {
                    throw new Exception('Error al insertar el nuevo grupo: ' . $stmt_insert_grupo->error);
                }

                $insert_grupo_id = $stmt_insert_grupo->insert_id;
                $stmt_insert_grupo->close();

                // Crear usuario creador
                crearUsuarioAsignacionDetalle($mysqli, $insert_grupo_id, $usuario_creador_id, 1, $usuario_id, $fecha);

                // Crear usuario aprobador
                crearUsuarioAsignacionDetalle($mysqli, $insert_grupo_id, $usuario_aprobador_id, 2, $usuario_id, $fecha);
            }

            $mysqli->commit();
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión";
        } catch (Exception $e) {
            $mysqli->rollback();
            $result["http_code"] = 400;
            $result["error"] = $e->getMessage();
        }

        $mysqli->autocommit(true);
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

function actualizarUsuarioAsignacionDetalle($mysqli, $grupo_id, $usuario_id, $rol_id, $updated_id, $fecha)
{
    $stmt_select_usuario = $mysqli->prepare("
        SELECT uad.id, uad.usuario_id
        FROM mepa_usuario_asignacion_detalle uad
        INNER JOIN mepa_usuario_asignacion ua ON ua.id = uad.mepa_usuario_asignacion_id
        WHERE uad.mepa_asignacion_rol_id = ? AND ua.id = ? AND uad.status = 1
        LIMIT 1
    ");

    $stmt_select_usuario->bind_param("ii", $rol_id, $grupo_id);
    $stmt_select_usuario->execute();
    $stmt_select_usuario->bind_result($id, $existing_user_id);
    $stmt_select_usuario->fetch();
    $stmt_select_usuario->close();

    if ((int)$existing_user_id !== (int)$usuario_id) {
        // Eliminar usuario existente
        $stmt_delete_usuario = $mysqli->prepare("
            UPDATE mepa_usuario_asignacion_detalle
            SET status = 0, user_updated_id = ?, updated_at = ?
            WHERE id = ?
        ");

        $stmt_delete_usuario->bind_param("isi", $updated_id, $fecha, $id);
        $stmt_delete_usuario->execute();
        $stmt_delete_usuario->close();

        // Crear nuevo usuario
        crearUsuarioAsignacionDetalle($mysqli, $grupo_id, $usuario_id, $rol_id, $updated_id, $fecha);
    }
}

function crearUsuarioAsignacionDetalle($mysqli, $grupo_id, $usuario_id, $rol_id, $created_id, $fecha)
{
    $stmt_insert_usuario = $mysqli->prepare("
        INSERT INTO mepa_usuario_asignacion_detalle (mepa_usuario_asignacion_id, usuario_id, mepa_asignacion_rol_id, status, user_created_id, created_at, user_updated_id, updated_at)
        VALUES (?, ?, ?, 1, ?, ?, ?, ?)
    ");

    $stmt_insert_usuario->bind_param("iiissss", $grupo_id, $usuario_id, $rol_id, $created_id, $fecha, $created_id, $fecha);
    $stmt_insert_usuario->execute();
    $stmt_insert_usuario->close();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_usuario_integrante") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$grupo_id = $_POST["grupo_id"];
        (int)$usuario_integrante_id = $_POST["usuario_integrante_id"];

        if ((int)$grupo_id > 0) {
            $error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_usuario_integrante = "
                INSERT INTO mepa_usuario_asignacion_detalle (
                    mepa_usuario_asignacion_id,
                    usuario_id,
                    mepa_asignacion_rol_id,
                    status,
                    user_created_id,
                    created_at,
                    user_updated_id,
                    updated_at
                )  
                VALUES (?, ?, 3, 1, ?, ?, ?, ?)
            ";

            $stmt = $mysqli->prepare($sql_insert_usuario_integrante);


            if ($stmt) {
                $stmt->bind_param("iiisis", $grupo_id, $usuario_integrante_id, $usuario_id, $fecha, $usuario_id, $fecha);

                try {
                    $stmt->execute();
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe el grupo";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_usuario_integrante") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id = $_POST["id"];

        if ((int)$id > 0) {
            $error = '';

            // Utilizar consultas preparadas para prevenir inyecciones SQL
            $query_update_usuario_creador = "
                UPDATE mepa_usuario_asignacion_detalle 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

            $stmt = $mysqli->prepare($query_update_usuario_creador);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id);

            if ($stmt->execute()) {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestion.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe el usuario";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_reporte_grupo")
{
	require_once '../phpexcel/classes/PHPExcel.php';

	global $mysqli;

	$area_id = $_POST['area_id']; 
	$fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin = $_POST['fecha_fin'];

    $login_usuario_id = $login?$login['id']:null;

    $where_redes = "";

    $select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
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

	$where_area = "";
	$where_fecha_inicio = "";

	if (!Empty($area_id))
	{
		$where_area = " AND p.area_id = '".$area_id."' ";
	}

	if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
		$where_fecha_inicio = " AND ua.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!Empty($fecha_inicio)) {
		$where_fecha_inicio = " AND ua.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!Empty($fecha_fin)) {
		$where_fecha_inicio = " AND ua.created_at <= '$fecha_fin 23:59:59'";
	}

	$query = "
		SELECT
			ua.id,
			ua.titulo,
			ua.descripcion,
			concat(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS nombre,
			CASE
				WHEN reportar_gerencia = 1 THEN 'Si'
				ELSE 'No'
				END AS reportar_gerencia,
			p.area_id,
			ar.nombre AS area,
			uc.usuario,
			p.correo,
			r.nombre AS rol,
			p.dni,
			DATE_FORMAT(STR_TO_DATE(ua.created_at, '%Y-%m-%d %H:%i:%s'), '%d-%m-%Y %H:%i:%s') AS fecha_registro
		FROM
			mepa_usuario_asignacion_detalle uad
		RIGHT JOIN mepa_usuario_asignacion ua ON uad.mepa_usuario_asignacion_id = ua.id
		INNER JOIN tbl_usuarios uc ON uad.usuario_id = uc.id
		INNER JOIN tbl_personal_apt p ON uc.personal_id = p.id
        INNER JOIN tbl_razon_social rs
        ON p.razon_social_id = rs.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		INNER JOIN mepa_asignacion_rol r ON  uad.mepa_asignacion_rol_id = r.id
		WHERE ua.status = 1 AND uad.status=1
            $where_redes
			$where_area
			$where_fecha_inicio
		ORDER BY ua.id, uad.id;
		";
	$list_query = $mysqli->query($query);

	if($list_query->num_rows == 0){
		// retorna mensaje indicando que no hay data
		echo json_encode(array(
			"estado_archivo" => 2
		));
		exit();
	}
	if ($list_query->num_rows > 0){
		$objPHPExcel = new PHPExcel();
					
		$objPHPExcel->getProperties()->setCreator("AT")->setDescription("Reporte");
			
		$tituloReporte = "Lista Detallada de grupos de asignación - Caja Chica";
			
		$titulosColumnas = array(
				'N°',
				'Titulo',
				'Descripción',
				'Reporte a Gerencia',
				'Nombres',
				'Area',
				'Usuario',
				'Correo',
				'Rol',
				'DNI',
				'Fecha Registro'
			);
			
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', $titulosColumnas[0])
				->setCellValue('B1', $titulosColumnas[1])
				->setCellValue('C1', $titulosColumnas[2])
				->setCellValue('D1', $titulosColumnas[3])
				->setCellValue('E1', $titulosColumnas[4])
				->setCellValue('F1', $titulosColumnas[5])
				->setCellValue('G1', $titulosColumnas[6])
				->setCellValue('H1', $titulosColumnas[7])
				->setCellValue('I1', $titulosColumnas[8])
				->setCellValue('J1', $titulosColumnas[9])
				->setCellValue('K1', $titulosColumnas[10]);
			
		$i = 2;
	
        $cont=1;
        while ($row = $list_query->fetch_array()) {
                        $id = $row["id"];
                        $titulo = $row["titulo"];
                        $descripcion = $row["descripcion"];
                        $reportar_gerencia = $row["reportar_gerencia"];
						$nombre = $row["nombre"];
						$area = $row["area"];
						$usuario = $row["usuario"];
						$correo = $row["correo"];
						$rol = $row["rol"];
						$dni = $row["dni"];
						$fecha_registro = $row["fecha_registro"];

                        $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A'.$i, $cont)
                            ->setCellValue('B'.$i, $titulo)
                            ->setCellValue('C'.$i, $descripcion)
                            ->setCellValue('D'.$i, $reportar_gerencia)
                            ->setCellValue('E'.$i, $nombre)
                            ->setCellValue('F'.$i, $area)
                            ->setCellValue('G'.$i, $usuario)
							->setCellValue('H'.$i, $correo)
                            ->setCellValue('I'.$i, $rol)
							->setCellValue('J'.$i, $dni)
                            ->setCellValue('K'.$i, $fecha_registro);
                
                        $i++;
						$cont++;
                    }
		}
            
        $estiloNombresFilas = array(
            'font' => array(
            'name'      => 'Calibri',
            'bold'      => true,
            'italic'    => false,
            'strike'    => false,
            'size' =>11,
            'color'     => array(
            'rgb' => '000000'
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
            
        $estiloColoFondo = array(
            'fill' => array(
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
            'rgb' => '000080')
                )
                );
                  
        $estiloTituloColumnas = array(
            'font' => array(
            'name'  => 'Calibri',
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
                  
        $estiloInformacion = new PHPExcel_Style();
        $estiloInformacion->applyFromArray( array(
            'font' => array(
            'name'  => 'Calibri',
            'italic'    => false,
            'strike'    => false,
            'size' =>10,
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
        $estiloInformacionLeft = new PHPExcel_Style();
        $estiloInformacionLeft->applyFromArray( array(
                    'font' => array(
                        'name'  => 'Calibri',
                        'italic'    => false,
                        'strike'    => false,
                        'size' =>10,
                        'color' => array(
                            'rgb' => '000000'
                        )
                        ),
                    'alignment' =>  array(
                        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Cambiado de CENTER a LEFT
                        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap'      => false
                    )
                ));
    
        $estiloInformacionNumero = new PHPExcel_Style();
        $estiloInformacionNumero->applyFromArray( array(
                            'font' => array(
                                'name'  => 'Calibri',
                                'italic'    => false,
                                'strike'    => false,
                                'size' =>10,
                                'color' => array(
                                    'rgb' => '000000'
                                )
                                ),
                            'numberformat' => array(
                                    'code' => '#,##0.00' // Formato de separación de miles por comas y decimales por puntos
                            )
                        ));
            
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        for ($i = 2; $i <= $objPHPExcel->getActiveSheet()->getHighestRow(); $i++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(18);
            }
            
        $objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray($estiloNombresFilas);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($estiloTituloColumnas);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($estiloColoFondo);
                
    	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AI".($i-1));
            
        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "B2:B2".($i-1));
 
        for($i = 'B'; $i <= 'K'; $i++)
            {
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
            }
            
        $objPHPExcel->getActiveSheet()->setTitle('Lista de grupos de asignación - Caja Chica');
                  
        $objPHPExcel->setActiveSheetIndex(0);
                
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(0);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Grupos_Asignación_Caja-Chica.xls');
        header('Cache-Control: max-age=0');
            
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    	$filename = "Grupos Asignación - Caja Chica Detalle " . $fechaSolicitud . ".xls";
        $excel_path = '/var/www/html/files_bucket/mepa/descargas/' . $filename;
    
        // $excel_path_download = '/files_bucket/kasnet/'. $filename;
        $path = "/var/www/html/files_bucket/mepa/descargas/";
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            }
        $excel_path_download='/files_bucket/mepa/descargas/' . $filename;
        $objWriter->save($excel_path);
       
        $data_return = array(
                    "ruta_archivo" => '/files_bucket/mepa/descargas/' . $filename,
                    "tipo" => "excel",
					"estado_archivo" => 1,
                    "ext" => "xls",
                    "size" => filesize($excel_path),
                    "fecha_registro" => date("Y-m-d h:i:s"),
                );
        echo json_encode(array(
			"ruta_archivo" => $excel_path_download,
			"estado_archivo" => 1
			));
		exit;
}


?>

