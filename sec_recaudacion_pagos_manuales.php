<?php
if($sub_sec_id=="pagos_manuales"){
	$locales_arr = [];
		$locales_command = "SELECT id, nombre FROM tbl_locales ORDER BY nombre ASC";
		$locales_query = $mysqli->query($locales_command);
		while($l=$locales_query->fetch_assoc()){
			// if(in_array($l["id"],array(81,1))){
				$locales_arr[$l["id"]]=$l["nombre"];
			// }
		}
	?>
	<style>
		.bg-pg-error{
			background-color: #fda8a4;
		}
		.bg-pg-success{
			background-color: #add096;
		}
	</style>
	<div class="modal" id="recaudacion_add_pago_manual_modal" data-backdrop="static">
		<div class="modal-dialog  modal-lg">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close cerrar_btn"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Agregar Pago Manual</h4>
				</div>
				<div class="modal-body">
					<div class="form row">
						<input type="hidden" name="at_unique_id" value="new" class="set_data">
						<div class="form-group">
							<label class="control-label col-sm-4">Tipo:</label>
							<div class=" col-sm-8">
								<select class="form-control select2 set_data" name="tipo_id" style="width: 100%">

									<?php
									$pm_tipos_command = "SELECT id, nombre FROM tbl_pago_manual_tipos";
									$pm_tipos_query = $mysqli->query($pm_tipos_command);
									while($pm_tipo=$pm_tipos_query->fetch_assoc()){
										?><option value="<?php echo $pm_tipo["id"];?>">[<?php echo $pm_tipo["id"];?>] <?php echo $pm_tipo["nombre"];?></option><?php
									}
									?>
									<option value="0">Sin Tipo</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">Canal de Venta:</label>
							<div class="col-sm-8">
								<?php
								$cdv_command = "SELECT cv.id, cv.codigo, s.nombre FROM tbl_canales_venta cv LEFT JOIN tbl_servicios s ON (s.id = cv.servicio_id) WHERE s.id NOT IN (2,4) AND cv.estado = 1 AND cv.pago_manual = 1 ORDER BY s.nombre ASC, cv.codigo ASC ";
								$cdv_query = $mysqli->query($cdv_command);
								?>
								<select class="form-control select2 set_data" name="cdv_id" style="width: 100%">
									<?php
									while ($cv=$cdv_query->fetch_assoc()) {
										?><option value="<?php echo $cv["id"];?>" <?php if($cv["id"]==16){ ?> selected="selected"<?php } ?>>[<?php echo $cv["id"];?>] <?php echo $cv["nombre"];?> - <?php echo $cv["codigo"];?></option><?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">Monto:</label>
							<div class=" col-sm-8">
								<input type="number" class="form-control set_data" name="monto" value="">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-label">Fecha:</label>
							<div class="col-sm-8">
								<input
									type="hidden"
									id="add_pm_created"
									class=" add_pm_created set_data"
									name="fecha"
									value="<?php echo date("Y-m-d",strtotime("-1 day"));?>"
									data-real-date="input_text-add_pm_created">
								<input
									type="text"
									class="form-control pm_datepicker"
									id="input_text-add_pm_created"
									value="<?php echo date("d-m-Y",strtotime("-1 day"));?>"
									readonly="readonly"
									>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">Local:</label>
							<div class=" col-sm-8">
								<select class="form-control select2 set_data" name="local_id" style="width: 100%">
									<?php
									foreach ($locales_arr as $local_id => $local_nombre) {
										?><option value="<?php echo $local_id;?>">[<?php echo $local_id;?>] - <?php echo $local_nombre;?></option><?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">Motivo:</label>
							<div class=" col-sm-8">
								<select class="form-control select2 set_data" name="motivo_id" style="width: 100%">
									<?php
									$pm_motivo_command = "SELECT id, nombre FROM tbl_pago_manual_motivos";
									$pm_motivo_query = $mysqli->query($pm_motivo_command);
									while($pm_motivo=$pm_motivo_query->fetch_assoc()){
										?><option value="<?php echo $pm_motivo["id"];?>">[<?php echo $pm_motivo["id"];?>] <?php echo $pm_motivo["nombre"];?></option><?php
									}
									?>
									<option value="0">Sin Motivo</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">Autoriza:</label>
							<div class=" col-sm-8">
								<?php
                                // p.id IN (9,16) : Tania Carpio y Antonio Salord
                                // p.area_id = 21 AND p.cargo_id = 16 : Area "Operaciones", Cargo "Jefe"
                                // u.id = 23 : Gonzalo Pérez
                                // u.id = 516: Maurice Rafael
								$per_command = "SELECT p.id, p.nombre, p.apellido_paterno 
												FROM tbl_personal_apt p 
												LEFT JOIN tbl_usuarios u ON (u.personal_id = p.id) 
												WHERE u.estado = '1' 
												AND (p.id IN (9,16) OR (p.area_id = 21 AND p.cargo_id = 16) OR u.id = 516 OR u.id = 23)
												ORDER BY p.area_id ASC";
								$per_query = $mysqli->query($per_command);
								?>
								<select class="form-control select2 set_data" name="autorizacion_id" style="width: 100%">
									<?php
									while($p=$per_query->fetch_assoc()){
										?><option value="<?php echo $p["id"];?>"><?php echo $p["nombre"];?> <?php echo $p["apellido_paterno"];?></option><?php
									}
									?>
									<option value="0">Sin Autorizacion</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">Referencia:</label>
							<div class=" col-sm-8">
								<input type="text" class="form-control set_data" name="referencia" value="" placeholder="Bet Id, Número de Transacción, etc...">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-label">Observaciones:</label>
							<div class="col-sm-8">
								<textarea class="form-control set_data" rows="3" name="descripcion" placeholder="Ingrese observaciones"></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success add_btn" >Guardar</button>
					<button class="btn btn-default cerrar_btn">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="recaudacion_importar_modal" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close cerrar_btn" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Importar Pagos Manuales</h4>
				</div>
				<div class="modal-body">
					<form id="form_import" enctype="multipart/form-data" method="POST">
						<input type="hidden" name="at_unique_id" value="new" class="set_data">
						<input id="archivo-pago-manuales" name="archivo" type="file" style="display:none;">
						<div class="row">
							<div class="col-md-12">
								<button type="button" class="btn btn-min-width btn-block btn-secondary">
									<label for="file-input-pago-manuales" style="width:100%;" class="btn">
										<i class="fa fa-file"> </i> <span>Cargar Archivo</span>
									</label>
								</button>
								<input id="file-input-pago-manuales" onchange="fncGetFileSecPagosManuales(this)" type="file" style="display:none;">
							</div>
						</div>
					</form>
					
					<hr>
					<div class="row">
						<div class="table-responsive">
							<table id="table_import_pagos_manuales" class="table table-bordered" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th class="text-center">#</th>
										<th class="text-center">Tipo</th>
										<th class="text-center">Motivo</th>
										<th class="text-center">Referencia</th>
										<th class="text-center">Descripción</th>
										<th class="text-center">Fecha Pago</th>
										<th class="text-center">Monto</th>
										<th class="text-center">Canal de Venta</th>
										<th class="text-center">Local</th>
										<th class="text-center">Autoriza</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button class="btn btn-success" style="display: none;" type="button" onclick="fnc_pago_manual_importar_archivo()" id="btn_cargar_plantilla"><span class="glyphicon glyphicon-upload"></span> &nbsp; Cargar Plantilla</button>
                    <button class="btn btn-danger" type="button" id="btn_descargar_plantilla"><span class="glyphicon glyphicon-download"></span> &nbsp; Descargar Plantilla</button>
					<button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	<?php
	$pagos_manuales = [];
	$pm_command = "SELECT
					pm.id,
					-- DATE_FORMAT(pm.fecha_proceso,'%Y-%m-%d') AS dia_proceso,
					fecha_proceso,
					pm.tipo_id,
					(SELECT t.nombre FROM tbl_pago_manual_tipos t WHERE t.id = pm.tipo_id) AS tipo,
					pm.motivo_id,
					(SELECT m.nombre FROM tbl_pago_manual_motivos m WHERE m.id = pm.motivo_id) AS motivo,
					SUBSTRING(pm.referencia,1,10) AS referencia,
					pm.descripcion,
					pm.estado,
					DATE_FORMAT(pm.fecha_pago,'%Y-%m-%d')  AS fecha_pago,
					pm.monto,
					-- (IFNULL(d.apostado,0) + IFNULL(d.ganado,0) + IFNULL(d.income,0) + IFNULL(d.withdraw,0) + IFNULL(d.terminal_income,0) + IFNULL(d.terminal_withdraw,0) + IFNULL(d.deposit,0)) AS monto,
					-- (IFNULL(d.apostado,IFNULL(d.ganado,IFNULL(d.income,IFNULL(d.withdraw,IFNULL(d.terminal_income,IFNULL(d.terminal_withdraw,IFNULL(d.deposit,0)))))))) AS monto,
					(SELECT cdv.codigo FROM tbl_canales_venta cdv WHERE cdv.id = pm.canal_de_venta_id) AS cdv,
					(SELECT l.nombre FROM tbl_locales l WHERE l.id = pm.local_id) AS local,
					(SELECT u.usuario FROM tbl_usuarios u WHERE u.id = pm.usuario_id) AS usuario,
					(SELECT a.nombre FROM tbl_personal_apt a WHERE a.id = pm.autorizacion_id) AS autoriza
					FROM tbl_pago_manual pm
					-- LEFT JOIN tbl_transacciones_detalle d ON (d.at_unique_id = pm.at_unique_id)
					ORDER BY pm.fecha_proceso DESC";
	$pm_query = $mysqli->query($pm_command);
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	while($pm=$pm_query->fetch_assoc()){
		$pagos_manuales[$pm["id"]]=$pm;
	}
	?>

 <link rel="stylesheet" href="css/simplePagination.css">
<div class="content container-fluid content_pagos_manuales">

 <div class="row">
	 <div class="col-lg-3 col-xs-12">
	 	<div class="form-group form-inline">
	 		Mostrar
	 		<select id="cbPagosLimit" name="cbPagosLimit" class="form-control">
	 			<option value="10">10</option>
	 			<option value="25">25</option>
	 			<option value="50">50</option>
	 			<option value="100">100</option>
	 		</select>
	 		Registros
	 	</div>
	 </div>
	 <div class="col-lg-6 col-xs-12">
	 	<div class="form-group form-inline pull-right">
	 		<label for="txtPagosStartDate">Fecha Inicio: </label>
 			<input type="text" id="txtPagosStartDate" class="form-control" readonly>
	 		<label for="txtPagosEndDate" class="ml-5">Fecha Fin: </label>
 			<input type="text" id="txtPagosEndDate" class="form-control" readonly>
 			<button id="btnClearPagosDate" class="btn btn-secondary btn-sm"><i class="fa fa-trash"></i></button>
	 	</div>
	 </div>
	 <div class="col-lg-3 col-xs-12">
	 	<div class="form-group form-inline pull-right">
	 		Buscar:
	 		<div class="form-group has-feedback has-search">
	 			<input type="text" id="txtPagosFilter" class="form-control" placeholder="" width="100%">
	 		</div>
	 	</div>
	 </div>
 </div>


	<div class="col-xs-12 no-pad">
		<div class="nose">
		<table class="table-responsive table table-bordered table-condensed" style="table-layout: fixed; width: 1598px;" id="table-pagos_manuales">
			<thead>
				<tr>
					<th style="width: 115px">Fecha Proceso</th>
					<th style="width: 95px">Tipo</th>
					<th style="width: 91px">Canal de Venta</th>
					<th style="width: 132px">Local</th>
					<th style="width: 80px">Ref. Empresa</th>
					<th style="width: 91px">Motivo</th>
					<th style="width: 83px">Autorizado</th>
					<th style="width: 86px">Referencia</th>
					<th class="descrip" style="">Descripción</th>
					<th style="width: 100px; text-align: center;">Monto</th>
					<th style="width: 100px; text-align: center;">Fecha Pago</th>
					<th style="width: 70px">Opciones</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
		<div class="pull-right">
			<div id="paginationPagosMensuales"></div>
		</div>
	</div>
	</div>
	<?php
}
?>
