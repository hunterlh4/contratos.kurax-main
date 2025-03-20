<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '" . $sec_id . "' AND sub_sec_id = '" . $sub_sec_id . "' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];


if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
	//die;
} else {
?>

	<style>
		fieldset.dhhBorder {
			border: 1px solid #ddd;
			padding: 0 15px 5px 15px;
			margin: 0 0 15px 0;
			box-shadow: 0px 0px 0px 0px #000;
			border-radius: 5px;
		}

		legend.dhhBorder {
			font-size: 14px;
			text-align: left;
			width: auto;
			padding: 0 10px;
			border-bottom: none;
			margin-bottom: 10px;
			text-transform: capitalize;
		}

		.text-white {
			color: #ffffff !important;
		}

		.btn-flotante {
			font-size: 12px; /* Cambiar el tamaño de la tipografia */
			text-transform: uppercase; /* Texto en mayusculas */
			font-weight: bold; /* Fuente en negrita o bold */
			color: #ffffff; /* Color del texto */
			border-radius: 5px; /* Borde del boton */
			letter-spacing: 2px; /* Espacio entre letras */
			background-color: #415b75; /* Color de fondo */
			padding: 10px 12px; /* Relleno del boton */
			position: fixed;
			bottom: 30px;
			right: 30px;
			transition: all 300ms ease 0ms;
			box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
			z-index: 99;
		}
		.btn-flotante:hover {
			background-color: #304457; /* Color de fondo al pasar el cursor */
			color: #fff;
			/* box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.3); */
			transform: translateY(-7px);
			text-decoration: none;
		}
		@media only screen and (max-width: 600px) {
			.btn-flotante {
				font-size: 14px;
				padding: 12px 20px;
				bottom: 20px;
				right: 20px;
			}
		} 
	</style>
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.1/css/buttons.dataTables.min.css">

	<div class="content container-fluid">
		<div class="page-header wide">
			<div class="row">
				<div class="col-xs-12 text-center">
					<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Kurax - Transacciones Consolidado </h1>
				</div>
			</div>
		</div>

		<div class="page-header wide">
			<div class="row mt-4 mb-2">
				<fieldset class="dhhBorder">
					<legend class="dhhBorder">Búsqueda</legend>
					<form autocomplete="off" id="frm_report_kx_transacciones_consolidado">

						<div class="form-group col-lg-3  col-xs-12">
							<label>Local</label>
							<div class="form-group">
								<div class="input-group col-xs-12">
									<select class="form-control select2" name="sec_kurax_tc_local_id" id="sec_kurax_tc_local_id">
										<option value="">-- Todos --</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group col-lg-2 col-xs-12">
							<label>Canal de Venta</label>
							<div class="form-group">
								<div class="input-group col-xs-12">
									<select class="form-control select2" name="sec_kurax_tc_canal_venta_id" id="sec_kurax_tc_canal_venta_id">
										<option value="">-- Todos --</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group col-lg-2 col-xs-12">
							<label>Estado</label>
							<div class="form-group">
								<div class="input-group col-xs-12">
									<select class="form-control select2" name="sec_kurax_tc_estado" id="sec_kurax_tc_estado">
										<option value="0">-- Todos --</option>
										<option selected value="1">Activo</option>
										<option value="5">Inactivo</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group col-lg-2  col-xs-12">
							<label>Desde</label>
							<div class="form-group">
								<div class="input-group col-xs-12">
									<?php 
									$fecha_hoy = date("Y-m-d");
									$fecha_ayer = date("Y-m-d",strtotime($fecha_hoy." - 1 days"));
									?>
									<input type="text" value="<?= $fecha_ayer ?>" name="sec_kurax_tc_desde" id="sec_kurax_tc_desde" class="form-control text-center">
								</div>
							</div>
						</div>

						<div class="form-group col-lg-2  col-xs-12">
							<label>Hasta</label>
							<div class="form-group">
								<div class="input-group col-xs-12">
									<input type="text" value="<?= $fecha_hoy ?>" name="sec_kurax_tc_hasta" id="sec_kurax_tc_hasta" class="form-control text-center">
								</div>
							</div>
						</div>


						<div class="form-group col-lg-1  col-xs-12">
							<label><br></label>
							<div class="form-group">
								<div class="input-group col-xs-12">
									<button type="submit" class="btn form-control btn-primary"><i class="icon icon-inline fa fa-search"></i> Buscar</button>
								</div>
							</div>
						</div>



					</form>
				</fieldset>
			</div>
		</div>


		<div class="row mt-3" id="">
			<table id="table_kr_transacciones_consolidado" class="table table-bordered table-hover" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th class="text-center bg-primary text-white">#</th>
						<th class="text-center bg-primary text-white">Local</th>
						<th class="text-center bg-primary text-white">Servicio</th>
						<th class="text-center bg-primary text-white">Canal <br> de Venta</th>
						<th class="text-center bg-primary text-white">Tipo <br> Transacción</th>
						<th class="text-center bg-primary text-white">Fecha <br> Consolidado</th>
						<th class="text-center bg-primary text-white">Tickets <br> Apostados</th>
						<th class="text-center bg-primary text-white">Tickets <br> Cancelados</th>
						<th class="text-center bg-primary text-white">Tickets <br> Ganados</th>
						<th class="text-center bg-primary text-white">Tickets <br> Pagados</th>
						<th class="text-center bg-primary text-white">Apostado</th>
						<th class="text-center bg-primary text-white">Cancelado</th>
						<th class="text-center bg-primary text-white">Ganado</th>
						<th class="text-center bg-primary text-white">Pagado</th>
						<th class="text-center bg-primary text-white">Caja <br> Deposito <br>Terminal</th>
						<th class="text-center bg-primary text-white">Vouchers <br>Caja Deposito <br> Terminal</th>
						<th class="text-center bg-primary text-white">Caja <br> Retiro <br> Terminal</th>
						<th class="text-center bg-primary text-white">Vouchers <br>Caja Retiro <br> Terminal</th>
						<th class="text-center bg-primary text-white">Deposito <br>Terminal </th>
						<th class="text-center bg-primary text-white">Total <br> Pagado <br> en Otra <br> Tienda</th>
						<th class="text-center bg-primary text-white">Tickets <br> Pagados <br> en Otra <br>Tienda </th>
						<th class="text-center bg-primary text-white">Total <br> Pagado <br>de Otra <br>Tienda</th>
						<th class="text-center bg-primary text-white">Tickets <br> Pagados <br> de Otra <br>Tienda </th>
						<th class="text-center bg-primary text-white">Estado</th>
						<th class="text-center bg-primary text-white">Fecha Registro</th>
						<th class="text-center bg-primary text-white">Usuario</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
				<tfoot>
					<tr>

						<th class="text-center bg-primary text-white">#</th>
						<th class="text-center bg-primary text-white">Local</th>
						<th class="text-center bg-primary text-white">Servicio</th>
						<th class="text-center bg-primary text-white">Canal <br> de Venta</th>
						<th class="text-center bg-primary text-white">Tipo <br> Transacción</th>
						<th class="text-center bg-primary text-white">Fecha <br> Consolidado</th>
						<th class="text-center bg-primary text-white">Tickets <br> Apostados</th>
						<th class="text-center bg-primary text-white">Tickets <br> Cancelados</th>
						<th class="text-center bg-primary text-white">Tickets <br> Ganados</th>
						<th class="text-center bg-primary text-white">Tickets <br> Pagados</th>
						<th class="text-center bg-primary text-white">Apostado</th>
						<th class="text-center bg-primary text-white">Cancelado</th>
						<th class="text-center bg-primary text-white">Ganado</th>
						<th class="text-center bg-primary text-white">Pagado</th>
						<th class="text-center bg-primary text-white">Caja <br> Deposito <br>Terminal</th>
						<th class="text-center bg-primary text-white">Vouchers <br>Caja Deposito <br> Terminal</th>
						<th class="text-center bg-primary text-white">Caja <br> Retiro <br> Terminal</th>
						<th class="text-center bg-primary text-white">Vouchers <br>Caja Retiro <br> Terminal</th>
						<th class="text-center bg-primary text-white">Deposito <br>Terminal </th>
						<th class="text-center bg-primary text-white">Total <br> Pagado <br> en Otra <br> Tienda</th>
						<th class="text-center bg-primary text-white">Tickets <br> Pagados <br> en Otra <br>Tienda </th>
						<th class="text-center bg-primary text-white">Total <br> Pagado <br>de Otra <br>Tienda</th>
						<th class="text-center bg-primary text-white">Tickets <br> Pagados <br> de Otra <br>Tienda </th>
						<th class="text-center bg-primary text-white">Estado</th>
						<th class="text-center bg-primary text-white">Fecha Registro</th>
						<th class="text-center bg-primary text-white">Usuario</th>
					</tr>
				</tfoot>

			</table>
		</div>
	</div>

	<button type="button" id="button_modal_reprocesar_transacciones_consolidado" class="btn-flotante">Reprocesar</button>


	<!-- INICIO MODAL NUEVO PROPIETARIO CA -->
	<div id="modal_kx_reprocesar_transacciones_consolidado" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modal_nuevo_propietario_titulo_ca">Reprocesar Tickets</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form autocomplete="off" id="frm_report_kx_transacciones_consolidadoASDASD">

							<div class="col-lg-4">
								<div class="form-group">
									<label>Local</label>
									<select class="form-control select2" name="sec_modal_tc_reprocesar_local_id" id="sec_modal_tc_reprocesar_local_id">
										<option value="">-- TODOS --</option>
									</select>
								</div>
							</div>

							<div class="col-lg-4">
								<div class="form-group">
									<label>Canal de Venta</label>
									<select class="form-control select2" name="sec_modal_tc_reprocesar_canal_venta_id" id="sec_modal_tc_reprocesar_canal_venta_id">
										<option value="">-- TODOS --</option>
									</select>
								</div>
							</div>

							<div class="col-lg-4">
								<div class="form-group">
									<label>Fecha</label>
									<input type="date" value="<?= date('Y-m-d') ?>" name="sec_modal_tc_reprocesar_fecha" id="sec_modal_tc_reprocesar_fecha" class="form-control text-center">
								</div>
							</div>
							
						</form>
					</div> 
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
					<button type="button" class="btn btn-primary" id="sec_modal_tc_btn_reprocesar">Reprocesar</button>
				</div>
			</div>
		</div>
	</div>
	<!-- FIN MODAL NUEVO PROPIETARIO CA -->
<?php
}
?>