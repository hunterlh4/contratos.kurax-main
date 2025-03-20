<?php
include("db_connect.php");
if ($_POST["where"]=="chart_tipo_de_contrato") {
	if ($mysqli->connect_error) {
	  die("Connection failed: " . $mysqli->connect_error);
	}
	$query = "SELECT  count(tbl_c.id) as cantidad,tbl_tc.nombre as tipo
	FROM tbl_contratos tbl_c
	INNER JOIN tbl_contrato_tipos tbl_tc
	ON tbl_c.tipo_contrato_id = tbl_tc.id
	GROUP BY tbl_tc.nombre";
	$result = $mysqli->query($query);
	$jsonArray = array();
	if ($result->num_rows > 0) {
	  while($row = $result->fetch_assoc()) {
		$jsonArrayItem = array();
		$jsonArrayItem['label'] = $row['tipo'];
		$jsonArrayItem['value'] = $row['cantidad'];
		array_push($jsonArray, $jsonArrayItem);
	  }
	}
	$mysqli->close();
	header('Content-type: application/json');
	echo json_encode($jsonArray);
}

if ($_POST["where"]=="chart_contratos_meses") {
	$meses = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

	if ($mysqli->connect_error) {
	  die("Connection failed: " . $mysqli->connect_error);
	}
	$query = "SELECT
	  Year(fecha_inicio_contrato) AS year,
	  Month(fecha_inicio_contrato) as month,
	  Count(tbl_c.id) As cantidad,
	  tbl_y.hex_color as color
	FROM tbl_contratos tbl_c
	INNER JOIN tbl_year tbl_y
	ON Year(fecha_inicio_contrato) = tbl_y.year
	GROUP BY Year(fecha_inicio_contrato), Month(fecha_inicio_contrato)";
	$result = $mysqli->query($query);
	$jsonArray = array();
	if ($result->num_rows > 0) {
			$datasource = array();
			$chart = array();
			$categories = array();
			$category= array();
			$data= array();
			$styles = array();
			$definition = array();
			$application = array();
			$chart["caption"] = "";
			$chart["subcaption"] = "";
			$chart["canvasborderalpha"] = "0";
			$chart["canvasbordercolor"] = "333";
			$chart["canvasborderthickness"] = "1";
			$chart["captionpadding"] = "30";
			$chart["numberprefix"] = "";
			$chart["plotgradientcolor"] = "";
			$chart["captionFontSize"]= "14";
			$chart["subcaptionFontSize"]= "14";
			$chart["subcaptionFontBold"]= "0";
			$chart["paletteColors"]= "#0075c2,#1aaf5d";
			$chart["bgcolor"]= "#ffffff";
			$chart["showBorder"]= "0";
			$chart["showShadow"]= "0";
			$chart["showCanvasBorder"]= "0";
			$chart["usePlotGradientColor"]= "0";
			$chart["legendBorderAlpha"]= "0";
			$chart["legendShadow"]= "0";
			$chart["linethickness"] = "3";
			$chart["showAxisLines"]= "0";
			$chart["showAlternateHGridColor"]= "0";
			$chart["divlineThickness"]= "1";
			$chart["divLineIsDashed"]= "1";
			$chart["divLineDashLen"]= "1";
			$chart["divLineGapLen"]= "1";
			$chart["divlinecolor"] = "111";
			$chart["yaxismaxvalue"] = "30";
			$chart["yaxisvaluespadding"] = "15";
			$chart["xAxisName"]= "Años";
			$chart["showValues"]= "1";
			$chart["exportenabled"] ="1";
			for ($i=0; $i <=12; $i++) { 
			  $detalles_category["label"] = $meses[$i];
			  $detalles_category["stepSkipped"] = false;
			  $detalles_category["appliedSmartLabel"] = true;
			  $category["category"][]=$detalles_category;
			}
			$datasource["categories"][] = $category;
		  $dataset = array();
		  while($row = $result->fetch_assoc()) {
			  $datasource["dataset"][$row["year"]]["color"] = $row["color"];
			  $datasource["dataset"][$row["year"]]["seriesname"] = $row["year"];
			  $datasource["dataset"][$row["year"]]["data"][$row["month"]] = array("value"=>$row["cantidad"]); 
		  }
		  foreach ($datasource["dataset"] as $key => $value) {
			  for ($i=1; $i <=12; $i++) {
				if (array_key_exists($i, $value["data"])) {
				}else{
				  $datasource["dataset"][$key]["data"][$i] = array("value"=>0);               
				}
			  }
			  ksort($datasource["dataset"][$key]["data"]);
		  }
		  sort($datasource["dataset"]);
		  $definition["name"] = "captionFont";
		  $definition["type"] = "font";
		  $definition["size"] = "15";      
		  $styles["styles"]["definition"]=$definition;
		  $application["toobject"]= "caption";
		  $application["styles"]= "captionfont";
		  $styles["styles"]["application"] = $application;
		  $datasource["chart"] = $chart;
		  $datasource["styles"] = $styles;

	}
	$mysqli->close();
	header('Content-type: application/json');
	echo json_encode($datasource);
}

if ($_POST["where"]=="chart_contratos_por_canal_de_venta") {
	$cdv= array("15" => "WEB","16" => "PBET","17" => "SBT-NEGOCIOS","18" => "JV GLOBAL BET","19" => "TABLET BC","20" => "SBT-BC","21" => "JV GOLDEN RACE",);
	if ($mysqli->connect_error) {
	  die("Connection failed: " . $mysqli->connect_error);
	}
	$query ="SELECT 
	DATE_FORMAT(c.fecha_inicio_contrato,'%Y-%m') AS date,
	p.cdv_id as cdv_id,
	cp.producto_id,
	p.nombre as nombre_cdv,
	COUNT(c.id) AS num
	FROM tbl_contratos c
	LEFT JOIN tbl_contrato_productos cp ON (cp.contrato_id = c.id)
	LEFT JOIN tbl_productos p ON (p.id = cp.producto_id)
	WHERE c.fecha_inicio_contrato >= '".$_POST["filtro"]["current_date"]."'
	AND c.fecha_inicio_contrato < '".$_POST["filtro"]["next_date"]."'
	GROUP BY 
	DATE_FORMAT(c.fecha_inicio_contrato,'%Y-%m') DESC,
	cp.producto_id";	

	$result = $mysqli->query($query);
	$jsonArray = array();
	if ($result->num_rows > 0) {
	  while($row = $result->fetch_assoc()) {
		$jsonArrayItem = array();
		$jsonArrayItem['label'] = $cdv[$row["cdv_id"]]." ".$row['nombre_cdv']   ;
		$jsonArrayItem['value'] = $row['num'];
		 $jsonArrayItem['sql'] = $query;
		array_push($jsonArray, $jsonArrayItem);
	  }
	}
	$mysqli->close();
	header('Content-type: application/json');
	echo json_encode($jsonArray);
}

if ($_POST["where"]=="chart_apostado_canal_de_venta_por_meses") {
	$previous_current_year = date("Y-01");
	$next_current_year = date('Y-01', strtotime('+1 year'));
	$meses = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	if ($mysqli->connect_error) {
	  die("Connection failed: " . $mysqli->connect_error);
	}
	$query = "SELECT
	YEAR(d.fecha) AS year,
	DATE_FORMAT(d.fecha,'%c') AS month,
	d.canal_de_venta_id,
	cdv.codigo AS canal_de_venta,
	SUM(d.total_apostado) AS total_apostado,
	cdv.hex_color as color,
	d.fecha AS fecha
	FROM tbl_transacciones_cabecera d
	LEFT JOIN tbl_locales l ON(l.id = d.local_id)
	LEFT JOIN tbl_canales_venta cdv ON (cdv.id = d.canal_de_venta_id)
	WHERE DATE(d.fecha) >= '".$previous_current_year."' AND DATE(d.fecha) < '".$next_current_year."'
	GROUP BY year ASC, month ASC, canal_de_venta_id ASC";
	$result = $mysqli->query($query);
	$jsonArray = array();
	if ($result->num_rows > 0) {
			$datasource = array();
			$chart = array();
			$categories = array();
			$category= array();
			$data= array();
			$styles = array();
			$definition = array();
			$application = array();
			$chart["canvasborderalpha"] = "0";
			$chart["canvasbordercolor"] = "333";
			$chart["canvasborderthickness"] = "1";
			$chart["captionpadding"] = "30";
			$chart["numberprefix"] = "";
			$chart["plotgradientcolor"] = "";
			$chart["captionFontSize"]= "14";
			$chart["subcaptionFontSize"]= "14";
			$chart["subcaptionFontBold"]= "0";
			$chart["bgcolor"]= "#ffffff";
			$chart["showBorder"]= "0";
			$chart["showShadow"]= "0";
			$chart["showCanvasBorder"]= "0";
			$chart["usePlotGradientColor"]= "0";
			$chart["legendBorderAlpha"]= "0";
			$chart["legendShadow"]= "0";
			$chart["linethickness"] = "3";
			$chart["showAxisLines"]= "0";
			$chart["showAlternateHGridColor"]= "0";
			$chart["divlineThickness"]= "1";
			$chart["divLineIsDashed"]= "1";
			$chart["divLineDashLen"]= "1";
			$chart["divLineGapLen"]= "1";
			$chart["divlinecolor"] = "111";
			$chart["yaxismaxvalue"] = "";
			$chart["yaxisvaluespadding"] = "";
			$chart["showValues"]= "0";
			$chart["exportenabled"] ="1";
		  for ($i=0; $i <=12; $i++) { 
			$detalles_category["label"] = $meses[$i];
			$detalles_category["stepSkipped"] = false;
			$detalles_category["appliedSmartLabel"] = true;
			$category["category"][]=$detalles_category;
		  }
		  $datasource["categories"][] = $category;
		  $dataset = array();
		  while($row = $result->fetch_assoc()) {
			  $datasource["dataset"][$row["canal_de_venta_id"]]["color"] = $row["color"];
			  $datasource["dataset"][$row["canal_de_venta_id"]]["seriesname"] = $row["canal_de_venta"];
			  $datasource["dataset"][$row["canal_de_venta_id"]]["data"][$row["month"]] = array("value"=>$row["total_apostado"]); 
		  }
		  
		  foreach ($datasource["dataset"] as $key => $value) {
			  for ($i=1; $i <=12; $i++) {
				if (array_key_exists($i, $value["data"])) {
				}else{
				  $datasource["dataset"][$key]["data"][$i] = array("value"=>0);               
				}
			  }
			  ksort($datasource["dataset"][$key]["data"]);
		  }
		  sort($datasource["dataset"]);
		  $definition["name"] = "captionFont";
		  $definition["type"] = "font";
		  $definition["size"] = "15";      
		  $styles["styles"]["definition"]=$definition;
		  $application["toobject"]= "caption";
		  $application["styles"]= "captionfont";
		  $styles["styles"]["application"] = $application;
		  $datasource["chart"] = $chart;
		  $datasource["styles"] = $styles;
		  $periods["next_current_year"] = $next_current_year;
		  $periods["previous_current_year"] = $previous_current_year;
		  $datasource["periodos"][] = $periods;
	}
	$mysqli->close();
	header('Content-type: application/json');
	echo json_encode($datasource);
}

if ($_POST["where"]=="total_apostado_asesor_cdv") {
	if ($_POST["asesor_id"]==-1) {
		$asesor_sql = "";
	}else{
		$asesor_id = $_POST["asesor_id"];
		$asesor_sql = "AND asesor_id = $asesor_id";		
	}
	$date = explode("-", $_POST['current_date']); 
	$year = (int)$date[0];
	$month = (int)$date[1];
	$query = "SELECT 
	YEAR(tcb.fecha) AS year,
	DATE_FORMAT(tcb.fecha,'%c') AS month,
	tcb.local_id as local_id,
	l.nombre as local,
	cdv.id as cdv_id,
	cdv.codigo as cdv,
	l.asesor_id as asesor_id,
	p.id as personal_id,
	CONCAT(p.nombre,' ',p.apellido_paterno,' ',p.apellido_materno) AS asesor,
	tc.nombre as cargo,
	tc.id as cargo_id,
	a.nombre as area,
	a.id as area_id,
	SUM(tcb.total_apostado) AS total_apostado
	FROM tbl_transacciones_cabecera tcb 
	LEFT JOIN tbl_locales l ON (tcb.local_id = l.id)
	LEFT JOIN tbl_canales_venta cdv ON (tcb.canal_de_venta_id=cdv.id)
	LEFT JOIN tbl_personal_apt p ON (p.id = l.asesor_id)
	LEFT JOIN tbl_cargos tc ON (p.cargo_id = tc.id)
	LEFT JOIN tbl_areas a ON (p.area_id = a.id)
	WHERE l.reportes_mostrar=1 AND l.estado=1 AND p.cargo_id=12 AND a.id = 15 
	AND YEAR(tcb.fecha) = $year AND DATE_FORMAT(tcb.fecha,'%c') = $month $asesor_sql
	GROUP BY year ASC, month ASC,local_id ASC,cdv_id ASC ORDER BY asesor_id ";
	$result = mysqli_query($mysqli, $query);
	$numero_filas = mysqli_num_rows($result);
	$cdv = array();
	$local=array();
	$asesor = array();
	$month = array();
	$year = array();
	$array_inicial = array();
	$array_final = array();
	$detalles = array();
	$state = array();
		$state["checked"] = true;
		$state["disabled"] = false;
		$state["expanded"] = false;
		$state["selected"] = false;
	$ii=0;	
	while($row = mysqli_fetch_array($result))
	{
		if ($row["total_apostado"]==null) {
			$row["total_apostado"]=0;
		}
		$detalles["year"] = $row["year"];
		$detalles["month"] = $row["month"];
		$detalles["asesor_id"] = $row["asesor_id"];
		$detalles["asesor"] = $row["asesor"];
		$detalles["local_id"] = $row["local_id"];
		$detalles["local"] = $row["local"];
		$detalles["cdv_id"] = $row["cdv_id"];
		$detalles["cdv"] = $row["cdv"];
		$detalles["area_id"] = $row["area_id"];
		$detalles["area"] = $row["area"];
		$detalles["cargo_id"] = $row["cargo_id"];
		$detalles["cargo"] = $row["cargo"];
		$detalles["total_apostado"] = $row["total_apostado"];
		$detalles["id"] = $row["local_id"];
		$detalles["text"] = $row["cdv"];
		$detalles["name"] = $row["cdv"];
		$detalles["tags"] = array(number_format($row["total_apostado"],2));
		$detalles["parent_id"] = $row["local_id"];	
		$detalles["states"] = $state;
		$array_inicial[$row["year"]][$row["month"]][$row["asesor_id"]][$row["local_id"]][$row["cdv_id"]]= $detalles;
	}

	foreach ($array_inicial as $year => $year_value) {
		foreach ($year_value as $month => $month_value) {
			foreach ($month_value as $asesor => $asesor_value) {
				foreach ($asesor_value as $local => $local_value) {
					$totales_apostados = 0;
					foreach ($local_value as $cdv => $cdv_value) {
						if ($cdv_value["total_apostado"]==null) {
							$totales_apostados=0;
						}
						$totales_apostados += $cdv_value["total_apostado"];
						$array_final[$year."".$month."".$asesor."".$local]["id"] = $local;
						$array_final[$year."".$month."".$asesor."".$local]["tags"][0] = number_format($totales_apostados,2);
						$array_final[$year."".$month."".$asesor."".$local]["state"] = $state;
						$array_final[$year."".$month."".$asesor."".$local]["name"] = $cdv_value["local"];
						$array_final[$year."".$month."".$asesor."".$local]["text"] = $cdv_value["local"];
						$array_final[$year."".$month."".$asesor."".$local]["sql"]  = $query;
						$array_final[$year."".$month."".$asesor."".$local]["asesor_sql"]  = $asesor_sql;
						$array_final[$year."".$month."".$asesor."".$local]["filas_resultado"]  = $numero_filas;												
						$array_final[$year."".$month."".$asesor."".$local]["total_apostado"] = $totales_apostados;
						$array_final[$year."".$month."".$asesor."".$local]["nodes"][] = $cdv_value;
					}
				}
			}
		}
	}
	sort($array_final);
	header('Content-type: application/json');	
	echo json_encode($array_final);	
}

if($_POST["where"]=="sec_home_get_data_asesores"){
	$sql_what = "id,nombre,apellido_paterno,apellido_materno";
	if(array_key_exists("what", $_POST)){
		$sql_what = implode(",", $_POST["what"]);
	}
	$sql_command = "SELECT $sql_what FROM tbl_personal_apt WHERE area_id = '15' AND cargo_id = '12' AND estado = 1 ORDER BY nombre ASC";
	$sql_query = $mysqli->query($sql_command);
	while($itm=$sql_query->fetch_assoc()){
		$return["data"][]=$itm;
	}
	header('Content-type: application/json');	
	echo json_encode($return);
}
if($_POST["where"]=="sec_home_get_lista_de_clientes"){
	$sql_what = "id,nombre,razon_social";
	if(array_key_exists("what", $_POST)){
		$sql_what = implode(",", $_POST["what"]);
	}
	$sql_command = "SELECT $sql_what FROM tbl_clientes WHERE estado = 1";
	$sql_query = $mysqli->query($sql_command);
	while($itm=$sql_query->fetch_assoc()){
		$return["data"][]=$itm;
	}
	header('Content-type: application/json');	
	echo json_encode($return);
}
?>
