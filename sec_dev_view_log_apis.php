<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'dev' AND sub_sec_id = 'view_log_apis' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

$area_id = $login ? $login['area_id'] : 0;

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id]) || $area_id <> 6) {
	echo "No tienes permisos para acceder a este recurso";
	die;
}

?>

<div class="sec_dev_view_log_apis">

	<input id="g_fecha_actual" type="hidden" value="<?php echo date('Y-m-d'); ?>">
	<input id="g_fecha_hace_15_dias" type="hidden" value="<?php echo date('Y-m-d', strtotime(date('Y-m-d') . '- 30 days')); ?>">

	<input id="g_fecha_actual_log" type="hidden" value="<?php echo date('Y-m-d H:i:s'); ?>">
	<input id="g_fecha_hace_15_dias_log" type="hidden" value="<?php echo date('Y-m-d H:i:s', strtotime(date('Y-m-d') . '- 30 days')); ?>">

	<input id="g_login_cod_cargo" type="hidden" value="<?php echo $login['cargo_id']; ?>">
	<input id="g_login_cod" type="hidden" value="<?php echo $login['id']; ?>">

	<div id="loader_"></div>



	<div class="panel" style="border-color: transparent;">

		

		<div class="panel-heading" style="border-color: #01579b;background: #fff;">
			<div class="panel-title" style="color: #000;text-align: center;font-size: 22px;">Log Apis
			</div>

			<div class="form-inline p-2" id="SecDevView_div_btn_return" style="display:none">
				<a class="SecDevView_btn_return btn btn-rounded btn-default" href="./?sec_id=dev&sub_sec_id=view_log_apis">
					<i class="glyphicon glyphicon-arrow-left"></i>
					Regresar
				</a>
			</div>
		</div>

		

		<div class="panel-body">

			<div class="row wide" id="SecDevView_filtros_x_fec">
				<div class="col-md-2 col-sm-4">
					<div class="form-group">
						<label for="SecDevView_fecha_inicio">Fecha Inicio</label>
						<input id="SecDevView_fecha_inicio" type="text" class="form-control" style="border-radius: 5px;border: 1px solid #aaa;">
					</div>
				</div>

				<div class="col-md-2 col-sm-4">
					<div class="form-group">
						<label for="SecDevView_fecha_fin">Fecha Fin</label>
						<input id="SecDevView_fecha_fin" type="text" class="form-control" style="border-radius: 5px;border: 1px solid #aaa;">
					</div>
				</div>

				<div class="col-md-2 col-sm-6">
					<div class="form-group">
						<button class="btn btn-primary" id="SecDevView_btn_buscar_list" type="button" style="margin-top: 15px;" >
							<span class="glyphicon glyphicon-search"></span>
							Buscar
						</button>
					</div>
				</div>

			</div>

			<div class="row wide" id="SecDevView_filtros_x_dia" style="display:none">
				<div class="col-md-2 col-sm-4">
					<div class="form-group">
						<label for="SecDevView_fecha_inicio_log">Fecha Inicio</label>
						<input id="SecDevView_fecha_inicio_log" type="text" class="form-control" style="border-radius: 5px;border: 1px solid #aaa;">
					</div>
				</div>

				<div class="col-md-2 col-sm-4">
					<div class="form-group">
						<label for="SecDevView_fecha_fin_log">Fecha Fin</label>
						<input id="SecDevView_fecha_fin_log" type="text" class="form-control" style="border-radius: 5px;border: 1px solid #aaa;">
					</div>
				</div>

				<div class="col-md-2 col-sm-6">
					<div class="form-group">
						<label for="SecDevView_proveedor_log">Proveedor</label>
						<div class="" style="margin-bottom: 10px;" id="SecDevView_proveedor_log_div">
							<input type="text" class="form-control" id="SecDevView_proveedor_log" placeholder="Nombre del Proveedor" autocomplete="off"
							style="border-radius: 5px;border: 1px solid #aaa; color: black; height:auto; cursor: text;">
							<ul id="SecDevView_lista_proveedor_log"></ul>
						</div>
					</div>
				</div>

				<div class="col-md-2 col-sm-4">
					<div class="form-group">
						<label for="SecDevView_method_log">Method</label>
						<div class="" style="margin-bottom: 10px;" id="SecDevView_method_log_div">
							<input type="text" class="form-control" id="SecDevView_method_log" placeholder="Nombre del Method" autocomplete="off"
							style="border-radius: 5px;border: 1px solid #aaa; color: black; height:auto; cursor: text;">
						</div>
					</div>
				</div>

				<div class="col-md-3 col-sm-6">
					<div class="form-group">
						<label for="SecDevView_cliente_log">Cliente</label>
						<div class="" style="margin-bottom: 10px;" id="SecDevView_cliente_log_div">
							<input type="text" class="form-control" id="SecDevView_cliente_log" placeholder="Número documento - Nombre del cliente - Web ID" autocomplete="off"
							style="border-radius: 5px;border: 1px solid #aaa; color: black; height:auto; cursor: text;">
							<ul id="SecDevView_lista_cliente_log"></ul>
						</div>
					</div>
				</div>
			</div>

			<div class="row wide" id="SecDevView_filtros_x_dia2" style="display:none">
				<div class="col-md-2 col-sm-4">
					<div class="form-group">
						<label for="SecDevView_betid_log">Bet ID</label>
						<div class="" style="margin-bottom: 10px;" id="SecDevView_betid_log_div">
							<input type="text" class="form-control" id="SecDevView_betid_log" placeholder="Número Bet ID" autocomplete="off"
							style="border-radius: 5px;border: 1px solid #aaa; color: black; height:auto; cursor: text;">
						</div>
					</div>
				</div>

				<div class="col-md-2 col-sm-4">
					<div class="form-group">
						<label for="SecDevView_usuario_log">Usuario</label>
						<div class="" style="margin-bottom: 10px;" id="SecDevView_usuario_log_div">
							<input type="text" class="form-control" id="SecDevView_usuario_log" placeholder="Nombre del usuario" autocomplete="off"
							style="border-radius: 5px;border: 1px solid #aaa; color: black; height:auto; cursor: text;">
							<ul id="SecDevView_lista_usuario_log"></ul>
						</div>
					</div>
				</div>
				
			</div>

			<div class="row wide" id="SecDevView_filtros_x_dia3" style="display:none">
				<div class="col-md-2 col-sm-6">
					<div class="form-group">
						<button class="btn btn-primary" id="SecDevView_btn_buscar_x_dia" type="button" style="width: 100%;">
							<span class="glyphicon glyphicon-search"></span>
							Buscar
						</button>
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
					<button class="btn btn-success" style="width: 100%;" id="SecDevView_btn_exportar_dl">
						<span class="glyphicon glyphicon-download-alt"></span> Exportar
					</button>					
				</div>
			</div>

			<hr>

			<div class="row mt-3 table-responsive" id="dev_view_log_div_tabla" style="display:none">
				<table id="sec_dev_view_log_apis" class="table table-bordered" cellspacing="0" width="100%">
					<thead>
						<tr>	
							<th class="text-center">#</th> 
							<th class="text-center">Created_at</th> 
							<th class="text-center">Proveedor</th>
							<th class="text-center">Method</th>
							<th class="text-center">Bet ID</th>
							<th class="text-center">Cliente </th>
							<th class="text-center">Body</th>
							<th class="text-center">Response</th>
							<th class="text-center">Hash</th>			
							<th class="text-center">Turno</th>
							<th class="text-center">CC ID</th>
							<th class="text-center">Status</th>	
							<th class="text-center">Usuario</th>
							<th class="text-center">Updated_at</th>	
							<th class="text-center">Descargar</th>	 
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>

			<div class="row" style="margin-top: 60px; margin-bottom: 30px; ">
				<div class="row mt-3 table-responsive" id="dev_view_log_x_fec_div_tabla">
					<table id="sec_dev_view_log_x_fec" class="table table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>	
								<th class="text-center">#</th> 
								<th class="text-center">Fecha</th> 
								<th style="background: red;color: #fff;" class="text-center">Cant. Errores</th>
								<th style="background: green;color: #fff;" class="text-center">Cant. Success</th>
								<th style="background: orange;color: #fff;" class="text-center">Total de Registros</th>
								<th class="text-center">Acciones</th>	 
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>		

		</div>

	</div>
 

	<div class="modal fade" id="SecDevView_modal_detalle_registro" role="dialog" aria-labelledby="myModalLabel">
	 				<div class="modal-dialog modal-xl" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 style="font-size: 22px;color: #1a72e3;" class="modal-title" id="SecDevView_modal_tit"></h4>
								<br>
							</div>
							<div class="modal-body">
								
								<div class="row">
									<textarea name="SecDevView_modal_campo" id="SecDevView_modal_campo" rows="10" style="width: 100%;" readonly></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
										<b><i class="fa fa-close"></i> CERRAR</b>
								</button>
									
							</div>
						</div>
					</div>
				</div>
 
</div>