
<?php
if($sub_sec_id=="transacciones_bancarias"){
	$bancos = array();
	$bancos_command = "SELECT b.id, b.nombre, b.color_hex, b.descripcion FROM tbl_bancos b WHERE estado = '1'";
	$bancos_query = $mysqli->query($bancos_command);
	while($banco = $bancos_query->fetch_assoc()){
		$bancos[$banco["id"]]=$banco;
	}
	$monedas = array();
	$monedas_command = "SELECT m.id, m.sigla, m.simbolo, m.descripcion FROM tbl_moneda m WHERE estado = '1'";
	$monedas_query = $mysqli->query($monedas_command);
	while($moneda = $monedas_query->fetch_assoc()){
		$monedas[$moneda["id"]]=$moneda;
	}
	?>
	<div class="modal transaccion_bancaria_modal" id="transaccion_bancaria_modal" >
		<div class="modal-dialog modal-lg modal-fs">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close close_btn"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Asignar Transaccion Bancaria a Liquidacion</h4>
				</div>
				<div class="modal-body" id="trans_holder">
				</div>
				<div class="modal-footer">
					<button class="btn btn-success save_btn" >Guardar</button>
					<button class="btn btn-default close_btn">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal transaccion_bancaria_modal" id="transaccion_bancaria_import_modal" data-backdrop="static">
		<div class="modal-dialog">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close close_btn hide_while_uploading"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Importar Transacciones Bancarias</h4>
				</div>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="form-group">
							<label class=" control-label col-xs-3">Banco:</label>
							<div class="btn-group col-xs-8 btn_group_banco_id" data-toggle="buttons">
								<?php
								foreach ($bancos as $banco) {
									?>
									<label class="btn btn-default <?php if($banco["id"]==12){ ?>active<?php } ?>">
										<div class="line" style="background-color: #<?php echo $banco["color_hex"];?>"></div>
										<input type="radio" class="import_input radio_banco_id hidden" id="radio_banco_id_<?php echo $banco["id"];?>" name="banco_id" value="<?php echo $banco["id"];?>" <?php if($banco["id"]==12){ ?> checked="checked"<?php } ?>>
										<?php echo $banco["id"];?> - <?php echo $banco["nombre"];?>
									</label>
									<?php
								}
								?>
							</div>
						</div>
						<div class="form-group">
							<label class=" control-label col-xs-3">Moneda:</label>
							<div class="btn-group col-xs-8 btn_group_moneda_id" data-toggle="buttons">
								<?php
								foreach ($monedas as $moneda){
									?>
									<label class="btn btn-default <?php if($moneda["id"]==1){ ?>active<?php } ?>" title="<?php echo $moneda["descripcion"];?>">
										<input type="radio" class="import_input radio_moneda_id hidden" autocomplete="off" id="radio_moneda_id_<?php echo $moneda["id"];?>" name="moneda_id" value="<?php echo $moneda["id"];?>" <?php if($moneda["id"]==1){ ?> checked="checked"<?php } ?>><?php echo $moneda["simbolo"];?><?php echo $moneda["id"];?> - <?php echo $moneda["sigla"];?>
									</label>
									<?php
								}
								?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3 control-label" for="import_file">Archivo:</label>
							<div class="col-xs-9">
								<input class="hidden" type="file" name="uploadfile" id="import_file" accept=".csv">									
								<label class="uploader_file_name" for="import_file">
									<div class="btn btn-warning">Seleccione</div>
								</label>
								<div class="btn btn-default filename"><span>nombre el archivo</span> <button class="btn btn-default btn-xs change_file_btn hide_while_uploading">&times;</button></div>
								
							</div>
						</div>
						<!-- <div class="form-group">
							<label for="input_referencia" class="col-xs-3 control-label">Referencia:</label>
							<div class="col-xs-9">
								<input type="text" name="referencia" class="form-control save_item" id="input_referencia" placeholder="Ej: INGRESO EN EFECTIVO O/P">
							</div>
						</div> -->
					</div>
					<div class="form-horizontal progress_holder">
						<div class="form-group">
							<label class="col-xs-3 control-label">Importando:</label>
							<div class="col-xs-9">
								<div class="progress progress-lg progress_import">
									<div class="progress-bar progress-bar-striped active progress-bar-success" style="width: 0%;">
										0%
									</div>
								</div>
								
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3 control-label">Procesando:</label>
							<div class="col-xs-9">
								<div class="progress progress-lg progress_process">
									<div class="progress-bar progress-bar-striped active"  style="width: 0%;">
										0%
									</div>
								</div>
								
							</div>
						</div>
						
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success import_btn hide_while_uploading" >Importar</button>
					<button class="btn btn-default close_btn hide_while_uploading">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal transaccion_bancaria_modal" id="sec_rtb_assig_modal" >
		<div class="modal-dialog modal-lg modal-fs">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close close_btn"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Asignar Transaccion Bancaria a Local</h4>
				</div>
				<div class="modal-body" id="un_used_local_list_holder">

				</div>
				<div class="modal-footer">
					<button class="btn btn-success assig_save_btn" >Guardar</button>
					<button class="btn btn-default close_btn">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	<?php

	$trans = array();
		$trans_command = "
			SELECT 
				t.at_unique_id,
				t.id,
				t.fecha_operacion,
				-- t.fecha_valor,
				IF(b.id = 12,t.referencia,CONCAT(t.movimiento,': ',t.referencia,'')) AS referencia,
				IF(b.id = 12, t.importe,t.abono) AS importe,
				t.usado,
				t.restante,
				-- t.cargo,
				-- t.abono,
				-- t.itf,
				t.numero_movimiento,
				t.local_id,
				t.comentario,
				t.usuario_id,
				t.fecha_ingreso,
				t.estado,
				b.nombre AS banco,
				b.color_hex AS banco_color_hex,
				-- u.nombre AS usuario_nombre,
				IF(
					u.personal_id,
					(SELECT 
						CONCAT(IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,'')) 
					FROM tbl_personal_apt p 
					WHERE p.id = u.personal_id),
					u.usuario
				) AS usuario_nombre,
				-- , IF(u.personal_id,p.nombre,u.usuario) AS nombre
				(
					SELECT 
						-- GROUP_CONCAT(DISTINCT(p.local_id)) 
						GROUP_CONCAT(DISTINCT(CONCAT('[',l.id,']',' ',l.nombre))) 
					FROM tbl_transaccion_bancaria_local tbl 
					LEFT JOIN tbl_locales l ON (l.id = tbl.local_id)
					WHERE tbl.trans_unique_id = t.at_unique_id
					AND tbl.estado = '1'
					-- GROUP BY p.local_id

					-- LIMIT 1
				) AS locales
				-- (
				-- 	SELECT 
				-- 		-- GROUP_CONCAT(DISTINCT(p.local_id)) 
				-- 		GROUP_CONCAT(DISTINCT(l.nombre)) 
				-- 	FROM tbl_pagos p 
				-- 	LEFT JOIN tbl_locales l ON (l.id = p.local_id)
				-- 	WHERE p.trans_unique_id = t.at_unique_id
				-- 	-- GROUP BY p.local_id

				-- 	-- LIMIT 1
				-- ) AS locales
			FROM tbl_repositorio_transacciones_bancarias t
			LEFT JOIN tbl_bancos b ON (b.id = t.banco_id)
			LEFT JOIN tbl_usuarios u ON (u.id = t.usuario_id)
			WHERE t.estado = '$view_estado'
			AND (
					t.importe >= 0
					OR
					t.cargo >= 0
					OR
					t.abono >= 0
				)
			ORDER BY t.fecha_operacion DESC
			";
		$trans_query = $mysqli->query($trans_command);
		print_r($mysqli->error);
		// exit();
		while($tra=$trans_query->fetch_assoc()){
			$trans[]=$tra;
		}
	?>
	<div class="col-xs-12">
		<div class="table-responsive">
			<table class="table table-bordered table-condensed table_transacciones_bancarias" id="table_transacciones_bancarias">
				<thead>
					<tr>
						<th></th>
						<th>Banco</th>
						<th class="w-100">Fecha Operacion</th>
						<th>Referencia</th>
						<th>Importe</th>
						<th>Usado</th>
						<th>Restante</th>
						<th>					
							<form class="form-inline">
								<div class="form-group">
									<label for="exampleInputAmount">Numero de Movimiento</label>
									<div class="input-group">
										<input class="pull-right form-control" id="recaudacion_transacciones_bancarias_locales_list_table_search" data-holder="table_transacciones_bancarias_tbody" placeholder="Ej: 0666" type="text" autofocus="autofocus">
										<div class="input-group-addon cursor-pointer search_clear_btn">X</div>
									</div>
								</div>
							</form>
						</th>
						<th>Locales</th>
						<th>Comentario</th>
						<th>Usuario</th>
						<th>Opciones</th>
					</tr>
				</thead>
				<tbody id="table_transacciones_bancarias_tbody">
					<?php
					foreach ($trans as $t_key => $t_val) {
						?>
						<tr class="trans_item" id="trans_<?php echo $t_val["at_unique_id"];?>">
							<td class="checkbox_me">
								<input type="checkbox" name="" class="hidden" data-id="<?php echo $t_val["at_unique_id"];?>">
								<span class="glyphicon glyphicon-unchecked checkbox_icon"></span>
							</td>
							<td class="td_banco"><div class="line" style="background-color: #<?php echo $t_val["banco_color_hex"];?>"></div><span><?php echo $t_val["banco"];?></span></td>
							<td><?php echo $t_val["fecha_operacion"];?></td>
							<td><?php echo $t_val["referencia"];?></td>
							<td><?php echo $t_val["importe"];?></td>
							<td class="<?php echo ($t_val["usado"] > 0 ? "text-warning" : "text-success") ?>"><?php echo $t_val["usado"];?></td>
							<td class="<?php echo ($t_val["restante"] > 0 ? "text-success" : "text-danger") ?>"><?php echo $t_val["restante"];?></td>
							<td><?php echo $t_val["numero_movimiento"];?></td>
							<td><?php 
								foreach (explode(",", $t_val["locales"]) as $key => $value) {
									if($value){
										?><span class="label"><?php echo $value;?></span><?php
									}
								}
							?></td>
							<td><?php echo $t_val["comentario"];?></td>
							<td><?php echo $t_val["usuario_nombre"];?></td>
							<td>
								<button class="btn btn-xs btn-success trans_ass_btn" data-id='<?php echo $t_val["at_unique_id"];?>' title="Asignar"><span class="glyphicon glyphicon-random"></span></button>
								<!-- <button class="btn btn-xs btn-success trans_view_btn" data-id='<?php echo $t_val["at_unique_id"];?>' title="Asignar"><span class="glyphicon glyphicon-random"></span></button> -->

								<?php
								if($t_val["estado"]==1){
									?><button class="btn btn-xs btn-warning trans_hide_btn" data-estado="0" data-id='<?php echo $t_val["at_unique_id"];?>'>Ocultar</button><?php
								}else{
									?><button class="btn btn-xs btn-success trans_show_btn" data-estado="1" data-id='<?php echo $t_val["at_unique_id"];?>'>Mostrar</button><?php
								}
								?>
								
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
<?php
}
?>