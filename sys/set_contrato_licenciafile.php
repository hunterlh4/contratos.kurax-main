<?php 
date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_locales_licenciafile")
{
	$cont_licencia_municipal_select_tienda = $_POST['cont_licencia_municipal_select_tienda'];

	//$cont_licencia_municipal_select_estado = $_POST['cont_licencia_municipal_select_estado'];

	//ESTE SELECT ES CUANDO ESTA ACTIVO EL ESTADO CUANDO HACEMOS JOIN
	$query = "SELECT
			c.nombre_tienda,
			
			lmf.nombre_file as lic_funcionamiento,
			lmf.ruta as lic_funcionamiento_ruta,
			lmf.extension as lic_funcionamiento_extension,
			lmf.size as lic_funcionamiento_size,
			lmf.download_file as lic_funcionamiento_download,
			lmf.estado as lic_funcionamiento_estado,
			
			lmi.nombre_file as lic_indeci,
			lmi.ruta as lic_indeci_ruta,
			lmi.extension as lic_indeci_extension,
			lmi.size as lic_indeci_size,
			lmi.download_file as lic_indeci_download,
			lmi.estado as lic_indeci_estado,
			
			lmp.nombre_file as lic_publicidad,
			lmp.ruta as lic_publicidad_ruta,
			lmp.extension as lic_publicidad_extension,
			lmp.size as lic_publicidad_size,
			lmp.download_file as lic_publicidad_download,
			lmp.estado as lic_publicidad_estado,
			
			lmdj.nombre_file as lic_dj,
			lmdj.ruta as lic_dj_ruta,
			lmdj.extension as lic_dj_extension,
			lmdj.size as lic_dj_size,
			lmdj.download_file as lic_dj_download,
			lmdj.estado as lic_dj_estado
		FROM cont_contrato c

			left join cont_licencia_municipales lmf
			on c.contrato_id = lmf.contrato_id and lmf.tipo_archivo_id = 4 and lmf.estado = 1
			left join cont_licencia_municipales lmi
			on c.contrato_id = lmi.contrato_id and lmi.tipo_archivo_id = 5 and lmi.estado = 1
			left join cont_licencia_municipales lmp
			on c.contrato_id = lmp.contrato_id and lmp.tipo_archivo_id = 6 and lmp.estado = 1
			left join cont_licencia_municipales lmdj
			on c.contrato_id = lmdj.contrato_id and lmdj.tipo_archivo_id = 7 and lmdj.estado = 1";

	if($cont_licencia_municipal_select_tienda == "0")
	{
		$query .= " WHERE c.status = 1 AND c.etapa_id = 5";
	}
	else
	{
		$query .= " WHERE c.status = 1 AND c.etapa_id = 5 AND c.contrato_id = '".$cont_licencia_municipal_select_tienda."'";
	}

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();


	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
		"0" => $reg->nombre_tienda,
		//FUNCIOAMIENTO
		"1" => ($reg->lic_funcionamiento != "") ? 
				'<button class="btn btn-rounded btn-primary btn-sm" title="Ver documento"
					onclick="cont_licenciafileVerFileEnVisor(\''.$reg->lic_funcionamiento_extension.'\', \''.$reg->lic_funcionamiento_download.'\')">
					<i class="fa fa-eye"></i>
				</button>
				<a 
					onclick="sec_contrato_licenciafile_btn_descargar(\''.$reg->lic_funcionamiento_download.'\')";
					title="Descargar documento" class="btn btn-primary btn-sm"><i class="fa fa-download"></i>
				</a>' : 'No existe archivo',
		//INDECI
		"2" => ($reg->lic_indeci != "") ? 
				'<button class="btn btn-rounded btn-primary btn-sm" title="Ver documento"
					onclick="cont_licenciafileVerFileEnVisor(\''.$reg->lic_indeci_extension.'\', \''.$reg->lic_indeci_download.'\')">
					<i class="fa fa-eye"></i>
				</button>
				<a 
					onclick="sec_contrato_licenciafile_btn_descargar(\''.$reg->lic_indeci_download.'\')";
					title="Descargar documento" class="btn btn-primary btn-sm"><i class="fa fa-download"></i>
				</a>' : 'No existe archivo',
		//PUBLICIDAD
		"3" => ($reg->lic_publicidad != "") ? 
				'<button class="btn btn-rounded btn-primary btn-sm" title="Ver documento"
					onclick="cont_licenciafileVerFileEnVisor(\''.$reg->lic_publicidad_extension.'\', \''.$reg->lic_publicidad_download.'\')">
					<i class="fa fa-eye"></i>
				</button>
				<a 
					onclick="sec_contrato_licenciafile_btn_descargar(\''.$reg->lic_publicidad_download.'\')";
					title="Descargar documento" class="btn btn-primary btn-sm"><i class="fa fa-download"></i>
				</a>' : 'No existe archivo',
		//DECLARACION JURADA
		"4" => ($reg->lic_dj != "") ? 
				'<button class="btn btn-rounded btn-primary btn-sm" title="Ver documento"
					onclick="cont_licenciafileVerFileEnVisor(\''.$reg->lic_dj_extension.'\', \''.$reg->lic_dj_download.'\')">
					<i class="fa fa-eye"></i>
				</button>
				<a 
					onclick="sec_contrato_licenciafile_btn_descargar(\''.$reg->lic_dj_download.'\')";
					title="Descargar documento" class="btn btn-primary btn-sm"><i class="fa fa-download"></i>
				</a>' : 'No existe archivo'
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

?>