<?php
include("db_connect.php");
include("sys_login.php");
require('/var/www/html/sys/globalFunctions/generalInfo/local.php');

if(isset($_POST["accion"]) && $_POST["accion"] === "consolidado_gastos"){
	$fecha_inicio = '2021-01-01';

	$meses = [];

	$date1  = $fecha_inicio;
	$date2  = date("Y-m-d");
	$time   = strtotime($date1);
	$last   = date('Y-m', strtotime($date2));
	$canales_de_venta = [1,2,3,4,5,6,7,8,9,10];

	do {
		$month = date('Y-m', $time);
		$total = date('t', $time);
		$meses[] = $month;
		$time = strtotime('+1 month', $time);
	} while ($month != $last);

	$data = [];
	$cabecera_where = "AND c.id IS NOT NULL";
	$cabecera_where = "AND c.gastos_conceptos_id in (".implode(",",$canales_de_venta).")";
	$locales_command_where = "";
	//$locales_command_where = "AND l.zona_id = ".$_POST["filtro"]["zona_id"]." ";
	$locales_command_where .= "AND l.id IS NOT NULL";
	
	$concepto = "GASTOS";
	$where_supervisores = "";
	$where_zonas = "";
	$where_zonas_filtro ="";
	if(array_key_exists("filtro", $_POST)){
		$filtro = $_POST["filtro"];
		/*
		if(array_key_exists("concepto", $filtro)){
			if($filtro["concepto"]){
				$concepto = $filtro["concepto"];
			}
		}
		*/
		if(array_key_exists("zona_id", $filtro)){

				if(array_key_exists("zona_id", $filtro)){
					if($filtro["zona_id"]){
						if(!in_array("all", $filtro["zona_id"])){
							$where_zonas .= " AND l.zona_id IN ('".implode("','", $filtro["zona_id"])."') ";
							$where_zonas_filtro .= " AND pa.zona_id IN ('".implode("','", $filtro["zona_id"])."') ";
							$locales_command_where .= $where_zonas;
						}
					}
				}
		}
		if(array_key_exists("supervisores", $filtro)){
			if($filtro["supervisores"]){
				if(!in_array("all", $filtro["supervisores"])){
					$where_supervisores .= " AND (SELECT 
													pa.id as personal_id
												FROM tbl_usuarios_locales ul 
												inner join tbl_usuarios u ON ul.usuario_id = u.id
												INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
												LEFT JOIN tbl_zonas z ON z.id = pa.zona_id
												WHERE	
												pa.area_id = 21 
												AND pa.estado = 1
												AND ul.estado = 1
												AND z.razon_social_id = 5
												AND z.status=1 
												$where_zonas_filtro
												AND ul.local_id = l.id 
												LIMIT 1)
							IN ('".implode("','", $filtro["supervisores"])."')";
				}
			}
		}

		if(array_key_exists("locales", $filtro) && $filtro["locales"] && !in_array("all", $filtro["locales"]) ){
			$cabecera_where .= " AND local_id IN ('".implode("','", $filtro["locales"])."')";
			$locales_command_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
		} 
		if($login["usuario_locales"]){
			$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
			$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
		}
	
		if(array_key_exists("estado_locales", $filtro)){
			if($filtro["estado_locales"] == "activos" ){
				$locales_command_where .= " AND l.operativo = 1";
			} else {
				$locales_command_where .= " AND l.operativo = 2";
			}
		}

		if(array_key_exists("canales_de_venta", $filtro)){
			if($filtro["canales_de_venta"]){
				if( !in_array( "all" ,$filtro["canales_de_venta"]) ){
					$cabecera_where .= " AND c.gastos_conceptos_id IN ('".implode("','", $filtro["canales_de_venta"])."')";
				}
				if( in_array( "all" ,$filtro["canales_de_venta"]) ){
					$cabecera_where .= " AND c.gastos_conceptos_id IN (".implode(",",$canales_de_venta).")";
				}
			}
		}
	}
	$cabecera_where .= " AND c.estado = '1'";
	
	$locales = []; //LOCALES

    // Mostrar locales
	$locales_command = "SELECT
			l.id as local_id
			,l.nombre AS 'NOMBRE TIENDA'
			,(SELECT 
					CONCAT(IFNULL(pa.nombre,'Sin supervisor asignado.'), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS supervisor_nombre
				FROM tbl_usuarios_locales ul 
				inner join tbl_usuarios u ON ul.usuario_id = u.id
				INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
				LEFT JOIN tbl_zonas z ON z.id = pa.zona_id
				WHERE	
				pa.area_id = 21 
				AND pa.estado = 1
				AND ul.estado = 1 
				AND z.razon_social_id = 5
				$where_zonas_filtro 
				AND z.status=1 
				AND ul.local_id = l.id  LIMIT 1
			) AS 'NOMBRE SOP'
			,udep.nombre as DEPARTAMENTO
			,up.nombre as PROVINCIA
			,ud.nombre as DISTRITO
			,zdep.nombre as ZONA_DEPARTAMENTO
			,z.nombre as ZONA_NOMBRE
		FROM tbl_locales l
		LEFT JOIN tbl_ubigeo ud ON (
			ud.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			ud.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
			ud.cod_dist = SUBSTRING(l.ubigeo_id, 5, 2)
		)
		LEFT JOIN tbl_ubigeo up ON (
			up.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			up.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
			up.cod_dist = '00'
		)
		LEFT JOIN tbl_ubigeo udep ON (
			udep.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
			udep.cod_prov = '00' AND
			udep.cod_dist = '00'
		)
		LEFT JOIN tbl_ubigeo_departamentos udep2 ON udep2.nombre = udep.nombre
        LEFT JOIN tbl_zonas_departamento zdep ON zdep.id = udep2.zonas_departamento_id
		INNER JOIN tbl_zonas z ON z.id = l.zona_id
		WHERE 1 = 1 
		AND z.razon_social_id=5 
		AND z.status=1 "
	;
    $locales_query = $mysqli->query($locales_command .$locales_command_where .$where_supervisores);
    if($mysqli->error){
        $return["ERROR_MYSQL"] = $mysqli->error;
        print_r($mysqli->error);
        echo $locales_command;
    }    

	$canales_where = " AND id IN (".implode(",",$canales_de_venta).")";
	if(array_key_exists("canales_de_venta", $filtro)){
		if($filtro["canales_de_venta"]){
			if( !in_array( "all" ,$filtro["canales_de_venta"]) ){
				$canales_where .= " AND id IN ('".implode("','", $filtro["canales_de_venta"])."')";
			}
			if( in_array( "all" ,$filtro["canales_de_venta"]) ){
					$canales_where .= " AND id IN (".implode(",",$canales_de_venta).")";
			}
		}
	}
    $cdv_arr = []; // Conceptos de gastos
	$cdv_command = "SELECT id, nombre, codigo FROM tbl_gastos_conceptos WHERE estado = '1' $canales_where ORDER BY id ASC";
	$cdv_query = $mysqli->query($cdv_command);
	while($cdv = $cdv_query->fetch_assoc()){
		$cdv_arr[$cdv["id"]] = $cdv;
	}
	
	$locales2 = [];
    while($lcl = $locales_query->fetch_assoc()){
    	$locales2[] = $lcl;
        foreach($cdv_arr as $cdv_id => $cdv)
        {
        	$lcl["CANAL DE VENTA"] = $cdv["codigo"];
        	$lcl["gastos_conceptos_id"] = $cdv["id"];
        	foreach ($meses as  $mes)
	    	{
	    		$lcl[$mes] = 0;
	        }
        	$locales[] = $lcl;
        }
        $lcl["CANAL DE VENTA"] = "TOTAL";
		$locales[] = $lcl;
    }

    $trans_command = "
				SELECT 
					c.local_id
					,l.nombre as local_nombre
					,c.gastos_conceptos_id
					,DATE_FORMAT(c.fecha,'%Y-%m') AS mes
					,sum(c.monto) AS 'GASTOS'
				FROM tbl_gastos c
				LEFT JOIN tbl_locales l ON l.id = c.local_id
				LEFT JOIN tbl_zonas z ON z.id = l.zona_id
				WHERE 
				c.estado = 1 
				AND l.estado = 1
				AND c.id IS NOT NULL
				AND c.fecha >= '{$fecha_inicio}'  AND c.fecha <= now()
				AND l.reportes_mostrar = 1
				AND c.gastos_conceptos_id in (".implode(",",$canales_de_venta).")
				AND z.razon_social_id = 5
				AND z.status=1 
				$locales_command_where
				$cabecera_where
				GROUP BY DATE_FORMAT(c.fecha,'%Y-%m') ,c.local_id ,c.gastos_conceptos_id
				ORDER BY c.fecha ASC ,local_nombre , c.gastos_conceptos_id";
    $transacciones_query = $mysqli->query($trans_command);
    if($mysqli->error){
        $return["ERROR_MYSQL"] = $mysqli->error;
        print_r($mysqli->error);
    }
    $locales_transacciones = [];
    while($lcl = $transacciones_query->fetch_assoc()){    	
        $locales_transacciones[$lcl["local_id"]][$lcl["mes"]][$lcl["gastos_conceptos_id"]] = $lcl;
    }

	foreach ($locales2 as $id => $data)
	{
		$locales2[$id]["liquidaciones"] = isset($locales_transacciones[$data["local_id"]]) ? $locales_transacciones[$data["local_id"]] : [];
	}	
	/*sum TOTAL local*/
	foreach ($locales2 as $id => $data)
	{
		foreach ($data["liquidaciones"] as $key_fecha => $canales) {
			$total = 0;
			$valores_fila = [];
			foreach ($canales as $key => $value) {
				$total += $value[$concepto];
				$valores_fila = $value;
			}
			$valores_fila[$concepto] = $total;
			$valores_fila["gastos_conceptos_id"] = "TOTAL";
			$locales2[$id]["liquidaciones"][$key_fecha]["TOTAL"] = $valores_fila;
		}
	}

	$totales2 = [];
	foreach ($meses as  $mes)
	{
		$totales2[$mes][$concepto] = 0;
	}
	foreach ($locales2 as $local_id => $local_data) {
		foreach ($data["liquidaciones"] as $key_mes => $canales) {
			foreach ($canales as $key_canal => $value) {
				$totales2[$key_mes][$concepto] += $value[$concepto];
			}
		}
	}
	
	$array_datatable = [];
	foreach ($locales2 as $id => $data_local)
	{
		foreach($cdv_arr as $cdv_id => $cdv) /*fill local with cdvs*/
        {
     	   	$objeto =
     	   	[
     	   		"NOMBRE TIENDA" => $data_local["NOMBRE TIENDA"] ,
     	   		"NOMBRE SOP" => $data_local["NOMBRE SOP"],
     	   		"DEPARTAMENTO" => $data_local["DEPARTAMENTO"],
     	   		"PROVINCIA" => $data_local["PROVINCIA"],
     	   		"DISTRITO" => $data_local["DISTRITO"],
     	   		"ZONA" => $data_local["ZONA_NOMBRE"],
     	   		"CANAL DE VENTA" => $cdv["codigo"],
     	   	];
			foreach ($meses as $mes)//add months columns
			{
				//add month  value 
				$valor_mes = 0;
				if( isset($data_local["liquidaciones"]) )
				{
					if( isset($data_local["liquidaciones"][$mes]) )
					{
						if( isset($data_local["liquidaciones"][$mes][$cdv["id"]]) )
						{
							if( isset($data_local["liquidaciones"][$mes][$cdv["id"]][$concepto]) )
							{
								$valor_mes = $data_local["liquidaciones"][$mes][$cdv["id"]][$concepto];
							}
						}
					}
				}
				$objeto[$mes]  = $valor_mes;
			}
			$array_datatable[] = $objeto;
        }

        $objeto =
     	   	[
     	   		"NOMBRE TIENDA" => $data_local["NOMBRE TIENDA"] ,
     	   		"NOMBRE SOP" => $data_local["NOMBRE SOP"],
     	   		"DEPARTAMENTO" => $data_local["DEPARTAMENTO"],
     	   		"PROVINCIA" => $data_local["PROVINCIA"],
     	   		"DISTRITO" => $data_local["DISTRITO"],
				"ZONA" => $data_local["ZONA_NOMBRE"],
     	   		"CANAL DE VENTA" => "TOTAL",
     	   	];
		foreach ($meses as  $mes)
		{//add month  value 
			$valor_mes = 0;
				if( isset($data_local["liquidaciones"]) )
				{
					if( isset($data_local["liquidaciones"][$mes]) )
					{
						if( isset($data_local["liquidaciones"][$mes]["TOTAL"]) )
						{
							if( isset($data_local["liquidaciones"][$mes]["TOTAL"][$concepto]) )
							{
								$valor_mes = $data_local["liquidaciones"][$mes]["TOTAL"][$concepto];
							}
						}
					}
				}
			$objeto[$mes] = $valor_mes;
		}
		$array_datatable[] = $objeto;

	}
	
	$totales = [];
	$data_return["datatable_data"] = $array_datatable;
	$data_return["meses"] = $meses;
	$data_return["totales"] = $totales2;
	$return["data"] = $data_return;
	print_r(json_encode($return));
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consolidado_gastos_obtener_conceptos") {
    try {
        $query = "SELECT id, codigo, nombre FROM tbl_gastos_conceptos WHERE estado = '1' ORDER BY nombre ASC";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        $stmt->execute();

        $list_query = $stmt->get_result();
        $list = $list_query->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        if (count($list) === 0) {
            $result["http_code"] = 400;
            $result["result"] = "El concepto no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $list;
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
    exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "consolidado_gastos_obtener_zonas") {
    try {
        $usuario_id = $login ? $login['id'] : null;

        if ((int)$usuario_id > 0) {
           
            $query = "SELECT z.id, CONCAT(z.nombre, ' - ', red.nombre ) as nombre
                            FROM tbl_zonas z
							LEFT join tbl_razon_social r on z.razon_social_id = r.id
							LEFT join tbl_locales_redes red on r.red_id = red.id
							WHERE z.razon_social_id = 5 AND z.status=1 
                            ORDER BY red.nombre, z.nombre ASC";
            $params = array();

            $stmt = $mysqli->prepare($query);

            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
            }

            if ($params) {
                $stmt->bind_param(str_repeat("i", count($params)), ...$params);
            }

            $stmt->execute();

            $list_query = $stmt->get_result();
            $list = $list_query->fetch_all(MYSQLI_ASSOC);

            $stmt->close();

            if (count($list) === 0) {
                $result["http_code"] = 400;
                $result["result"] = "La zona no existe.";
            } else {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestion.";
                $result["result"] = $list;
            }
        } else {
            $result["http_code"] = 400;
            $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consolidado_gastos_obtener_locales_por_supervisor") {
    try {
		
		$where_supervisores ="";
		$where_zonas="";
		if(array_key_exists("filtro", $_POST)){
			$filtro = $_POST["filtro"];
			if(array_key_exists("supervisores", $filtro)){
				if($filtro["supervisores"]){
					if(!in_array("all", $filtro["supervisores"])){
						$where_supervisores .= " AND pa.id IN ('".implode("','", $filtro["supervisores"])."') ";
					}
				}
			}

			if(array_key_exists("zona_id", $filtro)){
				if($filtro["zona_id"]){
					if(!in_array("all", $filtro["zona_id"])){
						$where_zonas .= " AND l.zona_id
								IN ('".implode("','", $filtro["zona_id"])."') ";
					}
				}
			}
		}
        $query = "SELECT 
                    l.id AS id,
                    CONCAT('[',l.cc_id,'] ',l.nombre) AS nombre,
                    pa.id as personal_id
                FROM tbl_usuarios_locales ul 
                INNER JOIN tbl_usuarios u ON ul.usuario_id = u.id
                INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
                INNER JOIN tbl_locales l ON ul.local_id = l.id
				LEFT JOIN tbl_zonas z ON z.id = l.zona_id
                WHERE
					pa.estado = 1
                    AND ul.estado = 1 
                    AND l.nombre IS NOT NULL
					AND z.razon_social_id = 5
					AND z.status=1 
					$where_zonas
					$where_supervisores
                GROUP BY l.id
                ORDER BY l.nombre ASC";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        //$stmt->bind_param("i", $supervisor_id);
        $stmt->execute();

        $list_query = $stmt->get_result();
        $list = $list_query->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        if (count($list) === 0) {
            $result["http_code"] = 400;
            $result["result"] = "El local no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $list;
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consolidado_gastos_obtener_supervisores") {
    try {

		$where_zonas ="";
		if(array_key_exists("filtro", $_POST)){
			$filtro = $_POST["filtro"];

			if(array_key_exists("zona_id", $filtro)){
				if($filtro["zona_id"]){
					if(!in_array("all", $filtro["zona_id"])){
						$where_zonas .= " AND pa.zona_id IN ('".implode("','", $filtro["zona_id"])."') ";
					}
				}
			}
		}

        $query = "SELECT 
                    pa.id as id,
                    pa.area_id,
                    pa.cargo_id,
                    CONCAT(IFNULL(pa.nombre,'Sin supervisor asignado.'), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS supervisor_nombre,
                    IFNULL(pa.correo, '') as supervisor_correo
                FROM tbl_usuarios_locales ul 
                INNER JOIN tbl_usuarios u ON ul.usuario_id = u.id
                INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id and pa.cargo_id = 4 
                INNER JOIN tbl_locales l ON ul.local_id = l.id
				LEFT JOIN tbl_zonas z ON z.id = pa.zona_id
                WHERE	
                pa.area_id = 21 
                AND pa.estado = 1
                AND ul.estado = 1 
				AND z.razon_social_id = 5
				AND z.status=1 
                $where_zonas
                GROUP BY u.id
                ORDER BY supervisor_nombre";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        $stmt->execute();

        $list_query = $stmt->get_result();
        $list = $list_query->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        if (count($list) === 0) {
            $result["http_code"] = 400;
            $result["result"] = "El local no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $list;
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "asd") {
	require_once '../phpexcel/classes/PHPExcel.php';

	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Formato para importar el consolidado de gastos";

					$list_cols["zona"]="Zona";
	$titulosColumnas = array('CC', 'Nombre', 'Departamento', 'Provincia', 'Distrito', 'Dirección', 'Ubicación G. Maps', 'Proveedor de Internet', 'Tipo de Internet',  '# DECOS MOVISTAR', '# DECOS DIRECTV', '# de CPU', '# de Monitores', '# de terminal KASNET','Trastienda', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado','Domingo', '# de cajas operativas', '# de autoservicios', '# de AIO', '# de terminales híbrido', '# de terminales antiguos', '# de televisores virtuales', '# de televisores apuestas deportivas', 'Subgerencia', 'Zona', 'Jefe Comercial', 'Celular del jefe comercial', 'Celular de la tienda', 'Correo de la tienda', 'Status diario', 'Fecha de apertura');

	

    /// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);

    // Establecer el encabezado y formato del archivo Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="archivo_gastos.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$filename = "Formato_Consolidado_Gastos.xls";
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/' . $filename;
	$excel_path_download = '/files_bucket/mepa/descargas/'. $filename;

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

if (isset($_POST["accion"]) && $_POST["accion"]==="exportar_consolidado_gastos")
{

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Lista Detallada de locales";

	$titulosColumnas = array('CECO', 'YEAR', 'NUM_MES', 'AGRUPADOR_RETAIL_1', 'TOTAL');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
			    ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
			    ->setCellValue('B1', $titulosColumnas[1])
			    ->setCellValue('C1', $titulosColumnas[2])
			    ->setCellValue('D1', $titulosColumnas[3])
			    ->setCellValue('E1', $titulosColumnas[4]);

	//Se agregan los datos a la lista del reporte

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
	  

	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
		// Recorre todas las filas mayores a 1 y aplica el ajuste automático de altura
		for ($i = 2; $i <= $objPHPExcel->getActiveSheet()->getHighestRow(); $i++) {
			$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(18);
		}

	$objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray($estiloNombresFilas);
	$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($estiloColoFondo);
	
	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'E'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Formato de consolidado');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Formato Consolidado.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$filename = "Formato_Consolidado.xls";
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/' . $filename;
	$excel_path_download = '/files_bucket/mepa/descargas/'. $filename;

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
?>