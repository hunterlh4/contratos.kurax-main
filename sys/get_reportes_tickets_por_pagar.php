<?php
include("db_connect.php");
include("sys_login.php");
if(isset($_POST["sec_ticket_por_pagar_reporte"])){
	// print_r($_POST);
	$get_data = $_POST["sec_ticket_por_pagar_reporte"];
	//print_r($get_data); exit();
	//
	$local_id = $get_data["local_id"];
	// $fecha_inicio = $get_data["fecha_inicio"];
	// $fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));

	// $fecha_fin = $get_data["fecha_fin"];
	// $fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));

	$local = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '".$local_id."'")->fetch_assoc();
	if(isset($local['nombre'])){
		$rowlocal = $local['nombre'];
	}else{
		$rowlocal = "";
	}

	// '2017-06-12'
	$date = date ('Y-m-d');
	$newdate = strtotime ('-30 day' , strtotime($date));
	$newdate = date ('Y-m-d', $newdate);
	//echo $newdate;
	$caja_arr = [];
	$where_local_id = '';
	if($local_id !="_all_"){
		$where_local_id = " AND l.id = '".$local_id."'";
	}	
	$locales_select = implode(",", $login["usuario_locales"]);
	if ($locales_select!='') {
		$where_local_id = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	}

		
	
	$caja_command = "
	SELECT 
       l.nombre                          AS local_name,
       count(l.id) 						 AS cantidad,
       sum(D.col_WinningAmount)                 AS ganado
	FROM   bc_apuestatotal.at_BetPendingPay  AS D
		LEFT JOIN bc_apuestatotal.tbl_CashDesk AS CD
				ON  D.col_CashDeskId = CD.col_id
		LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp
				ON  lp.proveedor_id = CD.col_id
		LEFT JOIN wwwapuestatotal_gestion.tbl_locales l
				ON  l.id = lp.local_id
	WHERE  D.col_Created >= '{$newdate}'
		AND D.col_CashDeskId IS NOT          NULL
		{$where_local_id}
		GROUP BY
       l.id
	";
	//echo $caja_command; exit();
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$caja_query = $mysqli->query($caja_command);
	if($mysqli->error){
		//print_r($caja_command);
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	$table=[];
	$table["tbody"]=[];
	$caja_data = [];
	while($c=$caja_query->fetch_assoc()){
		$caja_data[]=$c;
	}
	$total=[];
	//$total["apostado"]=0;
	$total["ganado"]=0;

	foreach ($caja_data as $key => $tr) {
		// print_r($value);
		// $tr=array();
		// 	$tr["local_id"]=$value["local_id"];
		// 	$tr["fecha_operacion"]=$value["fecha_operacion"];
		//$total["apostado"]+=$tr["apostado"];
		$total["ganado"]+=$tr["ganado"];
		$table["tbody"][]=$tr;
	}
	// print_r($table);
	if(count($table["tbody"])>0){;
		?>

		<?php if(array_key_exists(85,$usuario_permisos) && in_array("export", $usuario_permisos[85])){
				?>
				<a
					href="export.php?export=tbl_tickets_por_pagar&amp;type=lista&amp;ini=<?php echo $newdate;?>&amp;local=<?php echo $local_id;?>"
					class="btn btn-success btn-sm export_list_btn pull-right"
					style="margin-bottom: 10px"
					download="tickets_por_pagar_<?php echo $rowlocal; ?>_del_<?php echo $newdate;?>_hasta_<?php echo $date; ?>.xls"><span class="glyphicon glyphicon-export"></span> Exportar Lista</a>
				<?php
		}?>

		<h4>Del <b><?php echo $newdate; ?></b> al <b><?php echo $date; ?></b> (Ultimos 30 dias) </h4>
		<table class="table table-condensed table-bordered table-striped">
			<tr>
				<!-- <th>Fecha</th> -->
				<th>Local</th>
				<th>Cantidad</th>
				<th>Ganado</th>
			</tr>
			<?php
			foreach ($table["tbody"] as $k => $tr) {
				?>
				<tr>
					<!-- <td><?php //echo $tr["created"]; ?></td> -->
					<td><?php echo $tr["local_name"]; ?></td>
					<td><?php echo $tr["cantidad"]; ?></td>
					<td style="text-align: right;"><?php echo $tr["ganado"]; ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="2" class="text-right">Total:</td>
				<td style="text-align: right;"><?php echo number_format($total["ganado"],2); ?></td>
			</tr>
		</table>
		<?php
	}else{
		?>
			<div class="alert alert-danger alert-dismissible fade in" role="alert">
				<strong>No hay información para esta busqueda.</strong>
			</div>
		<?php
	}
		?>
	<?php
};
?>
