<?php

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_caja_chica_listar_liquidacion")
{
	$param_asignacion_id = $_POST['mepa_caja_chica_liquidacion_param_asignacion_id'];

	$suma_total = 0;
	$query = "
		SELECT
			l.id, l.asignacion_id, l.num_correlativo, l.fecha_desde, l.fecha_hasta, 
			IFNULL(l.total_rendicion, 0) AS total_liquidacion,
		    l.se_aplica_movilidad, l.id_movilidad, IFNULL(m.monto_cierre, 0) AS total_movilidad,
		    ce.situacion AS situacion_jefe, cec.situacion AS situacion_contabilidad, cet.situacion AS situacion_tesoreria
		FROM mepa_caja_chica_liquidacion l
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN cont_etapa ce
			ON l.situacion_etapa_id_superior = ce.etapa_id
			INNER JOIN cont_etapa cec
			ON l.situacion_etapa_id_contabilidad = cec.etapa_id
			INNER JOIN cont_etapa cet
			ON l.situacion_etapa_id_tesoreria = cet.etapa_id
		WHERE l.asignacion_id = '".$param_asignacion_id."'
		ORDER BY l.num_correlativo DESC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$suma_total = $reg->total_liquidacion + $reg->total_movilidad;

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->num_correlativo,
			"2" => $reg->fecha_desde.' al '.$reg->fecha_hasta,
			"3" => "S/ ".$reg->total_liquidacion,
			"4" => ($reg->se_aplica_movilidad == 1) ? "S/ ".$reg->total_movilidad : "0.00",
			"5" => "S/ ".number_format($suma_total, 2, '.', ','),
			"6" => $reg->situacion_jefe,
			"7" => $reg->situacion_contabilidad,
			"8" => $reg->situacion_tesoreria,
			"9" => '<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_atencion_liquidacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Acceder al detalle">
                        <span class="fa fa-eye"></span>
                    </a>'
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