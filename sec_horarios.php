<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '".$sec_id."' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

$zonas = [];
$result = $mysqli->query("SELECT id, nombre FROM tbl_zonas ORDER BY ord");
while($r = $result->fetch_assoc()) $zonas[$r["id"]] = $r["nombre"];

if(array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])): ?>
	<link rel="stylesheet" href="css/simplePagination.css">
	<div class="content container-fluid content_horarios">
		<div class="page-header wide">
			<div class="row">
				<div class="col-xs-12 text-center">
					<div class="page-title"><i class="icon icon-inline fa fa-fw fa-history"></i> Perfil de Horarios</div>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-xs-12 col-lg-6 col-lg-offset-3">
					<div class="form-group has-feedback has-search">
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group form-inline">
									Mostrar
									<select id="cbHorariosLimit" name="cbHorariosLimit" class="form-control">
										<option value="10">10</option>
										<option value="25">25</option>
										<option value="50">50</option>
										<option value="100">100</option>
									</select>
									Perfiles
								</div>
							</div>
							<div class="col-sm-4 text-right mt-2">Buscar</div>
							<div class="col-sm-4">
								<span class="form-control-feedback mt--1"><i id="icoHorariosSpinner" class="fa fa-spinner fa-spin"></i></span>
								<input type="text" id="txtHorariosFilter" class="form-control w-100" placeholder="">
							</div>
						</div>
					</div>
					<div class="table-responsive">
						<table  id="tblHorarios" class="table table-condensed table-bordered dt-responsive" cellspacing="0">
							<thead>
								<tr class="bg-primary">
									<th class="text-light" style="width: 25px">ID</th>
									<th class="text-light" style="">Nombre</th>
									<th class="text-light" style="width: 80px">Color</th>
									<th class="text-light" style="width: 135px">Creado en</th>
									<th class="text-light" style="width: 135px">Actualizado en</th>
									<th class="text-light" style="width: 145px">Acciones</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
							<?php if(array_key_exists($menu_id,$usuario_permisos) && in_array("new", $usuario_permisos[$menu_id])): ?>
								<tfoot>
									<tr>
										<td colspan="5">
											<div class="form-group">
												<input type="text" id="txtHorariosNombre" placeholder="Crear Nuevo Perfil" class="form-control">
											</div>
										</td>
										<td>
											<div class="form-group">
												<button id="btnHorariosNew" class="btn btn-success btn-block btn-sm trigger-enter"><i class="fa fa-plus"></i> Agregar</button>
											</div>
										</td>
									</tr>
								</tfoot>
							<?php endif; ?>
						</table>
					</div>
					<div class="pull-right">
						<div id="paginationHorarios"></div>
					</div>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-xs-12 col-lg-2">
				    <button class="btn btn-primary btn-sm" id="btnBatchHorariosEdit"><i class="fa fa-list"></i> Definir Perfil</button>
				</div>
				<div class="col-xs-12 col-lg-10">
					<div class="form-inline form-group pull-right">
						<?php if(!$login["zona_id"]): ?>
						    <label for="cbBatchHorariosZonas">Zona: </label>
						    <select id="cbBatchHorariosZonas" name="cbBatchHorariosZonas" class="form-control">
						    	<option value="0">Todas</option>
						    	<?php foreach($zonas as $key => $zona): ?>
						        	<option value="<?php echo $key;?>"><?php echo $zona;?></option>
						    	<?php endforeach; ?>
						    </select>
						<?php endif; ?>

						<label for="txtBatchHorariosStartDate" class="ml-5">Fecha Inicio: </label>
					    <input type="text" id="txtBatchHorariosStartDate" name="txtBatchHorariosStartDate" class="form-control mt-3" value="<?php echo date('Y-m-d'); ?>" readonly>

						<label for="txtBatchHorariosEndDate" class="ml-5">Fecha Fin: </label>
						<input type="text" id="txtBatchHorariosEndDate" name="txtBatchHorariosEndDate" class="form-control mt-3" value="<?php echo date('Y-m-d', strtotime("+5 Days")); ?>" readonly>

						<button id="btnBatchHorariosSearch" class="btn btn-secondary ml-5"><i class="fa fa-filter"></i> Filtrar</button>
					</div>
				</div>

			</div>
			<div class="row table-responsive" style="margin-left:-15px !important;">
				<table id="tblBatchHorarios" class="table table-striped table-hover" style="table-layout: fixed;"></table>
			</div>
		</div>
	</div>

	<div id="mdHorariosDias" class="modal fade" >
	    <div class="modal-dialog modal-xl">
	        <div class="modal-content">
	            <div class="modal-header">
	                <h4 class="modal-title">Horarios por Día - #<span class="text-bold" id="mdHorariosDiasTitle"></span></h4>
	                <button type="button" class="close" data-dismiss="modal">&times;</button>
	            </div>

	            <div class="modal-body">
	                <table  id="tblHorariosDias" class="table table-condensed table-bordered tblh" cellspacing="0"></table>
	            </div>

	            <div class="modal-footer">
	                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
	            </div>
	        </div>
	    </div>
	</div>

	<div id="mdBatchHorarios" class="modal fade">
	    <div class="modal-dialog modal-sm">
	        <div class="modal-content">
	            <div class="modal-header">
	                <h4 class="modal-title">Cambiar Horarios por Grupo</h4>
	                <button type="button" class="close" data-dismiss="modal">&times;</button>
	            </div>

	            <div class="modal-body">
	            	<div class="row">
	            		<div class="col-12">
							<div class="form-group mb-5">
							    <label for="cbBatchHorarios">Perfil de Horario</label>
							    <select id="cbBatchHorarios" name="cbBatchHorarios" class="form-control"></select>
							</div>

	            			<div class="form-check-inline">
	            				<input type="checkbox" id="chkBatchHorariosRange" name="chkBatchHorariosRange" value="" class="form-check-input">
	            				<label class="form-check-label" for="chkBatchHorariosRange">Aplicar para futuras fechas.</label>
	            			</div>

	            			<div class="form-group">
	            				<label for="txtBatchHorariosStart">Fecha: </label>
	            				<input type="text" id="txtBatchHorariosStart" name="txtBatchHorariosStart" class="form-control" readonly>
	            			</div>
	            		</div>
	            	</div>
	            </div>

	            <div class="modal-footer">
	                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
	                <button id="btnBatchHorariosModalSend" type="button" class="btn btn-success btn-sm">Enviar</button>
	            </div>
	        </div>
	    </div>
	</div>
<?php endif; ?>
