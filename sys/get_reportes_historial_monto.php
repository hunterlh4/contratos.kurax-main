<?php
include("db_connect.php");
include("sys_login.php");


if(isset($_POST["sec_caja_get_reporte"])){
	$get_data = $_POST["sec_caja_get_reporte"];
	// print_r($get_data);
	// exit();
	$local_id = $get_data["local_id"];

	$fecha_inicio = $get_data["fecha_apertura"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_apertura"]));
	$fecha_fin = $get_data["fecha_aperturafin"];
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_aperturafin"]));
	// $fecha_fin = $get_data["fecha_fin"];
	$rowlocal="";

	if($local_id=="_all_"){
		$rowlocal="Todos";
	}else{
		$local = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '".$local_id."'")->fetch_assoc();
		$rowlocal = $local['nombre'];
	}


	//echo $local_id;exit;
	$cajas_sql_command = "select id, fecha_registro,data,login from tbl_auditoria
	where proceso='locales_guardar_monto_inicial'
	and date(fecha_registro) >='".date("Y-m-d",strtotime($get_data["fecha_apertura"]))."'
	and date(fecha_registro) <='".date("Y-m-d",strtotime($get_data["fecha_aperturafin"]))."' ";
	//$cajas_sql_command.= " and lc.local_id = '".$local_id."'";
	//echo $cajas_sql_command;
	$cajas_sql_command.=" order by fecha_registro desc";
	$cajas_sql_query = $mysqli->query($cajas_sql_command);
	if($mysqli->error){
		echo "ERROR: ";
		print_r($mysqli->error);
		exit();
	}

	$cdv = array();
	$i=0;

	if($local_id=="_all_"){
		while($row_selected = $cajas_sql_query->fetch_assoc()) {

			$data2 = json_decode($row_selected['data'],true);
			$caja = json_decode($data2['item_id'],true);
			$localID = $caja;
			$monto = str_replace(",", "", $data2["config"]["monto_inicial"]);
			$monto_anterior=0;
			if(isset($data2["config"]["monto_anterior"])){
				$monto_anterior = str_replace(",", "", $data2["config"]["monto_anterior"]);
			}

			$usuarioElimina = json_decode($row_selected['login'],true);
			$local_ = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '".$localID."'")->fetch_assoc();
			$rowlocalnombre = $local_['nombre'];

			$cdv[$i]['id'] = $localID;
			$cdv[$i]['fecha_registro'] = $row_selected["fecha_registro"];
			$cdv[$i]['valla_deposito'] = (isset($data2["config"]["valla_deposito"]) ? str_replace(",", "", $data2["config"]["valla_deposito"]) : 0);
			$cdv[$i]['saldo_kasnet'] = (isset($data2["config"]["saldo_kasnet"]) ? str_replace(",", "", $data2["config"]["saldo_kasnet"]) : 0);
			$cdv[$i]['monto_inicial'] = $monto;
			$cdv[$i]['monto_anterior'] = $monto_anterior;
			$cdv[$i]['usuario_elimina'] = $usuarioElimina['nombre']." ".$usuarioElimina['apellido_paterno'];
			$cdv[$i]['local_nombre'] = $rowlocalnombre;
			$i++;
		}
	}else{
		while($row_selected = $cajas_sql_query->fetch_assoc()) {

			$data2 = json_decode($row_selected['data'],true);
			$caja = json_decode($data2['item_id'],true);
			$localID = $caja;
			$monto = $data2["config"]["monto_inicial"];
			$monto_anterior=0;
			if(isset($data2["config"]["monto_anterior"])){
				$monto_anterior = $data2["config"]["monto_anterior"];
			}
			$usuarioElimina = json_decode($row_selected['login'],true);

			$local_ = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '".$localID."'")->fetch_assoc();
			$rowlocalnombre = $local_['nombre'];

			if($localID== $local_id ){
				$cdv[$i]['id'] = $localID;
				$cdv[$i]['fecha_registro'] = $row_selected["fecha_registro"];
				$cdv[$i]['valla_deposito'] = (isset($data2["config"]["valla_deposito"]) ? str_replace(",", "", $data2["config"]["valla_deposito"]) : 0);
				$cdv[$i]['saldo_kasnet'] = (isset($data2["config"]["saldo_kasnet"]) ? str_replace(",", "", $data2["config"]["saldo_kasnet"]) : 0);
				$cdv[$i]['monto_inicial'] = $monto;
				$cdv[$i]['monto_anterior'] = $monto_anterior;
				$cdv[$i]['usuario_elimina'] = $usuarioElimina['nombre']." ".$usuarioElimina['apellido_paterno'];
				$cdv[$i]['local_nombre'] = $rowlocalnombre;
				$i++;
			}
		}
	};

	if(count($cdv)){

		// print_r($table_total);

			?>
		<?php if(array_key_exists(86,$usuario_permisos) && in_array("export", $usuario_permisos[86])){
								?>
									<a
										href="export.php?export=tbl_historial_monto&amp;type=lista&amp;ini=<?php echo date("Y-m-d",strtotime($get_data["fecha_apertura"]));?>&amp;fin=<?php echo date("Y-m-d",strtotime($get_data["fecha_aperturafin"]));?>&amp;local=<?php echo $local_id;?>"
										class="btn btn-success btn-sm export_list_btn pull-right"
										style="margin-bottom: 10px"
										download="historial_monto_<?php echo $rowlocal; ?>_<?php echo date("d-m-Y",strtotime($get_data["fecha_apertura"]));?>_al_<?php echo date("d-m-Y",strtotime($get_data["fecha_aperturafin"]));?>.xls"
										><span class="glyphicon glyphicon-export"></span> Exportar Lista</a>
								<?php
							}?>
		<table class="tbl_apertura_caja table table-bordered table-condensed table-striped" id="tbl_apertura_caja">
			<thead>
				<tr>
					<th>
						<div class="form-group">
							<div class="control-label">FECHA REGISTRO</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">USUARIO EDICIÓN</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">LOCAL</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">MONTO INICIAL</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">VALLA DEPOSITO</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">SALDO KASNET</div>
						</div>
					</th>

				</tr>
			</thead>
			<tbody>
				<?php

					foreach ($cdv as $key => $value) {

						?>
						<tr class="tr_turno">
							<td><?php echo $value['fecha_registro']; ?></td>
							<td><?php echo Utf8_ansi($value['usuario_elimina']); ?></td>
							<td><?php echo '['.$value['id'].'] '.$value['local_nombre']; ?></td>
							<?php /*
							<td style="text-align: right;"><?php
								if($value['monto_anterior']){
									echo number_format($value['monto_anterior'],2,'.',false);
								}
								else{
									echo number_format(0,2,'.',false);
								}
							 ?></td>
							*/ ?>
							<td style="text-align: right;"><?php
								if($value['monto_inicial']){
									echo number_format(str_replace(",", "", $value['monto_inicial']),2,'.',false);
								}
								else{
									echo number_format(0,2,'.',false);
								}
							 ?></td>
							 <td style="text-align: right;"><?php echo number_format($value['valla_deposito'],2,".", ","); ?></td>
							 <td style="text-align: right;"><?php echo number_format($value['saldo_kasnet'],2,".", ","); ?></td>
						</tr>
						<?php
					}
				?>
			</tbody>
			<tfoot>
			</tfoot>
		</table>

		<?php
	}else{
			?>
			<div class="alert alert-danger alert-dismissible fade in" role="alert">
				<strong>No hay información para esta busqueda.</strong>
			</div>

			<?php
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

?>
