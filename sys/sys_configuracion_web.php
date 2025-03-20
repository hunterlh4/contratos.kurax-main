<?php

include('/var/www/html/sys/mailer/class.phpmailer.php');
include_once '/var/www/html/sys/helpers.php';

function enviar_correo_local_cerrado($local_id){

	global $mysqli;
	global $login;
	$zonas = [];
	$locales =[];
	$locales_command = "
		SELECT
			l.id AS 'local_id',
			l.nombre AS 'local_nombre',
			l.zona_id,
			z.nombre AS 'zona_nombre',
			z.ord AS 'zona_ord',
			CONCAT(IFNULL(p.nombre,'Sin jefe de operaciones asignado.'), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS jop_nombre,
			CONCAT(IFNULL(MAX(psop.nombre),'Sin supervisor asignado.'), ' ', IFNULL(MAX(psop.apellido_paterno), ''), ' ', IFNULL(MAX(psop.apellido_materno), '')) AS sop_nombre
			,IFNULL(p.correo, '') AS jop_correo
 			,IFNULL(MAX(psop.correo), '') AS sop_correo
		FROM tbl_locales l
		INNER JOIN tbl_zonas z  ON z.id = l.zona_id
		INNER JOIN tbl_personal_apt p  ON (p.id = z.jop_id AND p.area_id = 21 AND p.cargo_id = 16 AND p.estado = 1)
		INNER JOIN tbl_usuarios ujop  ON (ujop.personal_id = p.id AND ujop.estado = 1)
		INNER JOIN tbl_usuarios_locales ul  ON (ul.local_id = l.id AND ul.estado = 1)
		LEFT JOIN tbl_usuarios u  ON (u.id = ul.usuario_id AND u.estado = 1)
		LEFT JOIN tbl_personal_apt psop  ON (psop.id = u.personal_id AND psop.area_id = 21 AND psop.cargo_id = 4 AND psop.estado = 1)
		WHERE l.red_id IN ('1','7') and l.id= {$local_id}
		GROUP BY l.id
	";

	echo "init get locales at ".date("Y-m-d H:i:s")."\n";
	$locales_query = $mysqli->query($locales_command);

	$correos=[];
	while ($l=$locales_query->fetch_assoc()) {
		$nombre_clear = indexnizer($l["local_nombre"]);
		$locales[$nombre_clear]["Supervisores"][]=$l["sop_nombre"];
		$zonas[$l["zona_ord"]]=$l["zona_nombre"]." - ".$l["jop_nombre"];
		$locales[$nombre_clear]["local_nombre"]=$l["local_nombre"];
		$locales[$nombre_clear]["local_id"]=$l["local_id"];
		$locales[$nombre_clear]["zona_ord"]=$l["zona_ord"];
		$correos[$nombre_clear]["jop_correo"]=$l["jop_correo"];
		$correos[$nombre_clear]["sop_correo"]=$l["sop_correo"];
	}
	print_r($locales);
	print_r($correos);

	echo "end get locales at ".date("Y-m-d H:i:s")."\n";
	if(count($locales)>0){
		$firstKey = array_keys($locales)[0];
		$local_nombre=($locales[$firstKey])["local_nombre"];	
		$correo_supervisor=$correos[$firstKey]["sop_correo"];
		$correo_jop=$correos[$firstKey]["jop_correo"];
	}else{
		$local_nombre="-";
		$correo_supervisor="";
		$correo_jop=""; 
	}

	//echo $correo_supervisor;
	//echo " jop:".$correo_jop;
	require_once '/var/www/html/env.php';
	$con_db_name=env('DB_BC_DATABASE');
	$con_host=env('DB_HOST');
	$con_user=env('DB_USERNAME');
	$con_pass=env('DB_PASSWORD');
	$mysqli_bc = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);

	if (mysqli_connect_errno()) {
	    printf("Conexion fallida: %s\n", mysqli_connect_error());
	    exit();
	}
	$mysqli_bc->query("SET CHARACTER SET utf8");
	$command_rqs = "
		SELECT 
			c.col_Id as 'ID Cliente',
			c.col_Name AS 'Nombre de Cliente',
			cr.col_Amount AS 'Monto S/',
			DATE_FORMAT(ADDDATE(cr.col_RequestTime, INTERVAL -9 HOUR), '%Y-%m-%d %H:%i:%s') as 'Fecha de Solicitud',
            bt.col_Name AS 'Local'
		FROM tbl_ClientRequest cr 
		LEFT JOIN tbl_Client c    ON c.col_Id = cr.col_ClientId
		LEFT JOIN tbl_Betshop bt  ON bt.col_Id = cr.col_BetshopId
		WHERE cr.col_BetshopId IS NOT NULL
		AND cr.col_State = 1
		AND bt.col_Name='$local_nombre' 
		ORDER BY col_RequestTime DESC  limit 1000
		";

	echo "init get requests at ".date("Y-m-d H:i:s")."\n";
	$cons = $mysqli_bc->query($command_rqs);
	$rqs= [];
		while ($fila=$cons->fetch_assoc()) {
			$rqs[]=$fila;
	}
	//print_r($rqs);
	//$rqs =  pdoStatement($command_rqs);
	echo "end get requests at ".date("Y-m-d H:i:s")."\n";
	$data = [];
	foreach ($rqs as $k => $v) {
		$BetShopName_clear = indexnizer($v["Local"]);
		$v["Supervisores"]="";
		// $v["Jefe Comercial"]="";
		if(array_key_exists($BetShopName_clear, $locales)){
			$local_data=isset($locales[$BetShopName_clear]) ? $locales[$BetShopName_clear] : "";
			//local_data = $locales[$BetShopName_clear];
			$v["Supervisores"]=(array_key_exists("Supervisores", $local_data) ? implode(",", $local_data["Supervisores"]) : '');
			// $v["Jefe Comercial"]=(array_key_exists("Jefe Comercial", $local_data) ? implode(",", $local_data["Jefe Comercial"]) : '');
		}
		$zona_ord = (array_key_exists("zona_ord", $local_data) ? $local_data["zona_ord"] : 0);
		$data[$zona_ord][]=$v;
	}
	ksort($data);
	//print_r($data);

	if ($local_nombre == "-") {
		$sql_local_nombre = "SELECT nombre FROM tbl_locales WHERE id = {$local_id}";
		$query_local_nombre = $mysqli->query($sql_local_nombre);
		while ($l=$query_local_nombre->fetch_assoc()) {
			$local_nombre = $l["nombre"];
		}
	}

	$cols = ["ID Cliente","Nombre de Cliente","Monto S/","Fecha de Solicitud","Local","Supervisores"];
	$cols_len = count($cols);
	$col_w = 140;
	$cols_w = [80,250,70,135,200,150,150];

	$body="";
	$body.="<table cellpadding=6>";
	$body.="<tbody>";
	    $body.="<tr><td style='text-align:right;font-weight:bold'>Local:</td><td>".$local_nombre."</td></tr>";
	    $body.="<tr><td style='text-align:right;font-weight:bold'>Usuario:</td><td>".$login['usuario']."</td></tr>";
	  $body.="</tbody>";
	$body.="</table>";

	$body .='<table border="1" cellpadding="5" cellspacing="0" style="font-family: arial" width="'.array_sum($cols_w).'px">';
	$body .='<thead>';
		$body .='<tr>';
			$body .='<th colspan="'.$cols_len.'" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Retiros web pendientes de pago en tienda al '.date("Y-m-d H:i:s").'</b>';
			$body .= '</th>';
		$body .='</tr>';
	$body .='</thead>';
	$body .='<tbody>';
	foreach ($data as $zona_id => $requests) {
		$body .='<tr>';
			$body .='<td colspan="'.$cols_len.'" style="background-color: #ffffdd;  font-size: 14px; text-align:center;">';
				$body.='<b>';
				$body.= $zonas[$zona_id];
				$body.='</b>';
			$body .='</td>';
		$body .='</tr>';
		$body .='<tr>';
			foreach ($cols as $col_k => $col) {
				$body .='<td style="background-color: #ffffdd;  font-size: 14px; width:'.($cols_w[$col_k]).'px;">';
				$body.='<b>';
				$body.= $col;
				$body.='</b>';
				$body .='</td>';
			}
		$body .='</tr>';
		foreach ($requests as $d) {
			$body .='<tr>';
				foreach ($d as $v) {
					$body .='<td style="font-size: 12px; text-align:'.(is_numeric($v)?'right':'left').';">';
					$body.= $v;
					$body .='</td>';
				}
			$body .='</tr>';
		}
	}
	if(count($data)==0){
		$body .='<tr><td colspan='.$cols_len.'>No hay registros</td></tr>';
	}
	$body .='</tbody>';
	$body .='</table>';

	

	$request = [
		"subject" => "WEB - Local cerrado - ".$local_nombre."  ".date("Y-m-d H:i:s")." #".date('YmdHis'),
		"body" => $body,
		"cc" => [
			"gonzalo.perez@testtest.apuestatotal.com",
			"walter.cortes@testtest.apuestatotal.com",
			"manuel.llaguno@testtest.apuestatotal.com",
			"tania.carpio@testtest.apuestatotal.com",
			"soporte@testtest.apuestatotal.com"
		],
		"bcc" => [
			"bladimir.quispe@testtest.kurax.dev"
		]
	];

	if ($correo_jop != "") {
		array_push($request["cc"], $correo_jop);
	}
	if ($correo_supervisor != "") {
		array_push($request["cc"], $correo_supervisor);
	}

	//print_r($request);
	if(isset($_POST["test"])){
		echo $body;
	} else{
		echo "init send_email at ".date("Y-m-d H:i:s")."\n";
		send_email($request);
		echo "end send_email at ".date("Y-m-d H:i:s")."\n";
		echo json_encode($request);
		return "Correo Enviado";
	}

}
?>
