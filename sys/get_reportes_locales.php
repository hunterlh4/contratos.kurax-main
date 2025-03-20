<?php
include("db_connect.php");
include("sys_login.php");

if (isset($_POST['accion'])) {
	$inputs = json_decode(json_encode($_POST));
	switch ($_POST['accion']) {
		case 'listar_zonas_departamentos':
			$data_result_departamentos 				= fnc_list_departamentos();
			$data_result_zones 				= fnc_list_zones();
            $data_result_redes 	= fnc_list_redes();
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["data"] = [
				'departamentos' => $data_result_departamentos,
				'zones' => $data_result_zones,
                'redes' => $data_result_redes
			];
			echo json_encode($result);
			break;
        case 'export_reporte_locales':
			$data_result = locales_report_export($inputs);
			echo json_encode($data_result);
			break;
		default:
			# code...
			break;
	}
}

function locales_report_export($inputs)
{
	global $mysqli, $login;
	$list_where = '';

    if ($inputs->zona != 0) 		$list_where .= " AND l.zona_id=" . $inputs->zona;
    if ($inputs->red != 0) 		$list_where .= " AND l.red_id=" . $inputs->red;
	if ($inputs->estado != -1) 	$list_where .= " AND l.estado =" . $inputs->estado;
    if ($inputs->departamento != 0) 	$list_where .= " AND ud.id =" . $inputs->departamento;
    
	$mysqli->query("START TRANSACTION");

        $row_count_detalle_locales = 0;    
        $query_detalle_locales = 
        "
            SELECT l.id                                               
            , l.cc_id
            , tlrx.nombre  as  red
            , IF(TRIM(l.latitud) = '' OR l.latitud IS NULL OR TRIM(l.longitud) = '' OR l.longitud IS NULL, NULL, CONCAT(TRIM(l.latitud), ',', TRIM(l.longitud))) AS coordenadas
            , l.nombre
            , l.direccion
            , l.descripcion
            , l.email
            , l.phone
            , l.area
            , l.ubigeo_id
            , CASE WHEN l.trastienda = 1 THEN 'Si' ELSE 'No' END as trastienda
            ,lt.nombre AS tipo
            ,cl.nombre AS cliente_nombre
            ,l.zona_id
            ,z.nombre AS zona
            ,cl.razon_social AS cliente_razon_social
            ,el.nombre AS estado_legal
            ,lp.nombre AS agente
            ,IF(
                TRIM(l.latitud) <> '' AND TRIM(l.longitud) <> '',
                CONCAT('https://www.google.com/maps?q=', TRIM(l.latitud), ',', TRIM(l.longitud)),
                NULL
                ) AS enlace_mapa 
            ,lpr.proveedor_id AS num_terminales_kasnet                
            ,IFNULL(e.num_tv_apuestas_virtuales,0) as num_tv_apuestas_virtuales
            ,IFNULL(e.num_tv_apuestas_deportivas,0) as num_tv_apuestas_deportivas
            ,IFNULL(c.num_cpu,0) as num_cpu
            ,IFNULL(c.num_monitores,0) as num_monitores
            ,IFNULL(c.num_autoservicios,0) as num_autoservicios
            ,IFNULL(c.num_allinone,0) as num_allinone
            ,IFNULL(c.num_terminales_hibrido,0) as num_terminales_hibrido
            ,IFNULL(c.num_terminales_antiguo,0) as num_terminales_antiguo
            ,s.internet_proveedor_id
            ,p.nombre AS internet_proveedor_nombre
            ,s.internet_tipo_id
            ,t.nombre AS internet_tipo_nombre
            ,IFNULL(s.num_decos_internet,0) as num_decos_internet
            ,IFNULL(s.num_decos_directv,0) as num_decos_directv
            ,MAX(DISTINCT CASE WHEN wd.id = 1 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS monday
            ,MAX(DISTINCT CASE WHEN wd.id = 2 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS tuesday
            ,MAX(DISTINCT CASE WHEN wd.id = 3 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS wednesday
            ,MAX(DISTINCT CASE WHEN wd.id = 4 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS thursday
            ,MAX(DISTINCT CASE WHEN wd.id = 5 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS friday
            ,MAX(DISTINCT CASE WHEN wd.id = 6 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS saturday
            ,MAX(DISTINCT CASE WHEN wd.id = 0 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS sunday
            ,IFNULL(lc.conteo_cajas, 0) as conteo_cajas
            ,g.nombre_subgerente
            ,jc.jefe_comercial
            ,jc.telefono as js_telefono
            ,CASE l.estado
                WHEN 1 THEN 'Operativo'
                WHEN 0 THEN 'No operativo'
                ELSE 'Cerrado'
            END AS estado
            ,l.fecha_inicio_operacion
            ,ud.id 
        FROM tbl_locales l
        LEFT JOIN (
            SELECT zona_id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_subgerente FROM tbl_personal_apt WHERE cargo_id=29
            ) AS g ON g.zona_id = l.zona_id 
            LEFT JOIN (
            SELECT zona_id, telefono, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS jefe_comercial FROM tbl_personal_apt WHERE cargo_id=16
            ) AS jc ON jc.zona_id = l.zona_id
        LEFT JOIN tbl_ubigeo_departamentos ud ON SUBSTRING(l.ubigeo_id, 1, 2)=ud.id
        LEFT JOIN (SELECT local_id, proveedor_id
            FROM tbl_local_proveedor_id
            WHERE canal_de_venta_id = 28 AND estado = 1
            GROUP BY local_id) lpr ON l.id = lpr.local_id 
        LEFT JOIN tbl_local_tipo lt ON (lt.id = l.tipo_id)
        LEFT JOIN tbl_locales_redes tlrx on tlrx.id =l.red_id  
        LEFT JOIN tbl_clientes cl ON (cl.id = l.cliente_id)
        LEFT JOIN tbl_zonas z ON (z.id = l.zona_id)
        LEFT JOIN tbl_local_estado_legal el ON (el.id = l.estado_legal_id)
        LEFT JOIN tbl_personal_apt lp ON  (lp.id = l.asesor_id)                                 
        LEFT JOIN tbl_locales_equipos e ON l.id = e.local_id
        LEFT JOIN tbl_locales_equipos_computo c ON l.id = c.local_id
        LEFT JOIN tbl_locales_servicios s ON l.id = s.local_id
        LEFT JOIN tbl_internet_proveedor p ON s.internet_proveedor_id = p.id
        LEFT JOIN tbl_internet_tipo t ON s.internet_tipo_id = t.id
        LEFT JOIN (SELECT local_id, MAX(started_at) AS started_at 
            FROM tbl_locales_horarios 
            GROUP BY local_id) AS lh_max ON lh_max.local_id = l.id
        LEFT JOIN tbl_locales_horarios AS lh ON lh.local_id = l.id AND lh.started_at = lh_max.started_at
        LEFT JOIN tbl_horarios AS h ON h.id = lh.horario_id
        LEFT JOIN tbl_horarios_dias AS hd ON hd.horario_id = h.id
        LEFT JOIN tbl_weekdays AS wd ON wd.id = hd.weekday_id
        LEFT JOIN (SELECT local_id, COUNT(id) AS conteo_cajas 
            FROM tbl_local_cajas 
            GROUP BY local_id) AS lc ON lc.local_id = l.id
        WHERE l.id <> 1367 AND l.id <> 1198 AND l.id <> 1197 AND l.id <> 1368 AND l.id <> 1199 AND l.id <> 1532 AND l.id <> 1 AND l.id <> 786 AND l.id <> 781 AND l.id <> 784 AND l.id <> 785 AND l.id <> 780 AND l.id <> 782 AND l.id <> 787 AND l.id <> 783 
        {$list_where}
        GROUP BY l.id
        ORDER BY l.nombre ASC
        ";
        $ubigeos_arr = array();
        $ubigeos_query = $mysqli->query("SELECT id,cod_depa,cod_prov,cod_dist,nombre FROM tbl_ubigeo");
        while($ubg=$ubigeos_query->fetch_assoc()){
            $ubg_id=$ubg["cod_depa"].$ubg["cod_prov"].$ubg["cod_dist"];
            $ubigeos_arr[$ubg_id]=$ubg["nombre"];
        }
    
        $list_query_locales = $mysqli->query($query_detalle_locales);
    
        $row_count_detalle_locales = $list_query_locales->num_rows;
    
        require_once '../phpexcel/classes/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
    
        // Se asignan las propiedades del libro
        $objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
        ->setDescription("Reporte"); //Descripción
    
        $tituloReporte = "Lista Detallada de locales";
    
                        $list_cols["zona"]="Zona";
        $titulosColumnas = array('CC',"Red", 'Nombre', 'Departamento', 'Provincia', 'Distrito', 'Dirección', 'Ubicación G. Maps', 'Proveedor de Internet', 'Tipo de Internet', '# DECOS MOVISTAR', '# DECOS DIRECTV', '# de CPU', '# de Monitores',  '# de terminal KASNET','Trastienda', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado','Domingo', '# de cajas operativas', '# de autoservicios', '# de AIO', '# de terminales híbrido', '# de terminales antiguos', '# de televisores virtuales', '# de televisores apuestas deportivas', 'Subgerencia', 'Zona', 'Jefe Comercial', 'Celular del jefe comercial', 'Celular de la tienda', 'Correo de la tienda', 'Status diario', 'Fecha de apertura');
    
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
                    ->setCellValue('AC1', $titulosColumnas[28])
                    ->setCellValue('AD1', $titulosColumnas[29])
                    ->setCellValue('AE1', $titulosColumnas[30])
                    ->setCellValue('AF1', $titulosColumnas[31])
                    ->setCellValue('AG1', $titulosColumnas[32])
                    ->setCellValue('AH1', $titulosColumnas[33])
                    ->setCellValue('AI1', $titulosColumnas[34])
                    ->setCellValue('AJ1', $titulosColumnas[35])
                    ->setCellValue('AK1', $titulosColumnas[36])
                    ->setCellValue('AL1', $titulosColumnas[37])
                    //->setCellValue('AL1', $titulosColumnas[37])
                    //->setCellValue('AM1', $titulosColumnas[38])
                    //->setCellValue('AN1', $titulosColumnas[39])
                    ;
    
        //Se agregan los datos a la lista del reporte
        $i = 2; //Numero de fila donde se va a comenzar a rellenar
    
        $monto_maximo_concar = 41;
        $monto_restante = 0;
        $var_indice_movilidad = 0;
    
        if($row_count_detalle_locales > 0)
        {
            while ($row = $list_query_locales->fetch_array())
            {
                $row["departamento"]=@$ubigeos_arr[substr($row["ubigeo_id"], 0,2)."0000"];
                $row["provincia"]=@$ubigeos_arr[substr($row["ubigeo_id"], 0,4)."00"];;
                $row["distrito"]=@$ubigeos_arr[$row["ubigeo_id"]];			
                $cc_id = $row["cc_id"];
                $red = $row["red"];
                $nombre = $row["nombre"];
                $departamento = $row["departamento"];
                $provincia = $row["provincia"];
                $distrito = $row["distrito"];
                $direccion = $row["direccion"];
                $enlace_mapa = $row["enlace_mapa"];
                $internet_proveedor_nombre = $row["internet_proveedor_nombre"];
                $internet_tipo_nombre = $row["internet_tipo_nombre"];
                $num_decos_internet = $row["num_decos_internet"];
                $num_decos_directv = $row["num_decos_directv"];
                $num_cpu = $row["num_cpu"];
                $num_monitores = $row["num_monitores"];
                $num_terminales_kasnet = $row["num_terminales_kasnet"];
                $trastienda = $row["trastienda"];
                $monday = $row["monday"];
                $tuesday = $row["tuesday"];
                $wednesday = $row["wednesday"];
                $thursday = $row["thursday"];
                $friday = $row["friday"];
                $saturday = $row["saturday"];
                $sunday = $row["sunday"];
                $conteo_cajas = $row["conteo_cajas"];
                $num_autoservicios = $row["num_autoservicios"];
                $num_allinone = $row["num_allinone"];
                $num_terminales_hibrido = $row["num_terminales_hibrido"];
                $num_terminales_antiguo = $row["num_terminales_antiguo"];
                $num_tv_apuestas_virtuales = $row["num_tv_apuestas_virtuales"];
                $num_tv_apuestas_deportivas = $row["num_tv_apuestas_deportivas"];
                $nombre_subgerente = $row["nombre_subgerente"];
                $zona = $row["zona"];
                $jefe_comercial = $row["jefe_comercial"];
                $js_telefono = $row["js_telefono"];
                //$supervisor = $row["supervisor"];
               // $s_telefono = $row["s_telefono"];
                //$s_correo = $row["s_correo"];
                $phone = $row["phone"];
                $email = $row["email"];
                $estado = $row["estado"];
                $fecha_inicio_operacion = $row["fecha_inicio_operacion"];
    
                $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue('A'.$i, $cc_id)
                                ->setCellValue('B'.$i, $red)
                                ->setCellValue('C'.$i, $nombre)
                                ->setCellValue('D'.$i, $departamento)
                                ->setCellValue('E'.$i, $provincia)
                                ->setCellValue('F'.$i, $distrito)
                                ->setCellValue('G'.$i, $direccion)
                                ->setCellValue('H'.$i, $enlace_mapa)
                                ->setCellValue('I'.$i, $internet_proveedor_nombre)
                                ->setCellValue('J'.$i, $internet_tipo_nombre)
                                ->setCellValue('K'.$i, $num_decos_internet)
                                ->setCellValue('L'.$i, $num_decos_directv)
                                ->setCellValue('M'.$i, $num_cpu)
                                ->setCellValue('N'.$i, $num_monitores)
                                ->setCellValue('O'.$i, $num_terminales_kasnet)
                                ->setCellValue('P'.$i, $trastienda)
                                ->setCellValue('Q'.$i, $monday)
                                ->setCellValue('R'.$i, $tuesday)
                                ->setCellValue('S'.$i, $wednesday)
                                ->setCellValue('T'.$i, $thursday)
                                ->setCellValue('U'.$i, $friday)
                                ->setCellValue('V'.$i, $saturday)
                                ->setCellValue('W'.$i, $sunday)
                                ->setCellValue('X'.$i, $conteo_cajas)
                                ->setCellValue('Y'.$i, $num_autoservicios)
                                ->setCellValue('Z'.$i, $num_allinone)
                                ->setCellValue('AA'.$i, $num_terminales_hibrido)
                                ->setCellValue('AB'.$i, $num_terminales_antiguo)
                                ->setCellValue('AC'.$i, $num_tv_apuestas_virtuales)
                                ->setCellValue('AD'.$i, $num_tv_apuestas_deportivas)
                                ->setCellValue('AE'.$i, $nombre_subgerente)
                                ->setCellValue('AF'.$i, $zona)
                                ->setCellValue('AG'.$i, $jefe_comercial)
                                ->setCellValue('AH'.$i, $js_telefono)
                                //->setCellValue('AH'.$i, $supervisor)
                                //->setCellValue('AI'.$i, $s_telefono)
                                //->setCellValue('AJ'.$i, $s_correo)
                                ->setCellValue('AI'.$i, $phone)
                                ->setCellValue('AJ'.$i, $email)
                                ->setCellValue('AK'.$i, $estado)
                                ->setCellValue('AL'.$i, $fecha_inicio_operacion);
                $i++;
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
                    'rgb' => '900C0C')
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
    
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
            // Recorre todas las filas mayores a 1 y aplica el ajuste automático de altura
            for ($i = 2; $i <= $objPHPExcel->getActiveSheet()->getHighestRow(); $i++) {
                $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(18);
            }
    
        $objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray($estiloNombresFilas);
    
        $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloTituloColumnas);
        $objPHPExcel->getActiveSheet()->getStyle('AA1:AL1')->applyFromArray($estiloTituloColumnas);
    
        $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($estiloColoFondo);
        $objPHPExcel->getActiveSheet()->getStyle('AA1:AL1')->applyFromArray($estiloColoFondo);
        
        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AK".($i-1));
    
        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "B2:B2".($i-1));
        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "F2:F2".($i-1));
        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "G2:G2".($i-1));
        //$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacionLeft, "AL2:AL2".($i-1));
    
        // ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
        for($i = 'B'; $i <= 'Z'; $i++)
        {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }
    
        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Lista de locales detallada');
          
        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);
        
        // Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(0);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="LocalesDetallado.xls');
        header('Cache-Control: max-age=0');
    
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
   
       $filename = "LOCALES_20042023 [LOCALES_" . date("dmY") . "].xls";
       $excel_path = '/var/www/html/export/files_export/reporte_locales/' . $filename;
       
       $path = "/var/www/html/export/files_export/reporte_locales/";
       if (!is_dir($path)) {
           mkdir($path, 0777, true);
       }
       $objWriter->save($excel_path);
   
       $data_return = array(
           "path" => '/export/files_export/reporte_locales/' . $filename,
           "tipo" => "excel",
           "ext" => "xls",
           "size" => filesize($excel_path),
           "fecha_registro" => date("Y-m-d h:i:s"),
       );
       return $data_return;
}

function fnc_list_zones()
{
	global $mysqli;
	$query = "
	SELECT id, nombre FROM tbl_zonas
	";
	$result_query = $mysqli->query($query);
	while ($li = $result_query->fetch_assoc()) {
		$data_return[] = $li;
	}
	return $data_return;
}
function fnc_list_departamentos()
{
	global $mysqli;
	$query = "
    SELECT IF(id = 7, NULL, @num := @num + 1) as id,nombre
    FROM tbl_ubigeo_departamentos, (SELECT @num := 0) as num_init
    WHERE id <> 7
        ";
	$result_query = $mysqli->query($query);
	while ($li = $result_query->fetch_assoc()) {
		$data_return[] = $li;
	}
	return $data_return;
}
function fnc_list_redes()
{
	global $mysqli;
	$query = "
    SELECT * FROM tbl_locales_redes tlr ";
	$result_query = $mysqli->query($query);
	while ($li = $result_query->fetch_assoc()) {
		$data_return[] = $li;
	}
	return $data_return;
}



