<?php
$web_config_arr = [];
$web_config_arr["is_open"]=false;
$web_config_arr["has_ad"]=false;
$web_config_arr["has_jv"]=false;
$web_config_arr["has_bingo"]=false;
$web_config_arr["has_kasnet"]=false;
$web_config_arr["can_withdraw"]=false;
$web_config_arr["can_deposit"]=false;
$web_config_arr["agente_can_deposit"]=false;
$web_config_arr["zona_covid"]=0;
$web_config_command = "SELECT is_open,has_ad,has_jv,has_bingo,has_kasnet,can_withdraw,can_deposit,agente_can_deposit,zona_covid FROM tbl_locales_web_config WHERE local_id = '{$item_id}'";
$web_config_query = $mysqli->query($web_config_command)->fetch_assoc();
if($web_config_query){ 
	$web_config_arr = array_merge($web_config_arr,$web_config_query); 
}else {
	$web_config_insert_command = "INSERT INTO tbl_locales_web_config (local_id,created_at,updated_at) VALUES ('{$item_id}',NOW(),NOW())";
	$mysqli->query($web_config_insert_command);
}

$query = "SELECT red_id FROM tbl_locales where id = '{$item_id}'";
$fetch_query = $mysqli->query($query)->fetch_assoc(); 
$red = '';
if ($fetch_query) {
	$red = $fetch_query['red_id'];
}

?>
	<div class="tab-pane" id="tab_web">
		<div class="col-xs-8 col-xs-offset-2">
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Configuración web</div>
					<div class="panel-controls">
					</div>
				</div>
				<div class="panel-body">
					<?php
					// print_r($web_config_arr);
					?>
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>Config</th>
								<th>Op</th>
							</tr>
						</thead>
						<tbody>
							<?php
								foreach ($web_config_arr as $k => $v) {
								if(!in_array($k, ["zona_covid"])){
									if ($k == 'agente_can_deposit' && $red != 5) {
										continue;
									}
									if ($k == 'can_deposit' && $red == 5) {
										continue;
									}
							?>
								<tr class="lu_item">
									<td><?php echo $k;?></td>
									<td>
										<?php
										$swich = "";
										if ($k == "is_open") {
											$swich.="<input class='switch switch_is_open'";
										} else {
											$swich.="<input class='switch'";
										}
										$swich.= " id='checkbox_web'";
										$swich.= " type='checkbox'";
										$swich.= " data-table='tbl_locales_web_config'";
										$swich.= " data-id='".$item_id."'";
										$swich.= " data-col='".$k."'";
										$swich.= " data-on-value='1'";
										$swich.= " data-off-value='0'";
										if($v){
											$swich.="checked='checked'";
										}
										$swich.= "data-ignore='true'>";
										echo $swich;
										?>
									</td>
								</tr>
							<?php
									}
								}
							?>
						</tbody>
					</table>
					<!--<select>
						<option>Extremo</option>
					</select>-->
				</div>
			</div>
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title"><i class="icon fa fa-bug"></i>Covid</div>
				</div>
				<div class="panel-body">
					<div class="form-inline">
						<div class="form-group">
							<label for="">Zona Covid</label>
						</div>
						<div class="form-group">
							<?php
							$zonas_covid = [];
								$zonas_covid[0]="Ninguna";
								$zonas_covid[1]="Extremo";
								$zonas_covid[2]="Muy Alto";
								$zonas_covid[3]="Alto";
								$zonas_covid[4]="Moderado";
							?>
							<select class="form-control select2" data-col="zona_covid" name="zona_covid" id="select-zona_covid">
								<?php
								foreach ($zonas_covid as $k => $v) {
									?>
									<option value="<?php echo $k;?>" <?php if($web_config_arr["zona_covid"]==$k){?> selected="selected" <?php } ?>>[<?php echo $k;?>] <?php echo $v;?></option>
									<?php
								}
								?>
							</select>
						</div>
						<button class="btn btn-success local_guardar_zona_covid_btn">Guardar Zona Covid</button>
					</div>
				</div>
			</div>
		</div>
	</div>
