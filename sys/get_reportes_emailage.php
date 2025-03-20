<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php"); 
require_once '/var/www/html/sys/helpers.php';

if(isset($_POST["sec_emailage_get_reporte"])){
	$get_data = $_POST["sec_emailage_get_reporte"];
	// print_r($get_data);
	// exit();    
	$email_name = $_POST["email_name"];
	$risk_name = $_POST["risk_name"]; 
	$fecha_inicio = $_POST["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($_POST["fecha_inicio"]));
	$fecha_fin = $_POST["fecha_fin"];
	$fecha_fin_pretty = date("d-m-Y",strtotime($_POST["fecha_fin"]));
	 
	$update_emailage = $_POST["update_name"];
	if($update_emailage != 0){
		r_emailage($email_name);
	} 

	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$column_index = $_POST['order'][0]['column']; // Column index
	$column_name = $_POST['columns'][$column_index]['data']; // Column name
	$column_sort_order = $_POST['order'][0]['dir']; // asc or desc
	$search_value = $_POST['search']['value']; // Search value
	$search_value = $mysqli->real_escape_string($search_value);
	$search_query = " ";
	//echo $local_id;exit;
	if($search_value != ''){
		$search_query = " and 
		id LIKE '%".$search_value."%' or
		userdefinedrecordid LIKE '%".$search_value."%' or
		email LIKE '%".$search_value."%' or
		ipaddress LIKE '%".$search_value."%' or
		eName LIKE '%".$search_value."%' or
		emailAge LIKE '%".$search_value."%' or
		email_creation_days LIKE '%".$search_value."%' or
		domainAge LIKE '%".$search_value."%' or
		domain_creation_days LIKE '%".$search_value."%' or
		firstVerificationDate LIKE '%".$search_value."%' or
		first_seen_days LIKE '%".$search_value."%' or
		status LIKE '%".$search_value."%' or
		country LIKE '%".$search_value."%' or
		fraudRisk LIKE '%".$search_value."%' or
		EAReason LIKE '%".$search_value."%' or
		EAAdvice LIKE '%".$search_value."%' or
		EARiskBandID LIKE '%".$search_value."%' or
		source_industry LIKE '%".$search_value."%' or
		fraud_type LIKE '%".$search_value."%' or
		lastflaggedon LIKE '%".$search_value."%' or
		location LIKE '%".$search_value."%' or
		emailExists LIKE '%".$search_value."%' or
		domainExists LIKE '%".$search_value."%' or
		company LIKE '%".$search_value."%' or
		title LIKE '%".$search_value."%' or
		domainname LIKE '%".$search_value."%' or
		domaincompany LIKE '%".$search_value."%' or
		domaincountryname LIKE '%".$search_value."%' or
		domaincategory LIKE '%".$search_value."%' or
		domaincorporate LIKE '%".$search_value."%' or
		domainrisklevel LIKE '%".$search_value."%' or
		domainrelevantinfo LIKE '%".$search_value."%' or
		phone_status LIKE '%".$search_value."%' or
		shipforward LIKE '%".$search_value."%' or
		correlationId LIKE '%".$search_value."%' or
		transAmount LIKE '%".$search_value."%' or
		transCurrency LIKE '%".$search_value."%' or
		shipcitypostalmatch LIKE '%".$search_value."%' or
		ip_isp LIKE '%".$search_value."%' or
		ip_proxydescription LIKE '%".$search_value."%' or
		ip_proxytype LIKE '%".$search_value."%' or
		ip_anonymousdetected LIKE '%".$search_value."%' or
		ip_reputation LIKE '%".$search_value."%' or
		ip_riskreason LIKE '%".$search_value."%' or
		ip_risklevel LIKE '%".$search_value."%' or
		ip_risklevelid LIKE '%".$search_value."%' or
		created_at LIKE '%".$search_value."%' or
		updated_at LIKE '%".$search_value."%'
		 ";

	}
	$SELECT = "SELECT id, userdefinedrecordid, email, ipaddress , eName , emailAge, email_creation_days, domainAge, domain_creation_days, 
	firstVerificationDate, first_seen_days, status, country, fraudRisk, EAReason, EAAdvice, EARiskBandID, source_industry,fraud_type,
	lastflaggedon,location,emailExists, domainExists, company,title, domainname, domaincompany, domaincountryname, domaincategory, 
	domaincorporate, domainrisklevel, domainrelevantinfo,phone_status,shipforward,correlationId, transAmount,transCurrency,shipcitypostalmatch
	, ip_isp, ip_proxydescription, ip_proxytype, ip_anonymousdetected, ip_reputation, ip_riskreason,ip_risklevel, ip_risklevelid, created_at, updated_at 
	FROM api.register_emailage where EARiskBandID =  IF('$risk_name' != '', '$risk_name', EARiskBandID) and email = IF('$email_name' != '', '$email_name', email) 
	and date(created_at) >='".date("Y-m-d",strtotime($_POST["fecha_inicio"]))."'
	and date(created_at) <='".date("Y-m-d",strtotime($_POST["fecha_fin"]))."' ";
	//$SELECT = $mysqli->query($emailage_sql_command);
	

	$emp_query=   $SELECT;
	//print_r($emp_query);
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM ($SELECT) as filas");
	if($mysqli->error){
		echo "ERROR: ";
		print_r($mysqli->error);
		exit();
	}  
	$records = $sel->fetch_assoc();
	$total_records = $records['allcount'];

	$emp_query=   $SELECT."	".$search_query;
	//print_r($emp_query);

	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$emp_query.") AS subquery");
	$records = $sel->fetch_assoc();
	$total_recordwith_filter = $records['allcount'];

	$limit=" limit ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}
	$emp_query= $SELECT." ".$search_query." order by ".$column_name." ".$column_sort_order.$limit;	
	$registros = $mysqli->query($emp_query);
	$data = array();

	if(!empty($records)){
		while ($row = $registros->fetch_assoc()) {
			$data[] = $row;
		 }
	}
	 
	$response = array(
	  "draw" => $draw,
	  "iTotalRecords" => $total_records,
	  "iTotalDisplayRecords" => $total_recordwith_filter,
	  "aaData" => $data
	);

	echo json_encode($response);
	return;


}

function r_emailage($mail)
{
	$accessToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImRhOGIyMzllNjQ2YzRmNTM2MDAzZDFmMTNkYTZiZWUxN2FlMmUwNTIyODM1MWRlZjU2OWQyMTNlMTkwZDZmODg2ZDU1ZGNjZWUwN2FiZGRjIn0.eyJhdWQiOiIxIiwianRpIjoiZGE4YjIzOWU2NDZjNGY1MzYwMDNkMWYxM2RhNmJlZTE3YWUyZTA1MjI4MzUxZGVmNTY5ZDIxM2UxOTBkNmY4ODZkNTVkY2NlZTA3YWJkZGMiLCJpYXQiOjE1OTkyNzA2NDUsIm5iZiI6MTU5OTI3MDY0NSwiZXhwIjoxNjMwODA2NjQ1LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.IROpjLb_oSxGsVHduml2KUXKD7pTL5_DDZfNAnLhS-pL2tGgpbSYXgQayYHlwjGgbVCm8eEbpO00fFrSVWEkvmHcvt1xxhmvA8Y5vL6FI7XoJluo5T-UhXPwQIVgDfgygGQoWFATkTGF4SQ9MpjWANgav2GKgx-VjP6ucWrMn1ObSyFOtLf8C_IeI2JlAC-MzEJ3vlWqMaZ2HRU2V3msK6595L97eiUIpc7ruLOSjmDuPm_KoxtSwFEYd6UG0MNUPci6Ug0EheiuaZoHmwi--TQwHabm-vtqUzkWhwYaUEvXdeoxGYhJwUr_OX5ECEDfpo2Z5f0K7XKh6fpe5rq2NjU4U6I18kWZi1DTGfCsO41KNF6a5pxf5BOGBuxF-Zl7ixzzf_PxTZ7wGBj2tw8yBFOt-_8N7FyNaSMuFzNaWG5CCSgQtjsXktYqRx77v14RNctR7atTC09BYi0NVoSTIBeKqlMlRa8gqpG0bjofTZ1E2jhXzGyIoQkCj7UaJkMBTDwXhUZ65md8FY30aZ9AcSZaqo2HE0NgAu0oEMi4UwGNq04QsG4-DPMXZJyXBj2joOtzPdWOSkQjlpX_3fAknN28de7sSqd7pJa2fFs8cVH_rdq6_PKyzzvgpKN1RtMbJFzHO016F27-_vCeD24GCTSJFp56fXfmo21mgrq8Gb0";

	$url = "https://api.apuestatotal.com/v2/emailage";
	$headers = [];
	$headers[] = "Authorization: Bearer " . $accessToken;
	$rq = [];
	$rq["email"] = $mail; 
	ob_start();
	//$out = fopen('php://output', 'w');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	//curl_setopt($ch, CURLOPT_STDERR, $out);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $rq);
	try {
			$result = curl_exec($ch);
			$responseEmailage = json_decode($result, true);
			if($responseEmailage != null){
				if($responseEmailage["http_code"] == 200){  
					//log_write($result); 
					curl_close($ch); 
					return  true;
				}
				else{ 
					//log_write($result); 
					curl_close($ch); 
					return  false;
				}
			}
			else{
					//log_write($result); 
					curl_close($ch); 
					return  false;
			}
		}	
	catch (Exception $e) { 
				//log_write($e->getMessage()); 
				return false;
			}	  
}
function Utf8_ansi($valor='') {

    $utf8_ansi2 = array(
    "u00c0" =>"À",
    "u00c1" =>"Á",
    "u00c2" =>"Â",
    "u00c3" =>"Ã",
    "u00c4" =>"Ä",
    "u00c5" =>"Å",
    "u00c6" =>"Æ",
    "u00c7" =>"Ç",
    "u00c8" =>"È",
    "u00c9" =>"É",
    "u00ca" =>"Ê",
    "u00cb" =>"Ë",
    "u00cc" =>"Ì",
    "u00cd" =>"Í",
    "u00ce" =>"Î",
    "u00cf" =>"Ï",
    "u00d1" =>"Ñ",
    "u00d2" =>"Ò",
    "u00d3" =>"Ó",
    "u00d4" =>"Ô",
    "u00d5" =>"Õ",
    "u00d6" =>"Ö",
    "u00d8" =>"Ø",
    "u00d9" =>"Ù",
    "u00da" =>"Ú",
    "u00db" =>"Û",
    "u00dc" =>"Ü",
    "u00dd" =>"Ý",
    "u00df" =>"ß",
    "u00e0" =>"à",
    "u00e1" =>"á",
    "u00e2" =>"â",
    "u00e3" =>"ã",
    "u00e4" =>"ä",
    "u00e5" =>"å",
    "u00e6" =>"æ",
    "u00e7" =>"ç",
    "u00e8" =>"è",
    "u00e9" =>"é",
    "u00ea" =>"ê",
    "u00eb" =>"ë",
    "u00ec" =>"ì",
    "u00ed" =>"í",
    "u00ee" =>"î",
    "u00ef" =>"ï",
    "u00f0" =>"ð",
    "u00f1" =>"ñ",
    "u00f2" =>"ò",
    "u00f3" =>"ó",
    "u00f4" =>"ô",
    "u00f5" =>"õ",
    "u00f6" =>"ö",
    "u00f8" =>"ø",
    "u00f9" =>"ù",
    "u00fa" =>"ú",
    "u00fb" =>"û",
    "u00fc" =>"ü",
    "u00fd" =>"ý",
    "u00ff" =>"ÿ");
    return strtr($valor, $utf8_ansi2);
}
 
$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>
