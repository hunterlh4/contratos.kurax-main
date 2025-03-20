<?php 
date_default_timezone_set("America/Lima");

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_locales_contabilidad_reporte")
{
	$cont_contabilidad_numero_comprobante = $_POST['cont_contabilidad_numero_comprobante'];

	$cont_contabilidad_fecha_comprobante = $_POST['cont_contabilidad_fecha_comprobante'];

	$cont_contabilidad_tipo_cambio = $_POST['cont_contabilidad_tipo_cambio'];

	$cont_tipo_reporte = $_POST['cont_tipo_reporte'];

	$cont_tipo_moneda = $_POST['cont_tipo_moneda'];
	
	$cont_contabilidad_tipo_conversion = $_POST['cont_contabilidad_tipo_conversion'];


	$cont_contabilidad_anio = $_POST['cont_contabilidad_anio'];

	$cont_contabilidad_mes = $_POST['cont_contabilidad_mes'];

	
	if($cont_tipo_reporte == 1)
	{
		$query = " SELECT 
						cfc.id_condicion_economica, cfc.sub_diario, cfc.num_comprobante, cfc.fecha_comprobante, ce.tipo_moneda_id,
				    	ce.tipo_moneda_id AS codigo_moneda, cfc.tipo_cambio,
				    	CONCAT('ALQ', ' ', c.nombre_tienda) AS 'glosa_principal', cfc.cuenta_contable, cfc.codigo_anexo,
				    	tl.cc_id AS codigo_centro_costo, cfc.importe_original AS importe_original
				   FROM cont_file_concar cfc
					   	INNER JOIN cont_condicion_economica ce
					   	ON cfc.id_condicion_economica = ce.condicion_economica_id
					   	INNER JOIN cont_contrato c
					   	ON ce.contrato_id = c.contrato_id
					   	LEFT JOIN tbl_locales tl
	   				   	ON tl.contrato_id = c.contrato_id
				   WHERE ce.status = 1 AND cfc.status = 1 AND MONTH(cfc.registro_mes) = '".$cont_contabilidad_mes."' AND YEAR(cfc.registro_mes) = '".$cont_contabilidad_anio."' AND ce.tipo_moneda_id = '".$cont_tipo_moneda."' 
				   	ORDER BY cfc.id_condicion_economica" ;
	}
	elseif ($cont_tipo_reporte == 2) 
	{
		$query = " SELECT
						cfs.id_condicion_economica, cfs.cp_csubdia AS sub_diario, cfs.cp_ccompro AS num_comprobante, cfs.cp_cfecdoc AS fecha_comprobante, ce.tipo_moneda_id,
					    ce.tipo_moneda_id AS codigo_moneda, cfs.cp_csituac AS tipo_cambio,
					    CONCAT('ALQ', ' ', c.nombre_tienda) AS 'glosa_principal', cfs.cp_ccuenta AS cuenta_contable, cfs.cp_ccodigo AS codigo_anexo,
					    tl.cc_id AS codigo_centro_costo, cfs.cp_nimpomn AS importe_original
					FROM cont_file_sispag cfs
					INNER JOIN cont_condicion_economica ce
					ON cfs.id_condicion_economica = ce.condicion_economica_id
					INNER JOIN cont_contrato c
					ON ce.contrato_id = c.contrato_id
					LEFT JOIN tbl_locales tl
   				   ON tl.contrato_id = c.contrato_id
			   WHERE ce.status = 1 AND cfs.status = 1 AND MONTH(cfs.registro_mes) = '".$cont_contabilidad_mes."' AND YEAR(cfs.registro_mes) = '".$cont_contabilidad_anio."' AND ce.tipo_moneda_id = '".$cont_tipo_moneda."' 
			   ORDER BY cfs.id_condicion_economica" ;
	}
	

    $list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	$cont = 0;
	$id_actual = "";
	$id_anterior = "";

	while($reg = $list_query->fetch_object()) 
	{
		$id_actual = $reg->id_condicion_economica;
		
		if($cont == 0)
		{
			$id_anterior = $reg->id_condicion_economica;	
		}

		if($id_anterior == $id_actual)
		{
			$id_anterior = $reg->id_condicion_economica;
		}
		else
		{
			$id_anterior = $reg->id_condicion_economica;
			$cont_contabilidad_numero_comprobante ++;
		}

		$cont ++;

		$data[] = array(
			"0" => $reg->sub_diario,
			
			// NUMERO DE COMPROBANTE
			//"1" => $reg->num_comprobante,
			"1" => $reg->num_comprobante.str_pad($cont_contabilidad_numero_comprobante, 4, "0", STR_PAD_LEFT),
			
			// FECHA DE COMPROBANTE
			//"2" => $reg->fecha_comprobante,
			"2" => $cont_contabilidad_fecha_comprobante,
			

			"3" => $reg->glosa_principal,
			
			// TIPO CAMBIO
			//"4" => $reg->tipo_cambio,
			"4" => $cont_contabilidad_tipo_cambio,
			
			
			"5" => $reg->cuenta_contable,
			"6" => $reg->codigo_anexo,
			"7" => $reg->codigo_centro_costo,
			"8" => $reg->importe_original
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


if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_locales_contabilidad_centro_costos")
{
	$tienda = "";
	$tipo_moneda = "";

	$cont_contabilidad_centro_costos_param_tienda = $_POST['cont_contabilidad_centro_costos_param_tienda'];
	$cont_contabilidad_centro_costos_param_tipo_moneda = $_POST['cont_contabilidad_centro_costos_param_tipo_moneda'];
	$cont_contabilidad_centro_costos_param_fecha_inicio_contrato = $_POST['cont_contabilidad_centro_costos_param_fecha_inicio_contrato'];
	$cont_contabilidad_centro_costos_param_fecha_fin_contrato = $_POST['cont_contabilidad_centro_costos_param_fecha_fin_contrato'];

	if($cont_contabilidad_centro_costos_param_tienda != "0")
	{
		$tienda .= " AND c.contrato_id = '".$cont_contabilidad_centro_costos_param_tienda."' ";
	}

	if($cont_contabilidad_centro_costos_param_tipo_moneda != "0")
	{
		$tipo_moneda .= " AND ce.tipo_moneda_id = '".$cont_contabilidad_centro_costos_param_tipo_moneda."' ";
	}

	$query = "  SELECT 
					c.contrato_id, tl.nombre AS nombre_tienda, tl.cc_id AS codigo_concar, i.ubicacion, 
					ce.monto_renta, m.nombre AS tipo_moneda, ce.fecha_inicio, ce.fecha_fin
				FROM cont_contrato c
					INNER JOIN cont_condicion_economica ce
					ON c.contrato_id = ce.contrato_id AND ce.status = 1
					INNER JOIN tbl_moneda m
					ON ce.tipo_moneda_id = m.id
					INNER JOIN cont_inmueble i
					ON c.contrato_id = i.contrato_id AND i.status = 1
					INNER JOIN tbl_locales tl
					ON tl.contrato_id = c.contrato_id AND tl.estado = 1
				WHERE c.status = 1 AND c.etapa_id = 5 AND ce.fecha_inicio >= '".$cont_contabilidad_centro_costos_param_fecha_inicio_contrato."' AND ce.fecha_fin <= '".$cont_contabilidad_centro_costos_param_fecha_fin_contrato."'
				".$tienda."".$tipo_moneda."
				";

    $list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->contrato_id,
			"1" => $reg->nombre_tienda,
			"2" => $reg->codigo_concar,
			"3" => $reg->ubicacion,
			"4" => $reg->monto_renta,
			"5" => $reg->tipo_moneda,
			"6" => $reg->fecha_inicio,
			"7" => $reg->fecha_fin,
			"8" => '<button type="button" class="btn btn-primary btn-sm" title="Ingresar centro de costos" onclick="obtener_contrato_contabilidad_centrocosto('.$reg->contrato_id.')">
				  <i class="glyphicon glyphicon-edit"></i>
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

if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_locales_contabilidad_servicio_publico")
{
	$cont_contabilidad_servicio_publico_anio = $_POST['cont_contabilidad_servicio_publico_anio'];

	

	$query = "	SELECT
					tl.id as local_id, tl.cc_id AS centro_costos, tl.nombre AS nombre_tienda, trs.nombre AS razon_social,
					i.num_suministro_agua, i.num_suministro_luz,
				    (
						SELECT
							concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS supervisor
						FROM tbl_usuarios_locales tuls
							INNER JOIN tbl_usuarios tus
							ON tuls.usuario_id = tus.id AND tus.grupo_id = 10
							INNER JOIN tbl_personal_apt tp
							ON tp.id = tus.personal_id AND 
							tp.area_id = 21 -- AREA 15 = COMERCIAL, 21 = OPERACIONES
							AND tp.cargo_id = 16 -- CARGO 16 = JEFE
						where tuls.local_id = tl.id AND tuls.estado = 1
						LIMIT 1
					) AS jefe_comercial,
				    (
						SELECT
							concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS supervisor
						FROM tbl_usuarios_locales tuls
							INNER JOIN tbl_usuarios tus
							ON tuls.usuario_id = tus.id -- AND tus.grupo_id = 12
							INNER JOIN tbl_personal_apt tp
							ON tp.id = tus.personal_id AND
							tp.area_id = 21 -- AREA 21 = OPERACIONES 
							AND tp.cargo_id = 4 -- CARGO 4 = SUPERVISOR
						where tuls.local_id = tl.id AND tuls.estado = 1
						LIMIT 1
					) AS supervisor
				    
				FROM tbl_locales tl
					LEFT JOIN tbl_razon_social trs
					ON trs.id = tl.razon_social_id
					LEFT JOIN cont_inmueble i
					ON i.contrato_id = tl.contrato_id
				WHERE tl.estado = 1
				GROUP BY local_id
				ORDER BY jefe_comercial ASC, supervisor ASC";


    $list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->local_id,
			"1" => $reg->centro_costos,
			"2" => $reg->nombre_tienda,
			"3" => $reg->razon_social,
			"4" => $reg->jefe_comercial,
			"5" => $reg->supervisor,
			"6" => $reg->num_suministro_agua,
			"7" => $reg->num_suministro_luz
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


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_dato_contrato_contabilidad") 
{

	$id_contrato = $_POST["parametro"];

	$query = "SELECT c.contrato_id, ce.condicion_economica_id, c.nombre_tienda, i.ubicacion, ce.fecha_inicio, ce.fecha_fin FROM cont_contrato c INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id and ce.status = 1 
	INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id WHERE c.status = 1 AND c.contrato_id = '".$id_contrato."' ";
	
	$list_query = $mysqli->query($query);

	$li = $list_query->fetch_assoc();
	
	echo json_encode($li);

}

if (isset($_POST["accion"]) && $_POST["accion"]==="actualizar_local_centro_costos") 
{
	$resultado = "";

	$contrato_id = $_POST["contrato_id"];
	$codigoCentroCostos = $_POST["codigoCentroCostos"];

	$query_list = "
				SELECT 
					id, cc_id, contrato_id
				FROM tbl_locales
				WHERE contrato_id != '".$contrato_id."' AND cc_id = '".$codigoCentroCostos."' ";


    $list_query = $mysqli->query($query_list);

    $cant_reg = mysqli_num_rows($list_query);

	// $li = $list_query->fetch_assoc();

	// $local_id = $li["id"];

	// $cc_id = $li["cc_id"];

    // YA EXISTE EL CENTRO DE COSTOS EN OTRO LOCAL
	if ($cant_reg > 0) 
	{	
		$resultado = "2";
	}
	// AUN NO EXISTE EL CENTRO DE COSTOS EN OTRO LOCAL
	else
	{
		$query_update = "UPDATE tbl_locales SET cc_id = '". $codigoCentroCostos ."' WHERE contrato_id = '".$contrato_id."' ";
	
		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$resultado = $mysqli->error;
		}
		else
		{
			$resultado = "1";
		}

	}

	// $query_update = "UPDATE tbl_locales SET cc_id = '". $codigoCentroCostos ."' WHERE contrato_id = '".$contrato_id."' ";
	
	// $mysqli->query($query_update);

	
	echo json_encode($resultado);
	
}


?>