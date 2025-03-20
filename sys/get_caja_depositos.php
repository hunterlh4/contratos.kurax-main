<?php
include_once("db_connect.php");
include_once("sys_login.php");
include_once("globalFunctions/generalInfo/local.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


function excel_date_to_php($cell)
{
    try {
        $excelDate = $cell->getValue();
        if (PHPExcel_Shared_Date::isDateTime($cell)) {
            $excelDate = PHPExcel_Style_NumberFormat::toFormattedString($excelDate, 'YYYY-MM-DD');
        } elseif (DateTime::createFromFormat('d/m/Y H:i:s', $excelDate) !== false) {
        	$timestamp = DateTime::createFromFormat('d/m/Y H:i:s', $excelDate)->getTimestamp();
            $excelDate =   date('Y-m-d H:i:s', $timestamp); //date_format($date, 'd/m/Y H:i:s');
        } elseif (DateTime::createFromFormat('Y-m-d', $excelDate) !== false) {
			$timestamp = DateTime::createFromFormat('Y-m-d', $excelDate)->getTimestamp();
			$excelDate =   date('Y-m-d', $timestamp); //date_format($date, 'd/m/Y H:i:s');
        }
         elseif (is_numeric($excelDate)) {
            $unixDate = PHPExcel_Shared_Date::ExcelToPHP($excelDate);
            $excelDate = gmdate("d/m/Y H:i:s", $unixDate);
        } else {
            $excelDate = null;
        }
        return  $excelDate;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->message, null);
    }
}

if(isset($_POST["get_locales_caja_depositos"])){
	$get_data = $_POST["get_locales_caja_depositos"];
	$zona_id = $get_data["zona_id"];
	$locales = getLocalesByZona($zona_id);
	
	echo json_encode($locales);
	exit();
}

if(isset($_POST["sec_caja_depositos"])){
	// print_r($_POST);
	$get_data = $_POST["sec_caja_depositos"];
	// print_r($get_data); exit();
	$red_id = $get_data["sec_caja_despositos_red_id"];
	$zona_id = $get_data["zona_id"];
	$local_id = $get_data["local_id"];
	$fecha_inicio = $get_data["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));
	// $fecha_fin = $get_data["fecha_fin"];
	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));

    $cantidad_inicio = $get_data["sec_caja_depositos_cantidad_inicio"] > 0 ? $get_data["sec_caja_depositos_cantidad_inicio"] : null;
    $cantidad_fin = $get_data["sec_caja_depositos_cantidad_fin"] > 0 ? $get_data["sec_caja_depositos_cantidad_fin"] : null;

	$where_red = $local_id == "_all_terminales_" ? " AND l.red_id = 7" : "";

	$filtro_usuario_locales = "";
	if($login["usuario_locales"]){
		$filtro_usuario_locales = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";

	}

	$where_redes= "";
	if (!Empty($red_id) && $red_id != "0") {
		$where_redes = " AND l.red_id = ".$red_id;
	}

    $caja_arr = array();
	$caja_command = "
	SELECT
	c.id,
	c.fecha_operacion,
	c.turno_id,
	cd.id AS caja_deposito_id,
	cd.validar_registro,
	l.id AS local_id,
	l.nombre AS local_nombre,
	z.nombre AS zona_nombre,
	l.cc_id,
	cd.id as cd_id,
	(SELECT
		SUM(IFNULL(df.valor,0))
		FROM tbl_caja_datos_fisicos df
		WHERE df.caja_id = c.id AND df.tipo_id = '4') AS depo_venta,
	(SELECT
		SUM(IFNULL(df.valor,0))
		FROM tbl_caja_datos_fisicos df
		WHERE df.caja_id = c.id AND df.tipo_id = '3') AS depo_boveda

	FROM tbl_caja c
	LEFT JOIN tbl_caja_depositos cd ON(cd.caja_id = c.id)
	LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
	LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
	LEFT JOIN tbl_zonas z ON (l.zona_id = z.id)
	";
	$caja_command.=" WHERE c.estado = '1' AND l.estado = 1";
	$caja_command.=" AND z.status = 1";
	$caja_command.= $where_red;
	$caja_command.= $where_redes;
	$caja_command.=" AND c.validar = '1'";
	$caja_command.= $filtro_usuario_locales;
	if(!($zona_id=="_all_")){
        $caja_command.=" AND l.zona_id = '".$zona_id."'";
	}
	if(!($local_id=="_all_" || $local_id=="_all_terminales_")){
        $caja_command.=" AND l.id = '".$local_id."'";
	}
	$caja_command.=" AND c.fecha_operacion >= '".$fecha_inicio."'
	AND c.fecha_operacion < '".$fecha_fin."'
	GROUP BY c.fecha_operacion, l.id, c.turno_id
	ORDER BY c.fecha_operacion ASC, l.nombre ASC, c.turno_id ASC
	";
	// $caja_command; exit();
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$caja_query = $mysqli->query($caja_command);
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	$table=array();
	$table["tbody"]=array();
	$caja_data = array();
	while($c=$caja_query->fetch_assoc()){
		$caja_data[]=$c;
	}
	$total=array();
	$total["venta"]=0;
	$total["boveda"]=0;
	$total["total"]=0;
	$total["diferenciaboveda"]=0;
	$total["diferenciaventa"]=0;
	$total["totalventainput"]=0;
	$total["totaldevolucioninput"]=0;
	foreach ($caja_data as $key => $tr) {
		// print_r($value);
		// $tr=array();
		// 	$tr["local_id"]=$value["local_id"];
		// 	$tr["fecha_operacion"]=$value["fecha_operacion"];
		$tr["depo_total"]=($tr["depo_venta"]+$tr["depo_boveda"]);

		if($tr["depo_total"]>0){
			$total["venta"]+=$tr["depo_venta"];
			$total["boveda"]+=$tr["depo_boveda"];
			$total["total"]+=$tr["depo_total"];

			$sql_command_ = "SELECT
			*
			FROM tbl_repositorio_transacciones_bancarias lc
			WHERE lc.caja_id = '".$tr["id"]."' and tipo=0
			ORDER BY lc.id ASC";
			$sql_query_ = $mysqli->query($sql_command_);
			$input="";
			$totdepoventa=0;
			$disable="";
			if($sql_query_->num_rows){
				while($itm=$sql_query_->fetch_assoc()){
					$input.='<button id="btnCheckDeposito" class="btn btn-block btn-xs btn-success" name="cons'.$itm["id"].'" data-id="'.$itm["id"].'" data-cajaid="'.$itm["caja_id"].'" data-monto="'.$itm["importe"].'" data-fecha="'.$itm["fecha_operacion"].'" data-referencia="'.$itm["referencia"].'" data-mvto="'.$itm["numero_movimiento"].'" data-tipo="'.$itm["tipo"].'">'.$itm["importe"].'</button>';
					$totdepoventa+=$itm["importe"];
				}
			}
			else $input.='<button id="btnCheckDeposito" class="btn btn-block btn-xs btn-warning" data-id="'.$tr["id"].'" data-tipo="0"><i class="glyphicon glyphicon-search"></i></button>';
			$tr['VentaInput']=$totdepoventa;
			$tr['deposVentaInput']=$input;
			$total["totalventainput"]+=  $tr['VentaInput'];


			$sql_command_1 = "SELECT
			*
			FROM tbl_repositorio_transacciones_bancarias lc
			WHERE lc.caja_id = '".$tr["id"]."' and tipo=1
			ORDER BY lc.id ASC";
			$sql_query1 = $mysqli->query($sql_command_1);
			$input1="";
			$totdepodevolucion=0;
			$disable="";
			if($sql_query1->num_rows){
				while($itm_=$sql_query1->fetch_assoc()){
					$input1.='<button id="btnCheckDeposito" class="btn btn-block btn-xs btn-success" name="cons'.$itm_["id"].'" data-id="'.$itm_["id"].'" data-cajaid="'.$itm_["caja_id"].'" data-monto="'.$itm_["importe"].'" data-fecha="'.$itm_["fecha_operacion"].'" data-referencia="'.$itm_["referencia"].'" data-mvto="'.$itm_["numero_movimiento"].'" data-tipo="'.$itm_["tipo"].'">'.$itm_["importe"].'</button>';
					$totdepodevolucion+=$itm_["importe"];
				}
			}
			else $input1.='<button id="btnCheckDeposito" class="btn btn-block btn-xs btn-warning" data-id="'.$tr["id"].'" data-tipo="1"><i class="glyphicon glyphicon-search"></i></button>';
			$tr['DevolucionInput']=$totdepodevolucion;
			$tr['deposDevolucionInput']=$input1;
			$total["totaldevolucioninput"]+=  $tr['DevolucionInput'];
			$table["tbody"][]=$tr;
		}

	}
	// print_r($table);


    ?>



	<p><button id="btnFilterConciliados" class="btn btn-default"><i id="icoFilterConciliados" class="glyphicon glyphicon-filter"></i> Filtrar Conciliados</button></p>
	<table id="tbl_caja_depositos" class="table table-condensed table-bordered table-striped">
		<thead>
			<tr>
				<th>Fecha</th>
				<th>Zona Comercial</th>
				<th>CC</th>
				<th>Local</th>
				<th>Turno</th>
				<th>Deposito Venta</th>
				<th>Venta Banco</th>
				<th>Diferencia Deposito Venta</th>
				<th class="terminales-hide">Devolucion Boveda</th>
				<th class="terminales-hide">Devolucion Banco</th>
				<th class="terminales-hide">Diferencia Deposito Devolucion</th>
			</tr>
		</thead>
		<tbody id="tbl_caja_depositos_body">
			<?php
			foreach ($table["tbody"] as $k => $tr) {
				?>
				<tr>
					<td id="tblFecha"><?php echo $tr["fecha_operacion"]; ?></td>
					<td id="tblZona"><?php echo $tr["zona_nombre"]; ?></td>
					<td id="tblCC"><?php echo $tr["cc_id"]; ?></td>
					<td id="tblLocal"><?php echo $tr["local_nombre"]; ?></td>
					<td id="tblTurno"><?php echo $tr["turno_id"]; ?></td>
					<td id="tblMonto"><?php echo number_format($tr["depo_venta"],2); ?></td>
					<td><?php if($tr["depo_venta"] != 0) echo $tr["deposVentaInput"]; ?></td>
					<td class="td_diferencia"><?php echo number_format($tr["depo_venta"]-$tr["VentaInput"],2); ?></td>
					<td class="terminales-hide" id="tblBoveda"><?php echo number_format($tr["depo_boveda"],2); ?></td>
					<td class="terminales-hide"><?php if($tr["depo_boveda"] != 0) echo $tr["deposDevolucionInput"]; ?></td>
					<td class="td_diferencia_boveda terminales-hide"><?php echo number_format($tr["depo_boveda"]-$tr["DevolucionInput"],2); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5" class="text-right">Total:</td>
				<td><?php echo number_format($total["venta"],2); ?></td>
				<td><?php echo number_format($total["totalventainput"],2); ?></td>
				<td><?php echo number_format($total["venta"]-$total["totalventainput"],2); ?></td>
				<td class="terminales-hide"><?php echo number_format($total["boveda"],2); ?></td>
				<td class="terminales-hide"><?php echo number_format($total["totaldevolucioninput"],2); ?></td>
				<td class="terminales-hide"><?php echo number_format($total["boveda"]-$total["totaldevolucioninput"],2); ?></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
	<?php
};

if(isset($_POST["sec_caja_depositos_editar_conciliacion"])){

	$get_data = $_POST["sec_caja_depositos_editar_conciliacion"];

	$local_id = $get_data["local_id"];
	$fecha_inicio = $get_data["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));
	// $fecha_fin = $get_data["fecha_fin"];
	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));

	$caja_command_ = "
	SELECT
	c.id,
	c.fecha_operacion,
	c.turno_id,
	c.validar,
	cd.id AS caja_deposito_id,
	cd.validar_registro,
	l.id AS local_id,
	l.nombre AS local_nombre,
	l.cc_id,
	(SELECT
	SUM(IFNULL(df.valor,0))
	FROM tbl_caja_datos_fisicos df
	WHERE df.caja_id = c.id AND df.tipo_id = '4') AS depo_venta,
	(SELECT
	SUM(IFNULL(df.valor,0))
	FROM tbl_caja_datos_fisicos df
	WHERE df.caja_id = c.id AND df.tipo_id = '3') AS depo_boveda

	FROM tbl_caja c
	LEFT JOIN tbl_caja_depositos cd ON(cd.caja_id = c.id)
	LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
	LEFT JOIN tbl_locales l ON (l.id = lc.local_id)";
	$caja_command_.=" WHERE c.estado = '1'";
	$caja_command_.=" AND l.red_id IN (1,7,9)";
	$caja_command_.=" AND c.validar = '1'";
	//$caja_command_.=" AND c.validar = '1' and cd.validar_registro='0'";
	if($local_id=="_all_"){
		// $caja_command.=" WHERE l.id != 1";
	}else{
		$caja_command_.=" AND l.id = '".$local_id."'";
	}
	$caja_command_.=" AND c.fecha_operacion >= '".$fecha_inicio."'
	AND c.fecha_operacion < '".$fecha_fin."'
	GROUP BY c.fecha_operacion, l.id, c.turno_id
	ORDER BY c.fecha_operacion ASC, l.nombre ASC, c.turno_id ASC
	";

	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$caja_query_ = $mysqli->query($caja_command_);

	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");

	while($c=$caja_query_->fetch_assoc()){

		$sql_caja_deposito = "SELECT id,caja_id FROM tbl_caja_depositos WHERE caja_id = '".$c['id']."'";
		$result_caja_deposito = $mysqli->query($sql_caja_deposito);
		if($result_caja_deposito->num_rows==0){
			$insert_command_deposito = "INSERT INTO tbl_caja_depositos (caja_id,validar_registro)";
			$insert_command_deposito.= "VALUES ('".$c['id']."',0)";

			$mysqli->query($insert_command_deposito);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $insert_command_deposito;
				exit();
			}
		}


		$query = "
			SELECT * 
			FROM tbl_repositorio_transacciones_bancarias
			WHERE fecha_operacion = '{$c['fecha_operacion']}' 
				AND importe=" . str_replace(",", "" , $c['depo_venta']) . "
				AND referencia like '%".$c['cc_id']."%' 
				AND caja_id is null
		";
		$result_ = $mysqli->query($query);
		if($mysqli->error){ echo $mysqli->error; die; }
		while($r = $query_result->fetch_assoc()) {
			$rows[$r[""]] = $r[""];	
		}

		if($result_->num_rows > 0){
			$row_ = $result_->fetch_assoc();
			$rowid = $row_["id"];
			$row_importe = $row_["importe"];
			$update_estado_data = "UPDATE tbl_repositorio_transacciones_bancarias SET caja_id='".$c['id']."' , tipo='0' WHERE id =  '".$rowid."'";
			$mysqli->query($update_estado_data);
		}

		$sql_depb = "SELECT * FROM tbl_repositorio_transacciones_bancarias
		WHERE fecha_operacion = '".$c['fecha_operacion']."' and importe=".str_replace(",", "" , $c['depo_boveda'])."
		and  referencia like '%".$c['cc_id']."%' and caja_id is null";
		$result_depb = $mysqli->query($sql_depb);

		if($result_depb->num_rows>0){
			$row_b = $result_depb->fetch_assoc();
			$rowidb = $row_b["id"];
			$row_importe = $row_b["importe"];
			$update_estado_datab = "UPDATE tbl_repositorio_transacciones_bancarias SET caja_id='".$c['id']."' , tipo='1' WHERE id =  '".$rowidb."'";
			$mysqli->query($update_estado_datab);
		}
	}

	$caja_arr = array();
	$caja_command = "
	SELECT
	c.id,
	c.fecha_operacion,
	c.turno_id,
	c.validar,
	cd.id AS caja_deposito_id,
	(SELECT
	SUM(IFNULL(ex.importe,0))
	FROM tbl_repositorio_transacciones_bancarias ex
	WHERE ex.caja_id = c.id and ex.tipo=0) AS depo_ventaExcel,
	(SELECT
	SUM(IFNULL(ex.importe,0))
	FROM tbl_repositorio_transacciones_bancarias ex
	WHERE ex.caja_id = c.id and ex.tipo=1) AS depo_devolucionExcel,
	cd.validar_registro,
	l.id AS local_id,
	l.nombre AS local_nombre,
	l.cc_id,
	(SELECT
	SUM(IFNULL(df.valor,0))
	FROM tbl_caja_datos_fisicos df
	WHERE df.caja_id = c.id AND df.tipo_id = '4') AS depo_venta,
	(SELECT
	SUM(IFNULL(df.valor,0))
	FROM tbl_caja_datos_fisicos df
	WHERE df.caja_id = c.id AND df.tipo_id = '3') AS depo_boveda

	FROM tbl_caja c
	LEFT JOIN tbl_caja_depositos cd ON(cd.caja_id = c.id)
	LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
	LEFT JOIN tbl_locales l ON (l.id = lc.local_id)";
	$caja_command.=" WHERE c.estado = '1'";
	$caja_command.=" AND l.red_id IN (1,7,9)";
	$caja_command.=" AND c.validar = '1' and cd.validar_registro='0'";
	//$caja_command.=" AND c.validar = '1'";
	if($local_id=="_all_"){
		// $caja_command.=" WHERE l.id != 1";
	}else{
		$caja_command.=" AND l.id = '".$local_id."'";
	}
	$caja_command.=" AND c.fecha_operacion >= '".$fecha_inicio."'
	AND c.fecha_operacion < '".$fecha_fin."'
	GROUP BY c.fecha_operacion, l.id, c.turno_id
	ORDER BY c.fecha_operacion ASC, l.nombre ASC, c.turno_id ASC
	";

	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$caja_query = $mysqli->query($caja_command);
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	$table=array();
	$table["tbody"]=array();
	$caja_data = array();
	while($c=$caja_query->fetch_assoc()){
		$caja_data[]=$c;
	}
	$total=array();
	$total["venta"]=0;
	$total["boveda"]=0;
	$total["total"]=0;
	$total["diferenciaboveda"]=0;
	$total["diferenciaventa"]=0;
	$total["ventaExcel"]=0;
	$total["devolucionExcel"]=0;


	foreach ($caja_data as $key => $tr) {
		// print_r($value);
		// $tr=array();
		// 	$tr["local_id"]=$value["local_id"];
		// 	$tr["fecha_operacion"]=$value["fecha_operacion"];
		$tr["depo_total"]=($tr["depo_venta"]+$tr["depo_boveda"]);

		if($tr["depo_total"]>0){
			$total["venta"]+=$tr["depo_venta"];
			$total["boveda"]+=$tr["depo_boveda"];
			$total["diferenciaboveda"]+=($tr["depo_boveda"]-$tr["depo_devolucionExcel"]);
			$total["diferenciaventa"]+=($tr["depo_venta"]-$tr["depo_ventaExcel"]);
			$total["ventaExcel"]+=$tr["depo_ventaExcel"];
			$total["devolucionExcel"]+=$tr["depo_devolucionExcel"];
			$total["total"]+=$tr["depo_total"];
			$table["tbody"][]=$tr;
		}

	}
	// print_r($table);
	?>

	<!-- <a href="#" class="btn btn-success btn-sm  pull-right" style="margin-bottom: 10px" ><span class="glyphicon glyphicon-export"></span> Conciliar</a> -->
	<div class="col-md-12" id="divContPreseleccion"  style="margin-bottom:10px">
		<fieldset>
			<legend>Seleccionar y aceptar</legend>
			<div class="row" id="divPreseleccion">
			</div>
		</fieldset>
	</div>

	<table class="table table-condensed table-bordered ">
		<tr>
			<th>Fecha</th>
			<th>CC</th>
			<th>Local</th>
			<th>Turno</th>
			<th>Deposito Venta</th>
			<th>Venta Banco</th>
			<th>Diferencia Deposito Venta</th>
			<th>Devolucion Boveda</th>
			<th>Devolucion Banco</th>
			<th>Diferencia Deposito Devolucion</th>
		</tr>
		<?php
		foreach ($table["tbody"] as $k => $tr) {
			?>
			<tr>
				<td><?php echo $tr["fecha_operacion"]; ?></td>
				<td><?php echo $tr["cc_id"]; ?></td>
				<td><?php echo $tr["local_nombre"]; ?></td>
				<td><?php echo $tr["turno_id"]; ?></td>
				<td><?php echo number_format($tr["depo_venta"],2); ?></td>
				<td><?php echo number_format($tr["depo_ventaExcel"],2); ?>    <a href="#" class="monto" data-fecha="<?php echo $tr["fecha_operacion"]; ?>" data-id="<?php echo $tr["id"]; ?>" data-cc="<?php echo $tr["cc_id"]; ?>" data-monto="<?php echo $tr["depo_venta"]; ?>"><i class="glyphicon glyphicon-new-window"></i></a></td>
				<td><?php echo number_format($tr["depo_venta"]-$tr["depo_ventaExcel"],2); ?></td>
				<td><?php echo number_format($tr["depo_boveda"],2); ?></td>
				<td><?php echo number_format($tr["depo_devolucionExcel"],2); ?> <a href="#" class="devolucion" data-fecha="<?php echo $tr["fecha_operacion"]; ?>" data-id="<?php echo $tr["id"]; ?>" data-cc="<?php echo $tr["cc_id"]; ?>" data-monto="<?php echo $tr["depo_boveda"]; ?>"><i class="glyphicon glyphicon-new-window"></i></a></td>
				<td><?php echo number_format($tr["depo_boveda"]-$tr["depo_devolucionExcel"],2); ?></td>

			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="4" class="text-right">Total:</td>
			<td><?php echo number_format($total["venta"],2); ?></td>
			<td><?php echo number_format($total["ventaExcel"],2); ?></td>
			<td><?php echo number_format($total["diferenciaventa"],2); ?></td>
			<td><?php echo number_format($total["boveda"],2); ?></td>
			<td><?php echo number_format($total["devolucionExcel"],2); ?></td>
			<td><?php echo number_format($total["diferenciaboveda"],2); ?></td>
		</tr>
	</table>
	<?php
}

if(isset($_POST["sec_caja_depositos_Prevalidacion"])){
	// print_r($_POST);
	$get_data = $_POST["sec_caja_depositos_Prevalidacion"];
	// print_r($get_data); exit();
	//

    $is_terminales = $get_data["is_terminal"];
	$local_id = $get_data["local_id"];
	$fecha_inicio = $get_data["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));
	// $fecha_fin = $get_data["fecha_fin"];
	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));

    $red_id = $is_terminales == "true" ? "(7)" : "(1, 7, 9, 16)";

	$caja_command_ = "SELECT
		c.id,
		c.fecha_operacion,
		c.turno_id,
		c.validar,
		cd.id AS caja_deposito_id,
		cd.validar_registro,
		l.id AS local_id,
		l.nombre AS local_nombre,
		l.cc_id,
		(SELECT
		SUM(IFNULL(df.valor,0))
		FROM tbl_caja_datos_fisicos df
		WHERE df.caja_id = c.id AND df.tipo_id = '4') AS depo_venta,
		(SELECT
		SUM(IFNULL(df.valor,0))
	FROM tbl_caja_datos_fisicos df
	WHERE df.caja_id = c.id AND df.tipo_id = '3') AS depo_boveda
	FROM tbl_caja c
	LEFT JOIN tbl_caja_depositos cd ON(cd.caja_id = c.id)
	LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
	LEFT JOIN tbl_locales l ON (l.id = lc.local_id)";
	$caja_command_.=" WHERE c.estado = '1'";
	$caja_command_.=" AND l.red_id IN $red_id";
	$caja_command_.=" AND c.validar = '1'";
	//$caja_command_.=" AND c.validar = '1' and cd.validar_registro='0'";
	if($local_id=="_all_" || $local_id=="_all_terminales_"){
		// $caja_command.=" WHERE l.id != 1";
	}else{
		$caja_command_.=" AND l.id = '".$local_id."'";
	}
	if($login["usuario_locales"]){
        $caja_command_ .= " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
    }

	$caja_command_.=" AND c.fecha_operacion >= '".$fecha_inicio."'
	AND c.fecha_operacion < '".$fecha_fin."'
	GROUP BY c.fecha_operacion, l.id, c.turno_id
	ORDER BY c.fecha_operacion ASC, l.nombre ASC, c.turno_id ASC
	";

	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$caja_query_ = $mysqli->query($caja_command_);

	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	$transactions = array();
	while($c=$caja_query_->fetch_assoc()){
		$auto_conc = array();
		$sql_caja_deposito = "SELECT id,caja_id FROM tbl_caja_depositos WHERE caja_id = '".$c['id']."'";
		$result_caja_deposito = $mysqli->query($sql_caja_deposito);
		if($result_caja_deposito->num_rows==0){
			$insert_command_deposito = "INSERT INTO tbl_caja_depositos (caja_id,validar_registro)";
			$insert_command_deposito.= "VALUES ('".$c['id']."',0)";

			$mysqli->query($insert_command_deposito);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $insert_command_deposito;
				exit();
			}
		}

        if ($c['depo_venta'] == 0 && $c['depo_boveda'] == 0) {
            continue;
        }

        $sql_ = "SELECT * FROM tbl_repositorio_transacciones_bancarias
		WHERE fecha_operacion = '$c[fecha_operacion]' and importe > 0
		and  referencia like '%".$c['cc_id']."%' and caja_id is null";
		$result_ = $mysqli->query($sql_);
        if($result_){
            if($result_->num_rows > 0){
                $rows_aux = array();
                $ventas_sum = 0;
                $venta_total = str_replace(",", "" , $c['depo_venta']);

                $boveda_sum = 0;
                $boveda_total = str_replace(",", "" , $c['depo_boveda']);

                while($row = $result_->fetch_assoc()){
                    $ventas_sum += $row['importe'];
                    $rows_aux[] = $row;

                    if ($row['importe'] == $venta_total){
                        $auto_conc["venta"] = array();
                        $auto_conc["venta"][] = $row;
                    }

                    $boveda_sum += $row['importe'];

                    if ($row['importe'] == $boveda_total){
                        $auto_conc["boveda"] = array();
                        $auto_conc["boveda"][] = $row;
                    }
                }

                if ($ventas_sum == $venta_total && !array_key_exists('venta', $auto_conc)){
                    $auto_conc["venta"] = $rows_aux;
                }

                if ($boveda_sum == $boveda_total && !array_key_exists('boveda', $auto_conc)){
                    $auto_conc["boveda"] = $rows_aux;
                }
            }
			else
			{
				$sql_ = "SELECT * FROM tbl_repositorio_transacciones_bancarias
					WHERE fecha_operacion = '$c[fecha_operacion]' AND importe > 0
					-- AND referencia in ('DEPOSITO SIN LIBRETA' , 'DEPOSITO SIN TARJETA')
					AND caja_id IS null";
				$result_ = $mysqli->query($sql_);
				if($result_->num_rows > 0){
					$rows_aux = array();
					$ventas_sum = 0;
					$venta_total = str_replace(",", "" , $c['depo_venta']);

					$boveda_sum = 0;
					$boveda_total = str_replace(",", "" , $c['depo_boveda']);

					while($row = $result_->fetch_assoc()){
						$ventas_sum += $row['importe'];
						$rows_aux[] = $row;
						if ($row['importe'] == $venta_total){
							$auto_conc["venta"] = array();
							$auto_conc["venta"][] = $row;
						}

						$boveda_sum += $row['importe'];

						if ($row['importe'] == $boveda_total){
							$auto_conc["boveda"] = array();
							$auto_conc["boveda"][] = $row;
						}
					}
					if ($ventas_sum == $venta_total && !array_key_exists('venta', $auto_conc)){
						$auto_conc["venta"] = $rows_aux;
					}
					if ($boveda_sum == $boveda_total && !array_key_exists('boveda', $auto_conc)){
						$auto_conc["boveda"] = $rows_aux;
					}
				}
			}
        }


		if(!empty($auto_conc)) $transactions[] = array_merge($c, $auto_conc);
	}
	echo json_encode($transactions); die;
}

if(isset($_POST["sec_caja_conciliar_automatico"])){
	$get_data = $_POST["sec_caja_conciliar_automatico"];
	$mysqli->query("START TRANSACTION");
	foreach ($get_data as $data) {
		$update_estado_data = "UPDATE tbl_repositorio_transacciones_bancarias SET caja_id='".$data["caja_id"]."' , tipo='".$data["tipo"]."' WHERE id =  '".$data["id_transaccion"]."'";
		$mysqli->query($update_estado_data);
		$update_estado_data = "UPDATE tbl_caja_depositos SET validar_registro=1 WHERE caja_id='".$data["caja_id"]."'";
		$mysqli->query($update_estado_data);
	}
	$mysqli->query("COMMIT");
}

if(isset($_FILES['concar_file'])){
    $ext = pathinfo($_FILES['concar_file']['name'], PATHINFO_EXTENSION);
    if($ext == "xls" || $ext == "xlsx"){
        $return = array();
        include("global_config.php");
        include("db_connect.php");
        include("sys_login.php");
        include("/var/www/html/sys/helpers.php");
        require_once '../phpexcel/classes/PHPExcel.php';
        $tmpfname = $_FILES['concar_file']['tmp_name'];
        $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
        libxml_use_internal_errors(TRUE);
        $excelObj = $excelReader->load($tmpfname);
        $worksheet = $excelObj->getSheet(0);
        $lastRow = $worksheet->getHighestRow();
        $firstRow = 10;

        for ($row = $firstRow; $row <= $lastRow; $row++) {
            if($worksheet->getCell('A'. $row)->getValue() != ""){
                $fecha_documento = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('F'. $row)->getValue());
                $fecha_documento = $fecha_documento ? $fecha_documento->format("Y-m-d") : "Sin fecha";
                $fecha_comprobado = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('G'. $row)->getValue());
                $fecha_comprobado = $fecha_comprobado ? $fecha_comprobado->format("Y-m-d") : "Sin fecha";
                $fecha_vencimiento = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('H'. $row)->getValue());
                $fecha_vencimiento = $fecha_vencimiento ? $fecha_vencimiento->format("Y-m-d") : "Sin fecha";

                $data = [
                    "cuenta" => trim($worksheet->getCell('A'. $row)->getValue()),
                    "anexo" => trim($worksheet->getCell('B'. $row)->getValue()),
                    "nombre" => trim($worksheet->getCell('C'. $row)->getValue()),
                    "tipo_doc" => trim($worksheet->getCell('D'. $row)->getValue()),
                    "numero_documento" => trim($worksheet->getCell('E'. $row)->getValue()),
                    "fecha_documento" => $fecha_documento,
                    "fecha_comprobado" => $fecha_comprobado,
                    "fecha_vencimiento" => $fecha_vencimiento,
                    "sd" => trim($worksheet->getCell('I'. $row)->getValue()),
                    "numero" => trim($worksheet->getCell('J'. $row)->getValue()),
                    "glosa" => trim($worksheet->getCell('K'. $row)->getValue()),
                    "mo" => trim($worksheet->getCell('L'. $row)->getValue()),
                    "saldo_dolares_debe" => $worksheet->getCell('M'. $row)->getValue(),
                    "saldo_dolares_haber" => $worksheet->getCell('N'. $row)->getValue(),
                    "saldo_moneda_nacional_debe" => $worksheet->getCell('O'. $row)->getValue(),
                    "saldo_moneda_nacional_haber" => $worksheet->getCell('P'. $row)->getValue(),
                ];
                $data["unique_id"] = md5(
                        $data["cuenta"] .
                        $data["anexo"] .
                        $data["nombre"] .
                        $data["numero_documento"] .
                        $data["fecha_documento"] .
                        $data["sd"] .
                        $data["numero"] .
                        $data["glosa"] .
                        $data["saldo_moneda_nacional_debe"] .
                        $data["saldo_moneda_nacional_haber"]);
                $return[] = $data;
            }
            else
            {
            	break;
            }
        }

        if(!empty($return)){
            $mysqli->query("TRUNCATE tbl_repositorio_prestamos_boveda;");
            $ignore_fields = ["cuenta", "anexo",  "nombre",  "numero_documento",  "fecha_documento",  "sd",  "numero",  "glosa", "saldo_moneda_nacional_debe",  "saldo_moneda_nacional_haber"];
            feed_database($return, 'tbl_repositorio_prestamos_boveda', 'unique_id', true, $ignore_fields);
            echo json_encode(true);
            return;
        }
        echo json_encode(false);
        return;
    }
    echo json_encode(false);
    return;
}

if(isset($_POST["sec_caja_archivo"])){
	$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
	if($ext == "xls" || $ext == "xlsx"){
		$return = array();
		$return["memory_init"]=memory_get_usage();
		$return["time_init"] = microtime(true);
		include("global_config.php");
		include("db_connect.php");
		include("sys_login.php");
		include("/var/www/html/sys/helpers.php");
		require_once '../phpexcel/classes/PHPExcel.php';
		$tmpfname = $_FILES['file']['tmp_name'];
		$excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
		libxml_use_internal_errors(TRUE);
		$excelObj = $excelReader->load($tmpfname);
		$worksheet = $excelObj->getSheet(0);
		$lastRow = $worksheet->getHighestRow();
		$firstRow = 1;
		$titulo_excel = $worksheet->getCell('A1')->getValue();
		
		if($titulo_excel == "MOVIMIENTO DE CUENTAS - CAJA HUANCAYO")
		{
			$firstRow = 10;
			$errors = [];
			$columns_format = [
					["value" => "FECHA", 		"column" => "A" ] ,
					["value" => "HORA", 		"column" => "B" ] ,
					["value" => "MEDIO", 		"column" => "C" ] ,
					["value" => "OPERACIÓN", 	"column" => "D" ] ,
					["value" => "MONTO", 		"column" => "E" ] ,
					["value" => "ITF", 			"column" => "F" ] ,
					["value" => "COMISIÓN", 	"column" => "G" ] ,
					["value" => "SALDO", 		"column" => "H" ] 
			];
			foreach ($columns_format as $key => $value) {
				$column_letter = $value["column"];/*A B C -...*/
				$column_xls_value = $worksheet->getCell($column_letter.$firstRow)->getValue();
				if( strtoupper($column_xls_value) != strtoupper($value["value"]) )
				{
					$errors[] = "Columna <b>".$column_letter.$firstRow."</b> incorrecta  -  Se esperaba <b>" .$value["value"] ."</b> - " .$column_xls_value;
				}
			}
			$nro_registros = $lastRow - $firstRow - 2;
			if($nro_registros <= 0 )
			{
				$errors[] = "<b>No hay registros</b>";
			}

			if( count($errors) > 0 )
			{
				echo json_encode(
					["error" => true ,
						"msg" => "Error en archivo xls",
						"msg_error" => implode("<br>", $errors)
					]
				);
				return;
			}

			$extract = array();
			for ($row = $firstRow + 1; $row <= $lastRow; $row++) {
				if($worksheet->getCell('A'.$row)->getValue() != "" ){
					$operacion = $worksheet->getCell('D'.$row)->getValue();
					if( $operacion != "DEPOSITO")
					{
						continue;
					}
					//$fecha = DateTime::createFromFormat('d/m/Y H:i:s', $worksheet->getCell('A'.$row)->getValue()." ".$worksheet->getCell('B'.$row)->getValue())->format('Y-m-d H:i:s');
					//$fecha = DateTime::createFromFormat('Y-m-d H:i:s', $worksheet->getCell('A'.$row)->getValue()." ".$worksheet->getCell('B'.$row)->getValue())->format('Y-m-d H:i:s');
					$fecha = excel_date_to_php($worksheet->getCell('A'.$row));

					$descripcion = $worksheet->getCell('D'.$row)->getValue();
					$monto = $worksheet->getCell('E'.$row)->getValue();
					$itf = $worksheet->getCell('F'.$row)->getValue();
					$saldo = $worksheet->getCell('H'.$row)->getValue();

					$uid = md5($operacion.$fecha.$descripcion.$monto);
					$extract[$uid] = [
						"at_unique_id" => $uid,
						"moneda_id" => 1,
						"cuenta_id" => 1, // cuenta
						"codigo" => 0,
						"fecha_operacion" => $fecha,
						"fecha_valor" => $fecha,
						"referencia" => str_replace("'", "", $descripcion),
						"importe" => (float)$monto,
						"itf" => $itf,
						"saldo_contable" => $saldo,
						"oficina" => "",
						"numero_movimiento" => "",
						"fecha_ingreso" => date("Y-m-d"),
						"ultima_edicion" => date("Y-m-d"),
						"insert_tipo" => "import",
						"estado" => 0
					];
				}
			}
			$query_in = "";
			foreach ($extract as $key => $row) $query_in .= "'".$key."',";
			$query_in = substr($query_in, 0,-1);
			$query = "SELECT at_unique_id FROM tbl_repositorio_transacciones_bancarias WHERE at_unique_id IN(".$query_in.")";
			$result = $mysqli->query($query);
			while($uid = $result->fetch_assoc()){
				if(isset($extract[$uid["at_unique_id"]]))
					unset($extract[$uid["at_unique_id"]]);
			}
			if(!empty($extract)){
				feed_database($extract, 'tbl_repositorio_transacciones_bancarias', 'at_unique_id', false);
			}
			echo json_encode(true);
		}
		else if ( $titulo_excel == "N Cuenta" )
		{/*xls*/
			//CAJA PIURA
			$firstRow = 1;
			$errors = [];
			$columns_format = [
					["value" => "N Cuenta", 		"column" => "A" ] ,
					["value" => "Importe", 	"column" => "E" ] ,
					["value" => "Fecha Transaccion 2", 		"column" => "G" ] ,
					["value" => "CECO 2", 		"column" => "H" ] ,
			];
			foreach ($columns_format as $key => $value) {
				$column_letter = $value["column"];/*A B C -...*/
				$column_xls_value = $worksheet->getCell($column_letter.$firstRow)->getValue();
				if( strtoupper($column_xls_value) != strtoupper($value["value"]) )
				{
					$errors[] = "Columna ".$column_letter.$firstRow." incorrecta  -  Se esperaba " .$value["value"] ." - " .$column_xls_value;
				}
			}
			$nro_registros = $lastRow - $firstRow ;
			if($nro_registros <= 0 )
			{
				$errors[] = "<b>No hay registros</b>";
			}

			if( count($errors) > 0 )
			{
				echo json_encode(
					[	
						"error" => true ,
						"msg" => "Error en el formato de Caja Piura. ". implode(", ", $errors),
						"msg_error" => implode("<br>", $errors)
					]
				);
				return;
			}

			$num_cuenta = $worksheet->getCell('A2')->getValue();
			$cuenta_id = '';
			$banco_id = false; $moneda_id = 1;

			$fetch_query = $mysqli->query("SELECT id, banco_id, moneda_id FROM cont_num_cuenta WHERE num_cuenta_corriente = '$num_cuenta' AND status = 1 LIMIT 1")->fetch_assoc(); 
			if ($fetch_query) {
				$cuenta_id = $fetch_query['id'];
				$banco_id = $fetch_query['banco_id'];
				$moneda_id = $fetch_query['moneda_id'];
			} else
			if ($cuenta_id == ''){
				echo json_encode([
					'error' => true,
					'msg' => 'Error en formato CAJA PIURA. El número de cuenta en el archivo no se encuentra registrado'
				]);
				exit();
			} 

			$extract = array();
			for ($row = $firstRow + 1; $row <= $lastRow; $row++) {
				if($worksheet->getCell('A'.$row)->getValue() != "" ){
					$celda_fecha = $worksheet->getCell('G'.$row)->getValue();
					$excelDate = str_replace('/', '-', $celda_fecha);
					$fechaExcelToPHP = PHPExcel_Shared_Date::ExcelToPHP($excelDate+1);
            		$fecha =  date('Y-m-d', $fechaExcelToPHP);
					$hora = '';

					$numero_movimiento = $worksheet->getCell('C'.$row)->getValue();
					$descripcion = $worksheet->getCell('B'.$row)->getValue();
					$monto = str_replace(",", "", $worksheet->getCell('E'.$row)->getValue());
					$uid = md5($fecha.$hora.$descripcion.$monto);
					$extract[$uid] = [
						"at_unique_id" => $uid,
						"banco_id" => $banco_id, 
						"moneda_id" => $moneda_id, //1,
						"cuenta_id" => $cuenta_id, //1,
						"codigo" => null,
						"fecha_operacion" => $fecha,
						"fecha_valor" => $fecha,
						"referencia" => str_replace("'", "", $descripcion),
						"importe" => (float)$monto,
						"numero_movimiento" => $numero_movimiento,
						"fecha_ingreso" => date("Y-m-d"),
						"ultima_edicion" => date("Y-m-d"),
						"insert_tipo" => "import",
						"estado" => 0
					];
				}
			}
			$query_in = "";
			foreach ($extract as $key => $row) $query_in .= "'".$key."',";
			$query_in = substr($query_in, 0,-1);
			$query = "SELECT at_unique_id FROM tbl_repositorio_transacciones_bancarias WHERE at_unique_id IN(".$query_in.")";
			$result = $mysqli->query($query);
			while($uid = $result->fetch_assoc()){
				if(isset($extract[$uid["at_unique_id"]]))
					unset($extract[$uid["at_unique_id"]]);
			}
			if(!empty($extract)){
				feed_database($extract, 'tbl_repositorio_transacciones_bancarias', 'at_unique_id', false);
			}
			echo json_encode(true);
		}
		
		$text_cuenta = $worksheet->getCell('A7')->getValue();
		$text_cuenta = substr($text_cuenta, 0,15);

		if($text_cuenta == 'Cuenta Actual: '){
			// BBVA

			$num_cuenta = $worksheet->getCell('A7')->getValue();

			$num_cuenta = substr($num_cuenta, 15,20);

			$cuenta_id = '';
			$banco_id = false; $moneda_id = 1;

			$fetch_query = $mysqli->query("SELECT id, banco_id, moneda_id FROM cont_num_cuenta WHERE num_cuenta_corriente = '$num_cuenta' AND status = 1 LIMIT 1")->fetch_assoc(); 

			

			if ($fetch_query) {
				$cuenta_id = $fetch_query['id'];
				$banco_id = $fetch_query['banco_id'];
				$moneda_id = $fetch_query['moneda_id'];
			} else
			if ($cuenta_id == ''){
				echo json_encode([
					'error' => true,
					'msg' => 'Error en formato BBVA. El número de cuenta en el archivo no se encuentra registrado'
				]);
				exit();
			} 


			for($firstRow; $firstRow <= $lastRow; $firstRow++){
				if($worksheet->getCell('F'.$firstRow)->getValue() == "Importe"){
					// $banco_id = 12;
					break;
				}
				if($worksheet->getCell('D'.$firstRow)->getValue() == "Importe"){
					// caja piuraaaa
					// $banco_id = 15;
					break;
				}
			}
			if (!$banco_id) echo json_encode(false);

			// $moneda_id = 1;
			$extract = array();

			for ($row = $firstRow+1; $row <= $lastRow; $row++) {
				if($worksheet->getCell('A'.$row)->getValue() != ""){
					if($banco_id == 12){
						$movNumber = $worksheet->getCell('D'.$row)->getValue();

						$celda_fecha = $worksheet->getCell('A'.$row)->getFormattedValue();
						$fecha_op = date("Y-m-d", strtotime($celda_fecha));

						$uid = md5($banco_id.$movNumber.$moneda_id.$fecha_op);
						$extract[$uid] = [
							"at_unique_id" => $uid,
							"banco_id" => $banco_id,
							"moneda_id" => $moneda_id, //1,
							"cuenta_id" => $cuenta_id, //1,
							"codigo" => $worksheet->getCell('C'.$row)->getValue(),
							"fecha_operacion" =>$fecha_op,
							"fecha_valor" => date("Y-m-d", strtotime($worksheet->getCell('B'.$row)->getValue())),
							"referencia" => str_replace("'", "", $worksheet->getCell('E'.$row)->getValue()),
							"importe" => str_replace(",", "", $worksheet->getCell('F'.$row)->getValue()),
							"oficina" => $worksheet->getCell('G'.$row)->getValue(),
							"numero_movimiento" => $movNumber,
							"fecha_ingreso" => date("Y-m-d"),
							"ultima_edicion" => date("Y-m-d"),
							"insert_tipo" => "import",
							"estado" => 0
						];
					}
					// caja piuraaaa
					else if($banco_id == 15){
						//FALTA PROGRAMAR.. FECHA NO FUNCIONA!
						$fecha = DateTime::createFromFormat('d/m/Y H:i:s', $worksheet->getCell('A'.$row)->getValue()." ".$worksheet->getCell('B'.$row)->getValue())->format('Y-m-d H:i:s');
						$descripcion = $worksheet->getCell('C'.$row)->getValue();
						$importe = str_replace(",", "", $worksheet->getCell('D'.$row)->getValue());
						// $fecha = date_format($worksheet->getCell('A'.$row)->getValue()." ".$worksheet->getCell('B'.$row)->getValue(), 'd/m/Y H:s:i');
						//FALTA PROGRAMAR.. FECHA NO FUNCIONA!
						$uid = md5($banco_id.$fecha.$descripcion.$importe.$moneda_id);
						$extract[$uid] = [
							"at_unique_id" => $uid,
							"banco_id" => $banco_id,
							"moneda_id" => $moneda_id,
							"cuenta_id" => $cuenta_id,
							"codigo" => 0,
							"fecha_operacion" => $fecha,
							"fecha_valor" => $fecha,
							"referencia" => str_replace("'", "", $descripcion),
							"importe" => (float)$importe,
							"oficina" => "",
							"numero_movimiento" => "",
							"fecha_ingreso" => date("Y-m-d"),
							"ultima_edicion" => date("Y-m-d"),
							"insert_tipo" => "import",
							"estado" => 0
						];
					}
				}
			}
			$query_in ="";
			foreach ($extract as $key => $row) $query_in .= "'".$key."',";
			$query_in = substr($query_in, 0,-1);
			$query = "SELECT at_unique_id FROM tbl_repositorio_transacciones_bancarias where at_unique_id IN(".$query_in.")";
			$result = $mysqli->query($query);
			while($uid = $result->fetch_assoc()){
				if(isset($extract[$uid["at_unique_id"]]))
					unset($extract[$uid["at_unique_id"]]);
			}
			if(!empty($extract)){
				feed_database($extract, 'tbl_repositorio_transacciones_bancarias', 'at_unique_id', false);
			}
			echo json_encode(true);
		} else {
			echo json_encode([
				'error' => true,
				'msg' => 'Revise el contenido del Archivo de Excel que intenta importar'
			]);
			exit();
		}
	} else {
		echo json_encode([
			'error' => true,
			'msg' => 'Error en la extensión, solo se permite .xls y .xslx. Revise el formato del archivo que intenta importar'
		]);
		exit();
	}
}

if(isset($_POST["sec_caja_check_conciliations"])){
	$get_data = $_POST["sec_caja_check_conciliations"];
	$query = "SELECT count(*) as count_depositos from tbl_caja_depositos WHERE caja_id = {$get_data['id']} AND validar_registro = 1";

	$response = $mysqli->query($query)->fetch_assoc();
	echo $response['count_depositos'];
}

if(isset($_POST["sec_caja_remove_conciliations"])){
	$get_data = $_POST["sec_caja_remove_conciliations"];
	$query = "UPDATE tbl_repositorio_transacciones_bancarias SET caja_id=null , tipo=null WHERE caja_id = '{$get_data['id']}'";
	$mysqli->query($query);
	$query = "UPDATE tbl_caja_depositos SET validar_registro = 0 WHERE caja_id = {$get_data['id']}";
	$mysqli->query($query);
}

if(isset($_POST["get_concar_history"])){
	$get_data = $_POST["get_concar_history"];

	$get_data['offset'] = 5*$get_data['page'];
	$where = $get_data['is_terminal'] == "true" ? "WHERE l.red_id = 7 OR ch.local_id = -2" : "";

	$table = [];
	$result = $mysqli->query("SELECT
			ch.id,
			ch.local_id,
			l.nombre,
			ch.cambio,
			ch.correlativo,
			u.usuario,
			f.url,
			ch.fecha_operacion,
			ch.fecha_inicio,
			ch.fecha_fin
		FROM tbl_concar_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		LEFT JOIN tbl_locales l ON l.id = ch.local_id
		LEFT JOIN tbl_usuarios u ON u.id = ch.usuario_id
        $where
		ORDER BY ch.fecha_operacion DESC
		LIMIT 5 OFFSET {$get_data['offset']}
	");
	while($r = $result->fetch_assoc()) $table[] = $r;

	$num_rows = $mysqli->query("
		SELECT ch.id
		FROM tbl_concar_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		LEFT JOIN tbl_locales l ON l.id = ch.local_id
		LEFT JOIN tbl_usuarios u ON u.id = ch.usuario_id
        $where
	")->num_rows;



	$body = "";
	if(!empty($table)){
		$body .= '<thead>';
		$body .= '<tr class="bg-warning">';
		$body .= '<th>Local Creación</th>';
		$body .= '<th>Tipo Cambio</th>';
		$body .= '<th>Correlativo Inicial</th>';
		$body .= '<th>Usuario</th>';
		$body .= '<th>Fecha Inicio</th>';
		$body .= '<th>Fecha Fin</th>';
		$body .= '<th>Fecha Op.</th>';
		$body .= '<th>Ver</th>';
		$body .= '</tr>';
		$body .= '</thead>';
		$body .= '<tbody>';
		foreach ($table as $row) {
            $loc_creac_name = "Todos";
            if (isset($row["local_id"])){
                if($row["local_id"] == -1){
                    $loc_creac_name =  "Todos";
                } else if($row["local_id"] == -2){
                    $loc_creac_name =  "Todos los Terminales";
                } else {
                    $loc_creac_name = '['.$row["local_id"].'] '.$row["nombre"];
                }
            }

			$body .= '<tr>';
			$body .= '<td>'.$loc_creac_name.'</td>';
			$body .= '<td>'.number_format($row["cambio"], 2, ".", ",").'</td>';
			$body .= '<td>'.$row['correlativo'].'</td>';
			$body .= '<td>'.$row['usuario'].'</td>';
			$body .= '<td>'.date('Y-m-d', strtotime($row['fecha_inicio'])).'</td>';
			$body .= '<td>'.date('Y-m-d', strtotime($row['fecha_fin'])).'</td>';
			$body .= '<td>'.$row['fecha_operacion'].'</td>';
			$body .= '<td>';
			$body .= '<a href="/export/files_exported/'.$row['url'].'" class="btn btn-xs btn-success"><i class="fa fa-file-o"></i></a>';
			if($login["area_id"] == 6) $body .= ' <button id="deleteConcarHistory" data-id="'.$row["id"].'" class="btn btn-xs btn-danger"><i class="fa fa-close"></i></button>';
			$body .= '</td>';
			$body .= '</tr>';
		}
		$body .= '</tbody>';
	}

	echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
}

if(isset($_POST["get_concar_boveda_history"])){
    $get_data = $_POST["get_concar_boveda_history"];

    $get_data['offset'] = 5 * $get_data['page'];

    $table = [];
    $result = $mysqli->query("SELECT
			ch.id,
			l.cc_id,
			l.nombre,
			ch.cambio,
			ch.correlativo,
			u.usuario,
			f.url,
			ch.fecha_operacion,
			ch.fecha_inicio,
			ch.fecha_fin
		FROM tbl_concar_boveda_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		LEFT JOIN tbl_locales l ON l.id = ch.local_id
		LEFT JOIN tbl_usuarios u ON u.id = ch.usuario_id
		ORDER BY ch.fecha_operacion DESC
		LIMIT 5 OFFSET {$get_data['offset']}
	");
    while($r = $result->fetch_assoc()) $table[] = $r;

    $num_rows = $mysqli->query("
		SELECT ch.id
		FROM tbl_concar_boveda_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		LEFT JOIN tbl_locales l ON l.id = ch.local_id
		LEFT JOIN tbl_usuarios u ON u.id = ch.usuario_id
	")->num_rows;

    $body = "";
    if(!empty($table)){
        $body .= '<thead>';
        $body .= '<tr class="bg-warning">';
        $body .= '<th>Local Creación</th>';
        $body .= '<th>Tipo Cambio</th>';
        $body .= '<th>Correlativo Inicial</th>';
        $body .= '<th>Usuario</th>';
        $body .= '<th>Fecha Inicio</th>';
        $body .= '<th>Fecha Fin</th>';
        $body .= '<th>Fecha Op.</th>';
        $body .= '<th>Ver</th>';
        $body .= '</tr>';
        $body .= '</thead>';
        $body .= '<tbody>';
        foreach ($table as $row) {
            $body .= '<tr>';
            $body .= '<td>'.(($row["cc_id"] != null) ? '['.$row["cc_id"].'] '.$row["nombre"] : 'Todos').'</td>';
            $body .= '<td>'.number_format($row["cambio"], 2, ".", ",").'</td>';
            $body .= '<td>'.$row['correlativo'].'</td>';
            $body .= '<td>'.$row['usuario'].'</td>';
            $body .= '<td>'.date('Y-m-d', strtotime($row['fecha_inicio'])).'</td>';
            $body .= '<td>'.date('Y-m-d', strtotime($row['fecha_fin'])).'</td>';
            $body .= '<td>'.$row['fecha_operacion'].'</td>';
            $body .= '<td>';
            $body .= '<a href="/export/files_export/'.$row['url'].'" class="btn btn-xs btn-success"><i class="fa fa-file-o"></i></a>';
            if($login["area_id"] == 6) $body .= ' <button id="deleteConcarBovedaHistory" data-id="'.$row["id"].'" class="btn btn-xs btn-danger"><i class="fa fa-close"></i></button>';
            $body .= '</td>';
            $body .= '</tr>';
        }
        $body .= '</tbody>';
    }

    echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
}

if(isset($_POST["delete_concar_history"])){
	$get_data = $_POST["delete_concar_history"];
	$exported = [];
	$result = $mysqli->query("
		SELECT
			f.id,
			f.url
		FROM tbl_concar_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		WHERE ch.id =".$get_data["id"]
	);
	while($r = $result->fetch_assoc()) $exported = $r;

	unlink("/var/www/html/export/files_exported/".$exported["url"]);
	$mysqli->query("DELETE FROM tbl_exported_files WHERE id =".$exported["id"]);
	$mysqli->query("DELETE FROM tbl_concar_historico WHERE id =".$get_data["id"]);
}

if(isset($_POST["delete_concar_boveda_history"])){
    $get_data = $_POST["delete_concar_boveda_history"];
    $exported = [];
    $result = $mysqli->query("
		SELECT
			f.id,
			f.url
		FROM tbl_concar_boveda_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		WHERE ch.id =".$get_data["id"]
    );
    while($r = $result->fetch_assoc()) $exported = $r;

    unlink("/var/www/html/export/files_export/".$exported["url"]);
    $mysqli->query("DELETE FROM tbl_exported_files WHERE id =".$exported["id"]);
    $mysqli->query("DELETE FROM tbl_concar_boveda_historico WHERE id =".$get_data["id"]);
}

if(isset($_POST["get_locales"])){
    $get_locales = $_POST["get_locales"];

    $where_red = "";
    $locales_command = "";
    $locales_arr = [];

    if($get_locales === "true"){
        $where_red = " AND l.red_id = 7";
        $locales_arr[] = [
            "id" => "_all_terminales_",
            "nombre" => "Todos (Puede demorar)"
        ];
        //$locales_arr["_all_terminales_"]="Todos (Puede demorar)";
    } else {
        $locales_arr[] = [
            "id" => "_all_",
            "nombre" => "Todos (Puede demorar)"
        ];
    }

    if($login["usuario_locales"]){
        $locales_command = " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
    }

    $locales_command = "
            SELECT l.id, l.cc_id, l.nombre FROM tbl_locales l
			INNER JOIN tbl_zonas as z ON z.id = l.zona_id
			WHERE z.status = 1 AND l.estado = 1
			$where_red 
			$locales_command	
            ORDER BY l.nombre ASC
        ";

    $locales_query = $mysqli->query($locales_command);
    if($mysqli->error){
		print_r($mysqli->error);
		exit();
    }
    while($l=$locales_query->fetch_assoc()){
        $locales_arr[] = [
                "id" => $l["id"],
                "nombre" => "[$l[cc_id]] $l[nombre]"
        ];
        //$locales_arr[$l["id"]]='['.$l["cc_id"].'] '.$l["nombre"];
    }
    echo json_encode($locales_arr);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_caja_depositos_listar_zonas") 
{
	$param_caja_despositos_red_id = $_POST["param_caja_despositos_red_id"];

	$query = "";
	$where_red = "";

	if($param_caja_despositos_red_id != 0)
	{
		$where_red = " AND rz.red_id = '".$param_caja_despositos_red_id."' ";
	}

	$query = 
    "
        SELECT
			z.id AS zona_id, z.nombre AS zona_nombre,
			rz.nombre AS empresa_nombre
		FROM tbl_zonas z
			INNER JOIN tbl_razon_social rz
			ON z.razon_social_id = rz.id
			LEFT JOIN tbl_locales_redes lr
			ON rz.red_id = lr.id
		WHERE z.status = 1 AND lr.status = 1
			".$where_red."
    ";

	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["codigo"] = 2;
		$result["result"] = $mysqli->error;
		exit();
	}
	
	if(count($list) == 0)
	{
		$result["http_code"] = 400;
		$result["codigo"] = 1;
		$result["result"] = "No se encontro resultados.";
	} 
	elseif (count($list) > 0) 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["codigo"] = 1;
		$result["result"] = "No existen registros.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_caja_depositos_listar_locales_por_zona") 
{
	$param_red_id = $_POST["param_red_id"];
	$param_zona_id = $_POST["param_zona_id"];

	$query = "";
	$permisos_locales = "";
    $where_zona = "";

    if($login && $login["usuario_locales"])
    {
        $permisos_locales = " AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
    }

    if ($param_zona_id != "_all_")
    {
        $where_zona = " AND z.status = 1 AND l.zona_id = ".$param_zona_id;
    }
    else
    {
    	if($param_red_id != "0")
    	{
    		$where_zona = " AND l.red_id  = ".$param_red_id;
    	}
    }

	$query = 
    "
    	SELECT 
    		l.id, CONCAT('[',IFNULL(l.cc_id,''),'] ', IFNULL(l.nombre,'')) as nombre
		FROM tbl_locales l
			INNER JOIN tbl_zonas as z ON z.id = l.zona_id
			INNER JOIN tbl_razon_social rz
			ON z.razon_social_id = rz.id
			LEFT JOIN tbl_locales_redes lr
			ON rz.red_id = lr.id
		WHERE l.estado = 1 AND lr.status = 1
			$where_zona
			$permisos_locales
		ORDER BY l.nombre ASC
    ";

	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["codigo"] = 2;
		$result["result"] = $mysqli->error;
		exit();
	}
	
	if(count($list) == 0)
	{
		$result["http_code"] = 400;
		$result["codigo"] = 1;
		$result["result"] = "No se encontro resultados.";
		$result["query"] = $query;
	}
	elseif (count($list) > 0) 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
		$result["query"] = $query;
	}
	else 
	{
		$result["http_code"] = 400;
		$result["codigo"] = 1;
		$result["result"] = "No existen registros.";
		$result["query"] = $query;
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_caja_depositos_modal_transcribir_concar_boveda") 
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		include("global_config.php");
	    include("db_connect.php");
	    include("sys_login.php");
	    include("/var/www/html/sys/helpers.php");
	    require_once '../phpexcel/classes/PHPExcel.php';
	    
	    $return = array();

	    $tmpfname = $_FILES['archivo_transcribir_concar_boveda']['tmp_name'];
	    
	    $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
	    
	    libxml_use_internal_errors(TRUE);
	    $excelObj = $excelReader->load($tmpfname);
	    
	    $worksheet = $excelObj->getSheet(0);
	    $lastRow = $worksheet->getHighestRow();

	    $firstRow = 10;

	    // ELIMINAR FILAS EXISTENTES
	    for ($row = $firstRow; $row <= $lastRow; $row++)
	    {
	    	if($worksheet->getCell('A'. $row)->getValue() != "")
	        {
	        	$param_tipo_doc = trim($worksheet->getCell('D'. $row)->getValue());

	        	if($param_tipo_doc == "PR")
	        	{
	        		$param_anexo = trim($worksheet->getCell('B'. $row)->getValue());

	        		$primeros_cuatro = substr($param_anexo, 0, 4);
					
					$query_select = 
			        "
			        	SELECT 
			        		id 
			        	FROM tbl_repositorio_prestamos_boveda
						WHERE tipo_doc = 'PR' AND anexo LIKE '".$primeros_cuatro."%'
			        ";

			        $query_select_result = $mysqli->query($query_select);

			        if($mysqli->error)
					{
						$error .= $mysqli->error;

						$result["http_code"] = 400;
						$result["status"] = "Ocurrio un error.";
						$result["error"] = $error;

						echo json_encode($result);
						exit();
					}

					$row_count_query_select_result = $query_select_result->num_rows;

					$ids_data = '';
					$contador_ids = 0;
					
					if($row_count_query_select_result > 0) 
					{
						while ($sel = $query_select_result->fetch_assoc()) 
						{
							if ($contador_ids > 0) 
							{
								$ids_data .= ',';
							}

							$ids_data .= $sel["id"];			
							$contador_ids++;
						}

						$query_delete = 
				        "
				        	DELETE FROM tbl_repositorio_prestamos_boveda
							WHERE id IN ($ids_data)
				        ";

				        $mysqli->query($query_delete);

				        if($mysqli->error)
						{
							$error .= $mysqli->error;

							$result["http_code"] = 400;
							$result["status"] = "Ocurrio un error.";
							$result["error"] = $error;

							echo json_encode($result);
							exit();
						}
					}
	        	}
	        }
	        else
	        {
	        	break;
	        }
	    }

	    $firstRow = 10;
	    // REGISTRAR FILAS EXISTENTES
	    for ($row = $firstRow; $row <= $lastRow; $row++)
	    {
	    	$campos_cabecera_saldo_dolares_debe = "";
	    	$campos_valores_saldo_dolares_debe = "";
	    	$campos_cabecera_saldo_dolares_haber = "";
	    	$campos_valores_saldo_dolares_haber = "";
	    	$campos_cabecera_saldo_nacional_debe = "";
	    	$campos_valores_saldo_nacional_debe = "";
	    	$campos_cabecera_saldo_nacional_haber = "";
	    	$campos_valores_saldo_nacional_haber = "";

	    	if($worksheet->getCell('A'. $row)->getValue() != "")
	        {
	        	$param_tipo_doc = trim($worksheet->getCell('D'. $row)->getValue());

	        	if($param_tipo_doc == "PR")
	        	{
	        		$param_cuenta = trim($worksheet->getCell('A'. $row)->getValue());
					$param_anexo = trim($worksheet->getCell('B'. $row)->getValue());
			        $param_nombre = trim($worksheet->getCell('C'. $row)->getValue());
			        $param_tipo_doc = trim($worksheet->getCell('D'. $row)->getValue());
			        $param_num_documento = trim($worksheet->getCell('E'. $row)->getValue());
			        $param_fecha_documento = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('F'. $row)->getValue());
		            $param_fecha_documento = $param_fecha_documento ? $param_fecha_documento->format("Y-m-d") : "";
			        $param_fecha_comprobado = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('G'. $row)->getValue());
		            $param_fecha_comprobado = $param_fecha_comprobado ? $param_fecha_comprobado->format("Y-m-d") : "";
			        $param_fecha_vencimiento = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('H'. $row)->getValue());
		            $param_fecha_vencimiento = $param_fecha_vencimiento ? $param_fecha_vencimiento->format("Y-m-d") : "";
			        $param_sd = trim($worksheet->getCell('I'. $row)->getValue());
			        $param_numero = trim($worksheet->getCell('J'. $row)->getValue());
			        $param_glosa = trim($worksheet->getCell('K'. $row)->getValue());
			        $param_mo = trim($worksheet->getCell('L'. $row)->getValue());
			        $param_saldo_dolares_debe = trim($worksheet->getCell('M'. $row)->getValue());

			        if(!empty($param_saldo_dolares_debe))
			        {
			        	$param_saldo_dolares_debe = str_replace(",","",$param_saldo_dolares_debe);
			        	
			        	$campos_cabecera_saldo_dolares_debe = 
						"
							saldo_dolares_debe,
						";

						$campos_valores_saldo_dolares_debe = 
						"
							'".$param_saldo_dolares_debe."',
						";
			        }

			        $param_saldo_dolares_haber = trim($worksheet->getCell('N'. $row)->getValue());

			        if(!empty($param_saldo_dolares_haber))
			        {
			        	$param_saldo_dolares_haber = str_replace(",","",$param_saldo_dolares_haber);
			        	
			        	$campos_cabecera_saldo_dolares_haber = 
						"
							saldo_dolares_haber,
						";

						$campos_valores_saldo_dolares_haber = 
						"
							'".$param_saldo_dolares_haber."',
						";
			        }

			        $param_saldo_moneda_nacional_debe = trim($worksheet->getCell('O'. $row)->getValue());

			        if(!empty($param_saldo_moneda_nacional_debe))
			        {
			        	$param_saldo_moneda_nacional_debe = str_replace(",","",$param_saldo_moneda_nacional_debe);
			        	
			        	$campos_cabecera_saldo_nacional_debe = 
						"
							saldo_moneda_nacional_debe,
						";

						$campos_valores_saldo_nacional_debe = 
						"
							'".$param_saldo_moneda_nacional_debe."',
						";
			        }

			        $param_saldo_moneda_nacional_haber = trim($worksheet->getCell('P'. $row)->getValue());

			        if(!empty($param_saldo_moneda_nacional_haber))
			        {
			        	$param_saldo_moneda_nacional_haber = str_replace(",","",$param_saldo_moneda_nacional_haber);
			        	
			        	$campos_cabecera_saldo_nacional_haber = 
						"
							saldo_moneda_nacional_haber,
						";

						$campos_valores_saldo_nacional_haber = 
						"
							'".$param_saldo_moneda_nacional_haber."',
						";
			        }

			        $param_unique_id = md5(
						$param_cuenta.
		                $param_anexo.
		                $param_nombre.
		                $param_num_documento.
		                $param_fecha_documento.
		                $param_sd.
		                $param_numero.
		                $param_glosa.
		                $param_saldo_moneda_nacional_debe.
		                $param_saldo_moneda_nacional_haber);

					$query_insert = 
					"
						INSERT INTO tbl_repositorio_prestamos_boveda
						(
							unique_id,
							cuenta,
							anexo,
							nombre,
							tipo_doc,
							numero_documento,
							fecha_documento,
							fecha_comprobado,
							fecha_vencimiento,
							sd,
							numero,
							glosa,
							mo,
							".$campos_cabecera_saldo_dolares_debe."
							".$campos_cabecera_saldo_dolares_haber."
							".$campos_cabecera_saldo_nacional_debe."
							".$campos_cabecera_saldo_nacional_haber."
		    				created_at,
							updated_at
						)
						VALUES 
						(
							'".$param_unique_id."',
							'".$param_cuenta."',
							'".$param_anexo."',
							'".$param_nombre."',
							'".$param_tipo_doc."',
							'".$param_num_documento."',
							'".$param_fecha_documento."',
							'".$param_fecha_comprobado."',
							'".$param_fecha_vencimiento."',
							'".$param_sd."',
							'".$param_numero."',
							'".$param_glosa."',
							'".$param_mo."',
							".$campos_valores_saldo_dolares_debe."
							".$campos_valores_saldo_dolares_haber."
							".$campos_valores_saldo_nacional_debe."
							".$campos_valores_saldo_nacional_haber."
							'".date('Y-m-d H:i:s')."',
							'".date('Y-m-d H:i:s')."'
						)
					";

					$mysqli->query($query_insert);

			        if($mysqli->error)
					{
						$error .= $mysqli->error;

						$result["http_code"] = 400;
						$result["status"] = "Ocurrio un error.";
						$result["error"] = $error;

						echo json_encode($result);
						exit();
					}	
	        	}
	        }
	        else
	        {
	        	break;
	        }
	    }

	    if($error == '')
		{
			$result["http_code"] = 200;
			$result["status"] = "Se transcribieron datos correctamente.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
		$result["status"] = "";
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
	
}

?>
